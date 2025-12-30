<?php
namespace App\Core;

/**
 * Sistema de rotas
 */
class Router
{
    private $routes = [];
    private $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Adiciona uma rota
     */
    public function addRoute($route, $handler, $middleware = [])
    {
        // Formato: "GET /path" ou "POST /path" ou "/path"
        $parts = explode(' ', trim($route), 2);
        
        if (count($parts) === 1) {
            $method = 'GET';
            $path = $parts[0];
        } else {
            $method = strtoupper($parts[0]);
            $path = $parts[1];
        }
        
        // Normaliza o path (remove barra inicial duplicada)
        $path = '/' . ltrim($path, '/');
        
        // Converte {id} para regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Resolve a rota atual
     */
    public function resolve()
    {
        $method = $this->request->getMethod();
        $uri = $this->request->getUri();
        
        // Normaliza URI (garante que começa com /)
        $uri = '/' . ltrim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove primeiro match (rota completa)
                
                // Parse handler (Controller@method)
                $handler = $route['handler'];
                if (is_string($handler) && strpos($handler, '@') !== false) {
                    list($controller, $method) = explode('@', $handler);
                    $controller = "App\\Controllers\\{$controller}";
                } else {
                    $controller = $handler[0];
                    $method = $handler[1];
                }
                
                // Extrai parâmetros nomeados da rota
                preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['path'], $paramNames);
                $params = [];
                foreach ($paramNames[1] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }
                
                return [
                    'controller' => $controller,
                    'method' => $method,
                    'params' => array_values($params),
                    'middleware' => $route['middleware'] ?? []
                ];
            }
        }
        
        return null;
    }
}

