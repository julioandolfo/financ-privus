<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_010_CreateClientes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "tipo ENUM('fisica', 'juridica') NOT NULL",
            "nome_razao_social VARCHAR(255) NOT NULL",
            "cpf_cnpj VARCHAR(18) NOT NULL",
            "email VARCHAR(255) NULL",
            "telefone VARCHAR(20) NULL",
            "endereco JSON NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('clientes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('clientes', 'idx_empresa', ['empresa_id']);
        $this->addIndex('clientes', 'idx_cpf_cnpj', ['cpf_cnpj']);
        $this->addIndex('clientes', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS clientes");
    }
}

