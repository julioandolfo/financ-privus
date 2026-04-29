<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_063_SeedWhatsappConfiguracoes extends BaseMigration
{
    public function up()
    {
        $inserts = "
            INSERT IGNORE INTO configuracoes (chave, valor, tipo, descricao, grupo) VALUES
            ('whatsapp.envio_habilitado', 'true', 'boolean', 'Habilita envio de relatórios via WhatsApp (Evolution API)', 'whatsapp'),
            ('whatsapp.evolution_url_padrao', '', 'string', 'URL padrão da Evolution API (sugestão para novas conexões)', 'whatsapp'),
            ('whatsapp.numero_remetente_padrao', '', 'string', 'Número remetente padrão (apenas exibição)', 'whatsapp'),
            ('whatsapp.tentativas_max', '3', 'number', 'Máximo de tentativas de envio antes de marcar como falha', 'whatsapp'),
            ('whatsapp.timeout_segundos', '15', 'number', 'Timeout (segundos) para chamadas HTTP à Evolution', 'whatsapp')
        ";
        $this->execute($inserts);

        echo "Configurações padrão de WhatsApp inseridas.\n";
    }

    public function down()
    {
        $this->execute("DELETE FROM configuracoes WHERE grupo = 'whatsapp'");
        echo "Configurações de WhatsApp removidas.\n";
    }
}
