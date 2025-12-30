<?php
/**
 * Configurações de conexão com banco de dados
 */

return [
    'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'),
    'port' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306'),
    'database' => getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'financeiro'),
    'username' => getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'),
    'password' => getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''),
    'charset' => 'utf8mb4',
    'collate' => 'utf8mb4_unicode_ci'
];

