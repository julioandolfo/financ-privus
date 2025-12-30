<?php
namespace App\Core;

/**
 * Classe base para todos os controllers
 */
abstract class Controller
{
    protected $request;
    protected $response;
    protected $viewPath;
    
    /**
     * Retorna o caminho base dos assets
     */
    protected function asset($path)
    {
        // Calcula o caminho base relativo ao public
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = dirname($scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        return $basePath . '/assets/' . ltrim($path, '/');
    }
    
    /**
     * Retorna o caminho base da aplicação
     */
    protected function baseUrl($path = '')
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = dirname($scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        return $basePath . '/' . ltrim($path, '/');
    }
    
    /**
     * Define o Request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Define o Response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
    
    /**
     * Renderiza uma view
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        // Extrai variáveis do array $data
        extract($data);
        
        // Define caminho da view
        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        // Define caminho do layout
        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        
        if (file_exists($layoutFile)) {
            // Renderiza view dentro do layout
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            
            // Define $title se não estiver definido
            if (!isset($title)) {
                $title = 'Sistema Financeiro';
            }
            
            // Renderiza o layout com o conteúdo
            include $layoutFile;
        } else {
            // Renderiza apenas a view
            include $viewFile;
        }
        
        // Encerra a execução após renderizar
        exit;
    }
    
    /**
     * Retorna resposta JSON
     */
    protected function json($data, $statusCode = 200)
    {
        $this->response->json($data, $statusCode);
    }
    
    /**
     * Redireciona para uma URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        $this->response->redirect($url, $statusCode);
    }
    
    /**
     * Retorna dados validados
     */
    protected function validate($rules)
    {
        // TODO: Implementar validação
        return $this->request->all();
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['usuario_id']);
    }
    
    /**
     * Retorna ID do usuário autenticado
     */
    protected function getUserId()
    {
        return $_SESSION['usuario_id'] ?? null;
    }
    
    /**
     * Retorna ID da empresa ativa na sessão
     */
    protected function getEmpresaId()
    {
        return $_SESSION['empresa_id'] ?? null;
    }
}

