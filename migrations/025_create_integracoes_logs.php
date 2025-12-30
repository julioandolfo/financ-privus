<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_025_CreateIntegracoesLogs extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "integracao_id INT NOT NULL",
            "tipo ENUM('sucesso', 'erro', 'aviso') NOT NULL",
            "mensagem TEXT NOT NULL",
            "dados JSON NULL",
            "data_execucao DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('integracoes_logs', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('integracoes_logs', 'idx_integracao', ['integracao_id']);
        $this->addIndex('integracoes_logs', 'idx_tipo', ['tipo']);
        $this->addIndex('integracoes_logs', 'idx_data_execucao', ['data_execucao']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS integracoes_logs");
    }
}

