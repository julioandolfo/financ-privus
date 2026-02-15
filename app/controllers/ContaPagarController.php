<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaPagar;
use App\Models\RateioPagamento;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use App\Models\TransacaoPendente;
use includes\services\RateioService;
use includes\services\MovimentacaoService;

class ContaPagarController extends Controller
{
    private $contaPagarModel;
    private $rateioModel;
    private $empresaModel;
    private $fornecedorModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $formaPagamentoModel;
    private $contaBancariaModel;
    private $rateioService;
    private $movimentacaoService;

    public function index(Request $request, Response $response)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            
            // Prepara filtros
            $filters = [];
            
            // Filtro de empresa
            $empresaId = $request->get('empresa_id');
            if ($empresaId) {
                $filters['empresa_id'] = $empresaId;
            }
            
            // Filtros básicos
            if ($request->get('status')) {
                $filters['status'] = $request->get('status');
            }
            
            // Filtros por ID ou por Nome (consolidado)
            if ($request->get('fornecedor_id')) {
                $filters['fornecedor_id'] = $request->get('fornecedor_id');
            } elseif ($request->get('fornecedor_nome')) {
                $filters['fornecedor_nome'] = $request->get('fornecedor_nome');
            }
            
            if ($request->get('categoria_id')) {
                $filters['categoria_id'] = $request->get('categoria_id');
            } elseif ($request->get('categoria_nome')) {
                $filters['categoria_nome'] = $request->get('categoria_nome');
            }
            
            if ($request->get('centro_custo_id')) {
                $filters['centro_custo_id'] = $request->get('centro_custo_id');
            } elseif ($request->get('centro_custo_nome')) {
                $filters['centro_custo_nome'] = $request->get('centro_custo_nome');
            }
            
            if ($request->get('forma_pagamento_id')) {
                $filters['forma_pagamento_id'] = $request->get('forma_pagamento_id');
            }
            if ($request->get('tipo_custo')) {
                $filters['tipo_custo'] = $request->get('tipo_custo');
            }
            
            // Filtros de data
            if ($request->get('data_inicio')) {
                $filters['data_vencimento_inicio'] = $request->get('data_inicio');
            }
            if ($request->get('data_fim')) {
                $filters['data_vencimento_fim'] = $request->get('data_fim');
            }
            
            // Filtros de valor
            if ($request->get('valor_min')) {
                $filters['valor_min'] = $request->get('valor_min');
            }
            if ($request->get('valor_max')) {
                $filters['valor_max'] = $request->get('valor_max');
            }
            
            // Filtro de parcelamento
            if ($request->get('parcelamento')) {
                $filters['parcelamento'] = $request->get('parcelamento');
            }
            
            // Busca
            if ($request->get('search')) {
                $filters['search'] = $request->get('search');
            }
            
            // Ordenação
            if ($request->get('ordenar')) {
                $filters['ordenar'] = $request->get('ordenar');
            }
            
            // Paginação
            $porPagina = $request->get('por_pagina') ?? 25;
            $paginaAtual = $request->get('pagina') ?? 1;
            $paginaAtual = max(1, (int)$paginaAtual);
            
            // Busca total de registros para calcular paginação
            $totalRegistros = $this->contaPagarModel->countWithFilters($filters);
            
            // Calcula paginação
            $totalPaginas = 1;
            $offset = 0;
            
            if ($porPagina !== 'todos') {
                $porPagina = (int) $porPagina;
                $totalPaginas = ceil($totalRegistros / $porPagina);
                
                // Ajusta página atual se estiver fora do range
                if ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
                    $paginaAtual = $totalPaginas;
                }
                
                $offset = ($paginaAtual - 1) * $porPagina;
                $filters['limite'] = $porPagina;
                $filters['offset'] = $offset;
            }
            
            $contasPagar = $this->contaPagarModel->findAll($filters);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            
            // Retorna os filtros aplicados para a view
            $filtersApplied = $request->all();
            
            // Contar transações pendentes de aprovação (tipo débito = despesas)
            $transacaoPendenteModel = new TransacaoPendente();
            $transacoesPendentesCount = 0;
            if ($empresaId) {
                $transacoesPendentesCount = $transacaoPendenteModel->countByEmpresa($empresaId, 'pendente');
            } elseif ($empresaAtual) {
                $transacoesPendentesCount = $transacaoPendenteModel->countByEmpresa($empresaAtual, 'pendente');
            }
            
            return $this->render('contas_pagar/index', [
                'title' => 'Contas a Pagar',
                'contasPagar' => $contasPagar,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'filters' => $filtersApplied,
                'transacoes_pendentes_count' => $transacoesPendentesCount,
                'paginacao' => [
                    'total_registros' => $totalRegistros,
                    'por_pagina' => $porPagina,
                    'pagina_atual' => $paginaAtual,
                    'total_paginas' => $totalPaginas,
                    'offset' => $offset
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar contas a pagar: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            return $this->render('contas_pagar/create', [
                'title' => 'Nova Conta a Pagar',
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }

    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->all();
            
            // Validações
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect('/contas-pagar/create');
                return;
            }
            
            // Prepara dados
            $data['usuario_cadastro_id'] = $_SESSION['usuario_id'];
            $data['status'] = 'pendente';
            $data['valor_pago'] = 0;
            
            $this->contaPagarModel = new ContaPagar();
            
            // Verifica se é parcelado
            if (isset($data['eh_parcelado']) && $data['eh_parcelado'] == 1) {
                // Valida campos de parcelamento
                if (empty($data['parcelas_quantidade']) || $data['parcelas_quantidade'] < 2) {
                    $this->session->set('errors', ['parcelas_quantidade' => 'Informe pelo menos 2 parcelas']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                if (empty($data['parcelas_primeiro_vencimento'])) {
                    $this->session->set('errors', ['parcelas_primeiro_vencimento' => 'Informe a data do primeiro vencimento']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                // Prepara configuração de parcelas
                $configParcelas = [
                    'quantidade' => $data['parcelas_quantidade'],
                    'primeiro_vencimento' => $data['parcelas_primeiro_vencimento'],
                    'intervalo' => $data['parcelas_intervalo'] ?? 'mensal',
                    'intervalo_dias' => $data['parcelas_intervalo_dias'] ?? 30,
                    'tipo_valor' => $data['parcelas_tipo_valor'] ?? 'diluido',
                    'status_inicial' => $data['parcelas_status_inicial'] ?? 'pendente'
                ];
                
                // Cria as parcelas
                $resultado = $this->contaPagarModel->criarParcelas($data, $configParcelas);
                
                if (empty($resultado['parcelas_ids'])) {
                    throw new \Exception('Erro ao criar parcelas');
                }
                
                $_SESSION['success'] = "Parcelamento criado com sucesso! {$resultado['total_parcelas']} parcelas geradas.";
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Cria conta a pagar normal (não parcelada)
            $contaPagarId = $this->contaPagarModel->create($data);
            
            if (!$contaPagarId) {
                throw new \Exception('Erro ao criar conta a pagar');
            }
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $contaPagarId,
                'create',
                null,
                $this->contaPagarModel->findById($contaPagarId),
                'Conta a pagar criada'
            );
            
            // Se tem rateio, salva os rateios
            if (isset($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                // Valida rateios
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                if (!empty($errosRateio)) {
                    // Remove a conta criada
                    $this->contaPagarModel->cancelar($contaPagarId);
                    
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                // Salva rateios
                $this->rateioModel = new RateioPagamento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                $this->rateioModel->saveBatch($contaPagarId, $rateiosPreparados, $_SESSION['usuario_id']);
                
                // Atualiza flag de rateio
                $this->contaPagarModel->atualizarRateio($contaPagarId, 1);
            }
            
            // Se marcou como já pago, registra o pagamento
            if (isset($data['ja_pago']) && $data['ja_pago'] == 1) {
                // Valida dados de pagamento
                if (empty($data['data_pagamento']) || empty($data['forma_pagamento_id']) || empty($data['conta_bancaria_id'])) {
                    $this->contaPagarModel->cancelar($contaPagarId);
                    $this->session->set('errors', ['ja_pago' => 'Para marcar como já pago, preencha a data, forma de pagamento e conta bancária']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                $valorTotal = floatval($data['valor_total']);
                
                // Atualiza status para pago
                $this->contaPagarModel->atualizarPagamento($contaPagarId, $valorTotal, $data['data_pagamento'], 'pago');
                
                // Cria movimentação de caixa
                $this->movimentacaoService = new MovimentacaoService();
                $dadosBaixa = [
                    'empresa_id' => $data['empresa_id'],
                    'categoria_id' => $data['categoria_id'],
                    'centro_custo_id' => $data['centro_custo_id'] ?? null,
                    'conta_bancaria_id' => $data['conta_bancaria_id'],
                    'descricao' => "Pagamento: " . $data['descricao'],
                    'valor' => $valorTotal,
                    'data_movimento' => $data['data_pagamento'],
                    'data_competencia' => $data['data_competencia'],
                    'forma_pagamento_id' => $data['forma_pagamento_id'],
                    'observacoes' => $data['observacoes_pagamento'] ?? null
                ];
                
                $this->movimentacaoService->criarMovimentacaoPagamento($contaPagarId, $dadosBaixa);
                
                $_SESSION['success'] = 'Conta a pagar criada e registrada como paga com sucesso!';
            } else {
                $_SESSION['success'] = 'Conta a pagar criada com sucesso!';
            }
            
            // Se marcou para tornar recorrente, cria a despesa recorrente
            if (isset($data['tornar_recorrente']) && $data['tornar_recorrente'] == 1) {
                $recorrenciaService = new \Includes\Services\RecorrenciaService();
                $contaPagar = $this->contaPagarModel->findById($contaPagarId);
                
                $configRecorrencia = [
                    'frequencia' => $data['recorrencia_frequencia'] ?? 'mensal',
                    'dia_mes' => $data['recorrencia_dia_mes'] ?? date('j'),
                    'data_inicio' => $data['recorrencia_data_inicio'] ?? date('Y-m-d'),
                    'antecedencia_dias' => 5,
                    'status_inicial' => 'pendente',
                    'criar_automaticamente' => 1
                ];
                
                $recorrenciaService->criarDespesaRecorrenteDeContaPagar($contaPagar, $configRecorrencia, $_SESSION['usuario_id']);
                
                $_SESSION['success'] .= ' Despesa recorrente criada!';
            }
            
            $response->redirect('/contas-pagar');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar conta a pagar: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/contas-pagar/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaPagar['tem_rateio']) {
                $this->rateioModel = new RateioPagamento();
                $rateios = $this->rateioModel->findByContaPagar($id);
            }
            
            // Busca informações de parcelamento se houver
            $resumoParcelas = null;
            if (!empty($contaPagar['grupo_parcela_id'])) {
                $resumoParcelas = $this->contaPagarModel->getResumoParcelas($contaPagar['grupo_parcela_id']);
            }
            
            // Busca movimentações (histórico de pagamentos)
            $this->movimentacaoService = new MovimentacaoService();
            $movimentacoes = $this->movimentacaoService->buscarPorContaPagar($id);

            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();

            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();

            $openBaixaModal = $request->get('acao') === 'baixar';
            
            return $this->render('contas_pagar/show', [
                'title' => 'Detalhes da Conta a Pagar',
                'conta' => $contaPagar,
                'rateios' => $rateios,
                'resumoParcelas' => $resumoParcelas,
                'movimentacoes' => $movimentacoes,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'openBaixaModal' => $openBaixaModal
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a pagar: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaPagar['tem_rateio']) {
                $this->rateioModel = new RateioPagamento();
                $rateios = $this->rateioModel->findByContaPagar($id);
            }
            
            return $this->render('contas_pagar/edit', [
                'title' => 'Editar Conta a Pagar',
                'conta' => $contaPagar,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'rateios' => $rateios
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a pagar: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }

    public function update(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Validações
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect("/contas-pagar/{$id}/edit");
                return;
            }
            
            // Atualiza conta a pagar
            $this->contaPagarModel = new ContaPagar();
            $dadosAntes = $this->contaPagarModel->findById($id);
            $this->contaPagarModel->update($id, $data);
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $id,
                'update',
                $dadosAntes,
                $this->contaPagarModel->findById($id),
                'Conta a pagar atualizada'
            );
            
            // Atualiza rateios se necessário
            if (isset($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                // Valida rateios
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                if (!empty($errosRateio)) {
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect("/contas-pagar/{$id}/edit");
                    return;
                }
                
                // Salva rateios
                $this->rateioModel = new RateioPagamento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                $this->rateioModel->saveBatch($id, $rateiosPreparados, $_SESSION['usuario_id']);
                
                // Atualiza flag de rateio
                $this->contaPagarModel->atualizarRateio($id, 1);
            } else {
                // Remove rateios se desmarcou
                $this->rateioModel = new RateioPagamento();
                $this->rateioModel->deleteByContaPagar($id);
                $this->contaPagarModel->atualizarRateio($id, 0);
            }
            
            $_SESSION['success'] = 'Conta a pagar atualizada com sucesso!';
            $response->redirect('/contas-pagar');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar conta a pagar: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-pagar/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $motivo = $request->post('motivo', 'Registro excluído pelo usuário');
            
            // Soft delete - não remove do banco, apenas marca como deletado
            $this->contaPagarModel->softDelete($id, $motivo);
            $_SESSION['success'] = 'Conta a pagar excluída com sucesso! (É possível restaurar em Registros Deletados)';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir conta a pagar: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar');
    }
    
    /**
     * Restaura uma conta deletada
     */
    public function restore(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            
            $this->contaPagarModel->restore($id);
            $_SESSION['success'] = 'Conta a pagar restaurada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao restaurar conta a pagar: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar/deletados');
    }
    
    /**
     * Cancela um pagamento já realizado
     */
    public function cancelarPagamento(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $motivo = $request->post('motivo', 'Pagamento cancelado pelo usuário');
            
            $this->contaPagarModel->cancelarPagamento($id, $motivo);
            $_SESSION['success'] = 'Pagamento cancelado com sucesso! A conta voltou para status "Pendente".';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar pagamento: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar/' . $id);
    }
    
    /**
     * Lista registros deletados
     */
    public function deletados(Request $request, Response $response)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            
            // Buscar empresas do usuário
            $empresaModel = new \App\Models\Empresa();
            $empresas = $empresaModel->findAll(['ativo' => 1]);
            $empresasIds = array_column($empresas, 'id');
            
            $contasDeletadas = $this->contaPagarModel->findDeleted($empresasIds);
            
            return $this->render('contas_pagar/deletados', [
                'title' => 'Contas a Pagar - Registros Deletados',
                'contas' => $contasDeletadas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar registros deletados: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Visualiza histórico de auditoria de uma conta
     */
    public function historico(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $auditoriaModel = new \App\Models\Auditoria();
            
            $conta = $this->contaPagarModel->findByIdWithDeleted($id);
            
            if (!$conta) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $historico = $auditoriaModel->getHistorico('contas_pagar', $id);
            
            return $this->render('contas_pagar/historico', [
                'title' => 'Histórico de Auditoria - Conta a Pagar #' . $id,
                'conta' => $conta,
                'historico' => $historico
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar histórico: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Formulário de baixa (pagamento) da conta
     */
    public function baixar(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Não permite baixar conta já paga
            if ($contaPagar['status'] == 'pago') {
                $_SESSION['error'] = 'Esta conta já está paga!';
                $response->redirect('/contas-pagar/' . $id);
                return;
            }
            
            // Redireciona para a tela de detalhes com modal de baixa
            $response->redirect("/contas-pagar/{$id}?acao=baixar");
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário de baixa: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Processa baixa (pagamento) da conta
     */
    public function efetuarBaixa(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Validações de baixa
            $errors = $this->validateBaixa($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect("/contas-pagar/{$id}?acao=baixar");
                return;
            }
            
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            $valorPagamento = floatval($data['valor_pagamento']);
            $valorJaPago = $contaPagar['valor_pago'];
            $valorPago = $valorJaPago + $valorPagamento;
            $valorTotal = $contaPagar['valor_total'];
            
            // Determina status
            if ($valorPago >= $valorTotal) {
                $status = 'pago';
            } else {
                $status = 'parcial';
            }
            
            // Atualiza conta
            $dadosAntes = $contaPagar;
            $this->contaPagarModel->atualizarPagamento($id, $valorPago, $data['data_pagamento'], $status);
            
            // Registrar auditoria do pagamento
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $id,
                'make_payment',
                $dadosAntes,
                $this->contaPagarModel->findById($id),
                "Pagamento de R$ " . number_format($valorPagamento, 2, ',', '.')
            );
            
            // Cria movimentação de caixa
            $this->movimentacaoService = new MovimentacaoService();
            $dadosBaixa = [
                'empresa_id' => $contaPagar['empresa_id'],
                'categoria_id' => $contaPagar['categoria_id'],
                'centro_custo_id' => $contaPagar['centro_custo_id'],
                'conta_bancaria_id' => $data['conta_bancaria_id'],
                'descricao' => "Pagamento: " . $contaPagar['descricao'],
                'valor' => $valorPagamento,
                'data_movimento' => $data['data_pagamento'],
                'data_competencia' => $contaPagar['data_competencia'],
                'forma_pagamento_id' => $data['forma_pagamento_id'],
                'observacoes' => $data['observacoes_pagamento'] ?? null
            ];
            
            $this->movimentacaoService->criarMovimentacaoPagamento($id, $dadosBaixa);
            
            $_SESSION['success'] = 'Pagamento registrado com sucesso!';
            $response->redirect('/contas-pagar/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao registrar pagamento: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-pagar/{$id}?acao=baixar");
        }
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Categoria
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'A categoria é obrigatória';
        }
        
        // Número do documento
        if (empty($data['numero_documento'])) {
            $errors['numero_documento'] = 'O número do documento é obrigatório';
        }
        
        // Descrição
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'A descrição é obrigatória';
        }
        
        // Valor total
        if (empty($data['valor_total']) || $data['valor_total'] <= 0) {
            $errors['valor_total'] = 'O valor total deve ser maior que zero';
        }
        
        // Data de emissão
        if (empty($data['data_emissao'])) {
            $errors['data_emissao'] = 'A data de emissão é obrigatória';
        }
        
        // Data de competência
        if (empty($data['data_competencia'])) {
            $errors['data_competencia'] = 'A data de competência é obrigatória';
        }
        
        // Data de vencimento
        if (empty($data['data_vencimento'])) {
            $errors['data_vencimento'] = 'A data de vencimento é obrigatória';
        }
        
        return $errors;
    }
    
    protected function validateBaixa($data)
    {
        $errors = [];
        
        // Valor pagamento
        if (empty($data['valor_pagamento']) || $data['valor_pagamento'] <= 0) {
            $errors['valor_pagamento'] = 'O valor do pagamento deve ser maior que zero';
        }
        
        // Data de pagamento
        if (empty($data['data_pagamento'])) {
            $errors['data_pagamento'] = 'A data de pagamento é obrigatória';
        }
        
        // Conta bancária
        if (empty($data['conta_bancaria_id'])) {
            $errors['conta_bancaria_id'] = 'A conta bancária é obrigatória';
        }
        
        // Forma de pagamento
        if (empty($data['forma_pagamento_id'])) {
            $errors['forma_pagamento_id'] = 'A forma de pagamento é obrigatória';
        }
        
        return $errors;
    }
}
