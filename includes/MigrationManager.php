<?php
namespace includes;

require_once __DIR__ . '/Migration.php';

use App\Core\Database;
use PDO;
use PDOException;

/**
 * Gerenciador de migrations
 */
class MigrationManager
{
    private $db;
    private $migrationsPath;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->migrationsPath = __DIR__ . '/../migrations';
        
        // Cria tabela de controle se não existir
        $this->createMigrationsTable();
    }
    
    /**
     * Cria tabela de controle de migrations
     */
    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            execution_time DECIMAL(10,3) DEFAULT 0,
            status ENUM('success', 'failed') DEFAULT 'success',
            error_message TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->exec($sql);
    }
    
    /**
     * Retorna migrations já executadas
     */
    private function getExecutedMigrations()
    {
        $stmt = $this->db->query("SELECT migration_name FROM migrations WHERE status = 'success'");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Retorna todas as migrations disponíveis
     */
    private function getAvailableMigrations()
    {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Retorna migrations pendentes
     */
    public function getPendingMigrations()
    {
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();
        
        return array_diff($available, $executed);
    }
    
    /**
     * Executa migrations pendentes
     */
    public function run()
    {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            echo "Nenhuma migration pendente.\n";
            return;
        }
        
        echo "Executando " . count($pending) . " migration(s)...\n\n";
        
        foreach ($pending as $migrationName) {
            $this->runMigration($migrationName);
        }
        
        echo "\nMigrations executadas com sucesso!\n";
    }
    
    /**
     * Executa uma migration específica
     */
    private function runMigration($migrationName)
    {
        $startTime = microtime(true);
        
        try {
            echo "Executando: {$migrationName}... ";
            
            require_once $this->migrationsPath . '/' . $migrationName . '.php';
            
            // Converte nome do arquivo para nome de classe
            // Exemplo: 001_create_empresas -> Migration_001_CreateEmpresas
            // Mantém underscore apenas após números, resto vira PascalCase sem separadores
            $parts = explode('_', $migrationName);
            $classNameParts = [];
            $currentPart = '';
            foreach ($parts as $part) {
                if (is_numeric($part)) {
                    if ($currentPart) {
                        $classNameParts[] = ucfirst($currentPart);
                        $currentPart = '';
                    }
                    $classNameParts[] = $part;
                } else {
                    $currentPart .= ($currentPart ? '_' : '') . $part;
                }
            }
            if ($currentPart) {
                $classNameParts[] = ucfirst(str_replace('_', '', ucwords($currentPart, '_')));
            }
            $className = 'Migration_' . implode('_', $classNameParts);
            
            // Tenta instanciar a classe (pode estar no namespace includes ou global)
            if (class_exists($className)) {
                $migration = new $className($this->db);
            } else {
                throw new \Exception("Classe {$className} não encontrada. Arquivo: {$migrationName}.php");
            }
            
            $migration->up();
            
            $executionTime = microtime(true) - $startTime;
            
            // Registra migration executada
            $stmt = $this->db->prepare("
                INSERT INTO migrations (migration_name, execution_time, status) 
                VALUES (:name, :time, 'success')
            ");
            $stmt->execute([
                'name' => $migrationName,
                'time' => $executionTime
            ]);
            
            echo "OK (" . number_format($executionTime, 3) . "s)\n";
            
        } catch (Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Registra falha
            $stmt = $this->db->prepare("
                INSERT INTO migrations (migration_name, execution_time, status, error_message) 
                VALUES (:name, :time, 'failed', :error)
            ");
            $stmt->execute([
                'name' => $migrationName,
                'time' => $executionTime,
                'error' => $e->getMessage()
            ]);
            
            echo "ERRO: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Faz rollback da última migration
     */
    public function rollback($steps = 1)
    {
        $stmt = $this->db->query("
            SELECT migration_name FROM migrations 
            WHERE status = 'success' 
            ORDER BY executed_at DESC 
            LIMIT {$steps}
        ");
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($migrations)) {
            echo "Nenhuma migration para reverter.\n";
            return;
        }
        
        foreach ($migrations as $migrationName) {
            $this->rollbackMigration($migrationName);
        }
    }
    
    /**
     * Reverte uma migration específica
     */
    private function rollbackMigration($migrationName)
    {
        try {
            echo "Revertendo: {$migrationName}... ";
            
            require_once $this->migrationsPath . '/' . $migrationName . '.php';
            
            // Converte nome do arquivo para nome de classe
            // Exemplo: 001_create_empresas -> Migration_001_CreateEmpresas
            $className = 'Migration_' . str_replace('_', '', ucwords($migrationName, '_'));
            
            // Tenta instanciar a classe (pode estar no namespace includes ou global)
            if (class_exists($className)) {
                $migration = new $className($this->db);
            } else {
                throw new \Exception("Classe {$className} não encontrada. Arquivo: {$migrationName}.php");
            }
            
            $migration->down();
            
            // Remove registro
            $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration_name = :name");
            $stmt->execute(['name' => $migrationName]);
            
            echo "OK\n";
            
        } catch (Exception $e) {
            echo "ERRO: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Mostra status das migrations
     */
    public function status()
    {
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();
        $pending = array_diff($available, $executed);
        
        echo "Migrations Executadas: " . count($executed) . "\n";
        echo "Migrations Pendentes: " . count($pending) . "\n\n";
        
        if (!empty($pending)) {
            echo "Pendentes:\n";
            foreach ($pending as $migration) {
                echo "  - {$migration}\n";
            }
        }
    }
}

