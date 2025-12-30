<?php
/**
 * Entry point da aplicação
 */

// Define constantes
define('APP_ROOT', dirname(__DIR__));

// Carrega variáveis de ambiente PRIMEIRO
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

// Define APP_DEBUG baseado no .env
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');

// Ativa exibição de erros se estiver em debug
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', APP_ROOT . '/storage/logs/php-errors.log');
}

// Inicia sessão
require_once APP_ROOT . '/app/core/Session.php';
use App\Core\Session;
Session::start();

// Autoloader simples (substituir por Composer depois)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Tenta carregar classes do namespace includes
        if (strncmp('includes\\', $class, 9) === 0) {
            $relativeClass = substr($class, 9);
            $file = APP_ROOT . '/includes/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        }
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    } else {
        // Debug: mostra caminho tentado se APP_DEBUG estiver ativo
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log("Autoloader: Arquivo não encontrado - Tentou carregar: {$file} para classe: {$class}");
        }
    }
});

// Carrega configurações
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

// Define base path para assets
define('ASSET_PATH', '/assets');

// Carrega classes necessárias explicitamente (necessário para produção)
$requiredFiles = [
    APP_ROOT . '/app/core/Request.php',
    APP_ROOT . '/app/core/Response.php',
    APP_ROOT . '/app/core/Router.php',
    APP_ROOT . '/app/core/Database.php',
    APP_ROOT . '/app/core/Controller.php',
    APP_ROOT . '/app/core/App.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        throw new Exception("Arquivo necessário não encontrado: {$file}. APP_ROOT: " . APP_ROOT);
    }
    require_once $file;
}

// Inicia aplicação
use App\Core\App;

try {
    $app = new App();
    $app->run();
} catch (Throwable $e) {
    // Log do erro
    $errorLog = APP_ROOT . '/storage/logs/error.log';
    $logDir = dirname($errorLog);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $errorMessage = date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n";
    $errorMessage .= "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    $errorMessage .= "URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    $errorMessage .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
    @file_put_contents($errorLog, $errorMessage, FILE_APPEND);
    
    if (APP_DEBUG) {
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Erro</title>";
        echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:#fff;padding:15px;border:1px solid #ddd;border-radius:5px;overflow:auto;}</style>";
        echo "</head><body><h1 style='color:#d32f2f;'>Erro na Aplicação</h1>";
        echo "<pre>";
        echo "<strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
        echo "<strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "\n";
        echo "<strong>Linha:</strong> " . $e->getLine() . "\n\n";
        echo "<strong>URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        echo "<strong>Script:</strong> " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
        echo "<strong>Método:</strong> " . htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n\n";
        echo "<strong>Stack Trace:</strong>\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre></body></html>";
    } else {
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Erro 500</title></head><body>";
        echo "<h1>Erro Interno do Servidor</h1>";
        echo "<p>Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.</p>";
        echo "</body></html>";
    }
}
