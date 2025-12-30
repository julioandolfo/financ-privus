<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_005_CreateCategoriasFinanceiras extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "codigo VARCHAR(20) NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "tipo ENUM('receita', 'despesa') NOT NULL",
            "categoria_pai_id INT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (categoria_pai_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('categorias_financeiras', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('categorias_financeiras', 'idx_empresa', ['empresa_id']);
        $this->addIndex('categorias_financeiras', 'idx_codigo', ['codigo']);
        $this->addIndex('categorias_financeiras', 'idx_tipo', ['tipo']);
        $this->addIndex('categorias_financeiras', 'idx_categoria_pai', ['categoria_pai_id']);
        $this->addIndex('categorias_financeiras', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS categorias_financeiras");
    }
}

