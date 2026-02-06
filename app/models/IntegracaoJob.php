<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para gerenciar jobs/filas de integração
 */
class IntegracaoJob extends Model
{
    protected $table = 'integracao_jobs';
    protected $db;
    
    // Tipos de job
    const TIPO_SYNC_PRODUTOS = 'sync_produtos';
    const TIPO_SYNC_PEDIDOS = 'sync_pedidos';
    const TIPO_WEBHOOK = 'webhook';
    const TIPO_IMPORTAR_IMAGENS = 'importar_imagens';
    
    // Status
    const STATUS_PENDENTE = 'pendente';
    const STATUS_PROCESSANDO = 'processando';
    const STATUS_CONCLUIDO = 'concluido';
    const STATUS_ERRO = 'erro';
    const STATUS_CANCELADO = 'cancelado';
    
    // Prioridades
    const PRIORIDADE_ALTA = 1;
    const PRIORIDADE_NORMAL = 5;
    const PRIORIDADE_BAIXA = 10;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria um novo job
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, tipo, payload, status, prioridade, max_tentativas) 
                VALUES 
                (:integracao_id, :tipo, :payload, :status, :prioridade, :max_tentativas)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'tipo' => $data['tipo'],
            'payload' => isset($data['payload']) ? json_encode($data['payload']) : null,
            'status' => $data['status'] ?? self::STATUS_PENDENTE,
            'prioridade' => $data['prioridade'] ?? self::PRIORIDADE_NORMAL,
            'max_tentativas' => $data['max_tentativas'] ?? 3
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Busca próximo job pendente
     */
    public function buscarProximo()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = :status 
                AND tentativas < max_tentativas
                ORDER BY prioridade ASC, criado_em ASC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => self::STATUS_PENDENTE]);
        
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($job) {
            $job['payload'] = json_decode($job['payload'], true);
        }
        
        return $job ?: null;
    }
    
    /**
     * Marca job como processando
     */
    public function marcarProcessando($id)
    {
        $sql = "UPDATE {$this->table} 
                SET status = :status, 
                    iniciado_em = NOW(),
                    tentativas = tentativas + 1
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_PROCESSANDO
        ]);
    }
    
    /**
     * Marca job como concluído
     */
    public function marcarConcluido($id, $tempoExecucao = null)
    {
        $sql = "UPDATE {$this->table} 
                SET status = :status, 
                    concluido_em = NOW(),
                    tempo_execucao = :tempo_execucao
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_CONCLUIDO,
            'tempo_execucao' => $tempoExecucao
        ]);
    }
    
    /**
     * Marca job com erro
     */
    public function marcarErro($id, $erro, $tempoExecucao = null)
    {
        // Se atingiu max tentativas, marca como erro definitivo
        $job = $this->findById($id);
        $status = ($job['tentativas'] >= $job['max_tentativas']) 
            ? self::STATUS_ERRO 
            : self::STATUS_PENDENTE; // Volta para fila para retry
        
        $sql = "UPDATE {$this->table} 
                SET status = :status, 
                    erro = :erro,
                    tempo_execucao = :tempo_execucao,
                    concluido_em = IF(:definitivo, NOW(), NULL)
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'erro' => $erro,
            'tempo_execucao' => $tempoExecucao,
            'definitivo' => $status === self::STATUS_ERRO ? 1 : 0
        ]);
    }
    
    /**
     * Busca job por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($job && $job['payload']) {
            $job['payload'] = json_decode($job['payload'], true);
        }
        
        return $job ?: null;
    }
    
    /**
     * Lista jobs com filtros
     */
    public function listar($filtros = [])
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filtros['integracao_id'])) {
            $where[] = 'integracao_id = :integracao_id';
            $params['integracao_id'] = $filtros['integracao_id'];
        }
        
        if (!empty($filtros['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filtros['status'];
        }
        
        if (!empty($filtros['tipo'])) {
            $where[] = 'tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }
        
        $limit = $filtros['limit'] ?? 50;
        $offset = $filtros['offset'] ?? 0;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY criado_em DESC 
                LIMIT {$limit} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Conta jobs por status
     */
    public function contarPorStatus($integracaoId)
    {
        $sql = "SELECT status, COUNT(*) as total 
                FROM {$this->table} 
                WHERE integracao_id = :integracao_id 
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        
        $resultado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['status']] = $row['total'];
        }
        
        return $resultado;
    }
    
    /**
     * Limpa jobs antigos concluídos
     */
    public function limparAntigos($diasRetencao = 30)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE status IN (:concluido, :erro) 
                AND concluido_em < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'concluido' => self::STATUS_CONCLUIDO,
            'erro' => self::STATUS_ERRO,
            'dias' => $diasRetencao
        ]);
    }
}
