<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_000_CreateMigrationsTable extends BaseMigration
{
    public function up()
    {
        // Esta migration é criada automaticamente pelo MigrationManager
        // Não precisa fazer nada aqui
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS migrations");
    }
}

