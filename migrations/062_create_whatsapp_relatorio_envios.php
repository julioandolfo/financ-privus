<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_062_CreateWhatsappRelatorioEnvios extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "regra_id INT NOT NULL",
            "destinatario_id INT NULL COMMENT 'NULL se envio manual ad-hoc'",
            "evolution_config_id INT NOT NULL",
            "numero VARCHAR(20) NOT NULL",
            "mensagem MEDIUMTEXT NOT NULL",
            "status ENUM('aguardando','enviado','falha','cancelado') DEFAULT 'aguardando'",
            "tentativas TINYINT DEFAULT 0",
            "resposta_api JSON NULL",
            "erro TEXT NULL",
            "tipo_envio ENUM('automatico','manual','teste') DEFAULT 'automatico'",
            "agendado_para DATETIME NULL",
            "enviado_em DATETIME NULL",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ];

        $this->createTable('whatsapp_relatorio_envios', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        $this->addIndex('whatsapp_relatorio_envios', 'idx_regra', ['regra_id']);
        $this->addIndex('whatsapp_relatorio_envios', 'idx_status', ['status']);
        $this->addIndex('whatsapp_relatorio_envios', 'idx_created', ['created_at']);
        $this->addIndex('whatsapp_relatorio_envios', 'idx_enviado_em', ['enviado_em']);

        $this->execute("
            ALTER TABLE whatsapp_relatorio_envios
            ADD CONSTRAINT fk_whats_env_regra
            FOREIGN KEY (regra_id) REFERENCES whatsapp_relatorio_regras(id) ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE whatsapp_relatorio_envios
            ADD CONSTRAINT fk_whats_env_evolution
            FOREIGN KEY (evolution_config_id) REFERENCES evolution_config(id) ON DELETE RESTRICT
        ");

        echo "Tabela 'whatsapp_relatorio_envios' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS whatsapp_relatorio_envios");
        echo "Tabela 'whatsapp_relatorio_envios' removida com sucesso!\n";
    }
}
