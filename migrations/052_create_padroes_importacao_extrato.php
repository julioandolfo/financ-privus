<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

/**
 * Migration: Criar tabela de padrões de importação de extratos bancários
 * 
 * Armazena padrões de configuração por descrição de transação
 * para facilitar importações futuras
 */
class Migration_052_CreatePadroesImportacaoExtrato extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "usuario_id INT NOT NULL",
            "empresa_id INT NOT NULL",
            "descricao_padrao VARCHAR(255) NOT NULL COMMENT 'Descrição normalizada da transação'",
            "descricao_original VARCHAR(500) NULL COMMENT 'Descrição original da primeira vez que foi importada'",
            "categoria_id INT NULL COMMENT 'Categoria financeira padrão'",
            "centro_custo_id INT NULL COMMENT 'Centro de custo padrão'",
            "fornecedor_id INT NULL COMMENT 'Fornecedor padrão'",
            "conta_bancaria_id INT NULL COMMENT 'Conta bancária padrão'",
            "forma_pagamento_id INT NULL COMMENT 'Forma de pagamento padrão'",
            "tem_rateio BOOLEAN DEFAULT 0 COMMENT 'Se tem rateio por padrão'",
            "observacoes_padrao TEXT NULL COMMENT 'Observações padrão'",
            "usos INT DEFAULT 1 COMMENT 'Quantas vezes foi usado'",
            "ultimo_uso_em DATETIME NULL COMMENT 'Data da última vez que foi usado'",
            "ativo BOOLEAN DEFAULT 1",
            "criado_em DATETIME DEFAULT CURRENT_TIMESTAMP",
            "atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            "FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL",
            "FOREIGN KEY (centro_custo_id) REFERENCES centros_custo(id) ON DELETE SET NULL",
            "FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL",
            "FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL",
            "FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento(id) ON DELETE SET NULL"
        ];
        
        $this->createTable('padroes_importacao_extrato', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('padroes_importacao_extrato', 'idx_usuario_empresa', ['usuario_id', 'empresa_id']);
        $this->addIndex('padroes_importacao_extrato', 'idx_descricao', ['descricao_padrao']);
    }
    
    public function down()
    {
        $this->dropTable('padroes_importacao_extrato');
    }
}
