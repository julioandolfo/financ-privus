<?php
namespace Includes\Services;

use App\Models\LogSistema;

/**
 * Integração com a API de Cobrança Sicredi v1.
 * 
 * Auth: OAuth2 password grant + x-api-key (token do portal)
 * Docs: Manual API da Cobrança 1.2 Sicredi
 * 
 * URLs:
 *   Sandbox Auth: https://api-parceiro.sicredi.com.br/sb/auth/openapi/token
 *   Produção Auth: https://api-parceiro.sicredi.com.br/auth/openapi/token
 *   Sandbox Boletos: https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1/boletos
 *   Produção Boletos: https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos
 * 
 * Headers obrigatórios em todas as requisições:
 *   x-api-key: Access token gerado no portal do desenvolvedor
 *   context: COBRANCA
 *   Authorization: Bearer {access_token}
 * 
 * Autenticação:
 *   grant_type = password
 *   username = codigoBeneficiario + cooperativa (9 dígitos)
 *   password = Código de Acesso gerado no Internet Banking
 *   scope = cobranca
 * 
 * Token:
 *   access_token expira em ~300s (5min)
 *   refresh_token expira em ~1800s (30min)
 * 
 * Campos Sicredi vs Sicoob:
 *   cooperativa (header) = código da cooperativa (4 dígitos)
 *   posto (header) = código da agência/posto (2 dígitos)
 *   codigoBeneficiario (body) = código do convênio (5 dígitos)
 */
class SicrediCobrancaService extends AbstractBankService implements CobrancaApiInterface
{
    private $baseUrls = [
        'sandbox'  => 'https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1',
        'producao' => 'https://api-parceiro.sicredi.com.br/cobranca/boleto/v1',
    ];

    private $authUrls = [
        'sandbox'  => 'https://api-parceiro.sicredi.com.br/sb/auth/openapi/token',
        'producao' => 'https://api-parceiro.sicredi.com.br/auth/openapi/token',
    ];

    /** @var string|null refresh_token para renovação sem reautenticação */
    private $refreshToken = null;
    private $refreshTokenExpiresAt = 0;

    public function getBancoNome(): string { return 'sicredi'; }
    public function getBancoLabel(): string { return 'Sicredi Cobrança'; }

    // ========== BankApiInterface stubs (não usados — cobrança apenas) ==========
    public function getSaldo(array $conexao): array { return []; }
    public function getTransacoes(array $conexao, string $di, string $df): array { return []; }
    public function testarConexao(array $conexao): bool { return false; }
    public function getCamposConfiguracao(): array { return []; }

    // ========== Utilitários ==========

    private function getAmbiente(array $conexao): string
    {
        return $conexao['ambiente'] ?? 'sandbox';
    }

    private function getBaseUrl(array $conexao): string
    {
        $amb = $this->getAmbiente($conexao);
        return $this->baseUrls[$amb] ?? $this->baseUrls['sandbox'];
    }

    private function getAuthUrl(array $conexao): string
    {
        $amb = $this->getAmbiente($conexao);
        return $this->authUrls[$amb] ?? $this->authUrls['sandbox'];
    }

    /**
     * Monta headers padrão Sicredi (x-api-key, context, Authorization, cooperativa, posto).
     */
    private function sicrediHeaders(string $token, array $conexao): array
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'x-api-key: ' . ($conexao['x_api_key'] ?? ''),
            'context: COBRANCA',
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (!empty($conexao['cooperativa'])) {
            $headers[] = 'cooperativa: ' . $conexao['cooperativa'];
        }
        if (!empty($conexao['posto'])) {
            $headers[] = 'posto: ' . $conexao['posto'];
        }

        return $headers;
    }

    // ========== Autenticação ==========

    public function autenticar(array $conexao): array
    {
        return $this->autenticarCobranca($conexao);
    }

    /**
     * OAuth2 password grant com x-api-key no header.
     * 
     * Sandbox: username=123456789, password=teste123
     */
    public function autenticarCobranca(array $conexao): array
    {
        $xApiKey  = $conexao['x_api_key'] ?? '';
        $username = $conexao['username'] ?? '';
        $password = $conexao['password'] ?? '';

        if (empty($xApiKey)) {
            throw new \Exception('Sicredi: x-api-key (token do portal) não configurado.');
        }
        if (empty($username) || empty($password)) {
            throw new \Exception('Sicredi: Username (beneficiário+cooperativa) e Password (código de acesso) são obrigatórios.');
        }

        $authUrl = $this->getAuthUrl($conexao);

        $body = [
            'grant_type' => 'password',
            'username'   => $username,
            'password'   => $password,
            'scope'      => 'cobranca',
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'x-api-key: ' . $xApiKey,
            'context: COBRANCA',
        ];

        try {
            LogSistema::debug('SicrediAPI', 'auth_request', 'Autenticando na API Sicredi Cobrança', [
                'url' => $authUrl,
                'username' => $username,
                'ambiente' => $this->getAmbiente($conexao),
            ]);
        } catch (\Exception $e) {}

        $response = $this->httpRequest($authUrl, 'POST', $headers, $body, $conexao, true);

        if (empty($response['access_token'])) {
            $erro = $response['error_description'] ?? $response['error'] ?? 'Token não retornado';
            try {
                LogSistema::error('SicrediAPI', 'auth_erro', 'Falha na autenticação Sicredi Cobrança', [
                    'erro' => $erro,
                ]);
            } catch (\Exception $e) {}
            throw new \Exception("Falha na autenticação Sicredi Cobrança: {$erro}");
        }

        // Guardar refresh_token para renovação
        if (!empty($response['refresh_token'])) {
            $this->refreshToken = $response['refresh_token'];
            $this->refreshTokenExpiresAt = time() + ($response['refresh_expires_in'] ?? 1800);
        }

        try {
            LogSistema::info('SicrediAPI', 'auth_ok', 'Autenticação Sicredi Cobrança OK', [
                'expires_in' => $response['expires_in'] ?? 300,
            ]);
        } catch (\Exception $e) {}

        return [
            'access_token' => $response['access_token'],
            'expires_in'   => $response['expires_in'] ?? 300,
            'token_type'   => $response['token_type'] ?? 'Bearer',
        ];
    }

    private function getTokenAndHeaders(array $conexao): array
    {
        $token = $this->getAccessToken($conexao);
        return [$token, $this->sicrediHeaders($token, $conexao)];
    }

    // ========== Incluir Boleto ==========

    /**
     * Traduz payload formato Sicoob -> formato Sicredi e envia.
     * 
     * Campos Sicredi obrigatórios no body:
     *   tipoCobranca, codigoBeneficiario, pagador{tipoPessoa, documento, nome},
     *   especieDocumento, valor, dataVencimento
     * 
     * Campos Sicredi nos headers:
     *   cooperativa, posto
     */
    public function incluirBoleto(array $conexao, array $boleto): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';

        // Determinar tipo de pessoa pelo CPF/CNPJ
        $cpfCnpj = $boleto['pagador']['numeroCpfCnpj'] ?? '';
        $tipoPessoa = strlen(preg_replace('/\D/', '', $cpfCnpj)) > 11
            ? 'PESSOA_JURIDICA'
            : 'PESSOA_FISICA';

        // Mapear espécie de documento Sicoob -> Sicredi
        $especieMap = [
            'DM' => 'DUPLICATA_MERCANTIL_INDICACAO',
            'DR' => 'DUPLICATA_RURAL',
            'NP' => 'NOTA_PROMISSORIA',
            'NPR' => 'NOTA_PROMISSORIA_RURAL',
            'NS' => 'NOTA_SEGUROS',
            'RC' => 'RECIBO',
            'LC' => 'LETRA_CAMBIO',
            'ND' => 'NOTA_DEBITO',
            'DS' => 'DUPLICATA_SERVICO_INDICACAO',
            'OUT' => 'OUTROS',
            'BP' => 'BOLETO_PROPOSTA',
            'CC' => 'CARTAO_CREDITO',
        ];
        $especieSicoob = $boleto['codigoEspecieDocumento'] ?? 'DM';
        $especieSicredi = $especieMap[$especieSicoob] ?? 'OUTROS';

        // Determinar tipo cobrança
        $tipoCobranca = 'NORMAL';
        if (!empty($boleto['codigoCadastrarPIX']) && (int)$boleto['codigoCadastrarPIX'] === 1) {
            $tipoCobranca = 'HIBRIDO';
        }

        // Montar payload Sicredi
        $payload = [
            'tipoCobranca'        => $tipoCobranca,
            'codigoBeneficiario'  => $codigoBeneficiario,
            'especieDocumento'    => $especieSicredi,
            'valor'               => (float)($boleto['valor'] ?? 0),
            'dataVencimento'      => $boleto['dataVencimento'] ?? '',
            'dataEmissao'         => $boleto['dataEmissao'] ?? date('Y-m-d'),
            'seuNumero'           => $boleto['seuNumero'] ?? '',
            'pagador' => [
                'tipoPessoa' => $tipoPessoa,
                'documento'  => preg_replace('/\D/', '', $cpfCnpj),
                'nome'       => substr($boleto['pagador']['nome'] ?? '', 0, 40),
            ],
        ];

        // NossoNumero (opcional — Sicredi gera automaticamente se omitido)
        if (!empty($boleto['nossoNumero'])) {
            $payload['nossoNumero'] = (string)$boleto['nossoNumero'];
        }

        // Endereço do pagador (opcional, mas pode ser obrigatório dependendo do convênio)
        if (!empty($boleto['pagador']['endereco'])) {
            $payload['pagador']['endereco'] = substr($boleto['pagador']['endereco'], 0, 40);
        }
        if (!empty($boleto['pagador']['cidade'])) {
            $payload['pagador']['cidade'] = substr($boleto['pagador']['cidade'], 0, 25);
        }
        if (!empty($boleto['pagador']['uf'])) {
            $payload['pagador']['uf'] = strtoupper(substr($boleto['pagador']['uf'], 0, 2));
        }
        if (!empty($boleto['pagador']['cep'])) {
            $payload['pagador']['cep'] = preg_replace('/\D/', '', $boleto['pagador']['cep']);
        }
        if (!empty($boleto['pagador']['telefone'])) {
            $payload['pagador']['telefone'] = preg_replace('/\D/', '', $boleto['pagador']['telefone']);
        }
        if (!empty($boleto['pagador']['email'])) {
            $payload['pagador']['email'] = substr($boleto['pagador']['email'], 0, 40);
        }

        // Desconto
        if (!empty($boleto['tipoDesconto']) && (int)$boleto['tipoDesconto'] > 0) {
            if (!empty($boleto['dataPrimeiroDesconto'])) {
                $payload['descontoData1'] = $boleto['dataPrimeiroDesconto'];
            }
            if (!empty($boleto['valorPrimeiroDesconto'])) {
                $payload['descontoValor1'] = (float)$boleto['valorPrimeiroDesconto'];
            }
        }

        // Juros
        if (!empty($boleto['tipoJurosMora']) && (int)$boleto['tipoJurosMora'] < 3) {
            if (!empty($boleto['valorJurosMora'])) {
                $payload['jurosValor'] = (float)$boleto['valorJurosMora'];
            }
        }

        // Multa
        if (!empty($boleto['tipoMulta']) && (int)$boleto['tipoMulta'] > 0) {
            if (!empty($boleto['valorMulta'])) {
                $payload['multaValor'] = (float)$boleto['valorMulta'];
            }
            if (!empty($boleto['dataMulta'])) {
                $payload['multaData'] = $boleto['dataMulta'];
            }
        }

        // Mensagens/Instruções (máximo 5 linhas de 40 chars)
        if (!empty($boleto['mensagensInstrucao']) && is_array($boleto['mensagensInstrucao'])) {
            foreach ($boleto['mensagensInstrucao'] as $i => $msg) {
                $num = $i + 1;
                if ($num <= 5) {
                    $payload["informativo{$num}"] = substr($msg, 0, 40);
                }
            }
        }

        // Limpar campos vazios
        $payload = $this->limparPayload($payload);

        try {
            LogSistema::info('SicrediAPI', 'incluir_boleto', 'Enviando boleto para API Sicredi', [
                'valor' => $payload['valor'] ?? 0,
                'pagador' => $payload['pagador']['nome'] ?? '',
                'vencimento' => $payload['dataVencimento'] ?? '',
                'tipo' => $tipoCobranca,
                'beneficiario' => $codigoBeneficiario,
            ]);
        } catch (\Exception $e) {}

        $baseUrl = $this->getBaseUrl($conexao);
        $response = $this->httpRequest(
            $baseUrl . '/boletos',
            'POST',
            $headers,
            $payload,
            $conexao
        );

        try {
            LogSistema::info('SicrediAPI', 'boleto_incluido', 'Boleto incluído com sucesso na API Sicredi', [
                'nosso_numero' => $response['nossoNumero'] ?? null,
                'linha_digitavel' => $response['linhaDigitavel'] ?? null,
            ]);
        } catch (\Exception $e) {}

        // Após incluir, buscar PDF automaticamente
        $pdfBoleto = null;
        if (!empty($response['nossoNumero'])) {
            try {
                $pdfData = $this->imprimirBoleto($conexao, (string)$response['nossoNumero']);
                $pdfBoleto = $pdfData['pdf_boleto'] ?? null;
            } catch (\Exception $e) {
                try {
                    LogSistema::warning('SicrediAPI', 'pdf_erro', 'Erro ao obter PDF do boleto Sicredi', [
                        'nosso_numero' => $response['nossoNumero'],
                        'erro' => $e->getMessage(),
                    ]);
                } catch (\Exception $ex) {}
            }
        }

        return [
            'nosso_numero'   => $response['nossoNumero'] ?? null,
            'seu_numero'     => $response['seuNumero'] ?? $boleto['seuNumero'] ?? null,
            'codigo_barras'  => $response['codigoBarras'] ?? null,
            'linha_digitavel' => $response['linhaDigitavel'] ?? null,
            'qr_code'        => $response['qrCode'] ?? null,
            'pdf_boleto'     => $pdfBoleto,
            'dados_completos' => $response,
        ];
    }

    // ========== Impressão de Boleto (segunda via / PDF) ==========

    /**
     * Imprime boleto (PDF) pelo nosso número.
     */
    private function imprimirBoleto(array $conexao, string $nossoNumero): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $baseUrl = $this->getBaseUrl($conexao);
        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';

        $params = [
            'codigoBeneficiario' => $codigoBeneficiario,
        ];

        $response = $this->httpRequest(
            $baseUrl . "/boletos/{$nossoNumero}/impressao",
            'GET',
            $headers,
            $params,
            $conexao
        );

        return [
            'pdf_boleto'     => $response['arquivo'] ?? $response['pdf'] ?? $response['pdfBoleto'] ?? null,
            'dados_completos' => $response,
        ];
    }

    public function segundaViaBoleto(array $conexao, array $filtros): array
    {
        $nossoNumero = $filtros['nosso_numero'] ?? '';
        if (empty($nossoNumero)) {
            throw new \Exception('Sicredi: Nosso Número é obrigatório para segunda via.');
        }

        return $this->imprimirBoleto($conexao, (string)$nossoNumero);
    }

    // ========== Consultar Boleto ==========

    public function consultarBoleto(array $conexao, array $filtros): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $baseUrl = $this->getBaseUrl($conexao);
        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';

        $nossoNumero = $filtros['nosso_numero'] ?? '';
        if (empty($nossoNumero)) {
            throw new \Exception('Sicredi: Nosso Número é obrigatório para consulta de boleto.');
        }

        $params = [
            'codigoBeneficiario' => $codigoBeneficiario,
        ];

        $response = $this->httpRequest(
            $baseUrl . "/boletos/{$nossoNumero}",
            'GET',
            $headers,
            $params,
            $conexao
        );

        return $response;
    }

    // ========== Listar Boletos do Pagador ==========

    public function listarBoletosPagador(array $conexao, string $cpfCnpj, array $filtros = []): array
    {
        // Sicredi não tem endpoint específico para listar por pagador.
        // Usa-se consulta de liquidados por dia como alternativa.
        try {
            LogSistema::warning('SicrediAPI', 'listar_pagador', 'Sicredi não possui endpoint de listagem por pagador. Use consulta de liquidados.', [
                'cpf_cnpj' => substr($cpfCnpj, 0, 6) . '***',
            ]);
        } catch (\Exception $e) {}

        return [];
    }

    // ========== Alterar Boleto ==========

    /**
     * Comandos de instrução para alteração de boleto.
     * Sicredi separa cada tipo de alteração em endpoints diferentes.
     * 
     * Tipos suportados via $dados['tipo_alteracao']:
     *   - vencimento: altera data de vencimento
     *   - desconto: altera valor de desconto
     *   - data_desconto: altera data de desconto
     *   - juros: altera juros
     *   - seu_numero: altera seu número
     */
    public function alterarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $baseUrl = $this->getBaseUrl($conexao);
        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';
        $tipoAlteracao = $dados['tipo_alteracao'] ?? 'vencimento';

        $payload = [
            'codigoBeneficiario' => $codigoBeneficiario,
        ];

        $endpoint = '';

        switch ($tipoAlteracao) {
            case 'vencimento':
                $endpoint = "/boletos/{$nossoNumero}/alteracao-vencimento";
                $payload['dataVencimento'] = $dados['dataVencimento'] ?? $dados['data_vencimento'] ?? '';
                break;

            case 'desconto':
                $endpoint = "/boletos/{$nossoNumero}/alteracao-desconto";
                if (!empty($dados['valorDesconto'])) $payload['valorDesconto'] = (float)$dados['valorDesconto'];
                if (!empty($dados['tipoDesconto'])) $payload['tipoDesconto'] = $dados['tipoDesconto'];
                break;

            case 'data_desconto':
                $endpoint = "/boletos/{$nossoNumero}/alteracao-data-desconto";
                if (!empty($dados['dataDesconto'])) $payload['dataDesconto'] = $dados['dataDesconto'];
                break;

            case 'juros':
                $endpoint = "/boletos/{$nossoNumero}/alteracao-juros";
                if (!empty($dados['valorJuros'])) $payload['valorJuros'] = (float)$dados['valorJuros'];
                if (!empty($dados['tipoJuros'])) $payload['tipoJuros'] = $dados['tipoJuros'];
                break;

            case 'seu_numero':
                $endpoint = "/boletos/{$nossoNumero}/alteracao-seu-numero";
                $payload['seuNumero'] = $dados['seuNumero'] ?? $dados['seu_numero'] ?? '';
                break;

            default:
                throw new \Exception("Sicredi: Tipo de alteração não suportado: {$tipoAlteracao}");
        }

        $payload = $this->limparPayload($payload);

        try {
            LogSistema::info('SicrediAPI', 'alterar_boleto', "Alterando boleto Sicredi ({$tipoAlteracao})", [
                'nosso_numero' => $nossoNumero,
                'tipo' => $tipoAlteracao,
            ]);
        } catch (\Exception $e) {}

        $this->httpRequest(
            $baseUrl . $endpoint,
            'PATCH',
            $headers,
            $payload,
            $conexao
        );

        return $this->lastHttpCode === 200 || $this->lastHttpCode === 204;
    }

    // ========== Baixar Boleto ==========

    public function baixarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $baseUrl = $this->getBaseUrl($conexao);
        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';

        $payload = [
            'codigoBeneficiario' => $codigoBeneficiario,
        ];

        try {
            LogSistema::info('SicrediAPI', 'baixar_boleto', 'Comandando baixa de boleto Sicredi', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}

        $this->httpRequest(
            $baseUrl . "/boletos/{$nossoNumero}/baixa",
            'PATCH',
            $headers,
            $payload,
            $conexao
        );

        return $this->lastHttpCode === 200 || $this->lastHttpCode === 204;
    }

    // ========== Protesto ==========

    public function protestarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        try {
            LogSistema::warning('SicrediAPI', 'protesto_nao_suportado',
                'Protesto via API não disponível no Sicredi. Solicite diretamente no Internet Banking.', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}
        return false;
    }

    public function cancelarProtesto(array $conexao, int $nossoNumero, array $dados): bool
    {
        return false;
    }

    public function desistirProtesto(array $conexao, int $nossoNumero, array $dados): bool
    {
        return false;
    }

    // ========== Negativação ==========

    public function negativarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        try {
            LogSistema::warning('SicrediAPI', 'negativacao_nao_suportada',
                'Negativação via API não disponível no Sicredi.', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}
        return false;
    }

    public function cancelarNegativacao(array $conexao, int $nossoNumero, array $dados): bool
    {
        return false;
    }

    public function baixarNegativacao(array $conexao, int $nossoNumero, array $dados): bool
    {
        return false;
    }

    // ========== Movimentação (Liquidados por dia) ==========

    /**
     * Consulta boletos liquidados em uma data específica.
     */
    public function solicitarMovimentacao(array $conexao, array $dados): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $baseUrl = $this->getBaseUrl($conexao);
        $codigoBeneficiario = $conexao['codigo_beneficiario'] ?? '';

        $dataLiquidacao = $dados['data_inicio'] ?? date('Y-m-d');

        $params = [
            'codigoBeneficiario' => $codigoBeneficiario,
            'dataLiquidacao'     => $dataLiquidacao,
        ];

        try {
            LogSistema::debug('SicrediAPI', 'liquidados_dia', 'Consultando boletos liquidados no dia', [
                'data' => $dataLiquidacao,
                'beneficiario' => $codigoBeneficiario,
            ]);
        } catch (\Exception $e) {}

        $response = $this->httpRequest(
            $baseUrl . '/boletos/liquidados/dia',
            'GET',
            $headers,
            $params,
            $conexao
        );

        return $response;
    }

    // ========== Utilitário: limpar payload ==========

    /**
     * Remove propriedades com valor null, string vazia ou array vazio do payload.
     */
    private function limparPayload(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sub = $this->limparPayload($value);
                if (!empty($sub)) {
                    $cleaned[$key] = $sub;
                }
            } elseif ($value !== null && $value !== '' && $value !== false) {
                $cleaned[$key] = $value;
            } elseif ($value === false) {
                $cleaned[$key] = $value;
            } elseif (is_numeric($value)) {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }
}
