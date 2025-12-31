<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ConciliacaoBancaria;
use App\Models\ConciliacaoItem;
use App\Models\ContaBancaria;
use App\Models\MovimentacaoCaixa;
use App\Models\Empresa;
use includes\services\ExtratoParserService;
use includes\services\OpenAIService;

class ConciliacaoBancariaController extends Controller
{
    private $conciliacaoModel;
    private $itemModel;
    private $contaBancariaModel;
    private $movimentacaoModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->conciliacaoModel = new ConciliacaoBancaria();
        $this->itemModel = new ConciliacaoItem();
        $this->contaBancariaModel = new ContaBancaria();
        $this->movimentacaoModel = new MovimentacaoCaixa();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Lista todas as conciliações
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        $filters = [
            'conta_bancaria_id' => $request->get('conta_bancaria_id'),
            'status' => $request->get('status'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim')
        ];
        
        $conciliacoes = $this->conciliacaoModel->findAll($empresaId, $filters);
        $contas = $this->contaBancariaModel->findAll($empresaId);
        
        return $this->render('conciliacao_bancaria/index', [
            'title' => 'Conciliação Bancária',
            'conciliacoes' => $conciliacoes,
            'contas' => $contas,
            'filters' => $filters
        ]);
    }
    
    /**
     * Exibe formulário de nova conciliação
     */
    public function create(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        $contas = $this->contaBancariaModel->findAll($empresaId);
        
        return $this->render('conciliacao_bancaria/create', [
            'title' => 'Nova Conciliação Bancária',
            'contas' => $contas
        ]);
    }
    
    /**
     * Salva nova conciliação
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Validar
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/conciliacao-bancaria/create');
        }
        
        // Calcular saldo do sistema
        $saldoSistema = $this->conciliacaoModel->calcularSaldoSistema(
            $data['conta_bancaria_id'],
            $data['data_inicio'],
            $data['data_fim']
        );
        
        $diferenca = $data['saldo_extrato'] - $saldoSistema;
        
        // Criar conciliação
        $conciliacaoData = [
            'empresa_id' => $empresaId,
            'conta_bancaria_id' => $data['conta_bancaria_id'],
            'data_inicio' => $data['data_inicio'],
            'data_fim' => $data['data_fim'],
            'saldo_extrato' => $data['saldo_extrato'],
            'saldo_sistema' => $saldoSistema,
            'diferenca' => $diferenca,
            'status' => 'aberta',
            'observacoes' => $data['observacoes'] ?? null
        ];
        
        $conciliacaoId = $this->conciliacaoModel->create($conciliacaoData);
        
        if ($conciliacaoId) {
            // Processar itens do extrato (se houver)
            if (!empty($data['itens'])) {
                foreach ($data['itens'] as $item) {
                    $this->itemModel->create([
                        'conciliacao_id' => $conciliacaoId,
                        'descricao_extrato' => $item['descricao'],
                        'valor_extrato' => $item['valor'],
                        'data_extrato' => $item['data'],
                        'tipo_extrato' => $item['tipo'],
                        'vinculado' => 0
                    ]);
                }
            }
            
            $this->session->set('success', 'Conciliação bancária criada com sucesso!');
            return $response->redirect('/conciliacao-bancaria/' . $conciliacaoId);
        }
        
        $this->session->set('error', 'Erro ao criar conciliação bancária.');
        return $response->redirect('/conciliacao-bancaria/create');
    }
    
    /**
     * Exibe detalhes da conciliação
     */
    public function show(Request $request, Response $response, $id)
    {
        $conciliacao = $this->conciliacaoModel->findById($id);
        
        if (!$conciliacao) {
            $this->session->set('error', 'Conciliação não encontrada.');
            return $response->redirect('/conciliacao-bancaria');
        }
        
        // Buscar itens do extrato
        $itens = $this->itemModel->findByConciliacao($id);
        
        // Buscar movimentações não conciliadas do período
        $movimentacoesNaoConciliadas = $this->conciliacaoModel->getMovimentacoesNaoConciliadas(
            $conciliacao['conta_bancaria_id'],
            $conciliacao['data_inicio'],
            $conciliacao['data_fim']
        );
        
        // Estatísticas
        $estatisticas = $this->itemModel->getEstatisticas($id);
        
        return $this->render('conciliacao_bancaria/show', [
            'title' => 'Conciliação Bancária #' . $id,
            'conciliacao' => $conciliacao,
            'itens' => $itens,
            'movimentacoes' => $movimentacoesNaoConciliadas,
            'estatisticas' => $estatisticas
        ]);
    }
    
    /**
     * Importa itens do extrato (manual)
     */
    public function importarExtrato(Request $request, Response $response, $id)
    {
        $data = $request->all();
        
        if (empty($data['itens'])) {
            $this->session->set('error', 'Nenhum item foi informado.');
            return $response->redirect('/conciliacao-bancaria/' . $id);
        }
        
        $countAdicionados = 0;
        foreach ($data['itens'] as $item) {
            if (empty($item['descricao']) || empty($item['valor']) || empty($item['data']) || empty($item['tipo'])) {
                continue;
            }
            
            $this->itemModel->create([
                'conciliacao_id' => $id,
                'descricao_extrato' => $item['descricao'],
                'valor_extrato' => $item['valor'],
                'data_extrato' => $item['data'],
                'tipo_extrato' => $item['tipo'],
                'vinculado' => 0
            ]);
            
            $countAdicionados++;
        }
        
        $this->session->set('success', "{$countAdicionados} item(ns) do extrato adicionado(s) com sucesso!");
        return $response->redirect('/conciliacao-bancaria/' . $id);
    }
    
    /**
     * Vincular item do extrato com movimentação
     */
    public function vincular(Request $request, Response $response)
    {
        $data = $request->all();
        
        if (empty($data['item_id']) || empty($data['movimentacao_id'])) {
            $this->session->set('error', 'Dados inválidos.');
            return $response->json(['success' => false, 'message' => 'Dados inválidos.']);
        }
        
        // Vincular item
        $success = $this->itemModel->vincular($data['item_id'], $data['movimentacao_id']);
        
        if ($success) {
            // Marcar movimentação como conciliada
            $this->movimentacaoModel->marcarComoConciliada($data['movimentacao_id'], $data['conciliacao_id']);
            
            return $response->json(['success' => true, 'message' => 'Item vinculado com sucesso!']);
        }
        
        return $response->json(['success' => false, 'message' => 'Erro ao vincular item.']);
    }
    
    /**
     * Desvincular item
     */
    public function desvincular(Request $request, Response $response)
    {
        $data = $request->all();
        
        if (empty($data['item_id'])) {
            return $response->json(['success' => false, 'message' => 'Item ID não informado.']);
        }
        
        // Buscar item para pegar movimentacao_id antes de desvincular
        $sql = "SELECT movimentacao_id FROM conciliacao_itens WHERE id = :id";
        $stmt = $this->itemModel->db->prepare($sql);
        $stmt->execute(['id' => $data['item_id']]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($item && $item['movimentacao_id']) {
            // Desmarcar movimentação como conciliada
            $this->movimentacaoModel->desmarcarConciliacao($item['movimentacao_id']);
        }
        
        // Desvincular item
        $success = $this->itemModel->desvincular($data['item_id']);
        
        if ($success) {
            return $response->json(['success' => true, 'message' => 'Item desvinculado com sucesso!']);
        }
        
        return $response->json(['success' => false, 'message' => 'Erro ao desvincular item.']);
    }
    
    /**
     * Fechar conciliação
     */
    public function fechar(Request $request, Response $response, $id)
    {
        // Verificar se há itens não vinculados
        $naoVinculados = $this->itemModel->getNaoVinculados($id);
        
        if (!empty($naoVinculados)) {
            $this->session->set('error', 'Não é possível fechar a conciliação. Ainda há ' . count($naoVinculados) . ' item(ns) não vinculado(s).');
            return $response->redirect('/conciliacao-bancaria/' . $id);
        }
        
        $success = $this->conciliacaoModel->fechar($id);
        
        if ($success) {
            $this->session->set('success', 'Conciliação fechada com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao fechar conciliação.');
        }
        
        return $response->redirect('/conciliacao-bancaria/' . $id);
    }
    
    /**
     * Reabrir conciliação
     */
    public function reabrir(Request $request, Response $response, $id)
    {
        $success = $this->conciliacaoModel->reabrir($id);
        
        if ($success) {
            $this->session->set('success', 'Conciliação reaberta com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao reabrir conciliação.');
        }
        
        return $response->redirect('/conciliacao-bancaria/' . $id);
    }
    
    /**
     * Deletar conciliação
     */
    public function destroy(Request $request, Response $response, $id)
    {
        // Verificar se está fechada
        $conciliacao = $this->conciliacaoModel->findById($id);
        
        if ($conciliacao['status'] === 'fechada') {
            $this->session->set('error', 'Não é possível excluir uma conciliação fechada. Reabra-a primeiro.');
            return $response->redirect('/conciliacao-bancaria');
        }
        
        // Desmarcar todas as movimentações como não conciliadas
        $itens = $this->itemModel->findByConciliacao($id);
        foreach ($itens as $item) {
            if ($item['movimentacao_id']) {
                $this->movimentacaoModel->desmarcarConciliacao($item['movimentacao_id']);
            }
        }
        
        // Deletar conciliação (itens serão deletados em cascata)
        $success = $this->conciliacaoModel->delete($id);
        
        if ($success) {
            $this->session->set('success', 'Conciliação excluída com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao excluir conciliação.');
        }
        
        return $response->redirect('/conciliacao-bancaria');
    }
    
    /**
     * Processa upload de extrato bancário
     */
    public function processarExtrato(Request $request, Response $response)
    {
        if (empty($_FILES['arquivo_extrato']) || $_FILES['arquivo_extrato']['error'] !== UPLOAD_ERR_OK) {
            return $response->json([
                'success' => false,
                'message' => 'Nenhum arquivo foi enviado ou ocorreu um erro no upload.'
            ]);
        }
        
        try {
            $arquivo = $_FILES['arquivo_extrato'];
            
            // Processar extrato
            $itens = ExtratoParserService::processar($arquivo);
            
            // Tentar extrair saldo final
            $saldoFinal = ExtratoParserService::extrairSaldoFinal($arquivo);
            
            return $response->json([
                'success' => true,
                'itens' => $itens,
                'saldo_final' => $saldoFinal,
                'total_itens' => count($itens),
                'message' => count($itens) . ' transações encontradas no extrato!'
            ]);
            
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Erro ao processar extrato: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Analisa conciliação com IA
     */
    public function analisarIA(Request $request, Response $response, $id)
    {
        try {
            // Verificar se IA está configurada
            if (!OpenAIService::isConfigured()) {
                return $response->json([
                    'success' => false,
                    'message' => 'API OpenAI não configurada. Configure a chave API em Configurações → API e IA.'
                ]);
            }
            
            // Buscar dados da conciliação
            $conciliacao = $this->conciliacaoModel->findById($id);
            
            if (!$conciliacao) {
                return $response->json([
                    'success' => false,
                    'message' => 'Conciliação não encontrada.'
                ]);
            }
            
            // Buscar itens não vinculados
            $itensNaoVinculados = $this->itemModel->getNaoVinculados($id);
            
            // Buscar movimentações não conciliadas
            $movimentacoesNaoConciliadas = $this->conciliacaoModel->getMovimentacoesNaoConciliadas(
                $conciliacao['conta_bancaria_id'],
                $conciliacao['data_inicio'],
                $conciliacao['data_fim']
            );
            
            // Buscar itens vinculados
            $itensVinculados = $this->itemModel->getVinculados($id);
            
            // Preparar dados para análise
            $dadosAnalise = [
                'data_inicio' => $conciliacao['data_inicio'],
                'data_fim' => $conciliacao['data_fim'],
                'conta_descricao' => $conciliacao['conta_descricao'],
                'saldo_extrato' => $conciliacao['saldo_extrato'],
                'saldo_sistema' => $conciliacao['saldo_sistema'],
                'diferenca' => $conciliacao['diferenca'],
                'itens_nao_vinculados' => $itensNaoVinculados,
                'movimentacoes_nao_conciliadas' => $movimentacoesNaoConciliadas,
                'itens_vinculados' => $itensVinculados
            ];
            
            // Chamar IA
            $analise = OpenAIService::analisarConciliacao($dadosAnalise);
            
            return $response->json([
                'success' => true,
                'analise' => $analise
            ]);
            
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Erro ao analisar: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validação
     */
    protected function validate($data)
    {
        $errors = [];
        
        if (empty($data['conta_bancaria_id'])) {
            $errors['conta_bancaria_id'] = 'Conta bancária é obrigatória.';
        }
        
        if (empty($data['data_inicio'])) {
            $errors['data_inicio'] = 'Data de início é obrigatória.';
        }
        
        if (empty($data['data_fim'])) {
            $errors['data_fim'] = 'Data de fim é obrigatória.';
        }
        
        if (!empty($data['data_inicio']) && !empty($data['data_fim']) && $data['data_inicio'] > $data['data_fim']) {
            $errors['data_fim'] = 'Data de fim deve ser maior ou igual à data de início.';
        }
        
        if (!isset($data['saldo_extrato']) || $data['saldo_extrato'] === '') {
            $errors['saldo_extrato'] = 'Saldo do extrato é obrigatório.';
        }
        
        return $errors;
    }
}
