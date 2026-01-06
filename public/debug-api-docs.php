<?php
/**
 * Arquivo temporário para debug da documentação da API
 */

// Forçar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Debug da Documentação da API</h1>";
echo "<pre>";

// Define constantes
define('APP_ROOT', dirname(__DIR__));

echo "✅ APP_ROOT: " . APP_ROOT . "\n\n";

// Verificar se o controller existe
$controllerPath = APP_ROOT . '/app/controllers/ApiDocController.php';
echo "Verificando controller...\n";
echo "Caminho: {$controllerPath}\n";
echo "Existe: " . (file_exists($controllerPath) ? '✅ SIM' : '❌ NÃO') . "\n\n";

// Verificar se a view existe
$viewPath = APP_ROOT . '/app/views/api_docs/index.php';
echo "Verificando view...\n";
echo "Caminho: {$viewPath}\n";
echo "Existe: " . (file_exists($viewPath) ? '✅ SIM' : '❌ NÃO') . "\n\n";

// Carregar variáveis de ambiente
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

echo "✅ EnvLoader carregado\n\n";

// Inicia sessão
require_once APP_ROOT . '/app/core/Session.php';
use App\Core\Session;
Session::start();

echo "✅ Sessão iniciada\n\n";

// Autoloader
spl_autoload_register(function ($class) {
    echo "Autoloader tentando carregar: {$class}\n";
    
    if (strpos($class, 'App\\Controllers\\') === 0) {
        $relativeClass = str_replace('App\\Controllers\\', '', $class);
        $file = APP_ROOT . '/app/controllers/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "  ✅ Carregado: {$file}\n";
            return;
        }
        echo "  ❌ Não encontrado: {$file}\n";
    }
    
    if (strpos($class, 'App\\Models\\') === 0) {
        $relativeClass = str_replace('App\\Models\\', '', $class);
        $file = APP_ROOT . '/app/models/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "  ✅ Carregado: {$file}\n";
            return;
        }
        echo "  ❌ Não encontrado: {$file}\n";
    }
    
    if (strpos($class, 'App\\Core\\') === 0) {
        $relativeClass = str_replace('App\\Core\\', '', $class);
        $file = APP_ROOT . '/app/core/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "  ✅ Carregado: {$file}\n";
            return;
        }
        echo "  ❌ Não encontrado: {$file}\n";
    }
    
    if (strpos($class, 'includes\\') === 0 || strpos($class, 'Includes\\') === 0) {
        $relativeClass = str_replace(['includes\\', 'Includes\\'], '', $class);
        $file = APP_ROOT . '/includes/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "  ✅ Carregado: {$file}\n";
            return;
        }
        echo "  ❌ Não encontrado: {$file}\n";
    }
});

// Carrega configurações
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

echo "✅ Configurações carregadas\n\n";

// Carrega classes core
$requiredFiles = [
    APP_ROOT . '/app/core/Request.php',
    APP_ROOT . '/app/core/Response.php',
    APP_ROOT . '/app/core/Database.php',
    APP_ROOT . '/app/core/Model.php',
    APP_ROOT . '/app/core/Controller.php',
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo "❌ Arquivo não encontrado: {$file}\n";
    } else {
        require_once $file;
        echo "✅ Carregado: " . basename($file) . "\n";
    }
}

echo "\n";

// Tentar instanciar o controller
try {
    echo "Tentando instanciar ApiDocController...\n";
    
    use App\Controllers\ApiDocController;
    use App\Core\Request;
    use App\Core\Response;
    
    $controller = new ApiDocController();
    echo "✅ Controller instanciado com sucesso!\n\n";
    
    // Criar request e response fake
    $request = new Request();
    $response = new Response();
    
    echo "Tentando executar index()...\n";
    $result = $controller->index($request, $response);
    
    echo "✅ Método executado com sucesso!\n";
    echo "Tipo de retorno: " . gettype($result) . "\n";
    
} catch (\Throwable $e) {
    echo "❌ ERRO:\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='/api/docs'>Tentar acessar /api/docs normalmente</a></p>";
echo "<p><a href='/'>Voltar ao sistema</a></p>";
