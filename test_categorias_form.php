#!/usr/bin/env php
<?php
/**
 * Simula envio do formulário de categorias
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
echo "SIMULANDO FORMULÁRIO DE CATEGORIAS\n";
echo "========================================\n\n";

// 1. Estado atual
echo "1. ESTADO ATUAL:\n";
Configuracao::clearCache();
$configs = Configuracao::getGrupo('categorias');
foreach ($configs as $chave => $config) {
    $valor = $config['valor'] ? 'TRUE (marcado)' : 'FALSE (desmarcado)';
    echo "   {$chave}: {$valor}\n";
}
echo "\n";

// 2. Simular formulário com categorias.codigo_auto_gerado MARCADO
echo "2. SIMULANDO FORMULÁRIO (categorias.codigo_auto_gerado MARCADO):\n";
echo "   POST dados simulados:\n";

// O que o formulário envia quando o checkbox está MARCADO:
$postData = [
    'grupo' => 'categorias',
    'categorias.codigo_auto_gerado' => 'true'  // Note: vem como STRING 'true'
    // categorias.codigo_obrigatorio NÃO vem (desmarcado)
    // categorias.hierarquia_habilitada NÃO vem (desmarcado)
];

foreach ($postData as $k => $v) {
    echo "     {$k} => {$v}\n";
}
echo "\n";

// 3. Processar como o controller faz
echo "3. PROCESSANDO COMO CONTROLLER:\n";
$grupo = $postData['grupo'];
unset($postData['grupo']);

$configsGrupo = Configuracao::getGrupo($grupo);
$configuracoes = [];

// Passo 1: Processar checkboxes
echo "   Processando checkboxes (boolean):\n";
foreach ($configsGrupo as $chave => $config) {
    if ($config['tipo'] === 'boolean') {
        // Se o checkbox foi enviado no POST, está marcado (true)
        $marcado = isset($postData[$chave]);
        $configuracoes[$chave] = $marcado;
        $status = $marcado ? 'TRUE (marcado)' : 'FALSE (desmarcado)';
        echo "     {$chave}: {$status}\n";
    }
}
echo "\n";

// 4. Salvar
echo "4. SALVANDO:\n";
$success = Configuracao::setMultiplas($configuracoes);
echo "   Resultado: " . ($success ? 'SUCESSO' : 'FALHA') . "\n\n";

// 5. Verificar
echo "5. VERIFICANDO APÓS SALVAR:\n";
Configuracao::clearCache();
$configs = Configuracao::getGrupo('categorias');
foreach ($configs as $chave => $config) {
    $esperado = $configuracoes[$chave] ?? null;
    $atual = $config['valor'];
    $match = $esperado === $atual ? '✓' : '✗';
    $esperadoStr = $esperado ? 'TRUE' : 'FALSE';
    $atualStr = $atual ? 'TRUE' : 'FALSE';
    echo "   {$match} {$chave}: esperado={$esperadoStr}, atual={$atualStr}\n";
}
echo "\n";

// 6. Teste inverso: TODOS DESMARCADOS
echo "6. TESTE INVERSO (todos desmarcados):\n";
$postData = [
    'grupo' => 'categorias'
    // Nenhum checkbox enviado
];

$grupo = $postData['grupo'];
unset($postData['grupo']);

$configuracoes = [];
foreach ($configsGrupo as $chave => $config) {
    if ($config['tipo'] === 'boolean') {
        $marcado = isset($postData[$chave]);
        $configuracoes[$chave] = $marcado;
    }
}

$success = Configuracao::setMultiplas($configuracoes);
echo "   Salvamento: " . ($success ? 'SUCESSO' : 'FALHA') . "\n\n";

// 7. Verificar
echo "7. VERIFICANDO APÓS SALVAR (esperado: todos FALSE):\n";
Configuracao::clearCache();
$configs = Configuracao::getGrupo('categorias');
foreach ($configs as $chave => $config) {
    $esperado = false;
    $atual = $config['valor'];
    $match = $esperado === $atual ? '✓' : '✗';
    $atualStr = $atual ? 'TRUE' : 'FALSE';
    echo "   {$match} {$chave}: {$atualStr}\n";
}

echo "\n✅ Teste concluído!\n";
