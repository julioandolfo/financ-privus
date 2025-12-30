<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_022_CreateIntegracoesConfig extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "tipo ENUM('banco_dados', 'woocommerce', 'api') NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "configuracoes JSON NOT NULL",
            "ultima_sincronizacao DATETIME NULL",
            "proxima_sincronizacao DATETIME NULL",
            "intervalo_sincronizacao INT DEFAULT 60",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('integracoes_config', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('integracoes_config', 'idx_empresa', ['empresa_id']);
        $this->addIndex('integracoes_config', 'idx_tipo', ['tipo']);
        $this->addIndex('integracoes_config', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS integracoes_config");
    }
}

