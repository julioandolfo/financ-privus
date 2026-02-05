<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaReceber;
use App\Models\RateioRecebimento;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use App\Models\ParcelaReceber;
use App\Models\PedidoVinculado;
use App\Models\PedidoItem;
use includes\services\RateioService;
use includes\services\MovimentacaoService;

class ContaReceberController extends Controller
{
    private $contaReceberModel;
    private $rateioModel;
    private $empresaModel;
    private $clienteModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $formaPagamentoModel;
    private $contaBancariaModel;
    private $rateioService;
    private $movimentacaoService;

    public function index(Request $request, Response $response)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $this->empresaModel = new Empresa();
            $this->clienteModel = new Cliente();
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
            if ($request->get('cliente_id')) {
                $filters['cliente_id'] = $request->get('cliente_id');
            } elseif ($request->get('cliente_nome')) {
                $filters['cliente_nome'] = $request->get('cliente_nome');
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
            
            // Filtro de origem
            if ($request->get('origem')) {
                $filters['origem'] = $request->get('origem');
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
            if ($porPagina !== 'todos') {
                $filters['limite'] = (int) $porPagina;
            }
            
            $contasReceber = $this->contaReceberModel->findAll($filters);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'receita');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            
            // Retorna os filtros aplicados para a view
            $filtersApplied = $request->all();
            
            return $this->render('contas_receber/index', [
                'title' => 'Contas a Receber',
                'contasReceber' => $contasReceber,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'filters' => $filtersApplied
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar contas a receber: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $this->clienteModel = new Cliente();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'receita');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasRecebimento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            return $this->render('contas_receber/create', [
                'title' => 'Nova Conta a Receber',
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasRecebimento' => $formasRecebimento,
                'contasBancarias' => $contasBancarias
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/contas-receber');
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
                $response->redirect('/contas-receber/create');
                return;
            }
            
            // Prepara dados
            $data['usuario_cadastro_id'] = $_SESSION['usuario_id'];
            $data['status'] = 'pendente';
            $data['valor_recebido'] = 0;
            
            $this->contaReceberModel = new ContaReceber();
            
            // Verifica se é parcelado
            if (isset($data['eh_parcelado']) && $data['eh_parcelado'] == 1) {
                // Valida campos de parcelamento
                if (empty($data['parcelas_quantidade']) || $data['parcelas_quantidade'] < 2) {
                    $this->session->set('errors', ['parcelas_quantidade' => 'Informe pelo menos 2 parcelas']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-receber/create');
                    return;
                }
                
                if (empty($data['parcelas_primeiro_vencimento'])) {
                    $this->session->set('errors', ['parcelas_primeiro_vencimento' => 'Informe a data do primeiro vencimento']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-receber/create');
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
                $resultado = $this->contaReceberModel->criarParcelas($data, $configParcelas);
                
                if (empty($resultado['parcelas_ids'])) {
                    throw new \Exception('Erro ao criar parcelas');
                }
                
                $_SESSION['success'] = "Parcelamento criado com sucesso! {$resultado['total_parcelas']} parcelas geradas.";
                $response->redirect('/contas-receber');
                return;
            }
            
            // Cria conta a receber normal (não parcelada)
            $contaReceberId = $this->contaReceberModel->create($data);
            
            if (!$contaReceberId) {
                throw new \Exception('Erro ao criar conta a receber');
            }
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $contaReceberId,
                'create',
                null,
                $this->contaReceberModel->findById($contaReceberId),
                'Conta a receber criada'
            );
            
            // Se tem rateio, salva os rateios
            if (isset($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                // Valida rateios
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                if (!empty($errosRateio)) {
                    // Remove a conta criada
                    $this->contaReceberModel->cancelar($contaReceberId);
                    
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-receber/create');
                    return;
                }
                
                // Salva rateios
                $this->rateioModel = new RateioRecebimento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                $this->rateioModel->saveBatch($contaReceberId, $rateiosPreparados, $_SESSION['usuario_id']);
                
                // Atualiza flag de rateio
                $this->contaReceberModel->atualizarRateio($contaReceberId, 1);
            }
            
            // Se marcou como já recebido, registra o recebimento
            if (isset($data['ja_recebido']) && $data['ja_recebido'] == 1) {
                // Valida dados de recebimento
                if (empty($data['data_recebimento']) || empty($data['forma_recebimento_id']) || empty($data['conta_bancaria_id'])) {
                    $this->contaReceberModel->cancelar($contaReceberId);
                    $this->session->set('errors', ['ja_recebido' => 'Para marcar como já recebido, preencha a data, forma de recebimento e conta bancária']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-receber/create');
                    return;
                }
                
                $valorTotal = floatval($data['valor_total']);
                
                // Atualiza status para recebido
                $this->contaReceberModel->atualizarRecebimento($contaReceberId, $valorTotal, $data['data_recebimento'], 'recebido');
                
                // Cria movimentação de caixa
                $this->movimentacaoService = new MovimentacaoService();
                $dadosBaixa = [
                    'empresa_id' => $data['empresa_id'],
                    'categoria_id' => $data['categoria_id'],
                    'centro_custo_id' => $data['centro_custo_id'] ?? null,
                    'conta_bancaria_id' => $data['conta_bancaria_id'],
                    'descricao' => "Recebimento: " . $data['descricao'],
                    'valor' => $valorTotal,
                    'data_movimento' => $data['data_recebimento'],
                    'data_competencia' => $data['data_competencia'],
                    'forma_pagamento_id' => $data['forma_recebimento_id'],
                    'observacoes' => $data['observacoes_recebimento'] ?? null
                ];
                
                $this->movimentacaoService->criarMovimentacaoRecebimento($contaReceberId, $dadosBaixa);
                
                $_SESSION['success'] = 'Conta a receber criada e registrada como recebida com sucesso!';
            } else {
                $_SESSION['success'] = 'Conta a receber criada com sucesso!';
            }
            $response->redirect('/contas-receber');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar conta a receber: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/contas-receber/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            if (!$contaReceber) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaReceber['tem_rateio']) {
                $this->rateioModel = new RateioRecebimento();
                $rateios = $this->rateioModel->findByContaReceber($id);
            }
            
            // Busca informações de parcelamento se houver
            $resumoParcelas = null;
            if (!empty($contaReceber['grupo_parcela_id'])) {
                $resumoParcelas = $this->contaReceberModel->getResumoParcelas($contaReceber['grupo_parcela_id']);
            }
            
            // Busca parcelas da conta (via tabela parcelas_receber)
            $parcelaModel = new ParcelaReceber();
            $parcelas = $parcelaModel->findByContaReceber($id);
            $resumoParcelasTabela = $parcelaModel->getResumoByContaReceber($id);
            
            // Busca pedido vinculado se houver
            $pedidoVinculado = null;
            $itensPedido = [];
            if (!empty($contaReceber['pedido_id'])) {
                $pedidoModel = new PedidoVinculado();
                $pedidoVinculado = $pedidoModel->findById($contaReceber['pedido_id']);
                
                if ($pedidoVinculado) {
                    $pedidoItemModel = new PedidoItem();
                    $itensPedido = $pedidoItemModel->findByPedido($contaReceber['pedido_id']);
                    
                    // Calcular lucro e margem do pedido
                    $valorTotalPedido = floatval($pedidoVinculado['valor_total'] ?? 0);
                    $custoTotalPedido = floatval($pedidoVinculado['valor_custo_total'] ?? 0);
                    $pedidoVinculado['lucro'] = $valorTotalPedido - $custoTotalPedido;
                    $pedidoVinculado['margem_lucro'] = $valorTotalPedido > 0 
                        ? round(($pedidoVinculado['lucro'] / $valorTotalPedido) * 100, 2) 
                        : 0;
                }
            }
            
            // Busca movimentações (histórico de recebimentos)
            $this->movimentacaoService = new MovimentacaoService();
            $movimentacoes = $this->movimentacaoService->buscarPorContaReceber($id);
            
            return $this->render('contas_receber/show', [
                'title' => 'Detalhes da Conta a Receber',
                'conta' => $contaReceber,
                'rateios' => $rateios,
                'resumoParcelas' => $resumoParcelas,
                'parcelas' => $parcelas,
                'resumoParcelasTabela' => $resumoParcelasTabela,
                'pedidoVinculado' => $pedidoVinculado,
                'itensPedido' => $itensPedido,
                'movimentacoes' => $movimentacoes
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a receber: ' . $e->getMessage();
            $response->redirect('/contas-receber');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            if (!$contaReceber) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $this->clienteModel = new Cliente();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'receita');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasRecebimento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaReceber['tem_rateio']) {
                $this->rateioModel = new RateioRecebimento();
                $rateios = $this->rateioModel->findByContaReceber($id);
            }
            
            return $this->render('contas_receber/edit', [
                'title' => 'Editar Conta a Receber',
                'conta' => $contaReceber,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasRecebimento' => $formasRecebimento,
                'contasBancarias' => $contasBancarias,
                'rateios' => $rateios
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a receber: ' . $e->getMessage();
            $response->redirect('/contas-receber');
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
                $response->redirect("/contas-receber/{$id}/edit");
                return;
            }
            
            // Atualiza conta a receber
            $this->contaReceberModel = new ContaReceber();
            $dadosAntes = $this->contaReceberModel->findById($id);
            $this->contaReceberModel->update($id, $data);
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $id,
                'update',
                $dadosAntes,
                $this->contaReceberModel->findById($id),
                'Conta a receber atualizada'
            );
            
            // Atualiza rateios se necessário
            if (isset($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                // Valida rateios
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                if (!empty($errosRateio)) {
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect("/contas-receber/{$id}/edit");
                    return;
                }
                
                // Salva rateios
                $this->rateioModel = new RateioRecebimento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                $this->rateioModel->saveBatch($id, $rateiosPreparados, $_SESSION['usuario_id']);
                
                // Atualiza flag de rateio
                $this->contaReceberModel->atualizarRateio($id, 1);
            } else {
                // Remove rateios se desmarcou
                $this->rateioModel = new RateioRecebimento();
                $this->rateioModel->deleteByContaReceber($id);
                $this->contaReceberModel->atualizarRateio($id, 0);
            }
            
            $_SESSION['success'] = 'Conta a receber atualizada com sucesso!';
            $response->redirect('/contas-receber');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar conta a receber: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-receber/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            if (!$contaReceber) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            $motivo = $request->post('motivo', 'Registro excluído pelo usuário');
            
            // Soft delete - não remove do banco, apenas marca como deletado
            $this->contaReceberModel->softDelete($id, $motivo);
            $_SESSION['success'] = 'Conta a receber excluída com sucesso! (É possível restaurar em Registros Deletados)';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir conta a receber: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-receber');
    }
    
    /**
     * Restaura uma conta deletada
     */
    public function restore(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            
            $this->contaReceberModel->restore($id);
            $_SESSION['success'] = 'Conta a receber restaurada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao restaurar conta a receber: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-receber/deletados');
    }
    
    /**
     * Cancela um recebimento já realizado
     */
    public function cancelarRecebimento(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            if (!$contaReceber) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            $motivo = $request->post('motivo', 'Recebimento cancelado pelo usuário');
            
            $this->contaReceberModel->cancelarRecebimento($id, $motivo);
            $_SESSION['success'] = 'Recebimento cancelado com sucesso! A conta voltou para status "Pendente".';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar recebimento: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-receber/' . $id);
    }
    
    /**
     * Lista registros deletados
     */
    public function deletados(Request $request, Response $response)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            
            // Buscar empresas do usuário
            $empresaModel = new \App\Models\Empresa();
            $empresas = $empresaModel->findAll(['ativo' => 1]);
            $empresasIds = array_column($empresas, 'id');
            
            $contasDeletadas = $this->contaReceberModel->findDeleted($empresasIds);
            
            return $this->render('contas_receber/deletados', [
                'title' => 'Contas a Receber - Registros Deletados',
                'contas' => $contasDeletadas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar registros deletados: ' . $e->getMessage();
            $response->redirect('/contas-receber');
        }
    }
    
    /**
     * Visualiza histórico de auditoria de uma conta
     */
    public function historico(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $auditoriaModel = new \App\Models\Auditoria();
            
            $conta = $this->contaReceberModel->findByIdWithDeleted($id);
            
            if (!$conta) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            $historico = $auditoriaModel->getHistorico('contas_receber', $id);
            
            return $this->render('contas_receber/historico', [
                'title' => 'Histórico de Auditoria - Conta a Receber #' . $id,
                'conta' => $conta,
                'historico' => $historico
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar histórico: ' . $e->getMessage();
            $response->redirect('/contas-receber');
        }
    }
    
    /**
     * Formulário de baixa (recebimento) da conta
     */
    public function baixar(Request $request, Response $response, $id)
    {
        try {
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            if (!$contaReceber) {
                $_SESSION['error'] = 'Conta a receber não encontrada!';
                $response->redirect('/contas-receber');
                return;
            }
            
            // Não permite baixar conta já recebida
            if ($contaReceber['status'] == 'recebido') {
                $_SESSION['error'] = 'Esta conta já está recebida!';
                $response->redirect('/contas-receber/' . $id);
                return;
            }
            
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $formasRecebimento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            return $this->render('contas_receber/baixar', [
                'title' => 'Baixar Conta a Receber',
                'conta' => $contaReceber,
                'formasRecebimento' => $formasRecebimento,
                'contasBancarias' => $contasBancarias
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário de baixa: ' . $e->getMessage();
            $response->redirect('/contas-receber');
        }
    }
    
    /**
     * Processa baixa (recebimento) da conta
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
                $response->redirect("/contas-receber/{$id}/baixar");
                return;
            }
            
            $this->contaReceberModel = new ContaReceber();
            $contaReceber = $this->contaReceberModel->findById($id);
            
            $valorRecebimento = floatval($data['valor_recebimento']);
            $valorJaRecebido = $contaReceber['valor_recebido'];
            $valorRecebido = $valorJaRecebido + $valorRecebimento;
            $valorTotal = $contaReceber['valor_total'];
            
            // Determina status
            if ($valorRecebido >= $valorTotal) {
                $status = 'recebido';
            } else {
                $status = 'parcial';
            }
            
            // Atualiza conta
            $dadosAntes = $contaReceber;
            $this->contaReceberModel->atualizarRecebimento($id, $valorRecebido, $data['data_recebimento'], $status);
            
            // Registrar auditoria do recebimento
            \App\Models\Auditoria::registrar(
                'contas_receber',
                $id,
                'make_receipt',
                $dadosAntes,
                $this->contaReceberModel->findById($id),
                "Recebimento de R$ " . number_format($valorRecebimento, 2, ',', '.')
            );
            
            // Cria movimentação de caixa
            $this->movimentacaoService = new MovimentacaoService();
            $dadosBaixa = [
                'empresa_id' => $contaReceber['empresa_id'],
                'categoria_id' => $contaReceber['categoria_id'],
                'centro_custo_id' => $contaReceber['centro_custo_id'],
                'conta_bancaria_id' => $data['conta_bancaria_id'],
                'descricao' => "Recebimento: " . $contaReceber['descricao'],
                'valor_recebido' => $valorRecebimento,
                'data_recebimento' => $data['data_recebimento'],
                'data_competencia' => $contaReceber['data_competencia'],
                'forma_recebimento_id' => $data['forma_recebimento_id'],
                'observacoes' => $data['observacoes_recebimento'] ?? null
            ];
            
            $this->movimentacaoService->criarMovimentacaoRecebimento($id, $dadosBaixa);
            
            $_SESSION['success'] = 'Recebimento registrado com sucesso!';
            $response->redirect('/contas-receber/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao registrar recebimento: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-receber/{$id}/baixar");
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
        
        // Valor recebimento
        if (empty($data['valor_recebimento']) || $data['valor_recebimento'] <= 0) {
            $errors['valor_recebimento'] = 'O valor do recebimento deve ser maior que zero';
        }
        
        // Data de recebimento
        if (empty($data['data_recebimento'])) {
            $errors['data_recebimento'] = 'A data de recebimento é obrigatória';
        }
        
        // Conta bancária
        if (empty($data['conta_bancaria_id'])) {
            $errors['conta_bancaria_id'] = 'A conta bancária é obrigatória';
        }
        
        // Forma de recebimento
        if (empty($data['forma_recebimento_id'])) {
            $errors['forma_recebimento_id'] = 'A forma de recebimento é obrigatória';
        }
        
        return $errors;
    }
}
