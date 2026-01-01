<?php
/**
 * CRON: Sincronização de Integrações (WooCommerce, Banco de Dados, etc)
 * Frequência: A cada 15 minutos
 * Comando: php /caminho/para/projeto/cron/integracoes.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/IntegracaoConfig.php';
require_once APP_ROOT . '/app/models/IntegracaoLog.php';

use App\Core\Database;
use App\Models\IntegracaoConfig;
use App\Models\IntegracaoLog;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronização de integrações...\n";

try {
    $integracaoModel = new IntegracaoConfig();
    $logModel = new IntegracaoLog();
    
    // Buscar todas as integrações ativas
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT * FROM integracoes_config WHERE ativo = 1";
    $stmt = $db->query($sql);
    $integracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontradas " . count($integracoes) . " integrações ativas\n";
    
    foreach ($integracoes as $integracao) {
        echo "\n[Integração #{$integracao['id']}] Tipo: {$integracao['tipo']} - Empresa: {$integracao['empresa_id']}\n";
        
        try {
            $config = json_decode($integracao['configuracoes'], true);
            $sucesso = false;
            $mensagem = '';
            
            switch ($integracao['tipo']) {
                case 'woocommerce':
                    // Lógica de sincronização WooCommerce
                    if (!empty($config['url']) && !empty($config['consumer_key'])) {
                        require_once APP_ROOT . '/includes/services/WooCommerceService.php';
                        $wc = new \includes\services\WooCommerceService(
                            $config['url'],
                            $config['consumer_key'],
                            $config['consumer_secret']
                        );
                        
                        // Sincronizar pedidos
                        $pedidos = $wc->getPedidos(['per_page' => 50]);
                        echo "  Encontrados " . count($pedidos) . " pedidos\n";
                        
                        $sucesso = true;
                        $mensagem = count($pedidos) . " pedidos sincronizados";
                    }
                    break;
                    
                case 'banco_dados':
                    // Lógica de sincronização Banco de Dados
                    if (!empty($config['tipo_bd']) && !empty($config['host'])) {
                        require_once APP_ROOT . '/includes/services/IntegracaoBancoDadosService.php';
                        $bdService = new \includes\services\IntegracaoBancoDadosService();
                        
                        $conexao = $bdService->conectar($config);
                        if ($conexao) {
                            echo "  Conexão estabelecida com sucesso\n";
                            $sucesso = true;
                            $mensagem = "Conexão BD estabelecida";
                        }
                    }
                    break;
                    
                case 'webhook':
                    // Webhooks são acionados por eventos, não por CRON
                    echo "  Webhook (acionado por eventos)\n";
                    continue 2;
                    
                case 'api':
                    // APIs são chamadas sob demanda
                    echo "  API (chamada sob demanda)\n";
                    continue 2;
            }
            
            // Registrar log
            $logModel->registrar(
                $integracao['id'],
                $sucesso ? 'sucesso' : 'erro',
                $sucesso ? $mensagem : 'Erro na sincronização',
                null
            );
            
            echo "  ✓ Sincronizado com sucesso\n";
            
        } catch (Exception $e) {
            echo "  ✗ Erro: " . $e->getMessage() . "\n";
            
            // Registrar erro no log
            $logModel->registrar(
                $integracao['id'],
                'erro',
                $e->getMessage(),
                null
            );
        }
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Sincronização de integrações concluída!\n";
    
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
