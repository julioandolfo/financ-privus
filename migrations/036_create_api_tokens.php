<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_036_CreateApiTokens extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NULL",
            "usuario_id INT NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "token VARCHAR(64) NOT NULL UNIQUE",
            "permissoes JSON NULL COMMENT 'Permissões específicas do token'",
            "ip_whitelist JSON NULL COMMENT 'IPs permitidos'",
            "rate_limit INT DEFAULT 1000 COMMENT 'Requests por hora'",
            "ultimo_uso TIMESTAMP NULL",
            "expira_em TIMESTAMP NULL",
            "ativo BOOLEAN DEFAULT 1",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('api_tokens', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        // Índices são criados automaticamente para UNIQUE e FOREIGN KEY
        $this->addIndex('api_tokens', 'idx_ativo', 'ativo');
        
        echo "Tabela 'api_tokens' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS api_tokens");
        echo "Tabela 'api_tokens' removida com sucesso!\n";
    }
}
