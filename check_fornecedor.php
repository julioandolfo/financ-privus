<?php
require 'vendor/autoload.php';
$config = require 'config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== ESTRUTURA DA TABELA ===\n";
$stmt = $pdo->query('DESCRIBE fornecedores');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'].' | '.$col['Type'].' | Null:'.$col['Null'].' | Key:'.$col['Key'].' | Default:'.$col['Default']."\n";
}

echo "\n=== TOTAL DE FORNECEDORES ===\n";
$stmt = $pdo->query('SELECT COUNT(*) as total FROM fornecedores');
echo "Total: ".$stmt->fetch(PDO::FETCH_ASSOC)['total']."\n";

echo "\n=== ULTIMOS 5 FORNECEDORES ===\n";
$stmt = $pdo->query('SELECT id, empresa_id, nome_razao_social, cpf_cnpj, ativo, data_cadastro FROM fornecedores ORDER BY id DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) { print_r($row); }

echo "\n=== VERIFICAR INDICES/CONSTRAINTS ===\n";
$stmt = $pdo->query('SHOW INDEX FROM fornecedores');
$indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($indexes as $idx) {
    echo $idx['Key_name'].' | Col:'.$idx['Column_name'].' | Unique:'.($idx['Non_unique'] ? 'No' : 'Yes')."\n";
}
