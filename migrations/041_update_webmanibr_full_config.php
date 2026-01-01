<?php
use Includes\Migration;

class Migration_041_UpdateWebmanibrFullConfig extends Migration
{
    public function up()
    {
        // Verificar e adicionar novos campos em integracoes_webmanibr
        $stmt = $this->db->query("SHOW COLUMNS FROM integracoes_webmanibr");
        $existingColumns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $newColumns = [
            'bearer_token' => "VARCHAR(255) DEFAULT NULL COMMENT 'Bearer Access Token para NFS-e'",
            'emitir_data_pedido' => "BOOLEAN DEFAULT 0 COMMENT 'Emitir com data do pedido (retroativa)'",
            'email_notificacao' => "VARCHAR(255) DEFAULT NULL COMMENT 'E-mail para notificações de erros'",
            'nfse_classe_imposto' => "VARCHAR(50) DEFAULT NULL COMMENT 'Classe de imposto pré-configurada NFS-e (REF)'",
            'nfse_tipo_desconto' => "VARCHAR(20) DEFAULT 'nenhum' COMMENT 'nenhum, condicional, incondicional'",
            'nfse_incluir_taxas' => "BOOLEAN DEFAULT 0 COMMENT 'Incluir taxas no valor do serviço'",
            'nfe_classe_imposto' => "VARCHAR(50) DEFAULT NULL COMMENT 'Classe de imposto pré-configurada NF-e (REF)'",
            'ncm_padrao' => "VARCHAR(8) DEFAULT NULL COMMENT 'NCM padrão para produtos'",
            'cest_padrao' => "VARCHAR(7) DEFAULT NULL COMMENT 'CEST padrão para produtos'",
            'origem_padrao' => "TINYINT DEFAULT 0 COMMENT 'Origem padrão dos produtos'",
            'intermediador' => "TINYINT DEFAULT 0 COMMENT '0=Sem intermediador, 1=Com intermediador'",
            'intermediador_cnpj' => "VARCHAR(18) DEFAULT NULL COMMENT 'CNPJ do intermediador'",
            'intermediador_id' => "VARCHAR(60) DEFAULT NULL COMMENT 'ID do intermediador'",
            'informacoes_fisco' => "TEXT DEFAULT NULL COMMENT 'Informações ao Fisco'",
            'informacoes_complementares' => "TEXT DEFAULT NULL COMMENT 'Informações Complementares ao Consumidor'",
            'descricao_complementar_servico' => "TEXT DEFAULT NULL COMMENT 'Descrição complementar do serviço'",
            'preenchimento_automatico_endereco' => "BOOLEAN DEFAULT 1 COMMENT 'Preencher endereço por CEP'",
            'bairro_obrigatorio' => "BOOLEAN DEFAULT 1 COMMENT 'Campo bairro obrigatório'",
            'certificado_validade' => "DATE DEFAULT NULL COMMENT 'Data de validade do certificado'"
        ];
        
        foreach ($newColumns as $columnName => $definition) {
            if (!in_array($columnName, $existingColumns)) {
                $this->execute("ALTER TABLE integracoes_webmanibr ADD COLUMN {$columnName} {$definition}");
            }
        }
        
        // Modificar colunas existentes se necessário
        if (in_array('emitir_automatico', $existingColumns)) {
            $this->execute("ALTER TABLE integracoes_webmanibr MODIFY COLUMN emitir_automatico VARCHAR(20) DEFAULT 'nao' COMMENT 'nao, processando, concluido'");
        }
        
        if (in_array('certificado_digital', $existingColumns)) {
            $this->execute("ALTER TABLE integracoes_webmanibr MODIFY COLUMN certificado_digital LONGTEXT DEFAULT NULL COMMENT 'Conteúdo do certificado A1 (base64)'");
        }
        
        if (in_array('senha_certificado', $existingColumns)) {
            $this->execute("ALTER TABLE integracoes_webmanibr MODIFY COLUMN senha_certificado VARCHAR(255) DEFAULT NULL COMMENT 'Senha do certificado A1'");
        }
        
        // Criar tabela de transportadoras
        $tableExists = $this->db->query("SHOW TABLES LIKE 'webmanibr_transportadoras'")->fetch();
        if (!$tableExists) {
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
        }
        
        // Criar tabela de formas de pagamento
        $tableExists = $this->db->query("SHOW TABLES LIKE 'webmanibr_formas_pagamento'")->fetch();
        if (!$tableExists) {
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
        }
    }
    
    public function down()
    {
        // Remover colunas adicionadas
        $columns = [
            'bearer_token', 'emitir_data_pedido', 'email_notificacao',
            'nfse_classe_imposto', 'nfse_tipo_desconto', 'nfse_incluir_taxas',
            'nfe_classe_imposto', 'ncm_padrao', 'cest_padrao', 'origem_padrao',
            'intermediador', 'intermediador_cnpj', 'intermediador_id',
            'informacoes_fisco', 'informacoes_complementares', 'descricao_complementar_servico',
            'preenchimento_automatico_endereco', 'bairro_obrigatorio', 'certificado_validade'
        ];
        
        foreach ($columns as $column) {
            $this->execute("ALTER TABLE integracoes_webmanibr DROP COLUMN IF EXISTS {$column}");
        }
        
        // Remover tabelas
        $this->execute("DROP TABLE IF EXISTS webmanibr_formas_pagamento");
        $this->execute("DROP TABLE IF EXISTS webmanibr_transportadoras");
    }
}
