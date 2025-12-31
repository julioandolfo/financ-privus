<?php
// Calcula percentuais para evitar operador > dentro de atributos HTML
$pctUsuariosAtivos = $totais['usuarios'] > 0 ? ($usuarios['ativos'] / $totais['usuarios'] * 100) : 0;
$pctUsuariosInativos = $totais['usuarios'] > 0 ? ($usuarios['inativos'] / $totais['usuarios'] * 100) : 0;
$pctFornecedoresPF = $totais['fornecedores'] > 0 ? ($fornecedores['pf'] / $totais['fornecedores'] * 100) : 0;
$pctFornecedoresPJ = $totais['fornecedores'] > 0 ? ($fornecedores['pj'] / $totais['fornecedores'] * 100) : 0;
$pctClientesPF = $totais['clientes'] > 0 ? ($clientes['pf'] / $totais['clientes'] * 100) : 0;
$pctClientesPJ = $totais['clientes'] > 0 ? ($clientes['pj'] / $totais['clientes'] * 100) : 0;
$pctCategoriasReceita = $totais['categorias'] > 0 ? ($categorias['receita'] / $totais['categorias'] * 100) : 0;
$pctCategoriasDespesa = $totais['categorias'] > 0 ? ($categorias['despesa'] / $totais['categorias'] * 100) : 0;
?>
<div class="animate-fade-in">
    <!-- Hero Banner -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-800 dark:via-indigo-900 dark:to-purple-900 rounded-2xl shadow-2xl mb-8">
        <div class="absolute inset-0 bg-grid-white/10"></div>
        <div class="relative px-8 py-12">
            <h1 class="text-4xl font-extrabold text-white mb-2">Dashboard</h1>
            <p class="text-xl text-blue-100">Visão geral do sistema financeiro</p>
        </div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Cards de Totais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Empresas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <a href="/empresas" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Empresas</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['empresas'] ?></p>
        </div>

        <!-- Usuários -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <a href="/usuarios" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Usuários</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['usuarios'] ?></p>
        </div>

        <!-- Fornecedores -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <a href="/fornecedores" class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Fornecedores</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['fornecedores'] ?></p>
        </div>

        <!-- Clientes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <a href="/clientes" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Clientes</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['clientes'] ?></p>
        </div>
    </div>

    <!-- Linha 2: Configurações -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Categorias -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
                <a href="/categorias" class="text-cyan-600 dark:text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Categorias</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['categorias'] ?></p>
        </div>

        <!-- Centros de Custo -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-rose-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <a href="/centros-custo" class="text-rose-600 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Centros de Custo</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['centros_custo'] ?></p>
        </div>

        <!-- Formas de Pagamento -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <a href="/formas-pagamento" class="text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Formas de Pagamento</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['formas_pagamento'] ?></p>
        </div>

        <!-- Contas Bancárias -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <a href="/contas-bancarias" class="text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Contas Bancárias</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['contas_bancarias'] ?></p>
        </div>
    </div>

    <!-- Saldo Total das Contas -->
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 dark:from-emerald-700 dark:to-green-800 rounded-2xl p-8 shadow-xl mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-white text-lg font-semibold mb-2">Saldo Total em Contas Bancárias</h3>
                <p class="text-white/80 text-sm">Soma de todas as contas ativas</p>
            </div>
            <div class="text-right">
                <p class="text-4xl font-bold text-white">R$ <?= number_format($contas_bancarias['saldo_total'], 2, ',', '.') ?></p>
                <p class="text-white/80 text-sm mt-1"><?= $totais['contas_bancarias'] ?> conta(s)</p>
            </div>
        </div>
    </div>

    <!-- Gráficos e Detalhes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Usuários: Ativos vs Inativos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Usuários por Status</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativos</span>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400"><?= $usuarios['ativos'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full" style="width: <?= $pctUsuariosAtivos ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Inativos</span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400"><?= $usuarios['inativos'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-red-500 to-rose-600 h-3 rounded-full" style="width: <?= $pctUsuariosInativos ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fornecedores: PF vs PJ -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Fornecedores por Tipo</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Física</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400"><?= $fornecedores['pf'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full" style="width: <?= $pctFornecedoresPF ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Jurídica</span>
                        <span class="text-sm font-bold text-purple-600 dark:text-purple-400"><?= $fornecedores['pj'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 h-3 rounded-full" style="width: <?= $pctFornecedoresPJ ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes: PF vs PJ -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Clientes por Tipo</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Física</span>
                        <span class="text-sm font-bold text-cyan-600 dark:text-cyan-400"><?= $clientes['pf'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-cyan-500 to-blue-600 h-3 rounded-full" style="width: <?= $pctClientesPF ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Jurídica</span>
                        <span class="text-sm font-bold text-violet-600 dark:text-violet-400"><?= $clientes['pj'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-violet-500 to-purple-600 h-3 rounded-full" style="width: <?= $pctClientesPJ ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categorias: Receita vs Despesa -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Categorias por Tipo</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Receita</span>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400"><?= $categorias['receita'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full" style="width: <?= $pctCategoriasReceita ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Despesa</span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400"><?= $categorias['despesa'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-red-500 to-rose-600 h-3 rounded-full" style="width: <?= $pctCategoriasDespesa ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contas Bancárias por Tipo -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Contas Bancárias por Tipo</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= $contas_bancarias['corrente'] ?></span>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Conta Corrente</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400"><?= $contas_bancarias['poupanca'] ?></span>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Poupança</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 mx-auto bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?= $contas_bancarias['investimento'] ?></span>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Investimento</p>
            </div>
        </div>
    </div>

    <!-- Contas por Banco -->
    <?php if (!empty($contas_bancarias['por_banco'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Saldo por Banco</h3>
        <div class="space-y-4">
            <?php foreach ($contas_bancarias['por_banco'] as $banco => $dados): ?>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($banco) ?></span>
                    <div class="text-right">
                        <span class="text-sm font-bold <?= $dados['saldo'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                            R$ <?= number_format($dados['saldo'], 2, ',', '.') ?>
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">(<?= $dados['total'] ?> conta<?= $dados['total'] > 1 ? 's' : '' ?>)</span>
                    </div>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="<?= $dados['saldo'] >= 0 ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600' ?> h-2 rounded-full" 
                         style="width: <?= $contas_bancarias['saldo_total'] > 0 ? (abs($dados['saldo']) / abs($contas_bancarias['saldo_total']) * 100) : 0 ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Seção Contas a Pagar -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <svg class="w-7 h-7 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Contas a Pagar
        </h2>

        <!-- Cards Resumo Contas a Pagar -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total de Contas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <a href="/contas-pagar" class="text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Total de Contas</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $contas_pagar['total'] ?></p>
            </div>

            <!-- Valor a Pagar -->
            <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl p-6 shadow-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-white/90 mb-1">Valor Total a Pagar</h3>
                <p class="text-3xl font-bold">R$ <?= number_format($contas_pagar['valor_a_pagar'], 2, ',', '.') ?></p>
            </div>

            <!-- Contas Vencidas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border-2 border-red-500 dark:border-red-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Vencidas</h3>
                <p class="text-3xl font-bold text-red-600"><?= $contas_pagar['vencidas']['quantidade'] ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">R$ <?= number_format($contas_pagar['vencidas']['valor_total'], 2, ',', '.') ?></p>
            </div>

            <!-- A Vencer (7 dias) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border-2 border-amber-500 dark:border-amber-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">A Vencer (7 dias)</h3>
                <p class="text-3xl font-bold text-amber-600"><?= $contas_pagar['a_vencer_7d']['quantidade'] ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">R$ <?= number_format($contas_pagar['a_vencer_7d']['valor_total'], 2, ',', '.') ?></p>
            </div>
        </div>

        <!-- Contas por Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Status das Contas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Contas por Status</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pendente</span>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400"><?= $contas_pagar['por_status']['pendente'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <?php $pctPendente = $contas_pagar['total'] > 0 ? ($contas_pagar['por_status']['pendente'] / $contas_pagar['total'] * 100) : 0; ?>
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full" style="width: <?= $pctPendente ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Vencido</span>
                            <span class="text-sm font-bold text-red-600 dark:text-red-400"><?= $contas_pagar['por_status']['vencido'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <?php $pctVencido = $contas_pagar['total'] > 0 ? ($contas_pagar['por_status']['vencido'] / $contas_pagar['total'] * 100) : 0; ?>
                            <div class="bg-gradient-to-r from-red-500 to-rose-600 h-3 rounded-full" style="width: <?= $pctVencido ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Parcial</span>
                            <span class="text-sm font-bold text-amber-600 dark:text-amber-400"><?= $contas_pagar['por_status']['parcial'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <?php $pctParcial = $contas_pagar['total'] > 0 ? ($contas_pagar['por_status']['parcial'] / $contas_pagar['total'] * 100) : 0; ?>
                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-3 rounded-full" style="width: <?= $pctParcial ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pago</span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400"><?= $contas_pagar['por_status']['pago'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <?php $pctPago = $contas_pagar['total'] > 0 ? ($contas_pagar['por_status']['pago'] / $contas_pagar['total'] * 100) : 0; ?>
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full" style="width: <?= $pctPago ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparativo de Valores -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Comparativo de Valores</h3>
                <div class="space-y-6">
                    <div class="text-center p-4 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Valor a Pagar</p>
                        <p class="text-3xl font-bold text-red-600">R$ <?= number_format($contas_pagar['valor_a_pagar'], 2, ',', '.') ?></p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Valor Pago</p>
                        <p class="text-3xl font-bold text-green-600">R$ <?= number_format($contas_pagar['valor_pago'], 2, ',', '.') ?></p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">A Vencer (30 dias)</p>
                        <p class="text-2xl font-bold text-amber-600">R$ <?= number_format($contas_pagar['a_vencer_30d']['valor_total'], 2, ',', '.') ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= $contas_pagar['a_vencer_30d']['quantidade'] ?> conta<?= $contas_pagar['a_vencer_30d']['quantidade'] != 1 ? 's' : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção Contas a Receber -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <svg class="w-7 h-7 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Contas a Receber
        </h2>

        <!-- Cards Resumo Contas a Receber -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total de Contas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <a href="/contas-receber" class="text-green-600 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Total de Contas</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $contas_receber['total'] ?></p>
            </div>

            <!-- Valor a Receber -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 shadow-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-white/90 mb-1">Valor Total a Receber</h3>
                <p class="text-3xl font-bold">R$ <?= number_format($contas_receber['valor_a_receber'], 2, ',', '.') ?></p>
            </div>

            <!-- Contas Vencidas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border-2 border-amber-500 dark:border-amber-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Vencidas</h3>
                <p class="text-3xl font-bold text-amber-600"><?= $contas_receber['vencidas']['quantidade'] ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">R$ <?= number_format($contas_receber['vencidas']['valor_total'], 2, ',', '.') ?></p>
            </div>

            <!-- A Vencer (7 dias) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border-2 border-blue-500 dark:border-blue-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">A Vencer (7 dias)</h3>
                <p class="text-3xl font-bold text-blue-600"><?= $contas_receber['a_vencer_7d']['quantidade'] ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">R$ <?= number_format($contas_receber['a_vencer_7d']['valor_total'], 2, ',', '.') ?></p>
            </div>
        </div>

        <!-- Contas por Status e Comparativo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Status das Contas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Contas por Status</h3>
                <div class="space-y-4">
                    <?php 
                    $totalContasRec = $contas_receber['total'];
                    $pctPendenteRec = $totalContasRec > 0 ? ($contas_receber['por_status']['pendente'] / $totalContasRec * 100) : 0;
                    $pctVencidoRec = $totalContasRec > 0 ? ($contas_receber['por_status']['vencido'] / $totalContasRec * 100) : 0;
                    $pctParcialRec = $totalContasRec > 0 ? ($contas_receber['por_status']['parcial'] / $totalContasRec * 100) : 0;
                    $pctRecebido = $totalContasRec > 0 ? ($contas_receber['por_status']['recebido'] / $totalContasRec * 100) : 0;
                    ?>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pendente</span>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400"><?= $contas_receber['por_status']['pendente'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full" style="width: <?= $pctPendenteRec ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Vencido</span>
                            <span class="text-sm font-bold text-amber-600 dark:text-amber-400"><?= $contas_receber['por_status']['vencido'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-3 rounded-full" style="width: <?= $pctVencidoRec ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Parcial</span>
                            <span class="text-sm font-bold text-cyan-600 dark:text-cyan-400"><?= $contas_receber['por_status']['parcial'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 h-3 rounded-full" style="width: <?= $pctParcialRec ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Recebido</span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400"><?= $contas_receber['por_status']['recebido'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full" style="width: <?= $pctRecebido ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparativo de Valores -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Comparativo de Valores</h3>
                <div class="space-y-6">
                    <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Valor a Receber</p>
                        <p class="text-3xl font-bold text-green-600">R$ <?= number_format($contas_receber['valor_a_receber'], 2, ',', '.') ?></p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Valor Recebido</p>
                        <p class="text-3xl font-bold text-blue-600">R$ <?= number_format($contas_receber['valor_recebido'], 2, ',', '.') ?></p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-r from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">A Vencer (30 dias)</p>
                        <p class="text-2xl font-bold text-cyan-600">R$ <?= number_format($contas_receber['a_vencer_30d']['valor_total'], 2, ',', '.') ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= $contas_receber['a_vencer_30d']['quantidade'] ?> conta<?= $contas_receber['a_vencer_30d']['quantidade'] != 1 ? 's' : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
