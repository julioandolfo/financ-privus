<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_026_CreateIntegracoesSincronizacoes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "integracao_id INT NOT NULL",
            "tipo_sincronizacao VARCHAR(100) NOT NULL",
            "registros_processados INT DEFAULT 0",
            "registros_inseridos INT DEFAULT 0",
            "registros_atualizados INT DEFAULT 0",
            "registros_erros INT DEFAULT 0",
            "tempo_execucao INT DEFAULT 0",
            "status ENUM('sucesso', 'erro', 'parcial') DEFAULT 'sucesso'",
            "data_inicio DATETIME DEFAULT CURRENT_TIMESTAMP",
            "data_fim DATETIME NULL",
            "log_erros JSON NULL",
            "FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('integracoes_sincronizacoes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('integracoes_sincronizacoes', 'idx_integracao', ['integracao_id']);
        $this->addIndex('integracoes_sincronizacoes', 'idx_status', ['status']);
        $this->addIndex('integracoes_sincronizacoes', 'idx_data_inicio', ['data_inicio']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS integracoes_sincronizacoes");
    }
}

