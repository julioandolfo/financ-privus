<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_045_CreateTransacoesPendentes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "conexao_bancaria_id INT NOT NULL",
            
            // Dados da transação
            "data_transacao DATE NOT NULL",
            "descricao_original VARCHAR(255)",
            "valor DECIMAL(15,2) NOT NULL",
            "tipo ENUM('debito', 'credito') NOT NULL",
            "origem ENUM('conta_corrente', 'cartao_credito', 'pix') NOT NULL",
            "referencia_externa VARCHAR(100) COMMENT 'ID da transação no banco'",
            
            // Hash para evitar duplicatas
            "transacao_hash VARCHAR(64) UNIQUE COMMENT 'MD5 para detectar duplicatas'",
            
            // Classificação sugerida pela IA
            "categoria_sugerida_id INT",
            "centro_custo_sugerido_id INT",
            "fornecedor_sugerido_id INT",
            "cliente_sugerido_id INT",
            "confianca_ia DECIMAL(5,2) COMMENT 'Confiança da IA de 0 a 100'",
            "justificativa_ia TEXT COMMENT 'Por que a IA sugeriu isso'",
            
            // Status e aprovação
            "status ENUM('pendente', 'aprovada', 'ignorada', 'erro') DEFAULT 'pendente'",
            "aprovada_por INT COMMENT 'ID do usuário que aprovou'",
            "aprovada_em DATETIME",
            "observacao TEXT COMMENT 'Observação do usuário ao aprovar/ignorar'",
            
            // Vínculos com contas criadas
            "conta_pagar_id INT",
            "conta_receber_id INT",
            
            // Auditoria
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        $this->createTable('transacoes_pendentes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Adicionar índices
        $this->addIndex('transacoes_pendentes', 'idx_empresa_status', 'empresa_id, status');
        $this->addIndex('transacoes_pendentes', 'idx_data_transacao', 'data_transacao');
        $this->addIndex('transacoes_pendentes', 'idx_conexao', 'conexao_bancaria_id');
        
        // Adicionar foreign keys
        $this->execute("
            ALTER TABLE transacoes_pendentes 
            ADD CONSTRAINT fk_transacao_empresa 
            FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
        ");
        
        $this->execute("
            ALTER TABLE transacoes_pendentes 
            ADD CONSTRAINT fk_transacao_conexao 
            FOREIGN KEY (conexao_bancaria_id) REFERENCES conexoes_bancarias(id) ON DELETE CASCADE
        ");
        
        echo "Tabela 'transacoes_pendentes' criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS transacoes_pendentes");
        echo "Tabela 'transacoes_pendentes' removida com sucesso!\n";
    }
}
