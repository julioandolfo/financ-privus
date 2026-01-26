<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Logs do Sistema (Debug)
 */
class LogSistema extends Model
{
    protected $table = 'logs_sistema';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
        
        // Criar tabela se não existir
        $this->createTableIfNotExists();
    }
    
    /**
     * Cria a tabela de logs se não existir
     */
    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(50) NOT NULL DEFAULT 'info',
            modulo VARCHAR(100) NOT NULL,
            acao VARCHAR(100) NOT NULL,
            mensagem TEXT,
            dados JSON,
            usuario_id INT NULL,
            ip VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tipo (tipo),
            INDEX idx_modulo (modulo),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->exec($sql);
        } catch (\Exception $e) {
            // Tabela já pode existir, ignorar erro
        }
    }
    
    /**
     * Registra um log
     */
    public static function log($tipo, $modulo, $acao, $mensagem, $dados = null)
    {
        try {
            $instance = new self();
            
            $sql = "INSERT INTO {$instance->table} 
                    (tipo, modulo, acao, mensagem, dados, usuario_id, ip, user_agent) 
                    VALUES 
                    (:tipo, :modulo, :acao, :mensagem, :dados, :usuario_id, :ip, :user_agent)";
            
            $stmt = $instance->db->prepare($sql);
            
            return $stmt->execute([
                'tipo' => $tipo,
                'modulo' => $modulo,
                'acao' => $acao,
                'mensagem' => $mensagem,
                'dados' => $dados ? json_encode($dados, JSON_UNESCAPED_UNICODE) : null,
                'usuario_id' => $_SESSION['usuario_id'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao salvar log: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atalhos para diferentes tipos de log
     */
    public static function info($modulo, $acao, $mensagem, $dados = null)
    {
        return self::log('info', $modulo, $acao, $mensagem, $dados);
    }
    
    public static function debug($modulo, $acao, $mensagem, $dados = null)
    {
        return self::log('debug', $modulo, $acao, $mensagem, $dados);
    }
    
    public static function warning($modulo, $acao, $mensagem, $dados = null)
    {
        return self::log('warning', $modulo, $acao, $mensagem, $dados);
    }
    
    public static function error($modulo, $acao, $mensagem, $dados = null)
    {
        return self::log('error', $modulo, $acao, $mensagem, $dados);
    }
    
    /**
     * Busca logs com filtros
     */
    public function findAll($filters = [], $limit = 100, $offset = 0)
    {
        $sql = "SELECT l.*, u.nome as usuario_nome 
                FROM {$this->table} l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['tipo'])) {
            $sql .= " AND l.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        
        if (!empty($filters['modulo'])) {
            $sql .= " AND l.modulo LIKE :modulo";
            $params['modulo'] = '%' . $filters['modulo'] . '%';
        }
        
        if (!empty($filters['acao'])) {
            $sql .= " AND l.acao LIKE :acao";
            $params['acao'] = '%' . $filters['acao'] . '%';
        }
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND l.created_at >= :data_inicio";
            $params['data_inicio'] = $filters['data_inicio'] . ' 00:00:00';
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND l.created_at <= :data_fim";
            $params['data_fim'] = $filters['data_fim'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (l.mensagem LIKE :search OR l.dados LIKE :search2)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Conta total de logs
     */
    public function count($filters = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        
        if (!empty($filters['modulo'])) {
            $sql .= " AND modulo LIKE :modulo";
            $params['modulo'] = '%' . $filters['modulo'] . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Limpa logs antigos
     */
    public function limparAntigos($dias = 30)
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['dias' => $dias]);
    }
    
    /**
     * Limpa todos os logs
     */
    public function limparTodos()
    {
        $sql = "TRUNCATE TABLE {$this->table}";
        return $this->db->exec($sql);
    }
}
