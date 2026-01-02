<?php
namespace includes\services;

/**
 * Serviço para integração nativa com o Sicoob (API própria, fora do padrão Open Finance).
 * Implementação enxuta para autenticação mTLS, obtenção de token e leitura de contas/transações.
 * Em produção, ajuste URLs oficiais, scopes e valide certificados/paths conforme ambiente.
 */
class SicoobApiService
{
    private $baseUrls = [
        'sandbox' => 'https://api.sicoob.com.br/sandbox',
        'producao' => 'https://api.sicoob.com.br'
    ];

    /**
    * Autenticação via client_credentials usando mTLS.
    */
    public function obterToken(array $conexao)
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];

        $url = $baseUrl . '/oauth2/token';
        $payload = http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => 'cooperado.extrato-cooperado cooperado.consultas'
        ]);

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode(($conexao['client_id'] ?? '') . ':' . ($conexao['client_secret'] ?? ''))
        ];

        $response = $this->curl($url, 'POST', $headers, $payload, $conexao);
        if (!$response || empty($response['access_token'])) {
            throw new \Exception('Falha ao autenticar na API Sicoob.');
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'] ?? 3600,
            'obtido_em' => date('Y-m-d H:i:s')
        ];
    }

    /**
    * Lista contas disponíveis.
    */
    public function listarContas(array $conexao, $accessToken)
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];
        $url = $baseUrl . '/open-banking/accounts/v1/accounts';

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ];

        $response = $this->curl($url, 'GET', $headers, null, $conexao);
        return $response['data'] ?? [];
    }

    /**
    * Lista transações (extrato) em período.
    */
    public function listarTransacoes(array $conexao, $accessToken, $dataInicio, $dataFim)
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];
        $url = $baseUrl . '/open-banking/accounts/v1/transactions?' . http_build_query([
            'fromDate' => $dataInicio,
            'toDate' => $dataFim
        ]);

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ];

        $response = $this->curl($url, 'GET', $headers, null, $conexao);

        // Normalizar para o formato interno
        $normalizadas = [];
        foreach ($response['data'] ?? [] as $txn) {
            $normalizadas[] = [
                'transacao_id_banco' => $txn['transactionId'] ?? uniqid('sicoob_', false),
                'data_transacao' => $txn['bookingDate'] ?? ($txn['date'] ?? date('Y-m-d')),
                'descricao_original' => $txn['description'] ?? 'Transação Sicoob',
                'valor' => $txn['amount']['amount'] ?? ($txn['amount'] ?? 0),
                'tipo' => ($txn['amount']['amount'] ?? 0) < 0 ? 'saida' : 'entrada',
                'origem' => 'sicoob_nativo'
            ];
        }

        return $normalizadas;
    }

    /**
    * Wrapper cURL com suporte a mTLS.
    */
    private function curl($url, $method, array $headers, $body, array $conexao)
    {
        // Fallback de mock se não houver certificados configurados
        if (empty($conexao['cert_pem']) || empty($conexao['key_pem'])) {
            return $this->mockResponse($url);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // mTLS
        curl_setopt($ch, CURLOPT_SSLCERT, $conexao['cert_pem']);
        curl_setopt($ch, CURLOPT_SSLKEY, $conexao['key_pem']);
        if (!empty($conexao['cert_password'])) {
            curl_setopt($ch, CURLOPT_KEYPASSWD, $conexao['cert_password']);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);
        if ($result === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \Exception('Erro cURL Sicoob: ' . $err);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($result, true);
        if ($status >= 400) {
            $msg = $json['error_description'] ?? $json['message'] ?? 'Erro na API Sicoob';
            throw new \Exception("HTTP {$status}: {$msg}");
        }

        return $json;
    }

    /**
    * Mock básico para ambiente sem certificados configurados.
    */
    private function mockResponse($url)
    {
        if (strpos($url, 'transactions') !== false) {
            return [
                'data' => [
                    [
                        'transactionId' => 'MOCK-SICOOB-001',
                        'bookingDate' => date('Y-m-d', strtotime('-1 day')),
                        'description' => 'Compra em estabelecimento',
                        'amount' => ['amount' => -120.50]
                    ],
                    [
                        'transactionId' => 'MOCK-SICOOB-002',
                        'bookingDate' => date('Y-m-d', strtotime('-3 days')),
                        'description' => 'Recebimento PIX Cliente',
                        'amount' => ['amount' => 850.00]
                    ]
                ]
            ];
        }

        return ['access_token' => 'mock-token', 'expires_in' => 3600];
    }
}
