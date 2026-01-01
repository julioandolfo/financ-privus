<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_037_CreateApiLogs extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "api_token_id INT NULL",
            "metodo VARCHAR(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE'",
            "endpoint VARCHAR(255) NOT NULL",
            "parametros TEXT NULL",
            "body TEXT NULL",
            "status_code INT NOT NULL",
            "resposta TEXT NULL",
            "ip VARCHAR(45) NOT NULL",
            "user_agent VARCHAR(512) NULL",
            "tempo_resposta FLOAT NULL COMMENT 'Tempo em segundos'",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (api_token_id) REFERENCES api_tokens(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('api_logs', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        // FK já cria índice automaticamente
        $this->addIndex('api_logs', 'idx_created_at', 'created_at');
        $this->addIndex('api_logs', 'idx_status_code', 'status_code');
        
        echo "Tabela 'api_logs' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS api_logs");
        echo "Tabela 'api_logs' removida com sucesso!\n";
    }
}
