<?php
/**
 * CRON: Sincronização Automática Bancária
 * Frequência: A cada hora (ou conforme configurado em cada conexão)
 * Comando: php /caminho/para/projeto/cron/sync_bancaria.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/ConexaoBancaria.php';
require_once APP_ROOT . '/app/models/TransacaoPendente.php';
require_once APP_ROOT . '/includes/services/OpenBankingService.php';
require_once APP_ROOT . '/includes/services/ClassificadorIAService.php';

use App\Core\Database;
use App\Models\ConexaoBancaria;
use App\Models\TransacaoPendente;
use Includes\Services\OpenBankingService;
use Includes\Services\ClassificadorIAService;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronização bancária automática...\n";

try {
    $conexaoModel = new ConexaoBancaria();
    $transacaoModel = new TransacaoPendente();
    $openBankingService = new OpenBankingService();
    
    // Buscar todas as conexões ativas com auto_sync habilitado
    $db = Database::getInstance()->getConnection();
    
    // Determinar quais frequências devem ser sincronizadas agora
    $horaAtual = date('i'); // Minuto atual
    $diaSemana = date('N'); // 1 (segunda) a 7 (domingo)
    
    $sql = "SELECT * FROM conexoes_bancarias 
            WHERE ativo = 1 AND auto_sync = 1 
            AND (
                frequencia_sync = '10min'
                OR frequencia_sync = '30min'
                OR frequencia_sync = 'horaria'
                OR frequencia_sync = 'diaria'
                OR (frequencia_sync = 'semanal' AND DAYOFWEEK(NOW()) = 2)
            )";
    
    $stmt = $db->query($sql);
    $todasConexoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filtrar conexões baseado na frequência e última sincronização
    $conexoes = [];
    foreach ($todasConexoes as $conexao) {
        $deveSincronizar = false;
        $ultimaSync = $conexao['ultima_sincronizacao'] ? strtotime($conexao['ultima_sincronizacao']) : 0;
        $tempoDecorrido = time() - $ultimaSync;
        
        switch ($conexao['frequencia_sync']) {
            case '10min':
                // Sincronizar se passaram mais de 10 minutos
                $deveSincronizar = $tempoDecorrido >= 600; // 10 * 60
                break;
            case '30min':
                // Sincronizar se passaram mais de 30 minutos
                $deveSincronizar = $tempoDecorrido >= 1800; // 30 * 60
                break;
            case 'horaria':
                // Sincronizar se passaram mais de 1 hora
                $deveSincronizar = $tempoDecorrido >= 3600; // 60 * 60
                break;
            case 'diaria':
                // Sincronizar se passaram mais de 24 horas
                $deveSincronizar = $tempoDecorrido >= 86400; // 24 * 60 * 60
                break;
            case 'semanal':
                // Sincronizar se passaram mais de 7 dias E é segunda-feira
                $deveSincronizar = $tempoDecorrido >= 604800 && $diaSemana == 1; // 7 * 24 * 60 * 60
                break;
        }
        
        if ($deveSincronizar) {
            $conexoes[] = $conexao;
        }
    }
    
    echo "Encontradas " . count($conexoes) . " conexões para sincronizar\n";
    
    foreach ($conexoes as $conexao) {
        echo "\n[Conexão #{$conexao['id']}] Banco: {$conexao['banco']} - {$conexao['identificacao']}\n";
        
        try {
            // Verificar se token expirou
            if (strtotime($conexao['token_expira_em']) < time()) {
                echo "  Token expirado, renovando...\n";
                $newTokens = $openBankingService->renovarAccessToken($conexao);
                
                $encryptionKey = getenv('ENCRYPTION_KEY') ?: 'default_key_change_in_production';
                $conexao['access_token'] = OpenBankingService::encrypt($newTokens['access_token'], $encryptionKey);
                $conexao['token_expira_em'] = date('Y-m-d H:i:s', time() + $newTokens['expires_in']);
                
                $conexaoModel->update($conexao['id'], $conexao);
                echo "  Token renovado com sucesso\n";
            }
            
            // Sincronizar transações
            if ($conexao['tipo'] === 'cartao_credito') {
                $transacoes = $openBankingService->sincronizarCartao($conexao);
            } else {
                $transacoes = $openBankingService->sincronizarExtrato($conexao);
            }
            
            echo "  Encontradas " . count($transacoes) . " transações\n";
            
            // Processar transações
            $classificadorService = new ClassificadorIAService($conexao['empresa_id']);
            $novas = 0;
            
            foreach ($transacoes as $transacao) {
                // Gerar hash único
                $hash = hash('sha256', $conexao['id'] . $transacao['transacao_id_banco'] . $transacao['data_transacao'] . $transacao['valor']);
                
                // Verificar duplicata
                $existente = $transacaoModel->findByHash($hash);
                if ($existente) {
                    continue;
                }
                
                // Classificar com IA
                $classificacao = $classificadorService->analisar($transacao);
                
                // Salvar
                $transacaoData = [
                    'empresa_id' => $conexao['empresa_id'],
                    'conexao_bancaria_id' => $conexao['id'],
                    'data_transacao' => $transacao['data_transacao'],
                    'descricao_original' => $transacao['descricao_original'],
                    'valor' => $transacao['valor'],
                    'tipo' => $transacao['tipo'],
                    'origem' => $transacao['origem'],
                    'referencia_externa' => $transacao['transacao_id_banco'],
                    'transacao_hash' => $hash,
                    'categoria_sugerida_id' => $classificacao['categoria_id'] ?? $conexao['categoria_padrao_id'],
                    'centro_custo_sugerido_id' => $classificacao['centro_custo_id'] ?? $conexao['centro_custo_padrao_id'],
                    'fornecedor_sugerido_id' => $classificacao['fornecedor_id'] ?? null,
                    'cliente_sugerido_id' => $classificacao['cliente_id'] ?? null,
                    'confianca_ia' => $classificacao['confianca'] ?? null,
                    'justificativa_ia' => $classificacao['justificativa'] ?? null
                ];
                
                if ($transacaoModel->create($transacaoData)) {
                    $novas++;
                }
            }
            
            echo "  ✓ {$novas} novas transações importadas\n";
            
            // Atualizar última sincronização
            $conexaoModel->update($conexao['id'], array_merge($conexao, [
                'ultima_sincronizacao' => date('Y-m-d H:i:s')
            ]));
            
        } catch (Exception $e) {
            echo "  ✗ Erro: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Sincronização concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
