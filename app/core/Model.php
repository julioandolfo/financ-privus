<?php
namespace App\Core;

use App\Core\Database;

/**
 * Classe base para todos os models
 */
abstract class Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Retorna conexão PDO
     */
    protected function getConnection()
    {
        return $this->db->getConnection();
    }
    
    // Métodos estáticos removidos - cada Model implementa seus próprios métodos de CRUD
    
    /**
     * Executa uma query customizada
     */
    protected function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }
    
    /**
     * Executa uma query e retorna todos os resultados
     */
    protected function fetchAll($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Executa uma query e retorna um resultado
     */
    protected function fetchOne($sql, $params = [])
    {
        return $this->db->fetchOne($sql, $params);
    }
}

