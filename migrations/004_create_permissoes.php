<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_004_CreatePermissoes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "usuario_id INT NOT NULL",
            "modulo VARCHAR(50) NOT NULL",
            "acao VARCHAR(50) NOT NULL",
            "empresa_id INT NULL",
            "FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('permissoes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('permissoes', 'idx_usuario', ['usuario_id']);
        $this->addIndex('permissoes', 'idx_empresa', ['empresa_id']);
        $this->addIndex('permissoes', 'idx_modulo_acao', ['modulo', 'acao']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS permissoes");
    }
}

