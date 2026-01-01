<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Centros de Custo
 * Suporta estrutura hierárquica (pai/filho)
 */
class CentroCusto extends Model
{
    protected $table = 'centros_custo';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os centros de custo (flat)
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY codigo ASC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna centros de custo organizados hierarquicamente
     */
    public function findHierarchical($empresaId = null)
    {
        $all = $this->findAll($empresaId);
        return $this->buildTree($all);
    }
    
    /**
     * Constrói árvore hierárquica
     */
    private function buildTree($items, $parentId = null)
    {
        $branch = [];
        
        foreach ($items as $item) {
            $itemParentId = $item['centro_pai_id'] ? (int)$item['centro_pai_id'] : null;
            
            if ($itemParentId === $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        
        return $branch;
    }
    
    /**
     * Retorna um centro de custo por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna centros de custo filhos de um centro pai
     */
    public function findChildren($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE centro_pai_id = :parent_id AND ativo = 1 ORDER BY codigo ASC, nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna centro de custo por código
     */
    public function findByCodigo($codigo, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo";
        $params = ['codigo' => $codigo];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verifica se um centro de custo pode ser pai (evita loops)
     */
    public function canBeParent($centroId, $parentId)
    {
        if (!$parentId || $centroId == $parentId) {
            return false;
        }
        
        // Verifica se o parentId não é descendente da centroId
        $current = $this->findById($parentId);
        while ($current && $current['centro_pai_id']) {
            if ($current['centro_pai_id'] == $centroId) {
                return false; // Loop detectado
            }
            $current = $this->findById($current['centro_pai_id']);
        }
        
        return true;
    }
    
    /**
     * Cria um novo centro de custo
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, codigo, nome, centro_pai_id, ativo) 
                VALUES 
                (:empresa_id, :codigo, :nome, :centro_pai_id, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'centro_pai_id' => $data['centro_pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza um centro de custo
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                codigo = :codigo,
                nome = :nome,
                centro_pai_id = :centro_pai_id,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'centro_pai_id' => $data['centro_pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui um centro de custo (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna caminho completo do centro de custo (breadcrumb)
     */
    public function getPath($id)
    {
        $path = [];
        $current = $this->findById($id);
        
        while ($current) {
            array_unshift($path, $current);
            if ($current['centro_pai_id']) {
                $current = $this->findById($current['centro_pai_id']);
            } else {
                $current = null;
            }
        }
        
        return $path;
    }
    
    /**
     * Retorna todos os centros de custo disponíveis para serem pais (exceto o próprio e seus descendentes)
     */
    public function getAvailableParents($empresaId, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE empresa_id = :empresa_id AND ativo = 1";
        $params = ['empresa_id' => $empresaId];
        
        if ($excludeId) {
            // Exclui o próprio centro de custo e todas as suas descendentes
            $excludeIds = [$excludeId];
            $children = $this->findChildren($excludeId);
            foreach ($children as $child) {
                $excludeIds = array_merge($excludeIds, $this->getAllDescendants($child['id']));
            }
            
            // Usar parâmetros nomeados em vez de posicionais
            $placeholders = [];
            foreach ($excludeIds as $index => $excludeIdValue) {
                $paramName = "exclude_id_$index";
                $placeholders[] = ":$paramName";
                $params[$paramName] = $excludeIdValue;
            }
            $sql .= " AND id NOT IN (" . implode(',', $placeholders) . ")";
        }
        
        $sql .= " ORDER BY codigo ASC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna todos os descendentes de um centro de custo
     */
    private function getAllDescendants($parentId)
    {
        $descendants = [];
        $children = $this->findChildren($parentId);
        
        foreach ($children as $child) {
            $descendants[] = $child['id'];
            $descendants = array_merge($descendants, $this->getAllDescendants($child['id']));
        }
        
        return $descendants;
    }
}

