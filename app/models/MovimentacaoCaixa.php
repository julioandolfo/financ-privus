<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Movimentações de Caixa
 */
class MovimentacaoCaixa extends Model
{
    protected $table = 'movimentacoes_caixa';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as movimentações
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT m.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome
                FROM {$this->table} m
                JOIN empresas e ON m.empresa_id = e.id
                JOIN categorias_financeiras c ON m.categoria_id = c.id
                LEFT JOIN centros_custo cc ON m.centro_custo_id = cc.id
                JOIN contas_bancarias cb ON m.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON m.forma_pagamento_id = fp.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $sql .= " AND m.empresa_id = :empresa_id";
            $params['empresa_id'] = $filters['empresa_id'];
        }
        
        if (isset($filters['empresas_ids']) && is_array($filters['empresas_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['empresas_ids']), '?'));
            $sql .= " AND m.empresa_id IN ({$placeholders})";
            $params = array_merge($params, $filters['empresas_ids']);
        }
        
        if (isset($filters['tipo']) && $filters['tipo'] !== '') {
            $sql .= " AND m.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        
        if (isset($filters['conta_bancaria_id']) && $filters['conta_bancaria_id'] !== '') {
            $sql .= " AND m.conta_bancaria_id = :conta_bancaria_id";
            $params['conta_bancaria_id'] = $filters['conta_bancaria_id'];
        }
        
        if (isset($filters['data_inicio']) && $filters['data_inicio'] !== '') {
            $sql .= " AND m.data_movimentacao >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (isset($filters['data_fim']) && $filters['data_fim'] !== '') {
            $sql .= " AND m.data_movimentacao <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        if (isset($filters['conciliado']) && $filters['conciliado'] !== '') {
            $sql .= " AND m.conciliado = :conciliado";
            $params['conciliado'] = $filters['conciliado'];
        }
        
        $sql .= " ORDER BY m.data_movimentacao DESC, m.id DESC";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros nomeados e posicionais
        $posicao = 1;
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $stmt->bindValue($posicao++, $value);
            } else {
                $stmt->bindValue(":{$key}", $value);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma movimentação por ID
     */
    public function findById($id)
    {
        $sql = "SELECT m.*, 
                       e.nome_fantasia as empresa_nome,
                       c.nome as categoria_nome,
                       cc.nome as centro_custo_nome,
                       cb.banco_nome,
                       fp.nome as forma_pagamento_nome
                FROM {$this->table} m
                JOIN empresas e ON m.empresa_id = e.id
                JOIN categorias_financeiras c ON m.categoria_id = c.id
                LEFT JOIN centros_custo cc ON m.centro_custo_id = cc.id
                JOIN contas_bancarias cb ON m.conta_bancaria_id = cb.id
                LEFT JOIN formas_pagamento fp ON m.forma_pagamento_id = fp.id
                WHERE m.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova movimentação
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, tipo, categoria_id, centro_custo_id, conta_bancaria_id, 
                 descricao, valor, data_movimentacao, data_competencia, conciliado, 
                 conciliacao_id, referencia_id, referencia_tipo, forma_pagamento_id, 
                 origem_movimento, observacoes) 
                VALUES 
                (:empresa_id, :tipo, :categoria_id, :centro_custo_id, :conta_bancaria_id,
                 :descricao, :valor, :data_movimentacao, :data_competencia, :conciliado,
                 :conciliacao_id, :referencia_id, :referencia_tipo, :forma_pagamento_id,
                 :origem_movimento, :observacoes)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'tipo' => $data['tipo'],
            'categoria_id' => $data['categoria_id'],
            'centro_custo_id' => $data['centro_custo_id'] ?? null,
            'conta_bancaria_id' => $data['conta_bancaria_id'],
            'descricao' => $data['descricao'],
            'valor' => $data['valor'],
            'data_movimentacao' => $data['data_movimentacao'],
            'data_competencia' => $data['data_competencia'] ?? null,
            'conciliado' => $data['conciliado'] ?? 0,
            'conciliacao_id' => $data['conciliacao_id'] ?? null,
            'referencia_id' => $data['referencia_id'] ?? null,
            'referencia_tipo' => $data['referencia_tipo'] ?? null,
            'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
            'origem_movimento' => $data['origem_movimento'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma movimentação
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                tipo = :tipo,
                categoria_id = :categoria_id,
                centro_custo_id = :centro_custo_id,
                conta_bancaria_id = :conta_bancaria_id,
                descricao = :descricao,
                valor = :valor,
                data_movimentacao = :data_movimentacao,
                data_competencia = :data_competencia,
                forma_pagamento_id = :forma_pagamento_id,
                origem_movimento = :origem_movimento,
                observacoes = :observacoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'tipo' => $data['tipo'],
            'categoria_id' => $data['categoria_id'],
            'centro_custo_id' => $data['centro_custo_id'] ?? null,
            'conta_bancaria_id' => $data['conta_bancaria_id'],
            'descricao' => $data['descricao'],
            'valor' => $data['valor'],
            'data_movimentacao' => $data['data_movimentacao'],
            'data_competencia' => $data['data_competencia'] ?? null,
            'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
            'origem_movimento' => $data['origem_movimento'] ?? null,
            'observacoes' => $data['observacoes'] ?? null
        ]);
    }
    
    /**
     * Exclui uma movimentação
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Marca movimentação como conciliada
     */
    public function conciliar($id, $conciliacaoId)
    {
        $sql = "UPDATE {$this->table} SET conciliado = 1, conciliacao_id = :conciliacao_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'conciliacao_id' => $conciliacaoId]);
    }
    
    /**
     * Remove conciliação de uma movimentação
     */
    public function desconciliar($id)
    {
        $sql = "UPDATE {$this->table} SET conciliado = 0, conciliacao_id = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Marcar movimentação como conciliada (alias para conciliar)
     */
    public function marcarComoConciliada($id, $conciliacaoId)
    {
        return $this->conciliar($id, $conciliacaoId);
    }
    
    /**
     * Desmarcar conciliação (alias para desconciliar)
     */
    public function desmarcarConciliacao($id)
    {
        return $this->desconciliar($id);
    }
    
    /**
     * Retorna movimentações por referência (conta pagar/receber)
     */
    public function findByReferencia($referenciaId, $referenciaTipo)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE referencia_id = :referencia_id AND referencia_tipo = :referencia_tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'referencia_id' => $referenciaId,
            'referencia_tipo' => $referenciaTipo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna saldo consolidado por período
     */
    public function getSaldoPorPeriodo($empresasIds, $dataInicio, $dataFim)
    {
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        
        $sql = "SELECT 
                    DATE(data_movimentacao) as data,
                    SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                    SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
                FROM {$this->table}
                WHERE empresa_id IN ({$placeholders})
                  AND data_movimentacao BETWEEN ? AND ?
                GROUP BY DATE(data_movimentacao)
                ORDER BY data";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($empresasIds, [$dataInicio, $dataFim]);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna soma por tipo
     */
    public function getSomaByTipo($empresaId, $dataInicio, $dataFim, $tipo)
    {
        $sql = "SELECT COALESCE(SUM(valor), 0) as total
                FROM {$this->table}
                WHERE data_movimentacao BETWEEN :data_inicio AND :data_fim
                AND tipo = :tipo";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'tipo' => $tipo
        ];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Retorna saldo inicial antes de uma data
     */
    public function getSaldoInicial($empresaId, $data)
    {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0) as saldo
                FROM {$this->table}
                WHERE data_movimentacao < :data";
        
        $params = ['data' => $data];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['saldo'] ?? 0;
    }
}
