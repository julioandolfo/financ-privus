<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Conciliação Bancária
 */
class ConciliacaoBancaria extends Model
{
    protected $table = 'conciliacao_bancaria';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todas as conciliações
     */
    public function findAll($empresaId = null, $filters = [])
    {
        $sql = "SELECT c.*, 
                       cb.banco, cb.agencia, cb.conta, cb.descricao as conta_descricao,
                       e.razao_social as empresa_nome
                FROM {$this->table} c
                INNER JOIN contas_bancarias cb ON c.conta_bancaria_id = cb.id
                INNER JOIN empresas e ON c.empresa_id = e.id
                WHERE 1=1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND c.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        // Filtro por conta bancária
        if (!empty($filters['conta_bancaria_id'])) {
            $sql .= " AND c.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filters['conta_bancaria_id'];
        }
        
        // Filtro por status
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }
        
        // Filtro por período
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND c.data_inicio >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND c.data_fim <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        $sql .= " ORDER BY c.data_cadastro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar conciliação por ID
     */
    public function findById($id)
    {
        $sql = "SELECT c.*, 
                       cb.banco, cb.agencia, cb.conta, cb.descricao as conta_descricao, cb.saldo as saldo_atual,
                       e.razao_social as empresa_nome
                FROM {$this->table} c
                INNER JOIN contas_bancarias cb ON c.conta_bancaria_id = cb.id
                INNER JOIN empresas e ON c.empresa_id = e.id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar nova conciliação
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, conta_bancaria_id, data_inicio, data_fim, saldo_extrato, saldo_sistema, diferenca, status, observacoes) 
                VALUES 
                (:empresa_id, :conta_bancaria_id, :data_inicio, :data_fim, :saldo_extrato, :saldo_sistema, :diferenca, :status, :observacoes)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'conta_bancaria_id' => $data['conta_bancaria_id'],
            'data_inicio' => $data['data_inicio'],
            'data_fim' => $data['data_fim'],
            'saldo_extrato' => $data['saldo_extrato'],
            'saldo_sistema' => $data['saldo_sistema'],
            'diferenca' => $data['diferenca'],
            'status' => $data['status'] ?? 'aberta',
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar conciliação
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                saldo_extrato = :saldo_extrato,
                saldo_sistema = :saldo_sistema,
                diferenca = :diferenca,
                status = :status,
                observacoes = :observacoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'saldo_extrato' => $data['saldo_extrato'],
            'saldo_sistema' => $data['saldo_sistema'],
            'diferenca' => $data['diferenca'],
            'status' => $data['status'],
            'observacoes' => $data['observacoes'] ?? null
        ]);
    }
    
    /**
     * Deletar conciliação
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Calcular saldo do sistema no período
     */
    public function calcularSaldoSistema($contaBancariaId, $dataInicio, $dataFim)
    {
        $sql = "SELECT 
                    cb.saldo_inicial,
                    COALESCE(SUM(CASE WHEN mc.tipo = 'entrada' THEN mc.valor ELSE 0 END), 0) as total_entradas,
                    COALESCE(SUM(CASE WHEN mc.tipo = 'saida' THEN mc.valor ELSE 0 END), 0) as total_saidas
                FROM contas_bancarias cb
                LEFT JOIN movimentacoes_caixa mc ON mc.conta_bancaria_id = cb.id 
                    AND mc.data_movimentacao BETWEEN :data_inicio AND :data_fim
                WHERE cb.id = :conta_bancaria_id
                GROUP BY cb.id, cb.saldo_inicial";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conta_bancaria_id' => $contaBancariaId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resultado) {
            return 0;
        }
        
        $saldoFinal = $resultado['saldo_inicial'] + $resultado['total_entradas'] - $resultado['total_saidas'];
        
        return $saldoFinal;
    }
    
    /**
     * Buscar movimentações não conciliadas
     */
    public function getMovimentacoesNaoConciliadas($contaBancariaId, $dataInicio, $dataFim)
    {
        $sql = "SELECT mc.*,
                       CASE 
                           WHEN mc.conta_pagar_id IS NOT NULL THEN CONCAT('Pagamento - ', cp.descricao)
                           WHEN mc.conta_receber_id IS NOT NULL THEN CONCAT('Recebimento - ', cr.descricao)
                           ELSE mc.descricao
                       END as descricao_completa,
                       fp.nome as forma_pagamento_nome
                FROM movimentacoes_caixa mc
                LEFT JOIN contas_pagar cp ON mc.conta_pagar_id = cp.id
                LEFT JOIN contas_receber cr ON mc.conta_receber_id = cr.id
                LEFT JOIN formas_pagamento fp ON mc.forma_pagamento_id = fp.id
                WHERE mc.conta_bancaria_id = :conta_bancaria_id
                    AND mc.data_movimentacao BETWEEN :data_inicio AND :data_fim
                    AND mc.conciliado = 0
                ORDER BY mc.data_movimentacao ASC, mc.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conta_bancaria_id' => $contaBancariaId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Fechar conciliação
     */
    public function fechar($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'fechada' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Reabrir conciliação
     */
    public function reabrir($id)
    {
        $sql = "UPDATE {$this->table} SET status = 'aberta' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Métricas para dashboard
     */
    public function getResumo($empresasIds = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'aberta' THEN 1 ELSE 0 END) as abertas,
                    SUM(CASE WHEN status = 'fechada' THEN 1 ELSE 0 END) as fechadas,
                    SUM(ABS(diferenca)) as total_diferencas
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if ($empresasIds && is_array($empresasIds) && count($empresasIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $sql .= " AND empresa_id IN ($placeholders)";
            $params = $empresasIds;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
