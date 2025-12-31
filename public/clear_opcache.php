<?php
// Simple OPcache reset script. Protect this by deleting after use or restricting access.

// Optional basic secret via query param, set your own secret here.
$secret = ''; // ex: 'minha_senha_segura'
if ($secret !== '' && ($_GET['secret'] ?? '') !== $secret) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Check function availability
if (!function_exists('opcache_reset')) {
    echo "OPcache não está habilitado ou a função não existe.";
    exit;
}

$result = opcache_reset();
if ($result) {
    echo "OPcache limpo com sucesso em " . date('Y-m-d H:i:s');
} else {
    echo "Falha ao limpar OPcache.";
}

// Opcional: limpar cache realpath se disponível
if (function_exists('clearstatcache')) {
    clearstatcache(true);
}
