<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_035_AddCategoriaCodigoBarrasProdutos extends BaseMigration
{
    public function up()
    {
        // Adicionar campo categoria_id
        $this->addColumn('produtos', 'categoria_id', 'INT NULL AFTER empresa_id');
        
        // Adicionar campo codigo_barras
        $this->addColumn('produtos', 'codigo_barras', 'VARCHAR(50) NULL AFTER codigo');
        
        // Adicionar campo estoque
        $this->addColumn('produtos', 'estoque', 'INT DEFAULT 0 AFTER preco_venda');
        
        // Adicionar campo estoque_minimo
        $this->addColumn('produtos', 'estoque_minimo', 'INT DEFAULT 0 AFTER estoque');
        
        // Adicionar foreign key para categoria
        $this->execute("ALTER TABLE produtos ADD CONSTRAINT fk_produto_categoria 
                        FOREIGN KEY (categoria_id) REFERENCES categorias_produtos(id) ON DELETE SET NULL");
        
        // Adicionar índices
        $this->addIndex('produtos', 'idx_categoria', ['categoria_id']);
        $this->addIndex('produtos', 'idx_codigo_barras', ['codigo_barras']);
        
        echo "Campos categoria_id, codigo_barras, estoque e estoque_minimo adicionados à tabela produtos!\n";
    }
    
    public function down()
    {
        // Remove foreign key
        $this->execute("ALTER TABLE produtos DROP FOREIGN KEY fk_produto_categoria");
        
        // Remove índices
        $this->execute("ALTER TABLE produtos DROP INDEX idx_categoria");
        $this->execute("ALTER TABLE produtos DROP INDEX idx_codigo_barras");
        
        // Remove colunas
        $this->execute("ALTER TABLE produtos DROP COLUMN categoria_id");
        $this->execute("ALTER TABLE produtos DROP COLUMN codigo_barras");
        $this->execute("ALTER TABLE produtos DROP COLUMN estoque");
        $this->execute("ALTER TABLE produtos DROP COLUMN estoque_minimo");
        
        echo "Campos removidos da tabela produtos!\n";
    }
}
