<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_013_CreateContasReceber extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "cliente_id INT NULL",
            "categoria_id INT NOT NULL",
            "centro_custo_id INT NULL",
            "numero_documento VARCHAR(100) NOT NULL",
            "descricao TEXT NOT NULL",
            "valor_total DECIMAL(15,2) NOT NULL",
            "valor_recebido DECIMAL(15,2) DEFAULT 0",
            "data_emissao DATE NOT NULL",
            "data_competencia DATE NOT NULL",
            "data_vencimento DATE NOT NULL",
            "data_recebimento DATE NULL",
            "status ENUM('pendente', 'recebido', 'vencido', 'cancelado', 'parcial') DEFAULT 'pendente'",
            "forma_recebimento_id INT NULL",
            "conta_bancaria_id INT NULL",
            "tem_rateio BOOLEAN DEFAULT 0",
            "observacoes TEXT NULL",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "usuario_cadastro_id INT NOT NULL",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL",
            "FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE RESTRICT",
            "FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id) ON DELETE SET NULL",
            "FOREIGN KEY (forma_recebimento_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL",
            "FOREIGN KEY (usuario_cadastro_id) REFERENCES usuarios(id) ON DELETE RESTRICT"
        ];
        
        $this->createTable('contas_receber', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('contas_receber', 'idx_empresa', ['empresa_id']);
        $this->addIndex('contas_receber', 'idx_cliente', ['cliente_id']);
        $this->addIndex('contas_receber', 'idx_categoria', ['categoria_id']);
        $this->addIndex('contas_receber', 'idx_status', ['status']);
        $this->addIndex('contas_receber', 'idx_data_vencimento', ['data_vencimento']);
        $this->addIndex('contas_receber', 'idx_data_competencia', ['data_competencia']);
        $this->addIndex('contas_receber', 'idx_data_recebimento', ['data_recebimento']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS contas_receber");
    }
}

