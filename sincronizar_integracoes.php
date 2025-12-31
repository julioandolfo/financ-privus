#!/usr/bin/env php
<?php
/**
 * Script CLI para sincronização automática de integrações
 * 
 * Uso:
 *   php sincronizar_integracoes.php
 * 
 * Para rodar via cron a cada hora:
 *   0 * * * * cd /caminho/do/projeto && php sincronizar_integracoes.php >> logs/sincronizacoes.log 2>&1
 */

require_once __DIR__ . '/includes/EnvLoader.php';
require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;
use App\Models\IntegracaoConfig;
use Includes\Services\WooCommerceService;
use Includes\Services\IntegracaoBancoDadosService;

// Carrega .env
\Includes\EnvLoader::load(__DIR__);

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronização de integrações...\n";

try {
    // Busca integrações ativas que precisam sincronizar
    $db = Database::getInstance()->getConnection();
    
    $sql = "
        SELECT * FROM integracoes_config 
        WHERE ativo = 1 
        AND (
            ultima_sincronizacao IS NULL 
            OR proxima_sincronizacao <= NOW()
        )
        ORDER BY proxima_sincronizacao ASC
    ";
    
    $stmt = $db->query($sql);
    $integracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($integracoes)) {
        echo "Nenhuma integração pendente de sincronização.\n";
        exit(0);
    }
    
    echo "Encontradas " . count($integracoes) . " integrações para sincronizar.\n\n";
    
    foreach ($integracoes as $integracao) {
        echo "----------------------------------------\n";
        echo "Sincronizando: {$integracao['nome']} (ID: {$integracao['id']})\n";
        echo "Tipo: {$integracao['tipo']}\n";
        
        try {
            $resultado = null;
            
            if ($integracao['tipo'] === 'woocommerce') {
                $service = new WooCommerceService();
                $resultado = $service->sincronizar($integracao['id']);
            } elseif ($integracao['tipo'] === 'banco_dados') {
                $service = new IntegracaoBancoDadosService();
                $resultado = $service->sincronizar($integracao['id']);
            }
            
            if ($resultado && $resultado['sucesso']) {
                echo "✓ Sucesso!\n";
                if (isset($resultado['resultados'])) {
                    $r = $resultado['resultados'];
                    if (isset($r['produtos'])) echo "  - Produtos: {$r['produtos']}\n";
                    if (isset($r['pedidos'])) echo "  - Pedidos: {$r['pedidos']}\n";
                    if (!empty($r['erros'])) {
                        echo "  - Erros: " . count($r['erros']) . "\n";
                        foreach ($r['erros'] as $erro) {
                            echo "    * {$erro}\n";
                        }
                    }
                } elseif (isset($resultado['total'])) {
                    echo "  - Total importado: {$resultado['total']}\n";
                }
            } else {
                echo "✗ Erro: " . ($resultado['erro'] ?? 'Desconhecido') . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Exceção: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
    
    echo "----------------------------------------\n";
    echo "[" . date('Y-m-d H:i:s') . "] Sincronização concluída.\n\n";
    
} catch (Exception $e) {
    echo "ERRO FATAL: {$e->getMessage()}\n";
    exit(1);
}

exit(0);
