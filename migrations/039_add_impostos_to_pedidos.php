<?php
use Includes\Migration;

class Migration_039_AddImpostosToPedidos extends Migration
{
    public function up()
    {
        // Verificar quais colunas já existem na tabela pedidos_vinculados
        $stmt = $this->db->query("SHOW COLUMNS FROM pedidos_vinculados");
        $existingColumns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Adicionar campos de impostos aos pedidos
        $columns = [
            'valor_frete' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor do frete'",
            'valor_seguro' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor do seguro'",
            'valor_desconto' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor do desconto'",
            'valor_outras_despesas' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Outras despesas acessórias'",
            'valor_icms' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor total do ICMS'",
            'valor_icms_st' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor do ICMS ST'",
            'valor_ipi' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor total do IPI'",
            'valor_pis' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor total do PIS'",
            'valor_cofins' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor total do COFINS'",
            'base_calculo_icms' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Base de cálculo do ICMS'",
            'base_calculo_icms_st' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Base de cálculo do ICMS ST'",
            'valor_total_tributos' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor aproximado total de tributos'",
            'informacoes_adicionais_fisco' => "TEXT DEFAULT NULL COMMENT 'Informações adicionais de interesse do Fisco'",
            'informacoes_complementares' => "TEXT DEFAULT NULL COMMENT 'Informações complementares de interesse do Contribuinte'"
        ];
        
        // Adicionar apenas colunas que não existem
        foreach ($columns as $columnName => $definition) {
            if (!in_array($columnName, $existingColumns)) {
                $this->execute("ALTER TABLE pedidos_vinculados ADD COLUMN {$columnName} {$definition}");
            }
        }
        
        // Verificar quais colunas já existem na tabela pedidos_itens
        $stmt = $this->db->query("SHOW COLUMNS FROM pedidos_itens");
        $existingColumnsItens = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Adicionar campos de impostos aos itens do pedido
        $columnsItens = [
            'cfop' => "VARCHAR(4) DEFAULT '5102' COMMENT 'CFOP do item'",
            'ncm' => "VARCHAR(8) DEFAULT NULL COMMENT 'NCM do item'",
            'cest' => "VARCHAR(7) DEFAULT NULL COMMENT 'CEST do item'",
            'origem' => "TINYINT DEFAULT 0 COMMENT 'Origem da mercadoria'",
            'cst_icms' => "VARCHAR(3) DEFAULT '00' COMMENT 'CST ICMS'",
            'aliquota_icms' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota ICMS %'",
            'valor_icms' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor ICMS do item'",
            'base_calculo_icms' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Base de cálculo ICMS'",
            'cst_ipi' => "VARCHAR(2) DEFAULT '99' COMMENT 'CST IPI'",
            'aliquota_ipi' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota IPI %'",
            'valor_ipi' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor IPI do item'",
            'cst_pis' => "VARCHAR(2) DEFAULT '99' COMMENT 'CST PIS'",
            'aliquota_pis' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota PIS %'",
            'valor_pis' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor PIS do item'",
            'cst_cofins' => "VARCHAR(2) DEFAULT '99' COMMENT 'CST COFINS'",
            'aliquota_cofins' => "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Alíquota COFINS %'",
            'valor_cofins' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor COFINS do item'",
            'valor_desconto' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor desconto do item'",
            'valor_frete' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor frete do item'",
            'valor_seguro' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor seguro do item'",
            'valor_outras_despesas' => "DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Outras despesas do item'",
            'informacoes_adicionais' => "TEXT DEFAULT NULL COMMENT 'Informações adicionais do item'"
        ];
        
        // Adicionar apenas colunas que não existem
        foreach ($columnsItens as $columnName => $definition) {
            if (!in_array($columnName, $existingColumnsItens)) {
                $this->execute("ALTER TABLE pedidos_itens ADD COLUMN {$columnName} {$definition}");
            }
        }
    }
    
    public function down()
    {
        // Remover colunas da tabela pedidos_vinculados
        $columns = [
            'valor_frete', 'valor_seguro', 'valor_desconto', 'valor_outras_despesas',
            'valor_icms', 'valor_icms_st', 'valor_ipi', 'valor_pis', 'valor_cofins',
            'base_calculo_icms', 'base_calculo_icms_st', 'valor_total_tributos',
            'informacoes_adicionais_fisco', 'informacoes_complementares'
        ];
        
        foreach ($columns as $column) {
            $this->execute("ALTER TABLE pedidos_vinculados DROP COLUMN IF EXISTS {$column}");
        }
        
        // Remover colunas da tabela pedidos_itens
        $columnsItens = [
            'cfop', 'ncm', 'cest', 'origem',
            'cst_icms', 'aliquota_icms', 'valor_icms', 'base_calculo_icms',
            'cst_ipi', 'aliquota_ipi', 'valor_ipi',
            'cst_pis', 'aliquota_pis', 'valor_pis',
            'cst_cofins', 'aliquota_cofins', 'valor_cofins',
            'valor_desconto', 'valor_frete', 'valor_seguro', 'valor_outras_despesas',
            'informacoes_adicionais'
        ];
        
        foreach ($columnsItens as $column) {
            $this->execute("ALTER TABLE pedidos_itens DROP COLUMN IF EXISTS {$column}");
        }
    }
}
