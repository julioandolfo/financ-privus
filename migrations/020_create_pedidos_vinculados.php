<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_020_CreatePedidosVinculados extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "origem VARCHAR(50) NOT NULL",
            "origem_id VARCHAR(100) NOT NULL",
            "numero_pedido VARCHAR(100) NOT NULL",
            "cliente_id INT NULL",
            "data_pedido DATETIME NOT NULL",
            "data_atualizacao DATETIME NOT NULL",
            "status VARCHAR(50) NOT NULL",
            "valor_total DECIMAL(15,2) NOT NULL",
            "valor_custo_total DECIMAL(15,2) DEFAULT 0",
            "dados_origem JSON NULL",
            "sincronizado_em DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('pedidos_vinculados', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('pedidos_vinculados', 'idx_empresa', ['empresa_id']);
        $this->addIndex('pedidos_vinculados', 'idx_origem', ['origem', 'origem_id']);
        $this->addIndex('pedidos_vinculados', 'idx_cliente', ['cliente_id']);
        $this->addIndex('pedidos_vinculados', 'idx_status', ['status']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS pedidos_vinculados");
    }
}

