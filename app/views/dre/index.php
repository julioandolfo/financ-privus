<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">DRE - Demonstrativo de Resultado do Exercício</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Análise de receitas, despesas e resultado por competência</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <form method="GET" action="/dre" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Empresa
                    </label>
                    <select name="empresa_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas (Consolidado)</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= $filters['empresa_id'] == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Início -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data Início (Competência)
                    </label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Data Fim -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data Fim (Competência)
                    </label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filters['data_fim']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Botões -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Gerar DRE
                    </button>
                    <a href="/dre" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Receitas -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border-2 border-green-200 dark:border-green-700">
            <div class="flex items-center mb-2">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                </svg>
                <p class="text-sm font-semibold text-green-700 dark:text-green-400">Receitas</p>
            </div>
            <p class="text-3xl font-bold text-green-800 dark:text-green-300">
                R$ <?= number_format($totais['receitas'], 2, ',', '.') ?>
            </p>
            <p class="text-xs text-green-600 dark:text-green-400 mt-1"><?= count($receitas) ?> categoria(s)</p>
        </div>

        <!-- Total Despesas -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-6 border-2 border-red-200 dark:border-red-700">
            <div class="flex items-center mb-2">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                </svg>
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Despesas</p>
            </div>
            <p class="text-3xl font-bold text-red-800 dark:text-red-300">
                R$ <?= number_format($totais['despesas'], 2, ',', '.') ?>
            </p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1"><?= count($despesas) ?> categoria(s)</p>
        </div>

        <!-- Resultado Líquido -->
        <div class="bg-gradient-to-br from-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-50 to-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-100 dark:from-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-900/20 dark:to-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-800/20 rounded-xl p-6 border-2 border-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-200 dark:border-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-700">
            <div class="flex items-center mb-2">
                <svg class="w-6 h-6 text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-600 dark:text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm font-semibold text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-700 dark:text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-400">Resultado Líquido</p>
            </div>
            <p class="text-3xl font-bold text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-800 dark:text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-300">
                R$ <?= number_format($totais['resultado'], 2, ',', '.') ?>
            </p>
            <p class="text-xs text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-600 dark:text-<?= $totais['resultado'] >= 0 ? 'blue' : 'orange' ?>-400 mt-1">
                <?= $totais['resultado'] >= 0 ? 'Lucro' : 'Prejuízo' ?>
            </p>
        </div>

        <!-- Margem Líquida -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-6 border-2 border-purple-200 dark:border-purple-700">
            <div class="flex items-center mb-2">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <p class="text-sm font-semibold text-purple-700 dark:text-purple-400">Margem Líquida</p>
            </div>
            <p class="text-3xl font-bold text-purple-800 dark:text-purple-300">
                <?= number_format($totais['margem'], 1, ',', '.') ?>%
            </p>
            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Rentabilidade</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Receitas Detalhadas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                    Receitas por Categoria
                </h2>
            </div>
            <div class="p-6">
                <?php if (empty($receitas)): ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhuma receita no período</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($receitas as $receita): 
                            $percentual = $totais['receitas'] > 0 ? ($receita['valor'] / $totais['receitas']) * 100 : 0;
                        ?>
                            <div class="border-l-4 border-green-500 pl-4 py-2">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($receita['categoria']) ?>
                                    </span>
                                    <span class="text-green-600 dark:text-green-400 font-bold">
                                        R$ <?= number_format($receita['valor'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: <?= $percentual ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        <?= number_format($percentual, 1) ?>%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?= $receita['quantidade'] ?> conta(s)
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Despesas Detalhadas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                    </svg>
                    Despesas por Categoria
                </h2>
            </div>
            <div class="p-6">
                <?php if (empty($despesas)): ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Nenhuma despesa no período</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($despesas as $despesa): 
                            $percentual = $totais['despesas'] > 0 ? ($despesa['valor'] / $totais['despesas']) * 100 : 0;
                        ?>
                            <div class="border-l-4 border-red-500 pl-4 py-2">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($despesa['categoria']) ?>
                                    </span>
                                    <span class="text-red-600 dark:text-red-400 font-bold">
                                        R$ <?= number_format($despesa['valor'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: <?= $percentual ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        <?= number_format($percentual, 1) ?>%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?= $despesa['quantidade'] ?> conta(s)
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos Comparativos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Gráfico de Pizza - Receitas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Composição das Receitas</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="receitasChart"></canvas>
            </div>
        </div>

        <!-- Gráfico de Pizza - Despesas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Composição das Despesas</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="despesasChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#1f2937';
    
    // Gráfico de Receitas
    const receitasCtx = document.getElementById('receitasChart');
    if (receitasCtx && <?= count($receitas) ?> > 0) {
        new Chart(receitasCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $grafico['categorias_receitas'] ?>,
                datasets: [{
                    data: <?= $grafico['valores_receitas'] ?>,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(5, 150, 105, 0.8)',
                        'rgba(4, 120, 87, 0.8)',
                        'rgba(6, 95, 70, 0.8)',
                        'rgba(6, 78, 59, 0.8)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = 'R$ ' + context.parsed.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1) + '%';
                                return label + ': ' + value + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Gráfico de Despesas
    const despesasCtx = document.getElementById('despesasChart');
    if (despesasCtx && <?= count($despesas) ?> > 0) {
        new Chart(despesasCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $grafico['categorias_despesas'] ?>,
                datasets: [{
                    data: <?= $grafico['valores_despesas'] ?>,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(220, 38, 38, 0.8)',
                        'rgba(185, 28, 28, 0.8)',
                        'rgba(153, 27, 27, 0.8)',
                        'rgba(127, 29, 29, 0.8)',
                        'rgba(109, 40, 40, 0.8)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = 'R$ ' + context.parsed.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1) + '%';
                                return label + ': ' + value + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
