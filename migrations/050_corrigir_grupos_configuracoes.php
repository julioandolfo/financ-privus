<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_050_CorrigirGruposConfiguracoes extends BaseMigration
{
    public function up()
    {
        // Corrigir grupos de configurações que estavam incorretos
        $updates = "
            -- Mover configurações financeiras para grupo próprio
            UPDATE configuracoes SET grupo = 'financeiro' 
            WHERE chave IN (
                'financeiro.permitir_data_retroativa',
                'financeiro.dias_retroativos_limite',
                'financeiro.bloquear_edicao_conciliado',
                'financeiro.aprovar_contas_antes_pagar',
                'financeiro.valor_minimo_aprovacao'
            );
            
            -- Mover configurações de dashboard para grupo próprio
            UPDATE configuracoes SET grupo = 'dashboard' 
            WHERE chave IN (
                'dashboard.periodo_padrao',
                'dashboard.auto_refresh',
                'dashboard.refresh_segundos'
            );
            
            -- Mover configurações de relatórios para grupo próprio
            UPDATE configuracoes SET grupo = 'relatorios' 
            WHERE chave IN (
                'relatorios.exportar_pdf',
                'relatorios.exportar_excel',
                'relatorios.limite_registros'
            );
            
            -- Mover configurações de email para grupo próprio
            UPDATE configuracoes SET grupo = 'email' 
            WHERE chave IN (
                'email.smtp_host',
                'email.smtp_porta',
                'email.smtp_usuario',
                'email.smtp_senha',
                'email.remetente_nome',
                'email.remetente_email'
            );
            
            -- Mover configurações de backup para grupo próprio
            UPDATE configuracoes SET grupo = 'backup' 
            WHERE chave IN (
                'backup.automatico_habilitado',
                'backup.frequencia',
                'backup.hora_execucao',
                'backup.manter_ultimos'
            );
            
            -- Mover configurações de integração para grupo próprio
            UPDATE configuracoes SET grupo = 'integracoes' 
            WHERE chave IN (
                'integracao.woocommerce_habilitado',
                'integracao.api_publica',
                'integracao.webhooks'
            );
        ";
        
        $this->execute($updates);
        
        echo "Grupos de configurações corrigidos com sucesso!\n";
    }
    
    public function down()
    {
        // Reverter para grupo 'sistema'
        $updates = "
            UPDATE configuracoes SET grupo = 'sistema' 
            WHERE chave LIKE 'financeiro.%' 
               OR chave LIKE 'dashboard.%' 
               OR chave LIKE 'relatorios.%' 
               OR chave LIKE 'email.%' 
               OR chave LIKE 'backup.%' 
               OR chave LIKE 'integracao.%';
        ";
        
        $this->execute($updates);
        
        echo "Grupos de configurações revertidos com sucesso!\n";
    }
}
