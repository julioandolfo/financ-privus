<?php
namespace Includes\Services;

use App\Core\Database;
use App\Models\EvolutionConfig;
use App\Models\WhatsAppRelatorioRegra;
use App\Models\WhatsAppRelatorioDestinatario;
use App\Models\WhatsAppRelatorioEnvio;
use DateTime;
use DateTimeZone;
use PDO;

/**
 * Service responsável por gerar os relatórios financeiros e enviá-los via WhatsApp
 * (Evolution API), com base em regras configuráveis.
 */
class RelatorioWhatsAppService
{
    private $db;
    private $regraModel;
    private $destinatarioModel;
    private $envioModel;
    private $evolutionModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->regraModel = new WhatsAppRelatorioRegra();
        $this->destinatarioModel = new WhatsAppRelatorioDestinatario();
        $this->envioModel = new WhatsAppRelatorioEnvio();
        $this->evolutionModel = new EvolutionConfig();
    }

    /**
     * Executa todas as regras devidas (CRON loop).
     *
     * @return array resumo da execução
     */
    public function executarRegrasDevidas($limit = 50)
    {
        $regras = $this->regraModel->findDevidas($limit);
        $resumo = ['regras' => count($regras), 'enviados' => 0, 'falhas' => 0, 'sem_dados' => 0];

        foreach ($regras as $regra) {
            $r = $this->executarRegra($regra['id'], 'automatico');
            $resumo['enviados'] += $r['enviados'];
            $resumo['falhas']  += $r['falhas'];
            if (!empty($r['sem_dados'])) {
                $resumo['sem_dados']++;
            }
        }
        return $resumo;
    }

    /**
     * Executa uma regra específica: gera relatório, envia para destinatários ativos,
     * grava log e reagenda a próxima execução.
     */
    public function executarRegra($regraId, $tipoEnvio = 'automatico')
    {
        $regra = $this->regraModel->findById($regraId);
        if (!$regra) {
            return ['enviados' => 0, 'falhas' => 0, 'erro' => 'Regra não encontrada'];
        }

        $destinatarios = $this->destinatarioModel->findByRegra($regraId, true);

        $evolution = $this->evolutionModel->findById($regra['evolution_config_id'], true);
        if (!$evolution || empty($evolution['ativo'])) {
            $erro = 'Configuração Evolution não encontrada ou inativa';
            $this->regraModel->marcarExecutada($regraId, 'falha', $this->calcularProximaExecucao($regra), $erro);
            return ['enviados' => 0, 'falhas' => 0, 'erro' => $erro];
        }

        // Gera dados do relatório
        try {
            $dados = $this->gerarDadosRelatorio($regra);
        } catch (\Throwable $e) {
            $this->regraModel->marcarExecutada($regraId, 'falha', $this->calcularProximaExecucao($regra), 'Erro ao gerar relatório: ' . $e->getMessage());
            return ['enviados' => 0, 'falhas' => 0, 'erro' => $e->getMessage()];
        }

        // Sem dados: registra e segue
        if (($dados['total'] ?? 0) === 0 && ($dados['_skip_se_vazio'] ?? false)) {
            $this->regraModel->marcarExecutada($regraId, 'sem_dados', $this->calcularProximaExecucao($regra), null);
            return ['enviados' => 0, 'falhas' => 0, 'sem_dados' => true];
        }

        $mensagem = $this->renderizarTemplate($regra, $dados);

        $svc = WhatsAppApiFactory::fromConfig($evolution);

        $enviados = 0;
        $falhas = 0;

        if (empty($destinatarios)) {
            $this->regraModel->marcarExecutada($regraId, 'falha', $this->calcularProximaExecucao($regra), 'Sem destinatários ativos');
            return ['enviados' => 0, 'falhas' => 0, 'erro' => 'sem_destinatarios'];
        }

        foreach ($destinatarios as $dest) {
            $envioId = $this->envioModel->create([
                'regra_id' => $regraId,
                'destinatario_id' => $dest['id'],
                'evolution_config_id' => $evolution['id'],
                'numero' => $dest['numero'],
                'mensagem' => $mensagem,
                'status' => 'aguardando',
                'tipo_envio' => $tipoEnvio,
            ]);

            $resp = $svc->enviarTexto($dest['numero'], $mensagem);
            if (!empty($resp['ok'])) {
                $this->envioModel->marcarEnviado($envioId, $resp['body']);
                $enviados++;
            } else {
                $this->envioModel->marcarFalha($envioId, $resp['error'] ?? 'Erro desconhecido', $resp['body'] ?? null);
                $falhas++;
            }

            // pequeno delay para não floodar a Evolution
            usleep(300000); // 0.3s
        }

        $statusFinal = ($enviados > 0 && $falhas === 0) ? 'sucesso' : (($enviados > 0) ? 'sucesso' : 'falha');
        $proxima = $tipoEnvio === 'automatico' ? $this->calcularProximaExecucao($regra) : $regra['proxima_execucao'];
        $this->regraModel->marcarExecutada($regraId, $statusFinal, $proxima, $falhas > 0 ? "Falhas: {$falhas}" : null);

        return ['enviados' => $enviados, 'falhas' => $falhas];
    }

    /**
     * Envio de teste manual: usa um número arbitrário e a primeira amostra de dados.
     */
    public function enviarTeste($regraId, $numero)
    {
        $regra = $this->regraModel->findById($regraId);
        if (!$regra) return ['ok' => false, 'error' => 'Regra não encontrada'];

        $evolution = $this->evolutionModel->findById($regra['evolution_config_id'], true);
        if (!$evolution) return ['ok' => false, 'error' => 'Configuração Evolution não encontrada'];

        $dados = $this->gerarDadosRelatorio($regra);
        $mensagem = "[TESTE] " . $this->renderizarTemplate($regra, $dados);

        $envioId = $this->envioModel->create([
            'regra_id' => $regraId,
            'destinatario_id' => null,
            'evolution_config_id' => $evolution['id'],
            'numero' => WhatsAppRelatorioDestinatario::normalizarNumero($numero),
            'mensagem' => $mensagem,
            'status' => 'aguardando',
            'tipo_envio' => 'teste',
        ]);

        $svc = WhatsAppApiFactory::fromConfig($evolution);
        $resp = $svc->enviarTexto(WhatsAppRelatorioDestinatario::normalizarNumero($numero), $mensagem);

        if (!empty($resp['ok'])) {
            $this->envioModel->marcarEnviado($envioId, $resp['body']);
            return ['ok' => true, 'envio_id' => $envioId];
        } else {
            $this->envioModel->marcarFalha($envioId, $resp['error'] ?? 'Erro desconhecido', $resp['body'] ?? null);
            return ['ok' => false, 'error' => $resp['error'] ?? 'Erro desconhecido', 'envio_id' => $envioId];
        }
    }

    /**
     * Gera o array de dados consumido pelo template, baseado em tipo_relatorio + filtros.
     */
    public function gerarDadosRelatorio(array $regra)
    {
        $tipo = $regra['tipo_relatorio'];
        $filtros = is_array($regra['filtros'] ?? null) ? $regra['filtros'] : [];
        $empresaId = (int)$regra['empresa_id'];

        switch ($tipo) {
            case 'contas_pagar_atrasadas':
                return $this->relatorioContasPagar($empresaId, $filtros, 'atrasadas', $regra);
            case 'contas_pagar_vencer':
                return $this->relatorioContasPagar($empresaId, $filtros, 'a_vencer', $regra);
            case 'contas_pagar_vencendo_hoje':
                return $this->relatorioContasPagar($empresaId, $filtros, 'hoje', $regra);
            case 'contas_receber_atrasadas':
                return $this->relatorioContasReceber($empresaId, $filtros, 'atrasadas', $regra);
            case 'contas_receber_vencer':
                return $this->relatorioContasReceber($empresaId, $filtros, 'a_vencer', $regra);
            case 'contas_receber_vencendo_hoje':
                return $this->relatorioContasReceber($empresaId, $filtros, 'hoje', $regra);
            case 'inadimplencia':
                $filtros['dias_atraso_min'] = $filtros['dias_atraso_min'] ?? 1;
                return $this->relatorioContasReceber($empresaId, $filtros, 'atrasadas', $regra);
            case 'resumo_diario':
                return $this->relatorioResumoDiario($empresaId, $filtros, $regra);
            case 'fluxo_caixa':
                return $this->relatorioFluxoCaixa($empresaId, $filtros, $regra);
            default:
                throw new \Exception("Tipo de relatório desconhecido: {$tipo}");
        }
    }

    private function relatorioContasPagar($empresaId, array $filtros, $modo, array $regra)
    {
        $hoje = date('Y-m-d');
        $sql = "SELECT cp.id, cp.numero_documento, cp.descricao, cp.valor_total, cp.valor_pago,
                       cp.data_vencimento, cp.status,
                       f.nome_razao_social AS fornecedor,
                       cat.nome AS categoria,
                       DATEDIFF('{$hoje}', cp.data_vencimento) AS dias_atraso
                  FROM contas_pagar cp
                  LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id
                  LEFT JOIN categorias_financeiras cat ON cat.id = cp.categoria_id
                 WHERE cp.empresa_id = :empresa_id
                   AND cp.status IN ('pendente','parcial')";

        $params = ['empresa_id' => $empresaId];
        $sql .= $this->aplicarFiltrosVencimento($modo, $filtros, $params, 'cp.data_vencimento');
        $sql .= $this->aplicarFiltrosComuns($filtros, $params, 'cp', 'fornecedor_id', 'categoria_id', 'centro_custo_id');

        $sql .= " ORDER BY cp.data_vencimento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $total = count($itens);
        $valorTotal = 0.0;
        foreach ($itens as $i) {
            $valorTotal += (float)$i['valor_total'] - (float)$i['valor_pago'];
        }

        return [
            'tipo' => 'contas_pagar',
            'titulo' => $this->tituloPorModo('Contas a Pagar', $modo, $filtros),
            'total' => $total,
            'valor_total' => $valorTotal,
            'itens' => $itens,
            'modo' => $modo,
            '_skip_se_vazio' => false,
            'empresa_nome' => $regra['empresa_nome'] ?? '',
        ];
    }

    private function relatorioContasReceber($empresaId, array $filtros, $modo, array $regra)
    {
        $hoje = date('Y-m-d');
        $sql = "SELECT cr.id, cr.numero_documento, cr.descricao, cr.valor_total, cr.valor_recebido,
                       cr.data_vencimento, cr.status,
                       c.nome_razao_social AS cliente,
                       cat.nome AS categoria,
                       DATEDIFF('{$hoje}', cr.data_vencimento) AS dias_atraso
                  FROM contas_receber cr
                  LEFT JOIN clientes c ON c.id = cr.cliente_id
                  LEFT JOIN categorias_financeiras cat ON cat.id = cr.categoria_id
                 WHERE cr.empresa_id = :empresa_id
                   AND cr.status IN ('pendente','parcial')";

        $params = ['empresa_id' => $empresaId];
        $sql .= $this->aplicarFiltrosVencimento($modo, $filtros, $params, 'cr.data_vencimento');
        $sql .= $this->aplicarFiltrosComuns($filtros, $params, 'cr', 'cliente_id', 'categoria_id', 'centro_custo_id');

        $sql .= " ORDER BY cr.data_vencimento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $total = count($itens);
        $valorTotal = 0.0;
        foreach ($itens as $i) {
            $valorTotal += (float)$i['valor_total'] - (float)$i['valor_recebido'];
        }

        return [
            'tipo' => 'contas_receber',
            'titulo' => $this->tituloPorModo('Contas a Receber', $modo, $filtros),
            'total' => $total,
            'valor_total' => $valorTotal,
            'itens' => $itens,
            'modo' => $modo,
            '_skip_se_vazio' => false,
            'empresa_nome' => $regra['empresa_nome'] ?? '',
        ];
    }

    private function relatorioResumoDiario($empresaId, array $filtros, array $regra)
    {
        $hoje = date('Y-m-d');
        $diasVencer = (int)($filtros['dias_vencer'] ?? 7);

        // Atrasadas (pagar)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_total - valor_pago),0) AS valor
              FROM contas_pagar
             WHERE empresa_id = :e AND status IN ('pendente','parcial')
               AND data_vencimento < :hoje
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje]);
        $pagarAtrasadas = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'valor'=>0];

        // A vencer (pagar) próximos N dias
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_total - valor_pago),0) AS valor
              FROM contas_pagar
             WHERE empresa_id = :e AND status IN ('pendente','parcial')
               AND data_vencimento BETWEEN :hoje AND DATE_ADD(:hoje, INTERVAL :d DAY)
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje, 'd' => $diasVencer]);
        $pagarVencer = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'valor'=>0];

        // Atrasadas (receber)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_total - valor_recebido),0) AS valor
              FROM contas_receber
             WHERE empresa_id = :e AND status IN ('pendente','parcial')
               AND data_vencimento < :hoje
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje]);
        $receberAtrasadas = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'valor'=>0];

        // A vencer (receber)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_total - valor_recebido),0) AS valor
              FROM contas_receber
             WHERE empresa_id = :e AND status IN ('pendente','parcial')
               AND data_vencimento BETWEEN :hoje AND DATE_ADD(:hoje, INTERVAL :d DAY)
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje, 'd' => $diasVencer]);
        $receberVencer = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'valor'=>0];

        $total = (int)$pagarAtrasadas['total'] + (int)$pagarVencer['total'] + (int)$receberAtrasadas['total'] + (int)$receberVencer['total'];
        $itens = [
            ['label' => 'Pagar atrasadas',  'total' => (int)$pagarAtrasadas['total'],  'valor' => (float)$pagarAtrasadas['valor']],
            ['label' => "Pagar próximos {$diasVencer}d", 'total' => (int)$pagarVencer['total'],   'valor' => (float)$pagarVencer['valor']],
            ['label' => 'Receber atrasadas','total' => (int)$receberAtrasadas['total'],'valor' => (float)$receberAtrasadas['valor']],
            ['label' => "Receber próximos {$diasVencer}d", 'total' => (int)$receberVencer['total'],'valor' => (float)$receberVencer['valor']],
        ];

        return [
            'tipo' => 'resumo_diario',
            'titulo' => 'Resumo Diário do Financeiro',
            'total' => $total,
            'valor_total' => (float)$pagarAtrasadas['valor'] + (float)$pagarVencer['valor'] + (float)$receberAtrasadas['valor'] + (float)$receberVencer['valor'],
            'itens' => $itens,
            'modo' => 'resumo',
            '_skip_se_vazio' => false,
            'empresa_nome' => $regra['empresa_nome'] ?? '',
            'dias_vencer' => $diasVencer,
        ];
    }

    private function relatorioFluxoCaixa($empresaId, array $filtros, array $regra)
    {
        $diasJanela = (int)($filtros['dias_janela'] ?? 30);
        $hoje = date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor_recebido),0) AS recebido,
                   COALESCE(SUM(CASE WHEN status IN ('pendente','parcial') THEN (valor_total - valor_recebido) ELSE 0 END),0) AS a_receber
              FROM contas_receber
             WHERE empresa_id = :e
               AND data_vencimento BETWEEN DATE_SUB(:hoje, INTERVAL :d DAY) AND DATE_ADD(:hoje, INTERVAL :d DAY)
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje, 'd' => $diasJanela]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['recebido'=>0,'a_receber'=>0];

        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor_pago),0) AS pago,
                   COALESCE(SUM(CASE WHEN status IN ('pendente','parcial') THEN (valor_total - valor_pago) ELSE 0 END),0) AS a_pagar
              FROM contas_pagar
             WHERE empresa_id = :e
               AND data_vencimento BETWEEN DATE_SUB(:hoje, INTERVAL :d DAY) AND DATE_ADD(:hoje, INTERVAL :d DAY)
        ");
        $stmt->execute(['e' => $empresaId, 'hoje' => $hoje, 'd' => $diasJanela]);
        $pag = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pago'=>0,'a_pagar'=>0];

        $saldoRealizado = (float)$rec['recebido'] - (float)$pag['pago'];
        $saldoPrevisto = (float)$rec['a_receber'] - (float)$pag['a_pagar'];

        $itens = [
            ['label' => 'Recebido', 'valor' => (float)$rec['recebido']],
            ['label' => 'Pago', 'valor' => (float)$pag['pago']],
            ['label' => 'A receber', 'valor' => (float)$rec['a_receber']],
            ['label' => 'A pagar', 'valor' => (float)$pag['a_pagar']],
            ['label' => 'Saldo realizado', 'valor' => $saldoRealizado],
            ['label' => 'Saldo previsto', 'valor' => $saldoPrevisto],
        ];

        return [
            'tipo' => 'fluxo_caixa',
            'titulo' => "Fluxo de Caixa (±{$diasJanela} dias)",
            'total' => count($itens),
            'valor_total' => $saldoRealizado + $saldoPrevisto,
            'itens' => $itens,
            'modo' => 'fluxo',
            '_skip_se_vazio' => false,
            'empresa_nome' => $regra['empresa_nome'] ?? '',
            'dias_janela' => $diasJanela,
        ];
    }

    /**
     * Aplica WHERE de vencimento conforme modo + filtros (dias_atraso_min/max, dias_vencer).
     * Modo:
     *   atrasadas: data_vencimento < hoje (+ janelas opcionais)
     *   a_vencer:  data_vencimento BETWEEN hoje AND hoje + dias_vencer
     *   hoje:      data_vencimento = hoje
     */
    private function aplicarFiltrosVencimento($modo, array $filtros, array &$params, $colVenc)
    {
        $sql = '';
        if ($modo === 'atrasadas') {
            $minAtraso = (int)($filtros['dias_atraso_min'] ?? 1);
            $sql .= " AND DATEDIFF(CURDATE(), {$colVenc}) >= :dias_atraso_min";
            $params['dias_atraso_min'] = $minAtraso;
            if (isset($filtros['dias_atraso_max']) && $filtros['dias_atraso_max'] !== '') {
                $sql .= " AND DATEDIFF(CURDATE(), {$colVenc}) <= :dias_atraso_max";
                $params['dias_atraso_max'] = (int)$filtros['dias_atraso_max'];
            }
        } elseif ($modo === 'a_vencer') {
            $diasVencer = (int)($filtros['dias_vencer'] ?? 7);
            $sql .= " AND {$colVenc} BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias_vencer DAY)";
            $params['dias_vencer'] = $diasVencer;
        } elseif ($modo === 'hoje') {
            $sql .= " AND {$colVenc} = CURDATE()";
        }
        return $sql;
    }

    private function aplicarFiltrosComuns(array $filtros, array &$params, $alias, $colEntidade, $colCategoria, $colCentro)
    {
        $sql = '';

        if (!empty($filtros['categorias']) && is_array($filtros['categorias'])) {
            $ids = array_filter(array_map('intval', $filtros['categorias']));
            if ($ids) {
                $sql .= " AND {$alias}.{$colCategoria} IN (" . implode(',', $ids) . ")";
            }
        }
        if (!empty($filtros['centros_custo']) && is_array($filtros['centros_custo'])) {
            $ids = array_filter(array_map('intval', $filtros['centros_custo']));
            if ($ids) {
                $sql .= " AND {$alias}.{$colCentro} IN (" . implode(',', $ids) . ")";
            }
        }
        if (!empty($filtros['entidades']) && is_array($filtros['entidades'])) {
            $ids = array_filter(array_map('intval', $filtros['entidades']));
            if ($ids) {
                $sql .= " AND {$alias}.{$colEntidade} IN (" . implode(',', $ids) . ")";
            }
        }
        if (isset($filtros['valor_min']) && $filtros['valor_min'] !== '') {
            $sql .= " AND {$alias}.valor_total >= :valor_min";
            $params['valor_min'] = (float)$filtros['valor_min'];
        }
        if (isset($filtros['valor_max']) && $filtros['valor_max'] !== '') {
            $sql .= " AND {$alias}.valor_total <= :valor_max";
            $params['valor_max'] = (float)$filtros['valor_max'];
        }
        return $sql;
    }

    private function tituloPorModo($base, $modo, array $filtros)
    {
        switch ($modo) {
            case 'atrasadas':
                $min = (int)($filtros['dias_atraso_min'] ?? 1);
                return "{$base} — Atrasadas ({$min}+ dias)";
            case 'a_vencer':
                $dias = (int)($filtros['dias_vencer'] ?? 7);
                return "{$base} — A vencer (próx. {$dias} dias)";
            case 'hoje':
                return "{$base} — Vencendo hoje";
            default:
                return $base;
        }
    }

    /**
     * Renderiza o template Markdown com variáveis ({empresa}, {total}, {valor_total}, {lista}, {data}, {titulo}).
     */
    public function renderizarTemplate(array $regra, array $dados)
    {
        $template = trim($regra['template_mensagem'] ?? '');
        if ($template === '') {
            $template = $this->templatePadrao($regra['tipo_relatorio']);
        }

        $empresaNome = $dados['empresa_nome'] ?? ($regra['empresa_nome'] ?? '');
        $valorFmt = 'R$ ' . number_format($dados['valor_total'] ?? 0, 2, ',', '.');
        $dataFmt = date('d/m/Y');
        $horaFmt = date('H:i');
        $titulo = $dados['titulo'] ?? ($regra['nome'] ?? 'Relatório');

        $lista = $this->montarLista($dados, $regra);

        $vars = [
            '{empresa}' => $empresaNome,
            '{titulo}' => $titulo,
            '{total}' => (int)($dados['total'] ?? 0),
            '{valor_total}' => $valorFmt,
            '{data}' => $dataFmt,
            '{hora}' => $horaFmt,
            '{lista}' => $lista,
            '{regra}' => $regra['nome'] ?? '',
        ];

        $msg = strtr($template, $vars);

        // Se template não tem {lista} e a regra pede lista, anexa no final
        if (!empty($regra['incluir_lista_detalhada']) && strpos($template, '{lista}') === false && $lista !== '') {
            $msg .= "\n\n" . $lista;
        }

        return $msg;
    }

    private function montarLista(array $dados, array $regra)
    {
        if (empty($dados['itens']) || !is_array($dados['itens'])) return '';
        $limite = (int)($regra['limite_itens_lista'] ?? 20);
        if ($limite <= 0) $limite = 20;

        $linhas = [];
        $count = 0;
        $tipo = $dados['tipo'] ?? '';

        foreach ($dados['itens'] as $item) {
            if ($count >= $limite) break;

            if ($tipo === 'contas_pagar') {
                $resta = (float)$item['valor_total'] - (float)$item['valor_pago'];
                $venc = !empty($item['data_vencimento']) ? date('d/m', strtotime($item['data_vencimento'])) : '?';
                $atraso = (int)($item['dias_atraso'] ?? 0);
                $marcador = $atraso > 0 ? "🔴 {$atraso}d" : ($atraso === 0 ? "🟡 hoje" : "🟢");
                $linhas[] = "{$marcador} *{$item['fornecedor']}* — R$ " . number_format($resta, 2, ',', '.') . " · venc {$venc}";
            } elseif ($tipo === 'contas_receber') {
                $resta = (float)$item['valor_total'] - (float)$item['valor_recebido'];
                $venc = !empty($item['data_vencimento']) ? date('d/m', strtotime($item['data_vencimento'])) : '?';
                $atraso = (int)($item['dias_atraso'] ?? 0);
                $marcador = $atraso > 0 ? "🔴 {$atraso}d" : ($atraso === 0 ? "🟡 hoje" : "🟢");
                $linhas[] = "{$marcador} *{$item['cliente']}* — R$ " . number_format($resta, 2, ',', '.') . " · venc {$venc}";
            } elseif ($tipo === 'resumo_diario' || $tipo === 'fluxo_caixa') {
                $valorFmt = 'R$ ' . number_format($item['valor'] ?? 0, 2, ',', '.');
                $totalFmt = isset($item['total']) ? " ({$item['total']} contas)" : '';
                $linhas[] = "• *{$item['label']}*: {$valorFmt}{$totalFmt}";
            }
            $count++;
        }

        $totalItens = count($dados['itens']);
        if ($totalItens > $limite && in_array($tipo, ['contas_pagar','contas_receber'])) {
            $linhas[] = "_… e mais " . ($totalItens - $limite) . " conta(s)._";
        }

        return implode("\n", $linhas);
    }

    private function templatePadrao($tipo)
    {
        switch ($tipo) {
            case 'contas_pagar_atrasadas':
                return "*🔴 {titulo}*\n_{empresa}_ · {data}\n\n*{total} contas* totalizando *{valor_total}*\n\n{lista}";
            case 'contas_pagar_vencer':
            case 'contas_pagar_vencendo_hoje':
                return "*💸 {titulo}*\n_{empresa}_ · {data}\n\n*{total} contas* totalizando *{valor_total}*\n\n{lista}";
            case 'contas_receber_atrasadas':
            case 'inadimplencia':
                return "*⚠️ {titulo}*\n_{empresa}_ · {data}\n\n*{total} contas* em aberto totalizando *{valor_total}*\n\n{lista}";
            case 'contas_receber_vencer':
            case 'contas_receber_vencendo_hoje':
                return "*💰 {titulo}*\n_{empresa}_ · {data}\n\n*{total} contas* totalizando *{valor_total}*\n\n{lista}";
            case 'resumo_diario':
                return "*📊 {titulo}*\n_{empresa}_ · {data}\n\nTotal em aberto: *{valor_total}*\n\n{lista}";
            case 'fluxo_caixa':
                return "*📈 {titulo}*\n_{empresa}_ · {data}\n\n{lista}";
            default:
                return "*{titulo}*\n_{empresa}_ · {data}\n\n{total} item(ns) · {valor_total}\n\n{lista}";
        }
    }

    /**
     * Calcula a próxima execução de uma regra com base em frequencia/horario/dias.
     * Retorna string 'Y-m-d H:i:s' ou null.
     */
    public function calcularProximaExecucao($regra)
    {
        $tz = new DateTimeZone(date_default_timezone_get() ?: 'America/Sao_Paulo');
        $agora = new DateTime('now', $tz);

        $freq = $regra['frequencia'] ?? 'diaria';

        switch ($freq) {
            case 'intervalo_horas':
                $h = max(1, (int)($regra['intervalo_horas'] ?? 1));
                $next = clone $agora;
                $next->modify("+{$h} hours");
                return $next->format('Y-m-d H:i:s');

            case 'mensal': {
                $hora = $regra['horario'] ?? '08:00:00';
                $diaMes = max(1, min(31, (int)($regra['dia_mes'] ?? 1)));
                $next = clone $agora;
                // Define este mês
                $diaEfetivo = min($diaMes, (int)$next->format('t'));
                $next->setDate((int)$next->format('Y'), (int)$next->format('m'), $diaEfetivo);
                list($H, $M, $S) = array_pad(explode(':', $hora), 3, 0);
                $next->setTime((int)$H, (int)$M, (int)$S);
                if ($next <= $agora) {
                    $next->modify('first day of next month');
                    $diaEfetivo = min($diaMes, (int)$next->format('t'));
                    $next->setDate((int)$next->format('Y'), (int)$next->format('m'), $diaEfetivo);
                    $next->setTime((int)$H, (int)$M, (int)$S);
                }
                return $next->format('Y-m-d H:i:s');
            }

            case 'semanal': {
                $hora = $regra['horario'] ?? '08:00:00';
                $dias = array_filter(array_map('intval', explode(',', $regra['dias_semana'] ?? '')));
                if (empty($dias)) $dias = [(int)$agora->format('N')]; // hoje
                list($H, $M, $S) = array_pad(explode(':', $hora), 3, 0);

                // Procura próximo dia da semana válido (1=Seg .. 7=Dom no formato N do PHP)
                // Mas a semana brasileira costuma usar 1=Dom .. 7=Sab. Aceitamos a convenção do form: 1=Dom..7=Sab.
                // Mapeamos para N do PHP (1=Mon..7=Sun). Convenção interna: armazenamos 0=Dom..6=Sab? Para evitar ambiguidade,
                // tratamos os valores fornecidos como N do PHP (1=Mon..7=Sun).
                for ($i = 0; $i < 8; $i++) {
                    $cand = clone $agora;
                    $cand->modify("+{$i} days");
                    $cand->setTime((int)$H, (int)$M, (int)$S);
                    $candN = (int)$cand->format('N');
                    if (in_array($candN, $dias, true) && $cand > $agora) {
                        return $cand->format('Y-m-d H:i:s');
                    }
                }
                $next = clone $agora;
                $next->modify('+7 days');
                $next->setTime((int)$H, (int)$M, (int)$S);
                return $next->format('Y-m-d H:i:s');
            }

            case 'diaria':
            default: {
                $hora = $regra['horario'] ?? '08:00:00';
                list($H, $M, $S) = array_pad(explode(':', $hora), 3, 0);
                $next = clone $agora;
                $next->setTime((int)$H, (int)$M, (int)$S);
                if ($next <= $agora) {
                    $next->modify('+1 day');
                }
                return $next->format('Y-m-d H:i:s');
            }
        }
    }
}
