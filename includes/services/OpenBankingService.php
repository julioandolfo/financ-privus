<?php
namespace Includes\Services;

/**
 * Serviço para integração com Open Banking Brasil
 * 
 * NOTA: Esta é uma implementação simplificada para demonstração.
 * Em produção, requer:
 * - Certificado Digital A1 ou A3
 * - Registro no Diretório Central do Open Banking
 * - Implementação completa de OAuth 2.0 / mTLS
 */
class OpenBankingService
{
    private $conexao;
    private $bancoConfig = [
        'sicredi' => [
            'auth_url' => 'https://auth.openbanking.sicredi.com.br',
            'api_url' => 'https://api.openbanking.sicredi.com.br',
            'docs' => 'https://developers.sicredi.com.br'
        ],
        'sicoob' => [
            'auth_url' => 'https://auth.openbanking.sicoob.com.br',
            'api_url' => 'https://api.openbanking.sicoob.com.br',
            'docs' => 'https://developers.sicoob.com.br'
        ],
        'bradesco' => [
            'auth_url' => 'https://proxy.api.prebanco.com.br/auth',
            'api_url' => 'https://proxy.api.prebanco.com.br',
            'docs' => 'https://developers.bradesco.com.br'
        ],
        'itau' => [
            'auth_url' => 'https://secure.api.itau.com.br/auth',
            'api_url' => 'https://secure.api.itau.com.br',
            'docs' => 'https://developer.itau.com.br'
        ]
    ];
    
    public function __construct($conexao = null)
    {
        $this->conexao = $conexao;
    }
    
    /**
     * Iniciar fluxo de autenticação OAuth 2.0
     * Retorna URL para redirecionar o usuário
     */
    public function iniciarAutenticacao($banco, $tipo, $redirectUri)
    {
        if (!isset($this->bancoConfig[$banco])) {
            throw new \Exception("Banco não suportado: {$banco}");
        }
        
        $config = $this->bancoConfig[$banco];
        $state = bin2hex(random_bytes(16)); // CSRF protection
        
        // Armazenar state na sessão para validação posterior
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_banco'] = $banco;
        $_SESSION['oauth_tipo'] = $tipo;
        
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => getenv('OPENBANKING_CLIENT_ID'), // Configurar no .env
            'redirect_uri' => $redirectUri,
            'scope' => $this->getScopes($tipo),
            'state' => $state
        ]);
        
        return $config['auth_url'] . '/authorize?' . $params;
    }
    
    /**
     * Processar callback de autenticação
     */
    public function processarCallback($code, $state)
    {
        // Validar state (CSRF)
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            throw new \Exception("State inválido");
        }
        
        $banco = $_SESSION['oauth_banco'];
        $config = $this->bancoConfig[$banco];
        
        // Trocar código por access_token
        // NOTA: Em produção, usar certificado mTLS aqui
        $response = $this->chamarAPI($config['auth_url'] . '/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => getenv('APP_URL') . '/conexoes-bancarias/callback'
        ], 'POST');
        
        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_in' => $response['expires_in'],
            'consent_id' => $response['consent_id'] ?? null
        ];
    }
    
    /**
     * Sincronizar extratos (últimos 30 dias)
     */
    public function sincronizarExtratos()
    {
        if (!$this->conexao) {
            throw new \Exception("Conexão não definida");
        }
        
        $banco = $this->conexao['banco'];
        $config = $this->bancoConfig[$banco];
        
        $dataInicio = date('Y-m-d', strtotime('-30 days'));
        $dataFim = date('Y-m-d');
        
        // NOTA: Em produção, usar access_token descriptografado
        $accessToken = $this->conexao['access_token'];
        
        $endpoint = $config['api_url'] . '/accounts/v1/transactions';
        $params = [
            'fromDate' => $dataInicio,
            'toDate' => $dataFim
        ];
        
        $transacoes = $this->chamarAPI($endpoint, $params, 'GET', $accessToken);
        
        return $transacoes['data'] ?? [];
    }
    
    /**
     * Sincronizar fatura de cartão de crédito
     */
    public function sincronizarCartao()
    {
        if (!$this->conexao) {
            throw new \Exception("Conexão não definida");
        }
        
        $banco = $this->conexao['banco'];
        $config = $this->bancoConfig[$banco];
        
        $accessToken = $this->conexao['access_token'];
        
        $endpoint = $config['api_url'] . '/credit-cards/v1/bills';
        
        $faturas = $this->chamarAPI($endpoint, [], 'GET', $accessToken);
        
        return $faturas['data'] ?? [];
    }
    
    /**
     * Renovar access token usando refresh token
     */
    public function renovarToken()
    {
        if (!$this->conexao) {
            throw new \Exception("Conexão não definida");
        }
        
        $banco = $this->conexao['banco'];
        $config = $this->bancoConfig[$banco];
        
        $response = $this->chamarAPI($config['auth_url'] . '/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->conexao['refresh_token']
        ], 'POST');
        
        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_in' => $response['expires_in']
        ];
    }
    
    /**
     * Chamar API (mock para desenvolvimento)
     */
    private function chamarAPI($url, $params, $method = 'GET', $accessToken = null)
    {
        // MOCK: Retornar dados fictícios para desenvolvimento
        // Em produção, implementar chamada real via cURL com mTLS
        
        if (strpos($url, '/transactions') !== false) {
            // Mock de transações
            return [
                'data' => [
                    [
                        'transactionId' => 'TXN001',
                        'date' => date('Y-m-d', strtotime('-2 days')),
                        'amount' => -150.00,
                        'type' => 'DEBITO',
                        'description' => 'Mercado Extra - Compra'
                    ],
                    [
                        'transactionId' => 'TXN002',
                        'date' => date('Y-m-d', strtotime('-5 days')),
                        'amount' => -2500.00,
                        'type' => 'TED',
                        'description' => 'TED - Fornecedor XYZ LTDA'
                    ]
                ]
            ];
        }
        
        return [];
    }
    
    /**
     * Obter scopes necessários baseado no tipo de conexão
     */
    private function getScopes($tipo)
    {
        $scopes = ['openid'];
        
        switch ($tipo) {
            case 'conta_corrente':
            case 'conta_poupanca':
                $scopes[] = 'accounts';
                $scopes[] = 'transactions';
                break;
            case 'cartao_credito':
                $scopes[] = 'credit-cards-accounts';
                break;
        }
        
        return implode(' ', $scopes);
    }
    
    /**
     * Obter informações do banco
     */
    public function getBancoConfig($banco)
    {
        return $this->bancoConfig[$banco] ?? null;
    }
}
