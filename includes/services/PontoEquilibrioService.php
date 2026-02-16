<?php
namespace includes\services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\DespesaRecorrente;
use App\Core\Database;
use PDO;

/**
 * Service para cálculo de Ponto de Equilíbrio (Break-even)
 * Usa dados reais de tipo_custo (contas_pagar + despesas_recorrentes)
 *
 * Regras de inclusão:
 * 1ª) Contas FIXA (tipo_custo='fixo'): sempre incluídas no PE, independente da categoria
 * 2ª) Contas VARIÁVEL ou sem tipo: só inclui se categoria.incluir_ponto_equilibrio = 1
 */
class PontoEquilibrioService
{
    private $db;
    private $contaPagarModel;
    private $contaReceberModel;
    private $despesaRecorrenteModel;

    /** Fatores para converter frequência em valor mensal */
    private static $fatoresMensais = [
        'diaria' => 30,
        'semanal' => 4.33,
        'quinzenal' => 2,
        'mensal' => 1,
        'bimestral' => 0.5,
        'trimestral' => 1/3,
        'semestral' => 1/6,
        'anual' => 1/12,
        'personalizado' => 1 // fallback, usar intervalo_dias se disponível
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
        $this->despesaRecorrenteModel = new DespesaRecorrente();
    }

    /**
     * Calcula ponto de equilíbrio para uma ou mais empresas
     * @param array $empresasIds IDs das empresas (vazio = todas ativas)
     * @param string $dataInicio
     * @param string $dataFim
     * @return array
     */
    public function calcular(array $empresasIds, string $dataInicio, string $dataFim): array
    {
        return $this->calcularV2($empresasIds, $dataInicio, $dataFim);
    }

    /**
     * Calcula por empresa individual + consolidado
     */
    public function calcularPorEmpresa(array $empresasIds, string $dataInicio, string $dataFim): array
    {
        $porEmpresa = [];
        foreach ($empresasIds as $empId) {
            $porEmpresa[$empId] = $this->calcularV2([$empId], $dataInicio, $dataFim);
        }
        $consolidado = $this->calcularV2($empresasIds, $dataInicio, $dataFim);
        return [
            'por_empresa' => $porEmpresa,
            'consolidado' => $consolidado
        ];
    }

    /**
     * Retorna detalhamento de custos fixos por categoria (para relatório)
     * Contas FIXA são incluídas por padrão no PE (independente da categoria)
     */
    public function detalharCustosFixos(array $empresasIds, string $dataInicio, string $dataFim): array
    {
        $params = [$dataInicio, $dataFim];
        $sql = "SELECT c.nome as categoria_nome, SUM(cp.valor_total) as total
                FROM contas_pagar cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.status = 'pago'
                  AND cp.data_pagamento BETWEEN ? AND ?
                  AND cp.tipo_custo = 'fixo'
                  AND cp.deleted_at IS NULL";
        if (!empty($empresasIds)) {
            $ph = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ($ph)";
            $params = array_merge($params, $empresasIds);
        }
        $sql .= " GROUP BY c.id, c.nome ORDER BY total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna detalhamento de custos variáveis por categoria
     * Custos variáveis: só inclui categorias ativas no PE (incluir_ponto_equilibrio = 1)
     */
    public function detalharCustosVariaveis(array $empresasIds, string $dataInicio, string $dataFim): array
    {
        $params = [$dataInicio, $dataFim];
        $sql = "SELECT c.nome as categoria_nome, SUM(cp.valor_total) as total
                FROM contas_pagar cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.status = 'pago'
                  AND cp.data_pagamento BETWEEN ? AND ?
                  AND (cp.tipo_custo = 'variavel' OR cp.tipo_custo = '' OR cp.tipo_custo IS NULL)
                  AND cp.deleted_at IS NULL
                  AND c.incluir_ponto_equilibrio = 1";
        if (!empty($empresasIds)) {
            $ph = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ($ph)";
            $params = array_merge($params, $empresasIds);
        }
        $sql .= " GROUP BY c.id, c.nome ORDER BY total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Soma contas a pagar. FIXA: sempre inclui (1ª regra).
     * VARIÁVEL/sem tipo: só se categoria ativa no PE (2ª regra).
     */
    private function somarContasPagarRaw(array $empresasIds, string $dataInicio, string $dataFim, ?string $tipoCusto = null): float
    {
        $params = [$dataInicio, $dataFim];
        $sql = "SELECT COALESCE(SUM(cp.valor_total), 0) as total
                FROM contas_pagar cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.status = 'pago'
                  AND cp.data_pagamento BETWEEN ? AND ?
                  AND cp.deleted_at IS NULL";
        if ($tipoCusto === 'fixo') {
            $sql .= " AND cp.tipo_custo = 'fixo'";
        } elseif ($tipoCusto !== null) {
            $params[] = $tipoCusto;
            $sql .= " AND cp.tipo_custo = ? AND c.incluir_ponto_equilibrio = 1";
        }
        if (!empty($empresasIds)) {
            $ph = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ($ph)";
            $params = array_merge($params, $empresasIds);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }

    /** Contas sem tipo: tratadas como variável, só se categoria ativa no PE */
    private function somarContasPagarSemTipoRaw(array $empresasIds, string $dataInicio, string $dataFim): float
    {
        $params = [$dataInicio, $dataFim];
        $sql = "SELECT COALESCE(SUM(cp.valor_total), 0) as total
                FROM contas_pagar cp
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.status = 'pago'
                  AND cp.data_pagamento BETWEEN ? AND ?
                  AND (cp.tipo_custo IS NULL OR cp.tipo_custo = '')
                  AND cp.deleted_at IS NULL
                  AND c.incluir_ponto_equilibrio = 1";
        if (!empty($empresasIds)) {
            $ph = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ($ph)";
            $params = array_merge($params, $empresasIds);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }

    /**
     * Valor mensal projetado das despesas recorrentes
     */
    private function valorMensalRecorrentes(array $empresasIds, string $tipoCusto): float
    {
        $recorrentes = $this->buscarDespesasRecorrentesPorTipo($empresasIds, $tipoCusto);

        $total = 0;
        foreach ($recorrentes as $dr) {
            if (!empty($empresasIds) && !in_array((int)$dr['empresa_id'], array_map('intval', $empresasIds))) {
                continue;
            }
            $total += $this->valorMensalRecorrente($dr);
        }
        return $total;
    }

    /**
     * FIXA: inclui todas (sem filtro de categoria).
     * VARIÁVEL: só categorias ativas no PE.
     */
    private function buscarDespesasRecorrentesPorTipo(array $empresasIds, string $tipoCusto): array
    {
        $categoriasExcluidas = $tipoCusto === 'variavel' ? $this->buscarCategoriasExcluidasPE() : [];
        $recorrentes = $this->despesaRecorrenteModel->findAll(['ativo' => 1]);
        $filtradas = [];
        foreach ($recorrentes as $r) {
            if (($r['tipo_custo'] ?? 'fixo') !== $tipoCusto) continue;
            if (!empty($empresasIds) && !in_array((int)$r['empresa_id'], array_map('intval', $empresasIds))) continue;
            if (!empty($categoriasExcluidas) && in_array((int)($r['categoria_id'] ?? 0), $categoriasExcluidas, true)) continue;
            $filtradas[] = $r;
        }
        return $filtradas;
    }

    /** IDs de categorias com incluir_ponto_equilibrio = 0 */
    private function buscarCategoriasExcluidasPE(): array
    {
        $sql = "SELECT id FROM categorias_financeiras WHERE incluir_ponto_equilibrio = 0";
        $stmt = $this->db->query($sql);
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return array_map('intval', array_column($rows, 'id'));
    }

    private function valorMensalRecorrente(array $dr): float
    {
        $valor = floatval($dr['valor'] ?? 0);
        $freq = $dr['frequencia'] ?? 'mensal';
        $fator = self::$fatoresMensais[$freq] ?? 1;
        if ($freq === 'personalizado' && !empty($dr['intervalo_dias'])) {
            $fator = 30 / max(1, (float)$dr['intervalo_dias']);
        }
        return $valor * $fator;
    }

    /**
     * Cálculo principal usando queries diretas
     */
    public function calcularV2(array $empresasIds, string $dataInicio, string $dataFim): array
    {
        $custosFixosContas = $this->somarContasPagarRaw($empresasIds, $dataInicio, $dataFim, 'fixo');
        $custosVariaveisContas = $this->somarContasPagarRaw($empresasIds, $dataInicio, $dataFim, 'variavel');
        $custosSemTipo = $this->somarContasPagarSemTipoRaw($empresasIds, $dataInicio, $dataFim);
        $custosVariaveisContas += $custosSemTipo;

        $recorrentesFixas = $this->valorMensalRecorrentes($empresasIds, 'fixo');
        $recorrentesVariaveis = $this->valorMensalRecorrentes($empresasIds, 'variavel');

        $custosFixos = $custosFixosContas + $recorrentesFixas;
        $custosVariaveis = $custosVariaveisContas + $recorrentesVariaveis;

        $receitas = $this->somarReceitasRaw($empresasIds, $dataInicio, $dataFim);

        $totalDespesas = $custosFixos + $custosVariaveis;
        $margemContribuicao = $receitas > 0 ? $receitas - $custosVariaveis : 0;
        $margemContribuicaoPct = $receitas > 0 ? ($margemContribuicao / $receitas) * 100 : 0;
        $pontoEquilibrio = $margemContribuicaoPct > 0 ? $custosFixos / ($margemContribuicaoPct / 100) : 0;

        $margemSeguranca = $receitas > 0 ? $receitas - $pontoEquilibrio : 0;
        $margemSegurancaPct = $pontoEquilibrio > 0 && $receitas > 0 
            ? (($receitas - $pontoEquilibrio) / $receitas) * 100 
            : ($receitas > 0 ? 100 : 0);

        return [
            'custos_fixos' => $custosFixos,
            'custos_fixos_contas' => $custosFixosContas,
            'custos_fixos_recorrentes' => $recorrentesFixas,
            'custos_variaveis' => $custosVariaveis,
            'custos_variaveis_contas' => $custosVariaveisContas,
            'custos_variaveis_recorrentes' => $recorrentesVariaveis,
            'receitas' => $receitas,
            'total_despesas' => $totalDespesas,
            'margem_contribuicao' => $margemContribuicao,
            'margem_contribuicao_pct' => $margemContribuicaoPct,
            'ponto_equilibrio' => $pontoEquilibrio,
            'margem_seguranca' => $margemSeguranca,
            'margem_seguranca_pct' => $margemSegurancaPct,
            'lucro_prejuizo' => $receitas - $totalDespesas,
            'acima_equilibrio' => $receitas >= $pontoEquilibrio
        ];
    }

    private function somarReceitasRaw(array $empresasIds, string $dataInicio, string $dataFim): float
    {
        $params = [$dataInicio, $dataFim];
        $sql = "SELECT COALESCE(SUM(
                    CASE WHEN cr.status = 'parcial' THEN cr.valor_recebido ELSE cr.valor_total END
                ), 0) as total
                FROM contas_receber cr
                WHERE cr.status IN ('recebido', 'parcial')
                  AND cr.data_recebimento IS NOT NULL
                  AND cr.data_recebimento BETWEEN ? AND ?
                  AND cr.deleted_at IS NULL
                  AND (cr.pedido_id IS NULL OR EXISTS (
                    SELECT 1 FROM pedidos_vinculados pv 
                    WHERE pv.id = cr.pedido_id AND pv.status IN ('processando','em_processamento','concluido')
                  ))";
        if (!empty($empresasIds)) {
            $ph = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cr.empresa_id IN ($ph)";
            $params = array_merge($params, $empresasIds);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }
}
