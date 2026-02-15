<?php
/**
 * CRON: Sincronização Automática Bancária (APIs Diretas)
 * 
 * Suporta: Sicoob, Sicredi, Itaú, Bradesco e Mercado Pago
 * Usa BankServiceFactory para instanciar o service correto de cada banco.
 * 
 * Frequência recomendada: A cada 10 minutos (filtro interno respeita frequencia_sync)
 * Comando: php /caminho/para/projeto/cron/sync_bancaria.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/ConexaoBancaria.php';
require_once APP_ROOT . '/app/models/TransacaoPendente.php';
require_once APP_ROOT . '/app/models/RegraClassificacao.php';
require_once APP_ROOT . '/app/models/ContaBancaria.php';
require_once APP_ROOT . '/includes/services/BankApiInterface.php';
require_once APP_ROOT . '/includes/services/AbstractBankService.php';
require_once APP_ROOT . '/includes/services/BankServiceFactory.php';
require_once APP_ROOT . '/includes/services/SicoobBankService.php';
require_once APP_ROOT . '/includes/services/SicrediBankService.php';
require_once APP_ROOT . '/includes/services/ItauBankService.php';
require_once APP_ROOT . '/includes/services/BradescoBankService.php';
require_once APP_ROOT . '/includes/services/MercadoPagoBankService.php';
require_once APP_ROOT . '/includes/services/ClassificadorIAService.php';

// Manter compatibilidade com serviços antigos (se existirem)
if (file_exists(APP_ROOT . '/includes/services/OpenBankingService.php')) {
    require_once APP_ROOT . '/includes/services/OpenBankingService.php';
}

use App\Core\Database;
use App\Models\ConexaoBancaria;
use App\Models\TransacaoPendente;
use Includes\Services\BankServiceFactory;
use Includes\Services\ClassificadorIAService;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronização bancária automática...\n";

try {
    $conexaoModel = new ConexaoBancaria();
    $transacaoModel = new TransacaoPendente();
    
    $db = Database::getInstance()->getConnection();
    
    // Determinar dia da semana para sync semanal
    $diaSemana = date('N'); // 1 (segunda) a 7 (domingo)
    
    // Buscar todas as conexões ativas com auto_sync habilitado
    $sql = "SELECT * FROM conexoes_bancarias 
            WHERE ativo = 1 AND auto_sync = 1 
            AND status_conexao != 'desconectada'
            AND (
                frequencia_sync = '10min'
                OR frequencia_sync = '30min'
                OR frequencia_sync = 'horaria'
                OR frequencia_sync = 'diaria'
                OR (frequencia_sync = 'semanal' AND DAYOFWEEK(NOW()) = 2)
            )";
    
    $stmt = $db->query($sql);
    $todasConexoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    // Filtrar conexões baseado na frequência e última sincronização
    $conexoes = [];
    foreach ($todasConexoes as $conexao) {
        $deveSincronizar = false;
        $ultimaSync = $conexao['ultima_sincronizacao'] ? strtotime($conexao['ultima_sincronizacao']) : 0;
        $tempoDecorrido = time() - $ultimaSync;
        
        switch ($conexao['frequencia_sync']) {
            case '10min':
                $deveSincronizar = $tempoDecorrido >= 600;
                break;
            case '30min':
                $deveSincronizar = $tempoDecorrido >= 1800;
                break;
            case 'horaria':
                $deveSincronizar = $tempoDecorrido >= 3600;
                break;
            case 'diaria':
                $deveSincronizar = $tempoDecorrido >= 86400;
                break;
            case 'semanal':
                $deveSincronizar = $tempoDecorrido >= 604800 && $diaSemana == 1;
                break;
        }
        
        if ($deveSincronizar) {
            $conexoes[] = $conexao;
        }
    }
    
    echo "Encontradas " . count($conexoes) . " conexões para sincronizar (de " . count($todasConexoes) . " ativas)\n";
    
    $totalNovas = 0;
    $totalErros = 0;
    
    foreach ($conexoes as $conexao) {
        $bancoNome = ucfirst($conexao['banco']);
        $identificacao = $conexao['identificacao'] ?: ('Conta ' . $conexao['id']);
        echo "\n[Conexão #{$conexao['id']}] {$bancoNome} - {$identificacao}\n";
        
        try {
            // Verificar se o banco é suportado pelo novo sistema
            if (!BankServiceFactory::isSuportado($conexao['banco'])) {
                echo "  ! Banco '{$conexao['banco']}' não suportado pela nova arquitetura. Pulando.\n";
                continue;
            }
            
            // Instanciar service via factory
            $service = BankServiceFactory::create($conexao['banco']);
            
            // Descriptografar tokens/credenciais
            $conexaoCredenciais = $conexaoModel->getConexaoComCredenciais($conexao['id']);
            
            // === 1. Autenticar ===
            echo "  Autenticando com {$bancoNome}...\n";
            $tokenData = $service->autenticar($conexaoCredenciais);
            
            // Salvar token atualizado
            if (!empty($tokenData['access_token'])) {
                $conexaoModel->update($conexao['id'], [
                    'access_token' => $tokenData['access_token'],
                    'token_expira_em' => date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600))
                ]);
                // Atualizar para uso nas próximas chamadas
                $conexaoCredenciais['access_token'] = $tokenData['access_token'];
            }
            
            // === 2. Buscar Saldo ===
            echo "  Buscando saldo...\n";
            try {
                $saldoData = $service->getSaldo($conexaoCredenciais);
                $conexaoModel->atualizarSaldo($conexao['id'], $saldoData['saldo']);
                echo "  Saldo: R$ " . number_format($saldoData['saldo'], 2, ',', '.') . "\n";
                
                // Propagar saldo real para conta bancária vinculada
                if (!empty($conexao['conta_bancaria_id'])) {
                    $contaBancariaModel = new \App\Models\ContaBancaria();
                    $contaBancariaModel->setSaldoReal($conexao['conta_bancaria_id'], $saldoData['saldo']);
                    echo "  Saldo propagado para conta bancária #{$conexao['conta_bancaria_id']}\n";
                }
            } catch (\Exception $e) {
                echo "  ! Erro ao buscar saldo: " . $e->getMessage() . "\n";
            }
            
            // === 3. Buscar Transações (últimos 30 dias) ===
            echo "  Buscando transações...\n";
            $dataInicio = date('Y-m-d', strtotime('-30 days'));
            $dataFim = date('Y-m-d');
            
            $transacoes = $service->getTransacoes($conexaoCredenciais, $dataInicio, $dataFim);
            echo "  Encontradas " . count($transacoes) . " transações no período\n";
            
            // Filtrar por tipo_sync (apenas_despesas, apenas_receitas ou ambos)
            $tipoSync = $conexao['tipo_sync'] ?? 'ambos';
            if ($tipoSync !== 'ambos') {
                $antes = count($transacoes);
                $transacoes = array_filter($transacoes, function($t) use ($tipoSync) {
                    if ($tipoSync === 'apenas_despesas') return ($t['tipo'] ?? '') === 'debito';
                    if ($tipoSync === 'apenas_receitas') return ($t['tipo'] ?? '') === 'credito';
                    return true;
                });
                $transacoes = array_values($transacoes);
                $filtradas = $antes - count($transacoes);
                $tipoLabel = $tipoSync === 'apenas_despesas' ? 'despesas' : 'receitas';
                echo "  Filtro tipo_sync: {$tipoSync} - mantidas " . count($transacoes) . " ({$filtradas} {$tipoLabel} filtradas)\n";
            }
            
            // === 4. Processar e Classificar ===
            $classificadorService = new ClassificadorIAService($conexao['empresa_id']);
            $novas = 0;
            $duplicadas = 0;
            
            foreach ($transacoes as $transacao) {
                // Classificar (regras fixas -> histórico -> IA -> fallback)
                $classificacao = $classificadorService->analisar($transacao);
                
                // Preparar dados para salvar
                $transacaoData = [
                    'empresa_id' => $conexao['empresa_id'],
                    'conexao_bancaria_id' => $conexao['id'],
                    'data_transacao' => $transacao['data_transacao'],
                    'descricao_original' => $transacao['descricao_original'],
                    'valor' => $transacao['valor'],
                    'tipo' => $transacao['tipo'],
                    'origem' => $transacao['origem'],
                    'banco_transacao_id' => $transacao['banco_transacao_id'] ?? null,
                    'metodo_pagamento' => $transacao['metodo_pagamento'] ?? null,
                    'saldo_apos' => $transacao['saldo_apos'] ?? null,
                    'referencia_externa' => $transacao['banco_transacao_id'] ?? null,
                    'categoria_sugerida_id' => $classificacao['categoria_id'] ?? $conexao['categoria_padrao_id'],
                    'centro_custo_sugerido_id' => $classificacao['centro_custo_id'] ?? $conexao['centro_custo_padrao_id'],
                    'fornecedor_sugerido_id' => $classificacao['fornecedor_id'] ?? null,
                    'cliente_sugerido_id' => $classificacao['cliente_id'] ?? null,
                    'confianca_ia' => $classificacao['confianca'] ?? null,
                    'justificativa_ia' => $classificacao['justificativa'] ?? null
                ];
                
                $resultado = $transacaoModel->create($transacaoData);
                if ($resultado) {
                    $novas++;
                } else {
                    $duplicadas++;
                }
            }
            
            echo "  + {$novas} novas transações importadas ({$duplicadas} duplicadas ignoradas)\n";
            $totalNovas += $novas;
            
            // === 5. Atualizar status da conexão ===
            $conexaoModel->atualizarUltimaSync($conexao['id']);
            $conexaoModel->update($conexao['id'], [
                'status_conexao' => 'ativa',
                'ultimo_erro' => null
            ]);
            
        } catch (\Exception $e) {
            $totalErros++;
            echo "  ! ERRO: " . $e->getMessage() . "\n";
            
            // Registrar erro na conexão
            $conexaoModel->registrarErro($conexao['id'], $e->getMessage());
        }
    }
    
    echo "\n========================================\n";
    echo "[" . date('Y-m-d H:i:s') . "] Sincronização concluída!\n";
    echo "  Total de novas transações: {$totalNovas}\n";
    echo "  Total de erros: {$totalErros}\n";
    echo "========================================\n";
    
} catch (\Exception $e) {
    echo "[ERRO FATAL] " . $e->getMessage() . "\n";
    exit(1);
}
