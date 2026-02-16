<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ReceitaRecorrente;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use Includes\Services\RecorrenciaService;
use includes\services\RateioService;

/**
 * Controller para Receitas Recorrentes
 */
class ReceitaRecorrenteController extends Controller
{
    private $receitaRecorrenteModel;
    private $empresaModel;
    private $clienteModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $formaPagamentoModel;
    private $contaBancariaModel;
    private $recorrenciaService;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Lista receitas recorrentes
     */
    public function index(Request $request, Response $response)
    {
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $this->empresaModel = new Empresa();
            
            $empresaId = $request->get('empresa_id') ?? $_SESSION['usuario_empresa_id'] ?? null;
            $ativo = $request->get('ativo') ?? '';
            
            $filtros = [];
            if ($empresaId) $filtros['empresa_id'] = $empresaId;
            if ($ativo !== '') $filtros['ativo'] = $ativo;
            
            $receitas = $this->receitaRecorrenteModel->findAll($filtros);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            
            // Calcula resumo
            $resumo = [
                'receitas_count' => count($receitas),
                'total_receitas' => array_sum(array_column($receitas, 'valor'))
            ];
            
            return $this->render('receitas_recorrentes/index', [
                'title' => 'Receitas Recorrentes',
                'receitas' => $receitas,
                'empresas' => $empresas,
                'filtros' => $filtros,
                'resumo' => $resumo
            ]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), 'Base table') !== false) {
                $_SESSION['error'] = 'A tabela de receitas recorrentes ainda não foi criada. Execute as queries SQL fornecidas.';
            } else {
                $_SESSION['error'] = 'Erro ao carregar receitas recorrentes: ' . $e->getMessage();
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
            $this->clienteModel = new Cliente();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
            
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaId, 'receita');
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            $old = $_SESSION['old'] ?? [];
            unset($_SESSION['old']);
            
            return $this->render('receitas_recorrentes/create', [
                'title' => 'Nova Receita Recorrente',
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'old' => $old
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/receitas-recorrentes');
        }
    }
    
    /**
     * Salva nova receita recorrente
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Validações
        $errors = $this->validateData($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect('/receitas-recorrentes/create');
            return;
        }
        
        $data['usuario_cadastro_id'] = $_SESSION['usuario_id'];
        
        // Processa rateios para JSON
        if (!empty($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
            $rateioService = new RateioService();
            $errosRateio = $rateioService->validarRateios($data['rateios'], $data['valor']);
            if (!empty($errosRateio)) {
                $_SESSION['errors'] = array_merge($_SESSION['errors'] ?? [], ['rateios' => implode(', ', $errosRateio)]);
                $_SESSION['old'] = $data;
                $response->redirect('/receitas-recorrentes/create');
                return;
            }
            $rateiosPreparados = $rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
            $data['rateios_json'] = json_encode($rateiosPreparados);
            unset($data['rateios'], $data['tem_rateio']);
        } else {
            unset($data['rateios'], $data['tem_rateio']);
        }
        
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $id = $this->receitaRecorrenteModel->create($data);
            
            if (!$id) {
                throw new \Exception('Erro ao criar receita recorrente');
            }
            
            $_SESSION['success'] = 'Receita recorrente criada com sucesso!';
            $response->redirect('/receitas-recorrentes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $_SESSION['old'] = $data;
            $response->redirect('/receitas-recorrentes/create');
        }
    }
    
    /**
     * Exibe detalhes
     */
    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $receita = $this->receitaRecorrenteModel->findById($id);
            
            if (!$receita) {
                $_SESSION['error'] = 'Receita recorrente não encontrada!';
                $response->redirect('/receitas-recorrentes');
                return;
            }
            
            // Busca contas geradas
            $contasGeradas = $this->receitaRecorrenteModel->buscarContasGeradas($id, 20);
            $totalGeradas = $this->receitaRecorrenteModel->contarContasGeradas($id);
            
            return $this->render('receitas_recorrentes/show', [
                'title' => 'Receita Recorrente - ' . $receita['descricao'],
                'receita' => $receita,
                'contasGeradas' => $contasGeradas,
                'totalGeradas' => $totalGeradas
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $response->redirect('/receitas-recorrentes');
        }
    }
    
    /**
     * Formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $this->empresaModel = new Empresa();
            $this->clienteModel = new Cliente();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $receita = $this->receitaRecorrenteModel->findById($id);
            
            if (!$receita) {
                $_SESSION['error'] = 'Receita recorrente não encontrada!';
                $response->redirect('/receitas-recorrentes');
                return;
            }
            
            $empresaId = $receita['empresa_id'];
            
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $clientes = $this->clienteModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaId, 'receita');
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            $old = $_SESSION['old'] ?? [];
            if (!empty($old)) unset($_SESSION['old']);
            
            return $this->render('receitas_recorrentes/edit', [
                'title' => 'Editar Receita Recorrente',
                'receita' => $receita,
                'old' => $old,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $response->redirect('/receitas-recorrentes');
        }
    }
    
    /**
     * Atualiza receita recorrente
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        
        // Validações
        $errors = $this->validateData($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect("/receitas-recorrentes/{$id}/edit");
            return;
        }
        
        // Processa rateios para JSON
        if (!empty($data['tem_rateio']) && $data['tem_rateio'] == 1 && !empty($data['rateios'])) {
            $rateioService = new RateioService();
            $errosRateio = $rateioService->validarRateios($data['rateios'], $data['valor']);
            if (!empty($errosRateio)) {
                $_SESSION['errors'] = array_merge($_SESSION['errors'] ?? [], ['rateios' => implode(', ', $errosRateio)]);
                $_SESSION['old'] = $data;
                $response->redirect("/receitas-recorrentes/{$id}/edit");
                return;
            }
            $rateiosPreparados = $rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
            $data['rateios_json'] = json_encode($rateiosPreparados);
        } else {
            $data['rateios_json'] = null;
        }
        unset($data['rateios'], $data['tem_rateio']);
        
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $this->receitaRecorrenteModel->update($id, $data);
            
            $_SESSION['success'] = 'Receita recorrente atualizada com sucesso!';
            $response->redirect('/receitas-recorrentes/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $response->redirect("/receitas-recorrentes/{$id}/edit");
        }
    }
    
    /**
     * Ativa/Desativa
     */
    public function toggle(Request $request, Response $response, $id)
    {
        try {
            $this->receitaRecorrenteModel = new ReceitaRecorrente();
            $this->receitaRecorrenteModel->toggleAtivo($id);
            
            $_SESSION['success'] = 'Status alterado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        $response->redirect('/receitas-recorrentes');
    }
    
    /**
     * Exclui
     */
    public function delete(Request $request, Response $response, $id)
    {
        $this->receitaRecorrenteModel->delete($id);
        
        $_SESSION['success'] = 'Receita recorrente excluída com sucesso!';
        $response->redirect('/receitas-recorrentes');
    }
    
    /**
     * Gera manualmente a próxima ocorrência
     */
    public function gerarManual(Request $request, Response $response, $id)
    {
        $receita = $this->receitaRecorrenteModel->findById($id);
        
        if (!$receita) {
            $_SESSION['error'] = 'Receita recorrente não encontrada!';
            $response->redirect('/receitas-recorrentes');
            return;
        }
        
        try {
            $contaId = $this->recorrenciaService->gerarReceita($receita);
            
            $_SESSION['success'] = 'Conta a receber gerada com sucesso!';
            $response->redirect('/contas-receber/' . $contaId);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao gerar: ' . $e->getMessage();
            $response->redirect('/receitas-recorrentes/' . $id);
        }
    }
    
    /**
     * Aplica reajuste manualmente
     */
    public function aplicarReajuste(Request $request, Response $response, $id)
    {
        $receita = $this->receitaRecorrenteModel->findById($id);
        
        if (!$receita || !$receita['reajuste_ativo']) {
            $_SESSION['error'] = 'Reajuste não disponível para esta receita!';
            $response->redirect('/receitas-recorrentes/' . $id);
            return;
        }
        
        $this->receitaRecorrenteModel->aplicarReajuste($id);
        
        $_SESSION['success'] = 'Reajuste aplicado com sucesso!';
        $response->redirect('/receitas-recorrentes/' . $id);
    }
    
    /**
     * Validação
     */
    private function validateData($data)
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
        
        return $errors;
    }
}
