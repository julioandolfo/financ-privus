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
        if (isset($filters['busca']) && $filters['busca'] !== '' && $filters['busca'] !== null) {
            $sql .= " AND (p.codigo LIKE :busca OR p.nome LIKE :busca)";
            $params['busca'] = '%' . $filters['busca'] . '%';
        }
        
        // Filtro por categoria
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '' && $filters['categoria_id'] !== null) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }
        
        // Filtro por status de estoque
        if (isset($filters['estoque_status']) && $filters['estoque_status'] !== '' && $filters['estoque_status'] !== null) {
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
        
        // Filtro por status de custo
        if (isset($filters['custo_status']) && $filters['custo_status'] !== '' && $filters['custo_status'] !== null) {
            switch ($filters['custo_status']) {
                case 'sem_custo':
                    $sql .= " AND (p.custo_unitario IS NULL OR p.custo_unitario = 0)";
                    break;
                case 'com_custo':
                    $sql .= " AND p.custo_unitario > 0";
                    break;
            }
        }
        
        $sql .= " ORDER BY p.id DESC";
        
        // Paginação
        if (isset($filters['limite'])) {
            if (isset($filters['offset'])) {
                $sql .= " LIMIT " . (int)$filters['limite'] . " OFFSET " . (int)$filters['offset'];
            } else {
                $sql .= " LIMIT " . (int)$filters['limite'];
            }
        }
        
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
     * Buscar produto por SKU
     */
    public function findBySku($sku, $empresaId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE sku = :sku AND empresa_id = :empresa_id AND ativo = 1
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sku' => $sku,
            'empresa_id' => $empresaId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar ou buscar produto por SKU (para API)
     */
    public function findOrCreateBySku($data, $empresaId)
    {
        // Se tem SKU, tenta buscar primeiro
        if (!empty($data['sku'])) {
            $produto = $this->findBySku($data['sku'], $empresaId);
            if ($produto) {
                return $produto;
            }
        }
        
        // Se não encontrou ou não tem SKU, cria novo produto
        $data['empresa_id'] = $empresaId;
        
        // Gera código automático se não fornecido
        if (empty($data['codigo'])) {
            $data['codigo'] = $data['sku'] ?? 'AUTO-' . uniqid();
        }
        
        // Define valores padrão
        $data['custo_unitario'] = $data['custo_unitario'] ?? 0;
        $data['preco_venda'] = $data['preco_venda'] ?? 0;
        $data['unidade_medida'] = $data['unidade_medida'] ?? 'UN';
        $data['estoque'] = $data['estoque'] ?? 0;
        $data['estoque_minimo'] = $data['estoque_minimo'] ?? 0;
        
        $id = $this->create($data);
        
        if ($id) {
            return $this->findById($id);
        }
        
        return null;
    }
    
    /**
     * Criar produto
     */
    public function create($data)
    {
        // Verifica se coluna cod_fornecedor existe
        $temCodFornecedor = $this->colunaExiste('cod_fornecedor');
        
        $colunas = 'empresa_id, categoria_id, codigo, sku, codigo_barras, nome, descricao, custo_unitario, preco_venda, unidade_medida, estoque, estoque_minimo';
        $placeholders = ':empresa_id, :categoria_id, :codigo, :sku, :codigo_barras, :nome, :descricao, :custo_unitario, :preco_venda, :unidade_medida, :estoque, :estoque_minimo';
        
        if ($temCodFornecedor) {
            $colunas .= ', cod_fornecedor';
            $placeholders .= ', :cod_fornecedor';
        }
        
        $sql = "INSERT INTO {$this->table} ({$colunas}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            'empresa_id' => $data['empresa_id'],
            'categoria_id' => $data['categoria_id'] ?? null,
            'codigo' => $data['codigo'],
            'sku' => $data['sku'] ?? null,
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'preco_venda' => $data['preco_venda'] ?? 0,
            'unidade_medida' => $data['unidade_medida'] ?? 'UN',
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0
        ];
        
        if ($temCodFornecedor) {
            $params['cod_fornecedor'] = $data['cod_fornecedor'] ?? null;
        }
        
        $success = $stmt->execute($params);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar produto
     */
    public function update($id, $data)
    {
        $temCodFornecedor = $this->colunaExiste('cod_fornecedor');
        
        $sql = "UPDATE {$this->table} SET
                categoria_id = :categoria_id,
                codigo = :codigo,
                sku = :sku,
                codigo_barras = :codigo_barras,
                nome = :nome,
                descricao = :descricao,
                custo_unitario = :custo_unitario,
                preco_venda = :preco_venda,
                unidade_medida = :unidade_medida,
                estoque = :estoque,
                estoque_minimo = :estoque_minimo";
        
        if ($temCodFornecedor && array_key_exists('cod_fornecedor', $data)) {
            $sql .= ", cod_fornecedor = :cod_fornecedor";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            'id' => $id,
            'categoria_id' => $data['categoria_id'] ?? null,
            'codigo' => $data['codigo'],
            'sku' => $data['sku'] ?? null,
            'codigo_barras' => $data['codigo_barras'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'custo_unitario' => $data['custo_unitario'] ?? 0,
            'preco_venda' => $data['preco_venda'] ?? 0,
            'unidade_medida' => $data['unidade_medida'] ?? 'UN',
            'estoque' => $data['estoque'] ?? 0,
            'estoque_minimo' => $data['estoque_minimo'] ?? 0
        ];
        
        if ($temCodFornecedor && array_key_exists('cod_fornecedor', $data)) {
            $params['cod_fornecedor'] = $data['cod_fornecedor'] ?? null;
        }
        
        return $stmt->execute($params);
    }
    
    /**
     * Verifica se uma coluna existe na tabela
     */
    private function colunaExiste($coluna)
    {
        static $cache = [];
        $key = $this->table . '.' . $coluna;
        
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        
        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE :coluna";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['coluna' => $coluna]);
            $cache[$key] = $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            $cache[$key] = false;
        }
        
        return $cache[$key];
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
     * Contar produtos com filtros aplicados
     */
    public function countWithFilters($empresaId = null, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} p
                INNER JOIN empresas e ON p.empresa_id = e.id
                WHERE p.ativo = 1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND p.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        // Aplicar os mesmos filtros do findAll
        if (isset($filters['busca']) && $filters['busca'] !== '' && $filters['busca'] !== null) {
            $sql .= " AND (p.codigo LIKE :busca OR p.nome LIKE :busca)";
            $params['busca'] = '%' . $filters['busca'] . '%';
        }
        
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '' && $filters['categoria_id'] !== null) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params['categoria_id'] = $filters['categoria_id'];
        }
        
        if (isset($filters['estoque_status']) && $filters['estoque_status'] !== '' && $filters['estoque_status'] !== null) {
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
        
        // Filtro por status de custo (para contagem)
        if (isset($filters['custo_status']) && $filters['custo_status'] !== '' && $filters['custo_status'] !== null) {
            switch ($filters['custo_status']) {
                case 'sem_custo':
                    $sql .= " AND (p.custo_unitario IS NULL OR p.custo_unitario = 0)";
                    break;
                case 'com_custo':
                    $sql .= " AND p.custo_unitario > 0";
                    break;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
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
