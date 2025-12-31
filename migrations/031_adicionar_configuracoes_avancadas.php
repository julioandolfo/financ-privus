<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_031_AdicionarConfiguracoesAvancadas extends BaseMigration
{
    public function up()
    {
        // Inserir configurações mais avançadas e complexas
        $inserts = "
            INSERT INTO configuracoes (chave, valor, tipo, descricao, grupo) VALUES
            -- Sistema - Aparência
            ('sistema.titulo', 'Sistema Financeiro Empresarial', 'string', 'Título do sistema', 'sistema'),
            ('sistema.logo', '', 'string', 'Logo do sistema (upload)', 'sistema'),
            ('sistema.favicon', '', 'string', 'Favicon do sistema (upload)', 'sistema'),
            ('sistema.cor_primaria', '#3B82F6', 'string', 'Cor primária do sistema (hex)', 'sistema'),
            ('sistema.cor_secundaria', '#6366F1', 'string', 'Cor secundária do sistema (hex)', 'sistema'),
            
            -- Sistema - Funcionalidades
            ('sistema.multi_empresa', 'true', 'boolean', 'Habilitar modo multi-empresa', 'sistema'),
            ('sistema.consolidacao', 'true', 'boolean', 'Habilitar consolidação de empresas', 'sistema'),
            ('sistema.notificacoes_email', 'true', 'boolean', 'Enviar notificações por email', 'sistema'),
            ('sistema.notificacoes_sistema', 'true', 'boolean', 'Notificações no sistema', 'sistema'),
            
            -- Sistema - Segurança
            ('sistema.sessao_timeout', '3600', 'number', 'Timeout da sessão (segundos)', 'sistema'),
            ('sistema.max_tentativas_login', '5', 'number', 'Máximo de tentativas de login', 'sistema'),
            ('sistema.dois_fatores', 'false', 'boolean', 'Autenticação de dois fatores', 'sistema'),
            ('sistema.senha_expira_dias', '90', 'number', 'Senha expira em X dias (0=nunca)', 'sistema'),
            
            -- Financeiro - Regras
            ('financeiro.permitir_data_retroativa', 'true', 'boolean', 'Permitir lançamentos retroativos', 'sistema'),
            ('financeiro.dias_retroativos_limite', '90', 'number', 'Limite de dias retroativos (0=ilimitado)', 'sistema'),
            ('financeiro.bloquear_edicao_conciliado', 'true', 'boolean', 'Bloquear edição de lançamentos conciliados', 'sistema'),
            ('financeiro.aprovar_contas_antes_pagar', 'false', 'boolean', 'Exigir aprovação antes de pagar', 'sistema'),
            ('financeiro.valor_minimo_aprovacao', '0', 'number', 'Valor mínimo que exige aprovação', 'sistema'),
            
            -- Dashboard
            ('dashboard.periodo_padrao', 'mes_atual', 'string', 'Período padrão do dashboard', 'sistema'),
            ('dashboard.auto_refresh', 'false', 'boolean', 'Auto-refresh do dashboard', 'sistema'),
            ('dashboard.refresh_segundos', '300', 'number', 'Intervalo de refresh (segundos)', 'sistema'),
            
            -- Relatórios
            ('relatorios.exportar_pdf', 'true', 'boolean', 'Habilitar exportação PDF', 'sistema'),
            ('relatorios.exportar_excel', 'true', 'boolean', 'Habilitar exportação Excel', 'sistema'),
            ('relatorios.limite_registros', '10000', 'number', 'Limite de registros por relatório', 'sistema'),
            
            -- Email
            ('email.smtp_host', '', 'string', 'Servidor SMTP', 'sistema'),
            ('email.smtp_porta', '587', 'number', 'Porta SMTP', 'sistema'),
            ('email.smtp_usuario', '', 'string', 'Usuário SMTP', 'sistema'),
            ('email.smtp_senha', '', 'string', 'Senha SMTP', 'sistema'),
            ('email.remetente_nome', 'Sistema Financeiro', 'string', 'Nome do remetente', 'sistema'),
            ('email.remetente_email', '', 'string', 'Email do remetente', 'sistema'),
            
            -- API - Mais opções
            ('api.openai_temperatura', '0.7', 'number', 'Temperatura da OpenAI (0-2)', 'api'),
            ('api.openai_max_tokens', '2000', 'number', 'Máximo de tokens por requisição', 'api'),
            ('api.openai_timeout', '30', 'number', 'Timeout das requisições (segundos)', 'api'),
            
            -- IA - Funcionalidades
            ('ia.sugestao_categorias', 'true', 'boolean', 'IA sugerir categorias automaticamente', 'api'),
            ('ia.sugestao_fornecedores', 'true', 'boolean', 'IA sugerir fornecedores/clientes', 'api'),
            ('ia.analise_textos', 'true', 'boolean', 'IA analisar descrições de lançamentos', 'api'),
            ('ia.deteccao_duplicatas', 'true', 'boolean', 'IA detectar lançamentos duplicados', 'api'),
            ('ia.previsao_fluxo', 'false', 'boolean', 'IA prever fluxo de caixa futuro', 'api'),
            ('ia.alertas_inteligentes', 'true', 'boolean', 'IA gerar alertas inteligentes', 'api'),
            
            -- Integrações
            ('integracao.woocommerce_habilitado', 'false', 'boolean', 'Integração WooCommerce', 'sistema'),
            ('integracao.api_publica', 'false', 'boolean', 'Habilitar API pública', 'sistema'),
            ('integracao.webhooks', 'false', 'boolean', 'Habilitar webhooks', 'sistema'),
            
            -- Backup
            ('backup.automatico_habilitado', 'true', 'boolean', 'Backup automático habilitado', 'sistema'),
            ('backup.frequencia', 'diario', 'string', 'Frequência do backup (diario/semanal/mensal)', 'sistema'),
            ('backup.hora_execucao', '03:00', 'string', 'Horário de execução (HH:MM)', 'sistema'),
            ('backup.manter_ultimos', '30', 'number', 'Manter últimos X backups', 'sistema')
        ";
        
        $this->execute($inserts);
        
        echo "Configurações avançadas adicionadas com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("
            DELETE FROM configuracoes WHERE grupo IN ('sistema', 'api') 
            AND chave NOT IN (
                'sistema.modo_debug', 
                'sistema.logs_habilitados',
                'api.openai_key',
                'api.openai_model',
                'api.openai_habilitado'
            )
        ");
        echo "Configurações avançadas removidas com sucesso!\n";
    }
}
