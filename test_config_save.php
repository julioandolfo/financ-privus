#!/usr/bin/env php
<?php
/**
 * Script para testar salvamento de configurações
 * Uso: php test_config_save.php
 */

// Define constantes
define('APP_ROOT', __DIR__);
define('APP_DEBUG', true);

// Carrega variáveis de ambiente
require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

// Carrega configurações
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/Configuracao.php';

use App\Models\Configuracao;

echo "========================================\n";
echo "TESTE DE SALVAMENTO DE CONFIGURAÇÕES\n";
echo "========================================\n\n";

// Teste 1: Buscar configuração existente
echo "1. Buscando configuração existente (empresas.codigo_obrigatorio)...\n";
$valor = Configuracao::get('empresas.codigo_obrigatorio', null);
echo "   Valor atual: " . ($valor === null ? 'NULL' : ($valor ? 'TRUE' : 'FALSE')) . "\n";
echo "   Tipo: " . gettype($valor) . "\n\n";

// Teste 2: Alterar valor
echo "2. Alterando para TRUE...\n";
$success = Configuracao::set('empresas.codigo_obrigatorio', true);
echo "   Sucesso: " . ($success ? 'SIM' : 'NÃO') . "\n";

// Teste 3: Verificar se foi salvo
Configuracao::clearCache();
$novoValor = Configuracao::get('empresas.codigo_obrigatorio', null);
echo "   Novo valor: " . ($novoValor === null ? 'NULL' : ($novoValor ? 'TRUE' : 'FALSE')) . "\n";
echo "   Tipo: " . gettype($novoValor) . "\n\n";

// Teste 4: Verificar no banco diretamente
echo "3. Verificando no banco de dados...\n";
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT chave, valor, tipo FROM configuracoes WHERE chave = :chave");
$stmt->execute(['chave' => 'empresas.codigo_obrigatorio']);
$config = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   Chave: " . $config['chave'] . "\n";
echo "   Valor (string): " . $config['valor'] . "\n";
echo "   Tipo: " . $config['tipo'] . "\n\n";

// Teste 5: Testar setMultiplas
echo "4. Testando setMultiplas() com múltiplos valores...\n";
$configuracoes = [
    'empresas.codigo_obrigatorio' => false,
    'empresas.codigo_auto_gerado' => true,
    'empresas.cnpj_obrigatorio' => true
];

$success = Configuracao::setMultiplas($configuracoes);
echo "   Sucesso: " . ($success ? 'SIM' : 'NÃO') . "\n\n";

// Teste 6: Verificar todos os valores salvos
echo "5. Verificando valores após setMultiplas()...\n";
Configuracao::clearCache();
foreach ($configuracoes as $chave => $valorEsperado) {
    $valorAtual = Configuracao::get($chave, null);
    $match = $valorAtual === $valorEsperado ? '✓' : '✗';
    echo "   {$match} {$chave}: esperado=" . ($valorEsperado ? 'true' : 'false') . 
         ", atual=" . ($valorAtual ? 'true' : 'false') . "\n";
}
echo "\n";

// Teste 7: Verificar conversão de string para boolean
echo "6. Testando conversão de valores...\n";
$testCases = [
    'true' => true,
    'false' => false,
    '1' => true,
    '0' => false,
    true => true,
    false => false
];

foreach ($testCases as $input => $expected) {
    $inputStr = is_bool($input) ? ($input ? 'true(bool)' : 'false(bool)') : "'{$input}'(string)";
    
    // Salvar
    Configuracao::set('teste.valor', $input);
    
    // Buscar no banco
    $stmt = $db->prepare("SELECT valor, tipo FROM configuracoes WHERE chave = 'teste.valor'");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscar via Model
    Configuracao::clearCache();
    $retrieved = Configuracao::get('teste.valor');
    
    $match = $retrieved === $expected ? '✓' : '✗';
    echo "   {$match} Input: {$inputStr} -> DB: '{$config['valor']}' ({$config['tipo']}) -> Retrieved: " . 
         ($retrieved ? 'true' : 'false') . " (esperado: " . ($expected ? 'true' : 'false') . ")\n";
}

// Limpar teste
$db->prepare("DELETE FROM configuracoes WHERE chave = 'teste.valor'")->execute();

echo "\n✅ Testes concluídos!\n";
