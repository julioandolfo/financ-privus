<?php
require_once __DIR__ . '/../includes/Migration.php';

use Includes\Migration;

class Migration_027_CreateIntegracoesApi extends Migration
{
    public function up()
    {
        $this->createTable('integracoes_api', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            'nome_api VARCHAR(255) NOT NULL',
            'base_url TEXT NOT NULL',
            'tipo_api ENUM(\'rest\', \'graphql\', \'soap\') DEFAULT \'rest\'',
            'autenticacao ENUM(\'none\', \'basic\', \'bearer\', \'api_key\', \'oauth2\') DEFAULT \'none\'',
            'auth_usuario VARCHAR(255) DEFAULT NULL',
            'auth_senha VARCHAR(255) DEFAULT NULL',
            'auth_token TEXT DEFAULT NULL',
            'api_key_header VARCHAR(100) DEFAULT NULL',
            'api_key_value VARCHAR(255) DEFAULT NULL',
            'oauth2_client_id VARCHAR(255) DEFAULT NULL',
            'oauth2_client_secret VARCHAR(255) DEFAULT NULL',
            'oauth2_token_url TEXT DEFAULT NULL',
            'oauth2_scope VARCHAR(255) DEFAULT NULL',
            'headers_padrao JSON DEFAULT NULL COMMENT \'Headers enviados em todas requisições\'',
            'endpoints JSON DEFAULT NULL COMMENT \'Mapeamento de endpoints\'',
            'timeout INT DEFAULT 30',
            'formato_resposta ENUM(\'json\', \'xml\', \'text\') DEFAULT \'json\'',
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
        $this->execute('DROP TABLE IF EXISTS integracoes_api');
    }
}
