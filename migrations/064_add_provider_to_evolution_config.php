<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_064_AddProviderToEvolutionConfig extends BaseMigration
{
    public function up()
    {
        // Adiciona coluna provider (Evolution ou QuePasa)
        $this->execute("
            ALTER TABLE evolution_config
            ADD COLUMN provider ENUM('evolution','quepasa') NOT NULL DEFAULT 'evolution'
            COMMENT 'Provedor da API WhatsApp' AFTER nome
        ");

        // QuePasa não usa instance_name → torna NULLable
        $this->execute("
            ALTER TABLE evolution_config
            MODIFY instance_name VARCHAR(150) NULL
            COMMENT 'Nome da instância (Evolution). Não usado pelo QuePasa.'
        ");

        $this->execute("
            ALTER TABLE evolution_config
            ADD INDEX idx_provider (provider)
        ");

        echo "Coluna 'provider' adicionada em evolution_config.\n";
    }

    public function down()
    {
        $this->execute("ALTER TABLE evolution_config DROP INDEX idx_provider");
        $this->execute("ALTER TABLE evolution_config DROP COLUMN provider");
        $this->execute("ALTER TABLE evolution_config MODIFY instance_name VARCHAR(150) NOT NULL");
    }
}
