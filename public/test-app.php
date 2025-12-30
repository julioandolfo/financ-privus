<?php
/**
 * Script de teste da aplicação com debug completo
 * Acesse: https://financeiro.privus.com.br/test-app.php
 * REMOVA ESTE ARQUIVO APÓS O TESTE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste da Aplicação</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f4f4f4;padding:10px;overflow:auto;}</style>";

// Define constantes
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

echo "<h2>1. Verificando Estrutura</h2>";
echo "<pre>";
echo "APP_ROOT: " . APP_ROOT . " - " . (is_dir(APP_ROOT) ? '✓' : '✗') . "\n";
echo "PUBLIC_ROOT: " . PUBLIC_ROOT . " - " . (is_dir(PUBLIC_ROOT) ? '✓' : '✗') . "\n";
echo "</pre>";

// Carrega .env usando EnvLoader real
echo "<h2>2. Carregando .env</h2>";

// Inclui o arquivo EnvLoader
$envLoaderFile = APP_ROOT . '/includes/EnvLoader.php';
if (!file_exists($envLoaderFile)) {
    echo "<p class='error'>✗ EnvLoader.php não encontrado!</p>";
} else {
    require_once $envLoaderFile;
    
    try {
        // EnvLoader::load() é um método estático
        EnvLoader::load(APP_ROOT . '/.env');
        echo "<p class='success'>✓ .env carregado via EnvLoader</p>";
        
        // Mostra as variáveis de banco
        echo "<pre>";
        echo "DB_HOST: " . (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'não definido')) . "\n";
        echo "DB_NAME: " . (getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'não definido')) . "\n";
        echo "DB_USER: " . (getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'não definido')) . "\n";
        echo "DB_PASS: " . (getenv('DB_PASS') || isset($_ENV['DB_PASS']) ? '*********' : 'não definido') . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Erro ao carregar .env: " . $e->getMessage() . "</p>";
        echo "<pre class='error'>" . $e->getTraceAsString() . "</pre>";
    }
}

// Testa autoloader
echo "<h2>3. Testando Autoloader</h2>";
require_once APP_ROOT . '/vendor/autoload.php';
echo "<p class='success'>✓ Autoloader carregado</p>";

// Testa Database
echo "<h2>4. Testando Database Class</h2>";
try {
    require_once APP_ROOT . '/app/core/Database.php';
    echo "<p class='success'>✓ Database.php incluído</p>";
    
    $db = \App\Core\Database::getInstance();
    echo "<p class='success'>✓ Database::getInstance() OK</p>";
    
    $conn = $db->getConnection();
    echo "<p class='success'>✓ getConnection() OK</p>";
    
    $result = $conn->query("SELECT 1 as test")->fetch();
    echo "<p class='success'>✓ Query teste OK: " . json_encode($result) . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro no Database:</p>";
    echo "<pre class='error'>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Testa Model
echo "<h2>5. Testando Model Class</h2>";
try {
    require_once APP_ROOT . '/app/core/Model.php';
    echo "<p class='success'>✓ Model.php incluído</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro no Model:</p>";
    echo "<pre class='error'>" . $e->getMessage() . "</pre>";
}

// Testa Usuario Model
echo "<h2>6. Testando Usuario Model</h2>";
try {
    require_once APP_ROOT . '/app/models/Usuario.php';
    echo "<p class='success'>✓ Usuario.php incluído</p>";
    
    $usuario = new \App\Models\Usuario();
    echo "<p class='success'>✓ Usuario instanciado</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro no Usuario:</p>";
    echo "<pre class='error'>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p style='color:orange;'><strong>⚠ IMPORTANTE: Delete este arquivo (test-app.php) após o teste!</strong></p>";

