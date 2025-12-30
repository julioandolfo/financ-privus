<?php
/**
 * Configurações de conexão com banco de dados
 */

return [
    'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'),
    'port' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306'),
    'database' => getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'financeiro'),
    'username' => getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root'),
    'password' => getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? ''),
    'charset' => 'utf8mb4',
    'collate' => 'utf8mb4_unicode_ci'
];

