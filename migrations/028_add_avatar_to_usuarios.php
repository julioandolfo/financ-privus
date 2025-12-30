<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_028_AddAvatarToUsuarios extends BaseMigration
{
    public function up()
    {
        // Adiciona coluna avatar
        $this->addColumn('usuarios', 'avatar', 'VARCHAR(255) NULL AFTER email');
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE usuarios DROP COLUMN avatar");
    }
}

