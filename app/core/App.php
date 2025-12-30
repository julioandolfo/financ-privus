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
            
            if (!class_exists($controller)) {
                throw new Exception("Controller {$controller} não encontrado");
            }
            
            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                throw new Exception("Método {$method} não encontrado no controller {$controller}");
            }
            
            // Injeta Request e Response no controller
            $controllerInstance->setRequest($this->request);
            $controllerInstance->setResponse($this->response);
            
            // Chama método do controller
            call_user_func_array([$controllerInstance, $method], $params);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Carrega rotas do arquivo de configuração
     */
    private function loadRoutes()
    {
        $routes = require __DIR__ . '/../../config/routes.php';
        
        foreach ($routes as $route => $handler) {
            $this->router->addRoute($route, $handler);
        }
    }
    
    /**
     * Trata exceções
     */
    private function handleException(Exception $e)
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<pre>";
            echo "Erro: " . $e->getMessage() . "\n";
            echo "Arquivo: " . $e->getFile() . "\n";
            echo "Linha: " . $e->getLine() . "\n";
            echo $e->getTraceAsString();
            echo "</pre>";
        } else {
            $this->response->setStatusCode(500);
            $this->response->send('Erro interno do servidor');
        }
        
        // Log do erro
        error_log($e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    }
}

