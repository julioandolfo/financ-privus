<?php
namespace Includes\Services;

/**
 * Integração com a API do Sicredi.
 * 
 * Autenticação: mTLS (certificado digital) + OAuth 2.0 client_credentials
 * 
 * Documentação: https://developers.sicredi.com.br
 * 
 * Endpoints:
 *   POST /oauth/token                    -> autenticação
 *   GET  /conta-corrente/v1/saldo        -> saldo
 *   GET  /conta-corrente/v1/extrato      -> extrato
 */
class SicrediBankService extends AbstractBankService
{
    private $baseUrls = [
        'sandbox' => 'https://api-sandbox.sicredi.com.br',
        'producao' => 'https://api.sicredi.com.br'
    ];

    private $authUrls = [
        'sandbox' => 'https://api-sandbox.sicredi.com.br/oauth/token',
        'producao' => 'https://api.sicredi.com.br/oauth/token'
    ];

    public function getBancoNome(): string
    {
        return 'sicredi';
    }

    public function getBancoLabel(): string
    {
        return 'Sicredi';
    }

    public function autenticar(array $conexao): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $authUrl = $this->authUrls[$ambiente] ?? $this->authUrls['sandbox'];

        $clientId = $conexao['client_id'] ?? '';
        $clientSecret = $conexao['client_secret'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('Client ID e Client Secret do Sicredi são obrigatórios.');
        }

        $hasCerts = !empty($conexao['cert_pem']) && !empty($conexao['key_pem']);

        if ($ambiente === 'sandbox' && !$hasCerts) {
            return [
                'access_token' => 'sandbox-mock-token-sicredi-' . time(),
                'expires_in' => 3600,
                'token_type' => 'Bearer'
            ];
        }

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'conta-corrente'
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $response = $this->httpRequest($authUrl, 'POST', $headers, $body, $conexao, true);

        if (empty($response['access_token'])) {
            throw new \Exception('Falha na autenticação Sicredi: token não retornado.');
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'] ?? 3600,
            'token_type' => $response['token_type'] ?? 'Bearer'
        ];
    }

    public function getSaldo(array $conexao): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];

        if ($ambiente === 'sandbox' && (empty($conexao['cert_pem']) || empty($conexao['key_pem']))) {
            return [
                'saldo' => 22340.88,
                'saldo_bloqueado' => 0,
                'atualizado_em' => date('Y-m-d\TH:i:s'),
                'moeda' => 'BRL'
            ];
        }

        $token = $this->getAccessToken($conexao);
        $cooperativa = $conexao['banco_conta_id'] ?? '';

        $url = $baseUrl . '/conta-corrente/v1/saldo';
        $params = [];
        if (!empty($cooperativa)) {
            $params['cooperativa'] = $cooperativa;
        }

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->authHeaders($token),
            !empty($params) ? $params : null,
            $conexao
        );

        return [
            'saldo' => (float) ($response['saldo'] ?? $response['saldoDisponivel'] ?? $response['vlrSaldo'] ?? 0),
            'saldo_bloqueado' => (float) ($response['saldoBloqueado'] ?? 0),
            'atualizado_em' => date('Y-m-d\TH:i:s'),
            'moeda' => 'BRL'
        ];
    }

    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];

        if ($ambiente === 'sandbox' && (empty($conexao['cert_pem']) || empty($conexao['key_pem']))) {
            return $this->getMockTransacoes();
        }

        $token = $this->getAccessToken($conexao);

        $url = $baseUrl . '/conta-corrente/v1/extrato';
        $params = [
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ];

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->authHeaders($token),
            $params,
            $conexao
        );

        $transacoes = [];
        $items = $response['transacoes'] ?? $response['lancamentos'] ?? $response['data'] ?? $response;

        if (!is_array($items)) return [];

        foreach ($items as $txn) {
            if (!is_array($txn)) continue;

            $valor = (float) ($txn['valor'] ?? $txn['amount'] ?? 0);
            $descricao = $txn['descricao'] ?? $txn['historico'] ?? $txn['description'] ?? 'Transação Sicredi';

            $transacoes[] = [
                'banco_transacao_id' => 'SCR-' . ($txn['idTransacao'] ?? $txn['transactionId'] ?? $txn['numeroDocumento'] ?? uniqid()),
                'data_transacao' => $txn['data'] ?? $txn['dataLancamento'] ?? $txn['date'] ?? $dataInicio,
                'descricao_original' => $descricao,
                'valor' => abs($valor),
                'tipo' => $valor < 0 ? 'debito' : 'credito',
                'metodo_pagamento' => $this->identificarMetodoPagamento($descricao),
                'saldo_apos' => isset($txn['saldo']) ? (float) $txn['saldo'] : null,
                'origem' => 'sicredi',
                'dados_extras' => [
                    'tipo_lancamento' => $txn['tipoLancamento'] ?? '',
                    'numero_documento' => $txn['numeroDocumento'] ?? ''
                ]
            ];
        }

        return $transacoes;
    }

    public function testarConexao(array $conexao): bool
    {
        try {
            $tokenData = $this->autenticar($conexao);
            return !empty($tokenData['access_token']);
        } catch (\Exception $e) {
            $this->logError('Teste de conexão falhou', ['erro' => $e->getMessage()]);
            return false;
        }
    }

    public function getCamposConfiguracao(): array
    {
        return [
            ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true,
             'placeholder' => 'Client ID do portal developers.sicredi.com.br'],
            ['name' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true,
             'placeholder' => 'Client Secret'],
            ['name' => 'cert_pem', 'label' => 'Certificado (PEM)', 'type' => 'textarea', 'required' => true,
             'placeholder' => '-----BEGIN CERTIFICATE-----\n...'],
            ['name' => 'key_pem', 'label' => 'Chave Privada (PEM)', 'type' => 'textarea', 'required' => true,
             'placeholder' => '-----BEGIN PRIVATE KEY-----\n...'],
            ['name' => 'cert_password', 'label' => 'Senha do Certificado', 'type' => 'password', 'required' => false],
            ['name' => 'banco_conta_id', 'label' => 'Cooperativa / Conta', 'type' => 'text', 'required' => false,
             'placeholder' => 'Ex: 0100/12345-6'],
            ['name' => 'ambiente', 'label' => 'Ambiente', 'type' => 'select', 'required' => true,
             'options' => ['sandbox' => 'Sandbox (testes)', 'producao' => 'Produção'],
             'default' => 'sandbox']
        ];
    }

    private function getMockTransacoes(): array
    {
        return [
            [
                'banco_transacao_id' => 'SCR-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-1 day')),
                'descricao_original' => 'PIX RECEBIDO - VENDA PRODUTO',
                'valor' => 3200.00, 'tipo' => 'credito',
                'metodo_pagamento' => 'PIX', 'saldo_apos' => 25540.88,
                'origem' => 'sicredi', 'dados_extras' => []
            ],
            [
                'banco_transacao_id' => 'SCR-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-2 days')),
                'descricao_original' => 'PAGTO BOLETO - ALUGUEL SALA COMERCIAL',
                'valor' => 1800.00, 'tipo' => 'debito',
                'metodo_pagamento' => 'Boleto', 'saldo_apos' => 22340.88,
                'origem' => 'sicredi', 'dados_extras' => []
            ]
        ];
    }
}
