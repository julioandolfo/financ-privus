<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_017_CreateFormasPagamentoPadroes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "forma_pagamento_id INT NOT NULL",
            "origem VARCHAR(100) NOT NULL",
            "descricao_padrao VARCHAR(255) NULL",
            "fornecedor_id INT NULL",
            "cliente_id INT NULL",
            "categoria_id INT NULL",
            "confianca DECIMAL(5,2) DEFAULT 0",
            "quantidade_uso INT DEFAULT 0",
            "quantidade_acerto INT DEFAULT 0",
            "ultimo_uso DATETIME NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento(id) ON DELETE CASCADE",
            "FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL",
            "FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL",
            "FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('formas_pagamento_padroes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('formas_pagamento_padroes', 'idx_empresa', ['empresa_id']);
        $this->addIndex('formas_pagamento_padroes', 'idx_forma_pagamento', ['forma_pagamento_id']);
        $this->addIndex('formas_pagamento_padroes', 'idx_origem', ['origem']);
        $this->addIndex('formas_pagamento_padroes', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS formas_pagamento_padroes");
    }
}

