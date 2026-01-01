<?php
/**
 * CRON: Limpeza de Sessões, Logs e Arquivos Temporários
 * Frequência: Diário às 02:00
 * Comando: php /caminho/para/projeto/cron/limpeza_sistema.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';

use App\Core\Database;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando limpeza do sistema...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Limpar sessões expiradas (PHP Sessions)
    echo "\n1. Limpando sessões PHP...\n";
    $sessionPath = session_save_path();
    if (empty($sessionPath)) {
        $sessionPath = sys_get_temp_dir();
    }
    
    $sessionFiles = glob($sessionPath . '/sess_*');
    $now = time();
    $sessionsCleaned = 0;
    
    foreach ($sessionFiles as $file) {
        if (is_file($file) && $now - filemtime($file) >= 24 * 3600) { // 24 horas
            unlink($file);
            $sessionsCleaned++;
        }
    }
    echo "  ✓ {$sessionsCleaned} sessões expiradas removidas\n";
    
    // 2. Limpar logs de integrações antigos (manter últimos 90 dias)
    echo "\n2. Limpando logs de integrações...\n";
    $sql = "DELETE FROM integracoes_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)";
    $stmt = $db->query($sql);
    $logsDeleted = $stmt->rowCount();
    echo "  ✓ {$logsDeleted} logs antigos removidos\n";
    
    // 3. Limpar logs de API antigos (manter últimos 60 dias)
    echo "\n3. Limpando logs de API...\n";
    $sql = "DELETE FROM api_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)";
    $stmt = $db->query($sql);
    $apiLogsDeleted = $stmt->rowCount();
    echo "  ✓ {$apiLogsDeleted} logs de API removidos\n";
    
    // 4. Limpar transações pendentes antigas ignoradas (manter 30 dias)
    echo "\n4. Limpando transações pendentes antigas...\n";
    $sql = "DELETE FROM transacoes_pendentes 
            WHERE status IN ('ignorada', 'erro') 
            AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $db->query($sql);
    $transacoesDeleted = $stmt->rowCount();
    echo "  ✓ {$transacoesDeleted} transações antigas removidas\n";
    
    // 5. Limpar arquivos temporários de upload
    echo "\n5. Limpando arquivos temporários...\n";
    $tempDir = APP_ROOT . '/public/uploads/temp';
    if (is_dir($tempDir)) {
        $tempFiles = glob($tempDir . '/*');
        $tempCleaned = 0;
        
        foreach ($tempFiles as $file) {
            if (is_file($file) && $now - filemtime($file) >= 24 * 3600) { // 24 horas
                unlink($file);
                $tempCleaned++;
            }
        }
        echo "  ✓ {$tempCleaned} arquivos temporários removidos\n";
    } else {
        echo "  ℹ Diretório temp não existe\n";
    }
    
    // 6. Otimizar tabelas do banco de dados
    echo "\n6. Otimizando tabelas do banco...\n";
    $tables = [
        'usuarios', 'empresas', 'fornecedores', 'clientes',
        'contas_pagar', 'contas_receber', 'movimentacoes_caixa',
        'integracoes_logs', 'transacoes_pendentes', 'conexoes_bancarias'
    ];
    
    $optimized = 0;
    foreach ($tables as $table) {
        try {
            $db->query("OPTIMIZE TABLE {$table}");
            $optimized++;
        } catch (Exception $e) {
            echo "  ⚠ Erro ao otimizar {$table}: " . $e->getMessage() . "\n";
        }
    }
    echo "  ✓ {$optimized} tabelas otimizadas\n";
    
    // 7. Estatísticas de espaço
    echo "\n7. Estatísticas de espaço:\n";
    $sql = "SELECT 
                table_name AS `Tabela`,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `Tamanho (MB)`
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
            LIMIT 10";
    
    $stmt = $db->query($sql);
    $tabelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Maiores tabelas:\n";
    foreach ($tabelas as $tabela) {
        echo "    - {$tabela['Tabela']}: {$tabela['Tamanho (MB)']} MB\n";
    }
    
    $totalSize = array_sum(array_column($tabelas, 'Tamanho (MB)'));
    echo "  Total (top 10): " . number_format($totalSize, 2) . " MB\n";
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Limpeza concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
