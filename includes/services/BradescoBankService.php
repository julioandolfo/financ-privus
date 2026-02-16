<?php
namespace Includes\Services;

/**
 * Integração com a API do Bradesco.
 * 
 * Autenticação: OAuth 2.0 client_credentials + certificado digital
 * 
 * Documentação: https://developers.bradesco.com.br
 * 
 * Endpoints:
 *   POST /auth/server/v1.1/token             -> autenticação
 *   GET  /conta-corrente/v1/saldo            -> saldo
 *   GET  /conta-corrente/v1/extrato          -> extrato
 */
class BradescoBankService extends AbstractBankService
{
    private $baseUrls = [
        'sandbox' => 'https://proxy.api.prebanco.com.br',
        'producao' => 'https://openapi.bradesco.com.br'
    ];

    private $authUrls = [
        'sandbox' => 'https://proxy.api.prebanco.com.br/auth/server/v1.1/token',
        'producao' => 'https://openapi.bradesco.com.br/auth/server/v1.1/token'
    ];

    public function getBancoNome(): string
    {
        return 'bradesco';
    }

    public function getBancoLabel(): string
    {
        return 'Bradesco';
    }

    public function autenticar(array $conexao): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $authUrl = $this->authUrls[$ambiente] ?? $this->authUrls['sandbox'];

        $clientId = $conexao['client_id'] ?? '';
        $clientSecret = $conexao['client_secret'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('Client ID e Client Secret do Bradesco são obrigatórios.');
        }

        $hasCerts = (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) || !empty($conexao['cert_pfx']);

        if ($ambiente === 'sandbox' && !$hasCerts) {
            return [
                'access_token' => 'sandbox-mock-token-bradesco-' . time(),
                'expires_in' => 3600,
                'token_type' => 'Bearer'
            ];
        }

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret)
        ];

        $body = [
            'grant_type' => 'client_credentials'
        ];

        $response = $this->httpRequest($authUrl, 'POST', $headers, $body, $conexao, true);

        if (empty($response['access_token'])) {
            throw new \Exception('Falha na autenticação Bradesco: token não retornado.');
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

        $hasCerts = (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) || !empty($conexao['cert_pfx']);
        if ($ambiente === 'sandbox' && !$hasCerts) {
            return [
                'saldo' => 31480.67,
                'saldo_bloqueado' => 0,
                'atualizado_em' => date('Y-m-d\TH:i:s'),
                'moeda' => 'BRL'
            ];
        }

        $token = $this->getAccessToken($conexao);

        $url = $baseUrl . '/conta-corrente/v1/saldo';
        $params = [];
        $agenciaConta = $conexao['banco_conta_id'] ?? '';
        if (!empty($agenciaConta)) {
            $parts = preg_split('/[\/\-]/', $agenciaConta);
            if (count($parts) >= 2) {
                $params['agencia'] = $parts[0];
                $params['conta'] = $parts[1];
            }
        }

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->authHeaders($token),
            !empty($params) ? $params : null,
            $conexao
        );

        return [
            'saldo' => (float) ($response['saldo'] ?? $response['saldoDisponivel'] ?? 0),
            'saldo_bloqueado' => (float) ($response['saldoBloqueado'] ?? 0),
            'atualizado_em' => date('Y-m-d\TH:i:s'),
            'moeda' => 'BRL'
        ];
    }

    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];

        $hasCerts = (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) || !empty($conexao['cert_pfx']);
        if ($ambiente === 'sandbox' && !$hasCerts) {
            return $this->getMockTransacoes();
        }

        $token = $this->getAccessToken($conexao);

        $url = $baseUrl . '/conta-corrente/v1/extrato';
        $params = [
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ];

        $agenciaConta = $conexao['banco_conta_id'] ?? '';
        if (!empty($agenciaConta)) {
            $parts = preg_split('/[\/\-]/', $agenciaConta);
            if (count($parts) >= 2) {
                $params['agencia'] = $parts[0];
                $params['conta'] = $parts[1];
            }
        }

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->authHeaders($token),
            $params,
            $conexao
        );

        $transacoes = [];
        $items = $response['lancamentos'] ?? $response['transacoes'] ?? $response['data'] ?? $response;

        if (!is_array($items)) return [];

        foreach ($items as $txn) {
            if (!is_array($txn)) continue;

            $valor = (float) ($txn['valor'] ?? $txn['amount'] ?? 0);
            $descricao = $txn['descricao'] ?? $txn['historico'] ?? $txn['description'] ?? 'Transação Bradesco';

            $transacoes[] = [
                'banco_transacao_id' => 'BRD-' . ($txn['idTransacao'] ?? $txn['transactionId'] ?? $txn['codigoLancamento'] ?? uniqid()),
                'data_transacao' => $txn['data'] ?? $txn['dataLancamento'] ?? $txn['date'] ?? $dataInicio,
                'descricao_original' => $descricao,
                'valor' => abs($valor),
                'tipo' => $valor < 0 ? 'debito' : 'credito',
                'metodo_pagamento' => $this->identificarMetodoPagamento($descricao),
                'saldo_apos' => isset($txn['saldo']) ? (float) $txn['saldo'] : null,
                'origem' => 'bradesco',
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
             'placeholder' => 'Client ID do developers.bradesco.com.br'],
            ['name' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true,
             'placeholder' => 'Client Secret'],
            ['name' => 'cert_pfx', 'label' => 'Certificado Digital (.pfx)', 'type' => 'file', 'required' => false,
             'accept' => '.pfx,.p12',
             'help' => 'Upload do arquivo .pfx ou .p12. Se preferir PEM, use os campos abaixo.'],
            ['name' => 'cert_pem', 'label' => 'Certificado PEM (alternativa ao PFX)', 'type' => 'textarea', 'required' => false,
             'placeholder' => '-----BEGIN CERTIFICATE-----\n...'],
            ['name' => 'key_pem', 'label' => 'Chave Privada PEM (alternativa ao PFX)', 'type' => 'textarea', 'required' => false,
             'placeholder' => '-----BEGIN PRIVATE KEY-----\n...'],
            ['name' => 'cert_password', 'label' => 'Senha do Certificado', 'type' => 'password', 'required' => false],
            ['name' => 'banco_conta_id', 'label' => 'Agência / Conta', 'type' => 'text', 'required' => false,
             'placeholder' => 'Ex: 1234/567890-1'],
            ['name' => 'ambiente', 'label' => 'Ambiente', 'type' => 'select', 'required' => true,
             'options' => ['sandbox' => 'Sandbox (testes)', 'producao' => 'Produção'],
             'default' => 'sandbox']
        ];
    }

    private function getMockTransacoes(): array
    {
        return [
            [
                'banco_transacao_id' => 'BRD-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-1 day')),
                'descricao_original' => 'PIX RECEBIDO - CLIENTE BRADESCO',
                'valor' => 4200.00, 'tipo' => 'credito',
                'metodo_pagamento' => 'PIX', 'saldo_apos' => 35680.67,
                'origem' => 'bradesco', 'dados_extras' => []
            ],
            [
                'banco_transacao_id' => 'BRD-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-3 days')),
                'descricao_original' => 'DEB.AUT INTERNET - TELEFONE',
                'valor' => 189.90, 'tipo' => 'debito',
                'metodo_pagamento' => 'Débito Automático', 'saldo_apos' => 31480.67,
                'origem' => 'bradesco', 'dados_extras' => []
            ]
        ];
    }
}
