<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_034_CreateProdutosVariacoes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "produto_id INT NOT NULL",
            "nome VARCHAR(100) NOT NULL COMMENT 'Ex: Tamanho P - Cor Azul'",
            "sku VARCHAR(100) NULL COMMENT 'SKU específico da variação'",
            "codigo_barras VARCHAR(50) NULL",
            "atributos JSON NULL COMMENT 'Ex: {\"tamanho\":\"P\",\"cor\":\"Azul\"}'",
            "custo_unitario DECIMAL(15,2) NULL COMMENT 'Custo específico da variação'",
            "preco_venda DECIMAL(15,2) NULL COMMENT 'Preço específico da variação'",
            "estoque INT DEFAULT 0",
            "estoque_minimo INT DEFAULT 0",
            "peso DECIMAL(10,3) NULL COMMENT 'Peso em kg'",
            "dimensoes JSON NULL COMMENT 'Ex: {\"altura\":10,\"largura\":20,\"profundidade\":5}'",
            "ativo BOOLEAN DEFAULT 1",
            "data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('produtos_variacoes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('produtos_variacoes', 'idx_produto', ['produto_id']);
        $this->addIndex('produtos_variacoes', 'idx_sku', ['sku']);
        $this->addIndex('produtos_variacoes', 'idx_codigo_barras', ['codigo_barras']);
        $this->addIndex('produtos_variacoes', 'idx_ativo', ['ativo']);
        
        echo "Tabela produtos_variacoes criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS produtos_variacoes");
        echo "Tabela produtos_variacoes removida com sucesso!\n";
    }
}
