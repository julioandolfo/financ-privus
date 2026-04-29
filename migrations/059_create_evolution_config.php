<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_059_CreateEvolutionConfig extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NULL COMMENT 'NULL = configuração global'",
            "nome VARCHAR(255) NOT NULL COMMENT 'Apelido da configuração'",
            "base_url VARCHAR(255) NOT NULL COMMENT 'URL da Evolution API (ex: https://evo.exemplo.com)'",
            "instance_name VARCHAR(150) NOT NULL COMMENT 'Nome da instância na Evolution'",
            "api_key TEXT NOT NULL COMMENT 'API Key (criptografada)'",
            "webhook_url VARCHAR(255) NULL COMMENT 'URL para callbacks (opcional)'",
            "numero_remetente VARCHAR(20) NULL COMMENT 'Número associado à instância (apenas exibição)'",
            "ativo BOOLEAN DEFAULT 1",
            "status_conexao ENUM('desconhecido','conectado','desconectado','erro') DEFAULT 'desconhecido'",
            "ultima_verificacao DATETIME NULL",
            "ultima_resposta TEXT NULL COMMENT 'Última resposta crua da API (debug)'",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];

        $this->createTable('evolution_config', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        $this->addIndex('evolution_config', 'idx_empresa', ['empresa_id']);
        $this->addIndex('evolution_config', 'idx_ativo', ['ativo']);

        $this->execute("
            ALTER TABLE evolution_config
            ADD CONSTRAINT fk_evolution_empresa
            FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
        ");

        echo "Tabela 'evolution_config' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS evolution_config");
        echo "Tabela 'evolution_config' removida com sucesso!\n";
    }
}
