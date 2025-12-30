<?php
namespace includes;

/**
 * Classe base para migrations
 * 
 * Todas as migrations devem estender esta classe e implementar
 * os métodos up() e down()
 */
abstract class Migration
{
    protected $db;
    protected $tableName = 'migrations';
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Executa a migration (cria/modifica tabelas)
     */
    abstract public function up();
    
    /**
     * Reverte a migration (rollback)
     */
    abstract public function down();
    
    /**
     * Retorna o nome da migration (nome do arquivo sem extensão)
     */
    public function getName()
    {
        $reflection = new ReflectionClass($this);
        return basename($reflection->getFileName(), '.php');
    }
    
    /**
     * Executa uma query SQL
     */
    protected function execute($sql)
    {
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erro ao executar migration: " . $e->getMessage());
        }
    }
    
    /**
     * Cria uma tabela
     */
    protected function createTable($tableName, $columns, $options = [])
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
        $sql .= implode(", ", $columns);
        $sql .= ")";
        
        if (isset($options['engine'])) {
            $sql .= " ENGINE={$options['engine']}";
        }
        
        if (isset($options['charset'])) {
            $sql .= " DEFAULT CHARSET={$options['charset']}";
        }
        
        if (isset($options['collate'])) {
            $sql .= " COLLATE={$options['collate']}";
        }
        
        $this->execute($sql);
    }
    
    /**
     * Adiciona uma coluna a uma tabela existente
     */
    protected function addColumn($tableName, $columnName, $definition)
    {
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$definition}";
        $this->execute($sql);
    }
    
    /**
     * Remove uma coluna de uma tabela existente
     */
    protected function dropColumn($tableName, $columnName)
    {
        $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`";
        $this->execute($sql);
    }
    
    /**
     * Adiciona um índice
     */
    protected function addIndex($tableName, $indexName, $columns, $unique = false)
    {
        $type = $unique ? 'UNIQUE' : 'INDEX';
        $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;
        $sql = "ALTER TABLE `{$tableName}` ADD {$type} `{$indexName}` ({$columnsStr})";
        $this->execute($sql);
    }
    
    /**
     * Remove um índice
     */
    protected function dropIndex($tableName, $indexName)
    {
        $sql = "ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`";
        $this->execute($sql);
    }
    
    /**
     * Adiciona uma foreign key
     */
    protected function addForeignKey($tableName, $constraintName, $column, $refTable, $refColumn, $onDelete = 'RESTRICT', $onUpdate = 'RESTRICT')
    {
        $sql = "ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$constraintName}` 
                FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) 
                ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
        $this->execute($sql);
    }
    
    /**
     * Remove uma foreign key
     */
    protected function dropForeignKey($tableName, $constraintName)
    {
        $sql = "ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`";
        $this->execute($sql);
    }
}

