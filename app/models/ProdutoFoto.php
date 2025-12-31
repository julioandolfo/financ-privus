<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ProdutoFoto extends Model
{
    protected $table = 'produtos_fotos';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todas as fotos de um produto
     */
    public function findByProduto($produtoId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE produto_id = :produto_id 
                ORDER BY principal DESC, ordem ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar foto por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Buscar foto principal de um produto
     */
    public function findPrincipal($produtoId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE produto_id = :produto_id AND principal = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Criar nova foto
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (produto_id, arquivo, caminho, tamanho, tipo, principal, ordem) 
                VALUES 
                (:produto_id, :arquivo, :caminho, :tamanho, :tipo, :principal, :ordem)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'produto_id' => $data['produto_id'],
            'arquivo' => $data['arquivo'],
            'caminho' => $data['caminho'],
            'tamanho' => $data['tamanho'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'principal' => $data['principal'] ?? 0,
            'ordem' => $data['ordem'] ?? 0
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar foto
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                principal = :principal,
                ordem = :ordem
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'principal' => $data['principal'] ?? 0,
            'ordem' => $data['ordem'] ?? 0
        ]);
    }
    
    /**
     * Excluir foto
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Definir foto como principal (desmarca outras)
     */
    public function setPrincipal($id, $produtoId)
    {
        // Remove principal de todas as fotos do produto
        $sql = "UPDATE {$this->table} SET principal = 0 WHERE produto_id = :produto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        // Define a foto como principal
        $sql = "UPDATE {$this->table} SET principal = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Atualizar ordem das fotos
     */
    public function updateOrdem($fotos)
    {
        foreach ($fotos as $ordem => $fotoId) {
            $sql = "UPDATE {$this->table} SET ordem = :ordem WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ordem' => $ordem, 'id' => $fotoId]);
        }
        return true;
    }
    
    /**
     * Contar fotos de um produto
     */
    public function count($produtoId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE produto_id = :produto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
}
