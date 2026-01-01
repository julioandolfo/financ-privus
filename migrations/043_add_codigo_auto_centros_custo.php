<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_043_AddCodigoAutoCentrosCusto extends BaseMigration
{
    public function up()
    {
        // Adicionar configuração de código auto gerado para centros de custo
        $insert = "
            INSERT INTO configuracoes (chave, valor, tipo, descricao, grupo) 
            VALUES ('centros_custo.codigo_auto_gerado', 'true', 'boolean', 'Gerar código automaticamente', 'centros_custo')
            ON DUPLICATE KEY UPDATE descricao = VALUES(descricao)
        ";
        
        $this->execute($insert);
        
        echo "Configuração 'centros_custo.codigo_auto_gerado' adicionada com sucesso!\n";
    }
    
    public function down()
    {
        $delete = "DELETE FROM configuracoes WHERE chave = 'centros_custo.codigo_auto_gerado'";
        $this->execute($delete);
        
        echo "Configuração 'centros_custo.codigo_auto_gerado' removida com sucesso!\n";
    }
}
