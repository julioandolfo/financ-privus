<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\ApiToken;
use App\Models\ApiLog;

class ApiAuthMiddleware
{
    private $apiTokenModel;
    private $apiLogModel;
    private $startTime;

    public function __construct()
    {
        $this->apiTokenModel = new ApiToken();
        $this->apiLogModel = new ApiLog();
        $this->startTime = microtime(true);
    }

    public function handle(Request $request, Response $response)
    {
        // Extrair token do header Authorization
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->unauthorizedResponse($response, 'Token não fornecido', $request);
        }

        // Validar token
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $validation = $this->apiTokenModel->isValid($token, $clientIp);
        
        if (!$validation['valid']) {
            return $this->unauthorizedResponse($response, $validation['message'], $request, $token);
        }

        // Atualizar último uso
        $this->apiTokenModel->updateLastUsed($validation['token']['id']);
        
        // Armazenar informações do token na requisição
        $request->apiToken = $validation['token'];
        
        // Continuar para o controller
        return true;
    }

    /**
     * Extrai o token do header Authorization
     */
    private function extractToken(Request $request)
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            // Formato: "Bearer TOKEN"
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
            // Formato direto: "TOKEN"
            return $headers['Authorization'];
        }
        
        // Também aceita token via query string (menos seguro, mas útil para testes)
        if (isset($_GET['api_token'])) {
            return $_GET['api_token'];
        }
        
        return null;
    }

    /**
     * Retorna resposta de não autorizado e registra no log
     */
    private function unauthorizedResponse(Response $response, $message, Request $request, $token = null)
    {
        $statusCode = 401;
        $responseData = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Registrar no log
        $this->logRequest($request, $statusCode, $responseData, $token);
        
        $response->json($responseData, $statusCode);
        return false;
    }

    /**
     * Registra a requisição no log
     */
    public function logRequest(Request $request, $statusCode, $responseData, $token = null)
    {
        $endpoint = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Calcular tempo de resposta
        $endTime = microtime(true);
        $responseTime = $endTime - $this->startTime;
        
        // Buscar token_id se o token for válido
        $tokenId = null;
        if ($token && is_string($token)) {
            $tokenData = $this->apiTokenModel->findByToken($token);
            $tokenId = $tokenData['id'] ?? null;
        } elseif ($token && is_array($token)) {
            $tokenId = $token['id'] ?? null;
        }
        
        // Preparar dados do body
        $body = null;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $body = file_get_contents('php://input');
            // Limitar tamanho do body no log
            if (strlen($body) > 10000) {
                $body = substr($body, 0, 10000) . '... (truncado)';
            }
        }
        
        // Preparar parâmetros
        $parametros = json_encode($_GET ?: null);
        
        // Preparar resposta
        $resposta = is_array($responseData) ? json_encode($responseData) : $responseData;
        if (strlen($resposta) > 10000) {
            $resposta = substr($resposta, 0, 10000) . '... (truncado)';
        }
        
        $this->apiLogModel->create([
            'api_token_id' => $tokenId,
            'metodo' => $method,
            'endpoint' => $endpoint,
            'parametros' => $parametros,
            'body' => $body,
            'status_code' => $statusCode,
            'resposta' => $resposta,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'tempo_resposta' => round($responseTime, 4)
        ]);
    }
}
