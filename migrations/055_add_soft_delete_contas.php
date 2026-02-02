<?php
/**
 * Migration: Adicionar Soft Delete nas Contas a Pagar e Receber
 * 
 * Adiciona:
 * - Campo deleted_at para soft delete
 * - Campos de auditoria para rastreio de quem deletou/restaurou
 */

require_once __DIR__ . '/../includes/Migration.php';

class AddSoftDeleteContas extends Migration
{
    public function up()
    {
        echo "Adicionando soft delete nas contas a pagar...\n";
        
        // Contas a Pagar
        $sql = "ALTER TABLE contas_pagar 
                ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
                ADD COLUMN deleted_by INT NULL,
                ADD COLUMN deleted_reason TEXT NULL,
                ADD INDEX idx_deleted_at (deleted_at)";
        $this->db->exec($sql);
        
        echo "Adicionando soft delete nas contas a receber...\n";
        
        // Contas a Receber
        $sql = "ALTER TABLE contas_receber 
                ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
                ADD COLUMN deleted_by INT NULL,
                ADD COLUMN deleted_reason TEXT NULL,
                ADD INDEX idx_deleted_at (deleted_at)";
        $this->db->exec($sql);
        
        echo "Soft delete adicionado com sucesso!\n";
    }
    
    public function down()
    {
        echo "Removendo soft delete das contas...\n";
        
        $sql1 = "ALTER TABLE contas_pagar 
                 DROP COLUMN deleted_at, 
                 DROP COLUMN deleted_by, 
                 DROP COLUMN deleted_reason,
                 DROP INDEX idx_deleted_at";
        $this->db->exec($sql1);
        
        $sql2 = "ALTER TABLE contas_receber 
                 DROP COLUMN deleted_at, 
                 DROP COLUMN deleted_by, 
                 DROP COLUMN deleted_reason,
                 DROP INDEX idx_deleted_at";
        $this->db->exec($sql2);
        
        echo "Soft delete removido!\n";
    }
}

// Executar migration
$migration = new AddSoftDeleteContas();
$migration->run();
