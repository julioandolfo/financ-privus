<?php
use Includes\Migration;

class Migration_038_AddImpostosToProdutos extends Migration
{
    public function up()
    {
        // Verificar quais colunas já existem
        $stmt = $this->db->query("SHOW COLUMNS FROM produtos");
        $existingColumns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Lista de colunas a adicionar
        $columns = [
            'ncm' => "VARCHAR(8) DEFAULT NULL COMMENT 'Nomenclatura Comum do Mercosul'",
            'cest' => "VARCHAR(7) DEFAULT NULL COMMENT 'Código Especificador da Substituição Tributária'",
            'origem' => "TINYINT DEFAULT 0 COMMENT '0=Nacional, 1=Estrangeira, 2=Importação Direta'",
            'cfop_venda' => "VARCHAR(4) DEFAULT '5102' COMMENT 'CFOP padrão para venda'",
            'cst_icms' => "VARCHAR(3) DEFAULT '00' COMMENT 'Código Situação Tributária ICMS'",
            'aliquota_icms' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota ICMS %'",
            'reducao_base_icms' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Redução Base ICMS %'",
            'cst_ipi' => "VARCHAR(2) DEFAULT '99' COMMENT 'Código Situação Tributária IPI'",
            'aliquota_ipi' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota IPI %'",
            'cst_pis' => "VARCHAR(2) DEFAULT '99' COMMENT 'Código Situação Tributária PIS'",
            'aliquota_pis' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota PIS %'",
            'cst_cofins' => "VARCHAR(2) DEFAULT '99' COMMENT 'Código Situação Tributária COFINS'",
            'aliquota_cofins' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota COFINS %'",
            'unidade_tributavel' => "VARCHAR(6) DEFAULT 'UN' COMMENT 'Unidade para tributação'",
            'informacoes_adicionais' => "TEXT DEFAULT NULL COMMENT 'Informações adicionais para NF-e'",
            'gtin' => "VARCHAR(14) DEFAULT NULL COMMENT 'GTIN/EAN'",
            'gtin_tributavel' => "VARCHAR(14) DEFAULT NULL COMMENT 'GTIN/EAN da unidade tributável'"
        ];
        
        // Adicionar apenas colunas que não existem
        foreach ($columns as $columnName => $definition) {
            if (!in_array($columnName, $existingColumns)) {
                $this->execute("ALTER TABLE produtos ADD COLUMN {$columnName} {$definition}");
            }
        }
        
        // Índices para busca (verificar se já existem)
        $indexes = $this->db->query("SHOW INDEX FROM produtos")->fetchAll(\PDO::FETCH_ASSOC);
        $existingIndexes = array_column($indexes, 'Key_name');
        
        if (!in_array('idx_produtos_ncm', $existingIndexes)) {
            $this->execute("CREATE INDEX idx_produtos_ncm ON produtos(ncm)");
        }
        
        if (!in_array('idx_produtos_cest', $existingIndexes)) {
            $this->execute("CREATE INDEX idx_produtos_cest ON produtos(cest)");
        }
    }
    
    public function down()
    {
        // Remover índices
        $this->execute("ALTER TABLE produtos DROP INDEX IF EXISTS idx_produtos_ncm");
        $this->execute("ALTER TABLE produtos DROP INDEX IF EXISTS idx_produtos_cest");
        
        // Remover colunas
        $columns = [
            'ncm', 'cest', 'origem', 'cfop_venda',
            'cst_icms', 'aliquota_icms', 'reducao_base_icms',
            'cst_ipi', 'aliquota_ipi',
            'cst_pis', 'aliquota_pis',
            'cst_cofins', 'aliquota_cofins',
            'unidade_tributavel', 'informacoes_adicionais',
            'gtin', 'gtin_tributavel'
        ];
        
        foreach ($columns as $column) {
            $this->execute("ALTER TABLE produtos DROP COLUMN IF EXISTS {$column}");
        }
    }
}
