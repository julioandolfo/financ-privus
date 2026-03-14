<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Rateios de Recebimentos
 */
class RateioRecebimento extends Model
{
    protected $table = 'rateios_recebimentos';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os rateios de uma conta a receber
     */
    public function findByContaReceber($contaReceberId)
    {
        $sql = "SELECT rr.*, e.nome_fantasia as empresa_nome
                FROM {$this->table} rr
                JOIN empresas e ON rr.empresa_id = e.id
                WHERE rr.conta_receber_id = ?
                ORDER BY rr.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contaReceberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Cria um novo rateio
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (conta_receber_id, empresa_id, valor_rateio, percentual, 
                 data_competencia, observacoes, usuario_cadastro_id) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            $data['conta_receber_id'],
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
    public function saveBatch($contaReceberId, $rateios, $usuarioId)
    {
        try {
            $this->db->beginTransaction();
            
            $this->deleteByContaReceber($contaReceberId);
            
            foreach ($rateios as $rateio) {
                $rateio['conta_receber_id'] = $contaReceberId;
                $rateio['usuario_cadastro_id'] = $usuarioId;
                $this->create($rateio);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar rateios recebimento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove todos os rateios de uma conta a receber
     */
    public function deleteByContaReceber($contaReceberId)
    {
        $sql = "DELETE FROM {$this->table} WHERE conta_receber_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contaReceberId]);
    }
    
    /**
     * Retorna total de rateios de uma conta
     */
    public function getTotalRateado($contaReceberId)
    {
        $sql = "SELECT SUM(valor_rateio) as total FROM {$this->table} WHERE conta_receber_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contaReceberId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
