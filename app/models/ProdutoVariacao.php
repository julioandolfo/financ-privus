<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ProdutoVariacao extends Model
{
    protected $table = 'produtos_variacoes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todas as variações de um produto
     */
    public function findByProduto($produtoId, $incluirInativos = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE produto_id = :produto_id";
        
        if (!$incluirInativos) {
            $sql .= " AND ativo = 1";
        }
        
        $sql .= " ORDER BY nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        $variacoes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Decodifica JSON de atributos e dimensões
        foreach ($variacoes as &$variacao) {
            $variacao['atributos'] = $variacao['atributos'] ? json_decode($variacao['atributos'], true) : [];
            $variacao['dimensoes'] = $variacao['dimensoes'] ? json_decode($variacao['dimensoes'], true) : [];
        }
        
        return $variacoes;
    }
    
    /**
     * Buscar variação por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $variacao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($variacao) {
            $variacao['atributos'] = $variacao['atributos'] ? json_decode($variacao['atributos'], true) : [];
            $variacao['dimensoes'] = $variacao['dimensoes'] ? json_decode($variacao['dimensoes'], true) : [];
        }
        
        return $variacao ?: null;
    }
    
    /**
     * Buscar variação por SKU
     */
    public function findBySku($sku)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sku = :sku LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sku' => $sku]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Buscar variação por código de barras
     */
    public function findByCodigoBarras($codigoBarras)
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_barras = :codigo_barras LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['codigo_barras' => $codigoBarras]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Criar nova variação
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (produto_id, nome, sku, codigo_barras, atributos, custo_unitario, preco_venda, 
                 estoque, estoque_minimo, peso, dimensoes, ativo) 
                VALUES 
                (:produto_id, :nome, :sku, :codigo_barras, :atributos, :custo_unitario, :preco_venda, 
                 :estoque, :estoque_minimo, :peso, :dimensoes, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'produto_id' => $data['produto_id'],
            'nome' => $data['nome'],
            'sku' => $data['sku'] ?? null,
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'atributos' => isset($data['atributos']) ? json_encode($data['atributos']) : null,
            'custo_unitario' => $data['custo_unitario'] ?? null,
            'preco_venda' => $data['preco_venda'] ?? null,
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0,
            'peso' => $data['peso'] ?? null,
            'dimensoes' => isset($data['dimensoes']) ? json_encode($data['dimensoes']) : null,
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar variação
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome = :nome,
                sku = :sku,
                codigo_barras = :codigo_barras,
                atributos = :atributos,
                custo_unitario = :custo_unitario,
                preco_venda = :preco_venda,
                estoque = :estoque,
                estoque_minimo = :estoque_minimo,
                peso = :peso,
                dimensoes = :dimensoes,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'sku' => $data['sku'] ?? null,
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'atributos' => isset($data['atributos']) ? json_encode($data['atributos']) : null,
            'custo_unitario' => $data['custo_unitario'] ?? null,
            'preco_venda' => $data['preco_venda'] ?? null,
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0,
            'peso' => $data['peso'] ?? null,
            'dimensoes' => isset($data['dimensoes']) ? json_encode($data['dimensoes']) : null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Excluir variação (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Excluir permanentemente
     */
    public function deletePermanent($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Atualizar estoque
     */
    public function updateEstoque($id, $quantidade, $operacao = 'adicionar')
    {
        if ($operacao === 'adicionar') {
            $sql = "UPDATE {$this->table} SET estoque = estoque + :quantidade WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->table} SET estoque = estoque - :quantidade WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'quantidade' => $quantidade]);
    }
    
    /**
     * Contar variações de um produto
     */
    public function count($produtoId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE produto_id = :produto_id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Calcular estoque total do produto (soma de todas as variações)
     */
    public function calcularEstoqueTotal($produtoId)
    {
        $sql = "SELECT SUM(estoque) as total FROM {$this->table} 
                WHERE produto_id = :produto_id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Verificar variações com estoque baixo
     */
    public function getEstoqueBaixo($produtoId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE produto_id = :produto_id 
                AND ativo = 1 
                AND estoque <= estoque_minimo
                ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
