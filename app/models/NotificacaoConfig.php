<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Configurações de Notificação
 */
class NotificacaoConfig extends Model
{
    protected $table = 'notificacoes_config';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca configurações do usuário
     */
    public function findByUsuario($usuarioId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se não existir, cria com valores padrão
        if (!$config) {
            $this->create(['usuario_id' => $usuarioId]);
            return $this->findByUsuario($usuarioId);
        }
        
        return $config;
    }
    
    /**
     * Cria configuração padrão para o usuário
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (usuario_id) VALUES (?)
                ON DUPLICATE KEY UPDATE usuario_id = usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['usuario_id']]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza configurações
     */
    public function update($usuarioId, $data)
    {
        $campos = [];
        $valores = [];
        
        $camposPermitidos = [
            'notificar_vencimentos', 'antecedencia_vencimento', 'notificar_vencidas',
            'notificar_recorrencias', 'notificar_recebimentos', 'notificar_fluxo_caixa',
            'web_push_ativo', 'web_push_endpoint', 'web_push_p256dh', 'web_push_auth',
            'som_ativo', 'agrupar_notificacoes', 'horario_silencio_inicio', 'horario_silencio_fim'
        ];
        
        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $data)) {
                $campos[] = "{$campo} = ?";
                $valores[] = $data[$campo];
            }
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $valores[] = $usuarioId;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Salva subscription do Web Push
     */
    public function salvarSubscription($usuarioId, $endpoint, $p256dh, $auth)
    {
        // Garante que a config existe
        $this->findByUsuario($usuarioId);
        
        $sql = "UPDATE {$this->table} SET 
                web_push_ativo = 1,
                web_push_endpoint = ?,
                web_push_p256dh = ?,
                web_push_auth = ?
                WHERE usuario_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$endpoint, $p256dh, $auth, $usuarioId]);
    }
    
    /**
     * Remove subscription do Web Push
     */
    public function removerSubscription($usuarioId)
    {
        $sql = "UPDATE {$this->table} SET 
                web_push_ativo = 0,
                web_push_endpoint = NULL,
                web_push_p256dh = NULL,
                web_push_auth = NULL
                WHERE usuario_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }
    
    /**
     * Busca usuários com push ativo para determinado tipo de notificação
     */
    public function buscarUsuariosComPush($tipo)
    {
        $campoTipo = 'notificar_' . $tipo;
        
        $sql = "SELECT nc.*, u.nome as usuario_nome, u.email as usuario_email
                FROM {$this->table} nc
                JOIN usuarios u ON nc.usuario_id = u.id
                WHERE nc.web_push_ativo = 1 
                AND nc.web_push_endpoint IS NOT NULL";
        
        // Adiciona filtro por tipo se existir o campo
        $camposValidos = ['vencimentos', 'vencidas', 'recorrencias', 'recebimentos', 'fluxo_caixa'];
        if (in_array($tipo, $camposValidos)) {
            $sql .= " AND nc.notificar_{$tipo} = 1";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
