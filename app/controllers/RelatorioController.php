<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Produto;
use App\Models\PedidoVinculado;
use App\Models\Empresa;

class RelatorioController extends Controller
{
    private $contaPagarModel;
    private $contaReceberModel;
    private $produtoModel;
    private $pedidoModel;
    private $empresaModel;

    public function __construct()
    {
        parent::__construct();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
        $this->produtoModel = new Produto();
        $this->pedidoModel = new PedidoVinculado();
        $this->empresaModel = new Empresa();
    }

    /**
     * Índice de relatórios
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        $empresas = $this->empresaModel->findAll();
        
        return $this->render('relatorios/index', [
            'empresas' => $empresas,
            'empresaId' => $empresaId
        ]);
    }

    /**
     * Relatório de Lucro
     */
    public function lucro(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Filtros
        $dataInicio = $request->get('data_inicio', date('Y-m-01'));
        $dataFim = $request->get('data_fim', date('Y-m-t'));
        $empresaSelecionada = $request->get('empresa_id', $empresaId);
        
        // Buscar empresas
        $empresas = $this->empresaModel->findAll();
        
        // Calcular receitas (exclui pedidos cancelados)
        $receitas = $this->contaReceberModel->getSomaByPeriodo(
            $empresaSelecionada, $dataInicio, $dataFim, 'recebido', true
        );
        
        // Calcular despesas
        $despesas = $this->contaPagarModel->getSomaByPeriodo(
            $empresaSelecionada, $dataInicio, $dataFim, 'pago'
        );
        
        // Lucro bruto e líquido
        $lucroBruto = $receitas - $despesas;
        
        // Receitas por categoria (exclui pedidos cancelados)
        $receitasPorCategoria = $this->contaReceberModel->getReceitasPorCategoria(
            $empresaSelecionada, $dataInicio, $dataFim, true
        );
        
        // Despesas por categoria
        $despesasPorCategoria = $this->contaPagarModel->getDespesasPorCategoria(
            $empresaSelecionada, $dataInicio, $dataFim
        );
        
        // Evolução mensal
        $evolucaoMensal = $this->getEvolucaoMensal($empresaSelecionada, $dataInicio, $dataFim);
        
        return $this->render('relatorios/lucro', [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'lucroBruto' => $lucroBruto,
            'margemLiquida' => $receitas > 0 ? ($lucroBruto / $receitas) * 100 : 0,
            'receitasPorCategoria' => $receitasPorCategoria,
            'despesasPorCategoria' => $despesasPorCategoria,
            'evolucaoMensal' => $evolucaoMensal,
            'empresas' => $empresas,
            'empresaSelecionada' => $empresaSelecionada,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);
    }

    /**
     * Relatório de Margem
     */
    public function margem(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Filtros
        $empresaSelecionada = $request->get('empresa_id', $empresaId);
        
        // Buscar empresas
        $empresas = $this->empresaModel->findAll();
        
        // Buscar produtos com margem
        $produtos = $this->produtoModel->findAll($empresaSelecionada);
        
        // Calcular margens
        $produtosComMargem = [];
        foreach ($produtos as $produto) {
            if ($produto['preco_venda'] > 0) {
                $margem = (($produto['preco_venda'] - $produto['custo_unitario']) / $produto['preco_venda']) * 100;
                $produto['margem'] = $margem;
                $produto['lucro_unitario'] = $produto['preco_venda'] - $produto['custo_unitario'];
                $produtosComMargem[] = $produto;
            }
        }
        
        // Ordenar por margem (maior primeiro)
        usort($produtosComMargem, function($a, $b) {
            return $b['margem'] <=> $a['margem'];
        });
        
        // Estatísticas
        $totalProdutos = count($produtosComMargem);
        $margemMedia = $totalProdutos > 0 ? array_sum(array_column($produtosComMargem, 'margem')) / $totalProdutos : 0;
        
        return $this->render('relatorios/margem', [
            'produtos' => $produtosComMargem,
            'totalProdutos' => $totalProdutos,
            'margemMedia' => $margemMedia,
            'empresas' => $empresas,
            'empresaSelecionada' => $empresaSelecionada
        ]);
    }

    /**
     * Relatório de Inadimplência
     */
    public function inadimplencia(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Filtros
        $empresaSelecionada = $request->get('empresa_id', $empresaId);
        
        // Buscar empresas
        $empresas = $this->empresaModel->findAll();
        
        // Buscar contas vencidas (exclui pedidos cancelados)
        $contasVencidas = $this->contaReceberModel->getContasVencidasDetalhadas($empresaSelecionada, true);
        
        // Calcular inadimplência
        $valorTotal = 0;
        $valorVencido = 0;
        $contasPorCliente = [];
        
        foreach ($contasVencidas as $conta) {
            $valorVencido += $conta['valor_total'];
            
            $clienteId = $conta['cliente_id'];
            if (!isset($contasPorCliente[$clienteId])) {
                $contasPorCliente[$clienteId] = [
                    'cliente_nome' => $conta['cliente_nome'],
                    'total_vencido' => 0,
                    'quantidade' => 0,
                    'contas' => []
                ];
            }
            
            $contasPorCliente[$clienteId]['total_vencido'] += $conta['valor_total'];
            $contasPorCliente[$clienteId]['quantidade']++;
            $contasPorCliente[$clienteId]['contas'][] = $conta;
        }
        
        // Ordenar por valor (maior devedor primeiro)
        uasort($contasPorCliente, function($a, $b) {
            return $b['total_vencido'] <=> $a['total_vencido'];
        });
        
        // Calcular todas as contas a receber (exclui pedidos cancelados)
        $todasContas = $this->contaReceberModel->findAll([
            'empresa_id' => $empresaSelecionada,
            'status' => 'pendente',
            'excluir_pedido_cancelado' => true
        ]);
        
        foreach ($todasContas as $conta) {
            $valorTotal += $conta['valor_total'];
        }
        
        $taxaInadimplencia = $valorTotal > 0 ? ($valorVencido / $valorTotal) * 100 : 0;
        
        return $this->render('relatorios/inadimplencia', [
            'contasVencidas' => $contasVencidas,
            'contasPorCliente' => $contasPorCliente,
            'valorVencido' => $valorVencido,
            'valorTotal' => $valorTotal,
            'taxaInadimplencia' => $taxaInadimplencia,
            'totalClientes' => count($contasPorCliente),
            'empresas' => $empresas,
            'empresaSelecionada' => $empresaSelecionada
        ]);
    }

    /**
     * Evolução mensal de receitas e despesas
     */
    private function getEvolucaoMensal($empresaId, $dataInicio, $dataFim)
    {
        $db = $this->contaPagarModel->getDb();
        
        $sql = "SELECT 
                    DATE_FORMAT(data, '%Y-%m') as mes,
                    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
                FROM (
                    SELECT cr.data_recebimento as data, cr.valor_total as valor, 'receita' as tipo
                    FROM contas_receber cr
                    LEFT JOIN pedidos_vinculados pv ON cr.pedido_id = pv.id
                    WHERE cr.status = 'recebido'
                    AND cr.data_recebimento BETWEEN :data_inicio AND :data_fim
                    AND (cr.pedido_id IS NULL OR pv.status IN ('processando', 'em_processamento', 'concluido'))
                    " . ($empresaId ? "AND cr.empresa_id = :empresa_id1" : "") . "
                    
                    UNION ALL
                    
                    SELECT data_pagamento as data, valor_total as valor, 'despesa' as tipo
                    FROM contas_pagar
                    WHERE status = 'pago'
                    AND data_pagamento BETWEEN :data_inicio2 AND :data_fim2
                    " . ($empresaId ? "AND empresa_id = :empresa_id2" : "") . "
                ) as movimentos
                GROUP BY mes
                ORDER BY mes";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'data_inicio2' => $dataInicio,
            'data_fim2' => $dataFim
        ];
        
        if ($empresaId) {
            $params['empresa_id1'] = $empresaId;
            $params['empresa_id2'] = $empresaId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
