<?php
/**
 * CRON: Lembretes de Contas a Vencer
 * Frequência: Diário às 08:00
 * Comando: php /caminho/para/projeto/cron/lembretes_vencimento.php
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/ContaPagar.php';
require_once APP_ROOT . '/app/models/ContaReceber.php';
require_once APP_ROOT . '/app/models/Usuario.php';

use App\Core\Database;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Usuario;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando verificação de vencimentos...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Contas a Pagar vencendo em 3 dias
    $dataLimite = date('Y-m-d', strtotime('+3 days'));
    $hoje = date('Y-m-d');
    
    $sql = "SELECT cp.*, e.nome_fantasia as empresa_nome, f.nome_razao_social as fornecedor_nome
            FROM contas_pagar cp
            LEFT JOIN empresas e ON cp.empresa_id = e.id
            LEFT JOIN fornecedores f ON cp.fornecedor_id = f.id
            WHERE cp.status = 'pendente' 
            AND cp.data_vencimento BETWEEN :hoje AND :data_limite
            ORDER BY cp.data_vencimento ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['hoje' => $hoje, 'data_limite' => $dataLimite]);
    $contasPagar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Contas a Pagar vencendo em 3 dias: " . count($contasPagar) . "\n";
    
    // Contas a Receber vencendo em 3 dias
    $sql = "SELECT cr.*, e.nome_fantasia as empresa_nome, c.nome_razao_social as cliente_nome
            FROM contas_receber cr
            LEFT JOIN empresas e ON cr.empresa_id = e.id
            LEFT JOIN clientes c ON cr.cliente_id = c.id
            WHERE cr.status = 'pendente' 
            AND cr.data_vencimento BETWEEN :hoje AND :data_limite
            ORDER BY cr.data_vencimento ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['hoje' => $hoje, 'data_limite' => $dataLimite]);
    $contasReceber = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Contas a Receber vencendo em 3 dias: " . count($contasReceber) . "\n";
    
    // Agrupar por empresa
    $empresasAlerta = [];
    
    foreach ($contasPagar as $conta) {
        if (!isset($empresasAlerta[$conta['empresa_id']])) {
            $empresasAlerta[$conta['empresa_id']] = [
                'empresa_nome' => $conta['empresa_nome'],
                'contas_pagar' => [],
                'contas_receber' => []
            ];
        }
        $empresasAlerta[$conta['empresa_id']]['contas_pagar'][] = $conta;
    }
    
    foreach ($contasReceber as $conta) {
        if (!isset($empresasAlerta[$conta['empresa_id']])) {
            $empresasAlerta[$conta['empresa_id']] = [
                'empresa_nome' => $conta['empresa_nome'],
                'contas_pagar' => [],
                'contas_receber' => []
            ];
        }
        $empresasAlerta[$conta['empresa_id']]['contas_receber'][] = $conta;
    }
    
    // Buscar usuários para notificar (aqui você pode integrar com sistema de e-mail)
    foreach ($empresasAlerta as $empresaId => $dados) {
        echo "\n[Empresa: {$dados['empresa_nome']}]\n";
        echo "  Contas a Pagar: " . count($dados['contas_pagar']) . "\n";
        echo "  Contas a Receber: " . count($dados['contas_receber']) . "\n";
        
        // TODO: Enviar e-mail para os usuários da empresa
        // Você pode usar PHPMailer ou outra biblioteca de e-mail
        
        // Por enquanto, apenas log
        $totalValorPagar = array_sum(array_column($dados['contas_pagar'], 'valor'));
        $totalValorReceber = array_sum(array_column($dados['contas_receber'], 'valor'));
        
        echo "  Total a Pagar: R$ " . number_format($totalValorPagar, 2, ',', '.') . "\n";
        echo "  Total a Receber: R$ " . number_format($totalValorReceber, 2, ',', '.') . "\n";
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Verificação concluída!\n";
    
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
