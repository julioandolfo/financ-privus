<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_007_CreateContasBancarias extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "banco_codigo VARCHAR(10) NOT NULL",
            "banco_nome VARCHAR(255) NOT NULL",
            "agencia VARCHAR(20) NOT NULL",
            "conta VARCHAR(20) NOT NULL",
            "tipo_conta ENUM('corrente', 'poupanca', 'investimento') DEFAULT 'corrente'",
            "saldo_inicial DECIMAL(15,2) DEFAULT 0",
            "saldo_atual DECIMAL(15,2) DEFAULT 0",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('contas_bancarias', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('contas_bancarias', 'idx_empresa', ['empresa_id']);
        $this->addIndex('contas_bancarias', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS contas_bancarias");
    }
}

