<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_050_AddPedidoIdToContasReceber extends BaseMigration
{
    public function up()
    {
        // Adiciona campo pedido_id à tabela contas_receber
        $this->execute("ALTER TABLE contas_receber ADD COLUMN pedido_id INT NULL AFTER cliente_id");
        $this->execute("ALTER TABLE contas_receber ADD CONSTRAINT fk_conta_receber_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos_vinculados(id) ON DELETE SET NULL");
        
        // Adiciona índice
        $this->addIndex('contas_receber', 'idx_pedido', ['pedido_id']);
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE contas_receber DROP FOREIGN KEY fk_conta_receber_pedido");
        $this->execute("ALTER TABLE contas_receber DROP INDEX idx_pedido");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN pedido_id");
    }
}
