<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Auditoria do Sistema
 */
class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registra uma ação de auditoria
     */
    public static function registrar($tabela, $registroId, $acao, $dadosAntes = null, $dadosDepois = null, $motivo = null)
    {
        try {
            $instance = new self();
            
            $sql = "INSERT INTO auditoria 
                    (tabela, registro_id, acao, usuario_id, dados_antes, dados_depois, ip, user_agent, motivo) 
                    VALUES 
                    (:tabela, :registro_id, :acao, :usuario_id, :dados_antes, :dados_depois, :ip, :user_agent, :motivo)";
            
            $stmt = $instance->db->prepare($sql);
            
            return $stmt->execute([
                'tabela' => $tabela,
                'registro_id' => $registroId,
                'acao' => $acao,
                'usuario_id' => $_SESSION['usuario_id'] ?? null,
                'dados_antes' => $dadosAntes ? json_encode($dadosAntes, JSON_UNESCAPED_UNICODE) : null,
                'dados_depois' => $dadosDepois ? json_encode($dadosDepois, JSON_UNESCAPED_UNICODE) : null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'motivo' => $motivo
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca histórico de um registro específico
     */
    public function getHistorico($tabela, $registroId)
    {
        $sql = "SELECT a.*, u.nome as usuario_nome 
                FROM {$this->table} a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.tabela = :tabela AND a.registro_id = :registro_id 
                ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tabela' => $tabela,
            'registro_id' => $registroId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca todos os registros de auditoria com filtros
     */
    public function findAll($filters = [], $limit = 100, $offset = 0)
    {
        $sql = "SELECT a.*, u.nome as usuario_nome 
                FROM {$this->table} a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['tabela'])) {
            $sql .= " AND a.tabela = :tabela";
            $params['tabela'] = $filters['tabela'];
        }
        
        if (!empty($filters['acao'])) {
            $sql .= " AND a.acao = :acao";
            $params['acao'] = $filters['acao'];
        }
        
        if (!empty($filters['usuario_id'])) {
            $sql .= " AND a.usuario_id = :usuario_id";
            $params['usuario_id'] = $filters['usuario_id'];
        }
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND a.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'] . ' 00:00:00';
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND a.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Conta total de registros de auditoria
     */
    public function count($filters = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['tabela'])) {
            $sql .= " AND tabela = :tabela";
            $params['tabela'] = $filters['tabela'];
        }
        
        if (!empty($filters['acao'])) {
            $sql .= " AND acao = :acao";
            $params['acao'] = $filters['acao'];
        }
        
        if (!empty($filters['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $filters['usuario_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
