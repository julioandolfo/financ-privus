<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ConexaoBancaria extends Model
{
    protected $table = 'conexoes_bancarias';
    protected $db;
    
    const BANCOS_DISPONIVEIS = [
        'sicredi' => ['nome' => 'Sicredi', 'logo' => '🏦', 'cor' => 'green'],
        'sicoob' => ['nome' => 'Sicoob', 'logo' => '🏦', 'cor' => 'blue'],
        'bradesco' => ['nome' => 'Bradesco', 'logo' => '🏦', 'cor' => 'red'],
        'itau' => ['nome' => 'Itaú', 'logo' => '🏦', 'cor' => 'orange']
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as conexões de uma empresa
     */
    public function findByEmpresa($empresaId, $apenasAtivas = true)
    {
        $sql = "SELECT cb.*, u.nome as usuario_nome 
                FROM {$this->table} cb
                LEFT JOIN usuarios u ON cb.usuario_id = u.id
                WHERE cb.empresa_id = :empresa_id";
        
        if ($apenasAtivas) {
            $sql .= " AND cb.ativo = 1";
        }
        
        $sql .= " ORDER BY cb.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conexão por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conexão bancária
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, usuario_id, banco, tipo_integracao, tipo, identificacao, 
                 access_token, refresh_token, token_expira_em, consent_id,
                 auto_sync, frequencia_sync, categoria_padrao_id, 
                 centro_custo_padrao_id, aprovacao_automatica,
                 ambiente, client_id, client_secret, cert_pem, key_pem, cert_password, ativo, ultima_sincronizacao) 
                VALUES 
                (:empresa_id, :usuario_id, :banco, :tipo_integracao, :tipo, :identificacao,
                 :access_token, :refresh_token, :token_expira_em, :consent_id,
                 :auto_sync, :frequencia_sync, :categoria_padrao_id,
                 :centro_custo_padrao_id, :aprovacao_automatica,
                 :ambiente, :client_id, :client_secret, :cert_pem, :key_pem, :cert_password, :ativo, :ultima_sincronizacao)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'usuario_id' => $data['usuario_id'],
            'banco' => $data['banco'],
            'tipo_integracao' => $data['tipo_integracao'] ?? 'of',
            'tipo' => $data['tipo'],
            'identificacao' => $data['identificacao'] ?? null,
            'access_token' => $this->encryptToken($data['access_token'] ?? null),
            'refresh_token' => $this->encryptToken($data['refresh_token'] ?? null),
            'token_expira_em' => $data['token_expira_em'] ?? null,
            'consent_id' => $data['consent_id'] ?? null,
            'auto_sync' => $data['auto_sync'] ?? 1,
            'frequencia_sync' => $data['frequencia_sync'] ?? 'diaria',
            'categoria_padrao_id' => $data['categoria_padrao_id'] ?? null,
            'centro_custo_padrao_id' => $data['centro_custo_padrao_id'] ?? null,
            'aprovacao_automatica' => $data['aprovacao_automatica'] ?? 0,
            'ambiente' => $data['ambiente'] ?? 'sandbox',
            'client_id' => $data['client_id'] ?? null,
            'client_secret' => $data['client_secret'] ?? null,
            'cert_pem' => $data['cert_pem'] ?? null,
            'key_pem' => $data['key_pem'] ?? null,
            'cert_password' => $data['cert_password'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'ultima_sincronizacao' => $data['ultima_sincronizacao'] ?? null
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma conexão
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        $allowed = ['identificacao', 'auto_sync', 'frequencia_sync', 
                   'categoria_padrao_id', 'centro_custo_padrao_id', 'aprovacao_automatica',
                   'access_token', 'refresh_token', 'token_expira_em', 'ultima_sincronizacao',
                   'ambiente', 'client_id', 'client_secret', 'cert_pem', 'key_pem', 'cert_password', 'ativo', 'tipo_integracao'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['access_token', 'refresh_token'])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $this->encryptToken($data[$field]);
                } else {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Desativa uma conexão (soft delete)
     */
    public function desconectar($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verifica se o token está próximo de expirar (menos de 7 dias)
     */
    public function tokenProximoExpiracao($id)
    {
        $conexao = $this->findById($id);
        if (!$conexao || !$conexao['token_expira_em']) {
            return false;
        }
        
        $dataExpiracao = new \DateTime($conexao['token_expira_em']);
        $hoje = new \DateTime();
        $diff = $hoje->diff($dataExpiracao);
        
        return $diff->days <= 7 && $diff->invert == 0;
    }
    
    /**
     * Estatísticas de uma empresa
     */
    public function getEstatisticas($empresaId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_conexoes,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as conexoes_ativas,
                    SUM(CASE WHEN auto_sync = 1 THEN 1 ELSE 0 END) as com_auto_sync,
                    MAX(ultima_sincronizacao) as ultima_sync_geral
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criptografa token (base64 simples - em produção usar algo mais robusto)
     */
    private function encryptToken($token)
    {
        if (!$token) return null;
        return base64_encode($token);
    }
    
    /**
     * Descriptografa token
     */
    public function decryptToken($encryptedToken)
    {
        if (!$encryptedToken) return null;
        return base64_decode($encryptedToken);
    }
    
    /**
     * Retorna informações do banco
     */
    public static function getBancoInfo($banco)
    {
        return self::BANCOS_DISPONIVEIS[$banco] ?? ['nome' => ucfirst($banco), 'logo' => '🏦', 'cor' => 'gray'];
    }
}
