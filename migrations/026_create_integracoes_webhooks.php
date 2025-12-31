<?php
require_once __DIR__ . '/../includes/Migration.php';

use Includes\Migration;

class Migration_026_CreateIntegracoesWebhooks extends Migration
{
    public function up()
    {
        $this->createTable('integracoes_webhooks', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            'nome_webhook VARCHAR(255) NOT NULL',
            'url_webhook TEXT NOT NULL',
            'metodo VARCHAR(10) DEFAULT \'POST\'',
            'headers JSON DEFAULT NULL COMMENT \'Cabeçalhos HTTP\'',
            'autenticacao ENUM(\'none\', \'basic\', \'bearer\', \'api_key\') DEFAULT \'none\'',
            'auth_usuario VARCHAR(255) DEFAULT NULL',
            'auth_senha VARCHAR(255) DEFAULT NULL',
            'auth_token TEXT DEFAULT NULL',
            'api_key_header VARCHAR(100) DEFAULT NULL',
            'api_key_value VARCHAR(255) DEFAULT NULL',
            'eventos_disparo JSON DEFAULT NULL COMMENT \'Quais eventos disparam este webhook\'',
            'payload_template LONGTEXT DEFAULT NULL COMMENT \'Template JSON do payload\'',
            'timeout INT DEFAULT 30',
            'retry_attempts INT DEFAULT 3',
            'retry_delay INT DEFAULT 60 COMMENT \'Segundos entre tentativas\'',
            'ativo BOOLEAN DEFAULT 1',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_integracao_id (integracao_id)',
            'INDEX idx_ativo (ativo)'
        ]);
    }
    
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS integracoes_webhooks');
    }
}
