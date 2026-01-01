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
<div class="animate-fade-in" x-data="{ showFiltro: false, showRunwayModal: false, empresasSelecionadas: <?= json_encode($filtro['empresas_ids']) ?> }">
    <!-- Hero Banner -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-800 dark:via-indigo-900 dark:to-purple-900 rounded-2xl shadow-2xl mb-8">
        <div class="absolute inset-0 bg-grid-white/10 z-0"></div>
        <div class="relative px-8 py-12 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold text-white mb-2">Dashboard</h1>
                    <p class="text-xl text-blue-100">Visão geral do sistema financeiro</p>
                </div>
                
                <!-- Indicador de Filtro -->
                <div class="flex items-center space-x-3 relative z-20">
                    <?php if ($filtro['ativo']): ?>
                        <div class="bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="text-white font-semibold"><?= $filtro['empresas_filtradas'] ?> de <?= $filtro['total_empresas'] ?> empresa(s)</span>
                        </div>
                    <?php else: ?>
                        <div class="bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-2 rounded-lg flex items-center space-x-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-white font-semibold">Todas as Empresas (<?= $filtro['total_empresas'] ?>)</span>
                        </div>
                    <?php endif; ?>
                    
                    <button 
                        type="button"
                        @click="showFiltro = !showFiltro"
                        class="bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-2 rounded-lg hover:bg-white/30 transition-all flex items-center space-x-2 cursor-pointer"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <span class="text-white font-semibold">Filtrar</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none z-0"></div>
    </div>

    <!-- Painel de Filtro -->
    <div x-show="showFiltro" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtrar por Empresas</h3>
            <button 
                @click="showFiltro = false"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form method="POST" action="/dashboard/filtrar">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php foreach ($todas_empresas as $empresa): ?>
                    <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            name="empresas[]" 
                            value="<?= $empresa['id'] ?>"
                            <?= in_array($empresa['id'], $filtro['empresas_ids']) ? 'checked' : '' ?>
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2"
                        >
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <button 
                        type="button"
                        onclick="document.querySelectorAll('input[name=\'empresas[]\']').forEach(cb => cb.checked = true)"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Selecionar Todas
                    </button>
                    <button 
                        type="button"
                        onclick="document.querySelectorAll('input[name=\'empresas[]\']').forEach(cb => cb.checked = false)"
                        class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 font-medium"
                    >
                        Limpar Seleção
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <?php if ($filtro['ativo']): ?>
                        <button 
                            type="button"
                            onclick="window.location.href='/dashboard/limpar-filtro'"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            Remover Filtro
                        </button>
                    <?php endif; ?>
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg"
                    >
                        Aplicar Filtro
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================
         MÉTRICAS FINANCEIRAS AVANÇADAS
         ======================================== -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Indicadores Financeiros</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Últimos <?= $metricas_financeiras['periodo'] ?></p>
            </div>
            <a href="/relatorios" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Ver Relatórios Detalhados
            </a>
        </div>

        <!-- Métricas Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Receitas -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold opacity-90">Receitas</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold mb-1">R$ <?= number_format($metricas_financeiras['receitas'], 2, ',', '.') ?></p>
                <p class="text-sm opacity-75">Valores recebidos</p>
            </div>

            <!-- Despesas -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold opacity-90">Despesas</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold mb-1">R$ <?= number_format($metricas_financeiras['despesas'], 2, ',', '.') ?></p>
                <p class="text-sm opacity-75">Valores pagos</p>
            </div>

            <!-- Lucro Líquido -->
            <div class="bg-gradient-to-br from-<?= $metricas_financeiras['lucro_liquido'] >= 0 ? 'blue' : 'amber' ?>-500 to-<?= $metricas_financeiras['lucro_liquido'] >= 0 ? 'blue' : 'amber' ?>-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold opacity-90">Lucro Líquido</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold mb-1">R$ <?= number_format($metricas_financeiras['lucro_liquido'], 2, ',', '.') ?></p>
                <p class="text-sm opacity-75">Margem: <?= number_format($metricas_financeiras['margem_liquida'], 1) ?>%</p>
            </div>

            <!-- EBITDA -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold opacity-90">EBITDA</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold mb-1">R$ <?= number_format($metricas_financeiras['ebitda'], 2, ',', '.') ?></p>
                <p class="text-sm opacity-75">Margem: <?= number_format($metricas_financeiras['margem_ebitda'], 1) ?>%</p>
            </div>
        </div>

        <!-- Métricas Secundárias -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6">
            <!-- Margem Bruta -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Margem Bruta</h4>
                    <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($metricas_financeiras['margem_bruta'], 1) ?>%</p>
            </div>

            <!-- ROI -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">ROI</h4>
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($metricas_financeiras['roi'], 1) ?>%</p>
            </div>

            <!-- Ticket Médio -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Ticket Médio</h4>
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($metricas_financeiras['ticket_medio'], 0, ',', '.') ?></p>
            </div>

            <!-- Inadimplência -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Inadimplência</h4>
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($metricas_financeiras['inadimplencia_taxa'], 1) ?>%</p>
            </div>

            <!-- Burn Rate / Runway -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Runway</h4>
                        <button 
                            @click="showRunwayModal = true"
                            type="button"
                            class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors flex items-center justify-center cursor-pointer"
                            title="O que é Runway?"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-rose-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    <?= $metricas_financeiras['runway'] > 24 ? '24+' : number_format($metricas_financeiras['runway'], 0) ?> meses
                </p>
            </div>
        </div>

        <!-- Detalhamento -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Análise de Rentabilidade -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Rentabilidade
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Lucro Bruto</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($metricas_financeiras['lucro_bruto'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Despesas Operacionais</span>
                        <span class="font-bold text-red-600 dark:text-red-400">R$ <?= number_format($metricas_financeiras['despesas_operacionais'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Margem de Contribuição</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400"><?= number_format($metricas_financeiras['margem_contribuicao'], 1) ?>%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Ponto de Equilíbrio</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">R$ <?= number_format($metricas_financeiras['ponto_equilibrio'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Análise de Caixa -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Fluxo de Caixa
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Saldo em Bancos</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($contas_bancarias['saldo_total'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Burn Rate (mensal)</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">R$ <?= number_format($metricas_financeiras['burn_rate'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Contas a Receber</span>
                        <span class="font-bold text-green-600 dark:text-green-400">R$ <?= number_format($contas_receber['total'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Contas a Pagar</span>
                        <span class="font-bold text-red-600 dark:text-red-400">R$ <?= number_format($contas_pagar['total'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Análise de Risco -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Riscos e Alertas
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Contas Vencidas</span>
                        <span class="font-bold text-red-600 dark:text-red-400"><?= number_format($metricas_financeiras['contas_vencidas']) ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Valor em Atraso</span>
                        <span class="font-bold text-red-600 dark:text-red-400">R$ <?= number_format($metricas_financeiras['inadimplencia_valor'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Movimentações Pendentes</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400"><?= number_format($movimentacoes_caixa['pendentes']) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Meses de Sobrevivência</span>
                        <span class="font-bold <?= $metricas_financeiras['runway'] < 3 ? 'text-red-600 dark:text-red-400' : ($metricas_financeiras['runway'] < 6 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400') ?>">
                            <?= $metricas_financeiras['runway'] > 24 ? '24+' : number_format($metricas_financeiras['runway'], 0) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
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

        <!-- Produtos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <a href="/produtos" class="text-orange-600 dark:text-orange-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Produtos</h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $totais['produtos'] ?></p>
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

    <!-- Métricas de Produtos -->
    <?php if (isset($produtos) && $produtos['total'] > 0): ?>
        <div class="bg-gradient-to-r from-orange-500 to-amber-600 dark:from-orange-700 dark:to-amber-800 rounded-2xl p-8 shadow-xl mb-8">
            <div class="mb-6">
                <h3 class="text-white text-2xl font-bold mb-2 flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Catálogo de Produtos
                </h3>
                <p class="text-white/80 text-sm">Análise financeira do seu estoque</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total de Produtos -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Total de Produtos</p>
                    <p class="text-3xl font-bold text-white"><?= $produtos['total'] ?></p>
                </div>

                <!-- Valor de Venda Total -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Valor Total (Venda)</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($produtos['valor_venda_total'], 2, ',', '.') ?></p>
                </div>

                <!-- Margem Média -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Margem Média</p>
                    <p class="text-3xl font-bold text-white"><?= number_format($produtos['margem_media'], 1, ',', '.') ?>%</p>
                </div>

                <!-- Lucro Potencial -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Lucro Potencial</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($produtos['lucro_potencial'], 2, ',', '.') ?></p>
                </div>
            </div>

            <!-- Produtos Destaque -->
            <?php if ($produtos['produto_mais_caro'] || $produtos['produto_mais_barato']): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <?php if ($produtos['produto_mais_caro']): ?>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-white/70 text-xs mb-2">🏆 Produto Mais Caro</p>
                            <p class="text-lg font-bold text-white"><?= htmlspecialchars($produtos['produto_mais_caro']['nome']) ?></p>
                            <p class="text-2xl font-bold text-white mt-1">R$ <?= number_format($produtos['produto_mais_caro']['preco_venda'], 2, ',', '.') ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($produtos['produto_mais_barato']): ?>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-white/70 text-xs mb-2">💰 Produto Mais Barato</p>
                            <p class="text-lg font-bold text-white"><?= htmlspecialchars($produtos['produto_mais_barato']['nome']) ?></p>
                            <p class="text-2xl font-bold text-white mt-1">R$ <?= number_format($produtos['produto_mais_barato']['preco_venda'], 2, ',', '.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Métricas de Pedidos -->
    <?php if (isset($pedidos) && ($pedidos['total_pedidos'] ?? 0) > 0): ?>
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 dark:from-purple-700 dark:to-pink-800 rounded-2xl p-8 shadow-xl mb-8">
            <div class="mb-6">
                <h3 class="text-white text-2xl font-bold mb-2 flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Pedidos Vinculados
                </h3>
                <p class="text-white/80 text-sm">Análise de vendas e performance</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total de Pedidos -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Total de Pedidos</p>
                    <p class="text-3xl font-bold text-white"><?= $pedidos['total_pedidos'] ?></p>
                </div>

                <!-- Valor Total -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Valor Total</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($pedidos['valor_total'], 2, ',', '.') ?></p>
                </div>

                <!-- Ticket Médio -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Ticket Médio</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($pedidos['ticket_medio'], 2, ',', '.') ?></p>
                </div>

                <!-- Lucro Total -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-1">Lucro Total</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($pedidos['lucro_total'], 2, ',', '.') ?></p>
                    <p class="text-xs text-white/70 mt-1">Margem: <?= number_format($pedidos['margem_lucro'], 1, ',', '.') ?>%</p>
                </div>
            </div>

            <!-- Status dos Pedidos -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <p class="text-white/70 text-xs mb-1">Pendentes</p>
                    <p class="text-xl font-bold text-white"><?= $pedidos['pendentes'] ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <p class="text-white/70 text-xs mb-1">Processando</p>
                    <p class="text-xl font-bold text-white"><?= $pedidos['processando'] ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <p class="text-white/70 text-xs mb-1">Concluídos</p>
                    <p class="text-xl font-bold text-white"><?= $pedidos['concluidos'] ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <p class="text-white/70 text-xs mb-1">Cancelados</p>
                    <p class="text-xl font-bold text-white"><?= $pedidos['cancelados'] ?></p>
                </div>
            </div>

            <!-- Pedidos por Origem -->
            <?php if (!empty($pedidosPorOrigem)): ?>
                <div class="mt-6 bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <p class="text-white/70 text-sm mb-3">Pedidos por Origem</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php foreach ($pedidosPorOrigem as $origem): ?>
                            <div class="bg-white/5 rounded-lg p-3">
                                <p class="text-white text-sm font-semibold uppercase"><?= htmlspecialchars($origem['origem']) ?></p>
                                <p class="text-white text-xl font-bold"><?= $origem['total'] ?> pedidos</p>
                                <p class="text-white/70 text-sm">R$ <?= number_format($origem['valor_total'], 2, ',', '.') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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

    <!-- Modal Runway -->
    <div x-show="showRunwayModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showRunwayModal = false"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showRunwayModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                
                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-rose-600 to-pink-600 px-8 py-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white">Runway (Pista de Pouso)</h2>
                                <p class="text-rose-100 text-sm">Métrica de Sobrevivência Financeira</p>
                            </div>
                        </div>
                        <button @click="showRunwayModal = false" 
                                class="text-white/80 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-8 py-6 space-y-6">
                    <!-- O que é -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            🛫 O que é Runway?
                        </p>
                        <p class="text-gray-700 dark:text-gray-300">
                            Runway é uma métrica financeira crucial que responde à pergunta:<br>
                            <strong class="text-blue-600 dark:text-blue-400">"Por quantos meses minha empresa pode operar com o dinheiro que tenho em caixa hoje?"</strong>
                        </p>
                    </div>

                    <!-- Cálculo -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            📊 Como é Calculado
                        </h3>
                        <div class="bg-gray-100 dark:bg-gray-700/50 rounded-xl p-4 font-mono text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Fórmula:</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                Runway = <span class="text-green-600">Saldo em Caixa</span> / <span class="text-red-600">Burn Rate</span>
                            </p>
                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <p><strong class="text-green-600">Saldo em Caixa:</strong> Todo dinheiro disponível (contas bancárias)</p>
                                <p><strong class="text-red-600">Burn Rate:</strong> Taxa de "queima" mensal = |Despesas - Receitas|</p>
                            </div>
                        </div>
                    </div>

                    <!-- Exemplo Prático -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            🎯 Exemplo Prático
                        </h3>
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4">
                            <p class="text-gray-700 dark:text-gray-300 mb-3">Se sua empresa tem:</p>
                            <ul class="space-y-2 mb-3">
                                <li class="flex items-start">
                                    <span class="text-green-600 font-bold mr-2">💰</span>
                                    <span class="text-gray-700 dark:text-gray-300"><strong>R$ 120.000</strong> em caixa</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 font-bold mr-2">🔥</span>
                                    <span class="text-gray-700 dark:text-gray-300"><strong>Burn Rate de R$ 10.000/mês</strong> (gasta R$ 10k a mais do que recebe)</span>
                                </li>
                            </ul>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border-2 border-blue-300 dark:border-blue-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Resultado:</p>
                                <p class="text-xl font-bold text-blue-600">Runway = R$ 120.000 / R$ 10.000 = <span class="text-2xl">12 meses</span></p>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 italic">
                                Isso significa que sua empresa pode operar por 12 meses antes do dinheiro acabar (se mantiver o padrão atual).
                            </p>
                        </div>
                    </div>

                    <!-- Interpretação -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            🚦 Interpretação dos Valores
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3 p-3 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-lg">
                                <span class="text-2xl">🔴</span>
                                <div class="flex-1">
                                    <p class="font-bold text-red-900 dark:text-red-100">< 3 meses - CRÍTICO</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">Ação urgente! Cortar custos ou buscar capital imediatamente</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3 p-3 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-r-lg">
                                <span class="text-2xl">🟡</span>
                                <div class="flex-1">
                                    <p class="font-bold text-amber-900 dark:text-amber-100">3-6 meses - ATENÇÃO</p>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">Planejar redução de custos ou aumento de receita</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3 p-3 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded-r-lg">
                                <span class="text-2xl">🟢</span>
                                <div class="flex-1">
                                    <p class="font-bold text-green-900 dark:text-green-100">6-12 meses - SAUDÁVEL</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">Situação boa, mas monitorar de perto</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 rounded-r-lg">
                                <span class="text-2xl">🟢</span>
                                <div class="flex-1">
                                    <p class="font-bold text-emerald-900 dark:text-emerald-100">> 12 meses - EXCELENTE</p>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Empresa financeiramente estável</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Por que é Importante -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            💡 Por que é Importante?
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-4 rounded-lg">
                                <p class="font-semibold text-purple-900 dark:text-purple-100 mb-1">📈 Planejamento Estratégico</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Saber quanto tempo você tem para ajustar o negócio</p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 p-4 rounded-lg">
                                <p class="font-semibold text-blue-900 dark:text-blue-100 mb-1">💼 Captação de Investimento</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Investidores sempre perguntam sobre runway</p>
                            </div>
                            <div class="bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 p-4 rounded-lg">
                                <p class="font-semibold text-red-900 dark:text-red-100 mb-1">🚨 Gestão de Crise</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Identificar problemas antes que seja tarde</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-lg">
                                <p class="font-semibold text-green-900 dark:text-green-100 mb-1">🎯 Tomada de Decisão</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Decidir se deve contratar, investir ou cortar custos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Seu Runway Atual -->
                    <div class="bg-gradient-to-r from-rose-500 to-pink-600 rounded-xl p-6 text-white">
                        <h3 class="text-xl font-bold mb-2 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            🔍 Seu Runway Atual
                        </h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-rose-100 mb-1">Meses de sobrevivência:</p>
                                <p class="text-4xl font-bold"><?= $metricas_financeiras['runway'] > 24 ? '24+' : number_format($metricas_financeiras['runway'], 0) ?> meses</p>
                            </div>
                            <div class="text-right">
                                <p class="text-rose-100 text-sm mb-1">Saldo em Caixa:</p>
                                <p class="text-xl font-bold">R$ <?= number_format($contas_bancarias['saldo_total'], 2, ',', '.') ?></p>
                                <p class="text-rose-100 text-sm mt-2">Burn Rate:</p>
                                <p class="text-xl font-bold">R$ <?= number_format($metricas_financeiras['burn_rate'], 2, ',', '.') ?>/mês</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900/50 px-8 py-4 rounded-b-2xl border-t border-gray-200 dark:border-gray-700">
                    <button @click="showRunwayModal = false" 
                            class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                        Entendi!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
