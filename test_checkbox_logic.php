#!/usr/bin/env php
<?php
/**
 * Script para testar lógica de checkboxes
 */

echo "========================================\n";
echo "TESTE DE LÓGICA DE CHECKBOXES\n";
echo "========================================\n\n";

// Simular dados recebidos do formulário
$scenarios = [
    'Todos marcados' => [
        'grupo' => 'empresas',
        'empresas.codigo_obrigatorio' => 'true',
        'empresas.codigo_auto_gerado' => 'true',
        'empresas.cnpj_obrigatorio' => 'true'
    ],
    'Todos desmarcados' => [
        'grupo' => 'empresas'
        // Nenhum checkbox enviado
    ],
    'Misto' => [
        'grupo' => 'empresas',
        'empresas.codigo_obrigatorio' => 'true',
        // codigo_auto_gerado não enviado (desmarcado)
        'empresas.cnpj_obrigatorio' => 'true'
    ]
];

// Configurações do grupo (simulado)
$configsGrupo = [
    'empresas.codigo_obrigatorio' => ['tipo' => 'boolean'],
    'empresas.codigo_auto_gerado' => ['tipo' => 'boolean'],
    'empresas.cnpj_obrigatorio' => ['tipo' => 'boolean']
];

foreach ($scenarios as $nome => $data) {
    echo "Cenário: {$nome}\n";
    echo str_repeat("-", 60) . "\n";
    
    $grupo = $data['grupo'];
    unset($data['grupo']);
    
    $configuracoes = [];
    
    // LÓGICA NOVA: Processar checkboxes
    foreach ($configsGrupo as $chave => $config) {
        if ($config['tipo'] === 'boolean') {
            // Se o checkbox foi enviado no POST, está marcado (true)
            // Se não foi enviado, está desmarcado (false)
            $configuracoes[$chave] = isset($data[$chave]);
        }
    }
    
    // Mostrar resultado
    foreach ($configuracoes as $chave => $valor) {
        $status = $valor ? '✓ MARCADO  ' : '✗ DESMARCADO';
        echo "  {$status} {$chave}\n";
    }
    
    echo "\n";
}

echo "✅ Testes concluídos!\n";
