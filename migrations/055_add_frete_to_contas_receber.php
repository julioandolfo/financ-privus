<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_055_AddFreteToContasReceber extends BaseMigration
{
    public function up()
    {
        // Adicionar campo frete
        $this->execute("ALTER TABLE contas_receber ADD COLUMN frete DECIMAL(15,2) DEFAULT 0 AFTER desconto");
        
        // Adicionar campo frete também na tabela de parcelas
        $this->execute("ALTER TABLE parcelas_receber ADD COLUMN frete DECIMAL(15,2) DEFAULT 0 AFTER desconto");
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE parcelas_receber DROP COLUMN frete");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN frete");
    }
}
