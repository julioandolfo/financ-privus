<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Formas de Pagamento
 */
class FormaPagamento extends Model
{
    protected $table = 'formas_pagamento';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as formas de pagamento
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY codigo ASC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna um forma de pagamento por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca forma de pagamento por nome ou código (match exato ou contém)
     * Útil para vincular formas do WooCommerce (ex: "PIX" -> forma "PIX")
     */
    public function findByNomeOuCodigo($termo, $empresaId)
    {
        if (empty($termo)) return null;
        
        $termo = trim($termo);
        $like = '%' . $termo . '%';
        $exato = strtolower($termo);
        
        // 1. Match exato em nome ou código
        $sql = "SELECT id FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND ativo = 1 
                AND (LOWER(nome) = :exato OR LOWER(COALESCE(codigo,'')) = :exato2) 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'exato' => $exato,
            'exato2' => $exato
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int)$row['id'];
        
        // 2. Match LIKE (contém) em nome ou código
        $sql2 = "SELECT id FROM {$this->table} 
                 WHERE empresa_id = :empresa_id AND ativo = 1 
                 AND (LOWER(nome) LIKE :like OR LOWER(COALESCE(codigo,'')) LIKE :like2) 
                 ORDER BY LENGTH(nome) ASC 
                 LIMIT 1";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([
            'empresa_id' => $empresaId,
            'like' => strtolower($like),
            'like2' => strtolower($like)
        ]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        return $row2 ? (int)$row2['id'] : null;
    }
    
    /**
     * Retorna forma de pagamento por código
     */
    public function findByCodigo($codigo, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo";
        $params = ['codigo' => $codigo];
        
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
     * Cria uma nova forma de pagamento
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, codigo, nome, tipo, ativo) 
                VALUES 
                (:empresa_id, :codigo, :nome, :tipo, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'] ?? 'ambos',
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma forma de pagamento
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                codigo = :codigo,
                nome = :nome,
                tipo = :tipo,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'] ?? 'ambos',
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui uma forma de pagamento (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}

