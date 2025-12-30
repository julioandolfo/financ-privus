<?php
/**
 * Entry point da aplicação
 */

// Define constantes
define('APP_ROOT', dirname(__DIR__));
define('APP_DEBUG', true); // Mudar para false em produção

// Carrega variáveis de ambiente
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

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
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Carrega configurações
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

// Define base path para assets
define('ASSET_PATH', '/assets');

// Inicia aplicação
use App\Core\App;

try {
    $app = new App();
    $app->run();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo "<pre>";
        echo "Erro: " . $e->getMessage() . "\n";
        echo "Arquivo: " . $e->getFile() . "\n";
        echo "Linha: " . $e->getLine() . "\n";
        echo "URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        echo "Script: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    } else {
        http_response_code(500);
        echo "Erro interno do servidor";
    }
}
