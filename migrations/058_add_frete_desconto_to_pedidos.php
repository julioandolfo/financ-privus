<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_058_AddFreteDescontoToPedidos extends BaseMigration
{
    public function up()
    {
        // Adicionar campo de frete
        $this->addColumn('pedidos_vinculados', 'frete', 'DECIMAL(15,2) DEFAULT 0 AFTER valor_custo_total');
        
        // Adicionar campo de desconto
        $this->addColumn('pedidos_vinculados', 'desconto', 'DECIMAL(15,2) DEFAULT 0 AFTER frete');
        
        // Adicionar observações
        $this->addColumn('pedidos_vinculados', 'observacoes', 'TEXT NULL AFTER desconto');
    }
    
    public function down()
    {
        $this->dropColumn('pedidos_vinculados', 'frete');
        $this->dropColumn('pedidos_vinculados', 'desconto');
        $this->dropColumn('pedidos_vinculados', 'observacoes');
    }
}
