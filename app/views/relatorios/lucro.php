<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/relatorios') ?>" 
           class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">Relatório de Lucro</h1>
        <p class="text-gray-600 dark:text-gray-400">Análise de receitas, despesas e lucratividade</p>
    </div>

    <!-- Filtros -->
    <form method="GET" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                <select name="empresa_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= $empresaSelecionada == $empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="<?= $dataInicio ?>" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                <input type="date" name="data_fim" value="<?= $dataFim ?>" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Total de Receitas</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">R$ <?= number_format($receitas, 2, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Total de Despesas</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">R$ <?= number_format($despesas, 2, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-<?= $lucroBruto >= 0 ? 'blue' : 'amber' ?>-500 to-<?= $lucroBruto >= 0 ? 'blue' : 'amber' ?>-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Lucro Bruto</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">R$ <?= number_format($lucroBruto, 2, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Margem Líquida</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($margemLiquida, 1) ?>%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Receitas por Categoria -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Receitas por Categoria</h2>
            <div class="space-y-4">
                <?php if (empty($receitasPorCategoria)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center py-8">Nenhuma receita registrada no período</p>
                <?php else: ?>
                    <?php 
                    $maxReceita = max(array_column($receitasPorCategoria, 'total'));
                    foreach ($receitasPorCategoria as $receita): 
                        $percentual = $maxReceita > 0 ? ($receita['total'] / $maxReceita) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($receita['categoria']) ?></span>
                            <span class="text-green-600 dark:text-green-400 font-bold">R$ <?= number_format($receita['total'], 2, ',', '.') ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all" style="width: <?= $percentual ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Despesas por Categoria -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Despesas por Categoria</h2>
            <div class="space-y-4">
                <?php if (empty($despesasPorCategoria)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center py-8">Nenhuma despesa registrada no período</p>
                <?php else: ?>
                    <?php 
                    $maxDespesa = max(array_column($despesasPorCategoria, 'total'));
                    foreach ($despesasPorCategoria as $despesa): 
                        $percentual = $maxDespesa > 0 ? ($despesa['total'] / $maxDespesa) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($despesa['categoria']) ?></span>
                            <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($despesa['total'], 2, ',', '.') ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full transition-all" style="width: <?= $percentual ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Evolução Mensal -->
    <?php if (!empty($evolucaoMensal)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Evolução Mensal</h2>
        <canvas id="chartEvolucao" height="80"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('chartEvolucao');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($evolucaoMensal, 'mes')) ?>,
            datasets: [
                {
                    label: 'Receitas',
                    data: <?= json_encode(array_column($evolucaoMensal, 'receitas')) ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Despesas',
                    data: <?= json_encode(array_column($evolucaoMensal, 'despesas')) ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    </script>
    <?php endif; ?>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
