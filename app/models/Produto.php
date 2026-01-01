<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Produtos
 */
class Produto extends Model
{
    protected $table = 'produtos';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os produtos
     */
    public function findAll($empresaId = null, $filters = [])
    {
        $sql = "SELECT p.*, e.razao_social as empresa_nome
                FROM {$this->table} p
                INNER JOIN empresas e ON p.empresa_id = e.id
                WHERE p.ativo = 1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND p.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        // Filtro por código ou nome
        if (!empty($filters['busca'])) {
            $sql .= " AND (p.codigo LIKE :busca OR p.nome LIKE :busca)";
            $params['busca'] = '%' . $filters['busca'] . '%';
        }
        
        // Filtro por categoria
        if (!empty($filters['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }
        
        // Filtro por status de estoque
        if (!empty($filters['estoque_status'])) {
            switch ($filters['estoque_status']) {
                case 'baixo':
                    $sql .= " AND p.estoque <= p.estoque_minimo";
                    break;
                case 'ok':
                    $sql .= " AND p.estoque > p.estoque_minimo";
                    break;
                case 'zero':
                    $sql .= " AND p.estoque = 0";
                    break;
            }
        }
        
        $sql .= " ORDER BY p.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar produto por ID
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, e.razao_social as empresa_nome
                FROM {$this->table} p
                INNER JOIN empresas e ON p.empresa_id = e.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar produto por código
     */
    public function findByCodigo($codigo, $empresaId, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE codigo = :codigo AND empresa_id = :empresa_id AND ativo = 1";
        
        $params = [
            'codigo' => $codigo,
            'empresa_id' => $empresaId
        ];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar produto
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, categoria_id, codigo, codigo_barras, nome, descricao, custo_unitario, preco_venda, unidade_medida, estoque, estoque_minimo) 
                VALUES 
                (:empresa_id, :categoria_id, :codigo, :codigo_barras, :nome, :descricao, :custo_unitario, :preco_venda, :unidade_medida, :estoque, :estoque_minimo)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'categoria_id' => $data['categoria_id'] ?? null,
            'codigo' => $data['codigo'],
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'preco_venda' => $data['preco_venda'] ?? 0,
            'unidade_medida' => $data['unidade_medida'] ?? 'UN',
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar produto
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                categoria_id = :categoria_id,
                codigo = :codigo,
                codigo_barras = :codigo_barras,
                nome = :nome,
                descricao = :descricao,
                custo_unitario = :custo_unitario,
                preco_venda = :preco_venda,
                unidade_medida = :unidade_medida,
                estoque = :estoque,
                estoque_minimo = :estoque_minimo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'categoria_id' => $data['categoria_id'] ?? null,
            'codigo' => $data['codigo'],
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'preco_venda' => $data['preco_venda'] ?? 0,
            'unidade_medida' => $data['unidade_medida'] ?? 'UN',
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0
        ]);
    }
    
    /**
     * Deletar produto (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Calcular margem de lucro
     */
    public function calcularMargemLucro($custoUnitario, $precoVenda)
    {
        if ($custoUnitario <= 0) {
            return 0;
        }
        
        $lucro = $precoVenda - $custoUnitario;
        $margemPercentual = ($lucro / $custoUnitario) * 100;
        
        return $margemPercentual;
    }
    
    /**
     * Buscar produtos mais vendidos (placeholder para futura integração com pedidos)
     */
    public function getMaisVendidos($empresaId, $limit = 10)
    {
        // Por enquanto retorna produtos ordenados por nome
        // Futuramente será por quantidade de pedidos
        $sql = "SELECT * FROM {$this->table}
                WHERE empresa_id = :empresa_id AND ativo = 1
                ORDER BY nome ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Contar produtos por empresa
     */
    public function countByEmpresa($empresaId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE empresa_id = :empresa_id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar produtos para select/autocomplete
     */
    public function findForSelect($empresaId, $busca = '')
    {
        $sql = "SELECT id, codigo, nome, preco_venda, custo_unitario, unidade_medida
                FROM {$this->table}
                WHERE empresa_id = :empresa_id AND ativo = 1";
        
        $params = ['empresa_id' => $empresaId];
        
        if (!empty($busca)) {
            $sql .= " AND (codigo LIKE :busca OR nome LIKE :busca)";
            $params['busca'] = '%' . $busca . '%';
        }
        
        $sql .= " ORDER BY nome ASC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Obter estatísticas de produtos
     */
    public function getEstatisticas($empresaId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_produtos,
                    AVG(preco_venda) as preco_medio,
                    AVG(custo_unitario) as custo_medio,
                    MIN(preco_venda) as preco_minimo,
                    MAX(preco_venda) as preco_maximo
                FROM {$this->table}
                WHERE empresa_id = :empresa_id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter produtos com estoque baixo (estoque <= estoque_minimo)
     */
    public function getProdutosEstoqueBaixo($empresaId)
    {
        $sql = "SELECT p.*, 
                       c.nome as categoria_nome
                FROM {$this->table} p
                LEFT JOIN categorias_produtos c ON p.categoria_id = c.id
                WHERE p.empresa_id = :empresa_id 
                AND p.ativo = 1
                AND p.estoque <= p.estoque_minimo
                ORDER BY (p.estoque_minimo - p.estoque) DESC, p.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Atualiza apenas os campos tributários de um produto
     */
    public function updateTributos($id, $dadosTributarios)
    {
        $sql = "UPDATE {$this->table} SET
                    ncm = :ncm,
                    cest = :cest,
                    origem = :origem,
                    cfop_venda = :cfop_venda,
                    cst_icms = :cst_icms,
                    aliquota_icms = :aliquota_icms,
                    reducao_base_icms = :reducao_base_icms,
                    cst_ipi = :cst_ipi,
                    aliquota_ipi = :aliquota_ipi,
                    cst_pis = :cst_pis,
                    aliquota_pis = :aliquota_pis,
                    cst_cofins = :cst_cofins,
                    aliquota_cofins = :aliquota_cofins,
                    unidade_tributavel = :unidade_tributavel,
                    informacoes_adicionais = :informacoes_adicionais,
                    gtin = :gtin,
                    gtin_tributavel = :gtin_tributavel,
                    updated_at = NOW()
                WHERE id = :id";
        
        $params = array_merge($dadosTributarios, ['id' => $id]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
