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
use App\Models\LogSistema;

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
            
            // LOG: Filtro de empresas
            LogSistema::debug('Dashboard', 'filtro_empresas', 'Empresas selecionadas para dashboard', [
                'tem_filtro' => !is_null($empresasFiltradas),
                'empresas_ids' => $empresasIds,
                'total_empresas_sistema' => count($todasEmpresas),
                'total_empresas_selecionadas' => count($empresasIds)
            ]);
            
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
            $bonificadosResumo = $pedidoModel->getResumoBonificados($empresasIds);
            $bonificadosPorEmpresa = $pedidoModel->getBonificadosPorEmpresa($empresasIds);
            
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
            // saldo_atual já vem com prioridade para API quando disponível (via findAll)
            $saldoTotal = 0;
            $saldoCalculadoTotal = 0;
            $contasComApi = 0;
            $contasPorBanco = [];
            foreach ($contasBancarias as $conta) {
                $saldoTotal += (float)$conta['saldo_atual'];
                $saldoCalculadoTotal += (float)($conta['saldo_calculado'] ?? $conta['saldo_atual']);
                if (!empty($conta['tem_conexao_api'])) {
                    $contasComApi++;
                }
                
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
            $contasReceberResumo = $contaReceberModel->getResumo($empresasIds, true);
            
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
            
            // Período dinâmico (padrão: este mês)
            $periodoSelecionado = $_SESSION['dashboard_periodo'] ?? 'este_mes';
            $periodoPersonalizado = $_SESSION['dashboard_periodo_personalizado'] ?? null;
            $periodoDatas = $this->calcularPeriodo($periodoSelecionado, $periodoPersonalizado);
            $dataInicio = $periodoDatas['inicio'];
            $dataFim = $periodoDatas['fim'];
            $periodoLabel = $periodoDatas['label'];
            
            // LOG: Início do cálculo das métricas financeiras
            LogSistema::debug('Dashboard', 'metricas_financeiras', 'Iniciando cálculo de métricas financeiras', [
                'empresas_ids' => $empresasIds,
                'total_empresas' => count($empresasIds),
                'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim]
            ]);
            
            // Receitas e Despesas (últimos 30 dias)
            $receitasUltimos30Dias = 0;
            $despesasUltimos30Dias = 0;
            
            // Receitas (contas recebidas) - busca todas as empresas de uma vez (exclui pedidos cancelados)
            $contasRecebidas = $contaReceberModel->findAll([
                'empresas_ids' => $empresasIds,
                'excluir_pedido_cancelado' => true
            ]);
            
            // LOG: Contas a receber encontradas
            LogSistema::debug('Dashboard', 'contas_receber', 'Contas a receber encontradas', [
                'total_contas' => count($contasRecebidas),
                'filtro_usado' => ['empresas_ids' => $empresasIds]
            ]);
            
            // Detalhe dos status das contas recebidas
            $statusContasReceber = [];
            $contasRecibidasNoPeriodo = 0;
            
            foreach ($contasRecebidas as $conta) {
                $status = $conta['status'] ?? 'sem_status';
                if (!isset($statusContasReceber[$status])) {
                    $statusContasReceber[$status] = ['count' => 0, 'valor' => 0];
                }
                $statusContasReceber[$status]['count']++;
                $statusContasReceber[$status]['valor'] += $conta['valor_total'] ?? 0;
                
                // Verifica se foi recebida no período
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $dataInicio && 
                    $conta['data_recebimento'] <= $dataFim) {
                    
                    // Usa valor_recebido para contas parciais, valor_total para totalmente recebidas
                    $valorRecebido = $conta['status'] === 'parcial' 
                        ? floatval($conta['valor_recebido'] ?? 0) 
                        : floatval($conta['valor_total'] ?? 0);
                    
                    $receitasUltimos30Dias += $valorRecebido;
                    $contasRecibidasNoPeriodo++;
                }
            }
            
            // LOG: Detalhamento por status - Contas a Receber
            LogSistema::debug('Dashboard', 'contas_receber_status', 'Distribuição de contas a receber por status', [
                'status_distribuicao' => $statusContasReceber,
                'receitas_30_dias' => $receitasUltimos30Dias,
                'contas_recebidas_periodo' => $contasRecibidasNoPeriodo,
                'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim]
            ]);
            
            // Despesas (contas pagas) - busca todas as empresas de uma vez
            $contasPagas = $contaPagarModel->findAll(['empresas_ids' => $empresasIds]);
            
            // LOG: Contas a pagar encontradas
            LogSistema::debug('Dashboard', 'contas_pagar', 'Contas a pagar encontradas', [
                'total_contas' => count($contasPagas),
                'filtro_usado' => ['empresas_ids' => $empresasIds]
            ]);
            
            // Detalhe dos status das contas pagas
            $statusContasPagar = [];
            foreach ($contasPagas as $conta) {
                $status = $conta['status'] ?? 'sem_status';
                if (!isset($statusContasPagar[$status])) {
                    $statusContasPagar[$status] = ['count' => 0, 'valor' => 0];
                }
                $statusContasPagar[$status]['count']++;
                $statusContasPagar[$status]['valor'] += $conta['valor_total'] ?? 0;
                
                if ($conta['status'] === 'pago' && 
                    $conta['data_pagamento'] >= $dataInicio && 
                    $conta['data_pagamento'] <= $dataFim) {
                    $despesasUltimos30Dias += $conta['valor_total'] ?? 0;
                }
            }
            
            // LOG: Detalhamento por status - Contas a Pagar
            LogSistema::debug('Dashboard', 'contas_pagar_status', 'Distribuição de contas a pagar por status', [
                'status_distribuicao' => $statusContasPagar,
                'despesas_30_dias' => $despesasUltimos30Dias
            ]);
            
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
            // Reutiliza $contasRecebidas já buscadas acima
            $totalContasRecebidas = count(array_filter($contasRecebidas, function($c) use ($dataInicio, $dataFim) {
                return $c['status'] === 'recebido' && 
                       $c['data_recebimento'] >= $dataInicio && 
                       $c['data_recebimento'] <= $dataFim;
            }));
            $ticketMedio = $totalContasRecebidas > 0 ? 
                ($receitasUltimos30Dias / $totalContasRecebidas) : 0;
            
            // INADIMPLÊNCIA
            // Reutiliza $contasRecebidas já buscadas acima
            $totalContasVencidas = 0;
            $valorContasVencidas = 0;
            $contasVencidasDetalhes = [];
            
            foreach ($contasRecebidas as $conta) {
                // Status 'vencido' = conta pendente que passou do vencimento (definido no SQL do model)
                if ($conta['status'] === 'vencido' || 
                    ($conta['status'] === 'pendente' && isset($conta['data_vencimento']) && $conta['data_vencimento'] < date('Y-m-d'))) {
                    $totalContasVencidas++;
                    $valorContasVencidas += $conta['valor_total'] ?? 0;
                    $contasVencidasDetalhes[] = [
                        'id' => $conta['id'],
                        'empresa_id' => $conta['empresa_id'],
                        'status' => $conta['status'],
                        'valor' => $conta['valor_total'],
                        'vencimento' => $conta['data_vencimento']
                    ];
                }
            }
            
            // Total geral de contas a receber
            $totalContasReceber = count($contasRecebidas);
            $valorTotalReceber = array_sum(array_column($contasRecebidas, 'valor_total'));
            
            $taxaInadimplencia = $valorTotalReceber > 0 ? 
                ($valorContasVencidas / $valorTotalReceber) * 100 : 0;
            
            // LOG: Inadimplência detalhada
            LogSistema::debug('Dashboard', 'inadimplencia_calculo', 'Cálculo de inadimplência consolidada', [
                'total_contas_receber' => $totalContasReceber,
                'valor_total_receber' => $valorTotalReceber,
                'contas_vencidas_count' => $totalContasVencidas,
                'contas_vencidas_valor' => $valorContasVencidas,
                'taxa_inadimplencia' => $taxaInadimplencia,
                'empresas_ids' => $empresasIds,
                'amostra_vencidas' => array_slice($contasVencidasDetalhes, 0, 5) // Primeiras 5 para não logar demais
            ]);
            
            // LOG: Resumo final das métricas financeiras
            LogSistema::debug('Dashboard', 'metricas_resumo', 'Métricas financeiras calculadas', [
                'receitas_30_dias' => $receitasUltimos30Dias,
                'despesas_30_dias' => $despesasUltimos30Dias,
                'lucro_bruto' => $lucroBruto,
                'lucro_liquido' => $lucroLiquido,
                'ebitda' => $ebitda,
                'margem_bruta' => $margemBruta,
                'margem_liquida' => $margemLiquida,
                'ticket_medio' => $ticketMedio,
                'total_contas_recebidas_periodo' => $totalContasRecebidas,
                'total_contas_receber' => $totalContasReceber,
                'valor_total_receber' => $valorTotalReceber,
                'contas_vencidas' => $totalContasVencidas,
                'valor_inadimplencia' => $valorContasVencidas,
                'taxa_inadimplencia' => $taxaInadimplencia,
                'saldo_bancos' => $saldoTotal,
                'burn_rate' => $burnRate,
                'runway_meses' => $runway
            ]);
            
            // ========================================
            // MÉTRICAS POR EMPRESA
            // Usar a mesma lista de empresas que o dashboard (todasEmpresas filtrada por empresasIds)
            // ========================================
            $empresasIdsNorm = array_map('intval', (array)$empresasIds);
            $empresasParaMetricas = array_values(array_filter($todasEmpresas, function($e) use ($empresasIdsNorm) {
                return in_array((int)($e['id'] ?? 0), $empresasIdsNorm, true);
            }));
            $metricasPorEmpresa = $this->calcularMetricasPorEmpresa($empresasParaMetricas, $contaReceberModel, $contaPagarModel, $contaBancariaModel, $dataInicio, $dataFim);
            
            // ========================================
            // COMPARATIVO MÊS ATUAL VS MÊS ANTERIOR
            // ========================================
            $mesAnteriorInicio = date('Y-m-01', strtotime('-1 month'));
            $mesAnteriorFim = date('Y-m-t', strtotime('-1 month'));
            
            $receitasMesAnterior = 0;
            $despesasMesAnterior = 0;
            foreach ($contasRecebidas as $conta) {
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $mesAnteriorInicio && 
                    $conta['data_recebimento'] <= $mesAnteriorFim) {
                    $receitasMesAnterior += $conta['status'] === 'parcial' 
                        ? floatval($conta['valor_recebido'] ?? 0) 
                        : floatval($conta['valor_total'] ?? 0);
                }
            }
            foreach ($contasPagas as $conta) {
                if ($conta['status'] === 'pago' && 
                    $conta['data_pagamento'] >= $mesAnteriorInicio && 
                    $conta['data_pagamento'] <= $mesAnteriorFim) {
                    $despesasMesAnterior += $conta['valor_total'] ?? 0;
                }
            }
            $lucroMesAnterior = $receitasMesAnterior - $despesasMesAnterior;
            $ebitdaMesAnterior = $receitasMesAnterior - ($despesasMesAnterior * 0.60) - ($despesasMesAnterior * 0.20);
            
            $varReceitas = $receitasMesAnterior > 0 ? (($receitasUltimos30Dias - $receitasMesAnterior) / $receitasMesAnterior) * 100 : ($receitasUltimos30Dias > 0 ? 100 : 0);
            $varDespesas = $despesasMesAnterior > 0 ? (($despesasUltimos30Dias - $despesasMesAnterior) / $despesasMesAnterior) * 100 : ($despesasUltimos30Dias > 0 ? 100 : 0);
            $varLucro = $lucroMesAnterior != 0 ? (($lucroLiquido - $lucroMesAnterior) / abs($lucroMesAnterior)) * 100 : ($lucroLiquido != 0 ? 100 : 0);
            $varEbitda = $ebitdaMesAnterior != 0 ? (($ebitda - $ebitdaMesAnterior) / abs($ebitdaMesAnterior)) * 100 : ($ebitda != 0 ? 100 : 0);
            
            // ========================================
            // AGING DE RECEBÍVEIS
            // ========================================
            $aging = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
            $agingQtd = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
            $hoje = date('Y-m-d');
            foreach ($contasRecebidas as $conta) {
                if ($conta['status'] === 'vencido' || 
                    ($conta['status'] === 'pendente' && isset($conta['data_vencimento']) && $conta['data_vencimento'] < $hoje)) {
                    $diasAtraso = (strtotime($hoje) - strtotime($conta['data_vencimento'])) / 86400;
                    $valor = floatval($conta['valor_total'] ?? 0);
                    if ($diasAtraso <= 30) {
                        $aging['0_30'] += $valor;
                        $agingQtd['0_30']++;
                    } elseif ($diasAtraso <= 60) {
                        $aging['31_60'] += $valor;
                        $agingQtd['31_60']++;
                    } elseif ($diasAtraso <= 90) {
                        $aging['61_90'] += $valor;
                        $agingQtd['61_90']++;
                    } else {
                        $aging['90_plus'] += $valor;
                        $agingQtd['90_plus']++;
                    }
                }
            }
            
            // ========================================
            // TOP 5 CLIENTES DEVEDORES
            // ========================================
            $devedoresPorCliente = [];
            foreach ($contasRecebidas as $conta) {
                if ($conta['status'] === 'vencido' || 
                    ($conta['status'] === 'pendente' && isset($conta['data_vencimento']) && $conta['data_vencimento'] < $hoje)) {
                    $clienteNome = $conta['cliente_nome'] ?? $conta['empresa_nome'] ?? 'Sem cliente';
                    if (!isset($devedoresPorCliente[$clienteNome])) {
                        $devedoresPorCliente[$clienteNome] = ['valor' => 0, 'qtd' => 0, 'contas' => []];
                    }
                    $valorConta = floatval($conta['valor_total'] ?? 0);
                    $devedoresPorCliente[$clienteNome]['valor'] += $valorConta;
                    $devedoresPorCliente[$clienteNome]['qtd']++;
                    $devedoresPorCliente[$clienteNome]['contas'][] = [
                        'id' => $conta['id'],
                        'descricao' => $conta['descricao'] ?? 'Sem descrição',
                        'valor' => $valorConta,
                        'data_vencimento' => $conta['data_vencimento'] ?? null,
                        'numero_documento' => $conta['numero_documento'] ?? ''
                    ];
                }
            }
            uasort($devedoresPorCliente, fn($a, $b) => $b['valor'] <=> $a['valor']);
            $topDevedores = array_slice($devedoresPorCliente, 0, 5, true);
            
            // ========================================
            // TOP 5 MAIORES DESPESAS E RECEITAS DO PERÍODO
            // ========================================
            $topDespesas = [];
            foreach ($contasPagas as $conta) {
                if ($conta['status'] === 'pago' && 
                    $conta['data_pagamento'] >= $dataInicio && 
                    $conta['data_pagamento'] <= $dataFim) {
                    $topDespesas[] = [
                        'descricao' => $conta['descricao'] ?? 'Sem descrição',
                        'fornecedor' => $conta['fornecedor_nome'] ?? 'N/A',
                        'valor' => floatval($conta['valor_total'] ?? 0),
                        'data' => $conta['data_pagamento'],
                        'categoria' => $conta['categoria_nome'] ?? 'N/A'
                    ];
                }
            }
            usort($topDespesas, fn($a, $b) => $b['valor'] <=> $a['valor']);
            $topDespesas = array_slice($topDespesas, 0, 5);
            
            $topReceitas = [];
            foreach ($contasRecebidas as $conta) {
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $dataInicio && 
                    $conta['data_recebimento'] <= $dataFim) {
                    $topReceitas[] = [
                        'descricao' => $conta['descricao'] ?? 'Sem descrição',
                        'cliente' => $conta['cliente_nome'] ?? 'N/A',
                        'valor' => floatval($conta['valor_total'] ?? 0),
                        'data' => $conta['data_recebimento'],
                        'categoria' => $conta['categoria_nome'] ?? 'N/A'
                    ];
                }
            }
            usort($topReceitas, fn($a, $b) => $b['valor'] <=> $a['valor']);
            $topReceitas = array_slice($topReceitas, 0, 5);
            
            // ========================================
            // RECEITAS E DESPESAS POR CATEGORIA
            // ========================================
            $receitasPorCategoria = [];
            foreach ($contasRecebidas as $conta) {
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $dataInicio && 
                    $conta['data_recebimento'] <= $dataFim) {
                    $cat = $conta['categoria_nome'] ?? 'Sem categoria';
                    $receitasPorCategoria[$cat] = ($receitasPorCategoria[$cat] ?? 0) + floatval($conta['valor_total'] ?? 0);
                }
            }
            arsort($receitasPorCategoria);
            
            $despesasPorCategoria = [];
            foreach ($contasPagas as $conta) {
                if ($conta['status'] === 'pago' && 
                    $conta['data_pagamento'] >= $dataInicio && 
                    $conta['data_pagamento'] <= $dataFim) {
                    $cat = $conta['categoria_nome'] ?? 'Sem categoria';
                    $despesasPorCategoria[$cat] = ($despesasPorCategoria[$cat] ?? 0) + floatval($conta['valor_total'] ?? 0);
                }
            }
            arsort($despesasPorCategoria);
            
            // ========================================
            // EVOLUÇÃO MENSAL (ÚLTIMOS 12 MESES)
            // ========================================
            $evolucaoMensal = [];
            for ($i = 11; $i >= 0; $i--) {
                $mesInicio = date('Y-m-01', strtotime("-{$i} months"));
                $mesFim = date('Y-m-t', strtotime("-{$i} months"));
                $mesLabel = strftime('%b/%y', strtotime($mesInicio));
                // fallback
                $mesLabel = date('M/y', strtotime($mesInicio));
                
                $rec = 0;
                $desp = 0;
                foreach ($contasRecebidas as $conta) {
                    if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                        !empty($conta['data_recebimento']) &&
                        $conta['data_recebimento'] >= $mesInicio && 
                        $conta['data_recebimento'] <= $mesFim) {
                        $rec += $conta['status'] === 'parcial' 
                            ? floatval($conta['valor_recebido'] ?? 0) 
                            : floatval($conta['valor_total'] ?? 0);
                    }
                }
                foreach ($contasPagas as $conta) {
                    if ($conta['status'] === 'pago' && 
                        $conta['data_pagamento'] >= $mesInicio && 
                        $conta['data_pagamento'] <= $mesFim) {
                        $desp += floatval($conta['valor_total'] ?? 0);
                    }
                }
                
                $evolucaoMensal[] = [
                    'mes' => $mesLabel,
                    'receitas' => $rec,
                    'despesas' => $desp,
                    'lucro' => $rec - $desp
                ];
            }
            
            // ========================================
            // TIMELINE DE VENCIMENTOS (PRÓXIMOS 7 DIAS)
            // ========================================
            $vencimentosProximos = [];
            $dataLimite = date('Y-m-d', strtotime('+7 days'));
            
            foreach ($contasRecebidas as $conta) {
                if (in_array($conta['status'], ['pendente', 'vencido', 'parcial']) && 
                    !empty($conta['data_vencimento']) &&
                    $conta['data_vencimento'] >= $hoje && 
                    $conta['data_vencimento'] <= $dataLimite) {
                    $vencimentosProximos[] = [
                        'tipo' => 'receber',
                        'descricao' => $conta['descricao'] ?? 'Sem descrição',
                        'valor' => floatval($conta['valor_total'] ?? 0),
                        'vencimento' => $conta['data_vencimento'],
                        'cliente' => $conta['cliente_nome'] ?? 'N/A',
                        'id' => $conta['id']
                    ];
                }
            }
            foreach ($contasPagas as $conta) {
                if (in_array($conta['status'], ['pendente', 'vencido', 'parcial']) && 
                    !empty($conta['data_vencimento']) &&
                    $conta['data_vencimento'] >= $hoje && 
                    $conta['data_vencimento'] <= $dataLimite) {
                    $vencimentosProximos[] = [
                        'tipo' => 'pagar',
                        'descricao' => $conta['descricao'] ?? 'Sem descrição',
                        'valor' => floatval($conta['valor_total'] ?? 0),
                        'vencimento' => $conta['data_vencimento'],
                        'fornecedor' => $conta['fornecedor_nome'] ?? 'N/A',
                        'id' => $conta['id']
                    ];
                }
            }
            usort($vencimentosProximos, fn($a, $b) => $a['vencimento'] <=> $b['vencimento']);
            
            // ========================================
            // MINI FLUXO DE CAIXA PROJETADO (30 DIAS)
            // ========================================
            $fluxoProjetado = [];
            $saldoAcumulado = $saldoTotal;
            for ($i = 0; $i < 30; $i++) {
                $dia = date('Y-m-d', strtotime("+{$i} days"));
                $entradasDia = 0;
                $saidasDia = 0;
                
                foreach ($contasRecebidas as $conta) {
                    if (in_array($conta['status'], ['pendente', 'vencido', 'parcial']) && 
                        !empty($conta['data_vencimento']) &&
                        $conta['data_vencimento'] === $dia) {
                        $entradasDia += floatval($conta['valor_total'] ?? 0);
                    }
                }
                foreach ($contasPagas as $conta) {
                    if (in_array($conta['status'], ['pendente', 'vencido', 'parcial']) && 
                        !empty($conta['data_vencimento']) &&
                        $conta['data_vencimento'] === $dia) {
                        $saidasDia += floatval($conta['valor_total'] ?? 0);
                    }
                }
                
                $saldoAcumulado += $entradasDia - $saidasDia;
                $fluxoProjetado[] = [
                    'dia' => $dia,
                    'entradas' => $entradasDia,
                    'saidas' => $saidasDia,
                    'saldo' => $saldoAcumulado
                ];
            }
            
            // ========================================
            // MINI DRE DO PERÍODO
            // ========================================
            $receitaBruta = $receitasUltimos30Dias;
            $deducoes = $receitaBruta * 0.0925; // PIS/COFINS/ISS estimado
            $receitaLiquida = $receitaBruta - $deducoes;
            $custoServicos = $despesasUltimos30Dias * 0.60;
            $lucroBrutoDRE = $receitaLiquida - $custoServicos;
            $despAdministrativas = $despesasOperacionais * 0.60;
            $despComerciais = $despesasOperacionais * 0.40;
            $resultadoOperacional = $lucroBrutoDRE - $despAdministrativas - $despComerciais;
            $resultadoFinanceiro = 0; // simplificado
            $resultadoAntesTributos = $resultadoOperacional + $resultadoFinanceiro;
            $impostoEstimado = $resultadoAntesTributos > 0 ? $resultadoAntesTributos * 0.15 : 0;
            $resultadoLiquido = $resultadoAntesTributos - $impostoEstimado;
            
            // ========================================
            // INDICADOR DE SAÚDE FINANCEIRA (SCORE 0-100)
            // ========================================
            $scoreComponents = [];
            
            // 1. Liquidez (runway > 6 meses = ótimo) - peso 25
            $scoreLiquidez = min(25, ($runway / 6) * 25);
            $scoreComponents['liquidez'] = ['score' => round($scoreLiquidez), 'max' => 25, 'label' => 'Liquidez'];
            
            // 2. Inadimplência (0% = ótimo, >10% = péssimo) - peso 25
            $scoreInadimplencia = max(0, 25 - ($taxaInadimplencia * 2.5));
            $scoreComponents['inadimplencia'] = ['score' => round($scoreInadimplencia), 'max' => 25, 'label' => 'Inadimplência'];
            
            // 3. Margem (margem líquida >20% = ótimo) - peso 25
            $scoreMargem = $margemLiquida > 0 ? min(25, ($margemLiquida / 20) * 25) : 0;
            $scoreComponents['margem'] = ['score' => round($scoreMargem), 'max' => 25, 'label' => 'Rentabilidade'];
            
            // 4. Tendência (receitas crescendo = bom) - peso 25
            $scoreTendencia = $varReceitas > 0 ? min(25, 12.5 + ($varReceitas / 20) * 12.5) : max(0, 12.5 + ($varReceitas / 20) * 12.5);
            $scoreComponents['tendencia'] = ['score' => round(max(0, min(25, $scoreTendencia))), 'max' => 25, 'label' => 'Tendência'];
            
            $saudeFinanceira = round(array_sum(array_column($scoreComponents, 'score')));
            $saudeFinanceira = max(0, min(100, $saudeFinanceira));
            
            if ($saudeFinanceira >= 80) $saudeLabel = 'Excelente';
            elseif ($saudeFinanceira >= 60) $saudeLabel = 'Bom';
            elseif ($saudeFinanceira >= 40) $saudeLabel = 'Regular';
            elseif ($saudeFinanceira >= 20) $saudeLabel = 'Atenção';
            else $saudeLabel = 'Crítico';
            
            // ========================================
            // ALERTAS INTELIGENTES
            // ========================================
            $alertas = [];
            
            // Conta bancária com saldo baixo
            foreach ($contasBancarias as $cb) {
                if (floatval($cb['saldo_atual']) < 1000 && floatval($cb['saldo_atual']) >= 0) {
                    $alertas[] = [
                        'tipo' => 'warning',
                        'icone' => 'bank',
                        'titulo' => 'Saldo Baixo',
                        'mensagem' => $cb['banco_nome'] . ' - Ag: ' . ($cb['agencia'] ?? '') . ' - Saldo: R$ ' . number_format($cb['saldo_atual'], 2, ',', '.'),
                        'link' => '/contas-bancarias'
                    ];
                }
                if (floatval($cb['saldo_atual']) < 0) {
                    $alertas[] = [
                        'tipo' => 'danger',
                        'icone' => 'bank',
                        'titulo' => 'Saldo Negativo',
                        'mensagem' => $cb['banco_nome'] . ' está com saldo negativo: R$ ' . number_format($cb['saldo_atual'], 2, ',', '.'),
                        'link' => '/contas-bancarias'
                    ];
                }
            }
            
            // Inadimplência alta
            if ($taxaInadimplencia > 5) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icone' => 'alert',
                    'titulo' => 'Inadimplência Alta',
                    'mensagem' => 'Taxa de inadimplência em ' . number_format($taxaInadimplencia, 1, ',', '.') . '% - ' . $totalContasVencidas . ' contas vencidas totalizando R$ ' . number_format($valorContasVencidas, 2, ',', '.'),
                    'link' => '/contas-receber?status=vencido'
                ];
            } elseif ($taxaInadimplencia > 2) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'alert',
                    'titulo' => 'Inadimplência Moderada',
                    'mensagem' => 'Taxa de inadimplência em ' . number_format($taxaInadimplencia, 1, ',', '.') . '% - Atenção necessária',
                    'link' => '/contas-receber?status=vencido'
                ];
            }
            
            // Runway baixo
            if ($runway < 3 && $runway > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icone' => 'clock',
                    'titulo' => 'Runway Crítico',
                    'mensagem' => 'Apenas ' . number_format($runway, 1, ',', '.') . ' meses de sobrevivência no ritmo atual',
                    'link' => null
                ];
            }
            
            // Despesas crescendo acima de 15%
            if ($varDespesas > 15) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'trending-up',
                    'titulo' => 'Despesas em Alta',
                    'mensagem' => 'Despesas cresceram ' . number_format($varDespesas, 1, ',', '.') . '% comparado ao mês anterior',
                    'link' => '/contas-pagar'
                ];
            }
            
            // Receitas caindo
            if ($varReceitas < -10) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'trending-down',
                    'titulo' => 'Receitas em Queda',
                    'mensagem' => 'Receitas caíram ' . number_format(abs($varReceitas), 1, ',', '.') . '% comparado ao mês anterior',
                    'link' => '/contas-receber'
                ];
            }
            
            // Transações bancárias pendentes de aprovação
            if ($transacoesPendentes > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'bank',
                    'titulo' => $transacoesPendentes . ' Transaç' . ($transacoesPendentes > 1 ? 'ões' : 'ão') . ' Bancária' . ($transacoesPendentes > 1 ? 's' : '') . ' Pendente' . ($transacoesPendentes > 1 ? 's' : ''),
                    'mensagem' => 'Há transações importadas dos bancos aguardando sua revisão e aprovação.',
                    'link' => '/transacoes-pendentes'
                ];
            }
            
            // Movimentações pendentes
            if ($movimentacoesPendentes > 10) {
                $alertas[] = [
                    'tipo' => 'info',
                    'icone' => 'list',
                    'titulo' => 'Movimentações Pendentes',
                    'mensagem' => $movimentacoesPendentes . ' movimentações aguardando conciliação',
                    'link' => '/movimentacoes-caixa'
                ];
            }
            
            // Contas a pagar vencidas
            $contasPagarVencidasQtd = $contasPagarResumo['vencidas']['quantidade'] ?? 0;
            if ($contasPagarVencidasQtd > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icone' => 'alert',
                    'titulo' => 'Contas a Pagar Vencidas',
                    'mensagem' => $contasPagarVencidasQtd . ' conta(s) a pagar vencida(s) - R$ ' . number_format($contasPagarResumo['vencidas']['valor_total'] ?? 0, 2, ',', '.'),
                    'link' => '/contas-pagar?status=vencido'
                ];
            }
            
            // Fluxo de caixa vai ficar negativo
            $fluxoNegativo = false;
            foreach ($fluxoProjetado as $fp) {
                if ($fp['saldo'] < 0 && !$fluxoNegativo) {
                    $fluxoNegativo = true;
                    $alertas[] = [
                        'tipo' => 'danger',
                        'icone' => 'trending-down',
                        'titulo' => 'Caixa Negativo Projetado',
                        'mensagem' => 'Saldo projetado ficará negativo em ' . date('d/m/Y', strtotime($fp['dia'])),
                        'link' => '/fluxo-caixa'
                    ];
                }
            }
            
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
                'bonificados' => $bonificadosResumo,
                'bonificadosPorEmpresa' => $bonificadosPorEmpresa,
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
                    'saldo_calculado' => $saldoCalculadoTotal,
                    'contas_com_api' => $contasComApi,
                    'diferenca' => $saldoTotal - $saldoCalculadoTotal,
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
                // Métricas Financeiras Avançadas
                'metricas_financeiras' => [
                    'periodo' => $periodoLabel,
                    'periodo_selecionado' => $periodoSelecionado,
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
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
                ],
                // Boletos Bancários
                'boletos' => $this->getBoletosResumo($empresasIds),
                // Métricas por Empresa (separadas visualmente)
                'metricas_por_empresa' => $metricasPorEmpresa,
                // Comparativo vs Mês Anterior
                'comparativo' => [
                    'receitas_anterior' => $receitasMesAnterior,
                    'despesas_anterior' => $despesasMesAnterior,
                    'lucro_anterior' => $lucroMesAnterior,
                    'ebitda_anterior' => $ebitdaMesAnterior,
                    'var_receitas' => $varReceitas,
                    'var_despesas' => $varDespesas,
                    'var_lucro' => $varLucro,
                    'var_ebitda' => $varEbitda
                ],
                // Aging de Recebíveis
                'aging' => ['valores' => $aging, 'quantidade' => $agingQtd],
                // Top 5
                'top_devedores' => $topDevedores,
                'top_despesas' => $topDespesas,
                'top_receitas' => $topReceitas,
                // Por Categoria
                'receitas_por_categoria' => $receitasPorCategoria,
                'despesas_por_categoria' => $despesasPorCategoria,
                // Evolução Mensal
                'evolucao_mensal' => $evolucaoMensal,
                // Vencimentos Próximos
                'vencimentos_proximos' => $vencimentosProximos,
                // Fluxo Projetado
                'fluxo_projetado' => $fluxoProjetado,
                // Mini DRE
                'mini_dre' => [
                    'receita_bruta' => $receitaBruta,
                    'deducoes' => $deducoes,
                    'receita_liquida' => $receitaLiquida,
                    'custo_servicos' => $custoServicos,
                    'lucro_bruto' => $lucroBrutoDRE,
                    'desp_administrativas' => $despAdministrativas,
                    'desp_comerciais' => $despComerciais,
                    'resultado_operacional' => $resultadoOperacional,
                    'resultado_financeiro' => $resultadoFinanceiro,
                    'resultado_antes_tributos' => $resultadoAntesTributos,
                    'imposto_estimado' => $impostoEstimado,
                    'resultado_liquido' => $resultadoLiquido
                ],
                // Saúde Financeira
                'saude_financeira' => [
                    'score' => $saudeFinanceira,
                    'label' => $saudeLabel,
                    'componentes' => $scoreComponents
                ],
                // Alertas Inteligentes
                'alertas' => $alertas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar dashboard: ' . $e->getMessage();
            return $this->render('home/index', [
                'title' => 'Dashboard - Sistema Financeiro',
                'metricas_por_empresa' => [],
                'filtro' => ['ativo' => false, 'empresas_ids' => [], 'total_empresas' => 0, 'empresas_filtradas' => 0],
                'todas_empresas' => [],
                'totais' => [
                    'empresas' => 0, 'usuarios' => 0, 'fornecedores' => 0, 'clientes' => 0,
                    'categorias' => 0, 'centros_custo' => 0, 'formas_pagamento' => 0,
                    'contas_bancarias' => 0, 'produtos' => 0, 'pedidos' => 0
                ],
                'produtos' => ['total' => 0, 'custo_total' => 0, 'valor_venda_total' => 0, 'margem_media' => 0, 'lucro_potencial' => 0],
                'pedidos' => ['total_pedidos' => 0, 'valor_total' => 0, 'ticket_medio' => 0, 'lucro_total' => 0, 'margem_lucro' => 0, 'pendentes' => 0, 'processando' => 0, 'concluidos' => 0, 'cancelados' => 0],
                'pedidosPorOrigem' => [],
                'bonificados' => ['total_pedidos' => 0, 'valor_total' => 0],
                'bonificadosPorEmpresa' => [],
                'empresasData' => [],
                'usuarios' => ['ativos' => 0, 'inativos' => 0],
                'fornecedores' => ['pf' => 0, 'pj' => 0],
                'clientes' => ['pf' => 0, 'pj' => 0],
                'categorias' => ['receita' => 0, 'despesa' => 0],
                'formas_pagamento' => ['pagamento' => 0, 'recebimento' => 0, 'ambos' => 0],
                'contas_bancarias' => ['corrente' => 0, 'poupanca' => 0, 'investimento' => 0, 'saldo_total' => 0, 'por_banco' => []],
                'contas_pagar' => ['total' => 0, 'valor_a_pagar' => 0, 'valor_pago' => 0, 'vencidas' => ['quantidade' => 0, 'valor_total' => 0], 'a_vencer_7d' => ['quantidade' => 0, 'valor_total' => 0], 'a_vencer_30d' => ['quantidade' => 0, 'valor_total' => 0], 'por_status' => ['pendente' => 0, 'vencido' => 0, 'parcial' => 0, 'pago' => 0]],
                'contas_receber' => ['total' => 0, 'valor_a_receber' => 0, 'valor_recebido' => 0, 'vencidas' => ['quantidade' => 0, 'valor_total' => 0], 'a_vencer_7d' => ['quantidade' => 0, 'valor_total' => 0], 'a_vencer_30d' => ['quantidade' => 0, 'valor_total' => 0], 'por_status' => ['pendente' => 0, 'vencido' => 0, 'parcial' => 0, 'recebido' => 0]],
                'movimentacoes_caixa' => ['total' => 0, 'entradas' => 0, 'saidas' => 0, 'saldo' => 0, 'conciliadas' => 0, 'pendentes' => 0],
                'metricas_financeiras' => [
                    'periodo' => 'Este mês', 'periodo_selecionado' => 'este_mes',
                    'data_inicio' => date('Y-m-01'), 'data_fim' => date('Y-m-d'),
                    'receitas' => 0, 'despesas' => 0, 'lucro_bruto' => 0, 'margem_bruta' => 0,
                    'despesas_operacionais' => 0, 'ebitda' => 0, 'margem_ebitda' => 0,
                    'lucro_liquido' => 0, 'margem_liquida' => 0, 'roi' => 0,
                    'ponto_equilibrio' => 0, 'margem_contribuicao' => 0,
                    'burn_rate' => 0, 'runway' => 0, 'ticket_medio' => 0,
                    'inadimplencia_valor' => 0, 'inadimplencia_taxa' => 0, 'contas_vencidas' => 0
                ],
                'sincronizacao_bancaria' => ['conexoes_ativas' => 0, 'transacoes_pendentes' => 0, 'transacoes_aprovadas' => 0, 'transacoes_ignoradas' => 0, 'ultima_sincronizacao' => null],
                'comparativo' => ['var_receitas' => 0, 'var_despesas' => 0, 'var_lucro' => 0, 'var_ebitda' => 0],
                'aging' => ['valores' => ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0], 'quantidade' => ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0]],
                'top_devedores' => [], 'top_despesas' => [], 'top_receitas' => [],
                'receitas_por_categoria' => [], 'despesas_por_categoria' => [],
                'evolucao_mensal' => [], 'vencimentos_proximos' => [], 'fluxo_projetado' => [],
                'mini_dre' => [],
                'saude_financeira' => ['score' => 0, 'label' => 'N/A', 'componentes' => []],
                'alertas' => []
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
     * Filtrar período do dashboard
     */
    public function filtrarPeriodo(Request $request, Response $response)
    {
        $periodo = $request->post('periodo', 'este_mes');
        $periodosValidos = ['hoje', 'ontem', 'esta_semana', 'semana_passada', 'este_mes', 'mes_passado', 'ultimos_30_dias', 'personalizado'];
        
        if (!in_array($periodo, $periodosValidos)) {
            $periodo = 'este_mes';
        }
        
        $_SESSION['dashboard_periodo'] = $periodo;
        
        if ($periodo === 'personalizado') {
            $dataInicio = $request->post('data_inicio', '');
            $dataFim = $request->post('data_fim', '');
            if ($dataInicio && $dataFim) {
                $_SESSION['dashboard_periodo_personalizado'] = [
                    'inicio' => $dataInicio,
                    'fim' => $dataFim
                ];
            }
        } else {
            unset($_SESSION['dashboard_periodo_personalizado']);
        }
        
        return $response->redirect('/');
    }
    
    /**
     * Calcular datas de início e fim com base no período selecionado
     */
    private function calcularPeriodo($periodo, $personalizado = null)
    {
        $hoje = date('Y-m-d');
        
        switch ($periodo) {
            case 'hoje':
                return ['inicio' => $hoje, 'fim' => $hoje, 'label' => 'Hoje'];
                
            case 'ontem':
                $ontem = date('Y-m-d', strtotime('-1 day'));
                return ['inicio' => $ontem, 'fim' => $ontem, 'label' => 'Ontem'];
                
            case 'esta_semana':
                // Segunda-feira desta semana até hoje
                $inicioSemana = date('Y-m-d', strtotime('monday this week'));
                return ['inicio' => $inicioSemana, 'fim' => $hoje, 'label' => 'Esta semana'];
                
            case 'semana_passada':
                $inicioSemanaPassada = date('Y-m-d', strtotime('monday last week'));
                $fimSemanaPassada = date('Y-m-d', strtotime('sunday last week'));
                return ['inicio' => $inicioSemanaPassada, 'fim' => $fimSemanaPassada, 'label' => 'Semana passada'];
                
            case 'este_mes':
                $inicioMes = date('Y-m-01');
                return ['inicio' => $inicioMes, 'fim' => $hoje, 'label' => 'Este mês'];
                
            case 'mes_passado':
                $inicioMesPassado = date('Y-m-01', strtotime('first day of last month'));
                $fimMesPassado = date('Y-m-t', strtotime('last month'));
                return ['inicio' => $inicioMesPassado, 'fim' => $fimMesPassado, 'label' => 'Mês passado'];
                
            case 'ultimos_30_dias':
                $inicio30 = date('Y-m-d', strtotime('-30 days'));
                return ['inicio' => $inicio30, 'fim' => $hoje, 'label' => 'Últimos 30 dias'];
                
            case 'personalizado':
                if ($personalizado && !empty($personalizado['inicio']) && !empty($personalizado['fim'])) {
                    $di = $personalizado['inicio'];
                    $df = $personalizado['fim'];
                    return [
                        'inicio' => $di,
                        'fim' => $df,
                        'label' => date('d/m', strtotime($di)) . ' a ' . date('d/m/Y', strtotime($df))
                    ];
                }
                // Fallback para este mês
                return ['inicio' => date('Y-m-01'), 'fim' => $hoje, 'label' => 'Este mês'];
                
            default:
                return ['inicio' => date('Y-m-01'), 'fim' => $hoje, 'label' => 'Este mês'];
        }
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
     * Calcular métricas financeiras por empresa individual
     * @param array $empresas Array de registros de empresas (from findByIds)
     */
    private function calcularMetricasPorEmpresa($empresas, $contaReceberModel, $contaPagarModel, $contaBancariaModel, $dataInicio, $dataFim)
    {
        $metricasPorEmpresa = [];
        
        foreach ($empresas as $empresa) {
            $empresaId = (int)($empresa['id'] ?? 0);
            if (!$empresaId) continue;
            
            // Contas a Receber e Pagar da empresa específica (exclui pedidos cancelados)
            $contasReceber = $contaReceberModel->findAll([
                'empresa_id' => $empresaId,
                'excluir_pedido_cancelado' => true
            ]);
            $contasPagar = $contaPagarModel->findAll(['empresa_id' => $empresaId]);
            
            // Calcular receitas e despesas da empresa (últimos 30 dias)
            $receitas = 0;
            $despesas = 0;
            
            foreach ($contasReceber as $conta) {
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $dataInicio && 
                    $conta['data_recebimento'] <= $dataFim) {
                    
                    $valorRecebido = $conta['status'] === 'parcial' 
                        ? floatval($conta['valor_recebido'] ?? 0) 
                        : floatval($conta['valor_total'] ?? 0);
                    
                    $receitas += $valorRecebido;
                }
            }
            
            foreach ($contasPagar as $conta) {
                if ($conta['status'] === 'pago' && 
                    $conta['data_pagamento'] >= $dataInicio && 
                    $conta['data_pagamento'] <= $dataFim) {
                    $despesas += $conta['valor_total'] ?? 0;
                }
            }
            
            // Calcular lucro líquido
            $lucroLiquido = $receitas - $despesas;
            
            // Margem líquida
            $margemLiquida = $receitas > 0 ? ($lucroLiquido / $receitas) * 100 : 0;
            
            // EBITDA simplificado
            $despesasOperacionais = $despesas * 0.20; // 20% estimado
            $custosVariaveis = $despesas * 0.60; // 60% estimado
            $ebitda = $receitas - $custosVariaveis - $despesasOperacionais;
            $margemEbitda = $receitas > 0 ? ($ebitda / $receitas) * 100 : 0;
            
            // Inadimplência da empresa
            $totalContasVencidas = 0;
            $valorContasVencidas = 0;
            foreach ($contasReceber as $conta) {
                // Status 'vencido' = conta pendente que passou do vencimento (definido no SQL do model)
                if ($conta['status'] === 'vencido' || 
                    ($conta['status'] === 'pendente' && isset($conta['data_vencimento']) && $conta['data_vencimento'] < date('Y-m-d'))) {
                    $totalContasVencidas++;
                    $valorContasVencidas += $conta['valor_total'] ?? 0;
                }
            }
            
            $valorTotalReceber = array_sum(array_column($contasReceber, 'valor_total'));
            $taxaInadimplencia = $valorTotalReceber > 0 ? ($valorContasVencidas / $valorTotalReceber) * 100 : 0;
            
            // Saldo em bancos da empresa
            $contasBancarias = $contaBancariaModel->findAll($empresaId);
            $saldoBancos = array_sum(array_column($contasBancarias, 'saldo_atual'));
            
            // Ticket médio
            $contasRecebidasNoPeriodo = 0;
            foreach ($contasReceber as $conta) {
                if (($conta['status'] === 'recebido' || $conta['status'] === 'parcial') && 
                    !empty($conta['data_recebimento']) &&
                    $conta['data_recebimento'] >= $dataInicio && 
                    $conta['data_recebimento'] <= $dataFim) {
                    $contasRecebidasNoPeriodo++;
                }
            }
            $ticketMedio = $contasRecebidasNoPeriodo > 0 ? ($receitas / $contasRecebidasNoPeriodo) : 0;
            
            // Burn Rate e Runway
            $burnRate = abs($despesas - $receitas);
            $runway = $burnRate > 0 ? ($saldoBancos / $burnRate) : 999;
            
            $metricasPorEmpresa[$empresaId] = [
                'empresa' => [
                    'id' => $empresaId,
                    'nome' => $empresa['nome_fantasia'],
                    'razao_social' => $empresa['razao_social'],
                    'cnpj' => $empresa['cnpj']
                ],
                'receitas' => $receitas,
                'despesas' => $despesas,
                'lucro_liquido' => $lucroLiquido,
                'margem_liquida' => $margemLiquida,
                'ebitda' => $ebitda,
                'margem_ebitda' => $margemEbitda,
                'saldo_bancos' => $saldoBancos,
                'taxa_inadimplencia' => $taxaInadimplencia,
                'ticket_medio' => $ticketMedio,
                'burn_rate' => $burnRate,
                'runway' => $runway,
                'contas_vencidas' => $totalContasVencidas,
                'valor_vencido' => $valorContasVencidas
            ];
        }
        
        return $metricasPorEmpresa;
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

    /**
     * Resumo de boletos para o dashboard principal.
     */
    private function getBoletosResumo(array $empresasIds): array
    {
        try {
            $boletoModel = new \App\Models\Boleto();
            $resumo = [
                'em_aberto' => 0,
                'valor_em_aberto' => 0,
                'vencidos' => 0,
                'valor_vencido' => 0,
                'liquidados_mes' => 0,
                'valor_liquidado_mes' => 0,
            ];
            foreach ($empresasIds as $empId) {
                $est = $boletoModel->getEstatisticas($empId);
                $resumo['em_aberto'] += $est['em_aberto'] ?? 0;
                $resumo['valor_em_aberto'] += $est['valor_em_aberto'] ?? 0;
                $resumo['vencidos'] += $est['vencidos'] ?? 0;
                $resumo['valor_vencido'] += $est['valor_vencido'] ?? 0;
                $resumo['liquidados_mes'] += $est['liquidados'] ?? 0;
                $resumo['valor_liquidado_mes'] += $est['valor_liquidado'] ?? 0;
            }
            return $resumo;
        } catch (\Exception $e) {
            return [
                'em_aberto' => 0, 'valor_em_aberto' => 0,
                'vencidos' => 0, 'valor_vencido' => 0,
                'liquidados_mes' => 0, 'valor_liquidado_mes' => 0,
            ];
        }
    }
}
