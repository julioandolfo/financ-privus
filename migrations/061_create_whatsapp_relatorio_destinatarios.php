<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_061_CreateWhatsappRelatorioDestinatarios extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "regra_id INT NOT NULL",
            "nome VARCHAR(150) NOT NULL COMMENT 'Rótulo livre (ex: Diretor, Financeiro)'",
            "numero VARCHAR(20) NOT NULL COMMENT 'Número em formato E.164 sem + (ex: 5511999999999)'",
            "ativo BOOLEAN DEFAULT 1",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];

        $this->createTable('whatsapp_relatorio_destinatarios', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        $this->addIndex('whatsapp_relatorio_destinatarios', 'idx_regra', ['regra_id']);
        $this->addIndex('whatsapp_relatorio_destinatarios', 'idx_ativo', ['ativo']);

        $this->execute("
            ALTER TABLE whatsapp_relatorio_destinatarios
            ADD CONSTRAINT fk_whats_dest_regra
            FOREIGN KEY (regra_id) REFERENCES whatsapp_relatorio_regras(id) ON DELETE CASCADE
        ");

        echo "Tabela 'whatsapp_relatorio_destinatarios' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS whatsapp_relatorio_destinatarios");
        echo "Tabela 'whatsapp_relatorio_destinatarios' removida com sucesso!\n";
    }
}
