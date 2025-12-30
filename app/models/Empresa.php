<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Empresas
 */
class Empresa extends Model
{
    protected $table = 'empresas';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as empresas ativas
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $sql .= " AND ativo = :ativo";
            $params['ativo'] = $filters['ativo'];
        }
        
        if (isset($filters['grupo_empresarial_id']) && $filters['grupo_empresarial_id'] !== '') {
            $sql .= " AND grupo_empresarial_id = :grupo_empresarial_id";
            $params['grupo_empresarial_id'] = $filters['grupo_empresarial_id'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (razao_social LIKE :search OR nome_fantasia LIKE :search OR codigo LIKE :search OR cnpj LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY razao_social ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna uma empresa por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna uma empresa por código
     */
    public function findByCodigo($codigo)
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna uma empresa por CNPJ
     */
    public function findByCnpj($cnpj)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cnpj = :cnpj";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cnpj' => $cnpj]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova empresa
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (codigo, razao_social, nome_fantasia, cnpj, grupo_empresarial_id, ativo, configuracoes) 
                VALUES 
                (:codigo, :razao_social, :nome_fantasia, :cnpj, :grupo_empresarial_id, :ativo, :configuracoes)";
        
        $stmt = $this->db->prepare($sql);
        
        $configuracoes = isset($data['configuracoes']) ? json_encode($data['configuracoes']) : null;
        
        $stmt->execute([
            'codigo' => $data['codigo'],
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'] ?? null,
            'grupo_empresarial_id' => $data['grupo_empresarial_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'configuracoes' => $configuracoes
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza uma empresa
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                codigo = :codigo,
                razao_social = :razao_social,
                nome_fantasia = :nome_fantasia,
                cnpj = :cnpj,
                grupo_empresarial_id = :grupo_empresarial_id,
                ativo = :ativo,
                configuracoes = :configuracoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $configuracoes = isset($data['configuracoes']) ? json_encode($data['configuracoes']) : null;
        
        return $stmt->execute([
            'id' => $id,
            'codigo' => $data['codigo'],
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'] ?? null,
            'grupo_empresarial_id' => $data['grupo_empresarial_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'configuracoes' => $configuracoes
        ]);
    }
    
    /**
     * Exclui uma empresa (soft delete - marca como inativa)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Remove uma empresa permanentemente
     */
    public function forceDelete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Valida os dados da empresa
     */
    public function validate($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['codigo'])) {
            $errors[] = 'Código é obrigatório';
        } else {
            // Verifica se o código já existe (exceto para o próprio registro)
            $existing = $this->findByCodigo($data['codigo']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors[] = 'Código já está em uso';
            }
        }
        
        if (empty($data['razao_social'])) {
            $errors[] = 'Razão social é obrigatória';
        }
        
        if (empty($data['nome_fantasia'])) {
            $errors[] = 'Nome fantasia é obrigatório';
        }
        
        // Valida CNPJ se fornecido
        if (!empty($data['cnpj'])) {
            $existing = $this->findByCnpj($data['cnpj']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors[] = 'CNPJ já está em uso';
            }
        }
        
        return $errors;
    }
    
    /**
     * Retorna empresas por IDs (para consolidação)
     */
    public function findByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE id IN ($placeholders) AND ativo = 1 ORDER BY razao_social ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

