<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_012_CreateContasPagar extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "fornecedor_id INT NULL",
            "categoria_id INT NOT NULL",
            "centro_custo_id INT NULL",
            "numero_documento VARCHAR(100) NOT NULL",
            "descricao TEXT NOT NULL",
            "valor_total DECIMAL(15,2) NOT NULL",
            "valor_pago DECIMAL(15,2) DEFAULT 0",
            "data_emissao DATE NOT NULL",
            "data_competencia DATE NOT NULL",
            "data_vencimento DATE NOT NULL",
            "data_pagamento DATE NULL",
            "status ENUM('pendente', 'pago', 'vencido', 'cancelado', 'parcial') DEFAULT 'pendente'",
            "forma_pagamento_id INT NULL",
            "conta_bancaria_id INT NULL",
            "tem_rateio BOOLEAN DEFAULT 0",
            "observacoes TEXT NULL",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "usuario_cadastro_id INT NOT NULL",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL",
            "FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE RESTRICT",
            "FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id) ON DELETE SET NULL",
            "FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL",
            "FOREIGN KEY (usuario_cadastro_id) REFERENCES usuarios(id) ON DELETE RESTRICT"
        ];
        
        $this->createTable('contas_pagar', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('contas_pagar', 'idx_empresa', ['empresa_id']);
        $this->addIndex('contas_pagar', 'idx_fornecedor', ['fornecedor_id']);
        $this->addIndex('contas_pagar', 'idx_categoria', ['categoria_id']);
        $this->addIndex('contas_pagar', 'idx_status', ['status']);
        $this->addIndex('contas_pagar', 'idx_data_vencimento', ['data_vencimento']);
        $this->addIndex('contas_pagar', 'idx_data_competencia', ['data_competencia']);
        $this->addIndex('contas_pagar', 'idx_data_pagamento', ['data_pagamento']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS contas_pagar");
    }
}

