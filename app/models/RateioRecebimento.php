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
     * Salva múltiplos rateios
     */
    public function saveBatch($contaReceberId, $rateios, $usuarioId)
    {
        // Remove rateios existentes
        $this->deleteByContaReceber($contaReceberId);
        
        // Insere novos rateios
        $sql = "INSERT INTO {$this->table} 
                (conta_receber_id, empresa_id, valor_rateio, percentual, data_competencia, usuario_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($rateios as $rateio) {
            $stmt->execute([
                $contaReceberId,
                $rateio['empresa_id'],
                $rateio['valor_rateio'],
                $rateio['percentual'],
                $rateio['data_competencia'],
                $usuarioId
            ]);
        }
        
        return true;
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
