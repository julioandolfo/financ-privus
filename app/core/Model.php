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
    
    /**
     * Busca todos os registros
     */
    public static function all()
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->table}";
        return $model->db->fetchAll($sql);
    }
    
    /**
     * Busca um registro por ID
     */
    public static function find($id)
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->table} WHERE {$model->primaryKey} = :id";
        return $model->db->fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Busca registros com condições
     */
    public static function where($column, $operator, $value = null)
    {
        $model = new static();
        
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$model->table} WHERE {$column} {$operator} :value";
        return $model->db->fetchAll($sql, ['value' => $value]);
    }
    
    /**
     * Cria um novo registro
     */
    public static function create($data)
    {
        $model = new static();
        
        // Filtra apenas campos fillable
        $data = array_intersect_key($data, array_flip($model->fillable));
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$model->table} ({$columns}) VALUES ({$placeholders})";
        $model->db->execute($sql, $data);
        
        return $model->db->lastInsertId();
    }
    
    /**
     * Atualiza um registro
     */
    public static function update($id, $data)
    {
        $model = new static();
        
        // Filtra apenas campos fillable
        $data = array_intersect_key($data, array_flip($model->fillable));
        
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$model->table} SET {$set} WHERE {$model->primaryKey} = :id";
        $data['id'] = $id;
        
        return $model->db->execute($sql, $data);
    }
    
    /**
     * Deleta um registro
     */
    public static function delete($id)
    {
        $model = new static();
        $sql = "DELETE FROM {$model->table} WHERE {$model->primaryKey} = :id";
        return $model->db->execute($sql, ['id' => $id]);
    }
    
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

