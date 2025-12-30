<?php
/**
 * Script CLI para gerenciar migrations
 * 
 * Uso:
 *   php migrate.php up          - Executa migrations pendentes
 *   php migrate.php down        - Reverte última migration
 *   php migrate.php down --steps=3  - Reverte 3 migrations
 *   php migrate.php status      - Mostra status das migrations
 */

// Define constantes
define('APP_ROOT', __DIR__);
define('APP_DEBUG', true);

// Carrega variáveis de ambiente
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

// Autoloader para App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Carrega classes includes manualmente
require_once APP_ROOT . '/includes/Migration.php';
require_once APP_ROOT . '/includes/MigrationManager.php';

// Carrega configurações
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

use App\Core\Database;
use includes\MigrationManager;

$command = $argv[1] ?? 'status';
$options = [];

// Parse opções
for ($i = 2; $i < count($argv); $i++) {
    if (strpos($argv[$i], '--') === 0) {
        $parts = explode('=', substr($argv[$i], 2));
        $options[$parts[0]] = $parts[1] ?? true;
    }
}

$manager = new MigrationManager();

switch ($command) {
    case 'up':
        $manager->run();
        break;
        
    case 'down':
        $steps = isset($options['steps']) ? (int)$options['steps'] : 1;
        $manager->rollback($steps);
        break;
        
    case 'status':
        $manager->status();
        break;
        
    default:
        echo "Comando inválido. Use: up, down ou status\n";
        exit(1);
}

