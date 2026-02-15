<?php
namespace Includes\Services;

/**
 * Integração com a API do Sicoob (Conta Corrente V4).
 * 
 * Autenticação: mTLS (certificado digital PFX/PEM) + OAuth2 client_credentials
 * O Sicoob NÃO utiliza client_secret — a autenticação é feita via certificado mTLS.
 * 
 * Documentação oficial: https://developers.sicoob.com.br
 * 
 * Endpoints:
 *   POST  auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token  -> token OAuth2
 *   GET   api.sicoob.com.br/conta-corrente/v4/saldo?numeroContaCorrente=XXX       -> saldo
 *   GET   api.sicoob.com.br/conta-corrente/v4/extrato/{mes}/{ano}?numConta=XXX    -> extrato
 * 
 * Scopes:
 *   cco_consulta           -> consulta saldo e extrato
 *   cco_transferencias     -> transferências (não usado aqui)
 */
class SicoobBankService extends AbstractBankService
{
    private $baseUrl = 'https://api.sicoob.com.br';
    
    private $authUrl = 'https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token';

    public function getBancoNome(): string
    {
        return 'sicoob';
    }

    public function getBancoLabel(): string
    {
        return 'Sicoob';
    }

    /**
     * Autenticação via client_credentials com mTLS (certificado digital).
     * O Sicoob NÃO usa client_secret.
     */
    public function autenticar(array $conexao): array
    {
        $clientId = $conexao['client_id'] ?? '';

        if (empty($clientId)) {
            throw new \Exception('Client ID do Sicoob não configurado. Obtenha em developers.sicoob.com.br');
        }

        $hasCerts = $this->temCertificado($conexao);

        if (!$hasCerts) {
            throw new \Exception(
                'Certificado digital não configurado. O Sicoob exige certificado mTLS (PFX ou PEM) para autenticação.'
            );
        }

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'scope' => 'cco_consulta'
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ];

        $response = $this->httpRequest(
            $this->authUrl,
            'POST',
            $headers,
            $body,
            $conexao,
            true
        );

        if (empty($response['access_token'])) {
            $erro = $response['error_description'] ?? $response['error'] ?? 'Token não retornado';
            throw new \Exception("Falha na autenticação Sicoob: {$erro}");
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'] ?? 300,
            'token_type' => $response['token_type'] ?? 'Bearer',
            'scope' => $response['scope'] ?? 'cco_consulta'
        ];
    }

    /**
     * Obtém saldo da conta corrente.
     * 
     * Endpoint: GET /conta-corrente/v4/saldo?numeroContaCorrente={numero}
     */
    public function getSaldo(array $conexao): array
    {
        $numeroConta = $this->getNumeroContaLimpo($conexao);

        if (empty($numeroConta)) {
            throw new \Exception('Número da conta corrente não configurado para consulta de saldo.');
        }

        $token = $this->getAccessToken($conexao);
        $clientId = $conexao['client_id'] ?? '';

        $url = $this->baseUrl . '/conta-corrente/v4/saldo';
        $params = ['numeroContaCorrente' => $numeroConta];

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->sicoobHeaders($token, $clientId),
            $params,
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
     * 
     * Endpoint: GET /conta-corrente/v4/extrato/{mes}/{ano}?numeroContaCorrente={numero}&diaInicial=X&diaFinal=Y
     */
    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $numeroConta = $this->getNumeroContaLimpo($conexao);

        if (empty($numeroConta)) {
            throw new \Exception('Número da conta corrente não configurado para consulta de extrato.');
        }

        $token = $this->getAccessToken($conexao);
        $clientId = $conexao['client_id'] ?? '';

        // A API do Sicoob usa mês/ano na URL + diaInicial/diaFinal como query params
        $mesInicio = (int) date('m', strtotime($dataInicio));
        $anoInicio = (int) date('Y', strtotime($dataInicio));
        $diaInicio = (int) date('d', strtotime($dataInicio));
        $diaFim = (int) date('d', strtotime($dataFim));
        $mesFim = (int) date('m', strtotime($dataFim));
        $anoFim = (int) date('Y', strtotime($dataFim));

        $transacoes = [];

        // Se o período cruza meses, faz múltiplas chamadas
        $currentYear = $anoInicio;
        $currentMonth = $mesInicio;

        while ($currentYear < $anoFim || ($currentYear === $anoFim && $currentMonth <= $mesFim)) {
            $params = [
                'numeroContaCorrente' => $numeroConta
            ];

            // Define dia inicial e final do mês consultado
            if ($currentYear === $anoInicio && $currentMonth === $mesInicio) {
                $params['diaInicial'] = $diaInicio;
            }
            if ($currentYear === $anoFim && $currentMonth === $mesFim) {
                $params['diaFinal'] = $diaFim;
            }

            $url = $this->baseUrl . "/conta-corrente/v4/extrato/{$currentMonth}/{$currentYear}";

            try {
                $response = $this->httpRequest(
                    $url,
                    'GET',
                    $this->sicoobHeaders($token, $clientId),
                    $params,
                    $conexao
                );

                $items = $response['resultado'] ?? $response['transacoes'] ?? $response['data'] ?? [];
                if (is_array($items)) {
                    $transacoes = array_merge($transacoes, $this->normalizarTransacoes($items));
                }
            } catch (\Exception $e) {
                $this->logError("Erro ao buscar extrato {$currentMonth}/{$currentYear}", [
                    'erro' => $e->getMessage()
                ]);
            }

            // Próximo mês
            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
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
            [
                'name' => 'client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ex: 5193d273-ceed-4889-b34a-57e599d4ba94',
                'help' => 'Obtenha em: developers.sicoob.com.br > Suas aplicações > Dados Gerais'
            ],
            [
                'name' => 'cooperativa',
                'label' => 'Número da Cooperativa',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Ex: 3125',
                'help' => 'Número da sua cooperativa Sicoob'
            ],
            [
                'name' => 'banco_conta_id',
                'label' => 'Número da Conta Corrente',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ex: 230088-5 (somente números e hífen)',
                'help' => 'Conta corrente conforme exibida no portal do Sicoob'
            ],
            [
                'name' => 'cert_pfx',
                'label' => 'Certificado PFX (Base64)',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => 'Cole aqui o conteúdo Base64 do arquivo .pfx',
                'help' => 'Se usar PFX: converta com base64 e cole aqui. Se preferir PEM, use os campos abaixo.'
            ],
            [
                'name' => 'cert_password',
                'label' => 'Senha do Certificado PFX',
                'type' => 'password',
                'required' => false,
                'placeholder' => 'Senha do arquivo .pfx'
            ],
            [
                'name' => 'cert_pem',
                'label' => 'Certificado PEM (alternativa ao PFX)',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => '-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----',
                'help' => 'Certificado digital no formato PEM. Alternativa ao PFX.'
            ],
            [
                'name' => 'key_pem',
                'label' => 'Chave Privada PEM (alternativa ao PFX)',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => '-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----',
                'help' => 'Chave privada correspondente ao certificado PEM.'
            ],
            [
                'name' => 'ambiente',
                'label' => 'Ambiente',
                'type' => 'select',
                'required' => true,
                'options' => ['producao' => 'Produção', 'sandbox' => 'Sandbox (testes)'],
                'default' => 'producao'
            ]
        ];
    }

    // =========================================================================
    // Métodos auxiliares privados
    // =========================================================================

    /**
     * Monta headers padrão para requisições à API do Sicoob.
     * O Sicoob exige o header client_id em todas as requisições.
     */
    private function sicoobHeaders(string $token, string $clientId): array
    {
        return [
            'Authorization: Bearer ' . $token,
            'client_id: ' . $clientId,
            'Accept: application/json',
            'Content-Type: application/json'
        ];
    }

    /**
     * Obtém número da conta limpo (sem pontos, somente números e hífen).
     */
    private function getNumeroContaLimpo(array $conexao): string
    {
        $conta = $conexao['banco_conta_id'] ?? $conexao['identificacao'] ?? '';
        // Remove pontos e espaços, mantém números e hífen
        return preg_replace('/[^0-9\-]/', '', $conta);
    }

    /**
     * Verifica se a conexão possui certificado configurado (PFX ou PEM).
     */
    private function temCertificado(array $conexao): bool
    {
        // Tem PFX?
        if (!empty($conexao['cert_pfx'])) {
            return true;
        }
        // Tem PEM (cert + key)?
        if (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) {
            return true;
        }
        return false;
    }

    /**
     * Normaliza array de transações do Sicoob para o formato padrão do sistema.
     */
    private function normalizarTransacoes(array $items): array
    {
        $transacoes = [];

        foreach ($items as $txn) {
            if (!is_array($txn)) continue;

            $valor = (float) ($txn['valor'] ?? $txn['vlrLancamento'] ?? $txn['amount'] ?? 0);
            $descricao = $txn['descricao'] ?? $txn['descLancamento'] ?? $txn['description'] ?? 'Transação Sicoob';
            $data = $txn['data'] ?? $txn['dtLancamento'] ?? $txn['bookingDate'] ?? date('Y-m-d');

            $transacoes[] = [
                'banco_transacao_id' => 'SCB-' . ($txn['idTransacao'] ?? $txn['numDocumento'] ?? $txn['transactionId'] ?? uniqid()),
                'data_transacao' => $data,
                'descricao_original' => $descricao,
                'valor' => abs($valor),
                'tipo' => $valor < 0 ? 'debito' : 'credito',
                'metodo_pagamento' => $this->identificarMetodoPagamento($descricao),
                'saldo_apos' => isset($txn['saldo']) ? (float) $txn['saldo'] : null,
                'origem' => 'sicoob',
                'dados_extras' => [
                    'tipo_lancamento' => $txn['tipoLancamento'] ?? $txn['codTipoLanc'] ?? '',
                    'numero_documento' => $txn['numDocumento'] ?? '',
                    'cooperativa' => $txn['numCooperativa'] ?? ''
                ]
            ];
        }

        return $transacoes;
    }
}
