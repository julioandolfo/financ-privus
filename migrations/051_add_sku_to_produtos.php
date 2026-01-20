<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_051_AddSkuToProdutos extends BaseMigration
{
    public function up()
    {
        // Adiciona campo SKU à tabela produtos
        $this->execute("ALTER TABLE produtos ADD COLUMN sku VARCHAR(100) NULL AFTER codigo");
        
        // Adiciona índice único para SKU por empresa
        $this->execute("CREATE UNIQUE INDEX idx_sku_empresa ON produtos(sku, empresa_id)");
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE produtos DROP INDEX idx_sku_empresa");
        $this->execute("ALTER TABLE produtos DROP COLUMN sku");
    }
}
