<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class WebmaniBRFormaPagamento extends Model
{
    protected $table = 'webmanibr_formas_pagamento';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findByIntegracao($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id ORDER BY gateway ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    integracao_id, gateway, forma_pagamento, descricao
                ) VALUES (
                    :integracao_id, :gateway, :forma_pagamento, :descricao
                )";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function deleteByIntegracao($integracaoId)
    {
        $sql = "DELETE FROM {$this->table} WHERE integracao_id = :integracao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['integracao_id' => $integracaoId]);
    }
}
