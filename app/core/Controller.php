<?php
namespace App\Core;

/**
 * Classe base para todos os controllers
 */
abstract class Controller
{
    protected $request;
    protected $response;
    protected $session;
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
     * Inicializa session se não estiver inicializada
     */
    public function __construct()
    {
        // Inicializa sessão
        Session::start();
        
        // Cria wrapper para acessar métodos de Session
        $this->session = new class {
            public function set($key, $value) {
                return Session::set($key, $value);
            }
            public function get($key, $default = null) {
                return Session::get($key, $default);
            }
            public function has($key) {
                return Session::has($key);
            }
            public function remove($key) {
                return Session::remove($key);
            }
            public function delete($key) {
                return Session::delete($key);
            }
            public function clear() {
                return Session::clear();
            }
        };
    }
    
    /**
     * Renderiza uma view (alias para render)
     */
    protected function view($view, $data = [], $layout = 'main')
    {
        return $this->render($view, $data, $layout);
    }
    
    /**
     * Renderiza uma view
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        // Extrai variáveis do array $data
        extract($data);
        
        // Cria helper para views usando ViewHelper
        require_once __DIR__ . '/ViewHelper.php';
        $viewHelper = new ViewHelper($this);
        
        // Armazena em GLOBALS para acesso nas views
        $GLOBALS['__view_helper'] = $viewHelper;
        
        // Cria função helper global baseUrl() para uso direto nas views
        if (!function_exists('baseUrl')) {
            function baseUrl($path = '') {
                if (isset($GLOBALS['__view_helper'])) {
                    return $GLOBALS['__view_helper']->baseUrl($path);
                }
                // Fallback: calcula baseUrl diretamente
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
                $basePath = dirname($scriptName);
                if ($basePath === '/' || $basePath === '\\') {
                    $basePath = '';
                }
                return $basePath . '/' . ltrim($path, '/');
            }
        }
        
        // Cria função helper global session() para uso direto nas views
        if (!function_exists('session')) {
            function session() {
                if (isset($GLOBALS['__view_helper'])) {
                    return $GLOBALS['__view_helper']->session;
                }
                // Fallback: retorna wrapper para Session
                return new class {
                    public function get($key, $default = null) {
                        return \App\Core\Session::get($key, $default);
                    }
                    public function set($key, $value) {
                        return \App\Core\Session::set($key, $value);
                    }
                    public function delete($key) {
                        return \App\Core\Session::delete($key);
                    }
                };
            }
        }
        
        // Extrai session como variável $session para uso direto nas views
        $session = $viewHelper->session;
        extract(['session' => $session], EXTR_OVERWRITE);
        
        // Extrai o helper como $this para as views
        // Usamos uma técnica especial: criamos uma variável $this que será usada nas views
        // Como não podemos reatribuir $this diretamente, vamos usar uma função wrapper
        // que retorna o helper quando $this é acessado
        extract(['viewHelper' => $viewHelper], EXTR_OVERWRITE);
        
        // Cria um wrapper que permite usar $this->baseUrl() nas views
        // Usamos uma técnica de "proxy" através de uma variável global
        // que será acessada via uma função helper
        $GLOBALS['__view_this'] = $viewHelper;
        
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
            // Substitui $this nas views pelo helper através de uma variável extraída
            // Como não podemos reatribuir $this, vamos usar uma técnica de "proxy"
            // Criamos uma função que será chamada quando $this->baseUrl() for usado
            // Mas isso requer modificar as views, então vamos usar uma abordagem diferente:
            // Vamos fazer as views usarem $viewHelper ao invés de $this
            // Mas para manter compatibilidade, vamos criar um wrapper que funciona
            // através de uma variável $this extraída (mas isso não funciona)
            // Solução final: usar função helper global baseUrl() e também disponibilizar
            // o helper como variável $viewHelper para acesso à sessão
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
     * Retorna o objeto de sessão
     */
    protected function getSession()
    {
        return $this->session;
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

