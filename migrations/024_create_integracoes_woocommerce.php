<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_024_CreateIntegracoesWoocommerce extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "integracao_id INT NOT NULL",
            "url_site VARCHAR(255) NOT NULL",
            "consumer_key VARCHAR(255) NOT NULL",
            "consumer_secret VARCHAR(255) NOT NULL",
            "webhook_secret VARCHAR(255) NULL",
            "eventos_webhook JSON NULL",
            "sincronizar_produtos BOOLEAN DEFAULT 1",
            "sincronizar_pedidos BOOLEAN DEFAULT 1",
            "empresa_vinculada_id INT NOT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE",
            "FOREIGN KEY (empresa_vinculada_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('integracoes_woocommerce', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('integracoes_woocommerce', 'idx_integracao', ['integracao_id']);
        $this->addIndex('integracoes_woocommerce', 'idx_empresa', ['empresa_vinculada_id']);
        $this->addIndex('integracoes_woocommerce', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS integracoes_woocommerce");
    }
}

