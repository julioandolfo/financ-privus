<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ApiLog extends Model
{
    protected $table = 'api_logs';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Cria um novo log
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (api_token_id, metodo, endpoint, parametros, body, status_code, 
                 resposta, ip, user_agent, tempo_resposta, created_at)
                VALUES 
                (:api_token_id, :metodo, :endpoint, :parametros, :body, :status_code,
                 :resposta, :ip, :user_agent, :tempo_resposta, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'api_token_id' => $data['api_token_id'] ?? null,
            'metodo' => $data['metodo'],
            'endpoint' => $data['endpoint'],
            'parametros' => $data['parametros'] ?? null,
            'body' => $data['body'] ?? null,
            'status_code' => $data['status_code'],
            'resposta' => $data['resposta'] ?? null,
            'ip' => $data['ip'],
            'user_agent' => $data['user_agent'] ?? null,
            'tempo_resposta' => $data['tempo_resposta'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Busca logs (com paginação)
     */
    public function findAll($filters = [], $limit = 100, $offset = 0)
    {
        $sql = "SELECT al.*, at.nome as token_nome
                FROM {$this->table} al
                LEFT JOIN api_tokens at ON al.api_token_id = at.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['token_id'])) {
            $sql .= " AND al.api_token_id = :token_id";
            $params['token_id'] = $filters['token_id'];
        }
        
        if (isset($filters['metodo'])) {
            $sql .= " AND al.metodo = :metodo";
            $params['metodo'] = $filters['metodo'];
        }
        
        if (isset($filters['status_code'])) {
            $sql .= " AND al.status_code = :status_code";
            $params['status_code'] = $filters['status_code'];
        }
        
        if (isset($filters['data_inicio'])) {
            $sql .= " AND al.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (isset($filters['data_fim'])) {
            $sql .= " AND al.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind de limite e offset como inteiros
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Busca log por ID
     */
    public function findById($id)
    {
        $sql = "SELECT al.*, at.nome as token_nome
                FROM {$this->table} al
                LEFT JOIN api_tokens at ON al.api_token_id = at.id
                WHERE al.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém estatísticas gerais
     */
    public function getStats($filters = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 END) as success_requests,
                    COUNT(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 END) as client_errors,
                    COUNT(CASE WHEN status_code >= 500 THEN 1 END) as server_errors,
                    AVG(tempo_resposta) as avg_response_time,
                    MAX(tempo_resposta) as max_response_time,
                    MIN(tempo_resposta) as min_response_time
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['token_id'])) {
            $sql .= " AND api_token_id = :token_id";
            $params['token_id'] = $filters['token_id'];
        }
        
        if (isset($filters['data_inicio'])) {
            $sql .= " AND created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'];
        }
        
        if (isset($filters['data_fim'])) {
            $sql .= " AND created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém endpoints mais usados
     */
    public function getTopEndpoints($limit = 10)
    {
        $sql = "SELECT endpoint, metodo, COUNT(*) as count
                FROM {$this->table}
                GROUP BY endpoint, metodo
                ORDER BY count DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Limpa logs antigos
     */
    public function deleteOld($days = 30)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        return $stmt->rowCount();
    }
}
