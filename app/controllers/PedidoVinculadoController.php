<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\PedidoVinculado;
use App\Models\PedidoItem;
use App\Models\Cliente;
use App\Models\Produto;

class PedidoVinculadoController extends Controller
{
    private $pedidoModel;
    private $itemModel;
    private $clienteModel;
    private $produtoModel;
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->pedidoModel = new PedidoVinculado();
        $this->itemModel = new PedidoItem();
        $this->clienteModel = new Cliente();
        $this->produtoModel = new Produto();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Lista todos os pedidos
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $this->session->get('empresa_id');
        
        $filters = [
            'origem' => $request->get('origem'),
            'status' => $request->get('status'),
            'cliente_id' => $request->get('cliente_id'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim'),
            'numero_pedido' => $request->get('numero_pedido')
        ];
        
        $pedidos = $this->pedidoModel->findAll($empresaId, $filters);
        $clientes = $this->clienteModel->findAll($empresaId);
        
        return $this->render('pedidos/index', [
            'title' => 'Pedidos Vinculados',
            'pedidos' => $pedidos,
            'clientes' => $clientes,
            'filters' => $filters
        ]);
    }
    
    /**
     * Exibe formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        $empresaId = $this->session->get('empresa_id');
        $clientes = $this->clienteModel->findAll($empresaId);
        $produtos = $this->produtoModel->findAll($empresaId);
        
        return $this->render('pedidos/create', [
            'title' => 'Novo Pedido',
            'clientes' => $clientes,
            'produtos' => $produtos
        ]);
    }
    
    /**
     * Salva novo pedido
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $empresaId = $this->session->get('empresa_id');
        
        // Validar
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/pedidos/create');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Criar pedido
            $pedidoData = [
                'empresa_id' => $empresaId,
                'origem' => $data['origem'] ?? PedidoVinculado::ORIGEM_MANUAL,
                'origem_id' => $data['numero_pedido'],
                'numero_pedido' => $data['numero_pedido'],
                'cliente_id' => $data['cliente_id'] ?? null,
                'data_pedido' => $data['data_pedido'],
                'data_atualizacao' => date('Y-m-d H:i:s'),
                'status' => $data['status'],
                'valor_total' => 0,
                'valor_custo_total' => 0
            ];
            
            $pedidoId = $this->pedidoModel->create($pedidoData);
            
            if (!$pedidoId) {
                throw new \Exception('Erro ao criar pedido');
            }
            
            // Adicionar itens
            if (!empty($data['itens'])) {
                foreach ($data['itens'] as $item) {
                    if (empty($item['nome_produto']) || empty($item['quantidade']) || empty($item['valor_unitario'])) {
                        continue;
                    }
                    
                    $valorTotal = $item['quantidade'] * $item['valor_unitario'];
                    $custoTotal = $item['quantidade'] * ($item['custo_unitario'] ?? 0);
                    
                    $this->itemModel->create([
                        'pedido_id' => $pedidoId,
                        'produto_id' => $item['produto_id'] ?? null,
                        'codigo_produto_origem' => $item['codigo_produto'] ?? null,
                        'nome_produto' => $item['nome_produto'],
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'valor_total' => $valorTotal,
                        'custo_unitario' => $item['custo_unitario'] ?? 0,
                        'custo_total' => $custoTotal
                    ]);
                }
            }
            
            // Recalcular totais do pedido
            $this->pedidoModel->recalcularTotais($pedidoId);
            
            $this->db->commit();
            
            $this->session->set('success', 'Pedido criado com sucesso!');
            return $response->redirect('/pedidos/' . $pedidoId);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->set('error', 'Erro ao criar pedido: ' . $e->getMessage());
            return $response->redirect('/pedidos/create');
        }
    }
    
    /**
     * Exibe detalhes do pedido
     */
    public function show(Request $request, Response $response, $id)
    {
        $pedido = $this->pedidoModel->findById($id);
        
        if (!$pedido) {
            $this->session->set('error', 'Pedido não encontrado.');
            return $response->redirect('/pedidos');
        }
        
        $itens = $this->itemModel->findByPedido($id);
        
        return $this->render('pedidos/show', [
            'title' => 'Pedido #' . $pedido['numero_pedido'],
            'pedido' => $pedido,
            'itens' => $itens
        ]);
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus(Request $request, Response $response, $id)
    {
        $status = $request->post('status');
        
        if (empty($status)) {
            $this->session->set('error', 'Status inválido.');
            return $response->redirect('/pedidos/' . $id);
        }
        
        $success = $this->pedidoModel->updateStatus($id, $status);
        
        if ($success) {
            $this->session->set('success', 'Status atualizado com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao atualizar status.');
        }
        
        return $response->redirect('/pedidos/' . $id);
    }
    
    /**
     * Deletar pedido
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $success = $this->pedidoModel->delete($id);
        
        if ($success) {
            $this->session->set('success', 'Pedido excluído com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao excluir pedido.');
        }
        
        return $response->redirect('/pedidos');
    }
    
    /**
     * Validação
     */
    protected function validate($data)
    {
        $errors = [];
        
        if (empty($data['numero_pedido'])) {
            $errors['numero_pedido'] = 'Número do pedido é obrigatório.';
        }
        
        if (empty($data['data_pedido'])) {
            $errors['data_pedido'] = 'Data do pedido é obrigatória.';
        }
        
        if (empty($data['status'])) {
            $errors['status'] = 'Status é obrigatório.';
        }
        
        if (empty($data['itens']) || !is_array($data['itens'])) {
            $errors['itens'] = 'Adicione pelo menos um item ao pedido.';
        }
        
        return $errors;
    }
}
