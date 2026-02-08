<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\MovimentacaoCaixa;
use App\Models\Empresa;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\ContaBancaria;
use App\Models\FormaPagamento;
use App\Models\ContaPagar;
use App\Models\ContaReceber;

class MovimentacaoCaixaController extends Controller
{
    private $movimentacaoModel;
    private $empresaModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $contaBancariaModel;
    private $formaPagamentoModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->movimentacaoModel = new MovimentacaoCaixa();
        $this->empresaModel = new Empresa();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->centroCustoModel = new CentroCusto();
        $this->contaBancariaModel = new ContaBancaria();
        $this->formaPagamentoModel = new FormaPagamento();
    }
    
    /**
     * Lista movimentações de caixa
     */
    public function index(Request $request, Response $response)
    {
        // Filtros
        $empresaId = $request->get('empresa_id', '');
        $tipo = $request->get('tipo', '');
        $contaBancariaId = $request->get('conta_bancaria_id', '');
        $dataInicio = $request->get('data_inicio', '');
        $dataFim = $request->get('data_fim', '');
        $conciliado = $request->get('conciliado', '');
        
        $filters = [];
        if ($empresaId) $filters['empresa_id'] = $empresaId;
        if ($tipo) $filters['tipo'] = $tipo;
        if ($contaBancariaId) $filters['conta_bancaria_id'] = $contaBancariaId;
        if ($dataInicio) $filters['data_inicio'] = $dataInicio;
        if ($dataFim) $filters['data_fim'] = $dataFim;
        if ($conciliado !== '') $filters['conciliado'] = $conciliado;
        
        // Paginação
        $porPagina = $request->get('por_pagina') ?? 25;
        $paginaAtual = $request->get('pagina') ?? 1;
        $paginaAtual = max(1, (int)$paginaAtual);
        
        $totalRegistros = $this->movimentacaoModel->countWithFilters($filters);
        
        $totalPaginas = 1;
        $offset = 0;
        
        if ($porPagina !== 'todos') {
            $porPagina = (int) $porPagina;
            $totalPaginas = ceil($totalRegistros / $porPagina);
            if ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
                $paginaAtual = $totalPaginas;
            }
            $offset = ($paginaAtual - 1) * $porPagina;
            $filters['limite'] = $porPagina;
            $filters['offset'] = $offset;
        }
        
        $movimentacoes = $this->movimentacaoModel->findAll($filters);
        
        // Calcular totais
        $totalEntradas = 0;
        $totalSaidas = 0;
        foreach ($movimentacoes as $mov) {
            if ($mov['tipo'] === 'entrada') {
                $totalEntradas += $mov['valor'];
            } else {
                $totalSaidas += $mov['valor'];
            }
        }
        $saldoPeriodo = $totalEntradas - $totalSaidas;
        
        // Dados para filtros
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $contasBancarias = $this->contaBancariaModel->findAll();
        $filtersApplied = $request->all();
        
        return $this->render('movimentacoes_caixa/index', [
            'title' => 'Movimentações de Caixa',
            'movimentacoes' => $movimentacoes,
            'empresas' => $empresas,
            'contasBancarias' => $contasBancarias,
            'filters' => $filtersApplied,
            'totais' => [
                'entradas' => $totalEntradas,
                'saidas' => $totalSaidas,
                'saldo' => $saldoPeriodo
            ],
            'paginacao' => [
                'total_registros' => $totalRegistros,
                'por_pagina' => $porPagina,
                'pagina_atual' => $paginaAtual,
                'total_paginas' => $totalPaginas,
                'offset' => $offset
            ]
        ]);
    }
    
    /**
     * Exibe formulário de nova movimentação
     */
    public function create(Request $request, Response $response)
    {
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $categorias = $this->categoriaModel->findAll();
        $centrosCusto = $this->centroCustoModel->findAll();
        $contasBancarias = $this->contaBancariaModel->findAll();
        $formasPagamento = $this->formaPagamentoModel->findAll();
        
        return $this->render('movimentacoes_caixa/create', [
            'title' => 'Nova Movimentação',
            'empresas' => $empresas,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'contasBancarias' => $contasBancarias,
            'formasPagamento' => $formasPagamento
        ]);
    }
    
    /**
     * Salva nova movimentação
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Validação
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect('/movimentacoes-caixa/create');
        }
        
        // Criar movimentação
        $movimentacaoId = $this->movimentacaoModel->create($data);
        
        if ($movimentacaoId) {
            // Atualizar saldo da conta bancária
            $contaBancaria = $this->contaBancariaModel->findById($data['conta_bancaria_id']);
            if ($contaBancaria) {
                $novoSaldo = $contaBancaria['saldo_atual'];
                
                if ($data['tipo'] === 'entrada') {
                    $novoSaldo += $data['valor'];
                } else {
                    $novoSaldo -= $data['valor'];
                }
                
                $this->contaBancariaModel->atualizarSaldo($data['conta_bancaria_id'], $novoSaldo);
            }
            
            $_SESSION['success'] = 'Movimentação cadastrada com sucesso!';
            return $response->redirect('/movimentacoes-caixa');
        } else {
            $_SESSION['error'] = 'Erro ao cadastrar movimentação.';
            $_SESSION['old'] = $data;
            return $response->redirect('/movimentacoes-caixa/create');
        }
    }
    
    /**
     * Exibe detalhes de uma movimentação
     */
    public function show(Request $request, Response $response, $id)
    {
        $movimentacao = $this->movimentacaoModel->findById($id);
        
        if (!$movimentacao) {
            $_SESSION['error'] = 'Movimentação não encontrada.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Buscar referência se existir
        $referencia = null;
        if ($movimentacao['referencia_id'] && $movimentacao['referencia_tipo']) {
            if ($movimentacao['referencia_tipo'] === 'conta_pagar') {
                $contaPagarModel = new ContaPagar();
                $referencia = $contaPagarModel->findById($movimentacao['referencia_id']);
                $referencia['tipo'] = 'Conta a Pagar';
            } elseif ($movimentacao['referencia_tipo'] === 'conta_receber') {
                $contaReceberModel = new ContaReceber();
                $referencia = $contaReceberModel->findById($movimentacao['referencia_id']);
                $referencia['tipo'] = 'Conta a Receber';
            }
        }
        
        return $this->render('movimentacoes_caixa/show', [
            'title' => 'Detalhes da Movimentação',
            'movimentacao' => $movimentacao,
            'referencia' => $referencia
        ]);
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $movimentacao = $this->movimentacaoModel->findById($id);
        
        if (!$movimentacao) {
            $_SESSION['error'] = 'Movimentação não encontrada.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Não permite editar movimentações vinculadas a contas
        if ($movimentacao['referencia_id']) {
            $_SESSION['error'] = 'Não é possível editar movimentações vinculadas a contas a pagar/receber. Edite a conta original.';
            return $response->redirect('/movimentacoes-caixa/' . $id);
        }
        
        // Não permite editar movimentações conciliadas
        if ($movimentacao['conciliado']) {
            $_SESSION['error'] = 'Não é possível editar movimentações já conciliadas.';
            return $response->redirect('/movimentacoes-caixa/' . $id);
        }
        
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $categorias = $this->categoriaModel->findAll();
        $centrosCusto = $this->centroCustoModel->findAll();
        $contasBancarias = $this->contaBancariaModel->findAll();
        $formasPagamento = $this->formaPagamentoModel->findAll();
        
        return $this->render('movimentacoes_caixa/edit', [
            'title' => 'Editar Movimentação',
            'movimentacao' => $movimentacao,
            'empresas' => $empresas,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'contasBancarias' => $contasBancarias,
            'formasPagamento' => $formasPagamento
        ]);
    }
    
    /**
     * Atualiza movimentação
     */
    public function update(Request $request, Response $response, $id)
    {
        $movimentacao = $this->movimentacaoModel->findById($id);
        
        if (!$movimentacao) {
            $_SESSION['error'] = 'Movimentação não encontrada.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Não permite editar movimentações vinculadas
        if ($movimentacao['referencia_id']) {
            $_SESSION['error'] = 'Não é possível editar movimentações vinculadas.';
            return $response->redirect('/movimentacoes-caixa/' . $id);
        }
        
        // Não permite editar movimentações conciliadas
        if ($movimentacao['conciliado']) {
            $_SESSION['error'] = 'Não é possível editar movimentações já conciliadas.';
            return $response->redirect('/movimentacoes-caixa/' . $id);
        }
        
        $data = $request->all();
        
        // Validação
        $errors = $this->validate($data, $id);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect('/movimentacoes-caixa/' . $id . '/edit');
        }
        
        // Reverter saldo anterior
        $contaBancaria = $this->contaBancariaModel->findById($movimentacao['conta_bancaria_id']);
        if ($contaBancaria) {
            $saldoAtual = $contaBancaria['saldo_atual'];
            
            if ($movimentacao['tipo'] === 'entrada') {
                $saldoAtual -= $movimentacao['valor'];
            } else {
                $saldoAtual += $movimentacao['valor'];
            }
            
            $this->contaBancariaModel->atualizarSaldo($movimentacao['conta_bancaria_id'], $saldoAtual);
        }
        
        // Atualizar movimentação
        $success = $this->movimentacaoModel->update($id, $data);
        
        if ($success) {
            // Aplicar novo saldo
            $contaBancaria = $this->contaBancariaModel->findById($data['conta_bancaria_id']);
            if ($contaBancaria) {
                $novoSaldo = $contaBancaria['saldo_atual'];
                
                if ($data['tipo'] === 'entrada') {
                    $novoSaldo += $data['valor'];
                } else {
                    $novoSaldo -= $data['valor'];
                }
                
                $this->contaBancariaModel->atualizarSaldo($data['conta_bancaria_id'], $novoSaldo);
            }
            
            $_SESSION['success'] = 'Movimentação atualizada com sucesso!';
            return $response->redirect('/movimentacoes-caixa/' . $id);
        } else {
            $_SESSION['error'] = 'Erro ao atualizar movimentação.';
            $_SESSION['old'] = $data;
            return $response->redirect('/movimentacoes-caixa/' . $id . '/edit');
        }
    }
    
    /**
     * Exclui movimentação
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $movimentacao = $this->movimentacaoModel->findById($id);
        
        if (!$movimentacao) {
            $_SESSION['error'] = 'Movimentação não encontrada.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Não permite excluir movimentações vinculadas
        if ($movimentacao['referencia_id']) {
            $_SESSION['error'] = 'Não é possível excluir movimentações vinculadas a contas. Cancele a conta original.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Não permite excluir movimentações conciliadas
        if ($movimentacao['conciliado']) {
            $_SESSION['error'] = 'Não é possível excluir movimentações já conciliadas.';
            return $response->redirect('/movimentacoes-caixa');
        }
        
        // Reverter saldo da conta bancária
        $contaBancaria = $this->contaBancariaModel->findById($movimentacao['conta_bancaria_id']);
        if ($contaBancaria) {
            $novoSaldo = $contaBancaria['saldo_atual'];
            
            if ($movimentacao['tipo'] === 'entrada') {
                $novoSaldo -= $movimentacao['valor'];
            } else {
                $novoSaldo += $movimentacao['valor'];
            }
            
            $this->contaBancariaModel->atualizarSaldo($movimentacao['conta_bancaria_id'], $novoSaldo);
        }
        
        $success = $this->movimentacaoModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Movimentação excluída com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir movimentação.';
        }
        
        return $response->redirect('/movimentacoes-caixa');
    }
    
    /**
     * Validação de dados
     */
    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'Empresa é obrigatória';
        }
        
        // Tipo
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'Tipo é obrigatório';
        } elseif (!in_array($data['tipo'], ['entrada', 'saida'])) {
            $errors['tipo'] = 'Tipo inválido';
        }
        
        // Categoria
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'Categoria é obrigatória';
        }
        
        // Conta bancária
        if (empty($data['conta_bancaria_id'])) {
            $errors['conta_bancaria_id'] = 'Conta bancária é obrigatória';
        }
        
        // Descrição
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        }
        
        // Valor
        if (empty($data['valor'])) {
            $errors['valor'] = 'Valor é obrigatório';
        } elseif (!is_numeric($data['valor']) || $data['valor'] <= 0) {
            $errors['valor'] = 'Valor deve ser maior que zero';
        }
        
        // Data de movimentação
        if (empty($data['data_movimentacao'])) {
            $errors['data_movimentacao'] = 'Data de movimentação é obrigatória';
        }
        
        return $errors;
    }
}
