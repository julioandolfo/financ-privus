<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para armazenar metadados do WooCommerce (status, formas de pagamento, categorias)
 */
class WooCommerceMetadata extends Model
{
    protected $table = 'woocommerce_metadata';
    protected $db;
    
    const TIPO_STATUS = 'status';
    const TIPO_PAYMENT_GATEWAY = 'payment_gateway';
    const TIPO_CATEGORIA = 'categoria';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria ou atualiza metadata
     */
    public function createOrUpdate($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, tipo, chave, nome, dados_extras, ativo) 
                VALUES 
                (:integracao_id, :tipo, :chave, :nome, :dados_extras, :ativo)
                ON DUPLICATE KEY UPDATE
                nome = VALUES(nome),
                dados_extras = VALUES(dados_extras),
                ativo = VALUES(ativo),
                atualizado_em = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'tipo' => $data['tipo'],
            'chave' => $data['chave'],
            'nome' => $data['nome'],
            'dados_extras' => isset($data['dados_extras']) ? json_encode($data['dados_extras']) : null,
            'ativo' => $data['ativo'] ?? true
        ]);
    }
    
    /**
     * Busca metadados por tipo
     */
    public function findByTipo($integracaoId, $tipo, $apenasAtivos = true)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE integracao_id = :integracao_id AND tipo = :tipo";
        
        if ($apenasAtivos) {
            $sql .= " AND ativo = 1";
        }
        
        $sql .= " ORDER BY nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo
        ]);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decodifica JSON
        foreach ($resultados as &$item) {
            if ($item['dados_extras']) {
                $item['dados_extras'] = json_decode($item['dados_extras'], true);
            }
        }
        
        return $resultados;
    }
    
    /**
     * Busca por chave
     */
    public function findByChave($integracaoId, $tipo, $chave)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE integracao_id = :integracao_id 
                AND tipo = :tipo 
                AND chave = :chave 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo,
            'chave' => $chave
        ]);
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item && $item['dados_extras']) {
            $item['dados_extras'] = json_decode($item['dados_extras'], true);
        }
        
        return $item ?: null;
    }
    
    /**
     * Limpa metadados antigos
     */
    public function limparPorTipo($integracaoId, $tipo)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE integracao_id = :integracao_id AND tipo = :tipo";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo
        ]);
    }
}
