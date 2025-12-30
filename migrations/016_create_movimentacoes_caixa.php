<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_016_CreateMovimentacoesCaixa extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "tipo ENUM('entrada', 'saida') NOT NULL",
            "categoria_id INT NOT NULL",
            "centro_custo_id INT NULL",
            "conta_bancaria_id INT NOT NULL",
            "descricao TEXT NOT NULL",
            "valor DECIMAL(15,2) NOT NULL",
            "data_movimentacao DATE NOT NULL",
            "data_competencia DATE NULL",
            "conciliado BOOLEAN DEFAULT 0",
            "conciliacao_id INT NULL",
            "referencia_id INT NULL",
            "referencia_tipo VARCHAR(20) NULL",
            "forma_pagamento_id INT NULL",
            "origem_movimento VARCHAR(100) NULL",
            "observacoes TEXT NULL",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE RESTRICT",
            "FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id) ON DELETE SET NULL",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE RESTRICT",
            "FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('movimentacoes_caixa', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('movimentacoes_caixa', 'idx_empresa', ['empresa_id']);
        $this->addIndex('movimentacoes_caixa', 'idx_tipo', ['tipo']);
        $this->addIndex('movimentacoes_caixa', 'idx_categoria', ['categoria_id']);
        $this->addIndex('movimentacoes_caixa', 'idx_conta_bancaria', ['conta_bancaria_id']);
        $this->addIndex('movimentacoes_caixa', 'idx_data_movimentacao', ['data_movimentacao']);
        $this->addIndex('movimentacoes_caixa', 'idx_data_competencia', ['data_competencia']);
        $this->addIndex('movimentacoes_caixa', 'idx_conciliado', ['conciliado']);
        $this->addIndex('movimentacoes_caixa', 'idx_referencia', ['referencia_tipo', 'referencia_id']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS movimentacoes_caixa");
    }
}

