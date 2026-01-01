<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_046_CreateRegrasClassificacaoBancaria extends BaseMigration
{
    public function up()
    {
        $columns = [
            "id INT AUTO_INCREMENT PRIMARY KEY",
            "empresa_id INT NOT NULL",
            "usuario_id INT NOT NULL COMMENT 'Quem criou a regra'",
            
            // Regra
            "nome VARCHAR(100) NOT NULL",
            "descricao TEXT",
            "tipo_condicao ENUM('contem', 'igual', 'comeca_com', 'termina_com', 'regex') DEFAULT 'contem'",
            "valor_busca VARCHAR(255) NOT NULL COMMENT 'O que buscar na descrição'",
            
            // Filtros adicionais
            "banco_especifico VARCHAR(50) COMMENT 'Aplicar só para este banco'",
            "tipo_origem ENUM('conta_corrente', 'cartao_credito', 'pix') COMMENT 'Aplicar só para este tipo'",
            "valor_minimo DECIMAL(15,2) COMMENT 'Valor mínimo para aplicar regra'",
            "valor_maximo DECIMAL(15,2) COMMENT 'Valor máximo para aplicar regra'",
            
            // Classificação automática
            "categoria_destino_id INT",
            "centro_custo_destino_id INT",
            "fornecedor_destino_id INT",
            "cliente_destino_id INT",
            
            // Comportamento
            "aprovar_automaticamente BOOLEAN DEFAULT 0",
            "prioridade INT DEFAULT 0 COMMENT 'Ordem de execução (maior = primeiro)'",
            "ativo BOOLEAN DEFAULT 1",
            
            // Estatísticas
            "vezes_aplicada INT DEFAULT 0",
            "ultima_aplicacao DATETIME",
            
            // Auditoria
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        $this->createTable('regras_classificacao_bancaria', $columns, [
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ]);
        
        // Adicionar índices
        $this->addIndex('regras_classificacao_bancaria', 'idx_empresa_ativo', 'empresa_id, ativo');
        $this->addIndex('regras_classificacao_bancaria', 'idx_prioridade', 'prioridade');
        
        // Adicionar foreign keys
        $this->execute("
            ALTER TABLE regras_classificacao_bancaria 
            ADD CONSTRAINT fk_regra_empresa 
            FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
        ");
        
        $this->execute("
            ALTER TABLE regras_classificacao_bancaria 
            ADD CONSTRAINT fk_regra_usuario 
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ");
        
        echo "Tabela 'regras_classificacao_bancaria' criada com sucesso!\n";
    }
    
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS regras_classificacao_bancaria");
        echo "Tabela 'regras_classificacao_bancaria' removida com sucesso!\n";
    }
}
