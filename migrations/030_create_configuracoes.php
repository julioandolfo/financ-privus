<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_030_CreateConfiguracoes extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "chave VARCHAR(255) NOT NULL UNIQUE",
            "valor TEXT",
            "tipo ENUM('boolean', 'string', 'number', 'json') DEFAULT 'string'",
            "descricao TEXT",
            "grupo VARCHAR(100)",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        $this->createTable('configuracoes', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Inserir configurações padrão
        $inserts = "
            INSERT INTO configuracoes (chave, valor, tipo, descricao, grupo) VALUES
            -- Empresas
            ('empresas.codigo_obrigatorio', 'false', 'boolean', 'Código é obrigatório', 'empresas'),
            ('empresas.codigo_auto_gerado', 'true', 'boolean', 'Gerar código automaticamente', 'empresas'),
            ('empresas.cnpj_obrigatorio', 'true', 'boolean', 'CNPJ é obrigatório', 'empresas'),
            
            -- Usuários
            ('usuarios.email_obrigatorio', 'true', 'boolean', 'Email é obrigatório', 'usuarios'),
            ('usuarios.senha_forte', 'true', 'boolean', 'Exigir senha forte', 'usuarios'),
            ('usuarios.avatar_obrigatorio', 'false', 'boolean', 'Avatar é obrigatório', 'usuarios'),
            
            -- Fornecedores
            ('fornecedores.codigo_obrigatorio', 'false', 'boolean', 'Código é obrigatório', 'fornecedores'),
            ('fornecedores.codigo_auto_gerado', 'true', 'boolean', 'Gerar código automaticamente', 'fornecedores'),
            ('fornecedores.email_obrigatorio', 'false', 'boolean', 'Email é obrigatório', 'fornecedores'),
            ('fornecedores.telefone_obrigatorio', 'true', 'boolean', 'Telefone é obrigatório', 'fornecedores'),
            
            -- Clientes
            ('clientes.codigo_obrigatorio', 'false', 'boolean', 'Código é obrigatório', 'clientes'),
            ('clientes.codigo_auto_gerado', 'true', 'boolean', 'Gerar código automaticamente', 'clientes'),
            ('clientes.email_obrigatorio', 'false', 'boolean', 'Email é obrigatório', 'clientes'),
            ('clientes.telefone_obrigatorio', 'true', 'boolean', 'Telefone é obrigatório', 'clientes'),
            
            -- Categorias Financeiras
            ('categorias.codigo_obrigatorio', 'false', 'boolean', 'Código é obrigatório', 'categorias'),
            ('categorias.hierarquia_habilitada', 'true', 'boolean', 'Permitir hierarquia de categorias', 'categorias'),
            
            -- Centros de Custo
            ('centros_custo.codigo_obrigatorio', 'false', 'boolean', 'Código é obrigatório', 'centros_custo'),
            ('centros_custo.hierarquia_habilitada', 'true', 'boolean', 'Permitir hierarquia de centros', 'centros_custo'),
            
            -- Contas Bancárias
            ('contas_bancarias.saldo_inicial_obrigatorio', 'true', 'boolean', 'Saldo inicial é obrigatório', 'contas_bancarias'),
            
            -- Contas a Pagar
            ('contas_pagar.numero_documento_obrigatorio', 'true', 'boolean', 'Número do documento é obrigatório', 'contas_pagar'),
            ('contas_pagar.rateio_habilitado', 'true', 'boolean', 'Habilitar rateio entre empresas', 'contas_pagar'),
            ('contas_pagar.centro_custo_obrigatorio', 'false', 'boolean', 'Centro de custo é obrigatório', 'contas_pagar'),
            ('contas_pagar.data_emissao_obrigatoria', 'true', 'boolean', 'Data de emissão é obrigatória', 'contas_pagar'),
            
            -- Contas a Receber
            ('contas_receber.numero_documento_obrigatorio', 'true', 'boolean', 'Número do documento é obrigatório', 'contas_receber'),
            ('contas_receber.rateio_habilitado', 'true', 'boolean', 'Habilitar rateio entre empresas', 'contas_receber'),
            ('contas_receber.centro_custo_obrigatorio', 'false', 'boolean', 'Centro de custo é obrigatório', 'contas_receber'),
            ('contas_receber.data_emissao_obrigatoria', 'true', 'boolean', 'Data de emissão é obrigatória', 'contas_receber'),
            
            -- Movimentações de Caixa
            ('movimentacoes.conciliacao_habilitada', 'true', 'boolean', 'Habilitar conciliação bancária', 'movimentacoes'),
            
            -- API e Integrações
            ('api.openai_key', '', 'string', 'Chave de API da OpenAI', 'api'),
            ('api.openai_model', 'gpt-4', 'string', 'Modelo da OpenAI a usar', 'api'),
            ('api.openai_habilitado', 'false', 'boolean', 'Habilitar integração com OpenAI', 'api'),
            
            -- Sistema
            ('sistema.modo_debug', 'false', 'boolean', 'Modo debug ativado', 'sistema'),
            ('sistema.logs_habilitados', 'true', 'boolean', 'Habilitar logs do sistema', 'sistema'),
            ('sistema.backup_automatico', 'true', 'boolean', 'Backup automático habilitado', 'sistema')
        ";
        
        $this->execute($inserts);
        
        echo "Tabela 'configuracoes' criada com sucesso!\n";
        echo "Configurações padrão inseridas.\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS configuracoes");
        echo "Tabela 'configuracoes' removida com sucesso!\n";
    }
}
