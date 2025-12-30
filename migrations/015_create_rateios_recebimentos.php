<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_015_CreateRateiosRecebimentos extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "conta_receber_id INT NOT NULL",
            "empresa_id INT NOT NULL",
            "valor_rateio DECIMAL(15,2) NOT NULL",
            "percentual DECIMAL(5,2) NOT NULL",
            "data_competencia DATE NOT NULL",
            "observacoes TEXT NULL",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "usuario_cadastro_id INT NOT NULL",
            "FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (usuario_cadastro_id) REFERENCES usuarios(id) ON DELETE RESTRICT",
            "CHECK (valor_rateio >= 0)",
            "CHECK (percentual >= 0 AND percentual <= 100)"
        ];
        
        $this->createTable('rateios_recebimentos', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('rateios_recebimentos', 'idx_conta_receber', ['conta_receber_id']);
        $this->addIndex('rateios_recebimentos', 'idx_empresa', ['empresa_id']);
        $this->addIndex('rateios_recebimentos', 'idx_data_competencia', ['data_competencia']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS rateios_recebimentos");
    }
}

