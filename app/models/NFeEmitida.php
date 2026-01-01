<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class NFeEmitida extends Model
{
    protected $table = 'nfes_emitidas';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca todas as NF-es por empresa
     */
    public function findAll($empresaId, $filters = [])
    {
        $sql = "SELECT n.*, p.numero_pedido, p.cliente_nome as pedido_cliente
                FROM {$this->table} n
                LEFT JOIN pedidos_vinculados p ON n.pedido_id = p.id
                WHERE n.empresa_id = :empresa_id";
        
        $params = ['empresa_id' => $empresaId];
        
        if (!empty($filters['status'])) {
            $sql .= " AND n.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND DATE(n.data_emissao) >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND DATE(n.data_emissao) <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        $sql .= " ORDER BY n.data_emissao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca NF-e por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca NF-e por UUID da WebmaniaBR
     */
    public function findByUuid($uuid)
    {
        $sql = "SELECT * FROM {$this->table} WHERE uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uuid' => $uuid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca NF-e por chave de acesso
     */
    public function findByChave($chaveNfe)
    {
        $sql = "SELECT * FROM {$this->table} WHERE chave_nfe = :chave_nfe";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['chave_nfe' => $chaveNfe]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca NF-es por pedido
     */
    public function findByPedido($pedidoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE pedido_id = :pedido_id ORDER BY data_emissao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pedido_id' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Cria uma nova NF-e
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    empresa_id, pedido_id, integracao_id, uuid, chave_nfe,
                    numero_nfe, serie_nfe, modelo, status, data_emissao,
                    valor_total, cliente_nome, cliente_documento
                ) VALUES (
                    :empresa_id, :pedido_id, :integracao_id, :uuid, :chave_nfe,
                    :numero_nfe, :serie_nfe, :modelo, :status, :data_emissao,
                    :valor_total, :cliente_nome, :cliente_documento
                )";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza status da NF-e
     */
    public function updateStatus($id, $status, $motivo = null, $dadosAdicionais = [])
    {
        $sql = "UPDATE {$this->table} SET
                    status = :status,
                    motivo_status = :motivo_status,
                    protocolo = :protocolo,
                    data_autorizacao = :data_autorizacao,
                    xml_nfe = :xml_nfe,
                    danfe_url = :danfe_url,
                    xml_url = :xml_url,
                    updated_at = NOW()
                WHERE id = :id";
        
        $params = [
            'id' => $id,
            'status' => $status,
            'motivo_status' => $motivo,
            'protocolo' => $dadosAdicionais['protocolo'] ?? null,
            'data_autorizacao' => $dadosAdicionais['data_autorizacao'] ?? null,
            'xml_nfe' => $dadosAdicionais['xml_nfe'] ?? null,
            'danfe_url' => $dadosAdicionais['danfe_url'] ?? null,
            'xml_url' => $dadosAdicionais['xml_url'] ?? null
        ];
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Estatísticas de NF-es
     */
    public function getEstatisticas($empresaId)
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'autorizada' THEN 1 END) as autorizadas,
                    COUNT(CASE WHEN status = 'aguardando' THEN 1 END) as aguardando,
                    COUNT(CASE WHEN status = 'rejeitada' THEN 1 END) as rejeitadas,
                    COUNT(CASE WHEN status = 'cancelada' THEN 1 END) as canceladas,
                    SUM(valor_total) as valor_total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
