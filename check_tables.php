<?php
require_once __DIR__ . '/includes/EnvLoader.php';
EnvLoader::load();
require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

echo "Tabelas criadas no banco de dados:\n";
foreach ($tables as $table) {
    echo "  - {$table}\n";
}

echo "\nTotal: " . count($tables) . " tabela(s)\n";

