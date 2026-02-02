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
        $this->addColumn('contas_pagar', [
            'deleted_at TIMESTAMP NULL DEFAULT NULL',
            'deleted_by INT NULL',
            'deleted_reason TEXT NULL',
            'INDEX idx_deleted_at (deleted_at)'
        ]);
        
        echo "Adicionando soft delete nas contas a receber...\n";
        
        // Contas a Receber
        $this->addColumn('contas_receber', [
            'deleted_at TIMESTAMP NULL DEFAULT NULL',
            'deleted_by INT NULL',
            'deleted_reason TEXT NULL',
            'INDEX idx_deleted_at (deleted_at)'
        ]);
        
        echo "Soft delete adicionado com sucesso!\n";
    }
    
    public function down()
    {
        echo "Removendo soft delete das contas...\n";
        
        $this->dropColumn('contas_pagar', ['deleted_at', 'deleted_by', 'deleted_reason']);
        $this->dropColumn('contas_receber', ['deleted_at', 'deleted_by', 'deleted_reason']);
        
        echo "Soft delete removido!\n";
    }
}

// Executar migration
$migration = new AddSoftDeleteContas();
$migration->run();
