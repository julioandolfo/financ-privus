<?php
namespace App\Core;

use Exception;

/**
 * Classe principal da aplicação MVC
 */
class App
{
    private $router;
    private $request;
    private $response;
    
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request);
    }
    
    /**
     * Inicializa a aplicação
     */
    public function run()
    {
        try {
            // Carrega rotas
            $this->loadRoutes();
            
            // Resolve rota
            $route = $this->router->resolve();
            
            if (!$route) {
                $this->response->setStatusCode(404);
                $uri = $this->request->getUri();
                $method = $this->request->getMethod();
                
                if (APP_DEBUG) {
                    $this->response->send("
                        <h1>404 - Página não encontrada</h1>
                        <p><strong>URI:</strong> {$uri}</p>
                        <p><strong>Método:</strong> {$method}</p>
                        <p><strong>Script:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>
                        <p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>
                    ");
                } else {
                    $this->response->send('Página não encontrada');
                }
                return;
            }
            
            // Executa middlewares
            if (isset($route['middleware']) && !empty($route['middleware'])) {
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = "App\\Middleware\\{$middleware}";
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        $result = $middlewareInstance->handle($this->request, function($req) {
                            return true;
                        });
                        
                        if ($result !== true) {
                            return;
                        }
                    }
                }
            }
            
            // Executa controller
            $controller = $route['controller'];
            $method = $route['method'];
            $params = $route['params'] ?? [];
            
            // Verifica se a classe existe, senão tenta carregar
            if (!class_exists($controller)) {
                // Tenta carregar o controller explicitamente
                $controllerFile = str_replace('App\\Controllers\\', '', $controller);
                $controllerPath = dirname(__DIR__) . '/controllers/' . $controllerFile . '.php';
                
                if (file_exists($controllerPath)) {
                    require_once $controllerPath;
                }
                
                // Verifica novamente após tentar carregar
                if (!class_exists($controller)) {
                    throw new Exception("Controller {$controller} não encontrado. Tentou carregar: {$controllerPath}");
                }
            }
            
            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                throw new Exception("Método {$method} não encontrado no controller {$controller}");
            }
            
            // Injeta Request e Response no controller
            $controllerInstance->setRequest($this->request);
            $controllerInstance->setResponse($this->response);
            
            // Prepara parâmetros para o método
            $reflection = new \ReflectionMethod($controllerInstance, $method);
            $methodParams = $reflection->getParameters();
            
            $callParams = [];
            if (count($methodParams) > 0) {
                // Se o primeiro parâmetro é Request, adiciona
                $firstParamType = $methodParams[0]->getType();
                if ($firstParamType && $firstParamType->getName() === 'App\\Core\\Request') {
                    $callParams[] = $this->request;
                    // Se o segundo é Response, adiciona
                    if (count($methodParams) > 1) {
                        $secondParamType = $methodParams[1]->getType();
                        if ($secondParamType && $secondParamType->getName() === 'App\\Core\\Response') {
                            $callParams[] = $this->response;
                            // Adiciona parâmetros da rota
                            $callParams = array_merge($callParams, $params);
                        } else {
                            // Adiciona parâmetros da rota
                            $callParams = array_merge($callParams, $params);
                        }
                    } else {
                        // Adiciona parâmetros da rota
                        $callParams = array_merge($callParams, $params);
                    }
                } else {
                    // Método não espera Request/Response, passa apenas parâmetros da rota
                    $callParams = $params;
                }
            } else {
                // Método não tem parâmetros
                $callParams = [];
            }
            
            // Chama método do controller
            call_user_func_array([$controllerInstance, $method], $callParams);
            
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Carrega rotas do arquivo de configuração
     */
    private function loadRoutes()
    {
        $routes = require __DIR__ . '/../../config/routes.php';
        
        foreach ($routes as $route => $config) {
            // Suporta formato antigo (string) e novo (array)
            if (is_string($config)) {
                $handler = $config;
                $middleware = [];
            } else {
                $handler = $config['handler'] ?? '';
                $middleware = $config['middleware'] ?? [];
            }
            
            $this->router->addRoute($route, $handler, $middleware);
        }
    }
    
    /**
     * Trata exceções
     */
    private function handleException(Throwable $e)
    {
        // Garante que o diretório de logs existe
        $logDir = dirname(__DIR__) . '/../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // Log do erro
        $errorLog = $logDir . '/error.log';
        $errorMessage = date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n";
        $errorMessage .= "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
        $errorMessage .= "URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        $errorMessage .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
        @file_put_contents($errorLog, $errorMessage, FILE_APPEND);
        
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Erro</title>";
            echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:#fff;padding:15px;border:1px solid #ddd;border-radius:5px;overflow:auto;}</style>";
            echo "</head><body><h1 style='color:#d32f2f;'>Erro na Aplicação</h1>";
            echo "<pre>";
            echo "<strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
            echo "<strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "\n";
            echo "<strong>Linha:</strong> " . $e->getLine() . "\n\n";
            echo "<strong>URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
            echo "<strong>Método:</strong> " . htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n\n";
            echo "<strong>Stack Trace:</strong>\n";
            echo htmlspecialchars($e->getTraceAsString());
            echo "</pre></body></html>";
        } else {
            $this->response->setStatusCode(500);
            $this->response->send('Erro interno do servidor');
        }
        
        // Log também no error_log do PHP
        error_log($e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    }
}

