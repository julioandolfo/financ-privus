<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_023_CreateIntegracoesBancosDados extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "integracao_id INT NOT NULL",
            "nome_conexao VARCHAR(255) NOT NULL",
            "tipo_banco ENUM('mysql', 'postgresql', 'sqlserver', 'oracle') NOT NULL",
            "host VARCHAR(255) NOT NULL",
            "porta INT NOT NULL",
            "`database` VARCHAR(255) NOT NULL",
            "usuario VARCHAR(255) NOT NULL",
            "senha TEXT NOT NULL",
            "tabela_origem VARCHAR(255) NOT NULL",
            "colunas_selecionadas JSON NULL",
            "condicoes JSON NULL",
            "mapeamento_colunas JSON NULL",
            "tabela_destino VARCHAR(100) NOT NULL",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('integracoes_bancos_dados', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('integracoes_bancos_dados', 'idx_integracao', ['integracao_id']);
        $this->addIndex('integracoes_bancos_dados', 'idx_ativo', ['ativo']);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS integracoes_bancos_dados");
    }
}

