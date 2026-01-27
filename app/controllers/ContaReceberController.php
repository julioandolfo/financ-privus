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
            if ($request->get('cliente_id')) {
                $filters['cliente_id'] = $request->get('cliente_id');
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
            
            $contasReceber = $this->contaReceberModel->findAll($filters);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'receita');
            
            return $this->render('contas_receber/index', [
                'title' => 'Contas a Receber',
                'contasReceber' => $contasReceber,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'filters' => $filters
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
            
            // Cria conta a receber
            $this->contaReceberModel = new ContaReceber();
            $contaReceberId = $this->contaReceberModel->create($data);
            
            if (!$contaReceberId) {
                throw new \Exception('Erro ao criar conta a receber');
            }
            
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
            
            // Busca movimentações (histórico de recebimentos)
            $this->movimentacaoService = new MovimentacaoService();
            $movimentacoes = $this->movimentacaoService->buscarPorContaReceber($id);
            
            return $this->render('contas_receber/show', [
                'title' => 'Detalhes da Conta a Receber',
                'conta' => $contaReceber,
                'rateios' => $rateios,
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
            
            // Não permite editar conta já recebida
            if ($contaReceber['status'] == 'recebido') {
                $_SESSION['error'] = 'Não é possível editar uma conta já recebida!';
                $response->redirect('/contas-receber/' . $id);
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
            $this->contaReceberModel->update($id, $data);
            
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
            
            // Não permite excluir conta já recebida
            if ($contaReceber['status'] == 'recebido' || $contaReceber['status'] == 'parcial') {
                $_SESSION['error'] = 'Não é possível excluir uma conta já recebida ou parcialmente recebida!';
                $response->redirect('/contas-receber');
                return;
            }
            
            $this->contaReceberModel->cancelar($id);
            $_SESSION['success'] = 'Conta a receber cancelada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar conta a receber: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-receber');
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
            $this->contaReceberModel->atualizarRecebimento($id, $valorRecebido, $data['data_recebimento'], $status);
            
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
