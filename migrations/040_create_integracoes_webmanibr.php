<?php
use Includes\Migration;

class Migration_040_CreateIntegracoesWebmanibr extends Migration
{
    public function up()
    {
        $this->createTable('integracoes_webmanibr', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            
            // Credenciais NF-e (API 1.0)
            'consumer_key VARCHAR(255) NOT NULL COMMENT \'Chave do consumidor WebmaniaBR\'',
            'consumer_secret VARCHAR(255) NOT NULL COMMENT \'Secret do consumidor WebmaniaBR\'',
            'access_token VARCHAR(255) NOT NULL COMMENT \'Token de acesso WebmaniaBR\'',
            'access_token_secret VARCHAR(255) NOT NULL COMMENT \'Secret do token de acesso WebmaniaBR\'',
            
            // Credenciais NFS-e (API 2.0)
            'bearer_token VARCHAR(255) DEFAULT NULL COMMENT \'Bearer Access Token para NFS-e\'',
            
            // Ambiente
            'ambiente ENUM(\'producao\', \'homologacao\') DEFAULT \'homologacao\' COMMENT \'Ambiente da emissão\'',
            
            // Configuração Padrão
            'emitir_automatico VARCHAR(20) DEFAULT \'nao\' COMMENT \'nao, processando, concluido\'',
            'enviar_email_cliente BOOLEAN DEFAULT 1 COMMENT \'Enviar e-mail com NF-e para o cliente\'',
            'emitir_data_pedido BOOLEAN DEFAULT 0 COMMENT \'Emitir com data do pedido (retroativa)\'',
            'email_notificacao VARCHAR(255) DEFAULT NULL COMMENT \'E-mail para notificações de erros\'',
            
            // Configurações NFS-e
            'nfse_classe_imposto VARCHAR(50) DEFAULT NULL COMMENT \'Classe de imposto pré-configurada NFS-e (REF)\'',
            'nfse_tipo_desconto VARCHAR(20) DEFAULT \'nenhum\' COMMENT \'nenhum, condicional, incondicional\'',
            'nfse_incluir_taxas BOOLEAN DEFAULT 0 COMMENT \'Incluir taxas no valor do serviço\'',
            
            // Configurações NF-e
            'natureza_operacao VARCHAR(60) DEFAULT \'Venda\' COMMENT \'Natureza da operação padrão\'',
            'nfe_classe_imposto VARCHAR(50) DEFAULT NULL COMMENT \'Classe de imposto pré-configurada NF-e (REF)\'',
            'ncm_padrao VARCHAR(8) DEFAULT NULL COMMENT \'NCM padrão para produtos\'',
            'cest_padrao VARCHAR(7) DEFAULT NULL COMMENT \'CEST padrão para produtos\'',
            'origem_padrao TINYINT DEFAULT 0 COMMENT \'Origem padrão dos produtos\'',
            
            // Intermediador
            'intermediador TINYINT DEFAULT 0 COMMENT \'0=Sem intermediador, 1=Com intermediador\'',
            'intermediador_cnpj VARCHAR(18) DEFAULT NULL COMMENT \'CNPJ do intermediador\'',
            'intermediador_id VARCHAR(60) DEFAULT NULL COMMENT \'ID do intermediador\'',
            
            // Informações Complementares
            'informacoes_fisco TEXT DEFAULT NULL COMMENT \'Informações ao Fisco\'',
            'informacoes_complementares TEXT DEFAULT NULL COMMENT \'Informações Complementares ao Consumidor\'',
            'descricao_complementar_servico TEXT DEFAULT NULL COMMENT \'Descrição complementar do serviço\'',
            
            // Checkout
            'preenchimento_automatico_endereco BOOLEAN DEFAULT 1 COMMENT \'Preencher endereço por CEP\'',
            'bairro_obrigatorio BOOLEAN DEFAULT 1 COMMENT \'Campo bairro obrigatório\'',
            
            // Certificado Digital A1
            'certificado_digital LONGTEXT DEFAULT NULL COMMENT \'Conteúdo do certificado A1 (base64)\'',
            'certificado_senha VARCHAR(255) DEFAULT NULL COMMENT \'Senha do certificado A1\'',
            'certificado_validade DATE DEFAULT NULL COMMENT \'Data de validade do certificado\'',
            
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_webmanibr_integracao (integracao_id)'
        ]);
        
        // Tabela para transportadoras
        $this->createTable('webmanibr_transportadoras', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            'metodo_entrega VARCHAR(100) NOT NULL COMMENT \'Nome do método de entrega\'',
            'transportadora_nome VARCHAR(100) NOT NULL COMMENT \'Nome da transportadora\'',
            'transportadora_cnpj VARCHAR(18) DEFAULT NULL COMMENT \'CNPJ da transportadora\'',
            'transportadora_ie VARCHAR(20) DEFAULT NULL COMMENT \'Inscrição Estadual\'',
            'transportadora_endereco VARCHAR(255) DEFAULT NULL COMMENT \'Endereço completo\'',
            'transportadora_cidade VARCHAR(100) DEFAULT NULL COMMENT \'Cidade\'',
            'transportadora_uf VARCHAR(2) DEFAULT NULL COMMENT \'UF\'',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_transportadora_integracao (integracao_id)'
        ]);
        
        // Tabela para formas de pagamento
        $this->createTable('webmanibr_formas_pagamento', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'integracao_id INT NOT NULL',
            'gateway VARCHAR(100) NOT NULL COMMENT \'Nome do gateway de pagamento\'',
            'forma_pagamento VARCHAR(50) NOT NULL COMMENT \'Forma: cartao_credito, boleto, pix, etc\'',
            'descricao VARCHAR(255) DEFAULT NULL COMMENT \'Descrição do pagamento\'',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (integracao_id) REFERENCES integracoes_config(id) ON DELETE CASCADE',
            'INDEX idx_forma_pagamento_integracao (integracao_id)'
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
        $this->execute("DROP TABLE IF EXISTS webmanibr_formas_pagamento");
        $this->execute("DROP TABLE IF EXISTS webmanibr_transportadoras");
        $this->execute("DROP TABLE IF EXISTS integracoes_webmanibr");
    }
}
