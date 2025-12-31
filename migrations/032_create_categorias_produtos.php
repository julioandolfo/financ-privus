<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_032_CreateCategoriasProdutos extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "categoria_pai_id INT NULL",
            "nome VARCHAR(100) NOT NULL",
            "descricao TEXT NULL",
            "icone VARCHAR(50) NULL COMMENT 'Nome do ícone (ex: tag, folder, etc)'",
            "cor VARCHAR(7) NULL COMMENT 'Cor hexadecimal (ex: #FF5733)'",
            "ordem INT DEFAULT 0 COMMENT 'Ordem de exibição'",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE",
            "FOREIGN KEY (categoria_pai_id) REFERENCES categorias_produtos(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('categorias_produtos', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('categorias_produtos', 'idx_empresa', ['empresa_id']);
        $this->addIndex('categorias_produtos', 'idx_categoria_pai', ['categoria_pai_id']);
        $this->addIndex('categorias_produtos', 'idx_ativo', ['ativo']);
        $this->addIndex('categorias_produtos', 'idx_ordem', ['ordem']);
        
        echo "Tabela categorias_produtos criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS categorias_produtos");
        echo "Tabela categorias_produtos removida com sucesso!\n";
    }
}
