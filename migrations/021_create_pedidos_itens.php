<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_021_CreatePedidosItens extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "pedido_id INT NOT NULL",
            "produto_id INT NULL",
            "codigo_produto_origem VARCHAR(100) NULL",
            "nome_produto VARCHAR(255) NOT NULL",
            "quantidade DECIMAL(10,3) NOT NULL",
            "valor_unitario DECIMAL(15,2) NOT NULL",
            "valor_total DECIMAL(15,2) NOT NULL",
            "custo_unitario DECIMAL(15,2) DEFAULT 0",
            "custo_total DECIMAL(15,2) DEFAULT 0",
            "FOREIGN KEY (pedido_id) REFERENCES pedidos_vinculados(id) ON DELETE CASCADE",
            "FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('pedidos_itens', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('pedidos_itens', 'idx_pedido', ['pedido_id']);
        $this->addIndex('pedidos_itens', 'idx_produto', ['produto_id']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS pedidos_itens");
    }
}

