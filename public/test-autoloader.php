<?php
/**
 * Teste do autoloader
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_ROOT', dirname(__DIR__));

echo "<h1>Teste do Autoloader</h1>";

echo "<h2>1. Verificando APP_ROOT</h2>";
echo "APP_ROOT: " . APP_ROOT . "<br>";
echo "Existe? " . (is_dir(APP_ROOT) ? 'SIM' : 'NÃO') . "<br>";

echo "<h2>2. Verificando estrutura de diretórios</h2>";
$dirs = [
    APP_ROOT . '/app' => 'app',
    APP_ROOT . '/app/core' => 'app/core',
    APP_ROOT . '/config' => 'config',
    APP_ROOT . '/includes' => 'includes'
];

foreach ($dirs as $path => $name) {
    echo "{$name}: " . (is_dir($path) ? 'EXISTE' : 'NÃO EXISTE') . " ({$path})<br>";
}

echo "<h2>3. Verificando arquivos principais</h2>";
$files = [
    APP_ROOT . '/app/core/App.php' => 'App.php',
    APP_ROOT . '/app/core/Database.php' => 'Database.php',
    APP_ROOT . '/app/core/Router.php' => 'Router.php',
    APP_ROOT . '/app/core/Controller.php' => 'Controller.php',
    APP_ROOT . '/config/config.php' => 'config.php',
    APP_ROOT . '/includes/EnvLoader.php' => 'EnvLoader.php'
];

foreach ($files as $path => $name) {
    $exists = file_exists($path);
    echo "{$name}: " . ($exists ? 'EXISTE' : 'NÃO EXISTE') . "<br>";
    if ($exists) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Caminho: {$path}<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Legível? " . (is_readable($path) ? 'SIM' : 'NÃO') . "<br>";
    }
}

echo "<h2>4. Testando autoloader</h2>";
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        if (strncmp('includes\\', $class, 9) === 0) {
            $relativeClass = substr($class, 9);
            $file = APP_ROOT . '/includes/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                echo "Carregando (includes): {$file}<br>";
                require $file;
                return;
            }
        }
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    echo "Tentando carregar: {$class}<br>";
    echo "Caminho: {$file}<br>";
    echo "Existe? " . (file_exists($file) ? 'SIM' : 'NÃO') . "<br>";
    
    if (file_exists($file)) {
        require $file;
        echo "✓ Carregado com sucesso!<br>";
    } else {
        echo "✗ Arquivo não encontrado!<br>";
    }
    echo "<br>";
});

echo "<h2>5. Testando carregamento de classes</h2>";
$classes = [
    'App\\Core\\App',
    'App\\Core\\Database',
    'App\\Core\\Router',
    'App\\Core\\Controller',
    'includes\\EnvLoader'
];

foreach ($classes as $class) {
    echo "Testando: {$class}<br>";
    if (class_exists($class)) {
        echo "✓ Classe existe!<br>";
    } else {
        echo "✗ Classe NÃO encontrada!<br>";
    }
    echo "<br>";
}

echo "<hr>";
echo "<p><strong>Teste concluído!</strong></p>";

