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
    public function handle(Request $request, $next)
    {
        // Verifica se há usuário na sessão
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Se for requisição AJAX, retorna JSON
            if ($request->isAjax()) {
                $response = new Response();
                $response->json([
                    'error' => true,
                    'message' => 'Não autenticado',
                    'redirect' => '/login'
                ], 401);
                return false;
            }
            
            // Redireciona para login
            $response = new Response();
            $response->redirect('/login');
            return false;
        }
        
        // Usuário autenticado, continua
        return $next($request);
    }
}

