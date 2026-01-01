<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Usuários
 */
class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna um usuário por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna um usuário por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna usuários por empresa
     */
    public function findByEmpresa($empresaId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE empresa_id = :empresa_id AND ativo = 1 ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Cria um novo usuário
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, nome, email, senha, ativo, empresas_consolidadas_padrao) 
                VALUES 
                (:empresa_id, :nome, :email, :senha, :ativo, :empresas_consolidadas_padrao)";
        
        $stmt = $this->db->prepare($sql);
        
        $empresasConsolidadas = isset($data['empresas_consolidadas_padrao']) 
            ? json_encode($data['empresas_consolidadas_padrao']) 
            : null;
        
        // Converte empresa_id vazio para null e valida se existe
        $empresaId = null;
        if (!empty($data['empresa_id']) && $data['empresa_id'] !== '') {
            $empresaId = (int)$data['empresa_id'];
            // Valida se empresa existe
            $empresaModel = new \App\Models\Empresa();
            if (!$empresaModel->findById($empresaId)) {
                throw new \Exception('Empresa selecionada não encontrada');
            }
        }
        
        // Bind dos parâmetros com tipo correto para NULL
        $stmt->bindValue(':empresa_id', $empresaId, $empresaId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome'], \PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindValue(':senha', password_hash($data['senha'], PASSWORD_DEFAULT), \PDO::PARAM_STR);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, \PDO::PARAM_INT);
        $stmt->bindValue(':empresas_consolidadas_padrao', $empresasConsolidadas, $empresasConsolidadas === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza um usuário
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                nome = :nome,
                email = :email,
                ativo = :ativo,
                empresas_consolidadas_padrao = :empresas_consolidadas_padrao";
        
        // Converte empresa_id vazio para null e valida se existe
        $empresaId = null;
        if (!empty($data['empresa_id']) && $data['empresa_id'] !== '') {
            $empresaId = (int)$data['empresa_id'];
            // Valida se empresa existe
            $empresaModel = new \App\Models\Empresa();
            if (!$empresaModel->findById($empresaId)) {
                throw new \Exception('Empresa selecionada não encontrada');
            }
        }
        
        $empresasConsolidadas = isset($data['empresas_consolidadas_padrao']) 
            ? json_encode($data['empresas_consolidadas_padrao']) 
            : null;
        
        // Se senha foi fornecida, atualiza
        if (!empty($data['senha'])) {
            $sql .= ", senha = :senha";
        }
        
        // Se avatar foi fornecido, atualiza
        if (isset($data['avatar'])) {
            $sql .= ", avatar = :avatar";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros com tipo correto para NULL
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, $empresaId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome'], \PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, \PDO::PARAM_INT);
        $stmt->bindValue(':empresas_consolidadas_padrao', $empresasConsolidadas, $empresasConsolidadas === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        
        if (!empty($data['senha'])) {
            $stmt->bindValue(':senha', password_hash($data['senha'], PASSWORD_DEFAULT), \PDO::PARAM_STR);
        }
        
        if (isset($data['avatar'])) {
            $stmt->bindValue(':avatar', $data['avatar'], \PDO::PARAM_STR);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Atualiza apenas o avatar do usuário
     */
    public function updateAvatar($id, $avatarPath)
    {
        $sql = "UPDATE {$this->table} SET avatar = :avatar WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'avatar' => $avatarPath
        ]);
    }
    
    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword($id, $senha)
    {
        $sql = "UPDATE {$this->table} SET senha = :senha WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'senha' => password_hash($senha, PASSWORD_DEFAULT)
        ]);
    }
    
    /**
     * Atualiza último acesso
     */
    public function updateLastAccess($id)
    {
        $sql = "UPDATE {$this->table} SET ultimo_acesso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verifica credenciais de login
     */
    public function authenticate($email, $senha)
    {
        $usuario = $this->findByEmail($email);
        
        if (!$usuario) {
            return false;
        }
        
        if (!$usuario['ativo']) {
            return false;
        }
        
        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }
        
        // Atualiza último acesso
        $this->updateLastAccess($usuario['id']);
        
        return $usuario;
    }
    
    /**
     * Valida dados do usuário
     */
    public function validate($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        } elseif ($this->emailExists($data['email'], $id)) {
            $errors[] = 'Email já está em uso';
        }
        
        if (!$id && empty($data['senha'])) {
            $errors[] = 'Senha é obrigatória';
        } elseif (!empty($data['senha']) && strlen($data['senha']) < 8) {
            $errors[] = 'Senha deve ter no mínimo 8 caracteres';
        }
        
        return $errors;
    }
    
    /**
     * Retorna todos os usuários
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT u.*, e.razao_social as empresa_nome 
                FROM {$this->table} u 
                LEFT JOIN empresas e ON u.empresa_id = e.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $sql .= " AND u.ativo = :ativo";
            $params['ativo'] = $filters['ativo'];
        }
        
        if (isset($filters['empresa_id']) && $filters['empresa_id'] !== '') {
            $sql .= " AND u.empresa_id = :empresa_id";
            $params['empresa_id'] = $filters['empresa_id'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (u.nome LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY u.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Exclui um usuário (soft delete - marca como inativo)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna todas as empresas que o usuário tem acesso
     * Inclui a empresa principal + empresas consolidadas
     */
    public function getEmpresas($usuarioId)
    {
        $usuario = $this->findById($usuarioId);
        
        if (!$usuario) {
            return [];
        }
        
        $empresasIds = [];
        
        // Empresa principal
        if ($usuario['empresa_id']) {
            $empresasIds[] = $usuario['empresa_id'];
        }
        
        // Empresas consolidadas
        if (!empty($usuario['empresas_consolidadas_padrao'])) {
            $empresasConsolidadas = json_decode($usuario['empresas_consolidadas_padrao'], true);
            if (is_array($empresasConsolidadas)) {
                $empresasIds = array_merge($empresasIds, $empresasConsolidadas);
            }
        }
        
        // Remover duplicatas
        $empresasIds = array_unique($empresasIds);
        
        if (empty($empresasIds)) {
            return [];
        }
        
        // Buscar dados das empresas
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        $sql = "SELECT * FROM empresas WHERE id IN ({$placeholders}) AND ativo = 1 ORDER BY nome_fantasia ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($empresasIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

