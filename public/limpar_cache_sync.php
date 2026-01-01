<?php
/**
 * Script para limpar cache do OPCache e verificar sistema
 */

echo "<h1>🔄 Limpeza de Cache - Sincronização Bancária</h1>";

// Limpar OPCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPCache limpo com sucesso!</p>";
} else {
    echo "<p>⚠️ OPCache não está ativo</p>";
}

// Limpar cache de realpath
clearstatcache(true);
echo "<p>✅ Cache de arquivos limpo</p>";

// Verificar arquivos importantes
$arquivos = [
    '../app/controllers/ConexaoBancariaController.php',
    '../app/controllers/TransacaoPendenteController.php',
    '../config/routes.php'
];

echo "<h2>📁 Verificação de Arquivos:</h2>";
echo "<ul>";
foreach ($arquivos as $arquivo) {
    $existe = file_exists(__DIR__ . '/' . $arquivo);
    $status = $existe ? '✅' : '❌';
    $ultimaMod = $existe ? date('d/m/Y H:i:s', filemtime(__DIR__ . '/' . $arquivo)) : 'N/A';
    echo "<li>{$status} {$arquivo} - Última modificação: {$ultimaMod}</li>";
}
echo "</ul>";

// Testar se as classes podem ser carregadas
echo "<h2>🔍 Teste de Classes:</h2>";
echo "<ul>";

try {
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/core/Model.php';
    require_once __DIR__ . '/../app/models/ConexaoBancaria.php';
    echo "<li>✅ Model ConexaoBancaria carregado</li>";
} catch (Exception $e) {
    echo "<li>❌ Erro ao carregar ConexaoBancaria: " . $e->getMessage() . "</li>";
}

try {
    require_once __DIR__ . '/../app/models/TransacaoPendente.php';
    echo "<li>✅ Model TransacaoPendente carregado</li>";
} catch (Exception $e) {
    echo "<li>❌ Erro ao carregar TransacaoPendente: " . $e->getMessage() . "</li>";
}

echo "</ul>";

// Verificar rotas
echo "<h2>🛣️ Rotas de Sincronização Bancária:</h2>";
$routes = require __DIR__ . '/../config/routes.php';
$routasSync = array_filter(array_keys($routes), function($rota) {
    return strpos($rota, 'conexoes-bancarias') !== false || strpos($rota, 'transacoes-pendentes') !== false;
});

echo "<ul>";
foreach ($routasSync as $rota) {
    echo "<li><strong>{$rota}</strong> → {$routes[$rota]['handler']}</li>";
}
echo "</ul>";

echo "<h2>✅ Limpeza Concluída!</h2>";
echo "<p>Tente acessar novamente:</p>";
echo "<ul>";
echo "<li><a href='/conexoes-bancarias' target='_blank'>🏦 Sincronização Bancária</a></li>";
echo "<li><a href='/transacoes-pendentes' target='_blank'>📋 Transações Pendentes</a></li>";
echo "<li><a href='/' target='_blank'>🏠 Dashboard</a></li>";
echo "</ul>";

// Informações do servidor
echo "<hr>";
echo "<h3>ℹ️ Informações do Servidor:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>SAPI:</strong> " . PHP_SAPI . "</li>";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</li>";
echo "<li><strong>OPCache:</strong> " . (function_exists('opcache_get_status') ? 'Ativo' : 'Inativo') . "</li>";
if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    echo "<li><strong>OPCache Scripts:</strong> " . ($opcache['opcache_statistics']['num_cached_scripts'] ?? 0) . "</li>";
}
echo "</ul>";
?>
