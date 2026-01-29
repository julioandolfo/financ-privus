<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\DespesaRecorrente;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use Includes\Services\RecorrenciaService;

/**
 * Controller para Despesas Recorrentes
 */
class DespesaRecorrenteController extends Controller
{
    private $despesaRecorrenteModel;
    private $empresaModel;
    private $fornecedorModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $formaPagamentoModel;
    private $contaBancariaModel;
    private $recorrenciaService;
    
    /**
     * Log de debug
     */
    private function logDebug($message, $context = [])
    {
        $logFile = __DIR__ . '/../../logs/despesas_recorrentes.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->logDebug('Constructor chamado');
    }
    
    /**
     * Lista despesas recorrentes
     */
    public function index(Request $request, Response $response)
    {
        $this->logDebug('=== INDEX INICIADO ===');
        
        try {
            $this->logDebug('Tentando criar DespesaRecorrente model');
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->logDebug('DespesaRecorrente model criado com sucesso');
            
            $this->logDebug('Tentando criar Empresa model');
            $this->empresaModel = new Empresa();
            $this->logDebug('Empresa model criado com sucesso');
            
            $empresaId = $request->get('empresa_id') ?? $_SESSION['usuario_empresa_id'] ?? null;
            $ativo = $request->get('ativo') ?? '';
            $this->logDebug('Parâmetros', ['empresa_id' => $empresaId, 'ativo' => $ativo]);
            
            $filtros = [];
            if ($empresaId) $filtros['empresa_id'] = $empresaId;
            if ($ativo !== '') $filtros['ativo'] = $ativo;
            
            $this->logDebug('Buscando despesas recorrentes');
            $despesas = $this->despesaRecorrenteModel->findAll($filtros);
            $this->logDebug('Despesas encontradas', ['count' => count($despesas)]);
            
            $this->logDebug('Buscando empresas');
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $this->logDebug('Empresas encontradas', ['count' => count($empresas)]);
            
            // Calcula resumo
            $resumo = [
                'despesas_count' => count($despesas),
                'receitas_count' => 0,
                'total_despesas' => array_sum(array_column($despesas, 'valor')),
                'total_receitas' => 0,
                'saldo_previsto' => 0 - array_sum(array_column($despesas, 'valor'))
            ];
            
            $this->logDebug('Renderizando view');
            return $this->render('despesas_recorrentes/index', [
                'title' => 'Despesas Recorrentes',
                'despesas' => $despesas,
                'empresas' => $empresas,
                'filtros' => $filtros,
                'resumo' => $resumo
            ]);
        } catch (\Exception $e) {
            $this->logDebug('ERRO NO INDEX', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Se a tabela não existe, mostra mensagem amigável
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), 'Base table') !== false) {
                $_SESSION['error'] = 'A tabela de despesas recorrentes ainda não foi criada. Execute as queries SQL fornecidas.';
            } else {
                $_SESSION['error'] = 'Erro ao carregar despesas recorrentes: ' . $e->getMessage();
            }
            $response->redirect('/');
        }
    }
    
    /**
     * Formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
            
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaId, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            $old = $_SESSION['old'] ?? [];
            unset($_SESSION['old']);
            
            return $this->render('despesas_recorrentes/create', [
                'title' => 'Nova Despesa Recorrente',
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'old' => $old
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/despesas-recorrentes');
        }
    }
    
    /**
     * Salva nova despesa recorrente
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Validações
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect('/despesas-recorrentes/create');
            return;
        }
        
        $data['usuario_cadastro_id'] = $_SESSION['usuario_id'];
        
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $id = $this->despesaRecorrenteModel->create($data);
            
            if (!$id) {
                throw new \Exception('Erro ao criar despesa recorrente');
            }
            
            $_SESSION['success'] = 'Despesa recorrente criada com sucesso!';
            $response->redirect('/despesas-recorrentes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $_SESSION['old'] = $data;
            $response->redirect('/despesas-recorrentes/create');
        }
    }
    
    /**
     * Exibe detalhes
     */
    public function show(Request $request, Response $response, $id)
    {
        $this->despesaRecorrenteModel = new DespesaRecorrente();
        $despesa = $this->despesaRecorrenteModel->findById($id);
        
        if (!$despesa) {
            $_SESSION['error'] = 'Despesa recorrente não encontrada!';
            $response->redirect('/despesas-recorrentes');
            return;
        }
        
        // Busca contas geradas
        $contasGeradas = $this->despesaRecorrenteModel->buscarContasGeradas($id, 20);
        $totalGeradas = $this->despesaRecorrenteModel->contarContasGeradas($id);
        
        return $this->render('despesas_recorrentes/show', [
            'title' => 'Despesa Recorrente - ' . $despesa['descricao'],
            'despesa' => $despesa,
            'contasGeradas' => $contasGeradas,
            'totalGeradas' => $totalGeradas
        ]);
    }
    
    /**
     * Formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $despesa = $this->despesaRecorrenteModel->findById($id);
            
            if (!$despesa) {
                $_SESSION['error'] = 'Despesa recorrente não encontrada!';
                $response->redirect('/despesas-recorrentes');
                return;
            }
            
            $empresaId = $despesa['empresa_id'];
            
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaId, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            return $this->render('despesas_recorrentes/edit', [
                'title' => 'Editar Despesa Recorrente',
                'despesa' => $despesa,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $response->redirect('/despesas-recorrentes');
        }
    }
    
    /**
     * Atualiza despesa recorrente
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        
        // Validações
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $response->redirect("/despesas-recorrentes/{$id}/edit");
            return;
        }
        
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->despesaRecorrenteModel->update($id, $data);
            
            $_SESSION['success'] = 'Despesa recorrente atualizada com sucesso!';
            $response->redirect('/despesas-recorrentes/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $response->redirect("/despesas-recorrentes/{$id}/edit");
        }
    }
    
    /**
     * Ativa/Desativa
     */
    public function toggle(Request $request, Response $response, $id)
    {
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->despesaRecorrenteModel->toggleAtivo($id);
            
            $_SESSION['success'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        $response->redirect('/despesas-recorrentes');
    }
    
    /**
     * Exclui
     */
    public function delete(Request $request, Response $response, $id)
    {
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->despesaRecorrenteModel->delete($id);
            
            $_SESSION['success'] = 'Despesa recorrente excluída com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        $response->redirect('/despesas-recorrentes');
    }
    
    /**
     * Gera manualmente a próxima ocorrência
     */
    public function gerarManual(Request $request, Response $response, $id)
    {
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $this->recorrenciaService = new RecorrenciaService();
            
            $despesa = $this->despesaRecorrenteModel->findById($id);
            
            if (!$despesa) {
                $_SESSION['error'] = 'Despesa recorrente não encontrada!';
                $response->redirect('/despesas-recorrentes');
                return;
            }
            
            $contaId = $this->recorrenciaService->gerarDespesa($despesa);
            
            $_SESSION['success'] = 'Conta a pagar gerada com sucesso!';
            $response->redirect('/contas-pagar/' . $contaId);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao gerar: ' . $e->getMessage();
            $response->redirect('/despesas-recorrentes/' . $id);
        }
    }
    
    /**
     * Aplica reajuste manualmente
     */
    public function aplicarReajuste(Request $request, Response $response, $id)
    {
        try {
            $this->despesaRecorrenteModel = new DespesaRecorrente();
            $despesa = $this->despesaRecorrenteModel->findById($id);
        
            if (!$despesa || !$despesa['reajuste_ativo']) {
                $_SESSION['error'] = 'Reajuste não disponível para esta despesa!';
                $response->redirect('/despesas-recorrentes/' . $id);
                return;
            }
            
            $this->despesaRecorrenteModel->aplicarReajuste($id);
            
            $_SESSION['success'] = 'Reajuste aplicado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        $response->redirect('/despesas-recorrentes/' . $id);
    }
    
    /**
     * Validação
     */
    private function validate($data)
    {
        $errors = [];
        
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'Empresa é obrigatória';
        }
        
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'Categoria é obrigatória';
        }
        
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        }
        
        if (empty($data['valor']) || $data['valor'] <= 0) {
            $errors['valor'] = 'Valor deve ser maior que zero';
        }
        
        if (empty($data['data_inicio'])) {
            $errors['data_inicio'] = 'Data de início é obrigatória';
        }
        
        if (empty($data['frequencia'])) {
            $errors['frequencia'] = 'Frequência é obrigatória';
        }
        
        if ($data['frequencia'] === 'mensal' && empty($data['dia_mes'])) {
            $errors['dia_mes'] = 'Dia do mês é obrigatório para frequência mensal';
        }
        
        if ($data['frequencia'] === 'semanal' && !isset($data['dia_semana'])) {
            $errors['dia_semana'] = 'Dia da semana é obrigatório para frequência semanal';
        }
        
        if ($data['frequencia'] === 'personalizado' && empty($data['intervalo_dias'])) {
            $errors['intervalo_dias'] = 'Intervalo de dias é obrigatório';
        }
        
        return $errors;
    }
}
