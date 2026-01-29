<?php
/**
 * Visualizador de logs - REMOVER EM PRODUÇÃO
 */

// Verifica senha simples (mude para algo seguro)
$senha = $_GET['key'] ?? '';
if ($senha !== 'debug123') {
    die('Acesso negado. Use ?key=debug123');
}

$logsDir = dirname(__DIR__) . '/logs';
$storageLogsDir = dirname(__DIR__) . '/storage/logs';

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Logs</title>';
echo '<style>
body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
h1, h2 { color: #4fc3f7; }
pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow: auto; max-height: 500px; white-space: pre-wrap; word-wrap: break-word; }
.log-entry { border-bottom: 1px solid #444; padding: 5px 0; }
.error { color: #f44336; }
.warning { color: #ff9800; }
.info { color: #4caf50; }
a { color: #4fc3f7; }
.btn { display: inline-block; padding: 10px 20px; background: #f44336; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn:hover { background: #d32f2f; }
</style></head><body>';

echo '<h1>Visualizador de Logs</h1>';
echo '<p><a href="?key=debug123&clear=app" class="btn">Limpar app_debug.log</a>';
echo '<a href="?key=debug123&clear=despesas" class="btn">Limpar despesas_recorrentes.log</a>';
echo '<a href="?key=debug123&clear=error" class="btn">Limpar error.log</a></p>';

// Limpa logs se solicitado
if (isset($_GET['clear'])) {
    $logName = $_GET['clear'];
    switch($logName) {
        case 'app':
            @file_put_contents($logsDir . '/app_debug.log', '');
            echo '<p style="color: green;">app_debug.log limpo!</p>';
            break;
        case 'despesas':
            @file_put_contents($logsDir . '/despesas_recorrentes.log', '');
            echo '<p style="color: green;">despesas_recorrentes.log limpo!</p>';
            break;
        case 'error':
            @file_put_contents($storageLogsDir . '/error.log', '');
            echo '<p style="color: green;">error.log limpo!</p>';
            break;
    }
}

// Mostra logs
$logFiles = [
    'app_debug.log' => $logsDir . '/app_debug.log',
    'despesas_recorrentes.log' => $logsDir . '/despesas_recorrentes.log',
    'notificacoes.log' => $logsDir . '/notificacoes.log',
    'error.log' => $storageLogsDir . '/error.log'
];

foreach ($logFiles as $name => $path) {
    echo "<h2>{$name}</h2>";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (empty($content)) {
            echo '<p style="color: #888;">Arquivo vazio</p>';
        } else {
            // Mostra últimas 100 linhas
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -100);
            $content = implode("\n", $lastLines);
            
            // Destaca erros
            $content = htmlspecialchars($content);
            $content = preg_replace('/\bERRO\b/', '<span class="error">ERRO</span>', $content);
            $content = preg_replace('/\bERROR\b/i', '<span class="error">ERROR</span>', $content);
            $content = preg_replace('/\bWARNING\b/i', '<span class="warning">WARNING</span>', $content);
            
            echo "<pre>{$content}</pre>";
        }
    } else {
        echo '<p style="color: #888;">Arquivo não existe: ' . htmlspecialchars($path) . '</p>';
    }
}

echo '<hr><p>Última atualização: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p><a href="?key=debug123">Atualizar</a></p>';
echo '</body></html>';
