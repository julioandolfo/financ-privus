<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_011_CreateProdutos extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "codigo VARCHAR(50) NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "descricao TEXT NULL",
            "custo_unitario DECIMAL(15,2) DEFAULT 0",
            "preco_venda DECIMAL(15,2) DEFAULT 0",
            "unidade_medida VARCHAR(20) DEFAULT 'UN'",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('produtos', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('produtos', 'idx_empresa', ['empresa_id']);
        $this->addIndex('produtos', 'idx_codigo', ['codigo']);
        $this->addIndex('produtos', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS produtos");
    }
}

