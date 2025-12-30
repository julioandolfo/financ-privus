<?php
/**
 * Informações do servidor e configuração
 * Remover após debug
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Informações do Servidor</h1>";

echo "<h2>Variáveis do Servidor</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Variável</th><th>Valor</th></tr>";

$serverVars = [
    'DOCUMENT_ROOT',
    'SCRIPT_FILENAME',
    'SCRIPT_NAME',
    'REQUEST_URI',
    'PHP_SELF',
    'HTTP_HOST',
    'SERVER_NAME',
    'PWD'
];

foreach ($serverVars as $var) {
    echo "<tr><td><strong>{$var}</strong></td><td>" . ($_SERVER[$var] ?? 'N/A') . "</td></tr>";
}

echo "</table>";

echo "<h2>Estrutura de Diretórios</h2>";
$root = dirname(__DIR__);
echo "APP_ROOT: {$root}<br>";
echo "Existe? " . (is_dir($root) ? 'SIM' : 'NÃO') . "<br><br>";

echo "<h3>Diretórios:</h3>";
$dirs = ['app', 'app/core', 'app/controllers', 'app/models', 'app/views', 'config', 'includes', 'public'];
foreach ($dirs as $dir) {
    $path = $root . '/' . $dir;
    echo "{$dir}: " . (is_dir($path) ? '✓ EXISTE' : '✗ NÃO EXISTE') . " ({$path})<br>";
}

echo "<h3>Arquivos importantes:</h3>";
$files = [
    'app/core/App.php',
    'app/controllers/HomeController.php',
    'config/routes.php',
    'public/index.php'
];
foreach ($files as $file) {
    $path = $root . '/' . $file;
    echo "{$file}: " . (file_exists($path) ? '✓ EXISTE' : '✗ NÃO EXISTE') . " ({$path})<br>";
}

echo "<h2>PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "<br>";

