<?php
namespace includes\services;

use App\Models\Configuracao;

/**
 * Serviço de Insights com IA para o Dashboard
 * 
 * Utiliza GPT-4o para análise especializada em gestão financeira empresarial,
 * gerando insights diários baseados em métricas reais do sistema.
 */
class InsightsIAService
{
    private const MODELO = 'gpt-4o';
    private const MAX_TOKENS = 4000;
    private const TEMPERATURA = 0.4; // Mais determinístico para análises precisas
    private const CACHE_HORAS = 12;
    private const CACHE_KEY_PREFIX = 'insights_ia_';

    /**
     * Verifica se o recurso de insights está habilitado
     * Aceita ia.insights_dashboard_habilitado ou ia.insights_dashboard_hab (chave truncada no banco)
     */
    public static function isHabilitado(): bool
    {
        $v = Configuracao::get('ia.insights_dashboard_habilitado', null);
        if ($v === null) {
            $v = Configuracao::get('ia.insights_dashboard_hab', true); // fallback chave truncada
        }
        return (bool) $v;
    }

    /**
     * Gera insights consolidados e por empresa
     * 
     * @param array $payload Dados do dashboard (métricas, alertas, top devedores, etc)
     * @param bool $forcarAtualizacao Ignorar cache
     * @return array ['consolidado' => [...], 'por_empresa' => [...], 'erro' => string|null]
     */
    public static function gerarInsights(array $payload, bool $forcarAtualizacao = false): array
    {
        if (!\includes\services\OpenAIService::isConfigured()) {
            return [
                'consolidado' => ['insights' => [], 'resumo' => 'API OpenAI não configurada.'],
                'por_empresa' => [],
                'erro' => 'Configure a chave API em Configurações → API e IA.'
            ];
        }

        if (!self::isHabilitado()) {
            return [
                'consolidado' => ['insights' => [], 'resumo' => 'Insights do dashboard desabilitados.'],
                'por_empresa' => [],
                'erro' => null
            ];
        }

        $cacheKey = self::CACHE_KEY_PREFIX . md5(json_encode($payload));
        if (!$forcarAtualizacao && ($cached = self::getCache($cacheKey))) {
            return $cached;
        }

        try {
            $resultado = [];
            $consolidado = self::analisarConsolidado($payload);
            $resultado['consolidado'] = $consolidado;

            $porEmpresa = [];
            if (!empty($payload['metricas_por_empresa']) && count($payload['metricas_por_empresa']) > 1) {
                $porEmpresa = self::analisarPorEmpresa($payload);
            }
            $resultado['por_empresa'] = $porEmpresa;
            $resultado['erro'] = null;

            self::setCache($cacheKey, $resultado);
            return $resultado;
        } catch (\Exception $e) {
            return [
                'consolidado' => [
                    'insights' => [],
                    'resumo' => 'Erro ao gerar insights.'
                ],
                'por_empresa' => [],
                'erro' => $e->getMessage()
            ];
        }
    }

    /**
     * Análise consolidada (visão geral)
     */
    private static function analisarConsolidado(array $payload): array
    {
        $prompt = self::montarPromptConsolidado($payload);
        $systemPrompt = self::getSystemPrompt();

        $resposta = \includes\services\OpenAIService::chat(
            $prompt,
            $systemPrompt,
            self::MODELO,
            self::MAX_TOKENS
        );

        return self::parsearRespostaJson($resposta);
    }

    /**
     * Análise por empresa individual
     */
    private static function analisarPorEmpresa(array $payload): array
    {
        $metricasPorEmpresa = $payload['metricas_por_empresa'] ?? [];
        if (empty($metricasPorEmpresa)) {
            return [];
        }

        $empresasParaAnalise = [];
        foreach ($metricasPorEmpresa as $empresaId => $dados) {
            $emp = $dados['empresa'] ?? [];
            $empresasParaAnalise[] = [
                'id' => $empresaId,
                'nome' => $emp['nome'] ?? 'Empresa ' . $empresaId,
                'razao_social' => $emp['razao_social'] ?? '',
                'receitas' => $dados['receitas'] ?? 0,
                'despesas' => $dados['despesas'] ?? 0,
                'lucro_liquido' => $dados['lucro_liquido'] ?? 0,
                'margem_liquida' => $dados['margem_liquida'] ?? 0,
                'margem_ebitda' => $dados['margem_ebitda'] ?? 0,
                'saldo_bancos' => $dados['saldo_bancos'] ?? 0,
                'taxa_inadimplencia' => $dados['taxa_inadimplencia'] ?? 0,
                'ticket_medio' => $dados['ticket_medio'] ?? 0,
                'burn_rate' => $dados['burn_rate'] ?? 0,
                'runway' => $dados['runway'] ?? 0,
                'contas_vencidas' => $dados['contas_vencidas'] ?? 0,
                'valor_vencido' => $dados['valor_vencido'] ?? 0,
                'ponto_equilibrio' => $dados['ponto_equilibrio'] ?? 0,
            ];
        }

        $topDevedoresPorEmpresa = self::extrairTopDevedoresPorEmpresa($payload);
        $prompt = self::montarPromptPorEmpresa($empresasParaAnalise, $topDevedoresPorEmpresa, $payload);
        $systemPrompt = self::getSystemPromptEmpresas();

        try {
            $resposta = \includes\services\OpenAIService::chat(
                $prompt,
                $systemPrompt,
                self::MODELO,
                self::MAX_TOKENS
            );
            return self::parsearRespostaPorEmpresa($resposta);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * System prompt para análise consolidada - especialização financeira
     */
    private static function getSystemPrompt(): string
    {
        return <<<PROMPT
Você é um CFO (Chief Financial Officer) virtual e consultor sênior de gestão financeira empresarial no Brasil, com 25+ anos de experiência em contabilidade, controladoria e análise de indicadores financeiros. Sua especialização inclui:

- Análise de fluxo de caixa, DRE e indicadores de rentabilidade
- Gestão de inadimplência e carteira de recebíveis
- Ponto de equilíbrio, margem de contribuição e burn rate
- Compliance fiscal e tributário brasileiro (PIS, COFINS, IR, CSLL)
- Melhores práticas de tesouraria e liquidez

IMPORTANTE:
- Seja DIRETO e OBJETIVO. Evite rodeios.
- Base suas conclusões EXCLUSIVAMENTE nos dados fornecidos.
- Use nomes reais de clientes/fornecedores quando for relevante para ações concretas.
- Priorize insights ACIONÁVEIS: o que fazer, quando, com quem.
- Cite valores em R$ quando fizer sentido.
- Use termos técnicos corretos (runway, aging, ticket médio, etc).
PROMPT;
    }

    private static function getSystemPromptEmpresas(): string
    {
        return <<<PROMPT
Você é um analista financeiro sênior especializado em gestão multi-empresas. Compare o desempenho das empresas usando os dados fornecidos. Identifique:
- Qual empresa precisa de mais atenção e por quê
- Diferenças significativas em inadimplência, margem, runway
- Empresas que estão puxando ou arrastando o resultado consolidado
Seja objetivo e acionável. Use nomes de clientes quando relevante.
PROMPT;
    }

    /**
     * Monta o prompt consolidado com todos os dados do dashboard
     */
    private static function montarPromptConsolidado(array $payload): string
    {
        $mf = $payload['metricas_financeiras'] ?? [];
        $comp = $payload['comparativo'] ?? [];
        $saude = $payload['saude_financeira'] ?? [];
        $alertas = $payload['alertas'] ?? [];
        $topDevedores = $payload['top_devedores'] ?? [];
        $topDespesas = $payload['top_despesas'] ?? [];
        $topReceitas = $payload['top_receitas'] ?? [];
        $aging = $payload['aging'] ?? ['valores' => [], 'quantidade' => []];
        $recCat = $payload['receitas_por_categoria'] ?? [];
        $despCat = $payload['despesas_por_categoria'] ?? [];
        $evolucao = $payload['evolucao_mensal'] ?? [];
        $fluxo = $payload['fluxo_projetado'] ?? [];
        $vencimentos = $payload['vencimentos_proximos'] ?? [];
        $contasPagar = $payload['contas_pagar'] ?? [];
        $contasReceber = $payload['contas_receber'] ?? [];
        $contasBancarias = $payload['contas_bancarias'] ?? [];

        $texto = "## CONTEXTO\nAnalise os dados financeiros abaixo e gere insights diários para o gestor.\n\n";

        $texto .= "## MÉTRICAS DO PERÍODO ({$payload['metricas_financeiras']['periodo'] ?? 'N/A'})\n";
        $texto .= "- Receitas: R$ " . number_format($mf['receitas'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Despesas: R$ " . number_format($mf['despesas'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Lucro Bruto: R$ " . number_format($mf['lucro_bruto'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Lucro Líquido: R$ " . number_format($mf['lucro_liquido'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Margem Bruta: " . number_format($mf['margem_bruta'] ?? 0, 1) . "% | Margem Líquida: " . number_format($mf['margem_liquida'] ?? 0, 1) . "% | Margem EBITDA: " . number_format($mf['margem_ebitda'] ?? 0, 1) . "%\n";
        $texto .= "- EBITDA: R$ " . number_format($mf['ebitda'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Ponto de Equilíbrio: R$ " . number_format($mf['ponto_equilibrio'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Margem de Contribuição: " . number_format($mf['margem_contribuicao'] ?? 0, 1) . "%\n";
        $texto .= "- Burn Rate (mensal): R$ " . number_format($mf['burn_rate'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Runway (meses de sobrevivência): " . number_format($mf['runway'] ?? 0, 1) . "\n";
        $texto .= "- Ticket Médio: R$ " . number_format($mf['ticket_medio'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Taxa Inadimplência: " . number_format($mf['inadimplencia_taxa'] ?? 0, 1) . "% (R$ " . number_format($mf['inadimplencia_valor'] ?? 0, 2, ',', '.') . " em atraso)\n";
        $texto .= "- Contas vencidas: " . ($mf['contas_vencidas'] ?? 0) . "\n";
        $texto .= "- Saldo em Bancos: R$ " . number_format($contasBancarias['saldo_total'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Contas a Receber (pendentes): R$ " . number_format($contasReceber['valor_a_receber'] ?? 0, 2, ',', '.') . "\n";
        $texto .= "- Contas a Pagar: R$ " . number_format($contasPagar['valor_a_pagar'] ?? 0, 2, ',', '.') . "\n\n";

        $texto .= "## COMPARATIVO MÊS ANTERIOR\n";
        $texto .= "- Variação Receitas: " . number_format($comp['var_receitas'] ?? 0, 1) . "%\n";
        $texto .= "- Variação Despesas: " . number_format($comp['var_despesas'] ?? 0, 1) . "%\n";
        $texto .= "- Variação Lucro: " . number_format($comp['var_lucro'] ?? 0, 1) . "%\n";
        $texto .= "- Variação EBITDA: " . number_format($comp['var_ebitda'] ?? 0, 1) . "%\n\n";

        $texto .= "## SAÚDE FINANCEIRA\n";
        $texto .= "- Score: " . ($saude['score'] ?? 0) . "/100 - " . ($saude['label'] ?? 'N/A') . "\n\n";

        if (!empty($topDevedores)) {
            $texto .= "## TOP DEVEDORES (clientes com valores em atraso)\n";
            foreach ($topDevedores as $nome => $info) {
                $texto .= "- {$nome}: R$ " . number_format($info['valor'] ?? 0, 2, ',', '.') . " (" . ($info['qtd'] ?? 0) . " conta(s))\n";
            }
            $texto .= "\n";
        }

        if (!empty($aging['valores'])) {
            $agingV = $aging['valores'];
            $totalAging = ($agingV['0_30'] ?? 0) + ($agingV['31_60'] ?? 0) + ($agingV['61_90'] ?? 0) + ($agingV['90_plus'] ?? 0);
            if ($totalAging > 0) {
                $texto .= "## AGING DE RECEBÍVEIS (valores vencidos por faixa)\n";
                $texto .= "- 0-30 dias: R$ " . number_format($agingV['0_30'] ?? 0, 2, ',', '.') . "\n";
                $texto .= "- 31-60 dias: R$ " . number_format($agingV['31_60'] ?? 0, 2, ',', '.') . "\n";
                $texto .= "- 61-90 dias: R$ " . number_format($agingV['61_90'] ?? 0, 2, ',', '.') . "\n";
                $texto .= "- 90+ dias: R$ " . number_format($agingV['90_plus'] ?? 0, 2, ',', '.') . "\n\n";
            }
        }

        if (!empty($topDespesas)) {
            $texto .= "## MAIORES DESPESAS DO PERÍODO\n";
            foreach (array_slice($topDespesas, 0, 5) as $d) {
                $texto .= "- " . ($d['descricao'] ?? '') . " | " . ($d['fornecedor'] ?? '') . " | R$ " . number_format($d['valor'] ?? 0, 2, ',', '.') . "\n";
            }
            $texto .= "\n";
        }

        if (!empty($topReceitas)) {
            $texto .= "## MAIORES RECEITAS DO PERÍODO\n";
            foreach (array_slice($topReceitas, 0, 5) as $r) {
                $texto .= "- " . ($r['descricao'] ?? '') . " | " . ($r['cliente'] ?? '') . " | R$ " . number_format($r['valor'] ?? 0, 2, ',', '.') . "\n";
            }
            $texto .= "\n";
        }

        if (!empty($recCat)) {
            $texto .= "## RECEITAS POR CATEGORIA (top 5)\n";
            $i = 0;
            foreach ($recCat as $cat => $val) {
                if ($i++ >= 5) break;
                $texto .= "- {$cat}: R$ " . number_format($val, 2, ',', '.') . "\n";
            }
            $texto .= "\n";
        }

        if (!empty($despCat)) {
            $texto .= "## DESPESAS POR CATEGORIA (top 5)\n";
            $i = 0;
            foreach ($despCat as $cat => $val) {
                if ($i++ >= 5) break;
                $texto .= "- {$cat}: R$ " . number_format($val, 2, ',', '.') . "\n";
            }
            $texto .= "\n";
        }

        if (!empty($alertas)) {
            $texto .= "## ALERTAS DO SISTEMA\n";
            foreach ($alertas as $a) {
                $texto .= "- [{$a['tipo']}] {$a['titulo']}: {$a['mensagem']}\n";
            }
            $texto .= "\n";
        }

        $fluxoNegativo = false;
        if (!empty($fluxo)) {
            foreach ($fluxo as $fp) {
                if (($fp['saldo'] ?? 0) < 0) {
                    $fluxoNegativo = true;
                    $texto .= "## FLUXO DE CAIXA PROJETADO\n";
                    $texto .= "- Atenção: saldo projetado ficará negativo em " . ($fp['dia'] ?? '') . "\n\n";
                    break;
                }
            }
        }

        if (!empty($vencimentos)) {
            $texto .= "## VENCIMENTOS PRÓXIMOS 7 DIAS\n";
            $totalRec = 0;
            $totalPag = 0;
            foreach ($vencimentos as $v) {
                if (($v['tipo'] ?? '') === 'receber') $totalRec += $v['valor'] ?? 0;
                else $totalPag += $v['valor'] ?? 0;
            }
            $texto .= "- A receber: R$ " . number_format($totalRec, 2, ',', '.') . " | A pagar: R$ " . number_format($totalPag, 2, ',', '.') . "\n\n";
        }

        $texto .= "## INSTRUÇÕES DE SAÍDA\n";
        $texto .= "Retorne um JSON válido no seguinte formato (sem markdown, apenas JSON):\n";
        $texto .= "{\n";
        $texto .= "  \"insights\": [\n";
        $texto .= "    {\n";
        $texto .= "      \"tipo\": \"atencao\" | \"sugestao\" | \"oportunidade\" | \"positivo\" | \"tendencia\",\n";
        $texto .= "      \"prioridade\": \"alta\" | \"media\" | \"baixa\",\n";
        $texto .= "      \"titulo\": \"Título curto e direto\",\n";
        $texto .= "      \"mensagem\": \"Mensagem detalhada com dados concretos. Use nomes de clientes quando relevante.\",\n";
        $texto .= "      \"link_sugerido\": \"/contas-receber ou null\"\n";
        $texto .= "    }\n";
        $texto .= "  ],\n";
        $texto .= "  \"resumo\": \"Frase de 1 linha resumindo a situação\"\n";
        $texto .= "}\n\n";
        $texto .= "Gere entre 3 e 8 insights. Priorize atenção e sugestões. Inclua pelo menos 1 insight positivo se houver algo bom. Seja específico com valores e nomes quando possível.";

        return $texto;
    }

    private static function extrairTopDevedoresPorEmpresa(array $payload): array
    {
        // O top_devedores é consolidado; para por empresa precisaríamos de dados separados
        // Por simplicidade retornamos vazio - o payload por empresa já tem métricas
        return [];
    }

    private static function montarPromptPorEmpresa(array $empresas, array $topDevedores, array $payload): string
    {
        $texto = "## MÉTRICAS POR EMPRESA\n\n";
        foreach ($empresas as $e) {
            $texto .= "### {$e['nome']}\n";
            $texto .= "- Receitas: R$ " . number_format($e['receitas'], 2, ',', '.') . " | Despesas: R$ " . number_format($e['despesas'], 2, ',', '.') . "\n";
            $texto .= "- Lucro Líquido: R$ " . number_format($e['lucro_liquido'], 2, ',', '.') . " | Margem Líquida: " . number_format($e['margem_liquida'], 1) . "%\n";
            $texto .= "- Margem EBITDA: " . number_format($e['margem_ebitda'], 1) . "% | Saldo Bancos: R$ " . number_format($e['saldo_bancos'], 2, ',', '.') . "\n";
            $texto .= "- Inadimplência: " . number_format($e['taxa_inadimplencia'], 1) . "% | Valor vencido: R$ " . number_format($e['valor_vencido'], 2, ',', '.') . "\n";
            $texto .= "- Runway: " . number_format($e['runway'], 1) . " meses | Burn: R$ " . number_format($e['burn_rate'], 2, ',', '.') . "\n";
            $texto .= "- Contas vencidas: {$e['contas_vencidas']} | Ponto equilíbrio: R$ " . number_format($e['ponto_equilibrio'], 2, ',', '.') . "\n\n";
        }

        $texto .= "## INSTRUÇÕES\nRetorne JSON: {\"por_empresa\": [{\"empresa_id\": X, \"empresa_nome\": \"...\", \"insights\": [{\"tipo\": \"...\", \"prioridade\": \"...\", \"titulo\": \"...\", \"mensagem\": \"...\"}]}]}";
        return $texto;
    }

    private static function parsearRespostaJson(string $resposta): array
    {
        $resposta = trim($resposta);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $resposta, $m)) {
            $resposta = trim($m[1]);
        }
        $decoded = json_decode($resposta, true);
        if (!$decoded) {
            return [
                'insights' => [
                    [
                        'tipo' => 'info',
                        'prioridade' => 'media',
                        'titulo' => 'Análise indisponível',
                        'mensagem' => 'Não foi possível processar a resposta da IA.',
                        'link_sugerido' => null
                    ]
                ],
                'resumo' => 'Erro ao processar insights.'
            ];
        }
        return [
            'insights' => $decoded['insights'] ?? [],
            'resumo' => $decoded['resumo'] ?? 'Análise concluída.'
        ];
    }

    private static function parsearRespostaPorEmpresa(string $resposta): array
    {
        $resposta = trim($resposta);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $resposta, $m)) {
            $resposta = trim($m[1]);
        }
        $decoded = json_decode($resposta, true);
        return $decoded['por_empresa'] ?? [];
    }

    private static function getCacheDir(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/cache/insights';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function getCache(string $key)
    {
        $file = self::getCacheDir() . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
        if (!is_file($file)) {
            return null;
        }
        $data = @file_get_contents($file);
        if (!$data) {
            return null;
        }
        $decoded = json_decode($data, true);
        if (!$decoded || !isset($decoded['expira'])) {
            return null;
        }
        if ($decoded['expira'] < time()) {
            @unlink($file);
            return null;
        }
        return $decoded['dados'] ?? null;
    }

    private static function setCache(string $key, $valor): void
    {
        $file = self::getCacheDir() . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
        $data = [
            'expira' => time() + (self::CACHE_HORAS * 3600),
            'dados' => $valor
        ];
        @file_put_contents($file, json_encode($data));
    }
}
