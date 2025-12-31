<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Perfis de Consolidação
 */
class PerfilConsolidacao extends Model
{
    protected $table = 'perfis_consolidacao';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os perfis
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT p.*,
                       u.nome as usuario_nome
                FROM {$this->table} p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.ativo = 1";
        $params = [];
        
        if (isset($filters['usuario_id']) && $filters['usuario_id'] !== '') {
            $sql .= " AND (p.usuario_id = :usuario_id OR p.usuario_id IS NULL)";
            $params['usuario_id'] = $filters['usuario_id'];
        }
        
        $sql .= " ORDER BY p.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna um perfil por ID
     */
    public function findById($id)
    {
        $sql = "SELECT p.*,
                       u.nome as usuario_nome
                FROM {$this->table} p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria um novo perfil
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (usuario_id, nome, empresas_ids, ativo) 
                VALUES 
                (:usuario_id, :nome, :empresas_ids, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'nome' => $data['nome'],
            'empresas_ids' => json_encode($data['empresas_ids']),
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza um perfil
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome = :nome,
                empresas_ids = :empresas_ids,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'empresas_ids' => json_encode($data['empresas_ids']),
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui um perfil (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna perfis do usuário logado
     */
    public function findByUsuario($usuarioId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = :usuario_id AND ativo = 1
                ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna perfis compartilhados (usuário_id NULL)
     */
    public function findCompartilhados()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id IS NULL AND ativo = 1
                ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Decodifica IDs das empresas do perfil
     */
    public function getEmpresasIds($perfil)
    {
        if (isset($perfil['empresas_ids'])) {
            return json_decode($perfil['empresas_ids'], true);
        }
        return [];
    }
    
    /**
     * Verifica se perfil pertence ao usuário ou é compartilhado
     */
    public function podeAcessar($perfilId, $usuarioId)
    {
        $perfil = $this->findById($perfilId);
        if (!$perfil) {
            return false;
        }
        
        return $perfil['usuario_id'] === null || $perfil['usuario_id'] == $usuarioId;
    }
}
