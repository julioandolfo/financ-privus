<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Contas Bancárias
 */
class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as contas bancárias
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY banco_nome ASC, agencia ASC, conta ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conta bancária por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna conta bancária por agência e conta
     */
    public function findByAgenciaConta($agencia, $conta, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE agencia = :agencia AND conta = :conta";
        $params = [
            'agencia' => $agencia,
            'conta' => $conta
        ];
        
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
     * Cria uma nova conta bancária
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, banco_codigo, banco_nome, agencia, conta, tipo_conta, saldo_inicial, saldo_atual, ativo) 
                VALUES 
                (:empresa_id, :banco_codigo, :banco_nome, :agencia, :conta, :tipo_conta, :saldo_inicial, :saldo_atual, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        $saldoInicial = isset($data['saldo_inicial']) ? (float)$data['saldo_inicial'] : 0;
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'banco_codigo' => $data['banco_codigo'],
            'banco_nome' => $data['banco_nome'],
            'agencia' => $data['agencia'],
            'conta' => $data['conta'],
            'tipo_conta' => $data['tipo_conta'] ?? 'corrente',
            'saldo_inicial' => $saldoInicial,
            'saldo_atual' => $saldoInicial, // Saldo atual começa igual ao inicial
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma conta bancária
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                banco_codigo = :banco_codigo,
                banco_nome = :banco_nome,
                agencia = :agencia,
                conta = :conta,
                tipo_conta = :tipo_conta,
                ativo = :ativo";
        
        $params = [
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'banco_codigo' => $data['banco_codigo'],
            'banco_nome' => $data['banco_nome'],
            'agencia' => $data['agencia'],
            'conta' => $data['conta'],
            'tipo_conta' => $data['tipo_conta'] ?? 'corrente',
            'ativo' => $data['ativo'] ?? 1
        ];
        
        // Atualiza saldo inicial se fornecido
        if (isset($data['saldo_inicial'])) {
            $sql .= ", saldo_inicial = :saldo_inicial";
            $params['saldo_inicial'] = (float)$data['saldo_inicial'];
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Atualiza o saldo atual da conta
     */
    public function atualizarSaldo($id, $valor, $operacao = 'adicionar')
    {
        if ($operacao === 'adicionar') {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual + :valor WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual - :valor WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'valor' => abs((float)$valor)
        ]);
    }
    
    /**
     * Exclui uma conta bancária (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna o saldo total de todas as contas de uma empresa
     */
    public function getSaldoTotalEmpresa($empresaId)
    {
        $sql = "SELECT SUM(saldo_atual) as saldo_total 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['saldo_total'] ?? 0;
    }
}
