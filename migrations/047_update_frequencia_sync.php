<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_047_UpdateFrequenciaSync extends BaseMigration
{
    public function up()
    {
        echo "Atualizando campo frequencia_sync com novas opções...\n";
        
        // Alterar ENUM para incluir novas frequências
        $sql = "ALTER TABLE conexoes_bancarias 
                MODIFY COLUMN frequencia_sync ENUM('manual', '10min', '30min', 'horaria', 'diaria', 'semanal') 
                DEFAULT 'diaria'";
        
        $this->execute($sql);
        
        echo "Campo frequencia_sync atualizado com sucesso!\n";
    }
    
    public function down()
    {
        echo "Revertendo campo frequencia_sync para valores originais...\n";
        
        // Primeiro, converter valores novos para valores antigos
        $this->execute("UPDATE conexoes_bancarias SET frequencia_sync = 'diaria' WHERE frequencia_sync IN ('10min', '30min', 'horaria')");
        
        // Depois, remover as opções novas do ENUM
        $sql = "ALTER TABLE conexoes_bancarias 
                MODIFY COLUMN frequencia_sync ENUM('manual', 'diaria', 'semanal') 
                DEFAULT 'diaria'";
        
        $this->execute($sql);
        
        echo "Campo frequencia_sync revertido com sucesso!\n";
    }
}
