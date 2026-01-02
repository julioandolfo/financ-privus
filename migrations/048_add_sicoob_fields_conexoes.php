<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_048_AddSicoobFieldsConexoes extends BaseMigration
{
    public function up()
    {
        $this->addColumn('conexoes_bancarias', 'ambiente', "VARCHAR(20) DEFAULT 'sandbox' AFTER consent_id");
        $this->addColumn('conexoes_bancarias', 'client_id', "VARCHAR(255) DEFAULT NULL AFTER ambiente");
        $this->addColumn('conexoes_bancarias', 'client_secret', "VARCHAR(255) DEFAULT NULL AFTER client_id");
        $this->addColumn('conexoes_bancarias', 'cert_pem', "LONGTEXT DEFAULT NULL AFTER client_secret");
        $this->addColumn('conexoes_bancarias', 'key_pem', "LONGTEXT DEFAULT NULL AFTER cert_pem");
        $this->addColumn('conexoes_bancarias', 'cert_password', "VARCHAR(255) DEFAULT NULL AFTER key_pem");
        echo "Colunas de credenciais Sicoob adicionadas em conexoes_bancarias\n";
    }

    public function down()
    {
        $this->dropColumn('conexoes_bancarias', 'cert_password');
        $this->dropColumn('conexoes_bancarias', 'key_pem');
        $this->dropColumn('conexoes_bancarias', 'cert_pem');
        $this->dropColumn('conexoes_bancarias', 'client_secret');
        $this->dropColumn('conexoes_bancarias', 'client_id');
        $this->dropColumn('conexoes_bancarias', 'ambiente');
        echo "Colunas de credenciais Sicoob removidas de conexoes_bancarias\n";
    }
}
