<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\MovimentacaoCaixa;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            // Models
            $empresaModel = new Empresa();
            $usuarioModel = new Usuario();
            $fornecedorModel = new Fornecedor();
            $clienteModel = new Cliente();
            $categoriaModel = new CategoriaFinanceira();
            $centroCustoModel = new CentroCusto();
            $formaPagamentoModel = new FormaPagamento();
            $contaBancariaModel = new ContaBancaria();
            $contaPagarModel = new ContaPagar();
            $contaReceberModel = new ContaReceber();
            $movimentacaoCaixaModel = new MovimentacaoCaixa();
            
            // Verificar se há filtro de empresas na sessão
            $empresasFiltradas = $_SESSION['dashboard_empresas_filtro'] ?? null;
            
            // Se não houver filtro, usar todas as empresas
            $todasEmpresas = $empresaModel->findAll(['ativo' => 1]);
            $empresasIds = $empresasFiltradas ?? array_column($todasEmpresas, 'id');
            
            // Totais gerais (com base nas empresas filtradas)
            $totalEmpresas = count($todasEmpresas);
            $empresasFiltro = $empresasFiltradas ? count($empresasFiltradas) : $totalEmpresas;
            $totalUsuarios = $this->contarPorEmpresas($usuarioModel, 'findAll', $empresasIds);
            $totalFornecedores = $this->contarPorEmpresas($fornecedorModel, 'findAll', $empresasIds);
            $totalClientes = $this->contarPorEmpresas($clienteModel, 'findAll', $empresasIds);
            $totalCategorias = $this->contarPorEmpresas($categoriaModel, 'findAll', $empresasIds);
            $totalCentrosCusto = $this->contarPorEmpresas($centroCustoModel, 'findAll', $empresasIds);
            $totalFormasPagamento = $this->contarPorEmpresas($formaPagamentoModel, 'findAll', $empresasIds);
            $totalContasBancarias = $this->contarPorEmpresas($contaBancariaModel, 'findAll', $empresasIds);
            
            // Dados das empresas
            $empresasData = [];
            foreach ($todasEmpresas as $empresa) {
                // Se houver filtro e esta empresa não estiver nele, pular
                if ($empresasFiltradas && !in_array($empresa['id'], $empresasFiltradas)) {
                    continue;
                }
                
                $empresasData[] = [
                    'nome' => $empresa['nome_fantasia'],
                    'usuarios' => count($usuarioModel->findByEmpresa($empresa['id'])),
                    'fornecedores' => count($fornecedorModel->findAll($empresa['id'])),
                    'clientes' => count($clienteModel->findAll($empresa['id'])),
                    'contas' => count($contaBancariaModel->findAll($empresa['id']))
                ];
            }
            
            // Buscar dados com filtro de empresas
            $usuarios = $this->buscarPorEmpresas($usuarioModel, 'findAll', $empresasIds);
            $usuariosAtivos = count(array_filter($usuarios, fn($u) => $u['ativo'] == 1));
            $usuariosInativos = count(array_filter($usuarios, fn($u) => $u['ativo'] == 0));
            
            $fornecedores = $this->buscarPorEmpresas($fornecedorModel, 'findAll', $empresasIds);
            $fornecedoresPF = count(array_filter($fornecedores, fn($f) => $f['tipo_pessoa'] == 'fisica'));
            $fornecedoresPJ = count(array_filter($fornecedores, fn($f) => $f['tipo_pessoa'] == 'juridica'));
            
            $clientes = $this->buscarPorEmpresas($clienteModel, 'findAll', $empresasIds);
            $clientesPF = count(array_filter($clientes, fn($c) => $c['tipo_pessoa'] == 'fisica'));
            $clientesPJ = count(array_filter($clientes, fn($c) => $c['tipo_pessoa'] == 'juridica'));
            
            $categorias = $this->buscarPorEmpresas($categoriaModel, 'findAll', $empresasIds);
            $categoriasReceita = count(array_filter($categorias, fn($c) => $c['tipo'] == 'receita'));
            $categoriasDespesa = count(array_filter($categorias, fn($c) => $c['tipo'] == 'despesa'));
            
            $formasPagamento = $this->buscarPorEmpresas($formaPagamentoModel, 'findAll', $empresasIds);
            $formasPagamentoSomente = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'pagamento'));
            $formasRecebimentoSomente = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'recebimento'));
            $formasAmbos = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'ambos'));
            
            $contasBancarias = $this->buscarPorEmpresas($contaBancariaModel, 'findAll', $empresasIds);
            $contasCorrente = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'corrente'));
            $contasPoupanca = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'poupanca'));
            $contasInvestimento = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'investimento'));
            
            // Saldo total das contas bancárias
            $saldoTotal = 0;
            $contasPorBanco = [];
            foreach ($contasBancarias as $conta) {
                $saldoTotal += (float)$conta['saldo_atual'];
                
                $banco = $conta['banco_nome'];
                if (!isset($contasPorBanco[$banco])) {
                    $contasPorBanco[$banco] = [
                        'total' => 0,
                        'saldo' => 0
                    ];
                }
                $contasPorBanco[$banco]['total']++;
                $contasPorBanco[$banco]['saldo'] += (float)$conta['saldo_atual'];
            }
            
            // Métricas de Contas a Pagar e Receber (com filtro de empresas)
            $contasPagarResumo = $contaPagarModel->getResumo($empresasIds);
            $contasReceberResumo = $contaReceberModel->getResumo($empresasIds);
            
            // Métricas de Movimentações de Caixa
            $movimentacoes = $this->buscarPorEmpresas($movimentacaoCaixaModel, 'findAll', $empresasIds);
            $totalMovimentacoes = count($movimentacoes);
            $totalEntradas = 0;
            $totalSaidas = 0;
            $movimentacoesConciliadas = 0;
            $movimentacoesPendentes = 0;
            
            foreach ($movimentacoes as $mov) {
                if ($mov['tipo'] === 'entrada') {
                    $totalEntradas += $mov['valor'];
                } else {
                    $totalSaidas += $mov['valor'];
                }
                
                if ($mov['conciliado']) {
                    $movimentacoesConciliadas++;
                } else {
                    $movimentacoesPendentes++;
                }
            }
            
            $saldoMovimentacoes = $totalEntradas - $totalSaidas;
            
            return $this->render('home/index', [
                'title' => 'Dashboard - Sistema Financeiro',
                'filtro' => [
                    'ativo' => !empty($empresasFiltradas),
                    'empresas_ids' => $empresasIds,
                    'total_empresas' => $totalEmpresas,
                    'empresas_filtradas' => $empresasFiltro
                ],
                'todas_empresas' => $todasEmpresas,
                'totais' => [
                    'empresas' => $totalEmpresas,
                    'usuarios' => $totalUsuarios,
                    'fornecedores' => $totalFornecedores,
                    'clientes' => $totalClientes,
                    'categorias' => $totalCategorias,
                    'centros_custo' => $totalCentrosCusto,
                    'formas_pagamento' => $totalFormasPagamento,
                    'contas_bancarias' => $totalContasBancarias
                ],
                'empresasData' => $empresasData,
                'usuarios' => [
                    'ativos' => $usuariosAtivos,
                    'inativos' => $usuariosInativos
                ],
                'fornecedores' => [
                    'pf' => $fornecedoresPF,
                    'pj' => $fornecedoresPJ
                ],
                'clientes' => [
                    'pf' => $clientesPF,
                    'pj' => $clientesPJ
                ],
                'categorias' => [
                    'receita' => $categoriasReceita,
                    'despesa' => $categoriasDespesa
                ],
                'formas_pagamento' => [
                    'pagamento' => $formasPagamentoSomente,
                    'recebimento' => $formasRecebimentoSomente,
                    'ambos' => $formasAmbos
                ],
                'contas_bancarias' => [
                    'corrente' => $contasCorrente,
                    'poupanca' => $contasPoupanca,
                    'investimento' => $contasInvestimento,
                    'saldo_total' => $saldoTotal,
                    'por_banco' => $contasPorBanco
                ],
                'contas_pagar' => $contasPagarResumo,
                'contas_receber' => $contasReceberResumo,
                'movimentacoes_caixa' => [
                    'total' => $totalMovimentacoes,
                    'entradas' => $totalEntradas,
                    'saidas' => $totalSaidas,
                    'saldo' => $saldoMovimentacoes,
                    'conciliadas' => $movimentacoesConciliadas,
                    'pendentes' => $movimentacoesPendentes
                ]
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar dashboard: ' . $e->getMessage();
            return $this->render('home/index', [
                'title' => 'Dashboard - Sistema Financeiro',
                'totais' => [
                    'empresas' => 0,
                    'usuarios' => 0,
                    'fornecedores' => 0,
                    'clientes' => 0,
                    'categorias' => 0,
                    'centros_custo' => 0,
                    'formas_pagamento' => 0,
                    'contas_bancarias' => 0
                ]
            ]);
        }
    }
    
    /**
     * Aplicar filtro de empresas no dashboard
     */
    public function filtrar(Request $request, Response $response)
    {
        $empresasSelecionadas = $request->post('empresas', []);
        
        if (!empty($empresasSelecionadas) && is_array($empresasSelecionadas)) {
            $_SESSION['dashboard_empresas_filtro'] = array_map('intval', $empresasSelecionadas);
            $_SESSION['success'] = count($empresasSelecionadas) . ' empresa(s) selecionada(s) para visualização';
        } else {
            unset($_SESSION['dashboard_empresas_filtro']);
            $_SESSION['success'] = 'Mostrando todas as empresas';
        }
        
        return $response->redirect('/');
    }
    
    /**
     * Limpar filtro de empresas do dashboard
     */
    public function limparFiltro(Request $request, Response $response)
    {
        unset($_SESSION['dashboard_empresas_filtro']);
        $_SESSION['success'] = 'Filtro removido. Mostrando todas as empresas';
        return $response->redirect('/');
    }
    
    /**
     * Buscar dados de múltiplas empresas
     */
    private function buscarPorEmpresas($model, $method, $empresasIds)
    {
        $resultado = [];
        
        foreach ($empresasIds as $empresaId) {
            $dados = $model->$method($empresaId);
            if (!empty($dados)) {
                $resultado = array_merge($resultado, $dados);
            }
        }
        
        return $resultado;
    }
    
    /**
     * Contar registros de múltiplas empresas
     */
    private function contarPorEmpresas($model, $method, $empresasIds)
    {
        return count($this->buscarPorEmpresas($model, $method, $empresasIds));
    }
}
