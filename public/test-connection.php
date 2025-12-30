<?php
/**
 * Script de teste de conexão e configuração
 * Remover após debug
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Configuração</h1>";

// Teste 1: Verificar .env
echo "<h2>1. Verificando arquivo .env</h2>";
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    echo "✓ Arquivo .env encontrado<br>";
    $envContent = file_get_contents($envPath);
    echo "<pre>" . htmlspecialchars($envContent) . "</pre>";
} else {
    echo "✗ Arquivo .env NÃO encontrado em: " . $envPath . "<br>";
}

// Teste 2: Carregar EnvLoader
echo "<h2>2. Carregando variáveis de ambiente</h2>";
require_once dirname(__DIR__) . '/includes/EnvLoader.php';
EnvLoader::load();
echo "✓ EnvLoader carregado<br>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NÃO DEFINIDO') . "<br>";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NÃO DEFINIDO') . "<br>";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NÃO DEFINIDO') . "<br>";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '***' : 'NÃO DEFINIDO') . "<br>";

// Teste 3: Testar conexão com banco
echo "<h2>3. Testando conexão com banco de dados</h2>";
try {
    require_once dirname(__DIR__) . '/config/database.php';
    $config = require dirname(__DIR__) . '/config/database.php';
    
    echo "Config carregada:<br>";
    echo "<pre>" . print_r($config, true) . "</pre>";
    
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    echo "DSN: " . htmlspecialchars($dsn) . "<br>";
    
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Conexão estabelecida com sucesso!<br>";
    
    // Teste 4: Listar tabelas
    echo "<h2>4. Tabelas no banco de dados</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Total de tabelas: " . count($tables) . "<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "✗ Erro ao conectar: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Código: " . $e->getCode() . "<br>";
}

// Teste 5: Verificar autoloader
echo "<h2>5. Testando autoloader</h2>";
$testClasses = [
    'App\\Core\\Database',
    'App\\Core\\App',
    'App\\Core\\Router',
    'App\\Models\\Empresa'
];

foreach ($testClasses as $class) {
    if (class_exists($class)) {
        echo "✓ {$class}<br>";
    } else {
        echo "✗ {$class} NÃO encontrada<br>";
    }
}

// Teste 6: Verificar arquivos importantes
echo "<h2>6. Verificando arquivos importantes</h2>";
$files = [
    dirname(__DIR__) . '/public/index.php' => 'index.php',
    dirname(__DIR__) . '/app/core/App.php' => 'App.php',
    dirname(__DIR__) . '/config/routes.php' => 'routes.php',
    dirname(__DIR__) . '/config/database.php' => 'database.php'
];

foreach ($files as $path => $name) {
    if (file_exists($path)) {
        echo "✓ {$name} existe<br>";
    } else {
        echo "✗ {$name} NÃO existe em: " . $path . "<br>";
    }
}

// Teste 7: Verificar permissões
echo "<h2>7. Verificando permissões</h2>";
$dirs = [
    dirname(__DIR__) . '/storage/logs' => 'storage/logs',
    dirname(__DIR__) . '/storage/cache' => 'storage/cache',
    dirname(__DIR__) . '/storage/uploads' => 'storage/uploads'
];

foreach ($dirs as $path => $name) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? 'gravável' : 'NÃO gravável';
        echo "✓ {$name} existe e é {$writable}<br>";
    } else {
        echo "✗ {$name} NÃO existe<br>";
    }
}

echo "<hr>";
echo "<p><strong>Teste concluído!</strong> Verifique os resultados acima.</p>";

