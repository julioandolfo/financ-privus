<?php
namespace Includes\Services;

use App\Models\LogSistema;

/**
 * Integração com a API do Sicoob (Conta Corrente V4).
 * 
 * Autenticação: mTLS (certificado digital PFX/PEM) + OAuth2 client_credentials
 * O Sicoob NÃO utiliza client_secret — a autenticação é feita via certificado mTLS.
 * 
 * Documentação oficial: https://developers.sicoob.com.br/portal/apis
 * Postman collection: API Conta Corrente Sicoob
 * 
 * Base URL: https://api.sicoob.com.br/conta-corrente/v4
 * Auth URL: https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token
 * 
 * Endpoints:
 *   GET /saldo?numeroContaCorrente=XXX                                          -> saldo
 *   GET /extrato/{mes}/{ano}?numeroContaCorrente=XXX&diaInicial=X&diaFinal=Y    -> extrato
 *   POST /transferencias                                                         -> transferência
 * 
 * Scopes (enviar no token):
 *   cco_consulta           -> consulta saldo e extrato
 *   cco_transferencias     -> transferências
 *   openid                 -> retorna id_token junto com access_token
 * 
 * Headers obrigatórios em todas as requisições:
 *   Authorization: Bearer {access_token}
 *   client_id: {client_id}
 *   Content-Type: application/json
 *   Accept: application/json
 * 
 * Resposta de saldo:
 *   { "resultado": { "saldo": 0, "saldoLimite": 0 } }
 * 
 * Resposta de extrato:
 *   { "saldoAtual": "...", "saldoBloqueado": "...", "transacoes": [ { "tipo", "valor", "data", "descricao", ... } ] }
 */
class SicoobBankService extends AbstractBankService
{
    /** Base URL da API de Conta Corrente V4 */
    private $baseUrl = 'https://api.sicoob.com.br/conta-corrente/v4';
    
    /** URL de autenticação OAuth2 */
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
     * 
     * Scopes: cco_transferencias cco_consulta openid
     * Token expira em 300 segundos (5 minutos).
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
            'scope' => 'cco_transferencias cco_consulta openid'
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
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
            'scope' => $response['scope'] ?? '',
            'id_token' => $response['id_token'] ?? null
        ];
    }

    /**
     * Obtém saldo da conta corrente.
     * 
     * GET https://api.sicoob.com.br/conta-corrente/v4/saldo?numeroContaCorrente={numero}
     * 
     * Resposta: { "resultado": { "saldo": 0, "saldoLimite": 0 } }
     */
    public function getSaldo(array $conexao): array
    {
        $numeroConta = $this->getNumeroContaLimpo($conexao);

        if (empty($numeroConta)) {
            throw new \Exception('Número da conta corrente não configurado para consulta de saldo.');
        }

        $token = $this->getAccessToken($conexao);
        $clientId = $conexao['client_id'] ?? '';

        $url = $this->baseUrl . '/saldo';
        $params = ['numeroContaCorrente' => $numeroConta];

        $urlCompleta = $url . '?numeroContaCorrente=' . $numeroConta;
        
        try {
            \App\Models\LogSistema::info('SicoobAPI', 'getSaldo_request', 'Requisição de saldo enviada', [
                'url_completa' => $urlCompleta,
                'numeroConta_enviado' => $numeroConta,
                'banco_conta_id_original' => $conexao['banco_conta_id'] ?? 'VAZIO',
                'client_id' => substr($clientId, 0, 10) . '...',
                'identificacao' => $conexao['identificacao'] ?? '',
            ]);
        } catch (\Exception $e) {}

        $response = $this->httpRequest(
            $url,
            'GET',
            $this->sicoobHeaders($token, $clientId),
            $params,
            $conexao
        );

        // Log COMPLETO da resposta
        try {
            $responseJson = is_array($response) ? json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : substr((string)$response, 0, 1000);
            \App\Models\LogSistema::info('SicoobAPI', 'getSaldo_response', 'Resposta completa da API de saldo', [
                'numeroConta' => $numeroConta,
                'http_code' => $this->lastHttpCode ?? null,
                'response_completa' => $responseJson,
            ]);
        } catch (\Exception $e) {}

        // Resposta oficial: { "resultado": { "saldo": 0, "saldoLimite": 0 } }
        $resultado = $response['resultado'] ?? $response;
        
        $saldoEndpoint = (float) ($resultado['saldo'] ?? 0);
        $saldoBloqueado = (float) ($resultado['saldoBloqueado'] ?? $response['saldoBloqueado'] ?? 0);
        $saldoLimite = (float) ($resultado['saldoLimite'] ?? $response['saldoLimite'] ?? 0);
        
        // Estratégia para obter saldo mais preciso via extrato:
        // 1) Busca do dia 1 até +5 dias no futuro (captura transações agendadas em feriados/fds)
        // 2) Se estiver nos primeiros dias do mês sem transações, consulta mês anterior
        // 3) Identifica a data de referência real do saldo (última transação processada)
        $saldoExtrato = null;
        $saldoAnterior = null;
        $saldoBloqueadoExtrato = null;
        $saldoLimiteExtrato = null;
        $ultimaTransacaoData = null;
        $totalTransacoes = 0;
        $dataReferencia = null;
        try {
            $mesAtual = date('m');
            $anoAtual = date('Y');
            $diaAtual = (int) date('d');
            
            // Estender até +5 dias para capturar transações agendadas (feriados, fins de semana)
            $ultimoDiaMes = (int) date('t');
            $diaFinal = min($diaAtual + 5, $ultimoDiaMes);
            
            LogSistema::debug('SicoobAPI', 'getSaldo_datas_extrato', 'Datas usadas para consulta de extrato', [
                'numeroConta' => $numeroConta,
                'timezone_php' => date_default_timezone_get(),
                'datetime_php' => date('Y-m-d H:i:s'),
                'dia_atual' => $diaAtual,
                'dia_final_consulta' => $diaFinal,
                'mes' => $mesAtual,
                'ano' => $anoAtual,
                'dias_futuro' => $diaFinal - $diaAtual,
            ]);
            
            $urlExtrato = $this->baseUrl . "/extrato/{$mesAtual}/{$anoAtual}";
            $paramsExtrato = [
                'numeroContaCorrente' => $numeroConta,
                'diaInicial' => '01',
                'diaFinal' => str_pad($diaFinal, 2, '0', STR_PAD_LEFT),
            ];
            
            $responseExtrato = $this->httpRequest(
                $urlExtrato,
                'GET',
                $this->sicoobHeaders($token, $clientId),
                $paramsExtrato,
                $conexao
            );
            
            if (is_array($responseExtrato)) {
                $extData = $responseExtrato;
                if (isset($responseExtrato['resultado']) && is_array($responseExtrato['resultado'])) {
                    $extData = $responseExtrato['resultado'];
                }
                
                $saldoExtrato = isset($extData['saldoAtual']) ? (float)$extData['saldoAtual'] : null;
                $saldoAnterior = isset($extData['saldoAnterior']) ? (float)$extData['saldoAnterior'] : null;
                $saldoBloqueadoExtrato = isset($extData['saldoBloqueado']) ? (float)$extData['saldoBloqueado'] : null;
                $saldoLimiteExtrato = isset($extData['saldoLimite']) ? (float)$extData['saldoLimite'] : null;
                
                $transacoes = $extData['transacoes'] ?? [];
                $totalTransacoes = count($transacoes);
                
                // Separar transações passadas/hoje vs futuras (agendamentos)
                // IMPORTANTE: O saldoAtual do Sicoob NÃO inclui transações futuras.
                // Ele representa o saldo de fechamento do último dia útil processado.
                // As transações futuras são listadas como pendentes/agendadas.
                $hoje = date('Y-m-d');
                $somaFuturosCredito = 0.0;
                $somaFuturosDebito = 0.0;
                $txFuturas = 0;
                $txPassadas = 0;
                $maxDataPassada = null;
                $maxDataFutura = null;
                
                foreach ($transacoes as $tx) {
                    $txData = $tx['data'] ?? null;
                    $txValor = (float) ($tx['valor'] ?? 0);
                    $txTipo = strtoupper($tx['tipo'] ?? '');
                    
                    $txDataDia = $txData ? substr($txData, 0, 10) : null;
                    
                    if ($txDataDia && $txDataDia > $hoje) {
                        $txFuturas++;
                        if ($txTipo === 'CREDITO') {
                            $somaFuturosCredito += $txValor;
                        } else {
                            $somaFuturosDebito += $txValor;
                        }
                        if ($maxDataFutura === null || $txData > $maxDataFutura) {
                            $maxDataFutura = $txData;
                        }
                    } else {
                        $txPassadas++;
                        if ($txDataDia && ($maxDataPassada === null || $txData > $maxDataPassada)) {
                            $maxDataPassada = $txData;
                        }
                    }
                }
                
                $ultimaTransacaoData = $maxDataPassada ?? $maxDataFutura;
                $dataReferencia = $maxDataPassada ?? $hoje;
                
                // Calcular saldo projetado: saldoAtual - débitos_futuros + créditos_futuros
                // (o que vai ficar na conta APÓS os agendamentos executarem)
                $saldoProjetadoCalc = null;
                if ($saldoExtrato !== null && $txFuturas > 0) {
                    $saldoProjetadoCalc = $saldoExtrato - $somaFuturosDebito + $somaFuturosCredito;
                }
                
                LogSistema::info('SicoobAPI', 'getSaldo_separacao_futuras', 'Separação transações passadas vs futuras', [
                    'numeroConta' => $numeroConta,
                    'hoje' => $hoje,
                    'total_transacoes' => $totalTransacoes,
                    'tx_passadas_hoje' => $txPassadas,
                    'tx_futuras_agendadas' => $txFuturas,
                    'soma_futuros_credito' => $somaFuturosCredito,
                    'soma_futuros_debito' => $somaFuturosDebito,
                    'saldoAtual_extrato' => $saldoExtrato,
                    'saldo_projetado_apos_agendamentos' => $saldoProjetadoCalc,
                    'ultima_tx_passada' => $maxDataPassada,
                    'ultima_tx_futura' => $maxDataFutura,
                    'nota' => 'saldoAtual NAO inclui futuras. Projetado = saldoAtual - debitos_futuros + creditos_futuros.',
                ]);
                
                // NÃO alterar saldoExtrato — ele já é o saldo correto do último dia útil
            }
            
            // Se estamos nos primeiros dias do mês e não tem transações,
            // buscar o mês anterior para ter uma referência
            if ($totalTransacoes === 0 && $diaAtual <= 5) {
                $mesAnterior = date('m', strtotime('first day of last month'));
                $anoAnterior = date('Y', strtotime('first day of last month'));
                $ultimoDiaMesAnterior = date('t', strtotime('last day of last month'));
                
                $urlExtratoAnterior = $this->baseUrl . "/extrato/{$mesAnterior}/{$anoAnterior}";
                $paramsExtratoAnterior = [
                    'numeroContaCorrente' => $numeroConta,
                    'diaInicial' => '01',
                    'diaFinal' => $ultimoDiaMesAnterior,
                ];
                
                $responseExtratoAnterior = $this->httpRequest(
                    $urlExtratoAnterior,
                    'GET',
                    $this->sicoobHeaders($token, $clientId),
                    $paramsExtratoAnterior,
                    $conexao
                );
                
                if (is_array($responseExtratoAnterior)) {
                    $extDataAnt = $responseExtratoAnterior;
                    if (isset($responseExtratoAnterior['resultado']) && is_array($responseExtratoAnterior['resultado'])) {
                        $extDataAnt = $responseExtratoAnterior['resultado'];
                    }
                    $saldoMesAnterior = isset($extDataAnt['saldoAtual']) ? (float)$extDataAnt['saldoAtual'] : null;
                    
                    if ($saldoMesAnterior !== null) {
                        $saldoExtrato = $saldoMesAnterior;
                    }
                    
                    // Buscar última transação do mês anterior
                    $txAnt = $extDataAnt['transacoes'] ?? [];
                    if (count($txAnt) > 0) {
                        $maxDataAnt = null;
                        foreach ($txAnt as $tx) {
                            $txData = $tx['data'] ?? null;
                            if ($txData && ($maxDataAnt === null || $txData > $maxDataAnt)) {
                                $maxDataAnt = $txData;
                            }
                        }
                        if ($maxDataAnt) {
                            $ultimaTransacaoData = $maxDataAnt;
                            $dataReferencia = $maxDataAnt;
                        }
                        $totalTransacoes += count($txAnt);
                    }
                    
                    LogSistema::debug('SicoobAPI', 'getSaldo_extrato_mes_anterior', 'Consultou mês anterior (sem transações recentes)', [
                        'numeroConta' => $numeroConta,
                        'periodo' => "01/{$mesAnterior}/{$anoAnterior} a {$ultimoDiaMesAnterior}/{$mesAnterior}/{$anoAnterior}",
                        'saldoAtual_mes_anterior' => $saldoMesAnterior,
                        'total_transacoes_ant' => count($txAnt),
                    ]);
                }
            }
            
            $extratoResponseJson = is_array($responseExtrato) ? json_encode($responseExtrato, JSON_UNESCAPED_UNICODE) : 'not_array';
            
            LogSistema::info('SicoobAPI', 'getSaldo_extrato_cruzamento', 'Cruzamento saldo endpoint vs extrato', [
                'numeroConta' => $numeroConta,
                'identificacao' => $conexao['identificacao'] ?? '',
                'endpoint_saldo' => $saldoEndpoint,
                'endpoint_saldo_limite' => $saldoLimite,
                'extrato_saldoAtual' => $saldoExtrato,
                'extrato_saldoAnterior' => $saldoAnterior,
                'extrato_saldoBloqueado' => $saldoBloqueadoExtrato,
                'extrato_saldoLimite' => $saldoLimiteExtrato,
                'extrato_total_transacoes' => $totalTransacoes,
                'extrato_ultima_transacao' => $ultimaTransacaoData,
                'extrato_data_referencia' => $dataReferencia,
                'extrato_periodo_consultado' => "01/{$mesAtual}/{$anoAtual} a {$diaFinal}/{$mesAtual}/{$anoAtual}",
                'extrato_http_code' => $this->lastHttpCode ?? null,
                'extrato_response_raw' => substr($extratoResponseJson, 0, 2000),
            ]);
        } catch (\Exception $e) {
            LogSistema::warning('SicoobAPI', 'getSaldo_extrato_erro', 'Erro ao cruzar saldo com extrato', [
                'erro' => $e->getMessage(),
            ]);
        }
        
        // saldoExtrato = saldo reportado pela API (último dia útil processado)
        // NÃO subtraímos agendamentos futuros — só mostra quando o banco processar de fato
        $saldoFinal = ($saldoExtrato !== null) ? $saldoExtrato : $saldoEndpoint;
        
        // Usar limite do extrato se disponível
        if ($saldoLimiteExtrato !== null && $saldoLimiteExtrato > 0) {
            $saldoLimite = $saldoLimiteExtrato;
        }
        
        if (!$dataReferencia) {
            $dataReferencia = date('Y-m-d');
        }
        
        // Saldo próprio = saldo da API menos o limite (dinheiro real na conta)
        $saldoContabil = round($saldoFinal - $saldoLimite, 2);
        
        // Saldo projetado (informativo) = o que vai ficar após os agendamentos
        $txFuturasCount = $txFuturas ?? 0;
        $saldoProjetado = $saldoFinal;
        if (isset($saldoProjetadoCalc) && $saldoProjetadoCalc !== null) {
            $saldoProjetado = round($saldoProjetadoCalc, 2);
        }
        $saldoProjetadoContabil = round($saldoProjetado - $saldoLimite, 2);
        
        try {
            LogSistema::info('SicoobAPI', 'getSaldo_resultado', 'Saldo final', [
                'numeroConta' => $numeroConta,
                'identificacao' => $conexao['identificacao'] ?? '',
                'saldo_disponivel' => $saldoFinal,
                'saldo_proprio' => $saldoContabil,
                'saldo_limite' => $saldoLimite,
                'saldo_bloqueado' => $saldoBloqueado,
                'saldo_projetado_disponivel' => $saldoProjetado,
                'saldo_projetado_proprio' => $saldoProjetadoContabil,
                'tx_futuras' => $txFuturasCount,
                'data_referencia' => $dataReferencia,
                'fonte' => ($saldoExtrato !== null) ? 'EXTRATO (saldoAtual)' : 'ENDPOINT (/saldo)',
            ]);
        } catch (\Exception $e) {}
        
        return [
            'saldo' => $saldoFinal,
            'saldo_contabil' => $saldoContabil,
            'saldo_bloqueado' => $saldoBloqueado,
            'saldo_limite' => $saldoLimite,
            'saldo_projetado' => $saldoProjetado,
            'saldo_projetado_contabil' => $saldoProjetadoContabil,
            'atualizado_em' => date('Y-m-d\TH:i:s'),
            'data_referencia' => $dataReferencia,
            'ultima_transacao' => $ultimaTransacaoData,
            'total_transacoes' => $totalTransacoes,
            'tx_futuras' => $txFuturasCount,
            'soma_futuros_debito' => $somaFuturosDebito ?? 0,
            'soma_futuros_credito' => $somaFuturosCredito ?? 0,
            'moeda' => 'BRL',
        ];
    }

    /**
     * Obtém extrato da conta corrente.
     * 
     * GET https://api.sicoob.com.br/conta-corrente/v4/extrato/{mes}/{ano}
     *     ?numeroContaCorrente={numero}&diaInicial=01&diaFinal=31&agruparCNAB=true
     * 
     * Resposta: { "saldoAtual", "saldoBloqueado", "saldoLimite", "saldoAnterior",
     *             "transacoes": [{ "tipo", "valor", "data", "dataLote", "descricao",
     *                              "numeroDocumento", "cpfCnpj", "descInfComplementar" }] }
     * 
     * Limitação: consulta limitada a 3 meses anteriores.
     */
    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        $numeroConta = $this->getNumeroContaLimpo($conexao);

        if (empty($numeroConta)) {
            throw new \Exception('Número da conta corrente não configurado para consulta de extrato.');
        }

        $token = $this->getAccessToken($conexao);
        $clientId = $conexao['client_id'] ?? '';

        $mesInicio = (int) date('m', strtotime($dataInicio));
        $anoInicio = (int) date('Y', strtotime($dataInicio));
        $diaInicio = (int) date('d', strtotime($dataInicio));
        $diaFim = (int) date('d', strtotime($dataFim));
        $mesFim = (int) date('m', strtotime($dataFim));
        $anoFim = (int) date('Y', strtotime($dataFim));

        $this->logError('getTransacoes - Parâmetros', [
            'numeroConta' => $numeroConta,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'mesInicio' => $mesInicio,
            'anoInicio' => $anoInicio,
            'diaInicio' => $diaInicio,
            'mesFim' => $mesFim,
            'anoFim' => $anoFim,
            'diaFim' => $diaFim
        ]);

        $transacoes = [];
        $debugMeses = [];

        $currentYear = $anoInicio;
        $currentMonth = $mesInicio;

        while ($currentYear < $anoFim || ($currentYear === $anoFim && $currentMonth <= $mesFim)) {
            // Mês com zero-padding conforme documentação Sicoob
            $mesFormatado = str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
            
            $params = [
                'numeroContaCorrente' => $numeroConta
            ];

            if ($currentYear === $anoInicio && $currentMonth === $mesInicio) {
                $params['diaInicial'] = str_pad($diaInicio, 2, '0', STR_PAD_LEFT);
            }
            if ($currentYear === $anoFim && $currentMonth === $mesFim) {
                $params['diaFinal'] = str_pad($diaFim, 2, '0', STR_PAD_LEFT);
            }

            $url = $this->baseUrl . "/extrato/{$mesFormatado}/{$currentYear}";
            $mesDebug = [
                'mes' => "{$mesFormatado}/{$currentYear}", 
                'url' => $url, 
                'params' => $params,
                'url_completa' => $url . '?' . http_build_query($params)
            ];

            try {
                $response = $this->httpRequest(
                    $url,
                    'GET',
                    $this->sicoobHeaders($token, $clientId),
                    $params,
                    $conexao
                );

                // Capturar resposta bruta para debug
                $rawTruncado = $this->lastRawResponse ? substr($this->lastRawResponse, 0, 2000) : 'vazio';
                $mesDebug['http_code'] = $this->lastHttpCode;
                $mesDebug['response_keys'] = is_array($response) ? array_keys($response) : 'not_array';
                $mesDebug['response_raw_preview'] = $rawTruncado;
                $mesDebug['saldoAtual'] = $response['saldoAtual'] ?? null;
                $mesDebug['saldoAnterior'] = $response['saldoAnterior'] ?? null;

                // Tentar encontrar transações em diferentes locais da resposta
                $items = [];
                
                // Formato 1: transacoes no root
                if (!empty($response['transacoes']) && is_array($response['transacoes'])) {
                    $items = $response['transacoes'];
                    $mesDebug['formato'] = 'root.transacoes';
                }
                // Formato 2: resultado.transacoes
                elseif (!empty($response['resultado']['transacoes']) && is_array($response['resultado']['transacoes'])) {
                    $items = $response['resultado']['transacoes'];
                    $mesDebug['formato'] = 'resultado.transacoes';
                }
                // Formato 3: resultado é array direto de transações
                elseif (!empty($response['resultado']) && is_array($response['resultado']) && isset($response['resultado'][0])) {
                    $items = $response['resultado'];
                    $mesDebug['formato'] = 'resultado[]';
                }
                // Formato 4: response é array direto
                elseif (is_array($response) && isset($response[0]) && isset($response[0]['valor'])) {
                    $items = $response;
                    $mesDebug['formato'] = 'root[]';
                }
                else {
                    $mesDebug['formato'] = 'nenhum_encontrado';
                    // Listar todas as chaves em cada nível para debug
                    $mesDebug['estrutura'] = $this->mapearEstrutura($response);
                }

                $mesDebug['transacoes_count'] = count($items);

                if (count($items) > 0) {
                    $mesDebug['primeira_transacao_raw'] = $items[0];
                    $transacoes = array_merge($transacoes, $this->normalizarTransacoes($items));
                }

                $this->logError("Extrato {$mesFormatado}/{$currentYear}", $mesDebug);

            } catch (\Exception $e) {
                $mesDebug['erro'] = $e->getMessage();
                $mesDebug['http_code'] = $this->lastHttpCode ?? null;
                $mesDebug['response_raw_preview'] = $this->lastRawResponse ? substr($this->lastRawResponse, 0, 1000) : 'vazio';
                $this->logError("Erro ao buscar extrato {$mesFormatado}/{$currentYear}", $mesDebug);
            }

            $debugMeses[] = $mesDebug;

            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        $this->logError('getTransacoes - Resultado final', [
            'total_transacoes' => count($transacoes),
            'meses_consultados' => count($debugMeses)
        ]);

        $this->lastDebug = $debugMeses;

        return $transacoes;
    }

    /** @var array Debug da última consulta de transações */
    public $lastDebug = [];
    
    /**
     * Mapeia estrutura de um array (para debug)
     */
    private function mapearEstrutura($data, $depth = 0): array
    {
        $result = [];
        if (!is_array($data) || $depth > 2) {
            return ['type' => gettype($data), 'value' => is_scalar($data) ? $data : '...'];
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = '[array:' . count($value) . '] keys=' . implode(',', array_slice(array_keys($value), 0, 5));
            } else {
                $result[$key] = gettype($value) . ':' . substr((string)$value, 0, 50);
            }
        }
        return $result;
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
                'placeholder' => 'Ex: 2300885 (somente números, sem pontos ou hífens)',
                'help' => 'Número da conta corrente somente dígitos (sem pontos, traços ou dígito verificador separado)'
            ],
            [
                'name' => 'cert_pfx',
                'label' => 'Certificado Digital (.pfx)',
                'type' => 'file',
                'required' => false,
                'accept' => '.pfx,.p12',
                'help' => 'Upload do arquivo .pfx ou .p12 (certificado ICP-Brasil emitido para o cooperado). Se preferir PEM, use os campos abaixo.'
            ],
            [
                'name' => 'cert_password',
                'label' => 'Senha do Certificado',
                'type' => 'password',
                'required' => false,
                'placeholder' => 'Senha do certificado digital'
            ],
            [
                'name' => 'cert_pem',
                'label' => 'Certificado PEM / CER (alternativa ao PFX)',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => '-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----',
                'help' => 'Chave pública do certificado no formato .PEM ou .CER'
            ],
            [
                'name' => 'key_pem',
                'label' => 'Chave Privada .KEY (alternativa ao PFX)',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => '-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----',
                'help' => 'Chave privada correspondente ao certificado (.KEY)'
            ],
            [
                'name' => 'ambiente',
                'label' => 'Ambiente',
                'type' => 'select',
                'required' => true,
                'options' => ['producao' => 'Produção', 'sandbox' => 'Sandbox (testes)'],
                'default' => 'producao'
            ],
            // === Campos de Cobrança Bancária (Boletos) ===
            [
                'name' => 'numero_cliente_banco',
                'label' => 'Número do Cliente (Cobrança)',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Ex: 25546454',
                'help' => 'Número que identifica o beneficiário na plataforma de cobrança da cooperativa. Necessário para emitir boletos.',
                'section' => 'cobranca'
            ],
            [
                'name' => 'codigo_modalidade_cobranca',
                'label' => 'Modalidade de Cobrança',
                'type' => 'select',
                'required' => false,
                'options' => [
                    '1' => '1 - Simples com Registro',
                    '3' => '3 - Caucionada',
                    '4' => '4 - Vinculada',
                    '5' => '5 - Carnê de Pagamentos',
                    '6' => '6 - Indexada',
                    '8' => '8 - Cobrança Conta Capital',
                ],
                'default' => '1',
                'help' => 'Modalidade do boleto. Padrão: Simples com Registro.',
                'section' => 'cobranca'
            ],
            [
                'name' => 'conta_corrente_cobranca',
                'label' => 'Conta Corrente (Cobrança)',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Ex: 2300885 (se diferente da conta do extrato)',
                'help' => 'Conta para crédito da liquidação de boletos. Se vazio, usa a conta do extrato.',
                'section' => 'cobranca'
            ],
        ];
    }

    // =========================================================================
    // Métodos auxiliares privados
    // =========================================================================

    /**
     * Monta headers padrão para requisições à API do Sicoob.
     * Conforme documentação oficial, TODAS as requisições exigem:
     *   - Authorization: Bearer {access_token}
     *   - client_id: {client_id}
     *   - Content-Type: application/json
     *   - Accept: application/json
     */
    private function sicoobHeaders(string $token, string $clientId): array
    {
        return [
            'Authorization: Bearer ' . $token,
            'client_id: ' . $clientId,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    /**
     * Obtém número da conta - somente dígitos (sem pontos, hífens ou espaços).
     * A API do Sicoob espera o número da conta como string numérica.
     */
    private function getNumeroContaLimpo(array $conexao): string
    {
        $conta = $conexao['banco_conta_id'] ?? $conexao['identificacao'] ?? '';
        // Remove tudo que não for dígito
        return preg_replace('/[^0-9]/', '', $conta);
    }

    /**
     * Verifica se a conexão possui certificado configurado (PFX ou PEM).
     */
    private function temCertificado(array $conexao): bool
    {
        if (!empty($conexao['cert_pfx'])) {
            return true;
        }
        if (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) {
            return true;
        }
        return false;
    }

    /**
     * Normaliza array de transações do Sicoob para o formato padrão do sistema.
     * 
     * Formato oficial da transação Sicoob:
     *   { "tipo", "valor", "data", "dataLote", "descricao",
     *     "numeroDocumento", "cpfCnpj", "descInfComplementar" }
     */
    private function normalizarTransacoes(array $items): array
    {
        $transacoes = [];

        foreach ($items as $txn) {
            if (!is_array($txn)) continue;

            $valor = (float) ($txn['valor'] ?? 0);
            $descricao = trim($txn['descricao'] ?? 'Transação Sicoob');
            $infoComplementar = trim($txn['descInfComplementar'] ?? '');
            $cpfCnpj = trim($txn['cpfCnpj'] ?? '');
            $data = $txn['data'] ?? date('Y-m-d');
            $tipo = strtoupper(trim($txn['tipo'] ?? ''));

            // Sicoob retorna tipo como: DEBITO, CREDITO, D, C
            $tipoTransacao = 'credito';
            if ($tipo === 'D' || $tipo === 'DEBITO' || $tipo === 'DÉBITO' || $valor < 0) {
                $tipoTransacao = 'debito';
            }

            // Normalizar data (remover horário T00:00 se presente)
            if (strlen($data) > 10) {
                $data = substr($data, 0, 10);
            }

            // Enriquecer descrição com informação complementar quando disponível
            // A API do Sicoob retorna descInfComplementar com múltiplas linhas:
            // Ex: "Recebimento PIX\nMAD MAIS MADEIRAS LTDA\n10.785.708/0001-59\nPIX"
            $descricaoCompleta = $descricao;
            if ($infoComplementar) {
                // Separar linhas da info complementar para extrair dados estruturados
                $linhas = preg_split('/[\r\n]+/', $infoComplementar);
                $linhas = array_map('trim', $linhas);
                $linhas = array_filter($linhas);
                
                // Montar descrição rica: descrição do banco + todas as linhas complementares
                $partes = [$descricao];
                foreach ($linhas as $linha) {
                    // Evitar repetir info que já está na descrição
                    if (stripos($descricao, $linha) === false) {
                        $partes[] = $linha;
                    }
                }
                $descricaoCompleta = implode(' | ', $partes);
            }
            if ($cpfCnpj && stripos($descricaoCompleta, $cpfCnpj) === false) {
                $descricaoCompleta .= ' | CNPJ/CPF: ' . $cpfCnpj;
            }

            // Gerar ID único usando transactionId se disponível
            $transacaoId = $txn['transactionId'] ?? $txn['numeroDocumento'] ?? uniqid();

            $transacoes[] = [
                'banco_transacao_id' => 'SCB-' . $transacaoId,
                'data_transacao' => $data,
                'descricao_original' => $descricaoCompleta,
                'valor' => abs($valor),
                'tipo' => $tipoTransacao,
                'metodo_pagamento' => $this->identificarMetodoPagamento($descricao),
                'saldo_apos' => null,
                'origem' => 'sicoob',
                'dados_extras' => [
                    'tipo_lancamento' => $txn['tipo'] ?? '',
                    'transaction_id' => $txn['transactionId'] ?? '',
                    'numero_documento' => $txn['numeroDocumento'] ?? '',
                    'cpf_cnpj' => $cpfCnpj,
                    'data_lote' => $txn['dataLote'] ?? '',
                    'info_complementar' => $infoComplementar,
                    'descricao_banco' => $descricao
                ]
            ];
        }

        return $transacoes;
    }
}
