<?php
/**
 * Teste direto do controller de despesas recorrentes
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Despesas Recorrentes</h1>";
echo "<pre>";

try {
    echo "1. Definindo APP_ROOT...\n";
    define('APP_ROOT', dirname(__DIR__));
    echo "   APP_ROOT = " . APP_ROOT . "\n\n";
    
    echo "2. Carregando EnvLoader...\n";
    require_once APP_ROOT . '/includes/EnvLoader.php';
    EnvLoader::load();
    echo "   EnvLoader OK\n\n";
    
    echo "3. Definindo constantes...\n";
    define('APP_DEBUG', true);
    define('APP_ENV', 'development');
    echo "   Constantes OK\n\n";
    
    echo "4. Iniciando sessão...\n";
    require_once APP_ROOT . '/app/core/Session.php';
    \App\Core\Session::start();
    echo "   Sessão OK\n\n";
    
    echo "5. Carregando arquivos core...\n";
    require_once APP_ROOT . '/app/core/Database.php';
    echo "   Database OK\n";
    require_once APP_ROOT . '/app/core/Model.php';
    echo "   Model OK\n";
    require_once APP_ROOT . '/app/core/Request.php';
    echo "   Request OK\n";
    require_once APP_ROOT . '/app/core/Response.php';
    echo "   Response OK\n";
    require_once APP_ROOT . '/app/core/Controller.php';
    echo "   Controller OK\n\n";
    
    echo "6. Testando conexão com banco...\n";
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "   Conexão OK\n\n";
    
    echo "7. Verificando se tabela despesas_recorrentes existe...\n";
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'despesas_recorrentes'");
        $table = $stmt->fetch();
        if ($table) {
            echo "   TABELA EXISTE!\n";
            
            // Conta registros
            $stmt = $db->query("SELECT COUNT(*) as total FROM despesas_recorrentes");
            $count = $stmt->fetch();
            echo "   Total de registros: " . $count['total'] . "\n\n";
        } else {
            echo "   <span style='color:red'>TABELA NÃO EXISTE!</span>\n";
            echo "   Você precisa executar as queries SQL para criar a tabela.\n\n";
        }
    } catch (Exception $e) {
        echo "   <span style='color:red'>ERRO: " . $e->getMessage() . "</span>\n\n";
    }
    
    echo "8. Carregando model DespesaRecorrente...\n";
    require_once APP_ROOT . '/app/models/DespesaRecorrente.php';
    echo "   Arquivo carregado\n";
    
    $model = new \App\Models\DespesaRecorrente();
    echo "   Model instanciado OK\n\n";
    
    echo "9. Testando findAll()...\n";
    $despesas = $model->findAll([]);
    echo "   findAll() OK - " . count($despesas) . " registros\n\n";
    
    echo "10. Carregando model Empresa...\n";
    require_once APP_ROOT . '/app/models/Empresa.php';
    $empresaModel = new \App\Models\Empresa();
    $empresas = $empresaModel->findAll(['ativo' => 1]);
    echo "   Empresa OK - " . count($empresas) . " empresas\n\n";
    
    echo "11. Carregando controller...\n";
    require_once APP_ROOT . '/app/controllers/DespesaRecorrenteController.php';
    echo "   Controller carregado OK\n\n";
    
    echo "<span style='color:green;font-weight:bold'>TODOS OS TESTES PASSARAM!</span>\n";
    echo "O problema pode estar na view ou no layout.\n\n";
    
    echo "12. Testando carregamento da view...\n";
    $viewFile = APP_ROOT . '/app/views/despesas_recorrentes/index.php';
    if (file_exists($viewFile)) {
        echo "   View existe: " . $viewFile . "\n";
        
        // Tenta incluir a view
        echo "   Tentando incluir view...\n";
        ob_start();
        
        // Simula variáveis que a view espera
        $despesas = [];
        $empresas = [];
        $filtros = [];
        $resumo = ['despesas_count' => 0, 'receitas_count' => 0, 'total_despesas' => 0, 'total_receitas' => 0, 'saldo_previsto' => 0];
        $title = 'Test';
        
        try {
            include $viewFile;
            $content = ob_get_clean();
            echo "   <span style='color:green'>View incluída com sucesso!</span>\n";
            echo "   Tamanho do conteúdo: " . strlen($content) . " bytes\n";
        } catch (Throwable $e) {
            ob_end_clean();
            echo "   <span style='color:red'>ERRO na view: " . $e->getMessage() . "</span>\n";
            echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    } else {
        echo "   <span style='color:red'>View não encontrada!</span>\n";
    }
    
} catch (Throwable $e) {
    echo "\n<span style='color:red;font-weight:bold'>ERRO:</span>\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
