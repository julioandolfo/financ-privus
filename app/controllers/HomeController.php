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
use App\Models\Produto;
use App\Models\PedidoVinculado;
use App\Models\ConexaoBancaria;
use App\Models\TransacaoPendente;

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
            
            // Usuários são globais - contar todos os ativos uma única vez
            $todosUsuarios = $usuarioModel->findAll(['ativo' => 1]);
            $totalUsuarios = count($todosUsuarios);
            $totalFornecedores = $this->contarPorEmpresas($fornecedorModel, 'findAll', $empresasIds);
            $totalClientes = $this->contarPorEmpresas($clienteModel, 'findAll', $empresasIds);
            $totalCategorias = $this->contarPorEmpresas($categoriaModel, 'findAll', $empresasIds);
            $totalCentrosCusto = $this->contarPorEmpresas($centroCustoModel, 'findAll', $empresasIds);
            $totalFormasPagamento = $this->contarPorEmpresas($formaPagamentoModel, 'findAll', $empresasIds);
            $totalContasBancarias = $this->contarPorEmpresas($contaBancariaModel, 'findAll', $empresasIds);
            
            // Produtos
            $produtoModel = new Produto();
            $produtosMetricas = $this->obterMetricasProdutos($produtoModel, $empresasIds);
            
            // Pedidos
            $pedidoModel = new PedidoVinculado();
            $pedidosMetricas = $pedidoModel->getEstatisticas($empresasIds);
            $pedidosPorOrigem = $pedidoModel->getPorOrigem($empresasIds);
            
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
            // Usuários são globais - usar os já buscados acima
            $usuariosAtivos = count($todosUsuarios); // Já filtrados por ativo = 1
            $todosUsuariosInativos = $usuarioModel->findAll(['ativo' => 0]);
            $usuariosInativos = count($todosUsuariosInativos);
            
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
            
            // ========================================
            // MÉTRICAS FINANCEIRAS AVANÇADAS
            // ========================================
            
            // Período dos últimos 30 dias para cálculos
            $dataInicio = date('Y-m-d', strtotime('-30 days'));
            $dataFim = date('Y-m-d');
            
            // Receitas e Despesas (últimos 30 dias)
            $receitasUltimos30Dias = 0;
            $despesasUltimos30Dias = 0;
            
            foreach ($empresasIds as $empresaId) {
                // Receitas (contas recebidas)
                $contasRecebidas = $contaReceberModel->findAll($empresaId);
                foreach ($contasRecebidas as $conta) {
                    if ($conta['status'] === 'recebido' && 
                        $conta['data_recebimento'] >= $dataInicio && 
                        $conta['data_recebimento'] <= $dataFim) {
                        $receitasUltimos30Dias += $conta['valor'];
                    }
                }
                
                // Despesas (contas pagas)
                $contasPagas = $contaPagarModel->findAll($empresaId);
                foreach ($contasPagas as $conta) {
                    if ($conta['status'] === 'pago' && 
                        $conta['data_pagamento'] >= $dataInicio && 
                        $conta['data_pagamento'] <= $dataFim) {
                        $despesasUltimos30Dias += $conta['valor'];
                    }
                }
            }
            
            // LUCRO BRUTO (últimos 30 dias)
            $lucroBruto = $receitasUltimos30Dias - $despesasUltimos30Dias;
            
            // MARGEM BRUTA
            $margemBruta = $receitasUltimos30Dias > 0 ? 
                ($lucroBruto / $receitasUltimos30Dias) * 100 : 0;
            
            // Despesas Operacionais Estimadas (20% das despesas para simplificar)
            // Em produção, isso deveria vir de categorias específicas
            $despesasOperacionais = $despesasUltimos30Dias * 0.20;
            
            // EBITDA (Earnings Before Interest, Taxes, Depreciation and Amortization)
            // EBITDA = Lucro Operacional + Depreciação + Amortização
            // Simplificado: Receita - Custos Variáveis - Despesas Operacionais
            $custosVariaveis = $despesasUltimos30Dias * 0.60; // 60% das despesas são custos variáveis
            $ebitda = $receitasUltimos30Dias - $custosVariaveis - $despesasOperacionais;
            
            // MARGEM EBITDA
            $margemEbitda = $receitasUltimos30Dias > 0 ? 
                ($ebitda / $receitasUltimos30Dias) * 100 : 0;
            
            // LUCRO LÍQUIDO (simplificado: Lucro Bruto - Despesas Operacionais)
            $lucroLiquido = $lucroBruto - $despesasOperacionais;
            
            // MARGEM LÍQUIDA
            $margemLiquida = $receitasUltimos30Dias > 0 ? 
                ($lucroLiquido / $receitasUltimos30Dias) * 100 : 0;
            
            // ROI (Return on Investment) - baseado em investimentos vs lucro
            $investimentoTotal = $saldoTotal; // Simplificado: saldo em caixa + bancos
            $roi = $investimentoTotal > 0 ? 
                (($lucroLiquido / $investimentoTotal) * 100) : 0;
            
            // PONTO DE EQUILÍBRIO (Break-even point)
            // Custos Fixos / (Margem de Contribuição)
            $custosFixos = $despesasOperacionais;
            $margemContribuicao = $receitasUltimos30Dias - $custosVariaveis;
            $margemContribuicaoPercentual = $receitasUltimos30Dias > 0 ?
                ($margemContribuicao / $receitasUltimos30Dias) * 100 : 0;
            
            $pontoEquilibrio = $margemContribuicaoPercentual > 0 ? 
                $custosFixos / ($margemContribuicaoPercentual / 100) : 0;
            
            // BURN RATE (Taxa de queima de caixa) - mensal
            $burnRate = abs($despesasUltimos30Dias - $receitasUltimos30Dias);
            
            // RUNWAY (Pista de pouso) - meses de sobrevivência
            $runway = $burnRate > 0 ? ($saldoTotal / $burnRate) : 999;
            
            // TICKET MÉDIO (Receita total / número de contas recebidas)
            $totalContasRecebidas = 0;
            foreach ($empresasIds as $empresaId) {
                $contas = $contaReceberModel->findAll($empresaId);
                $totalContasRecebidas += count(array_filter($contas, function($c) use ($dataInicio, $dataFim) {
                    return $c['status'] === 'recebido' && 
                           $c['data_recebimento'] >= $dataInicio && 
                           $c['data_recebimento'] <= $dataFim;
                }));
            }
            $ticketMedio = $totalContasRecebidas > 0 ? 
                ($receitasUltimos30Dias / $totalContasRecebidas) : 0;
            
            // INADIMPLÊNCIA
            $totalContasVencidas = 0;
            $valorContasVencidas = 0;
            foreach ($empresasIds as $empresaId) {
                $contas = $contaReceberModel->findAll($empresaId);
                foreach ($contas as $conta) {
                    if ($conta['status'] === 'pendente' && $conta['data_vencimento'] < date('Y-m-d')) {
                        $totalContasVencidas++;
                        $valorContasVencidas += $conta['valor'];
                    }
                }
            }
            
            $totalContasReceber = 0;
            $valorTotalReceber = 0;
            foreach ($empresasIds as $empresaId) {
                $contas = $contaReceberModel->findAll($empresaId);
                $totalContasReceber += count($contas);
                $valorTotalReceber += array_sum(array_column($contas, 'valor'));
            }
            
            $taxaInadimplencia = $valorTotalReceber > 0 ? 
                ($valorContasVencidas / $valorTotalReceber) * 100 : 0;
            
            // MÉTRICAS DE SINCRONIZAÇÃO BANCÁRIA (TODAS AS EMPRESAS DO USUÁRIO)
            $conexaoBancariaModel = new ConexaoBancaria();
            $transacaoPendenteModel = new TransacaoPendente();
            
            $conexoesAtivas = 0;
            $transacoesPendentes = 0;
            $ultimaSincronizacao = null;
            $transacoesAprovadas = 0;
            $transacoesIgnoradas = 0;
            
            // Buscar métricas de TODAS as empresas do usuário (não apenas filtradas)
            $todasEmpresasIds = array_column($todasEmpresas, 'id');
            
            foreach ($todasEmpresasIds as $empresaId) {
                // Conexões ativas
                $conexoes = $conexaoBancariaModel->findByEmpresa($empresaId);
                $conexoesAtivas += count($conexoes);
                
                // Última sincronização
                foreach ($conexoes as $conexao) {
                    if ($conexao['ultima_sincronizacao']) {
                        $dataSinc = strtotime($conexao['ultima_sincronizacao']);
                        if (!$ultimaSincronizacao || $dataSinc > $ultimaSincronizacao) {
                            $ultimaSincronizacao = $dataSinc;
                        }
                    }
                }
                
                // Transações pendentes
                $transacoesPendentes += $transacaoPendenteModel->countByEmpresa($empresaId, 'pendente');
                
                // Estatísticas do mês
                $estatisticas = $transacaoPendenteModel->getEstatisticas($empresaId);
                $transacoesAprovadas += $estatisticas['aprovadas'] ?? 0;
                $transacoesIgnoradas += $estatisticas['ignoradas'] ?? 0;
            }
            
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
                    'contas_bancarias' => $totalContasBancarias,
                    'produtos' => $produtosMetricas['total'],
                    'pedidos' => $pedidosMetricas['total_pedidos'] ?? 0
                ],
                'produtos' => $produtosMetricas,
                'pedidos' => $pedidosMetricas,
                'pedidosPorOrigem' => $pedidosPorOrigem,
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
                ],
                // Métricas Financeiras Avançadas (últimos 30 dias)
                'metricas_financeiras' => [
                    'periodo' => '30 dias',
                    'receitas' => $receitasUltimos30Dias,
                    'despesas' => $despesasUltimos30Dias,
                    'lucro_bruto' => $lucroBruto,
                    'margem_bruta' => $margemBruta,
                    'despesas_operacionais' => $despesasOperacionais,
                    'ebitda' => $ebitda,
                    'margem_ebitda' => $margemEbitda,
                    'lucro_liquido' => $lucroLiquido,
                    'margem_liquida' => $margemLiquida,
                    'roi' => $roi,
                    'ponto_equilibrio' => $pontoEquilibrio,
                    'margem_contribuicao' => $margemContribuicaoPercentual,
                    'burn_rate' => $burnRate,
                    'runway' => $runway,
                    'ticket_medio' => $ticketMedio,
                    'inadimplencia_valor' => $valorContasVencidas,
                    'inadimplencia_taxa' => $taxaInadimplencia,
                    'contas_vencidas' => $totalContasVencidas
                ],
                // Métricas de Sincronização Bancária
                'sincronizacao_bancaria' => [
                    'conexoes_ativas' => $conexoesAtivas,
                    'transacoes_pendentes' => $transacoesPendentes,
                    'transacoes_aprovadas' => $transacoesAprovadas,
                    'transacoes_ignoradas' => $transacoesIgnoradas,
                    'ultima_sincronizacao' => $ultimaSincronizacao
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
                    'contas_bancarias' => 0,
                    'produtos' => 0
                ],
                'produtos' => [
                    'total' => 0,
                    'custo_total' => 0,
                    'valor_venda_total' => 0,
                    'margem_media' => 0,
                    'lucro_potencial' => 0
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
        $idsJaAdicionados = []; // Para evitar duplicatas
        
        foreach ($empresasIds as $empresaId) {
            // Verificar qual modelo estamos usando e chamar o método correto
            $modelClass = get_class($model);
            
            if (strpos($modelClass, 'Usuario') !== false) {
                // Usuario usa findAll com filtros ou findByEmpresa
                if ($method === 'findAll') {
                    $dados = $model->findByEmpresa($empresaId);
                } else {
                    $dados = $model->$method($empresaId);
                }
            } else {
                // Outros modelos aceitam empresa_id como primeiro parâmetro
                $dados = $model->$method($empresaId);
            }
            
            if (!empty($dados)) {
                foreach ($dados as $item) {
                    // Evitar duplicatas baseado no ID
                    $itemId = $item['id'] ?? null;
                    if ($itemId && !in_array($itemId, $idsJaAdicionados)) {
                        $resultado[] = $item;
                        $idsJaAdicionados[] = $itemId;
                    } elseif (!$itemId) {
                        $resultado[] = $item;
                    }
                }
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
    
    /**
     * Obter métricas de produtos
     */
    private function obterMetricasProdutos($produtoModel, $empresasIds)
    {
        $totalProdutos = 0;
        $custoTotal = 0;
        $valorVendaTotal = 0;
        $precoMaiorVenda = 0;
        $precoMenorVenda = PHP_FLOAT_MAX;
        $produtoMaisCaro = null;
        $produtoMaisBarato = null;
        
        foreach ($empresasIds as $empresaId) {
            $produtos = $produtoModel->findAll($empresaId);
            
            foreach ($produtos as $produto) {
                $totalProdutos++;
                $custoTotal += $produto['custo_unitario'];
                $valorVendaTotal += $produto['preco_venda'];
                
                // Produto mais caro
                if ($produto['preco_venda'] > $precoMaiorVenda) {
                    $precoMaiorVenda = $produto['preco_venda'];
                    $produtoMaisCaro = $produto;
                }
                
                // Produto mais barato
                if ($produto['preco_venda'] < $precoMenorVenda && $produto['preco_venda'] > 0) {
                    $precoMenorVenda = $produto['preco_venda'];
                    $produtoMaisBarato = $produto;
                }
            }
        }
        
        // Calcular margem média
        $margemMedia = 0;
        if ($custoTotal > 0) {
            $margemMedia = (($valorVendaTotal - $custoTotal) / $custoTotal) * 100;
        }
        
        $precoMedio = $totalProdutos > 0 ? $valorVendaTotal / $totalProdutos : 0;
        $custoMedio = $totalProdutos > 0 ? $custoTotal / $totalProdutos : 0;
        
        return [
            'total' => $totalProdutos,
            'custo_total' => $custoTotal,
            'valor_venda_total' => $valorVendaTotal,
            'preco_medio' => $precoMedio,
            'custo_medio' => $custoMedio,
            'margem_media' => $margemMedia,
            'produto_mais_caro' => $produtoMaisCaro,
            'produto_mais_barato' => $produtoMaisBarato,
            'lucro_potencial' => $valorVendaTotal - $custoTotal
        ];
    }
}
