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
            
            // Verifica se está em modo consolidação
            $empresasIds = $_SESSION['empresas_consolidacao'] ?? [];
            $empresaId = $request->get('empresa_id');
            
            // Prepara filtros
            $filters = [];
            if (!empty($empresasIds)) {
                $filters['empresas_ids'] = $empresasIds;
            } elseif ($empresaId) {
                $filters['empresa_id'] = $empresaId;
            }
            
            // Outros filtros
            if ($request->get('status')) {
                $filters['status'] = $request->get('status');
            }
            if ($request->get('fornecedor_id')) {
                $filters['fornecedor_id'] = $request->get('fornecedor_id');
            }
            if ($request->get('categoria_id')) {
                $filters['categoria_id'] = $request->get('categoria_id');
            }
            if ($request->get('data_inicio')) {
                $filters['data_vencimento_inicio'] = $request->get('data_inicio');
            }
            if ($request->get('data_fim')) {
                $filters['data_vencimento_fim'] = $request->get('data_fim');
            }
            if ($request->get('search')) {
                $filters['search'] = $request->get('search');
            }
            
            $contasPagar = $this->contaPagarModel->findAll($filters);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            
            return $this->render('contas_pagar/index', [
                'title' => 'Contas a Pagar',
                'contasPagar' => $contasPagar,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'filters' => $filters
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
            
            // Cria conta a pagar
            $this->contaPagarModel = new ContaPagar();
            $contaPagarId = $this->contaPagarModel->create($data);
            
            if (!$contaPagarId) {
                throw new \Exception('Erro ao criar conta a pagar');
            }
            
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
            
            $_SESSION['success'] = 'Conta a pagar criada com sucesso!';
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
            
            // Busca movimentações (histórico de pagamentos)
            $this->movimentacaoService = new MovimentacaoService();
            $movimentacoes = $this->movimentacaoService->buscarPorContaPagar($id);
            
            return $this->render('contas_pagar/show', [
                'title' => 'Detalhes da Conta a Pagar',
                'conta' => $contaPagar,
                'rateios' => $rateios,
                'movimentacoes' => $movimentacoes
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
            
            // Não permite editar conta já paga
            if ($contaPagar['status'] == 'pago') {
                $_SESSION['error'] = 'Não é possível editar uma conta já paga!';
                $response->redirect('/contas-pagar/' . $id);
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
            $this->contaPagarModel->update($id, $data);
            
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
            
            // Não permite excluir conta já paga
            if ($contaPagar['status'] == 'pago' || $contaPagar['status'] == 'parcial') {
                $_SESSION['error'] = 'Não é possível excluir uma conta já paga ou parcialmente paga!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $this->contaPagarModel->cancelar($id);
            $_SESSION['success'] = 'Conta a pagar cancelada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar conta a pagar: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar');
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
            
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            $valorRestante = $contaPagar['valor_total'] - $contaPagar['valor_pago'];
            
            return $this->render('contas_pagar/baixar', [
                'title' => 'Baixar Conta a Pagar',
                'conta' => $contaPagar,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias
            ]);
            
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
                $response->redirect("/contas-pagar/{$id}/baixar");
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
            $this->contaPagarModel->atualizarPagamento($id, $valorPago, $data['data_pagamento'], $status);
            
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
            $response->redirect("/contas-pagar/{$id}/baixar");
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
