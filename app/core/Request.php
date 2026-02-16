<?php
namespace App\Core;

/**
 * Classe para manipulação de requisições HTTP
 */
class Request
{
    private $method;
    private $uri;
    private $headers;
    private $body;
    private $query;
    private $post;
    private $files;
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->headers = $this->getAllHeaders();
        $this->query = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->body = file_get_contents('php://input');
    }
    
    /**
     * Retorna o método HTTP
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Retorna a URI
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Retorna todos os headers
     */
    public function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Retorna um header específico
     */
    public function getHeader($name)
    {
        return $this->headers[$name] ?? null;
    }
    
    /**
     * Retorna todos os dados GET
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Retorna todos os dados POST
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        
        return $this->post[$key] ?? $default;
    }
    
    /**
     * Retorna todos os dados da requisição (GET + POST)
     */
    public function all()
    {
        return array_merge($this->query, $this->post);
    }
    
    /**
     * Retorna um arquivo enviado
     */
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Verifica se é uma requisição AJAX
     */
    public function isAjax()
    {
        return !empty($this->getHeader('X-Requested-With')) && 
               strtolower($this->getHeader('X-Requested-With')) === 'xmlhttprequest';
    }
    
    /**
     * Verifica se é uma requisição JSON
     */
    public function isJson()
    {
        return strpos($this->getHeader('Content-Type') ?? '', 'application/json') !== false;
    }
    
    /**
     * Retorna o body bruto (raw)
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Retorna o body como JSON
     */
    public function json()
    {
        $decoded = json_decode($this->body, true);
        return is_array($decoded) ? $decoded : null;
    }
    
    /**
     * Retorna o IP do cliente
     */
    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
    
    /**
     * Parse da URI removendo query string e base path
     */
    private function parseUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove /public do início da URI se existir
        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, 7); // Remove '/public'
        }
        
        // Remove o diretório base do script da URI
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        
        // Se o script está em /public, remove esse caminho da URI
        if ($scriptDir !== '/' && $scriptDir !== '.' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }
        
        // Garante que começa com / e remove barras duplicadas
        $uri = '/' . ltrim($uri, '/');
        $uri = preg_replace('#/+#', '/', $uri); // Remove barras duplicadas
        
        return $uri;
    }
}
