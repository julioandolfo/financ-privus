<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoLog extends Model
{
    protected $table = 'integracoes_logs';
    protected $db;
    
    const TIPO_SUCESSO = 'sucesso';
    const TIPO_ERRO = 'erro';
    const TIPO_AVISO = 'aviso';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca logs por integração
     */
    public function findByIntegracaoId($integracaoId, $limit = 100)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE integracao_id = :integracao_id 
                ORDER BY data_execucao DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':integracao_id', $integracaoId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca logs por tipo
     */
    public function findByTipo($tipo, $limit = 100)
    {
        $sql = "SELECT l.*, i.nome as integracao_nome 
                FROM {$this->table} l
                INNER JOIN integracoes_config i ON l.integracao_id = i.id
                WHERE l.tipo = :tipo 
                ORDER BY l.data_execucao DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Cria log
     */
    public function create($integracaoId, $tipo, $mensagem, $dados = null)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, tipo, mensagem, dados) 
                VALUES 
                (:integracao_id, :tipo, :mensagem, :dados)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'dados' => $dados ? json_encode($dados) : null
        ]);
    }
    
    /**
     * Limpa logs antigos
     */
    public function limparAntigos($dias = 30)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE data_execucao < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['dias' => $dias]);
    }
    
    /**
     * Estatísticas de logs
     */
    public function getEstatisticas($integracaoId = null, $periodo = '7 days')
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo = 'sucesso' THEN 1 ELSE 0 END) as sucessos,
                    SUM(CASE WHEN tipo = 'erro' THEN 1 ELSE 0 END) as erros,
                    SUM(CASE WHEN tipo = 'aviso' THEN 1 ELSE 0 END) as avisos
                FROM {$this->table}
                WHERE data_execucao >= DATE_SUB(NOW(), INTERVAL {$periodo})";
        
        $params = [];
        
        if ($integracaoId) {
            $sql .= " AND integracao_id = :integracao_id";
            $params['integracao_id'] = $integracaoId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Últimos logs (geral)
     */
    public function getUltimosLogs($limit = 50)
    {
        $sql = "SELECT l.*, i.nome as integracao_nome, i.tipo as integracao_tipo
                FROM {$this->table} l
                INNER JOIN integracoes_config i ON l.integracao_id = i.id
                ORDER BY l.data_execucao DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
