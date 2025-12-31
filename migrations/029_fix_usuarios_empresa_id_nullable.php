<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

/**
 * Garante que empresa_id na tabela usuarios aceita NULL
 */
class Migration_029_FixUsuariosEmpresaIdNullable extends BaseMigration
{
    public function up()
    {
        // Tenta remover a foreign key existente (ignora erro se não existir)
        try {
            $this->execute("ALTER TABLE usuarios DROP FOREIGN KEY usuarios_ibfk_1");
            echo "✓ Foreign key removida\n";
        } catch (\PDOException $e) {
            // Ignora se a constraint não existir
            if (strpos($e->getMessage(), '1091') === false) {
                throw $e; // Re-lança se for outro erro
            }
            echo "  (Foreign key não encontrada, continuando...)\n";
        }
        
        // Modifica a coluna para garantir que aceita NULL
        $this->execute("ALTER TABLE usuarios MODIFY COLUMN empresa_id INT NULL");
        echo "✓ Coluna empresa_id configurada para aceitar NULL\n";
        
        // Recria a foreign key
        $this->execute("
            ALTER TABLE usuarios 
            ADD CONSTRAINT usuarios_ibfk_1 
            FOREIGN KEY (empresa_id) 
            REFERENCES empresas(id) 
            ON DELETE SET NULL
        ");
        echo "✓ Foreign key recriada\n";
    }
    
    public function down()
    {
        // Não precisa reverter, pois é uma correção
        echo "Nada a reverter\n";
    }
}
