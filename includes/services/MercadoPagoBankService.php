<?php
namespace Includes\Services;

/**
 * Integração com a API do Mercado Pago.
 * 
 * A mais simples de todas - usa apenas Access Token (Bearer).
 * Não requer certificado digital nem mTLS.
 * 
 * Documentação: https://www.mercadopago.com.br/developers/pt/reference
 * 
 * Endpoints utilizados:
 *   GET /users/me          -> dados da conta (saldo)
 *   GET /v1/payments/search -> listar pagamentos recebidos
 *   GET /v1/account/bank_report -> relatório de conta (extrato)
 */
class MercadoPagoBankService extends AbstractBankService
{
    private $baseUrl = 'https://api.mercadopago.com';

    public function getBancoNome(): string
    {
        return 'mercadopago';
    }

    public function getBancoLabel(): string
    {
        return 'Mercado Pago';
    }

    /**
     * Mercado Pago usa Access Token direto (não precisa OAuth flow).
     * O token é obtido no painel do Mercado Pago.
     */
    public function autenticar(array $conexao): array
    {
        $accessToken = $conexao['access_token'] ?? null;

        if (empty($accessToken)) {
            throw new \Exception('Access Token do Mercado Pago não configurado.');
        }

        // Decodificar se estiver em base64 (criptografia do sistema)
        if (strpos($accessToken, 'APP_USR') === false && strpos($accessToken, 'TEST-') === false) {
            $decoded = base64_decode($accessToken);
            if ($decoded && (strpos($decoded, 'APP_USR') !== false || strpos($decoded, 'TEST-') !== false)) {
                $accessToken = $decoded;
            }
        }

        return [
            'access_token' => $accessToken,
            'expires_in' => 21600 // MP tokens duram ~6h
        ];
    }

    /**
     * Obtém saldo da conta Mercado Pago via GET /users/me
     */
    public function getSaldo(array $conexao): array
    {
        $token = $this->getAccessToken($conexao);

        try {
            // GET /users/me retorna dados da conta incluindo saldo
            $response = $this->httpRequest(
                $this->baseUrl . '/users/me',
                'GET',
                $this->authHeaders($token)
            );

            $saldo = 0;
            $saldoBloqueado = 0;

            // Saldo disponível pode estar em diferentes caminhos no response
            if (isset($response['available_balance'])) {
                $saldo = (float) $response['available_balance'];
            }
            if (isset($response['unavailable_balance'])) {
                $saldoBloqueado = (float) $response['unavailable_balance'];
            }

            // Alternativa: buscar via /balance (se /users/me não tiver saldo)
            if ($saldo === 0.0) {
                try {
                    $balanceResponse = $this->httpRequest(
                        $this->baseUrl . '/users/me/mercadopago_account/balance',
                        'GET',
                        $this->authHeaders($token)
                    );
                    $saldo = (float) ($balanceResponse['available_balance'] ?? 0);
                    $saldoBloqueado = (float) ($balanceResponse['unavailable_balance'] ?? 0);
                } catch (\Exception $e) {
                    // Ignorar - usar valor anterior
                }
            }

            return [
                'saldo' => $saldo,
                'saldo_bloqueado' => $saldoBloqueado,
                'atualizado_em' => date('Y-m-d\TH:i:s'),
                'moeda' => 'BRL'
            ];
        } catch (\Exception $e) {
            $this->logError('Erro ao obter saldo', ['erro' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Obtém transações do Mercado Pago via GET /v1/payments/search
     * Inclui pagamentos recebidos e saídas.
     */
    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $token = $this->getAccessToken($conexao);
        $transacoes = [];

        // Buscar pagamentos recebidos (entradas)
        $transacoes = array_merge(
            $transacoes,
            $this->buscarPagamentos($token, $dataInicio, $dataFim)
        );

        return $transacoes;
    }

    /**
     * Busca pagamentos com paginação automática.
     */
    private function buscarPagamentos(string $token, string $dataInicio, string $dataFim): array
    {
        $transacoes = [];
        $offset = 0;
        $limit = 50;
        $maxPages = 20; // Segurança: máximo de páginas

        for ($page = 0; $page < $maxPages; $page++) {
            $params = [
                'sort' => 'date_created',
                'criteria' => 'desc',
                'range' => 'date_created',
                'begin_date' => $dataInicio . 'T00:00:00.000-03:00',
                'end_date' => $dataFim . 'T23:59:59.999-03:00',
                'offset' => $offset,
                'limit' => $limit
            ];

            try {
                $response = $this->httpRequest(
                    $this->baseUrl . '/v1/payments/search',
                    'GET',
                    $this->authHeaders($token),
                    $params
                );

                $results = $response['results'] ?? [];
                
                if (empty($results)) {
                    break;
                }

                foreach ($results as $payment) {
                    $transacoes[] = $this->normalizarTransacao($payment);
                }

                $total = $response['paging']['total'] ?? 0;
                $offset += $limit;

                if ($offset >= $total) {
                    break;
                }
            } catch (\Exception $e) {
                $this->logError('Erro ao buscar pagamentos', [
                    'page' => $page,
                    'erro' => $e->getMessage()
                ]);
                break;
            }
        }

        return $transacoes;
    }

    /**
     * Normaliza pagamento do MP para formato padrão.
     */
    private function normalizarTransacao(array $payment): array
    {
        $amount = (float) ($payment['transaction_amount'] ?? 0);
        $status = $payment['status'] ?? '';
        
        // Determinar tipo: se é collector (vendedor), recebimentos são crédito
        $tipo = 'credito'; // Pagamentos no search são geralmente recebidos
        
        // Se status é refunded ou charged_back, é débito (devolução)
        if (in_array($status, ['refunded', 'charged_back'])) {
            $tipo = 'debito';
        }

        // Método de pagamento
        $paymentMethod = $payment['payment_method_id'] ?? '';
        $paymentType = $payment['payment_type_id'] ?? '';
        $metodo = $this->mapearMetodoPagamento($paymentMethod, $paymentType);

        // Descrição
        $descricao = $payment['description'] ?? '';
        if (empty($descricao)) {
            $descricao = 'Pagamento Mercado Pago';
        }

        // Adicionar info do pagador se disponível
        $payerEmail = $payment['payer']['email'] ?? '';
        if ($payerEmail && strpos($descricao, $payerEmail) === false) {
            $descricao .= ' | ' . $payerEmail;
        }

        return [
            'banco_transacao_id' => 'MP-' . ($payment['id'] ?? uniqid()),
            'data_transacao' => substr($payment['date_created'] ?? date('Y-m-d'), 0, 10),
            'descricao_original' => $descricao,
            'valor' => abs($amount),
            'tipo' => $tipo,
            'metodo_pagamento' => $metodo,
            'saldo_apos' => null,
            'origem' => 'mercadopago',
            'dados_extras' => [
                'status' => $status,
                'status_detail' => $payment['status_detail'] ?? '',
                'payment_id' => $payment['id'] ?? '',
                'external_reference' => $payment['external_reference'] ?? '',
                'payer_email' => $payerEmail,
                'fee_amount' => $payment['fee_details'][0]['amount'] ?? 0,
                'net_amount' => $payment['transaction_details']['net_received_amount'] ?? $amount
            ]
        ];
    }

    /**
     * Mapeia método de pagamento do MP para nosso padrão.
     */
    private function mapearMetodoPagamento(string $method, string $type): string
    {
        // Tipos específicos
        if (stripos($method, 'pix') !== false || $method === 'pix') return 'PIX';
        if ($method === 'bolbradesco' || $type === 'ticket') return 'Boleto';
        if ($type === 'credit_card') return 'Cartão de Crédito';
        if ($type === 'debit_card') return 'Cartão de Débito';
        if ($type === 'bank_transfer') return 'Transferência';
        if ($type === 'account_money') return 'Saldo MP';

        return 'Outros';
    }

    /**
     * Testa conexão fazendo GET /users/me
     */
    public function testarConexao(array $conexao): bool
    {
        try {
            $token = $this->getAccessToken($conexao);
            $response = $this->httpRequest(
                $this->baseUrl . '/users/me',
                'GET',
                $this->authHeaders($token)
            );
            return isset($response['id']);
        } catch (\Exception $e) {
            $this->logError('Teste de conexão falhou', ['erro' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Campos necessários para configurar o Mercado Pago.
     */
    public function getCamposConfiguracao(): array
    {
        return [
            [
                'name' => 'access_token',
                'label' => 'Access Token',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'APP_USR-... (obtido em mercadopago.com.br/developers)',
                'help' => 'Obtenha em: Mercado Pago > Seu negócio > Configurações > Gestão e Administração > Credenciais'
            ],
            [
                'name' => 'ambiente',
                'label' => 'Ambiente',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'sandbox' => 'Sandbox (testes)',
                    'producao' => 'Produção'
                ],
                'default' => 'sandbox'
            ]
        ];
    }
}
