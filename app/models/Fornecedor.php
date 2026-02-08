<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Fornecedores
 */
class Fornecedor extends Model
{
    protected $table = 'fornecedores';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os fornecedores
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $sql .= " ORDER BY nome_razao_social ASC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($empresaId) {
            $stmt->execute(['empresa_id' => $empresaId]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna fornecedores com filtros e paginação
     */
    public function findAllWithFilters($filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if (!empty($filters['empresa_id'])) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $filters['empresa_id'];
        }
        
        if (!empty($filters['busca'])) {
            $sql .= " AND (nome_razao_social LIKE :busca OR cpf_cnpj LIKE :busca OR email LIKE :busca)";
            $params['busca'] = '%' . $filters['busca'] . '%';
        }
        
        if (!empty($filters['tipo_pessoa'])) {
            $sql .= " AND tipo = :tipo_pessoa";
            $params['tipo_pessoa'] = $filters['tipo_pessoa'];
        }
        
        $sql .= " ORDER BY nome_razao_social ASC";
        
        // Paginação
        if (isset($filters['limite'])) {
            if (isset($filters['offset'])) {
                $sql .= " LIMIT " . (int)$filters['limite'] . " OFFSET " . (int)$filters['offset'];
            } else {
                $sql .= " LIMIT " . (int)$filters['limite'];
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Conta fornecedores com filtros aplicados
     */
    public function countWithFilters($filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $params = [];
        
        if (!empty($filters['empresa_id'])) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $filters['empresa_id'];
        }
        
        if (!empty($filters['busca'])) {
            $sql .= " AND (nome_razao_social LIKE :busca OR cpf_cnpj LIKE :busca OR email LIKE :busca)";
            $params['busca'] = '%' . $filters['busca'] . '%';
        }
        
        if (!empty($filters['tipo_pessoa'])) {
            $sql .= " AND tipo = :tipo_pessoa";
            $params['tipo_pessoa'] = $filters['tipo_pessoa'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Retorna um fornecedor por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca fornecedor por CPF/CNPJ
     */
    public function findByCpfCnpj($cpfCnpj, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf_cnpj = :cpf_cnpj";
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        $params = ['cpf_cnpj' => $cpfCnpj];
        if ($empresaId) {
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria um novo fornecedor
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, tipo, nome_razao_social, cpf_cnpj, email, telefone, endereco, ativo) 
                VALUES 
                (:empresa_id, :tipo, :nome_razao_social, :cpf_cnpj, :email, :telefone, :endereco, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        $endereco = isset($data['endereco']) && is_array($data['endereco']) 
            ? json_encode($data['endereco']) 
            : null;
        
        $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'tipo' => $data['tipo'],
            'nome_razao_social' => $data['nome_razao_social'],
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'endereco' => $endereco,
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza um fornecedor
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                tipo = :tipo,
                nome_razao_social = :nome_razao_social,
                cpf_cnpj = :cpf_cnpj,
                email = :email,
                telefone = :telefone,
                endereco = :endereco,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $endereco = isset($data['endereco']) && is_array($data['endereco']) 
            ? json_encode($data['endereco']) 
            : null;
        
        return $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'tipo' => $data['tipo'],
            'nome_razao_social' => $data['nome_razao_social'],
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'endereco' => $endereco,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui um fornecedor (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Exclui permanentemente um fornecedor
     */
    public function forceDelete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Valida CPF
     */
    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valida CNPJ
     */
    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        $tamanho = strlen($cnpj) - 2;
        $numeros = substr($cnpj, 0, $tamanho);
        $digitos = substr($cnpj, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        if ($resultado != $digitos[0]) {
            return false;
        }
        
        $tamanho = $tamanho + 1;
        $numeros = substr($cnpj, 0, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        if ($resultado != $digitos[1]) {
            return false;
        }
        
        return true;
    }
}

