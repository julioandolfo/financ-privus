<?php
/**
 * Script de Cron para processar recorrências e notificações
 * 
 * Configure no crontab para rodar diariamente:
 * 0 6 * * * php /caminho/para/financeiro/cron/processar_recorrencias.php >> /var/log/financeiro_cron.log 2>&1
 * 
 * Ou no Windows Task Scheduler:
 * php C:\laragon\www\financeiro\cron\processar_recorrencias.php
 */

// Evita execução via browser
if (php_sapi_name() !== 'cli') {
    die('Este script só pode ser executado via linha de comando');
}

// Define constantes
define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// Carrega configurações
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';

// Autoloader simples para classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/models/',
        BASE_PATH . '/app/core/',
        BASE_PATH . '/includes/services/'
    ];
    
    $className = str_replace(['App\\Models\\', 'App\\Core\\', 'Includes\\Services\\'], '', $class);
    $className = str_replace('\\', '/', $className);
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

use Includes\Services\RecorrenciaService;
use Includes\Services\NotificacaoService;

// Log helper
function logMsg($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
}

logMsg('=== Iniciando processamento de recorrências e notificações ===');

try {
    // 1. Processa recorrências (despesas e receitas)
    logMsg('Processando recorrências...');
    $recorrenciaService = new RecorrenciaService();
    $resultado = $recorrenciaService->processarRecorrencias();
    
    logMsg("Despesas geradas: {$resultado['despesas_geradas']}");
    logMsg("Receitas geradas: {$resultado['receitas_geradas']}");
    
    if (!empty($resultado['erros'])) {
        foreach ($resultado['erros'] as $erro) {
            logMsg($erro, 'ERROR');
        }
    }
    
    // 2. Gera notificações de vencimento
    logMsg('Gerando notificações de vencimento...');
    $notificacaoService = new NotificacaoService();
    $notificacaoService->gerarNotificacoesVencimento();
    
    // 3. Gera notificações de contas vencidas
    logMsg('Gerando notificações de contas vencidas...');
    $notificacaoService->gerarNotificacoesVencidas();
    
    // 4. Limpa notificações antigas
    logMsg('Limpando notificações antigas...');
    $notificacaoService->limparAntigas(30);
    
    // 5. Envia Web Push pendentes
    logMsg('Enviando Web Push...');
    $notificacaoService->enviarWebPush();
    
    // Tempo de execução
    $endTime = microtime(true);
    $executionTime = round($endTime - START_TIME, 2);
    
    logMsg("=== Processamento concluído em {$executionTime}s ===");
    
} catch (\Exception $e) {
    logMsg("Erro fatal: " . $e->getMessage(), 'FATAL');
    logMsg("Stack trace: " . $e->getTraceAsString(), 'DEBUG');
    exit(1);
}

exit(0);
