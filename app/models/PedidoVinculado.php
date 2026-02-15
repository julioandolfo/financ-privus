<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Pedidos Vinculados
 */
class PedidoVinculado extends Model
{
    protected $table = 'pedidos_vinculados';
    protected $db;
    
    // Status possíveis
    const STATUS_PENDENTE = 'pendente';
    const STATUS_PROCESSANDO = 'processando';
    const STATUS_CONCLUIDO = 'concluido';
    const STATUS_CANCELADO = 'cancelado';
    const STATUS_REEMBOLSADO = 'reembolsado';
    
    // Origens possíveis
    const ORIGEM_WOOCOMMERCE = 'woocommerce';
    const ORIGEM_MANUAL = 'manual';
    const ORIGEM_EXTERNO = 'externo';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os pedidos
     */
    public function findAll($empresaId = null, $filters = [])
    {
        $sql = "SELECT p.*, 
                       c.nome_razao_social as cliente_nome,
                       e.razao_social as empresa_nome,
                       (SELECT COUNT(*) FROM pedidos_itens WHERE pedido_id = p.id) as total_itens,
                       pp.numero_pedido as pedido_pai_numero,
                       (SELECT COUNT(*) FROM {$this->table} pf WHERE pf.pedido_pai_id = p.id) as total_filhos
                FROM {$this->table} p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                INNER JOIN empresas e ON p.empresa_id = e.id
                LEFT JOIN {$this->table} pp ON p.pedido_pai_id = pp.id
                WHERE 1=1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND p.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        // Filtro por origem
        if (!empty($filters['origem'])) {
            $sql .= " AND p.origem = :origem";
            $params['origem'] = $filters['origem'];
        }
        
        // Filtro por status
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Filtro por cliente
        if (!empty($filters['cliente_id'])) {
            $sql .= " AND p.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }
        
        // Filtro por período
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND DATE(p.data_pedido) >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND DATE(p.data_pedido) <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        // Filtro por número do pedido
        if (!empty($filters['numero_pedido'])) {
            $sql .= " AND p.numero_pedido LIKE :numero_pedido";
            $params['numero_pedido'] = '%' . $filters['numero_pedido'] . '%';
        }
        
        // Filtro por status do WooCommerce (status_origem)
        if (!empty($filters['status_origem'])) {
            $sql .= " AND p.status_origem = :status_origem";
            $params['status_origem'] = $filters['status_origem'];
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
     * Buscar pedido por ID
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, 
                       c.nome_razao_social as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
                       c.codigo_cliente as cliente_codigo,
                       e.razao_social as empresa_nome,
                       pp.numero_pedido as pedido_pai_numero,
                       pp.valor_total as pedido_pai_valor,
                       pp.status as pedido_pai_status
                FROM {$this->table} p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                INNER JOIN empresas e ON p.empresa_id = e.id
                LEFT JOIN {$this->table} pp ON p.pedido_pai_id = pp.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar pedido por origem
     */
    public function findByOrigem($origem, $origemId, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE origem = :origem AND origem_id = :origem_id";
        
        $params = [
            'origem' => $origem,
            'origem_id' => $origemId
        ];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar pedidos com filtros aplicados
     */
    public function countWithFilters($empresaId = null, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                INNER JOIN empresas e ON p.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND p.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        // Aplicar os mesmos filtros do findAll
        if (!empty($filters['origem'])) {
            $sql .= " AND p.origem = :origem";
            $params['origem'] = $filters['origem'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['cliente_id'])) {
            $sql .= " AND p.cliente_id = :cliente_id";
            $params['cliente_id'] = $filters['cliente_id'];
        }
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND DATE(p.data_pedido) >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND DATE(p.data_pedido) <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        if (!empty($filters['numero_pedido'])) {
            $sql .= " AND p.numero_pedido LIKE :numero_pedido";
            $params['numero_pedido'] = '%' . $filters['numero_pedido'] . '%';
        }
        
        // Filtro por status do WooCommerce (status_origem)
        if (!empty($filters['status_origem'])) {
            $sql .= " AND p.status_origem = :status_origem";
            $params['status_origem'] = $filters['status_origem'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Criar pedido
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, origem, origem_id, numero_pedido, cliente_id, data_pedido, data_atualizacao, status, status_origem, valor_total, valor_custo_total, frete, desconto, bonificado, pedido_pai_id, observacoes, dados_origem) 
                VALUES 
                (:empresa_id, :origem, :origem_id, :numero_pedido, :cliente_id, :data_pedido, :data_atualizacao, :status, :status_origem, :valor_total, :valor_custo_total, :frete, :desconto, :bonificado, :pedido_pai_id, :observacoes, :dados_origem)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'origem' => $data['origem'],
            'origem_id' => $data['origem_id'],
            'numero_pedido' => $data['numero_pedido'],
            'cliente_id' => $data['cliente_id'] ?? null,
            'data_pedido' => $data['data_pedido'],
            'data_atualizacao' => $data['data_atualizacao'] ?? date('Y-m-d H:i:s'),
            'status' => $data['status'] ?? self::STATUS_PENDENTE,
            'status_origem' => $data['status_origem'] ?? null,
            'valor_total' => $data['valor_total'],
            'valor_custo_total' => $data['valor_custo_total'] ?? 0,
            'frete' => $data['frete'] ?? 0,
            'desconto' => $data['desconto'] ?? 0,
            'bonificado' => $data['bonificado'] ?? 0,
            'pedido_pai_id' => $data['pedido_pai_id'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'dados_origem' => isset($data['dados_origem']) ? json_encode($data['dados_origem']) : null
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar pedido
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                numero_pedido = :numero_pedido,
                cliente_id = :cliente_id,
                data_pedido = :data_pedido,
                data_atualizacao = :data_atualizacao,
                status = :status,
                status_origem = :status_origem,
                valor_total = :valor_total,
                valor_custo_total = :valor_custo_total,
                dados_origem = :dados_origem
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'numero_pedido' => $data['numero_pedido'],
            'cliente_id' => $data['cliente_id'] ?? null,
            'data_pedido' => $data['data_pedido'],
            'data_atualizacao' => $data['data_atualizacao'] ?? date('Y-m-d H:i:s'),
            'status' => $data['status'],
            'status_origem' => $data['status_origem'] ?? null,
            'valor_total' => $data['valor_total'],
            'valor_custo_total' => $data['valor_custo_total'] ?? 0,
            'dados_origem' => isset($data['dados_origem']) ? json_encode($data['dados_origem']) : null
        ]);
    }
    
    /**
     * Deletar pedido
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET status = :status, data_atualizacao = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }
    
    /**
     * Atualizar apenas os totais do pedido
     */
    public function updateTotais($id, $valorTotal, $valorCustoTotal, $frete = 0, $desconto = 0)
    {
        $sql = "UPDATE {$this->table} SET 
                valor_total = :valor_total, 
                valor_custo_total = :valor_custo_total,
                frete = :frete,
                desconto = :desconto,
                data_atualizacao = NOW() 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'valor_total' => $valorTotal,
            'valor_custo_total' => $valorCustoTotal,
            'frete' => $frete,
            'desconto' => $desconto
        ]);
    }
    
    /**
     * Atualização parcial do pedido (aceita qualquer campo)
     */
    public function updateParcial($id, $data)
    {
        $allowedFields = [
            'frete', 'desconto', 'bonificado', 'pedido_pai_id', 'status', 'status_origem', 'observacoes',
            'numero_pedido', 'cliente_id', 'data_pedido', 
            'valor_total', 'valor_custo_total'
        ];
        
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = "data_atualizacao = NOW()";
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Atualizar frete, desconto e bonificado do pedido (retrocompatibilidade)
     */
    public function updateFreteDesconto($id, $frete = null, $desconto = null, $bonificado = null)
    {
        $data = [];
        if ($frete !== null) $data['frete'] = $frete;
        if ($desconto !== null) $data['desconto'] = $desconto;
        if ($bonificado !== null) $data['bonificado'] = $bonificado;
        
        return $this->updateParcial($id, $data);
    }
    
    /**
     * Calcular totais do pedido baseado nos itens
     */
    public function recalcularTotais($id)
    {
        $sql = "UPDATE {$this->table} p SET
                valor_total = (SELECT SUM(valor_total) FROM pedidos_itens WHERE pedido_id = p.id),
                valor_custo_total = (SELECT SUM(custo_total) FROM pedidos_itens WHERE pedido_id = p.id)
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Buscar pedidos filhos (bonificados) de um pedido pai
     */
    public function findFilhos($pedidoPaiId)
    {
        $sql = "SELECT p.*, 
                       c.nome_razao_social as cliente_nome
                FROM {$this->table} p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.pedido_pai_id = :pedido_pai_id
                ORDER BY p.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pedido_pai_id' => $pedidoPaiId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Estatísticas de pedidos
     */
    public function getEstatisticas($empresasIds)
    {
        if (empty($empresasIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    COUNT(DISTINCT cliente_id) as total_clientes,
                    SUM(valor_total) as valor_total,
                    SUM(valor_custo_total) as custo_total,
                    AVG(valor_total) as ticket_medio,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'processando' THEN 1 ELSE 0 END) as processando,
                    SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                    SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
                FROM {$this->table}
                WHERE empresa_id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresasIds);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular margem
        if ($result && $result['custo_total'] > 0) {
            $lucro = $result['valor_total'] - $result['custo_total'];
            $result['margem_lucro'] = ($lucro / $result['custo_total']) * 100;
            $result['lucro_total'] = $lucro;
        } else {
            $result['margem_lucro'] = 0;
            $result['lucro_total'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Pedidos por origem
     */
    public function getPorOrigem($empresasIds)
    {
        if (empty($empresasIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT origem, COUNT(*) as total, SUM(valor_total) as valor_total
                FROM {$this->table}
                WHERE empresa_id IN ($placeholders)
                GROUP BY origem";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresasIds);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Estatísticas de pedidos bonificados por empresa
     */
    public function getBonificadosPorEmpresa($empresasIds)
    {
        if (empty($empresasIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT 
                    p.empresa_id,
                    e.nome_fantasia as empresa_nome,
                    COUNT(*) as total_bonificados,
                    SUM(p.valor_total) as valor_total_bonificado
                FROM {$this->table} p
                INNER JOIN empresas e ON p.empresa_id = e.id
                WHERE p.empresa_id IN ($placeholders)
                  AND p.bonificado = 1
                GROUP BY p.empresa_id, e.nome_fantasia
                ORDER BY valor_total_bonificado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresasIds);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Resumo geral de bonificados
     */
    public function getResumoBonificados($empresasIds)
    {
        if (empty($empresasIds)) {
            return [
                'total_pedidos' => 0,
                'valor_total' => 0
            ];
        }
        
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    COALESCE(SUM(valor_total), 0) as valor_total
                FROM {$this->table}
                WHERE empresa_id IN ($placeholders)
                  AND bonificado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresasIds);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_pedidos' => 0, 'valor_total' => 0];
    }
    
    /**
     * Busca todos os status_origem distintos do WooCommerce
     * 
     * @param int $empresaId ID da empresa
     * @return array Lista de status_origem únicos
     */
    public function getStatusOrigemDisponiveis($empresaId = null)
    {
        $sql = "SELECT DISTINCT status_origem
                FROM {$this->table}
                WHERE status_origem IS NOT NULL
                AND status_origem != ''
                AND origem = 'woocommerce'";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY status_origem ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $resultados ?: [];
    }
}
