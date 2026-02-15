<?php
namespace Includes\Services;

/**
 * Integração com a API do Sicoob (API nativa para cooperados).
 * 
 * Autenticação: mTLS (certificado digital) + OAuth client_credentials
 * 
 * Documentação: https://developers.sicoob.com.br
 * 
 * Endpoints:
 *   POST /oauth2/token                              -> autenticação
 *   GET  /conta-corrente/v2/contas/{conta}/saldo     -> saldo
 *   GET  /conta-corrente/v2/contas/{conta}/extrato   -> extrato
 */
class SicoobBankService extends AbstractBankService
{
    private $baseUrls = [
        'sandbox' => 'https://sandbox.sicoob.com.br',
        'producao' => 'https://api.sicoob.com.br'
    ];

    private $authUrls = [
        'sandbox' => 'https://sandbox.sicoob.com.br/oauth2/token',
        'producao' => 'https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token'
    ];

    public function getBancoNome(): string
    {
        return 'sicoob';
    }

    public function getBancoLabel(): string
    {
        return 'Sicoob';
    }

    /**
     * Autenticação via client_credentials com mTLS.
     */
    public function autenticar(array $conexao): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $authUrl = $this->authUrls[$ambiente] ?? $this->authUrls['sandbox'];

        $clientId = $conexao['client_id'] ?? '';
        $clientSecret = $conexao['client_secret'] ?? '';

        if (empty($clientId)) {
            throw new \Exception('Client ID do Sicoob não configurado.');
        }

        // Verificar se tem certificados configurados
        $hasCerts = !empty($conexao['cert_pem']) && !empty($conexao['key_pem']);

        // Em sandbox sem certificados, retornar mock
        if ($ambiente === 'sandbox' && !$hasCerts) {
            return [
                'access_token' => 'sandbox-mock-token-sicoob-' . time(),
                'expires_in' => 300,
                'token_type' => 'Bearer',
                'scope' => 'cco_extrato cco_saldo'
            ];
        }

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'scope' => 'cco_extrato cco_saldo conta_corrente_boletos_consultar'
        ];

        // Se tiver client_secret, adicionar
        if (!empty($clientSecret)) {
            $body['client_secret'] = $clientSecret;
        }

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $response = $this->httpRequest($authUrl, 'POST', $headers, $body, $conexao, true);

        if (empty($response['access_token'])) {
            throw new \Exception('Falha na autenticação Sicoob: token não retornado.');
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'] ?? 300,
            'token_type' => $response['token_type'] ?? 'Bearer',
            'scope' => $response['scope'] ?? ''
        ];
    }

    /**
     * Obtém saldo da conta corrente.
     */
    public function getSaldo(array $conexao): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];
        $numeroConta = $conexao['banco_conta_id'] ?? $conexao['identificacao'] ?? '';

        // Mock para sandbox
        if ($ambiente === 'sandbox' && (empty($conexao['cert_pem']) || empty($conexao['key_pem']))) {
            return [
                'saldo' => 15750.42,
                'saldo_bloqueado' => 0,
                'atualizado_em' => date('Y-m-d\TH:i:s'),
                'moeda' => 'BRL'
            ];
        }

        $token = $this->getAccessToken($conexao);

        $url = $baseUrl . '/conta-corrente/v2/contas/saldo';
        if (!empty($numeroConta)) {
            $url = $baseUrl . "/conta-corrente/v2/contas/{$numeroConta}/saldo";
        }

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->authHeaders($token),
            null,
            $conexao
        );

        return [
            'saldo' => (float) ($response['saldo'] ?? $response['saldoDisponivel'] ?? $response['vlrSaldo'] ?? 0),
            'saldo_bloqueado' => (float) ($response['saldoBloqueado'] ?? $response['vlrBloqueado'] ?? 0),
            'atualizado_em' => date('Y-m-d\TH:i:s'),
            'moeda' => 'BRL'
        ];
    }

    /**
     * Obtém extrato da conta corrente.
     */
    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $ambiente = $conexao['ambiente'] ?? 'sandbox';
        $baseUrl = $this->baseUrls[$ambiente] ?? $this->baseUrls['sandbox'];
        $numeroConta = $conexao['banco_conta_id'] ?? $conexao['identificacao'] ?? '';

        // Mock para sandbox
        if ($ambiente === 'sandbox' && (empty($conexao['cert_pem']) || empty($conexao['key_pem']))) {
            return $this->getMockTransacoes();
        }

        $token = $this->getAccessToken($conexao);

        $url = $baseUrl . '/conta-corrente/v2/contas/extrato';
        if (!empty($numeroConta)) {
            $url = $baseUrl . "/conta-corrente/v2/contas/{$numeroConta}/extrato";
        }

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

        // Normalizar transações
        $transacoes = [];
        $items = $response['transacoes'] ?? $response['resultado'] ?? $response['data'] ?? $response;
        
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as $txn) {
            if (!is_array($txn)) continue;

            $valor = (float) ($txn['valor'] ?? $txn['vlrLancamento'] ?? $txn['amount'] ?? 0);
            $descricao = $txn['descricao'] ?? $txn['descLancamento'] ?? $txn['description'] ?? 'Transação Sicoob';

            $transacoes[] = [
                'banco_transacao_id' => 'SCB-' . ($txn['idTransacao'] ?? $txn['numDocumento'] ?? $txn['transactionId'] ?? uniqid()),
                'data_transacao' => $txn['data'] ?? $txn['dtLancamento'] ?? $txn['bookingDate'] ?? $dataInicio,
                'descricao_original' => $descricao,
                'valor' => abs($valor),
                'tipo' => $valor < 0 ? 'debito' : 'credito',
                'metodo_pagamento' => $this->identificarMetodoPagamento($descricao),
                'saldo_apos' => isset($txn['saldo']) ? (float) $txn['saldo'] : null,
                'origem' => 'sicoob',
                'dados_extras' => [
                    'tipo_lancamento' => $txn['tipoLancamento'] ?? $txn['codTipoLanc'] ?? '',
                    'numero_documento' => $txn['numDocumento'] ?? ''
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
             'placeholder' => 'Client ID do portal developers.sicoob.com.br',
             'help' => 'Obtenha em: developers.sicoob.com.br > Suas aplicações'],
            ['name' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => false,
             'placeholder' => 'Client Secret (se aplicável)'],
            ['name' => 'cert_pem', 'label' => 'Certificado (PEM)', 'type' => 'textarea', 'required' => true,
             'placeholder' => '-----BEGIN CERTIFICATE-----\n...',
             'help' => 'Certificado digital no formato PEM para autenticação mTLS'],
            ['name' => 'key_pem', 'label' => 'Chave Privada (PEM)', 'type' => 'textarea', 'required' => true,
             'placeholder' => '-----BEGIN PRIVATE KEY-----\n...'],
            ['name' => 'cert_password', 'label' => 'Senha do Certificado', 'type' => 'password', 'required' => false,
             'placeholder' => 'Senha (se o certificado tiver)'],
            ['name' => 'banco_conta_id', 'label' => 'Número da Conta', 'type' => 'text', 'required' => false,
             'placeholder' => 'Ex: 12345-6',
             'help' => 'Número da conta corrente no Sicoob'],
            ['name' => 'ambiente', 'label' => 'Ambiente', 'type' => 'select', 'required' => true,
             'options' => ['sandbox' => 'Sandbox (testes)', 'producao' => 'Produção'],
             'default' => 'sandbox']
        ];
    }

    /**
     * Transações mock para ambiente sandbox.
     */
    private function getMockTransacoes(): array
    {
        return [
            [
                'banco_transacao_id' => 'SCB-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-1 day')),
                'descricao_original' => 'PIX RECEBIDO - CLIENTE EXEMPLO LTDA',
                'valor' => 2500.00,
                'tipo' => 'credito',
                'metodo_pagamento' => 'PIX',
                'saldo_apos' => 18250.42,
                'origem' => 'sicoob',
                'dados_extras' => []
            ],
            [
                'banco_transacao_id' => 'SCB-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-2 days')),
                'descricao_original' => 'TED ENVIADO - FORNECEDOR ABC LTDA',
                'valor' => 1200.00,
                'tipo' => 'debito',
                'metodo_pagamento' => 'TED',
                'saldo_apos' => 15750.42,
                'origem' => 'sicoob',
                'dados_extras' => []
            ],
            [
                'banco_transacao_id' => 'SCB-MOCK-' . uniqid(),
                'data_transacao' => date('Y-m-d', strtotime('-3 days')),
                'descricao_original' => 'BOLETO PAGO - ENERGIA ELÉTRICA',
                'valor' => 450.00,
                'tipo' => 'debito',
                'metodo_pagamento' => 'Boleto',
                'saldo_apos' => 16950.42,
                'origem' => 'sicoob',
                'dados_extras' => []
            ]
        ];
    }
}
