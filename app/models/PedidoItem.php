<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Itens de Pedidos
 */
class PedidoItem extends Model
{
    protected $table = 'pedidos_itens';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar itens por pedido
     */
    public function findByPedido($pedidoId)
    {
        $sql = "SELECT pi.*,
                       pr.codigo as produto_codigo, pr.nome as produto_nome_cadastrado
                FROM {$this->table} pi
                LEFT JOIN produtos pr ON pi.produto_id = pr.id
                WHERE pi.pedido_id = :pedido_id
                ORDER BY pi.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pedido_id' => $pedidoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar item por ID
     */
    public function findById($id)
    {
        $sql = "SELECT pi.*,
                       pr.codigo as produto_codigo, pr.nome as produto_nome_cadastrado
                FROM {$this->table} pi
                LEFT JOIN produtos pr ON pi.produto_id = pr.id
                WHERE pi.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar item de pedido
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (pedido_id, produto_id, codigo_produto_origem, nome_produto, quantidade, valor_unitario, valor_total, custo_unitario, custo_total) 
                VALUES 
                (:pedido_id, :produto_id, :codigo_produto_origem, :nome_produto, :quantidade, :valor_unitario, :valor_total, :custo_unitario, :custo_total)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'pedido_id' => $data['pedido_id'],
            'produto_id' => $data['produto_id'] ?? null,
            'codigo_produto_origem' => $data['codigo_produto_origem'] ?? null,
            'nome_produto' => $data['nome_produto'],
            'quantidade' => $data['quantidade'],
            'valor_unitario' => $data['valor_unitario'],
            'valor_total' => $data['valor_total'],
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'custo_total' => $data['custo_total'] ?? 0
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar item
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                produto_id = :produto_id,
                codigo_produto_origem = :codigo_produto_origem,
                nome_produto = :nome_produto,
                quantidade = :quantidade,
                valor_unitario = :valor_unitario,
                valor_total = :valor_total,
                custo_unitario = :custo_unitario,
                custo_total = :custo_total
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'produto_id' => $data['produto_id'] ?? null,
            'codigo_produto_origem' => $data['codigo_produto_origem'] ?? null,
            'nome_produto' => $data['nome_produto'],
            'quantidade' => $data['quantidade'],
            'valor_unitario' => $data['valor_unitario'],
            'valor_total' => $data['valor_total'],
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'custo_total' => $data['custo_total'] ?? 0
        ]);
    }
    
    /**
     * Deletar item
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Deletar todos os itens de um pedido
     */
    public function deleteByPedido($pedidoId)
    {
        $sql = "DELETE FROM {$this->table} WHERE pedido_id = :pedido_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['pedido_id' => $pedidoId]);
    }
    
    /**
     * Contar itens por pedido
     */
    public function countByPedido($pedidoId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE pedido_id = :pedido_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pedido_id' => $pedidoId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Produtos mais vendidos
     */
    public function getProdutosMaisVendidos($empresasIds, $limit = 10)
    {
        if (empty($empresasIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT 
                    pi.produto_id,
                    pi.nome_produto,
                    pr.codigo as produto_codigo,
                    SUM(pi.quantidade) as quantidade_total,
                    SUM(pi.valor_total) as valor_total,
                    COUNT(DISTINCT pi.pedido_id) as total_pedidos
                FROM {$this->table} pi
                INNER JOIN pedidos_vinculados pv ON pi.pedido_id = pv.id
                LEFT JOIN produtos pr ON pi.produto_id = pr.id
                WHERE pv.empresa_id IN ($placeholders)
                GROUP BY pi.produto_id, pi.nome_produto, pr.codigo
                ORDER BY quantidade_total DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($empresasIds, [$limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
