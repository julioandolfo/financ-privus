<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Itens de Conciliação Bancária
 */
class ConciliacaoItem extends Model
{
    protected $table = 'conciliacao_itens';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar itens por conciliação
     */
    public function findByConciliacao($conciliacaoId)
    {
        $sql = "SELECT ci.*,
                       mc.descricao as movimentacao_descricao,
                       mc.valor as movimentacao_valor,
                       mc.data_movimentacao,
                       fp.nome as forma_pagamento_nome
                FROM {$this->table} ci
                LEFT JOIN movimentacoes_caixa mc ON ci.movimentacao_id = mc.id
                LEFT JOIN formas_pagamento fp ON ci.forma_pagamento_sugerida_id = fp.id
                WHERE ci.conciliacao_id = :conciliacao_id
                ORDER BY ci.data_extrato ASC, ci.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Criar item de conciliação
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (conciliacao_id, movimentacao_id, descricao_extrato, valor_extrato, data_extrato, tipo_extrato, vinculado, forma_pagamento_sugerida_id, observacoes) 
                VALUES 
                (:conciliacao_id, :movimentacao_id, :descricao_extrato, :valor_extrato, :data_extrato, :tipo_extrato, :vinculado, :forma_pagamento_sugerida_id, :observacoes)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'conciliacao_id' => $data['conciliacao_id'],
            'movimentacao_id' => $data['movimentacao_id'] ?? null,
            'descricao_extrato' => $data['descricao_extrato'],
            'valor_extrato' => $data['valor_extrato'],
            'data_extrato' => $data['data_extrato'],
            'tipo_extrato' => $data['tipo_extrato'],
            'vinculado' => $data['vinculado'] ?? 0,
            'forma_pagamento_sugerida_id' => $data['forma_pagamento_sugerida_id'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualizar item
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                movimentacao_id = :movimentacao_id,
                descricao_extrato = :descricao_extrato,
                valor_extrato = :valor_extrato,
                data_extrato = :data_extrato,
                tipo_extrato = :tipo_extrato,
                vinculado = :vinculado,
                forma_pagamento_sugerida_id = :forma_pagamento_sugerida_id,
                observacoes = :observacoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'movimentacao_id' => $data['movimentacao_id'] ?? null,
            'descricao_extrato' => $data['descricao_extrato'],
            'valor_extrato' => $data['valor_extrato'],
            'data_extrato' => $data['data_extrato'],
            'tipo_extrato' => $data['tipo_extrato'],
            'vinculado' => $data['vinculado'] ?? 0,
            'forma_pagamento_sugerida_id' => $data['forma_pagamento_sugerida_id'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
    }
    
    /**
     * Vincular item a uma movimentação
     */
    public function vincular($id, $movimentacaoId)
    {
        $sql = "UPDATE {$this->table} SET 
                movimentacao_id = :movimentacao_id, 
                vinculado = 1 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'movimentacao_id' => $movimentacaoId
        ]);
    }
    
    /**
     * Desvincular item
     */
    public function desvincular($id)
    {
        $sql = "UPDATE {$this->table} SET 
                movimentacao_id = NULL, 
                vinculado = 0 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Deletar item
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Deletar todos os itens de uma conciliação
     */
    public function deleteByConciliacao($conciliacaoId)
    {
        $sql = "DELETE FROM {$this->table} WHERE conciliacao_id = :conciliacao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['conciliacao_id' => $conciliacaoId]);
    }
    
    /**
     * Buscar itens não vinculados de uma conciliação
     */
    public function getNaoVinculados($conciliacaoId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE conciliacao_id = :conciliacao_id
                    AND vinculado = 0
                ORDER BY data_extrato ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar itens vinculados de uma conciliação
     */
    public function getVinculados($conciliacaoId)
    {
        $sql = "SELECT ci.*,
                       mc.descricao as movimentacao_descricao,
                       mc.valor as movimentacao_valor
                FROM {$this->table} ci
                INNER JOIN movimentacoes_caixa mc ON ci.movimentacao_id = mc.id
                WHERE ci.conciliacao_id = :conciliacao_id
                    AND ci.vinculado = 1
                ORDER BY ci.data_extrato ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Contar itens vinculados vs não vinculados
     */
    public function getEstatisticas($conciliacaoId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN vinculado = 1 THEN 1 ELSE 0 END) as vinculados,
                    SUM(CASE WHEN vinculado = 0 THEN 1 ELSE 0 END) as nao_vinculados,
                    SUM(CASE WHEN tipo_extrato = 'credito' THEN valor_extrato ELSE 0 END) as total_creditos,
                    SUM(CASE WHEN tipo_extrato = 'debito' THEN valor_extrato ELSE 0 END) as total_debitos
                FROM {$this->table}
                WHERE conciliacao_id = :conciliacao_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conciliacao_id' => $conciliacaoId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
