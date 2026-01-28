<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_053_AddDescontoRegiaoSegmentoToContasReceber extends BaseMigration
{
    public function up()
    {
        // Adicionar campo desconto
        $this->execute("ALTER TABLE contas_receber ADD COLUMN desconto DECIMAL(15,2) DEFAULT 0 AFTER valor_recebido");
        
        // Adicionar campo região
        $this->execute("ALTER TABLE contas_receber ADD COLUMN regiao VARCHAR(100) NULL AFTER observacoes");
        
        // Adicionar campo segmento
        $this->execute("ALTER TABLE contas_receber ADD COLUMN segmento VARCHAR(100) NULL AFTER regiao");
        
        // Adicionar campo numero_parcelas (para indicar se é parcelado)
        $this->execute("ALTER TABLE contas_receber ADD COLUMN numero_parcelas INT DEFAULT 1 AFTER segmento");
        
        // Adicionar campo parcela_atual (qual parcela é esta conta - 1, 2, 3...)
        $this->execute("ALTER TABLE contas_receber ADD COLUMN parcela_atual INT DEFAULT 1 AFTER numero_parcelas");
        
        // Adicionar campo conta_origem_id (para referenciar a conta original quando é parcela)
        $this->execute("ALTER TABLE contas_receber ADD COLUMN conta_origem_id INT NULL AFTER parcela_atual");
        
        // Adicionar índices
        $this->addIndex('contas_receber', 'idx_regiao', ['regiao']);
        $this->addIndex('contas_receber', 'idx_segmento', ['segmento']);
        $this->addIndex('contas_receber', 'idx_conta_origem', ['conta_origem_id']);
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE contas_receber DROP INDEX idx_conta_origem");
        $this->execute("ALTER TABLE contas_receber DROP INDEX idx_segmento");
        $this->execute("ALTER TABLE contas_receber DROP INDEX idx_regiao");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN conta_origem_id");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN parcela_atual");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN numero_parcelas");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN segmento");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN regiao");
        $this->execute("ALTER TABLE contas_receber DROP COLUMN desconto");
    }
}
