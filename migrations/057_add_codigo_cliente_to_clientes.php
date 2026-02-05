<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_057_AddCodigoClienteToClientes extends BaseMigration
{
    public function up()
    {
        // Adiciona campo codigo_cliente à tabela clientes
        $this->execute("ALTER TABLE clientes ADD COLUMN codigo_cliente VARCHAR(50) NULL AFTER empresa_id");
        
        // Adiciona índice único para codigo_cliente por empresa
        $this->execute("CREATE UNIQUE INDEX idx_codigo_cliente_empresa ON clientes(codigo_cliente, empresa_id)");
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE clientes DROP INDEX idx_codigo_cliente_empresa");
        $this->execute("ALTER TABLE clientes DROP COLUMN codigo_cliente");
    }
}
