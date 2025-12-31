<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Rateios de Pagamentos entre Empresas
 */
class RateioPagamento extends Model
{
    protected $table = 'rateios_pagamentos';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os rateios de uma conta a pagar
     */
    public function findByContaPagar($contaPagarId)
    {
        $sql = "SELECT rp.*, e.nome_fantasia as empresa_nome
                FROM {$this->table} rp
                JOIN empresas e ON rp.empresa_id = e.id
                WHERE rp.conta_pagar_id = ?
                ORDER BY rp.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contaPagarId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Cria um novo rateio
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (conta_pagar_id, empresa_id, valor_rateio, percentual, 
                 data_competencia, observacoes, usuario_cadastro_id) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            $data['conta_pagar_id'],
            $data['empresa_id'],
            $data['valor_rateio'],
            $data['percentual'],
            $data['data_competencia'],
            $data['observacoes'] ?? null,
            $data['usuario_cadastro_id']
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Salva múltiplos rateios de uma vez
     */
    public function saveBatch($contaPagarId, $rateios, $usuarioId)
    {
        try {
            $this->db->beginTransaction();
            
            // Remove rateios antigos
            $this->deleteByContaPagar($contaPagarId);
            
            // Insere novos rateios
            foreach ($rateios as $rateio) {
                $rateio['conta_pagar_id'] = $contaPagarId;
                $rateio['usuario_cadastro_id'] = $usuarioId;
                $this->create($rateio);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar rateios: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove todos os rateios de uma conta a pagar
     */
    public function deleteByContaPagar($contaPagarId)
    {
        $sql = "DELETE FROM {$this->table} WHERE conta_pagar_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contaPagarId]);
    }
    
    /**
     * Retorna rateios por empresa (para relatórios consolidados)
     */
    public function findByEmpresa($empresaId, $dataInicio = null, $dataFim = null)
    {
        $sql = "SELECT rp.*, cp.descricao, cp.numero_documento, cp.valor_total
                FROM {$this->table} rp
                JOIN contas_pagar cp ON rp.conta_pagar_id = cp.id
                WHERE rp.empresa_id = ?";
        $params = [$empresaId];
        
        if ($dataInicio) {
            $sql .= " AND rp.data_competencia >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND rp.data_competencia <= ?";
            $params[] = $dataFim;
        }
        
        $sql .= " ORDER BY rp.data_competencia DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna total rateado por empresa em um período
     */
    public function getTotalByEmpresa($empresaId, $dataInicio = null, $dataFim = null)
    {
        $sql = "SELECT SUM(rp.valor_rateio) as total
                FROM {$this->table} rp
                WHERE rp.empresa_id = ?";
        $params = [$empresaId];
        
        if ($dataInicio) {
            $sql .= " AND rp.data_competencia >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND rp.data_competencia <= ?";
            $params[] = $dataFim;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
