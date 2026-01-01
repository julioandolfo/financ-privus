<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_044_CreateConexoesBancarias extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "usuario_id INT NOT NULL",
            "banco VARCHAR(50) NOT NULL COMMENT 'sicredi, sicoob, bradesco, itau'",
            "tipo ENUM('conta_corrente', 'conta_poupanca', 'cartao_credito') NOT NULL",
            "identificacao VARCHAR(100) COMMENT 'Ex: Conta 12345-6 ou Cartão *1234'",
            
            // Open Banking / OAuth
            "access_token TEXT COMMENT 'Token criptografado'",
            "refresh_token TEXT COMMENT 'Refresh token criptografado'",
            "token_expira_em DATETIME",
            "consent_id VARCHAR(100) COMMENT 'ID do consentimento Open Banking'",
            
            // Configurações
            "auto_sync BOOLEAN DEFAULT 1 COMMENT 'Sincronização automática'",
            "frequencia_sync ENUM('manual', 'diaria', 'semanal') DEFAULT 'diaria'",
            "categoria_padrao_id INT COMMENT 'Categoria padrão para classificação'",
            "centro_custo_padrao_id INT COMMENT 'Centro de custo padrão'",
            "aprovacao_automatica BOOLEAN DEFAULT 0 COMMENT 'Aprovar transações automaticamente'",
            
            // Auditoria
            "ativo BOOLEAN DEFAULT 1",
            "ultima_sincronizacao DATETIME",
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        $this->createTable('conexoes_bancarias', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Adicionar índices
        $this->addIndex('conexoes_bancarias', 'idx_empresa_ativo', 'empresa_id, ativo');
        $this->addIndex('conexoes_bancarias', 'idx_usuario', 'usuario_id');
        
        // Adicionar foreign keys
        $this->execute("
            ALTER TABLE conexoes_bancarias 
            ADD CONSTRAINT fk_conexao_empresa 
            FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
        ");
        
        $this->execute("
            ALTER TABLE conexoes_bancarias 
            ADD CONSTRAINT fk_conexao_usuario 
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ");
        
        echo "Tabela 'conexoes_bancarias' criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS conexoes_bancarias");
        echo "Tabela 'conexoes_bancarias' removida com sucesso!\n";
    }
}
