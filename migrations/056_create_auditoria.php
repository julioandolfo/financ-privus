<?php
/**
 * Migration: Criar Tabela de Auditoria
 * 
 * Registra todas as ações realizadas no sistema para rastreabilidade completa
 */

require_once __DIR__ . '/../includes/Migration.php';

class CreateAuditoria extends Migration
{
    public function up()
    {
        echo "Criando tabela de auditoria...\n";
        
        $this->createTable('auditoria', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'tabela VARCHAR(100) NOT NULL COMMENT "Tabela afetada"',
            'registro_id INT NOT NULL COMMENT "ID do registro afetado"',
            'acao ENUM("create", "update", "delete", "restore", "cancel_payment", "make_payment", "cancel_receipt", "make_receipt") NOT NULL COMMENT "Ação realizada"',
            'usuario_id INT NOT NULL COMMENT "Usuário que realizou a ação"',
            'dados_antes JSON NULL COMMENT "Estado anterior do registro"',
            'dados_depois JSON NULL COMMENT "Estado posterior do registro"',
            'ip VARCHAR(45) NULL COMMENT "IP do usuário"',
            'user_agent TEXT NULL COMMENT "User agent do navegador"',
            'motivo TEXT NULL COMMENT "Motivo da ação (ex: cancelamento)"',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'INDEX idx_tabela (tabela)',
            'INDEX idx_registro (registro_id)',
            'INDEX idx_acao (acao)',
            'INDEX idx_usuario (usuario_id)',
            'INDEX idx_created_at (created_at)',
            'FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT'
        ]);
        
        echo "Tabela de auditoria criada com sucesso!\n";
    }
    
    public function down()
    {
        echo "Removendo tabela de auditoria...\n";
        $this->dropTable('auditoria');
        echo "Tabela de auditoria removida!\n";
    }
}

// Executar migration
$migration = new CreateAuditoria();
$migration->run();
