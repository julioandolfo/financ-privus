#!/usr/bin/env php
<?php
/**
 * Teste completo do fluxo de configurações
 */

define('APP_ROOT', __DIR__);
define('APP_DEBUG', true);

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/models/Configuracao.php';

use App\Models\Configuracao;

echo "========================================\n";
echo "TESTE COMPLETO DE FLUXO\n";
echo "========================================\n\n";

// Limpar cache
Configuracao::clearCache();

// 1. Ler valores atuais
echo "1. VALORES ATUAIS:\n";
$chaves = [
    'empresas.codigo_obrigatorio',
    'empresas.codigo_auto_gerado',
    'empresas.cnpj_obrigatorio'
];

$valoresOriginais = [];
foreach ($chaves as $chave) {
    $valor = Configuracao::get($chave);
    $valoresOriginais[$chave] = $valor;
    echo "   {$chave}: " . ($valor ? 'TRUE' : 'FALSE') . "\n";
}
echo "\n";

// 2. Simular envio do formulário com TODOS MARCADOS
echo "2. SIMULANDO FORMULÁRIO (todos marcados):\n";
$configuracoes = [
    'empresas.codigo_obrigatorio' => true,  // boolean true
    'empresas.codigo_auto_gerado' => true,
    'empresas.cnpj_obrigatorio' => true
];

$success = Configuracao::setMultiplas($configuracoes);
echo "   Salvamento: " . ($success ? 'SUCESSO' : 'FALHA') . "\n\n";

// 3. Verificar se foram salvos
echo "3. VERIFICANDO APÓS SALVAR (esperado: todos TRUE):\n";
Configuracao::clearCache();
$allCorrect = true;
foreach ($configuracoes as $chave => $esperado) {
    $atual = Configuracao::get($chave);
    $match = $atual === $esperado;
    $allCorrect = $allCorrect && $match;
    $symbol = $match ? '✓' : '✗';
    echo "   {$symbol} {$chave}: esperado=" . ($esperado ? 'TRUE' : 'FALSE') . 
         ", atual=" . ($atual ? 'TRUE' : 'FALSE') . "\n";
}
echo "   Resultado: " . ($allCorrect ? '✅ TODOS CORRETOS' : '❌ FALHOU') . "\n\n";

// 4. Simular formulário com TODOS DESMARCADOS
echo "4. SIMULANDO FORMULÁRIO (todos desmarcados):\n";
$configuracoes = [
    'empresas.codigo_obrigatorio' => false,  // boolean false
    'empresas.codigo_auto_gerado' => false,
    'empresas.cnpj_obrigatorio' => false
];

$success = Configuracao::setMultiplas($configuracoes);
echo "   Salvamento: " . ($success ? 'SUCESSO' : 'FALHA') . "\n\n";

// 5. Verificar se foram salvos
echo "5. VERIFICANDO APÓS SALVAR (esperado: todos FALSE):\n";
Configuracao::clearCache();
$allCorrect = true;
foreach ($configuracoes as $chave => $esperado) {
    $atual = Configuracao::get($chave);
    $match = $atual === $esperado;
    $allCorrect = $allCorrect && $match;
    $symbol = $match ? '✓' : '✗';
    echo "   {$symbol} {$chave}: esperado=" . ($esperado ? 'TRUE' : 'FALSE') . 
         ", atual=" . ($atual ? 'TRUE' : 'FALSE') . "\n";
}
echo "   Resultado: " . ($allCorrect ? '✅ TODOS CORRETOS' : '❌ FALHOU') . "\n\n";

// 6. Simular formulário MISTO
echo "6. SIMULANDO FORMULÁRIO (misto):\n";
$configuracoes = [
    'empresas.codigo_obrigatorio' => true,   // marcado
    'empresas.codigo_auto_gerado' => false,  // desmarcado
    'empresas.cnpj_obrigatorio' => true      // marcado
];

$success = Configuracao::setMultiplas($configuracoes);
echo "   Salvamento: " . ($success ? 'SUCESSO' : 'FALHA') . "\n\n";

// 7. Verificar se foram salvos
echo "7. VERIFICANDO APÓS SALVAR (esperado: misto):\n";
Configuracao::clearCache();
$allCorrect = true;
foreach ($configuracoes as $chave => $esperado) {
    $atual = Configuracao::get($chave);
    $match = $atual === $esperado;
    $allCorrect = $allCorrect && $match;
    $symbol = $match ? '✓' : '✗';
    echo "   {$symbol} {$chave}: esperado=" . ($esperado ? 'TRUE' : 'FALSE') . 
         ", atual=" . ($atual ? 'TRUE' : 'FALSE') . "\n";
}
echo "   Resultado: " . ($allCorrect ? '✅ TODOS CORRETOS' : '❌ FALHOU') . "\n\n";

// 8. Restaurar valores originais
echo "8. RESTAURANDO VALORES ORIGINAIS:\n";
Configuracao::setMultiplas($valoresOriginais);
echo "   Valores restaurados.\n\n";

echo "========================================\n";
echo "✅ TESTE COMPLETO CONCLUÍDO\n";
echo "========================================\n";
