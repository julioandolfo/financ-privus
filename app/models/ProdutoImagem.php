<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para gerenciar imagens de produtos
 */
class ProdutoImagem extends Model
{
    protected $table = 'produtos_imagens';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria uma nova imagem de produto
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (produto_id, url_original, caminho_local, ordem, principal, largura, altura, tamanho_bytes) 
                VALUES 
                (:produto_id, :url_original, :caminho_local, :ordem, :principal, :largura, :altura, :tamanho_bytes)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'produto_id' => $data['produto_id'],
            'url_original' => $data['url_original'] ?? null,
            'caminho_local' => $data['caminho_local'],
            'ordem' => $data['ordem'] ?? 0,
            'principal' => $data['principal'] ?? false,
            'largura' => $data['largura'] ?? null,
            'altura' => $data['altura'] ?? null,
            'tamanho_bytes' => $data['tamanho_bytes'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Busca imagens de um produto
     */
    public function findByProduto($produtoId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE produto_id = :produto_id 
                ORDER BY principal DESC, ordem ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['produto_id' => $produtoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca imagem principal de um produto
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
     * Define uma imagem como principal
     */
    public function definirPrincipal($id, $produtoId)
    {
        // Remove principal de todas as outras
        $sql1 = "UPDATE {$this->table} SET principal = 0 WHERE produto_id = :produto_id";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute(['produto_id' => $produtoId]);
        
        // Define a nova principal
        $sql2 = "UPDATE {$this->table} SET principal = 1 WHERE id = :id";
        $stmt2 = $this->db->prepare($sql2);
        return $stmt2->execute(['id' => $id]);
    }
    
    /**
     * Exclui imagem
     */
    public function delete($id)
    {
        // Busca caminho da imagem antes de excluir
        $imagem = $this->findById($id);
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(['id' => $id]);
        
        // Remove arquivo físico
        if ($result && $imagem) {
            $caminhoCompleto = __DIR__ . '/../../public/uploads/' . $imagem['caminho_local'];
            if (file_exists($caminhoCompleto)) {
                @unlink($caminhoCompleto);
            }
        }
        
        return $result;
    }
    
    /**
     * Exclui todas as imagens de um produto
     */
    public function deleteByProduto($produtoId)
    {
        $imagens = $this->findByProduto($produtoId);
        
        foreach ($imagens as $imagem) {
            $this->delete($imagem['id']);
        }
        
        return true;
    }
    
    /**
     * Busca por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Verifica se produto já tem imagem com essa URL
     */
    public function existePorUrl($produtoId, $urlOriginal)
    {
        $sql = "SELECT id FROM {$this->table} 
                WHERE produto_id = :produto_id AND url_original = :url_original 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'produto_id' => $produtoId,
            'url_original' => $urlOriginal
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
