<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Notificações
 */
class Notificacao extends Model
{
    protected $table = 'notificacoes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria uma nova notificação
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (usuario_id, empresa_id, tipo, titulo, mensagem, icone, cor, 
                 link_url, link_texto, dados_extras)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['usuario_id'],
            $data['empresa_id'] ?? null,
            $data['tipo'],
            $data['titulo'],
            $data['mensagem'],
            $data['icone'] ?? 'bell',
            $data['cor'] ?? 'blue',
            $data['link_url'] ?? null,
            $data['link_texto'] ?? null,
            isset($data['dados_extras']) ? json_encode($data['dados_extras']) : null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Busca notificações do usuário
     */
    public function findByUsuario($usuarioId, $apenasNaoLidas = false, $limite = 50)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = ?";
        
        if ($apenasNaoLidas) {
            $sql .= " AND lida = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Conta notificações não lidas
     */
    public function contarNaoLidas($usuarioId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE usuario_id = ? AND lida = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $result['total'];
    }
    
    /**
     * Marca notificação como lida
     */
    public function marcarComoLida($id)
    {
        $sql = "UPDATE {$this->table} SET lida = 1, lida_em = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Marca todas as notificações do usuário como lidas
     */
    public function marcarTodasComoLidas($usuarioId)
    {
        $sql = "UPDATE {$this->table} SET lida = 1, lida_em = NOW() 
                WHERE usuario_id = ? AND lida = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }
    
    /**
     * Busca notificações para envio de push
     */
    public function buscarParaPush()
    {
        $sql = "SELECT n.*, nc.web_push_endpoint, nc.web_push_p256dh, nc.web_push_auth
                FROM {$this->table} n
                JOIN notificacoes_config nc ON n.usuario_id = nc.usuario_id
                WHERE n.enviada_push = 0 
                AND nc.web_push_ativo = 1 
                AND nc.web_push_endpoint IS NOT NULL
                AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Marca como enviada via push
     */
    public function marcarComoEnviadaPush($id)
    {
        $sql = "UPDATE {$this->table} SET enviada_push = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Exclui notificações antigas (mais de 30 dias)
     */
    public function limparAntigas($dias = 30)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) AND lida = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$dias]);
    }
    
    /**
     * Busca por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Exclui notificação
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca notificações recentes para o dropdown
     */
    public function buscarRecentes($usuarioId, $limite = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = ?
                ORDER BY lida ASC, created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca notificações com paginação
     */
    public function findAll($usuarioId, $filtros = [], $pagina = 1, $porPagina = 20)
    {
        $offset = ($pagina - 1) * $porPagina;
        
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = ?";
        $params = [$usuarioId];
        
        if (isset($filtros['tipo']) && $filtros['tipo']) {
            $sql .= " AND tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (isset($filtros['lida']) && $filtros['lida'] !== '') {
            $sql .= " AND lida = ?";
            $params[] = $filtros['lida'];
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT {$porPagina} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Conta total de notificações
     */
    public function countAll($usuarioId, $filtros = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE usuario_id = ?";
        $params = [$usuarioId];
        
        if (isset($filtros['tipo']) && $filtros['tipo']) {
            $sql .= " AND tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (isset($filtros['lida']) && $filtros['lida'] !== '') {
            $sql .= " AND lida = ?";
            $params[] = $filtros['lida'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $result['total'];
    }
}
