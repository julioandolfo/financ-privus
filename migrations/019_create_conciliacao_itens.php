<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_019_CreateConciliacaoItens extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "conciliacao_id INT NOT NULL",
            "movimentacao_id INT NULL",
            "descricao_extrato TEXT NOT NULL",
            "valor_extrato DECIMAL(15,2) NOT NULL",
            "data_extrato DATE NOT NULL",
            "tipo_extrato ENUM('credito', 'debito') NOT NULL",
            "vinculado BOOLEAN DEFAULT 0",
            "forma_pagamento_sugerida_id INT NULL",
            "observacoes TEXT NULL",
            "FOREIGN KEY (conciliacao_id) REFERENCES conciliacao_bancaria(id) ON DELETE CASCADE",
            "FOREIGN KEY (movimentacao_id) REFERENCES movimentacoes_caixa(id) ON DELETE SET NULL",
            "FOREIGN KEY (forma_pagamento_sugerida_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('conciliacao_itens', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('conciliacao_itens', 'idx_conciliacao', ['conciliacao_id']);
        $this->addIndex('conciliacao_itens', 'idx_movimentacao', ['movimentacao_id']);
        $this->addIndex('conciliacao_itens', 'idx_vinculado', ['vinculado']);
        $this->addIndex('conciliacao_itens', 'idx_data_extrato', ['data_extrato']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS conciliacao_itens");
    }
}

