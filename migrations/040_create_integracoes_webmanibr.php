<?php
use Includes\Migration;

class Migration_040_CreateIntegracoesWebmanibr extends Migration
{
    public function up()
    {
        $this->createTable('integracoes_webmanibr', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            'consumer_key VARCHAR(255) NOT NULL COMMENT \'Chave do consumidor WebmaniaBR\'',
            'consumer_secret VARCHAR(255) NOT NULL COMMENT \'Secret do consumidor WebmaniaBR\'',
            'access_token VARCHAR(255) NOT NULL COMMENT \'Token de acesso WebmaniaBR\'',
            'access_token_secret VARCHAR(255) NOT NULL COMMENT \'Secret do token de acesso WebmaniaBR\'',
            'ambiente ENUM(\'producao\', \'homologacao\') DEFAULT \'homologacao\' COMMENT \'Ambiente da emissão\'',
            'serie_nfe VARCHAR(3) DEFAULT \'1\' COMMENT \'Série da NF-e\'',
            'numero_nfe_inicial INT DEFAULT 1 COMMENT \'Número inicial da NF-e\'',
            'certificado_digital TEXT DEFAULT NULL COMMENT \'Caminho do certificado digital (se necessário)\'',
            'senha_certificado VARCHAR(255) DEFAULT NULL COMMENT \'Senha do certificado digital\'',
            'emitir_automatico BOOLEAN DEFAULT 0 COMMENT \'Emitir NF-e automaticamente ao concluir pedido\'',
            'enviar_email_cliente BOOLEAN DEFAULT 1 COMMENT \'Enviar e-mail com NF-e para o cliente\'',
            'natureza_operacao VARCHAR(60) DEFAULT \'Venda de Produtos\' COMMENT \'Natureza da operação padrão\'',
            'tipo_documento TINYINT DEFAULT 1 COMMENT \'1=NF-e, 65=NFC-e\'',
            'finalidade_emissao TINYINT DEFAULT 1 COMMENT \'1=Normal, 2=Complementar, 3=Ajuste, 4=Devolução\'',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_webmanibr_integracao (integracao_id)'
        ]);
        
        // Tabela para armazenar as NF-es emitidas
        $this->createTable('nfes_emitidas', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'empresa_id INT NOT NULL',
            'pedido_id INT DEFAULT NULL COMMENT \'ID do pedido vinculado\'',
            'integracao_id INT NOT NULL',
            'uuid VARCHAR(50) NOT NULL COMMENT \'UUID da NF-e na WebmaniaBR\'',
            'chave_nfe VARCHAR(44) NOT NULL COMMENT \'Chave de acesso da NF-e\'',
            'numero_nfe INT NOT NULL COMMENT \'Número da NF-e\'',
            'serie_nfe VARCHAR(3) NOT NULL COMMENT \'Série da NF-e\'',
            'modelo VARCHAR(2) DEFAULT \'55\' COMMENT \'Modelo do documento (55=NF-e, 65=NFC-e)\'',
            'status ENUM(\'aguardando\', \'processando\', \'autorizada\', \'cancelada\', \'rejeitada\', \'denegada\', \'inutilizada\') DEFAULT \'aguardando\'',
            'protocolo VARCHAR(50) DEFAULT NULL COMMENT \'Protocolo de autorização\'',
            'data_emissao DATETIME NOT NULL',
            'data_autorizacao DATETIME DEFAULT NULL',
            'xml_nfe LONGTEXT DEFAULT NULL COMMENT \'XML da NF-e\'',
            'danfe_url VARCHAR(500) DEFAULT NULL COMMENT \'URL do DANFE (PDF)\'',
            'xml_url VARCHAR(500) DEFAULT NULL COMMENT \'URL do XML\'',
            'valor_total DECIMAL(10,2) NOT NULL',
            'cliente_nome VARCHAR(255) NOT NULL',
            'cliente_documento VARCHAR(18) NOT NULL',
            'motivo_status TEXT DEFAULT NULL COMMENT \'Motivo do status (rejeição, cancelamento, etc)\'',
            'observacoes TEXT DEFAULT NULL',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE',
            'FOREIGN KEY (pedido_id) REFERENCES pedidos_vinculados(id) ON DELETE SET NULL',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_nfe_empresa (empresa_id)',
            'INDEX idx_nfe_pedido (pedido_id)',
            'INDEX idx_nfe_chave (chave_nfe)',
            'INDEX idx_nfe_status (status)',
            'INDEX idx_nfe_data_emissao (data_emissao)'
        ]);
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS nfes_emitidas");
        $this->execute("DROP TABLE IF EXISTS integracoes_webmanibr");
    }
}
