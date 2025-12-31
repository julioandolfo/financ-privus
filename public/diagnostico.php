<?php
/**
 * Script de Diagnóstico
 * Verifica o conteúdo real do arquivo carregado pelo PHP
 */

// Impede execução em produção sem autenticação
$secret = 'financeiro2024'; // Altere para sua senha
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    die('Acesso negado');
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4}";
echo "pre{background:#2d2d2d;padding:15px;border-radius:5px;overflow-x:auto}";
echo ".ok{color:#4ec9b0}.error{color:#f48771}.warning{color:#ce9178}</style></head><body>";

echo "<h1>🔍 Diagnóstico do Sistema</h1>";

// 1. Verifica OPcache
echo "<h2>1. Status do OPcache</h2>";
if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    if ($opcache) {
        echo "<pre class='ok'>✓ OPcache está ATIVO</pre>";
        echo "<pre>Versão: " . phpversion('Zend OPcache') . "</pre>";
        echo "<pre>Scripts em cache: " . $opcache['opcache_statistics']['num_cached_scripts'] . "</pre>";
        echo "<pre>Hits: " . $opcache['opcache_statistics']['hits'] . "</pre>";
        echo "<pre>Misses: " . $opcache['opcache_statistics']['misses'] . "</pre>";
        echo "<pre>Memória usada: " . round($opcache['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB</pre>";
    } else {
        echo "<pre class='warning'>⚠ OPcache está DESABILITADO</pre>";
    }
} else {
    echo "<pre class='warning'>⚠ OPcache não está disponível</pre>";
}

// 2. Verifica arquivo CategoriaProdutoController
echo "<h2>2. Conteúdo do CategoriaProdutoController</h2>";
$file = __DIR__ . '/../app/controllers/CategoriaProdutoController.php';

if (file_exists($file)) {
    echo "<pre class='ok'>✓ Arquivo existe: $file</pre>";
    
    // Mostra data de modificação
    $mtime = filemtime($file);
    echo "<pre>Última modificação: " . date('Y-m-d H:i:s', $mtime) . "</pre>";
    
    // Lê e mostra o método index
    $content = file_get_contents($file);
    
    // Extrai o método index
    preg_match('/public function index\(.*?\n(.*?)\n    \}/s', $content, $matches);
    
    if ($matches) {
        echo "<h3>Método index():</h3>";
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
        
        // Verifica se tem o bug
        if (strpos($matches[0], "render(\$view,") !== false || strpos($matches[0], "render('tree',") !== false) {
            echo "<pre class='error'>❌ ERRO: Arquivo contém código ANTIGO com render(\$view,...)</pre>";
        } elseif (strpos($matches[0], "render('categorias_produtos/index',") !== false) {
            echo "<pre class='ok'>✓ CORRETO: Arquivo contém código correto com render('categorias_produtos/index',...)</pre>";
        } else {
            echo "<pre class='warning'>⚠ AVISO: Não foi possível determinar o estado do código</pre>";
        }
    }
} else {
    echo "<pre class='error'>❌ Arquivo NÃO existe: $file</pre>";
}

// 3. Limpa OPcache
echo "<h2>3. Limpeza do OPcache</h2>";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<pre class='ok'>✓ OPcache limpo com sucesso!</pre>";
    } else {
        echo "<pre class='error'>❌ Falha ao limpar OPcache</pre>";
    }
} else {
    echo "<pre class='warning'>⚠ Função opcache_reset() não disponível</pre>";
}

// 4. Verifica view
echo "<h2>4. View categorias_produtos/index.php</h2>";
$viewFile = __DIR__ . '/../app/views/categorias_produtos/index.php';
if (file_exists($viewFile)) {
    echo "<pre class='ok'>✓ View existe: $viewFile</pre>";
    echo "<pre>Última modificação: " . date('Y-m-d H:i:s', filemtime($viewFile)) . "</pre>";
} else {
    echo "<pre class='error'>❌ View NÃO existe: $viewFile</pre>";
}

// 5. Info PHP
echo "<h2>5. Informações do PHP</h2>";
echo "<pre>Versão PHP: " . PHP_VERSION . "</pre>";
echo "<pre>SAPI: " . php_sapi_name() . "</pre>";
echo "<pre>Servidor: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido' . "</pre>";

echo "<hr>";
echo "<p><strong>Próximos passos se o erro persistir:</strong></p>";
echo "<ol>";
echo "<li>Se o arquivo está CORRETO mas o erro persiste: <strong>reinicie o PHP-FPM/Apache</strong></li>";
echo "<li>Comando SSH: <code>sudo systemctl restart php-fpm</code> ou <code>sudo systemctl restart apache2</code></li>";
echo "<li>Se nada funcionar, substitua manualmente o arquivo via FTP</li>";
echo "</ol>";

echo "</body></html>";
