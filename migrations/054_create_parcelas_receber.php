<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

/**
 * Tabela de controle de parcelas de contas a receber
 * Permite gerenciar parcelas individualmente para uma mesma conta
 */
class Migration_054_CreateParcelasReceber extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "conta_receber_id INT NOT NULL COMMENT 'Conta a receber principal'",
            "empresa_id INT NOT NULL",
            "numero_parcela INT NOT NULL COMMENT 'Número da parcela (1, 2, 3...)'",
            "valor_parcela DECIMAL(15,2) NOT NULL",
            "valor_recebido DECIMAL(15,2) DEFAULT 0",
            "desconto DECIMAL(15,2) DEFAULT 0",
            "juros DECIMAL(15,2) DEFAULT 0",
            "multa DECIMAL(15,2) DEFAULT 0",
            "data_vencimento DATE NOT NULL",
            "data_recebimento DATE NULL",
            "status ENUM('pendente', 'recebido', 'vencido', 'cancelado', 'parcial') DEFAULT 'pendente'",
            "forma_recebimento_id INT NULL",
            "conta_bancaria_id INT NULL",
            "observacoes TEXT NULL",
            "created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP",
            "FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (forma_recebimento_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('parcelas_receber', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('parcelas_receber', 'idx_conta_receber', ['conta_receber_id']);
        $this->addIndex('parcelas_receber', 'idx_empresa', ['empresa_id']);
        $this->addIndex('parcelas_receber', 'idx_status', ['status']);
        $this->addIndex('parcelas_receber', 'idx_data_vencimento', ['data_vencimento']);
        $this->addIndex('parcelas_receber', 'idx_data_recebimento', ['data_recebimento']);
        $this->addIndex('parcelas_receber', 'idx_numero_parcela', ['conta_receber_id', 'numero_parcela']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS parcelas_receber");
    }
}
