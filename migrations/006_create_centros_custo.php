<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_006_CreateCentrosCusto extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "codigo VARCHAR(20) NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "centro_pai_id INT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (centro_pai_id) REFERENCES centros_custo(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('centros_custo', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('centros_custo', 'idx_empresa', ['empresa_id']);
        $this->addIndex('centros_custo', 'idx_codigo', ['codigo']);
        $this->addIndex('centros_custo', 'idx_centro_pai', ['centro_pai_id']);
        $this->addIndex('centros_custo', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS centros_custo");
    }
}

