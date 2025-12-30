<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_018_CreateConciliacaoBancaria extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "conta_bancaria_id INT NOT NULL",
            "data_inicio DATE NOT NULL",
            "data_fim DATE NOT NULL",
            "saldo_extrato DECIMAL(15,2) NOT NULL",
            "saldo_sistema DECIMAL(15,2) NOT NULL",
            "diferenca DECIMAL(15,2) NOT NULL",
            "status ENUM('aberta', 'fechada') DEFAULT 'aberta'",
            "observacoes TEXT NULL",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE RESTRICT"
        ];
        
        $this->createTable('conciliacao_bancaria', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Adiciona foreign key para conciliacao_id em movimentacoes_caixa após criar a tabela
        $this->execute("ALTER TABLE movimentacoes_caixa ADD CONSTRAINT fk_movimentacoes_conciliacao FOREIGN KEY (conciliacao_id) REFERENCES conciliacao_bancaria(id) ON DELETE SET NULL");
        
        // Índices
        $this->addIndex('conciliacao_bancaria', 'idx_empresa', ['empresa_id']);
        $this->addIndex('conciliacao_bancaria', 'idx_conta_bancaria', ['conta_bancaria_id']);
        $this->addIndex('conciliacao_bancaria', 'idx_status', ['status']);
        $this->addIndex('conciliacao_bancaria', 'idx_data_periodo', ['data_inicio', 'data_fim']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS conciliacao_bancaria");
    }
}

