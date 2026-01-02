<?php
require_once __DIR__ . '/../includes/Migration.php';

use includes\Migration as BaseMigration;

class Migration_049_AddTipoIntegracaoConexoes extends BaseMigration
{
    public function up()
    {
        $this->addColumn('conexoes_bancarias', 'tipo_integracao', "ENUM('of','nativo') DEFAULT 'of' AFTER banco");
        echo "Coluna tipo_integracao adicionada em conexoes_bancarias\n";
    }

    public function down()
    {
        $this->dropColumn('conexoes_bancarias', 'tipo_integracao');
        echo "Coluna tipo_integracao removida de conexoes_bancarias\n";
    }
}
