<?php
/**
 * Script de teste de conexão com banco de dados
 * Acesse: https://financeiro.privus.com.br/test-db.php
 * REMOVA ESTE ARQUIVO APÓS O TESTE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Conexão - Banco de Dados</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f4f4f4;padding:10px;}</style>";

// Carrega o .env
$envFile = dirname(__DIR__) . '/.env';
echo "<h2>1. Verificando arquivo .env</h2>";
if (file_exists($envFile)) {
    echo "<p class='success'>✓ Arquivo .env existe</p>";
    
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            list($key, $value) = $parts;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    
    echo "<p>Variáveis carregadas do .env:</p>";
    echo "<pre>";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'não definido') . "\n";
    echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'não definido') . "\n";
    echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'não definido') . "\n";
    echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? str_repeat('*', strlen($_ENV['DB_PASS'])) : 'não definido') . "\n";
    echo "</pre>";
} else {
    echo "<p class='error'>✗ Arquivo .env NÃO encontrado em: $envFile</p>";
    die();
}

// Teste de conexão PDO
echo "<h2>2. Testando Conexão PDO</h2>";

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    echo "<p>Tentando conectar com:</p>";
    echo "<pre>";
    echo "DSN: $dsn\n";
    echo "User: $user\n";
    echo "Pass: " . str_repeat('*', strlen($pass)) . "\n";
    echo "</pre>";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<p class='success'>✓ Conexão PDO estabelecida com sucesso!</p>";
    
    // Testa uma query
    echo "<h2>3. Testando Query</h2>";
    $stmt = $pdo->query("SELECT DATABASE() as db, USER() as user, VERSION() as version");
    $result = $stmt->fetch();
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    // Lista tabelas
    echo "<h2>4. Listando Tabelas</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p class='error'>⚠ Nenhuma tabela encontrada! Execute as migrations.</p>";
    } else {
        echo "<p class='success'>✓ Tabelas encontradas:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<p class='success'><strong>✓ TUDO OK! A conexão está funcionando.</strong></p>";
    echo "<p style='color:orange;'><strong>⚠ IMPORTANTE: Delete este arquivo (test-db.php) após o teste!</strong></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro ao conectar:</p>";
    echo "<pre class='error'>";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "</pre>";
}

