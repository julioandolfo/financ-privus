<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class CategoriaProduto extends Model
{
    protected $table = 'categorias_produtos';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todas as categorias
     */
    public function findAll($empresaId = null, $incluirInativos = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        if (!$incluirInativos) {
            $sql .= " AND ativo = 1";
        }
        
        $sql .= " ORDER BY ordem ASC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar categoria por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Criar nova categoria
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, categoria_pai_id, nome, descricao, icone, cor, ordem, ativo) 
                VALUES 
                (:empresa_id, :categoria_pai_id, :nome, :descricao, :icone, :cor, :ordem, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'categoria_pai_id' => $data['categoria_pai_id'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'icone' => $data['icone'] ?? null,
            'cor' => $data['cor'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar categoria
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                categoria_pai_id = :categoria_pai_id,
                nome = :nome,
                descricao = :descricao,
                icone = :icone,
                cor = :cor,
                ordem = :ordem,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'categoria_pai_id' => $data['categoria_pai_id'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'icone' => $data['icone'] ?? null,
            'cor' => $data['cor'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Excluir categoria (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Construir árvore de categorias
     */
    public function buildTree($empresaId, $parentId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND ativo = 1 
                AND " . ($parentId ? "categoria_pai_id = :parent_id" : "categoria_pai_id IS NULL") . "
                ORDER BY ordem ASC, nome ASC";
        
        $params = ['empresa_id' => $empresaId];
        if ($parentId) {
            $params['parent_id'] = $parentId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($categorias as &$categoria) {
            $categoria['filhos'] = $this->buildTree($empresaId, $categoria['id']);
            $categoria['tem_filhos'] = count($categoria['filhos']) > 0;
        }
        
        return $categorias;
    }
    
    /**
     * Obter caminho completo da categoria (breadcrumb)
     */
    public function getPath($id)
    {
        $path = [];
        $categoria = $this->findById($id);
        
        while ($categoria) {
            array_unshift($path, $categoria);
            $categoria = $categoria['categoria_pai_id'] 
                ? $this->findById($categoria['categoria_pai_id']) 
                : null;
        }
        
        return $path;
    }
    
    /**
     * Verificar se pode ser pai (evita loops)
     */
    public function canBeParent($categoriaId, $potentialParentId)
    {
        if ($categoriaId == $potentialParentId) {
            return false;
        }
        
        $descendentes = $this->getDescendants($categoriaId);
        foreach ($descendentes as $desc) {
            if ($desc['id'] == $potentialParentId) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Obter todos os descendentes de uma categoria
     */
    public function getDescendants($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE categoria_pai_id = :parent_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $descendants = [];
        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $this->getDescendants($child['id']));
        }
        
        return $descendants;
    }
    
    /**
     * Contar produtos por categoria
     */
    public function countProdutos($categoriaId)
    {
        $sql = "SELECT COUNT(*) as total FROM produtos 
                WHERE categoria_id = :categoria_id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['categoria_id' => $categoriaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obter lista flat (para selects)
     */
    public function getFlatList($empresaId, $level = 0, $parentId = null, &$result = [])
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND ativo = 1 
                AND " . ($parentId ? "categoria_pai_id = :parent_id" : "categoria_pai_id IS NULL") . "
                ORDER BY ordem ASC, nome ASC";
        
        $params = ['empresa_id' => $empresaId];
        if ($parentId) {
            $params['parent_id'] = $parentId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($categorias as $categoria) {
            $categoria['level'] = $level;
            $categoria['indent'] = str_repeat('—', $level);
            $result[] = $categoria;
            $this->getFlatList($empresaId, $level + 1, $categoria['id'], $result);
        }
        
        return $result;
    }
}
