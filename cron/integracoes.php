<?php
/**
 * CRON: Sincronização de Integrações (WooCommerce, Banco de Dados, etc)
 * Frequência recomendada do cron do SO: a cada 1-5 minutos
 * O agendamento real respeita o campo `intervalo_sincronizacao` de cada integração
 * (configurável pela UI). Roda apenas as integrações cuja proxima_sincronizacao já chegou.
 *
 * Comando: php /caminho/para/projeto/cron/integracoes.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/IntegracaoConfig.php';
require_once APP_ROOT . '/app/models/IntegracaoWooCommerce.php';
require_once APP_ROOT . '/app/models/IntegracaoLog.php';

use App\Models\IntegracaoConfig;
use App\Models\IntegracaoLog;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronização de integrações...\n";

try {
    $integracaoModel = new IntegracaoConfig();
    $logModel = new IntegracaoLog();

    // findParaSincronizar() retorna apenas integrações ativas cujo proxima_sincronizacao
    // já chegou (NULL ou <= NOW). O intervalo é configurado pelo usuário.
    $integracoes = $integracaoModel->findParaSincronizar();

    echo "Encontradas " . count($integracoes) . " integrações para sincronizar agora\n";

    foreach ($integracoes as $integracao) {
        echo "\n[Integração #{$integracao['id']}] Tipo: {$integracao['tipo']} - Empresa: {$integracao['empresa_id']} - Intervalo: {$integracao['intervalo_sincronizacao']} min\n";

        try {
            switch ($integracao['tipo']) {
                case 'woocommerce':
                    require_once APP_ROOT . '/includes/services/WooCommerceService.php';
                    $wc = new \includes\services\WooCommerceService();

                    // sincronizar() já cuida de:
                    //  - puxar produtos novos/atualizados (sincronizarProdutos)
                    //  - puxar pedidos novos/atualizados e criar contas a receber (sincronizarPedidos)
                    //  - registrar log de sucesso/aviso/erro
                    //  - atualizar ultima_sincronizacao e proxima_sincronizacao
                    $resultado = $wc->sincronizar($integracao['id']);

                    if ($resultado['sucesso']) {
                        $r = $resultado['resultados'];
                        echo "  ✓ {$r['produtos']} produto(s), {$r['pedidos']} pedido(s)\n";
                        if (!empty($r['erros'])) {
                            echo "  ! " . count($r['erros']) . " erro(s) durante a sincronização\n";
                        }
                    } else {
                        echo "  ✗ Erro: " . ($resultado['erro'] ?? 'desconhecido') . "\n";
                    }
                    break;

                case 'banco_dados':
                    if (!empty($integracao['configuracoes'])) {
                        $config = json_decode($integracao['configuracoes'], true);
                        if (!empty($config['tipo_bd']) && !empty($config['host'])) {
                            require_once APP_ROOT . '/includes/services/IntegracaoBancoDadosService.php';
                            $bdService = new \includes\services\IntegracaoBancoDadosService();
                            $conexao = $bdService->conectar($config);
                            if ($conexao) {
                                echo "  ✓ Conexão BD estabelecida\n";
                                $logModel->create($integracao['id'], IntegracaoLog::TIPO_SUCESSO, 'Conexão BD estabelecida');
                            }
                        }
                    }
                    // Atualiza próxima sincronização para respeitar o intervalo configurado
                    $proxima = date('Y-m-d H:i:s', strtotime("+{$integracao['intervalo_sincronizacao']} minutes"));
                    $integracaoModel->updateUltimaSincronizacao($integracao['id'], $proxima);
                    break;

                case 'webhook':
                    echo "  Webhook (acionado por eventos) — pulando\n";
                    break;

                case 'api':
                    echo "  API (chamada sob demanda) — pulando\n";
                    break;
            }
        } catch (Exception $e) {
            echo "  ✗ Erro: " . $e->getMessage() . "\n";
            $logModel->create($integracao['id'], IntegracaoLog::TIPO_ERRO, $e->getMessage());
        }
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Sincronização concluída!\n";

} catch (Exception $e) {
    echo "[ERRO FATAL] " . $e->getMessage() . "\n";
    exit(1);
}
