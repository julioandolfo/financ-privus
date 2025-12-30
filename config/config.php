<?php
/**
 * Configurações gerais do sistema
 */

return [
    'app_name' => 'Sistema Financeiro Empresarial',
    'app_version' => '1.0.0',
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    'charset' => 'UTF-8',
    
    // URLs
    'base_url' => $_ENV['BASE_URL'] ?? 'https://financeiro.privus.com.br',
    'asset_url' => $_ENV['ASSET_URL'] ?? 'https://financeiro.privus.com.br/assets',
    
    // Sessão
    'session_name' => 'financeiro_session',
    'session_lifetime' => 7200, // 2 horas
    
    // Segurança
    'csrf_token_name' => 'csrf_token',
    'password_min_length' => 8,
    
    // Paginação
    'per_page' => 20,
    
    // Uploads
    'upload_path' => __DIR__ . '/../storage/uploads',
    'upload_max_size' => 5242880, // 5MB
    
    // Logs
    'log_path' => __DIR__ . '/../storage/logs',
    'log_level' => 'debug', // debug, info, warning, error
];

