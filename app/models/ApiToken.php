<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ApiToken extends Model
{
    protected $table = 'api_tokens';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca todos os tokens (opcionalmente filtrados por empresa)
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT at.*, u.nome as usuario_nome, u.email as usuario_email, 
                       e.nome_fantasia as empresa_nome
                FROM {$this->table} at
                INNER JOIN usuarios u ON at.usuario_id = u.id
                LEFT JOIN empresas e ON at.empresa_id = e.id
                WHERE at.ativo = 1";
        
        $params = [];
        if ($empresaId !== null) {
            $sql .= " AND (at.empresa_id = :empresa_id OR at.empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY at.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Busca token por ID
     */
    public function findById($id)
    {
        $sql = "SELECT at.*, u.nome as usuario_nome, u.email as usuario_email,
                       e.nome_fantasia as empresa_nome
                FROM {$this->table} at
                INNER JOIN usuarios u ON at.usuario_id = u.id
                LEFT JOIN empresas e ON at.empresa_id = e.id
                WHERE at.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca token pela string do token
     */
    public function findByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE token = :token AND ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo token
     */
    public function create($data)
    {
        // Gerar token único
        $data['token'] = $this->generateUniqueToken();
        
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, usuario_id, nome, token, permissoes, ip_whitelist, 
                 rate_limit, expira_em, ativo, created_at, updated_at)
                VALUES 
                (:empresa_id, :usuario_id, :nome, :token, :permissoes, :ip_whitelist,
                 :rate_limit, :expira_em, :ativo, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $data['empresa_id'] ?? null,
            'usuario_id' => $data['usuario_id'],
            'nome' => $data['nome'],
            'token' => $data['token'],
            'permissoes' => isset($data['permissoes']) ? json_encode($data['permissoes']) : null,
            'ip_whitelist' => isset($data['ip_whitelist']) ? json_encode($data['ip_whitelist']) : null,
            'rate_limit' => $data['rate_limit'] ?? 1000,
            'expira_em' => $data['expira_em'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Atualiza um token
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                nome = :nome,
                permissoes = :permissoes,
                ip_whitelist = :ip_whitelist,
                rate_limit = :rate_limit,
                expira_em = :expira_em,
                ativo = :ativo,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'permissoes' => isset($data['permissoes']) ? json_encode($data['permissoes']) : null,
            'ip_whitelist' => isset($data['ip_whitelist']) ? json_encode($data['ip_whitelist']) : null,
            'rate_limit' => $data['rate_limit'] ?? 1000,
            'expira_em' => $data['expira_em'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }

    /**
     * Exclui (soft delete) um token
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Atualiza o último uso do token
     */
    public function updateLastUsed($tokenId)
    {
        $sql = "UPDATE {$this->table} SET ultimo_uso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $tokenId]);
    }

    /**
     * Verifica se o token está válido
     */
    public function isValid($token, $ip = null)
    {
        $tokenData = $this->findByToken($token);
        
        if (!$tokenData) {
            return ['valid' => false, 'message' => 'Token inválido'];
        }
        
        // Verificar se está ativo
        if (!$tokenData['ativo']) {
            return ['valid' => false, 'message' => 'Token desativado'];
        }
        
        // Verificar expiração
        if ($tokenData['expira_em'] && strtotime($tokenData['expira_em']) < time()) {
            return ['valid' => false, 'message' => 'Token expirado'];
        }
        
        // Verificar IP whitelist
        if ($ip && $tokenData['ip_whitelist']) {
            $whitelist = json_decode($tokenData['ip_whitelist'], true);
            if (!empty($whitelist) && !in_array($ip, $whitelist)) {
                return ['valid' => false, 'message' => 'IP não autorizado'];
            }
        }
        
        // Verificar rate limit
        if (!$this->checkRateLimit($tokenData['id'], $tokenData['rate_limit'])) {
            return ['valid' => false, 'message' => 'Rate limit excedido'];
        }
        
        return ['valid' => true, 'token' => $tokenData];
    }

    /**
     * Verifica rate limit
     */
    private function checkRateLimit($tokenId, $limit)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM api_logs 
                WHERE api_token_id = :token_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token_id' => $tokenId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] < $limit;
    }

    /**
     * Gera um token único
     */
    private function generateUniqueToken()
    {
        do {
            $token = bin2hex(random_bytes(32)); // 64 caracteres
            $existing = $this->findByToken($token);
        } while ($existing);
        
        return $token;
    }

    /**
     * Regenera o token
     */
    public function regenerate($id)
    {
        $newToken = $this->generateUniqueToken();
        
        $sql = "UPDATE {$this->table} SET token = :token, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $newToken, 'id' => $id]);
        
        return $newToken;
    }

    /**
     * Obtém estatísticas do token
     */
    public function getStats($tokenId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 END) as success_requests,
                    COUNT(CASE WHEN status_code >= 400 THEN 1 END) as error_requests,
                    AVG(tempo_resposta) as avg_response_time,
                    MAX(created_at) as last_request
                FROM api_logs
                WHERE api_token_id = :token_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token_id' => $tokenId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
