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
use App\Models\LogSistema;

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
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        $filters = [
            'origem' => $request->get('origem'),
            'status' => $request->get('status'),
            'cliente_id' => $request->get('cliente_id'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim'),
            'numero_pedido' => $request->get('numero_pedido')
        ];
        
        // Paginação
        $porPagina = $request->get('por_pagina') ?? 25;
        $paginaAtual = $request->get('pagina') ?? 1;
        $paginaAtual = max(1, (int)$paginaAtual);
        
        // Busca total de registros para calcular paginação
        $totalRegistros = $this->pedidoModel->countWithFilters($empresaId, $filters);
        
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
        
        $pedidos = $this->pedidoModel->findAll($empresaId, $filters);
        $clientes = $this->clienteModel->findAll($empresaId);
        
        // Filtros aplicados para a view
        $filtersApplied = $request->all();
        
        return $this->render('pedidos/index', [
            'title' => 'Pedidos Vinculados',
            'pedidos' => $pedidos,
            'clientes' => $clientes,
            'filters' => $filtersApplied,
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
     * Exibe formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
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
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
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
        
        // Buscar pedidos filhos (bonificados vinculados a este pedido)
        $pedidosFilhos = $this->pedidoModel->findFilhos($id);
        
        return $this->render('pedidos/show', [
            'title' => 'Pedido #' . $pedido['numero_pedido'],
            'pedido' => $pedido,
            'itens' => $itens,
            'pedidosFilhos' => $pedidosFilhos
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
        $excluirReceitas = $request->post('excluir_receitas');
        $excluirItens = $request->post('excluir_itens');
        
        $mensagens = [];
        
        try {
            // Excluir contas a receber vinculadas
            if ($excluirReceitas) {
                $db = Database::getInstance()->getConnection();
                $sql = "SELECT COUNT(*) as total FROM contas_receber WHERE pedido_id = :pedido_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['pedido_id' => $id]);
                $totalReceitas = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
                
                if ($totalReceitas > 0) {
                    $sqlDel = "DELETE FROM contas_receber WHERE pedido_id = :pedido_id";
                    $stmtDel = $db->prepare($sqlDel);
                    $stmtDel->execute(['pedido_id' => $id]);
                    $mensagens[] = "{$totalReceitas} conta(s) a receber excluída(s)";
                }
            }
            
            // Excluir itens do pedido
            if ($excluirItens) {
                $totalItens = $this->itemModel->countByPedido($id);
                if ($totalItens > 0) {
                    $this->itemModel->deleteByPedido($id);
                    $mensagens[] = "{$totalItens} item(ns) excluído(s)";
                }
            }
            
            // Excluir o pedido
            $success = $this->pedidoModel->delete($id);
            
            if ($success) {
                $msg = 'Pedido excluído com sucesso!';
                if (!empty($mensagens)) {
                    $msg .= ' (' . implode(', ', $mensagens) . ')';
                }
                $this->session->set('success', $msg);
            } else {
                $this->session->set('error', 'Erro ao excluir pedido.');
            }
        } catch (\Throwable $e) {
            $this->session->set('error', 'Erro ao excluir pedido: ' . $e->getMessage());
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
    
    /**
     * Recalcular custos e totais de pedidos
     */
    public function recalcular(Request $request, Response $response)
    {
        $logs = [];
        
        try {
            // Buscar pedidos que possuem itens com custo zero/nulo (busca direto sem filtro de empresa)
            $sqlPedidos = "SELECT DISTINCT p.id, p.numero_pedido, p.empresa_id
                           FROM pedidos_vinculados p
                           INNER JOIN pedidos_itens pi ON pi.pedido_id = p.id
                           WHERE (pi.custo_unitario IS NULL OR pi.custo_unitario <= 0 OR pi.custo_total IS NULL OR pi.custo_total <= 0)";
            $stmtPedidos = $this->db->prepare($sqlPedidos);
            $stmtPedidos->execute();
            $pedidos = $stmtPedidos->fetchAll(\PDO::FETCH_ASSOC);
            
            $logs[] = "=== RECALCULAR PEDIDOS === pedidos encontrados: " . count($pedidos);
            
            $totalRecalculados = 0;
            $totalItensAtualizados = 0;
            $itensIgnorados = 0;
            $erros = [];
            
            foreach ($pedidos as $pedido) {
                try {
                    $sqlItens = "SELECT pi.* FROM pedidos_itens pi
                                 WHERE pi.pedido_id = :pedido_id
                                   AND (pi.custo_unitario IS NULL OR pi.custo_unitario <= 0 OR pi.custo_total IS NULL OR pi.custo_total <= 0)";
                    $stmtItens = $this->db->prepare($sqlItens);
                    $stmtItens->execute(['pedido_id' => $pedido['id']]);
                    $itensSemCusto = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);
                    
                    $empresaIdPedido = $pedido['empresa_id'];
                    $logs[] = "Pedido #{$pedido['numero_pedido']} (ID:{$pedido['id']}, empresa:{$empresaIdPedido}): " . count($itensSemCusto) . " item(ns) sem custo";
                    
                    $atualizouAlgo = false;
                    
                    foreach ($itensSemCusto as $item) {
                        $produto = null;
                        $produtoId = $item['produto_id'] ?? null;
                        $nomeProdutoItem = $item['nome_produto'] ?? '';
                        
                        $logs[] = "  Item ID:{$item['id']} '{$nomeProdutoItem}' | produto_id=" . ($produtoId ?: 'NULL') . 
                            " | custo_unit=" . ($item['custo_unitario'] ?? 'NULL') . " | cod_origem=" . ($item['codigo_produto_origem'] ?? 'NULL');
                        
                        // 1. Busca pelo produto_id vinculado
                        if ($produtoId) {
                            $produto = $this->produtoModel->findById($produtoId);
                            $logs[] = $produto 
                                ? "  [1] findById({$produtoId}): '{$produto['nome']}' custo=R\${$produto['custo_unitario']}" 
                                : "  [1] findById({$produtoId}): NAO encontrado";
                        } else {
                            $logs[] = "  [1] Pulado - produto_id NULL";
                        }
                        
                        // 2. Se não tem produto ou custo é 0, busca por nome (usa empresa_id do pedido)
                        if (!$produto || floatval($produto['custo_unitario'] ?? 0) <= 0) {
                            $produtoEncontrado = null;
                            
                            // 2a. Nome exato
                            if (!empty($nomeProdutoItem)) {
                                $stmtBusca = $this->db->prepare("SELECT * FROM produtos WHERE nome = :nome AND empresa_id = :emp AND ativo = 1 LIMIT 1");
                                $stmtBusca->execute(['nome' => $nomeProdutoItem, 'emp' => $empresaIdPedido]);
                                $produtoEncontrado = $stmtBusca->fetch(\PDO::FETCH_ASSOC);
                                $logs[] = $produtoEncontrado 
                                    ? "  [2a] Nome exato: ID:{$produtoEncontrado['id']} custo=R\${$produtoEncontrado['custo_unitario']}" 
                                    : "  [2a] Nome exato: NAO encontrado (empresa={$empresaIdPedido})";
                            }
                            
                            // 2b. Nome LIKE
                            if (!$produtoEncontrado && !empty($nomeProdutoItem)) {
                                $stmtBusca2 = $this->db->prepare("SELECT * FROM produtos WHERE nome LIKE :nome AND empresa_id = :emp AND ativo = 1 LIMIT 1");
                                $stmtBusca2->execute(['nome' => '%' . $nomeProdutoItem . '%', 'emp' => $empresaIdPedido]);
                                $produtoEncontrado = $stmtBusca2->fetch(\PDO::FETCH_ASSOC);
                                $logs[] = $produtoEncontrado 
                                    ? "  [2b] Nome LIKE: ID:{$produtoEncontrado['id']} custo=R\${$produtoEncontrado['custo_unitario']}" 
                                    : "  [2b] Nome LIKE: NAO encontrado";
                            }
                            
                            if ($produtoEncontrado && floatval($produtoEncontrado['custo_unitario'] ?? 0) > 0) {
                                $produto = $produtoEncontrado;
                                if (!$produtoId) {
                                    $stmtVincula = $this->db->prepare("UPDATE pedidos_itens SET produto_id = :pid WHERE id = :id");
                                    $stmtVincula->execute(['pid' => $produtoEncontrado['id'], 'id' => $item['id']]);
                                    $logs[] = "  -> Vinculado produto_id={$produtoEncontrado['id']}";
                                }
                            } else {
                                $logs[] = "  [2] Nenhum produto com custo > 0 para '{$nomeProdutoItem}'";
                            }
                        }
                        
                        if ($produto && floatval($produto['custo_unitario'] ?? 0) > 0) {
                            $novoCustoUnitario = floatval($produto['custo_unitario']);
                            $quantidade = floatval($item['quantidade'] ?? 1);
                            $novoCustoTotal = round($quantidade * $novoCustoUnitario, 2);
                            
                            $stmtItem = $this->db->prepare("UPDATE pedidos_itens SET custo_unitario = :cu, custo_total = :ct WHERE id = :id");
                            $stmtItem->execute(['cu' => $novoCustoUnitario, 'ct' => $novoCustoTotal, 'id' => $item['id']]);
                            
                            $logs[] = "  OK Item ID:{$item['id']} custo=R\${$novoCustoUnitario} x {$quantidade} = R\${$novoCustoTotal}";
                            $totalItensAtualizados++;
                            $atualizouAlgo = true;
                        } else {
                            $logs[] = "  IGNORADO Item ID:{$item['id']} - sem produto/custo";
                            $itensIgnorados++;
                        }
                    }
                    
                    if ($atualizouAlgo) {
                        $this->pedidoModel->recalcularTotais($pedido['id']);
                        $logs[] = "Pedido #{$pedido['numero_pedido']}: totais recalculados OK";
                        $totalRecalculados++;
                    }
                    
                } catch (\Throwable $e) {
                    $logs[] = "ERRO pedido #{$pedido['numero_pedido']}: " . $e->getMessage();
                    $erros[] = "Erro no pedido #{$pedido['numero_pedido']}: " . $e->getMessage();
                }
            }
            
            $logs[] = "=== FIM === recalculados: {$totalRecalculados}, itens: {$totalItensAtualizados}, ignorados: {$itensIgnorados}, erros: " . count($erros);
            
            if ($totalRecalculados > 0) {
                $mensagem = "✅ {$totalRecalculados} pedido(s) recalculado(s), {$totalItensAtualizados} item(ns) atualizado(s) com custo.";
                if ($itensIgnorados > 0) {
                    $mensagem .= " {$itensIgnorados} item(ns) não atualizado(s) (produto sem custo cadastrado).";
                }
                $this->session->set('success', $mensagem);
            } else if (count($pedidos) > 0) {
                $this->session->set('info', count($pedidos) . ' pedido(s) com ' . $itensIgnorados . ' item(ns) sem custo, porém produtos não possuem custo para atualizar. Veja logs.');
            } else {
                $this->session->set('info', 'Nenhum pedido com itens sem custo encontrado.');
            }
            
            if (!empty($erros)) {
                $this->session->set('warning', implode('<br>', $erros));
            }
            
        } catch (\Throwable $e) {
            $logs[] = "ERRO FATAL: " . $e->getMessage();
            $this->session->set('error', 'Erro ao recalcular pedidos: ' . $e->getMessage());
        }
        
        // Grava todos os logs de uma vez no final (fora de qualquer transação)
        try {
            LogSistema::info('Recalcular', 'resultado', implode("\n", $logs));
        } catch (\Throwable $ignore) {}
        
        return $response->redirect('/pedidos');
    }
}
