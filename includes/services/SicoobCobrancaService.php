<?php
namespace Includes\Services;

use App\Models\LogSistema;

/**
 * Integração com a API de Cobrança Bancária Sicoob V3.
 * 
 * Base URL: https://api.sicoob.com.br/cobranca-bancaria/v3
 * Auth: mTLS + OAuth2 client_credentials
 * 
 * Scopes disponíveis:
 *   boletos_inclusao, boletos_consulta, boletos_alteracao
 *   webhooks_alteracao, webhooks_consulta, webhooks_inclusao
 * 
 * REGRAS IMPORTANTES DA API:
 * - Propriedades opcionais NÃO podem ser enviadas com valor nulo/vazio (erro 400)
 * - Campo "numeroContratoCobranca" NÃO deve ser enviado a menos que orientado
 * - Datas retornadas pela API estão em UTC (sufixo "Z"), converter para UTC-3 (Brasília)
 * - Webhook DEVE responder com HTTP 200, 201 ou 204 (202 ou 302 causam falha)
 * 
 * Rate Limits:
 *   POST Incluir Boletos: 5/s
 *   GET Consultar boleto: 20/s
 *   PATCH Alterar boleto: 5/s
 *   Movimentações: 10/s
 *   Demais endpoints: 20/s
 */

class SicoobCobrancaService extends AbstractBankService implements CobrancaApiInterface
{
    private $baseUrl = 'https://api.sicoob.com.br/cobranca-bancaria/v3';
    private $authUrl = 'https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token';

    /** @var string|null Token separado para webhooks */
    private $webhookToken = null;
    private $webhookTokenExpiresAt = 0;

    public function getBancoNome(): string { return 'sicoob'; }
    public function getBancoLabel(): string { return 'Sicoob Cobrança'; }

    // ========== BankApiInterface stubs (herdados do AbstractBankService) ==========
    public function getSaldo(array $conexao): array { return []; }
    public function getTransacoes(array $conexao, string $di, string $df): array { return []; }
    public function testarConexao(array $conexao): bool { return false; }
    public function getCamposConfiguracao(): array { return []; }

    // ========== Utilitário: remover propriedades nulas/vazias ==========

    /**
     * Remove propriedades com valor null, string vazia ou array vazio do payload.
     * A API Sicoob V3 retorna erro 400 se propriedades opcionais forem enviadas vazias/nulas.
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

    /**
     * Converte data/hora UTC (formato "Z") para horário de Brasília (UTC-3).
     */
    public static function utcParaBrasilia(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;
        try {
            $dt = new \DateTime($dateStr, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $dateStr;
        }
    }

    // ========== Autenticação ==========

    public function autenticar(array $conexao): array
    {
        return $this->autenticarCobranca($conexao);
    }

    /**
     * Autentica com scopes de boleto.
     */
    public function autenticarCobranca(array $conexao): array
    {
        $clientId = $conexao['client_id'] ?? '';
        if (empty($clientId)) {
            throw new \Exception('Client ID do Sicoob não configurado.');
        }

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'scope' => 'boletos_inclusao boletos_consulta boletos_alteracao'
        ];

        $response = $this->httpRequest(
            $this->authUrl, 'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            $body, $conexao, true
        );

        if (empty($response['access_token'])) {
            $erro = $response['error_description'] ?? $response['error'] ?? 'Token não retornado';
            try {
                LogSistema::error('SicoobAPI', 'auth_erro', 'Falha na autenticação Sicoob Cobrança', [
                    'erro' => $erro, 'client_id' => substr($clientId, 0, 8) . '***',
                ]);
            } catch (\Exception $e) {}
            throw new \Exception("Falha na autenticação Sicoob Cobrança: {$erro}");
        }

        return [
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'] ?? 300,
            'token_type' => $response['token_type'] ?? 'Bearer',
        ];
    }

    /**
     * Autentica com scopes de webhook (separados dos scopes de boleto).
     */
    public function autenticarWebhook(array $conexao): string
    {
        if ($this->webhookToken && time() < ($this->webhookTokenExpiresAt - 60)) {
            return $this->webhookToken;
        }

        $clientId = $conexao['client_id'] ?? '';
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'scope' => 'webhooks_inclusao webhooks_consulta webhooks_alteracao'
        ];

        $response = $this->httpRequest(
            $this->authUrl, 'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            $body, $conexao, true
        );

        if (empty($response['access_token'])) {
            throw new \Exception('Falha na autenticação Sicoob Webhook');
        }

        $this->webhookToken = $response['access_token'];
        $this->webhookTokenExpiresAt = time() + ($response['expires_in'] ?? 300);

        return $this->webhookToken;
    }

    // ========== Headers padrão Sicoob ==========

    private function sicoobHeaders(string $token, string $clientId): array
    {
        return [
            'Authorization: Bearer ' . $token,
            'client_id: ' . $clientId,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    private function getTokenAndHeaders(array $conexao): array
    {
        $token = $this->getAccessToken($conexao);
        $clientId = $conexao['client_id'] ?? '';
        return [$token, $this->sicoobHeaders($token, $clientId)];
    }

    private function getWebhookHeaders(array $conexao): array
    {
        $token = $this->autenticarWebhook($conexao);
        $clientId = $conexao['client_id'] ?? '';
        return $this->sicoobHeaders($token, $clientId);
    }

    // ========== Boletos ==========

    public function incluirBoleto(array $conexao, array $boleto): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        // Limpar propriedades nulas/vazias (exigência da API Sicoob V3)
        $boletoLimpo = $this->limparPayload($boleto);

        // Remover numeroContratoCobranca se não for explicitamente necessário
        unset($boletoLimpo['numeroContratoCobranca']);

        try {
            LogSistema::debug('SicoobAPI', 'incluir_boleto', 'Enviando boleto para API Sicoob', [
                'valor' => $boletoLimpo['valor'] ?? 0,
                'pagador' => $boletoLimpo['pagador']['nome'] ?? '',
                'vencimento' => $boletoLimpo['dataVencimento'] ?? '',
            ]);
        } catch (\Exception $e) {}

        $response = $this->httpRequest(
            $this->baseUrl . '/boletos', 'POST',
            $headers, $boletoLimpo, $conexao
        );

        $resultado = $response['resultado'] ?? $response;

        // Se resposta é array de resultados (batch), pegar o primeiro
        if (isset($resultado[0]) && is_array($resultado[0])) {
            $resultado = $resultado[0]['resultado'] ?? $resultado[0];
        }

        try {
            LogSistema::info('SicoobAPI', 'boleto_incluido', 'Boleto incluído com sucesso na API Sicoob', [
                'nosso_numero' => $resultado['nossoNumero'] ?? null,
                'codigo_barras' => $resultado['codigoBarras'] ?? null,
            ]);
        } catch (\Exception $e) {}

        return [
            'nosso_numero' => $resultado['nossoNumero'] ?? null,
            'seu_numero' => $resultado['seuNumero'] ?? null,
            'codigo_barras' => $resultado['codigoBarras'] ?? null,
            'linha_digitavel' => $resultado['linhaDigitavel'] ?? null,
            'qr_code' => $resultado['qrCode'] ?? null,
            'pdf_boleto' => $resultado['pdfBoleto'] ?? null,
            'dados_completos' => $resultado,
        ];
    }

    public function consultarBoleto(array $conexao, array $filtros): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $params = [
            'numeroCliente' => $conexao['numero_cliente_banco'] ?? $conexao['banco_conta_id'] ?? '',
            'codigoModalidade' => $conexao['codigo_modalidade_cobranca'] ?? 1,
        ];

        if (!empty($filtros['nosso_numero'])) {
            $params['nossoNumero'] = $filtros['nosso_numero'];
        }
        if (!empty($filtros['linha_digitavel'])) {
            $params['linhaDigitavel'] = $filtros['linha_digitavel'];
        }
        if (!empty($filtros['codigo_barras'])) {
            $params['codigoBarras'] = $filtros['codigo_barras'];
        }

        $response = $this->httpRequest(
            $this->baseUrl . '/boletos', 'GET',
            $headers, $params, $conexao
        );

        return $response['resultado'] ?? $response;
    }

    public function listarBoletosPagador(array $conexao, string $cpfCnpj, array $filtros = []): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $cpfCnpjLimpo = preg_replace('/[^0-9]/', '', $cpfCnpj);

        $params = [
            'numeroCliente' => $conexao['numero_cliente_banco'] ?? '',
        ];
        if (!empty($filtros['situacao'])) {
            $codSituacao = ['em_aberto' => 1, 'baixado' => 2, 'liquidado' => 3];
            $params['codigoSituacao'] = $codSituacao[$filtros['situacao']] ?? $filtros['situacao'];
        }
        if (!empty($filtros['data_inicio'])) $params['dataInicio'] = $filtros['data_inicio'];
        if (!empty($filtros['data_fim'])) $params['dataFim'] = $filtros['data_fim'];

        $response = $this->httpRequest(
            $this->baseUrl . "/pagadores/{$cpfCnpjLimpo}/boletos", 'GET',
            $headers, $params, $conexao
        );

        return $response['resultado'] ?? $response;
    }

    public function alterarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $body = [
            'numeroCliente' => (int)($conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($conexao['codigo_modalidade_cobranca'] ?? 1),
        ];
        $body = array_merge($body, $dados);
        $body = $this->limparPayload($body);

        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}", 'PATCH',
            $headers, $body, $conexao
        );

        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function baixarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $body = [
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ];

        try {
            LogSistema::info('SicoobAPI', 'baixar_boleto', 'Comandando baixa de boleto', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}

        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/baixar", 'POST',
            $headers, $body, $conexao
        );

        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function segundaViaBoleto(array $conexao, array $filtros): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $params = [
            'numeroCliente' => $conexao['numero_cliente_banco'] ?? '',
            'codigoModalidade' => $conexao['codigo_modalidade_cobranca'] ?? 1,
            'gerarPdf' => 'true',
        ];
        if (!empty($filtros['nosso_numero'])) $params['nossoNumero'] = $filtros['nosso_numero'];
        if (!empty($filtros['linha_digitavel'])) $params['linhaDigitavel'] = $filtros['linha_digitavel'];
        if (!empty($filtros['codigo_barras'])) $params['codigoBarras'] = $filtros['codigo_barras'];

        $response = $this->httpRequest(
            $this->baseUrl . '/boletos/segunda-via', 'GET',
            $headers, $params, $conexao
        );

        $resultado = $response['resultado'] ?? $response;
        return [
            'pdf_boleto' => $resultado['pdfBoleto'] ?? null,
            'qr_code' => $resultado['qrCode'] ?? null,
            'dados_completos' => $resultado,
        ];
    }

    /**
     * Consulta faixas de nosso número disponíveis.
     */
    public function consultarFaixasNossoNumero(array $conexao): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);

        $params = [
            'numeroCliente' => $conexao['numero_cliente_banco'] ?? '',
            'codigoModalidade' => $conexao['codigo_modalidade_cobranca'] ?? 1,
        ];

        $response = $this->httpRequest(
            $this->baseUrl . '/boletos/faixas-nosso-numero', 'GET',
            $headers, $params, $conexao
        );

        return $response['resultado'] ?? $response;
    }

    // ========== Protesto ==========

    public function protestarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);

        try {
            LogSistema::warning('SicoobAPI', 'protestar_boleto', 'Enviando boleto para protesto', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}

        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/protestos", 'POST',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function cancelarProtesto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);
        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/protestos", 'PATCH',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function desistirProtesto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);

        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/protestos", 'DELETE',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    // ========== Negativação ==========

    public function negativarBoleto(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);

        try {
            LogSistema::warning('SicoobAPI', 'negativar_boleto', 'Enviando boleto para negativação SERASA', [
                'nosso_numero' => $nossoNumero,
            ]);
        } catch (\Exception $e) {}

        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/negativacoes", 'POST',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function cancelarNegativacao(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);
        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/negativacoes", 'PATCH',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    public function baixarNegativacao(array $conexao, int $nossoNumero, array $dados): bool
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'codigoModalidade' => (int)($dados['codigo_modalidade'] ?? $conexao['codigo_modalidade_cobranca'] ?? 1),
        ]);
        $this->httpRequest(
            $this->baseUrl . "/boletos/{$nossoNumero}/negativacoes", 'DELETE',
            $headers, $body, $conexao
        );
        return $this->lastHttpCode === 204 || $this->lastHttpCode === 200;
    }

    // ========== Movimentação ==========

    /**
     * Solicita movimentação (consultas limitadas a 2 dias, dentro de 1 ano corrido).
     */
    public function solicitarMovimentacao(array $conexao, array $dados): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $body = $this->limparPayload([
            'numeroCliente' => (int)($dados['numero_cliente'] ?? $conexao['numero_cliente_banco'] ?? 0),
            'tipoMovimento' => $dados['tipo_movimento'] ?? 1,
            'dataInicial' => $dados['data_inicio'] ?? date('Y-m-d'),
            'dataFinal' => $dados['data_fim'] ?? date('Y-m-d'),
        ]);
        $response = $this->httpRequest(
            $this->baseUrl . '/boletos/movimentacoes', 'POST',
            $headers, $body, $conexao
        );
        return $response['resultado'] ?? $response;
    }

    /**
     * Consulta situação de solicitação de movimentação.
     */
    public function consultarMovimentacao(array $conexao, int $codigoSolicitacao): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $params = [
            'codigoSolicitacao' => $codigoSolicitacao,
        ];
        $response = $this->httpRequest(
            $this->baseUrl . '/boletos/movimentacoes', 'GET',
            $headers, $params, $conexao
        );
        return $response['resultado'] ?? $response;
    }

    /**
     * Download do arquivo de movimentação.
     */
    public function downloadMovimentacao(array $conexao, int $codigoSolicitacao, int $idArquivo): array
    {
        [$token, $headers] = $this->getTokenAndHeaders($conexao);
        $params = [
            'codigoSolicitacao' => $codigoSolicitacao,
            'idArquivo' => $idArquivo,
        ];
        $response = $this->httpRequest(
            $this->baseUrl . '/boletos/movimentacoes/download', 'GET',
            $headers, $params, $conexao
        );
        return $response;
    }

    // ========== Webhooks ==========

    /**
     * Cadastra um webhook para receber notificações de pagamento.
     * 
     * @param array $conexao Dados da conexão
     * @param string $url URL HTTPS na porta 443 que responda 200/201/204
     * @param string $email Email para notificações
     * @param int $tipoMovimento 7 = Pagamento (baixa operacional)
     * @param int $periodoMovimento 1 = Movimento Atual (D0)
     * @return array Com idWebhook
     */
    public function cadastrarWebhook(array $conexao, string $url, string $email, int $tipoMovimento = 7, int $periodoMovimento = 1): array
    {
        $headers = $this->getWebhookHeaders($conexao);

        $body = [
            'url' => $url,
            'email' => $email,
            'codigoTipoMovimento' => $tipoMovimento,
            'codigoPeriodoMovimento' => $periodoMovimento,
        ];

        try {
            LogSistema::info('SicoobAPI', 'cadastrar_webhook', 'Cadastrando webhook no Sicoob', [
                'url' => $url, 'email' => $email,
                'tipo_movimento' => $tipoMovimento, 'periodo' => $periodoMovimento,
            ]);
        } catch (\Exception $e) {}

        $response = $this->httpRequest(
            $this->baseUrl . '/webhooks', 'POST',
            $headers, $body, $conexao
        );

        try {
            LogSistema::info('SicoobAPI', 'webhook_cadastrado', 'Webhook cadastrado com sucesso', [
                'id_webhook' => $response['idWebhook'] ?? null,
            ]);
        } catch (\Exception $e) {}

        return $response;
    }

    /**
     * Consulta webhooks cadastrados.
     */
    public function consultarWebhooks(array $conexao, ?int $idWebhook = null, ?int $tipoMovimento = null): array
    {
        $headers = $this->getWebhookHeaders($conexao);

        $params = [];
        if ($idWebhook) $params['idWebhook'] = $idWebhook;
        if ($tipoMovimento) $params['codigoTipoMovimento'] = $tipoMovimento;

        $response = $this->httpRequest(
            $this->baseUrl . '/webhooks', 'GET',
            $headers, $params, $conexao
        );

        return $response;
    }

    /**
     * Atualiza URL de um webhook.
     */
    public function atualizarWebhook(array $conexao, int $idWebhook, string $url): bool
    {
        $headers = $this->getWebhookHeaders($conexao);

        $body = ['url' => $url];

        $this->httpRequest(
            $this->baseUrl . "/webhooks/{$idWebhook}", 'PATCH',
            $headers, $body, $conexao
        );

        return $this->lastHttpCode === 200 || $this->lastHttpCode === 204;
    }

    /**
     * Exclui um webhook.
     */
    public function excluirWebhook(array $conexao, int $idWebhook): bool
    {
        $headers = $this->getWebhookHeaders($conexao);

        $this->httpRequest(
            $this->baseUrl . "/webhooks/{$idWebhook}", 'DELETE',
            $headers, null, $conexao
        );

        return $this->lastHttpCode === 200 || $this->lastHttpCode === 204;
    }

    /**
     * Reativar um webhook inativo.
     */
    public function reativarWebhook(array $conexao, int $idWebhook): bool
    {
        $headers = $this->getWebhookHeaders($conexao);

        $this->httpRequest(
            $this->baseUrl . "/webhooks/{$idWebhook}/reativar", 'PATCH',
            $headers, null, $conexao
        );

        return $this->lastHttpCode === 200 || $this->lastHttpCode === 204;
    }

    /**
     * Consulta solicitações de um webhook.
     */
    public function consultarSolicitacoesWebhook(array $conexao, int $idWebhook, string $dataSolicitacao, int $pagina = 1, int $situacao = 3): array
    {
        $headers = $this->getWebhookHeaders($conexao);

        $params = [
            'dataSolicitacao' => $dataSolicitacao,
            'pagina' => $pagina,
            'codigoSolicitacaoSituacao' => $situacao,
        ];

        $response = $this->httpRequest(
            $this->baseUrl . "/webhooks/{$idWebhook}/solicitacoes", 'GET',
            $headers, $params, $conexao
        );

        return $response;
    }
}
