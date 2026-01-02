#!/usr/bin/env php
<?php
/**
 * Script para verificar configurações no banco de dados
 * Uso: php check_configuracoes.php
 */

// Define constantes
define('APP_ROOT', __DIR__);

// Carrega variáveis de ambiente
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

// Carrega configurações
require_once APP_ROOT . '/config/database.php';

try {
    // Conecta ao banco
    $config = require APP_ROOT . '/config/database.php';
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "========================================\n";
    echo "VERIFICAÇÃO DE CONFIGURAÇÕES\n";
    echo "========================================\n\n";
    
    // Buscar todos os grupos
    $sql = "SELECT DISTINCT grupo FROM configuracoes WHERE grupo IS NOT NULL ORDER BY grupo";
    $stmt = $pdo->query($sql);
    $grupos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 GRUPOS ENCONTRADOS: " . count($grupos) . "\n\n";
    
    foreach ($grupos as $grupo) {
        echo "📁 Grupo: " . strtoupper($grupo) . "\n";
        echo str_repeat("-", 60) . "\n";
        
        // Buscar configurações do grupo
        $sql = "SELECT chave, valor, tipo, descricao FROM configuracoes WHERE grupo = :grupo ORDER BY chave";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['grupo' => $grupo]);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configs as $config) {
            $valor = strlen($config['valor']) > 30 
                ? substr($config['valor'], 0, 30) . '...' 
                : $config['valor'];
            
            // Formatar valor vazio
            if (empty($config['valor'])) {
                $valor = '(vazio)';
            }
            
            printf(
                "  %-40s | %-10s | %s\n",
                $config['chave'],
                $config['tipo'],
                $valor
            );
        }
        
        echo "\n  Total: " . count($configs) . " configurações\n\n";
    }
    
    // Verificar configurações sem grupo
    $sql = "SELECT COUNT(*) FROM configuracoes WHERE grupo IS NULL";
    $stmt = $pdo->query($sql);
    $semGrupo = $stmt->fetchColumn();
    
    if ($semGrupo > 0) {
        echo "⚠️  ATENÇÃO: {$semGrupo} configurações sem grupo definido!\n";
        
        $sql = "SELECT chave, valor, tipo FROM configuracoes WHERE grupo IS NULL";
        $stmt = $pdo->query($sql);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configs as $config) {
            echo "  - {$config['chave']}\n";
        }
        echo "\n";
    }
    
    // Estatísticas gerais
    $sql = "SELECT COUNT(*) FROM configuracoes";
    $stmt = $pdo->query($sql);
    $total = $stmt->fetchColumn();
    
    echo "========================================\n";
    echo "ESTATÍSTICAS\n";
    echo "========================================\n";
    echo "Total de configurações: {$total}\n";
    echo "Total de grupos: " . count($grupos) . "\n";
    echo "Configurações sem grupo: {$semGrupo}\n";
    echo "\n";
    
    // Verificar quais grupos têm configurações mas não aparecem na view
    $gruposEsperados = ['empresas', 'usuarios', 'fornecedores', 'clientes', 'categorias', 
                        'centros_custo', 'contas_bancarias', 'contas_pagar', 'contas_receber', 
                        'movimentacoes', 'api', 'sistema'];
    
    $gruposFaltando = array_diff($gruposEsperados, $grupos);
    if (!empty($gruposFaltando)) {
        echo "⚠️  Grupos esperados mas não encontrados:\n";
        foreach ($gruposFaltando as $g) {
            echo "  - {$g}\n";
        }
        echo "\n";
    }
    
    $gruposExtras = array_diff($grupos, $gruposEsperados);
    if (!empty($gruposExtras)) {
        echo "ℹ️  Grupos extras não mapeados na view:\n";
        foreach ($gruposExtras as $g) {
            echo "  - {$g}\n";
        }
        echo "\n";
    }
    
    echo "✅ Verificação concluída!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
