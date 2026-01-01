<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_042_AddCodigoAutoCategorias extends BaseMigration
{
    public function up()
    {
        // Adicionar configuração de código auto gerado para categorias
        $insert = "
            INSERT INTO configuracoes (chave, valor, tipo, descricao, grupo) 
            VALUES ('categorias.codigo_auto_gerado', 'true', 'boolean', 'Gerar código automaticamente', 'categorias')
            ON DUPLICATE KEY UPDATE descricao = VALUES(descricao)
        ";
        
        $this->execute($insert);
        
        echo "Configuração 'categorias.codigo_auto_gerado' adicionada com sucesso!\n";
    }
    
    public function down()
    {
        $delete = "DELETE FROM configuracoes WHERE chave = 'categorias.codigo_auto_gerado'";
        $this->execute($delete);
        
        echo "Configuração 'categorias.codigo_auto_gerado' removida com sucesso!\n";
    }
}
