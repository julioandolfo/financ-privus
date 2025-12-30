<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe de conexão com banco de dados usando PDO
 * Singleton pattern para garantir uma única conexão
 */
class Database
{
    private static $instance = null;
    private $connection;
    
    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $options
            );
        } catch (PDOException $e) {
            $errorMsg = "Erro ao conectar ao banco de dados: " . $e->getMessage();
            $errorMsg .= "\nHost: " . $config['host'];
            $errorMsg .= "\nDatabase: " . $config['database'];
            $errorMsg .= "\nUsername: " . $config['username'];
            throw new \Exception($errorMsg);
        }
    }
    
    /**
     * Retorna instância única da conexão
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Retorna a conexão PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne unserialize da instância
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
    
    /**
     * Executa uma query e retorna o statement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Erro ao executar query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma query e retorna todos os resultados
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Executa uma query e retorna um único resultado
     */
    public function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Executa uma query e retorna o número de linhas afetadas
     */
    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit()
    {
        return $this->connection->commit();
    }
    
    /**
     * Reverte uma transação
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}

