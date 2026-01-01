<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">Relatórios Gerenciais</h1>
        <p class="text-gray-600 dark:text-gray-400">Análises e indicadores para tomada de decisão</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Relatório de Lucro -->
        <a href="<?= $this->baseUrl('/relatorios/lucro') ?>" 
           class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl shadow-xl border-2 border-green-200 dark:border-green-700 p-8 hover:shadow-2xl hover:border-green-500 dark:hover:border-green-500 transition-all transform hover:-translate-y-1">
            <div class="w-16 h-16 bg-green-500 dark:bg-green-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Relatório de Lucro</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-4">
                Análise de receitas, despesas e lucratividade por período
            </p>
            <div class="flex items-center text-green-600 dark:text-green-400 font-semibold">
                Visualizar
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>

        <!-- Relatório de Margem -->
        <a href="<?= $this->baseUrl('/relatorios/margem') ?>" 
           class="group bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl shadow-xl border-2 border-blue-200 dark:border-blue-700 p-8 hover:shadow-2xl hover:border-blue-500 dark:hover:border-blue-500 transition-all transform hover:-translate-y-1">
            <div class="w-16 h-16 bg-blue-500 dark:bg-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Análise de Margem</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-4">
                Margem de lucro por produto e rentabilidade
            </p>
            <div class="flex items-center text-blue-600 dark:text-blue-400 font-semibold">
                Visualizar
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>

        <!-- Relatório de Inadimplência -->
        <a href="<?= $this->baseUrl('/relatorios/inadimplencia') ?>" 
           class="group bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-2xl shadow-xl border-2 border-red-200 dark:border-red-700 p-8 hover:shadow-2xl hover:border-red-500 dark:hover:border-red-500 transition-all transform hover:-translate-y-1">
            <div class="w-16 h-16 bg-red-500 dark:bg-red-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Inadimplência</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-4">
                Contas vencidas, maiores devedores e taxa de inadimplência
            </p>
            <div class="flex items-center text-red-600 dark:text-red-400 font-semibold">
                Visualizar
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
