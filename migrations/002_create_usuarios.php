<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_002_CreateUsuarios extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NULL",
            "nome VARCHAR(255) NOT NULL",
            "email VARCHAR(255) NOT NULL UNIQUE",
            "senha VARCHAR(255) NOT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ultimo_acesso DATETIME NULL",
            "empresas_consolidadas_padrao JSON NULL",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('usuarios', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('usuarios', 'idx_email', ['email']);
        $this->addIndex('usuarios', 'idx_empresa', ['empresa_id']);
        $this->addIndex('usuarios', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS usuarios");
    }
}

