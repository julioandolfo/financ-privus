<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_008_CreateFormasPagamento extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "codigo VARCHAR(20) NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "tipo ENUM('pagamento', 'recebimento', 'ambos') DEFAULT 'ambos'",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('formas_pagamento', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('formas_pagamento', 'idx_empresa', ['empresa_id']);
        $this->addIndex('formas_pagamento', 'idx_codigo', ['codigo']);
        $this->addIndex('formas_pagamento', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS formas_pagamento");
    }
}

