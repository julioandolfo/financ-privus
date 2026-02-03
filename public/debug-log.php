<?php
/**
 * Script temporário para visualizar logs de erro
 * REMOVER EM PRODUÇÃO após resolver o problema
 */

// Proteção simples - mude a chave se precisar
$chave = $_GET['key'] ?? '';
if ($chave !== 'debug2026') {
    http_response_code(403);
    die('Acesso negado. Use ?key=debug2026');
}

header('Content-Type: text/plain; charset=utf-8');

$logFile = dirname(__DIR__) . '/storage/logs/error.log';

echo "=== DEBUG LOG VIEWER ===\n\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n\n";

// Verificar .env
echo "=== CONFIGURAÇÕES ===\n";
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    preg_match('/APP_DEBUG=(.*)/', $envContent, $matches);
    echo "APP_DEBUG no .env: " . ($matches[1] ?? 'não encontrado') . "\n";
} else {
    echo "Arquivo .env não encontrado!\n";
}

echo "\n=== ÚLTIMOS 100 ERROS ===\n\n";

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -100);
    echo implode("\n", $lastLines);
} else {
    echo "Arquivo de log não existe: $logFile\n";
    
    // Tentar criar diretório
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        echo "Diretório de logs não existe: $logDir\n";
        if (@mkdir($logDir, 0755, true)) {
            echo "Diretório criado com sucesso!\n";
        } else {
            echo "Falha ao criar diretório!\n";
        }
    }
}

echo "\n\n=== PHP ERROR LOG ===\n";
$phpErrorLog = ini_get('error_log');
echo "PHP error_log path: " . ($phpErrorLog ?: 'não definido') . "\n";
if ($phpErrorLog && file_exists($phpErrorLog)) {
    $content = file_get_contents($phpErrorLog);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -50);
    echo implode("\n", $lastLines);
}
