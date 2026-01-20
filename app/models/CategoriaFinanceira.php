<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Categorias Financeiras
 * Suporta estrutura hierárquica (pai/filho)
 */
class CategoriaFinanceira extends Model
{
    protected $table = 'categorias_financeiras';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as categorias (flat)
     */
    public function findAll($empresaId = null, $tipo = null, $limit = null, $offset = 0, $filters = [])
    {
        $sql = "SELECT c.*, e.nome_fantasia as empresa_nome 
                FROM {$this->table} c
                LEFT JOIN empresas e ON c.empresa_id = e.id
                WHERE c.ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND c.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        if ($tipo) {
            $sql .= " AND c.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        // Filtro por código
        if (!empty($filters['codigo'])) {
            $sql .= " AND c.codigo LIKE :codigo";
            $params['codigo'] = '%' . $filters['codigo'] . '%';
        }
        
        // Filtro por nome
        if (!empty($filters['nome'])) {
            $sql .= " AND c.nome LIKE :nome";
            $params['nome'] = '%' . $filters['nome'] . '%';
        }
        
        $sql .= " ORDER BY c.codigo ASC, c.nome ASC";
        
        // Adiciona paginação se limit for especificado
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        // Bind de limit e offset como inteiros
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Conta total de categorias (para paginação)
     */
    public function count($empresaId = null, $tipo = null, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        // Filtro por código
        if (!empty($filters['codigo'])) {
            $sql .= " AND codigo LIKE :codigo";
            $params['codigo'] = '%' . $filters['codigo'] . '%';
        }
        
        // Filtro por nome
        if (!empty($filters['nome'])) {
            $sql .= " AND nome LIKE :nome";
            $params['nome'] = '%' . $filters['nome'] . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Retorna categorias organizadas hierarquicamente
     */
    public function findHierarchical($empresaId = null, $tipo = null)
    {
        $all = $this->findAll($empresaId, $tipo);
        return $this->buildTree($all);
    }
    
    /**
     * Constrói árvore hierárquica
     */
    private function buildTree($items, $parentId = null)
    {
        $branch = [];
        
        foreach ($items as $item) {
            $itemParentId = $item['categoria_pai_id'] ? (int)$item['categoria_pai_id'] : null;
            
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
     * Retorna uma categoria por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna categorias filhas de uma categoria pai
     */
    public function findChildren($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE categoria_pai_id = :parent_id ORDER BY codigo ASC, nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna categoria por código
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
     * Verifica se uma categoria pode ser pai (evita loops)
     */
    public function canBeParent($categoriaId, $parentId)
    {
        if (!$parentId || $categoriaId == $parentId) {
            return false;
        }
        
        // Verifica se o parentId não é descendente da categoriaId
        $current = $this->findById($parentId);
        while ($current && $current['categoria_pai_id']) {
            if ($current['categoria_pai_id'] == $categoriaId) {
                return false; // Loop detectado
            }
            $current = $this->findById($current['categoria_pai_id']);
        }
        
        return true;
    }
    
    /**
     * Gera o próximo código sequencial para uma categoria
     */
    public function gerarProximoCodigo($empresaId, $tipo)
    {
        $sql = "SELECT codigo FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND tipo = :tipo 
                AND codigo REGEXP '^[0-9]+$'
                ORDER BY CAST(codigo AS UNSIGNED) DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'tipo' => $tipo
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && is_numeric($result['codigo'])) {
            // Incrementa o último código numérico
            $proximoCodigo = (int)$result['codigo'] + 1;
        } else {
            // Primeiro código
            $proximoCodigo = 1;
        }
        
        // Retorna com padding de zeros (ex: 001, 002, etc.)
        return str_pad($proximoCodigo, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Cria uma nova categoria
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, codigo, nome, tipo, categoria_pai_id, ativo) 
                VALUES 
                (:empresa_id, :codigo, :nome, :tipo, :categoria_pai_id, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'categoria_pai_id' => $data['categoria_pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma categoria
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                codigo = :codigo,
                nome = :nome,
                tipo = :tipo,
                categoria_pai_id = :categoria_pai_id,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'categoria_pai_id' => $data['categoria_pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui uma categoria (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna caminho completo da categoria (breadcrumb)
     */
    public function getPath($id)
    {
        $path = [];
        $current = $this->findById($id);
        
        while ($current) {
            array_unshift($path, $current);
            if ($current['categoria_pai_id']) {
                $current = $this->findById($current['categoria_pai_id']);
            } else {
                $current = null;
            }
        }
        
        return $path;
    }
    
    /**
     * Retorna todas as categorias disponíveis para serem pais (exceto a própria e seus descendentes)
     */
    public function getAvailableParents($empresaId, $excludeId = null, $tipo = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];
        
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        if ($excludeId) {
            // Exclui a própria categoria e todas as suas descendentes
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
     * Retorna todos os descendentes de uma categoria
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

