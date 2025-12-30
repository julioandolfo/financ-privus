<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_003_CreatePerfisConsolidacao extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "usuario_id INT NULL",
            "nome VARCHAR(255) NOT NULL",
            "empresas_ids JSON NOT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('perfis_consolidacao', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('perfis_consolidacao', 'idx_usuario', ['usuario_id']);
        $this->addIndex('perfis_consolidacao', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS perfis_consolidacao");
    }
}

