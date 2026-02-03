<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de autenticação
 * Verifica se o usuário está logado
 */
class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado
     */
    public function handle(Request $request, Response $response)
    {
        // Verifica se há usuário na sessão
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Se for requisição AJAX, retorna JSON
            if ($request->isAjax()) {
                $response->json([
                    'error' => true,
                    'message' => 'Não autenticado',
                    'redirect' => '/login'
                ], 401);
                return false;
            }
            
            // Salva URL para redirecionar após login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Redireciona para login
            $response->redirect('/login');
            return false;
        }
        
        // Usuário autenticado, continua
        return true;
    }
}

