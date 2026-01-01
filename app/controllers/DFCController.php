<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\MovimentacaoCaixa;
use App\Models\Empresa;

class DFCController extends Controller
{
    private $contaPagarModel;
    private $contaReceberModel;
    private $movimentacaoModel;
    private $empresaModel;

    public function __construct()
    {
        parent::__construct();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
        $this->movimentacaoModel = new MovimentacaoCaixa();
        $this->empresaModel = new Empresa();
    }

    /**
     * Exibe o relatório DFC
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Filtros
        $dataInicio = $request->get('data_inicio', date('Y-m-01'));
        $dataFim = $request->get('data_fim', date('Y-m-t'));
        $empresaSelecionada = $request->get('empresa_id', $empresaId);
        
        // Buscar empresas
        $empresas = $this->empresaModel->findAll();
        
        // Gerar DFC
        $dfc = $this->gerarDFC($empresaSelecionada, $dataInicio, $dataFim);
        
        return $this->render('dfc/index', [
            'dfc' => $dfc,
            'empresas' => $empresas,
            'empresaSelecionada' => $empresaSelecionada,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);
    }

    /**
     * Gera os dados do DFC
     */
    private function gerarDFC($empresaId, $dataInicio, $dataFim)
    {
        // ATIVIDADES OPERACIONAIS
        $recebimentosClientes = $this->contaReceberModel->getSomaByPeriodo(
            $empresaId, $dataInicio, $dataFim, 'pago'
        );
        
        $pagamentosFornecedores = $this->contaPagarModel->getSomaByPeriodo(
            $empresaId, $dataInicio, $dataFim, 'pago'
        );
        
        $pagamentosSalarios = $this->contaPagarModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Salários'
        );
        
        $pagamentosImpostos = $this->contaPagarModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Impostos'
        );
        
        $outrosRecebimentos = $this->movimentacaoModel->getSomaByTipo(
            $empresaId, $dataInicio, $dataFim, 'entrada'
        );
        
        $outrosPagamentos = $this->movimentacaoModel->getSomaByTipo(
            $empresaId, $dataInicio, $dataFim, 'saida'
        );
        
        // ATIVIDADES DE INVESTIMENTO
        $compraAtivos = $this->contaPagarModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Investimentos'
        );
        
        $vendaAtivos = $this->contaReceberModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Venda de Ativos'
        );
        
        // ATIVIDADES DE FINANCIAMENTO
        $emprestimosObtidos = $this->contaReceberModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Empréstimos'
        );
        
        $pagamentoEmprestimos = $this->contaPagarModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Pagamento de Empréstimos'
        );
        
        $distribuicaoLucros = $this->contaPagarModel->getSomaByCategoria(
            $empresaId, $dataInicio, $dataFim, 'Distribuição de Lucros'
        );
        
        // CÁLCULOS
        $caixaLiquidoOperacional = ($recebimentosClientes + $outrosRecebimentos) - 
                                   ($pagamentosFornecedores + $pagamentosSalarios + 
                                    $pagamentosImpostos + $outrosPagamentos);
        
        $caixaLiquidoInvestimento = $vendaAtivos - $compraAtivos;
        
        $caixaLiquidoFinanciamento = $emprestimosObtidos - 
                                     ($pagamentoEmprestimos + $distribuicaoLucros);
        
        $aumentoCaixa = $caixaLiquidoOperacional + $caixaLiquidoInvestimento + $caixaLiquidoFinanciamento;
        
        // Saldo inicial e final (simplificado)
        $saldoInicial = $this->movimentacaoModel->getSaldoInicial($empresaId, $dataInicio);
        $saldoFinal = $saldoInicial + $aumentoCaixa;
        
        return [
            'operacional' => [
                'recebimentos_clientes' => $recebimentosClientes,
                'pagamentos_fornecedores' => -abs($pagamentosFornecedores),
                'pagamentos_salarios' => -abs($pagamentosSalarios),
                'pagamentos_impostos' => -abs($pagamentosImpostos),
                'outros_recebimentos' => $outrosRecebimentos,
                'outros_pagamentos' => -abs($outrosPagamentos),
                'total' => $caixaLiquidoOperacional
            ],
            'investimento' => [
                'compra_ativos' => -abs($compraAtivos),
                'venda_ativos' => $vendaAtivos,
                'total' => $caixaLiquidoInvestimento
            ],
            'financiamento' => [
                'emprestimos_obtidos' => $emprestimosObtidos,
                'pagamento_emprestimos' => -abs($pagamentoEmprestimos),
                'distribuicao_lucros' => -abs($distribuicaoLucros),
                'total' => $caixaLiquidoFinanciamento
            ],
            'resumo' => [
                'saldo_inicial' => $saldoInicial,
                'aumento_caixa' => $aumentoCaixa,
                'saldo_final' => $saldoFinal
            ]
        ];
    }

    /**
     * Exporta DFC para PDF
     */
    public function exportarPDF(Request $request, Response $response)
    {
        $empresaId = $request->get('empresa_id');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        
        $dfc = $this->gerarDFC($empresaId, $dataInicio, $dataFim);
        
        // Aqui você pode implementar geração de PDF
        // Por enquanto, vamos retornar JSON
        $response->json(['success' => true, 'data' => $dfc]);
    }
}
