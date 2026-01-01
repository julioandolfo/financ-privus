<?php
/**
 * CRON: Backup Automático do Banco de Dados
 * Frequência: Diário às 03:00
 * Comando: php /caminho/para/projeto/cron/backup_database.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup do banco de dados...\n";

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db = $_ENV['DB_NAME'] ?? 'financeiro';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    // Diretório de backup
    $backupDir = APP_ROOT . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        echo "Diretório de backup criado\n";
    }
    
    // Nome do arquivo com timestamp
    $filename = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Comando mysqldump
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($user),
        escapeshellarg($pass),
        escapeshellarg($db),
        escapeshellarg($filename)
    );
    
    // Executar backup
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0 && file_exists($filename)) {
        $filesize = filesize($filename);
        echo "✓ Backup criado com sucesso: " . basename($filename) . "\n";
        echo "  Tamanho: " . number_format($filesize / 1024 / 1024, 2) . " MB\n";
        
        // Comprimir backup
        $gzFilename = $filename . '.gz';
        exec("gzip -9 " . escapeshellarg($filename), $gzOutput, $gzReturn);
        
        if ($gzReturn === 0 && file_exists($gzFilename)) {
            $gzSize = filesize($gzFilename);
            echo "✓ Backup comprimido: " . basename($gzFilename) . "\n";
            echo "  Tamanho comprimido: " . number_format($gzSize / 1024 / 1024, 2) . " MB\n";
            echo "  Taxa de compressão: " . number_format((1 - $gzSize / $filesize) * 100, 1) . "%\n";
        }
        
        // Limpar backups antigos (manter últimos 30 dias)
        $files = glob($backupDir . '/backup_*.sql.gz');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 30 * 24 * 3600) { // 30 dias
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        if ($deleted > 0) {
            echo "✓ {$deleted} backup(s) antigo(s) removido(s)\n";
        }
        
        // Contar backups restantes
        $totalBackups = count(glob($backupDir . '/backup_*.sql.gz'));
        echo "Total de backups mantidos: {$totalBackups}\n";
        
    } else {
        echo "✗ Erro ao criar backup\n";
        if (!empty($output)) {
            echo "Saída: " . implode("\n", $output) . "\n";
        }
        exit(1);
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Backup concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
