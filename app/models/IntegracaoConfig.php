<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoConfig extends Model
{
    protected $table = 'integracoes_config';
    protected $db;
    
    const TIPO_BANCO_DADOS = 'banco_dados';
    const TIPO_WOOCOMMERCE = 'woocommerce';
    const TIPO_API = 'api';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca todas as integrações
     */
    public function findAll($empresaId = null, $tipo = null, $ativo = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        if ($ativo !== null) {
            $sql .= " AND ativo = :ativo";
            $params['ativo'] = $ativo ? 1 : 0;
        }
        
        $sql .= " ORDER BY data_cadastro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca integração por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Cria nova integração
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, tipo, nome, ativo, configuracoes, intervalo_sincronizacao) 
                VALUES 
                (:empresa_id, :tipo, :nome, :ativo, :configuracoes, :intervalo_sincronizacao)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'ativo' => $data['ativo'] ?? 1,
            'configuracoes' => is_array($data['configuracoes']) ? json_encode($data['configuracoes']) : $data['configuracoes'],
            'intervalo_sincronizacao' => $data['intervalo_sincronizacao'] ?? 60
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza integração
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome = :nome,
                ativo = :ativo,
                configuracoes = :configuracoes,
                intervalo_sincronizacao = :intervalo_sincronizacao
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'ativo' => $data['ativo'] ?? 1,
            'configuracoes' => is_array($data['configuracoes']) ? json_encode($data['configuracoes']) : $data['configuracoes'],
            'intervalo_sincronizacao' => $data['intervalo_sincronizacao'] ?? 60
        ]);
    }
    
    /**
     * Exclui integração
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Atualiza data de última sincronização
     */
    public function updateUltimaSincronizacao($id, $proxima = null)
    {
        $sql = "UPDATE {$this->table} SET 
                ultima_sincronizacao = NOW()";
        
        if ($proxima) {
            $sql .= ", proxima_sincronizacao = :proxima";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $params = ['id' => $id];
        
        if ($proxima) {
            $params['proxima'] = $proxima;
        }
        
        return $stmt->execute($params);
    }
    
    /**
     * Busca integrações que precisam sincronizar
     */
    public function findParaSincronizar()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ativo = 1 
                AND (proxima_sincronizacao IS NULL OR proxima_sincronizacao <= NOW())
                ORDER BY proxima_sincronizacao ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Estatísticas de integrações
     */
    public function getEstatisticas($empresaId = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativas,
                    SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativas,
                    SUM(CASE WHEN tipo = 'woocommerce' THEN 1 ELSE 0 END) as woocommerce,
                    SUM(CASE WHEN tipo = 'banco_dados' THEN 1 ELSE 0 END) as banco_dados,
                    SUM(CASE WHEN tipo = 'api' THEN 1 ELSE 0 END) as api
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
