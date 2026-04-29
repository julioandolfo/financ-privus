<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_060_CreateWhatsappRelatorioRegras extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "evolution_config_id INT NOT NULL",
            "nome VARCHAR(255) NOT NULL",
            "descricao TEXT NULL",
            "ativo BOOLEAN DEFAULT 1",

            // Tipo de relatório
            "tipo_relatorio ENUM(
                'contas_pagar_atrasadas',
                'contas_pagar_vencer',
                'contas_pagar_vencendo_hoje',
                'contas_receber_atrasadas',
                'contas_receber_vencer',
                'contas_receber_vencendo_hoje',
                'resumo_diario',
                'fluxo_caixa',
                'inadimplencia'
            ) NOT NULL",

            // Filtros (JSON livre)
            "filtros JSON NULL COMMENT 'Filtros adicionais (categorias, centros, valor min/max, dias, etc)'",

            // Agendamento
            "frequencia ENUM('diaria','semanal','mensal','intervalo_horas') NOT NULL DEFAULT 'diaria'",
            "horario TIME NULL COMMENT 'Horário do envio (para diaria/semanal/mensal)'",
            "dias_semana VARCHAR(20) NULL COMMENT 'CSV 1-7 (1=Dom) para frequência semanal'",
            "dia_mes TINYINT NULL COMMENT '1-31 para frequência mensal'",
            "intervalo_horas INT NULL COMMENT 'Para frequencia=intervalo_horas'",

            // Template (Markdown WhatsApp)
            "template_mensagem TEXT NULL COMMENT 'Template com variáveis. Ex: {empresa}, {total}, {valor_total}, {lista}, {data}'",
            "incluir_lista_detalhada BOOLEAN DEFAULT 1 COMMENT 'Anexa lista de contas no fim da mensagem'",
            "limite_itens_lista INT DEFAULT 20 COMMENT 'Máximo de itens listados no detalhamento'",

            // Estado
            "ultima_execucao DATETIME NULL",
            "proxima_execucao DATETIME NULL",
            "ultimo_status ENUM('sucesso','falha','sem_dados','aguardando') DEFAULT 'aguardando'",
            "ultimo_erro TEXT NULL",

            // Auditoria
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            "usuario_cadastro_id INT NULL"
        ];

        $this->createTable('whatsapp_relatorio_regras', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);

        $this->addIndex('whatsapp_relatorio_regras', 'idx_empresa', ['empresa_id']);
        $this->addIndex('whatsapp_relatorio_regras', 'idx_ativo', ['ativo']);
        $this->addIndex('whatsapp_relatorio_regras', 'idx_proxima_exec', ['proxima_execucao']);
        $this->addIndex('whatsapp_relatorio_regras', 'idx_tipo', ['tipo_relatorio']);

        $this->execute("
            ALTER TABLE whatsapp_relatorio_regras
            ADD CONSTRAINT fk_whats_regra_empresa
            FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE whatsapp_relatorio_regras
            ADD CONSTRAINT fk_whats_regra_evolution
            FOREIGN KEY (evolution_config_id) REFERENCES evolution_config(id) ON DELETE RESTRICT
        ");

        echo "Tabela 'whatsapp_relatorio_regras' criada com sucesso!\n";
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS whatsapp_relatorio_regras");
        echo "Tabela 'whatsapp_relatorio_regras' removida com sucesso!\n";
    }
}
