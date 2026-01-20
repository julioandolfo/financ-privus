<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Clientes
 */
class Cliente extends Model
{
    protected $table = 'clientes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todos os clientes
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
     * Retorna um cliente por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca cliente por CPF/CNPJ
     */
    public function findByCpfCnpj($cpfCnpj, $empresaId = null)
    {
        // Remove formatação do CPF/CNPJ
        $cpfCnpjLimpo = preg_replace('/[^0-9]/', '', $cpfCnpj);
        
        $sql = "SELECT * FROM {$this->table} WHERE REPLACE(REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', ''), ' ', '') = :cpf_cnpj";
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        $params = ['cpf_cnpj' => $cpfCnpjLimpo];
        if ($empresaId) {
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar ou buscar cliente por CPF/CNPJ (para API)
     */
    public function findOrCreateByCpfCnpj($data, $empresaId)
    {
        // Se tem CPF/CNPJ, tenta buscar primeiro
        if (!empty($data['cpf_cnpj'])) {
            $cliente = $this->findByCpfCnpj($data['cpf_cnpj'], $empresaId);
            if ($cliente) {
                return $cliente;
            }
        }
        
        // Se não encontrou, cria novo cliente
        $data['empresa_id'] = $empresaId;
        
        // Define valores padrão
        $data['tipo'] = $data['tipo'] ?? $this->detectarTipo($data['cpf_cnpj'] ?? '');
        $data['nome_razao_social'] = $data['nome_razao_social'] ?? $data['nome'] ?? 'Cliente API';
        $data['ativo'] = 1;
        
        $id = $this->create($data);
        
        if ($id) {
            return $this->findById($id);
        }
        
        return null;
    }
    
    /**
     * Detecta tipo de pessoa por CPF/CNPJ
     */
    private function detectarTipo($cpfCnpj)
    {
        $numeros = preg_replace('/[^0-9]/', '', $cpfCnpj);
        return strlen($numeros) === 14 ? 'juridica' : 'fisica';
    }
    
    /**
     * Cria um novo cliente
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
     * Atualiza um cliente
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
     * Exclui um cliente (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Exclui permanentemente um cliente
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

