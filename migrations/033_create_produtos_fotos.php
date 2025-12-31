<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_033_CreateProdutosFotos extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "produto_id INT NOT NULL",
            "arquivo VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo'",
            "caminho VARCHAR(500) NOT NULL COMMENT 'Caminho completo do arquivo'",
            "tamanho INT NULL COMMENT 'Tamanho em bytes'",
            "tipo VARCHAR(50) NULL COMMENT 'Tipo MIME (image/jpeg, etc)'",
            "principal BOOLEAN DEFAULT 0 COMMENT 'Foto principal do produto'",
            "ordem INT DEFAULT 0 COMMENT 'Ordem de exibição'",
            "data_upload DATETIME DEFAULT CURRENT_TIMESTAMP",
            "FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE"
        ];
        
        $this->createTable('produtos_fotos', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Índices
        $this->addIndex('produtos_fotos', 'idx_produto', ['produto_id']);
        $this->addIndex('produtos_fotos', 'idx_principal', ['principal']);
        $this->addIndex('produtos_fotos', 'idx_ordem', ['ordem']);
        
        echo "Tabela produtos_fotos criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS produtos_fotos");
        echo "Tabela produtos_fotos removida com sucesso!\n";
    }
}
