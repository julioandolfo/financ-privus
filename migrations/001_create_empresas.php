<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_001_CreateEmpresas extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "codigo VARCHAR(20) NOT NULL UNIQUE",
            "razao_social VARCHAR(255) NOT NULL",
            "nome_fantasia VARCHAR(255) NOT NULL",
            "cnpj VARCHAR(18) UNIQUE",
            "grupo_empresarial_id INT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "configuracoes JSON NULL"
        ];
        
        $this->createTable('empresas', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('empresas', 'idx_cnpj', ['cnpj']);
        $this->addIndex('empresas', 'idx_grupo', ['grupo_empresarial_id']);
        $this->addIndex('empresas', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS empresas");
    }
}

