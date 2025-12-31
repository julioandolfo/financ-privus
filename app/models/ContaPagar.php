<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Contas a Pagar
 */
class ContaPagar extends Model
{
    protected $table = 'contas_pagar';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as contas a pagar
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome,
                       u.nome as usuario_cadastro_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cp.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cp.forma_pagamento_id = fp.id
                JOIN usuarios u ON cp.usuario_cadastro_id = u.id
                WHERE 1=1";
        $params = [];
        
        // Filtro por empresa ou consolidação
        if (isset($filters['empresas_ids']) && is_array($filters['empresas_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['empresas_ids']), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = array_merge($params, $filters['empresas_ids']);
        } elseif (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $sql .= " AND cp.empresa_id = ?";
            $params[] = $filters['empresa_id'];
        }
        
        // Filtro por status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND cp.status = ?";
            $params[] = $filters['status'];
        }
        
        // Filtro por fornecedor
        if (isset($filters['fornecedor_id']) && $filters['fornecedor_id'] !== '') {
            $sql .= " AND cp.fornecedor_id = ?";
            $params[] = $filters['fornecedor_id'];
        }
        
        // Filtro por categoria
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '') {
            $sql .= " AND cp.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }
        
        // Filtro por data de competência
        if (isset($filters['data_competencia_inicio']) && $filters['data_competencia_inicio'] !== '') {
            $sql .= " AND cp.data_competencia >= ?";
            $params[] = $filters['data_competencia_inicio'];
        }
        if (isset($filters['data_competencia_fim']) && $filters['data_competencia_fim'] !== '') {
            $sql .= " AND cp.data_competencia <= ?";
            $params[] = $filters['data_competencia_fim'];
        }
        
        // Filtro por data de vencimento
        if (isset($filters['data_vencimento_inicio']) && $filters['data_vencimento_inicio'] !== '') {
            $sql .= " AND cp.data_vencimento >= ?";
            $params[] = $filters['data_vencimento_inicio'];
        }
        if (isset($filters['data_vencimento_fim']) && $filters['data_vencimento_fim'] !== '') {
            $sql .= " AND cp.data_vencimento <= ?";
            $params[] = $filters['data_vencimento_fim'];
        }
        
        // Filtro por rateio
        if (isset($filters['tem_rateio']) && $filters['tem_rateio'] !== '') {
            $sql .= " AND cp.tem_rateio = ?";
            $params[] = $filters['tem_rateio'];
        }
        
        // Busca por descrição ou número de documento
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (cp.descricao LIKE ? OR cp.numero_documento LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY cp.data_vencimento DESC, cp.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conta a pagar por ID
     */
    public function findById($id)
    {
        $sql = "SELECT cp.*, 
                       e.nome_fantasia as empresa_nome,
                       f.nome_razao_social as fornecedor_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome,
                       u.nome as usuario_cadastro_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
                JOIN categorias_financeiras c ON cp.categoria_id = c.id
                LEFT JOIN centros_custo cc ON cp.centro_custo_id = cc.id
                LEFT JOIN contas_bancarias cb ON cp.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON cp.forma_pagamento_id = fp.id
                JOIN usuarios u ON cp.usuario_cadastro_id = u.id
                WHERE cp.id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conta a pagar
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, fornecedor_id, categoria_id, centro_custo_id, numero_documento,
                 descricao, valor_total, valor_pago, data_emissao, data_competencia,
                 data_vencimento, data_pagamento, status, forma_pagamento_id,
                 conta_bancaria_id, tem_rateio, observacoes, usuario_cadastro_id) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            $data['empresa_id'],
            $data['fornecedor_id'] ?? null,
            $data['categoria_id'],
            $data['centro_custo_id'] ?? null,
            $data['numero_documento'],
            $data['descricao'],
            $data['valor_total'],
            $data['valor_pago'] ?? 0,
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            $data['data_pagamento'] ?? null,
            $data['status'] ?? 'pendente',
            $data['forma_pagamento_id'] ?? null,
            $data['conta_bancaria_id'] ?? null,
            $data['tem_rateio'] ?? 0,
            $data['observacoes'] ?? null,
            $data['usuario_cadastro_id']
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma conta a pagar
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = ?,
                fornecedor_id = ?,
                categoria_id = ?,
                centro_custo_id = ?,
                numero_documento = ?,
                descricao = ?,
                valor_total = ?,
                data_emissao = ?,
                data_competencia = ?,
                data_vencimento = ?,
                forma_pagamento_id = ?,
                conta_bancaria_id = ?,
                observacoes = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['empresa_id'],
            $data['fornecedor_id'] ?? null,
            $data['categoria_id'],
            $data['centro_custo_id'] ?? null,
            $data['numero_documento'],
            $data['descricao'],
            $data['valor_total'],
            $data['data_emissao'],
            $data['data_competencia'],
            $data['data_vencimento'],
            $data['forma_pagamento_id'] ?? null,
            $data['conta_bancaria_id'] ?? null,
            $data['observacoes'] ?? null,
            $id
        ]);
    }
    
    /**
     * Atualiza status e valor pago da conta
     */
    public function atualizarPagamento($id, $valorPago, $dataPagamento, $status)
    {
        $sql = "UPDATE {$this->table} SET 
                valor_pago = ?,
                data_pagamento = ?,
                status = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$valorPago, $dataPagamento, $status, $id]);
    }
    
    /**
     * Cancela uma conta a pagar
     */
    public function cancelar($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelado' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Retorna contas vencidas
     */
    public function findVencidas($empresasIds = [])
    {
        $sql = "SELECT cp.*, e.nome_fantasia as empresa_nome
                FROM {$this->table} cp
                JOIN empresas e ON cp.empresa_id = e.id
                WHERE cp.status IN ('pendente', 'parcial')
                  AND cp.data_vencimento < CURDATE()";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND cp.empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " ORDER BY cp.data_vencimento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna total a pagar por status
     */
    public function getTotalPorStatus($empresasIds = [])
    {
        $sql = "SELECT status, SUM(valor_total - valor_pago) as total
                FROM {$this->table}
                WHERE status IN ('pendente', 'parcial', 'vencido')";
        
        $params = [];
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND empresa_id IN ({$placeholders})";
            $params = $empresasIds;
        }
        
        $sql .= " GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    
    /**
     * Atualiza flag de rateio
     */
    public function atualizarRateio($id, $temRateio)
    {
        $sql = "UPDATE {$this->table} SET tem_rateio = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$temRateio, $id]);
    }
}
