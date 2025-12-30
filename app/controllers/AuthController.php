<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Usuario;
use App\Models\Empresa;

/**
 * Controller para autenticação
 */
class AuthController extends Controller
{
    protected $usuario;
    protected $empresa;
    
    public function __construct()
    {
        parent::__construct();
        $this->usuario = new Usuario();
        $this->empresa = new Empresa();
    }
    
    /**
     * Exibe formulário de login
     */
    public function loginForm(Request $request, Response $response)
    {
        // Se já estiver logado, redireciona para home
        if (isset($_SESSION['usuario_id'])) {
            $response->redirect('/');
            return;
        }
        
        return $this->render('auth/login', [
            'title' => 'Login'
        ], 'auth');
    }
    
    /**
     * Processa login
     */
    public function login(Request $request, Response $response)
    {
        $email = $request->post('email');
        $senha = $request->post('senha');
        
        // Validação básica
        if (empty($email) || empty($senha)) {
            return $this->render('auth/login', [
                'title' => 'Login',
                'error' => 'Email e senha são obrigatórios',
                'email' => $email
            ], 'auth');
        }
        
        // Autentica usuário
        $usuario = $this->usuario->authenticate($email, $senha);
        
        if (!$usuario) {
            return $this->render('auth/login', [
                'title' => 'Login',
                'error' => 'Email ou senha inválidos',
                'email' => $email
            ], 'auth');
        }
        
        // Cria sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_avatar'] = $usuario['avatar'] ?? null;
        $_SESSION['usuario_empresa_id'] = $usuario['empresa_id'];
        
        // Carrega empresas consolidadas padrão se existir
        if ($usuario['empresas_consolidadas_padrao']) {
            $_SESSION['empresas_consolidadas'] = json_decode($usuario['empresas_consolidadas_padrao'], true);
        }
        
        // Redireciona para página que tentou acessar ou home
        $redirect = $_SESSION['redirect_after_login'] ?? '/';
        unset($_SESSION['redirect_after_login']);
        
        $_SESSION['success'] = 'Login realizado com sucesso!';
        $response->redirect($redirect);
    }
    
    /**
     * Logout
     */
    public function logout(Request $request, Response $response)
    {
        // Destrói sessão
        session_destroy();
        
        // Inicia nova sessão
        session_start();
        
        $_SESSION['success'] = 'Logout realizado com sucesso!';
        $response->redirect('/login');
    }
}

