<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Boleto;
use App\Models\BoletoHistorico;
use App\Models\ConexaoBancaria;

/**
 * Controller para receber webhooks de boletos do banco.
 * 
 * REGRAS CRÍTICAS SICOOB:
 * - DEVE responder HTTP 200, 201 ou 204 (202/302 causam FALHA na validação)
 * - Recebe 2 tipos de notificação:
 *   1. Validação de URL: { "idWebhook": X, "validacaoWebhook": true }
 *   2. Pagamento: { "idWebhook": X, "tipoMovimento": 7, "dados": {...} }
 * - Datas em UTC ("Z"), converter para America/Sao_Paulo
 * - Baixa operacional ≠ liquidação final (intenção de pagamento)
 * 
 * Rota: POST /webhook/boletos/{conexaoId} (PÚBLICA, sem auth middleware)
 */
class BoletoWebhookController extends Controller
{
    private $boletoModel;
    private $historicoModel;
    private $conexaoModel;

    public function __construct()
    {
        parent::__construct();
        $this->boletoModel = new Boleto();
        $this->historicoModel = new BoletoHistorico();
        $this->conexaoModel = new ConexaoBancaria();
    }

    /**
     * Recebe notificações do banco (webhook).
     * DEVE retornar 200 imediatamente.
     */
    public function receber(Request $request, Response $response, $conexaoId)
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true);

        if (!$payload || !isset($payload['idWebhook'])) {
            return $response->json(['status' => 'ignored'], 200);
        }

        $idWebhookBanco = (int)$payload['idWebhook'];

        try {
            $db = Database::getInstance();

            // Verificar se a conexão existe
            $conexao = $this->conexaoModel->findById((int)$conexaoId);
            if (!$conexao) {
                $this->logWebhook($db, null, $idWebhookBanco, $payload, 'Conexão não encontrada: ' . $conexaoId, true);
                return $response->json(['status' => 'ok'], 200);
            }

            // Buscar webhook local vinculado
            $webhookLocal = $this->buscarWebhookLocal($db, $idWebhookBanco, (int)$conexaoId);

            // 1. Notificação de VALIDAÇÃO de URL
            if (!empty($payload['validacaoWebhook'])) {
                $this->processarValidacao($db, $webhookLocal, $idWebhookBanco, (int)$conexaoId, $conexao['empresa_id']);
                $this->logWebhook($db, $webhookLocal['id'] ?? null, $idWebhookBanco, $payload, null, true);
                return $response->json(['status' => 'ok'], 200);
            }

            // 2. Notificação de PAGAMENTO (tipoMovimento = 7)
            if (isset($payload['tipoMovimento']) && (int)$payload['tipoMovimento'] === 7 && isset($payload['dados'])) {
                $this->processarPagamento($db, $webhookLocal, $conexao, $payload);
                return $response->json(['status' => 'ok'], 200);
            }

            // Tipo desconhecido, mas responder 200 mesmo assim
            $this->logWebhook($db, $webhookLocal['id'] ?? null, $idWebhookBanco, $payload, 'Tipo de notificação não reconhecido');
            return $response->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            // Mesmo com erro, responder 200 para evitar que o banco desative o webhook
            error_log("[BoletoWebhook] Erro: " . $e->getMessage() . " | Payload: " . substr($rawBody, 0, 1000));
            return $response->json(['status' => 'error'], 200);
        }
    }

    /**
     * Processa a validação da URL do webhook pelo banco.
     */
    private function processarValidacao($db, ?array $webhookLocal, int $idWebhookBanco, int $conexaoId, int $empresaId)
    {
        if ($webhookLocal) {
            // Atualizar como validado
            $db->query(
                "UPDATE boletos_webhooks SET validado = 1, updated_at = NOW() WHERE id = ?",
                [$webhookLocal['id']]
            );
        } else {
            // Criar registro local se não existir
            $db->query(
                "INSERT INTO boletos_webhooks (conexao_bancaria_id, empresa_id, webhook_id_banco, url_callback, validado, ativo) 
                 VALUES (?, ?, ?, 'auto-registrado', 1, 1)",
                [$conexaoId, $empresaId, $idWebhookBanco]
            );
        }
    }

    /**
     * Processa notificação de pagamento (baixa operacional).
     * 
     * ATENÇÃO: Baixa operacional ≠ liquidação final.
     * É o registro da intenção de pagamento realizada.
     */
    private function processarPagamento($db, ?array $webhookLocal, array $conexao, array $payload)
    {
        $dados = $payload['dados'];
        $idWebhookBanco = (int)$payload['idWebhook'];

        // Converter datas UTC para Brasília
        $dataHoraBaixa = $this->utcParaBrasilia($dados['dataHoraSituacaoBaixa'] ?? null);

        // Log do webhook recebido
        $logId = $this->logWebhook($db, $webhookLocal['id'] ?? null, $idWebhookBanco, $payload);

        // Verificar se é cancelamento de baixa
        $cancelamentoBaixa = $dados['cancelamentoBaixa'] ?? false;

        // Buscar boleto pelo nossoNumero
        $nossoNumero = $dados['nossoNumero'] ?? null;
        if (!$nossoNumero) {
            $this->atualizarLogErro($db, $logId, 'nossoNumero ausente no payload');
            return;
        }

        $boleto = $this->boletoModel->findByNossoNumero(
            $nossoNumero,
            $conexao['empresa_id'],
            $conexao['id']
        );

        if (!$boleto) {
            // Boleto não encontrado localmente - criar registro
            $this->criarBoletoDoWebhook($db, $conexao, $dados, $dataHoraBaixa, $cancelamentoBaixa);
            $this->atualizarLogProcessado($db, $logId);
            return;
        }

        // Processar: atualizar boleto existente
        if ($cancelamentoBaixa) {
            // Cancelamento de baixa: reverter status
            $this->boletoModel->atualizar($boleto['id'], [
                'situacao' => 'em_aberto',
                'data_pagamento' => null,
                'valor_recebido' => null,
            ]);
            $this->historicoModel->registrar(
                $boleto['id'],
                'cancelamento',
                'Cancelamento de baixa operacional recebido via webhook',
                ['webhook_payload' => $dados],
                null
            );
        } else {
            // Baixa operacional: marcar como liquidado
            $updateData = [
                'situacao' => 'liquidado',
                'valor_recebido' => $dados['valorPagamento'] ?? $dados['valorBoleto'] ?? $boleto['valor'],
                'data_pagamento' => $dataHoraBaixa ? substr($dataHoraBaixa, 0, 10) : date('Y-m-d'),
                'situacao_banco' => json_encode([
                    'codigo_canal_pagamento' => $dados['codigoCanalPagamento'] ?? null,
                    'banco_recebedor' => $dados['codigoBancoRecebedor'] ?? null,
                    'agencia_recebedora' => $dados['codigoAgenciaRecebedora'] ?? null,
                    'baixa_contingencia' => $dados['baixaRealizadaEmContigencia'] ?? false,
                    'data_hora_baixa_utc' => $dados['dataHoraSituacaoBaixa'] ?? null,
                ]),
                'codigo_barras' => $dados['codigoBarrasBoleto'] ?? $boleto['codigo_barras'],
            ];
            $this->boletoModel->atualizar($boleto['id'], $updateData);

            $this->historicoModel->registrar(
                $boleto['id'],
                'liquidacao',
                sprintf(
                    'Baixa operacional via webhook. Valor: R$ %s | Pagador: %s | Data: %s',
                    number_format($updateData['valor_recebido'], 2, ',', '.'),
                    $dados['nomePagador'] ?? '-',
                    $dataHoraBaixa ?? '-'
                ),
                ['webhook_payload' => $dados],
                null
            );

            // Atualizar Conta a Receber vinculada, se existir
            if ($boleto['conta_receber_id']) {
                $this->baixarContaReceber($db, $boleto['conta_receber_id'], $updateData['valor_recebido'], $updateData['data_pagamento']);
            }
        }

        // Atualizar contadores do webhook
        if ($webhookLocal) {
            $db->query(
                "UPDATE boletos_webhooks SET ultimo_recebimento = NOW(), total_recebimentos = total_recebimentos + 1 WHERE id = ?",
                [$webhookLocal['id']]
            );
        }

        $this->atualizarLogProcessado($db, $logId);
    }

    /**
     * Cria um boleto local a partir de dados recebidos pelo webhook.
     */
    private function criarBoletoDoWebhook($db, array $conexao, array $dados, ?string $dataHoraBaixa, bool $cancelamento)
    {
        $situacao = $cancelamento ? 'em_aberto' : 'liquidado';

        $boletoData = [
            'empresa_id' => $conexao['empresa_id'],
            'conexao_bancaria_id' => $conexao['id'],
            'banco' => $conexao['banco'],
            'nosso_numero' => $dados['nossoNumero'] ?? null,
            'seu_numero' => $dados['seuNumero'] ?? null,
            'codigo_barras' => $dados['codigoBarrasBoleto'] ?? null,
            'valor' => $dados['valorBoleto'] ?? 0,
            'valor_recebido' => $cancelamento ? null : ($dados['valorPagamento'] ?? null),
            'data_emissao' => $dados['dataEmissao'] ?? date('Y-m-d'),
            'data_vencimento' => $dados['dataVencimento'] ?? date('Y-m-d'),
            'data_pagamento' => $cancelamento ? null : ($dataHoraBaixa ? substr($dataHoraBaixa, 0, 10) : date('Y-m-d')),
            'pagador_cpf_cnpj' => $dados['cpfCnpjPagador'] ?? null,
            'pagador_nome' => $dados['nomePagador'] ?? 'Pagador via Webhook',
            'situacao' => $situacao,
            'situacao_banco' => json_encode($dados),
        ];

        $boletoId = $this->boletoModel->criar($boletoData);
        if ($boletoId) {
            $this->historicoModel->registrar(
                $boletoId,
                $cancelamento ? 'cancelamento' : 'liquidacao',
                'Boleto criado automaticamente a partir de notificação webhook',
                ['dados_webhook' => $dados],
                null
            );
        }
    }

    /**
     * Baixa automática na Conta a Receber vinculada.
     */
    private function baixarContaReceber($db, int $contaReceberId, float $valor, string $dataPagamento)
    {
        try {
            $db->query(
                "UPDATE contas_receber SET status = 'pago', valor_recebido = ?, data_recebimento = ?, updated_at = NOW() WHERE id = ? AND status != 'pago'",
                [$valor, $dataPagamento, $contaReceberId]
            );
        } catch (\Exception $e) {
            error_log("[BoletoWebhook] Erro ao baixar conta receber #{$contaReceberId}: " . $e->getMessage());
        }
    }

    // ========== Helpers ==========

    /**
     * Busca webhook local pelo idWebhook do banco.
     */
    private function buscarWebhookLocal($db, int $idWebhookBanco, int $conexaoId): ?array
    {
        $rows = $db->query(
            "SELECT * FROM boletos_webhooks WHERE webhook_id_banco = ? AND conexao_bancaria_id = ? LIMIT 1",
            [$idWebhookBanco, $conexaoId]
        );
        return $rows[0] ?? null;
    }

    /**
     * Registra log do webhook recebido.
     * @return int ID do log inserido
     */
    private function logWebhook($db, ?int $webhookId, int $idWebhookBanco, array $payload, ?string $erro = null, bool $isValidacao = false): int
    {
        $dados = $payload['dados'] ?? [];

        $db->query(
            "INSERT INTO boletos_webhook_logs 
             (webhook_id, webhook_id_banco, tipo_movimento, nosso_numero, codigo_barras, 
              valor_boleto, valor_pagamento, cpf_cnpj_pagador, nome_pagador, 
              data_vencimento, data_hora_baixa, cancelamento_baixa, is_validacao,
              payload_raw, erro_processamento, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $webhookId ?: 0,
                $idWebhookBanco,
                $payload['tipoMovimento'] ?? null,
                $dados['nossoNumero'] ?? null,
                $dados['codigoBarrasBoleto'] ?? null,
                $dados['valorBoleto'] ?? null,
                $dados['valorPagamento'] ?? null,
                $dados['cpfCnpjPagador'] ?? null,
                $dados['nomePagador'] ?? null,
                $dados['dataVencimento'] ?? null,
                $this->utcParaBrasilia($dados['dataHoraSituacaoBaixa'] ?? null),
                ($dados['cancelamentoBaixa'] ?? false) ? 1 : 0,
                $isValidacao ? 1 : 0,
                json_encode($payload),
                $erro,
            ]
        );

        return (int)$db->lastInsertId();
    }

    private function atualizarLogProcessado($db, int $logId)
    {
        $db->query(
            "UPDATE boletos_webhook_logs SET processado = 1, processado_em = NOW() WHERE id = ?",
            [$logId]
        );
    }

    private function atualizarLogErro($db, int $logId, string $erro)
    {
        $db->query(
            "UPDATE boletos_webhook_logs SET erro_processamento = ? WHERE id = ?",
            [$erro, $logId]
        );
    }

    /**
     * Converte data UTC (sufixo "Z") para horário de Brasília (UTC-3).
     */
    private function utcParaBrasilia(?string $dateStr): ?string
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
}
