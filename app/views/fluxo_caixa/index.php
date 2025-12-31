<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Fluxo de Caixa</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Visualize entradas, saídas e saldo ao longo do tempo</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <form method="GET" action="/fluxo-caixa" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Empresa
                    </label>
                    <select name="empresa_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= $filters['empresa_id'] == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Conta Bancária -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Conta Bancária
                    </label>
                    <select name="conta_bancaria_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        <?php foreach ($contasBancarias as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= $filters['conta_bancaria_id'] == $conta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco_nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Início -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data Início
                    </label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Data Fim -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data Fim
                    </label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filters['data_fim']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Agrupar Por -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Agrupar Por
                    </label>
                    <select name="agrupar_por" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="dia" <?= $filters['agrupar_por'] === 'dia' ? 'selected' : '' ?>>Por Dia</option>
                        <option value="semana" <?= $filters['agrupar_por'] === 'semana' ? 'selected' : '' ?>>Por Semana</option>
                        <option value="mes" <?= $filters['agrupar_por'] === 'mes' ? 'selected' : '' ?>>Por Mês</option>
                    </select>
                </div>

                <!-- Visualização -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Visualização
                    </label>
                    <select name="visualizacao" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="grafico" <?= $filters['visualizacao'] === 'grafico' ? 'selected' : '' ?>>Gráfico</option>
                        <option value="tabela" <?= $filters['visualizacao'] === 'tabela' ? 'selected' : '' ?>>Tabela</option>
                        <option value="ambos" <?= $filters['visualizacao'] === 'ambos' ? 'selected' : '' ?>>Ambos</option>
                    </select>
                </div>

                <!-- Botões -->
                <div class="flex items-end space-x-2 md:col-span-2">
                    <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Aplicar Filtros
                    </button>
                    <a href="/fluxo-caixa" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Saldo Inicial -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-400 mb-2">Saldo Inicial</p>
            <p class="text-3xl font-bold text-blue-800 dark:text-blue-300">
                R$ <?= number_format($totais['saldo_inicial'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Total Entradas -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
            <p class="text-sm font-semibold text-green-700 dark:text-green-400 mb-2">Entradas no Período</p>
            <p class="text-3xl font-bold text-green-800 dark:text-green-300">
                R$ <?= number_format($totais['entradas'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Total Saídas -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-6 border border-red-200 dark:border-red-700">
            <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-2">Saídas no Período</p>
            <p class="text-3xl font-bold text-red-800 dark:text-red-300">
                R$ <?= number_format($totais['saidas'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Saldo Final -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-700">
            <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400 mb-2">Saldo Final</p>
            <p class="text-3xl font-bold <?= $totais['saldo_final'] >= 0 ? 'text-indigo-800 dark:text-indigo-300' : 'text-red-800 dark:text-red-300' ?>">
                R$ <?= number_format($totais['saldo_final'], 2, ',', '.') ?>
            </p>
        </div>
    </div>

    <!-- Gráfico -->
    <?php if (in_array($filters['visualizacao'], ['grafico', 'ambos'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Visualização Gráfica</h2>
            <div class="relative" style="height: 400px;">
                <canvas id="fluxoCaixaChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabela Detalhada -->
    <?php if (in_array($filters['visualizacao'], ['tabela', 'ambos'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detalhamento por Período</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Período</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Entradas</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Saídas</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Saldo</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Movimentações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php 
                        $saldoAcumulado = $totais['saldo_inicial'];
                        foreach ($dadosAgrupados as $periodo => $dados): 
                            $saldoAcumulado += $dados['saldo'];
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php
                                    switch ($filters['agrupar_por']) {
                                        case 'dia':
                                            echo date('d/m/Y', strtotime($periodo));
                                            break;
                                        case 'semana':
                                            $parts = explode('-W', $periodo);
                                            echo 'Semana ' . $parts[1] . '/' . $parts[0];
                                            break;
                                        case 'mes':
                                            echo date('m/Y', strtotime($periodo . '-01'));
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600 dark:text-green-400">
                                    R$ <?= number_format($dados['entradas'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-red-600 dark:text-red-400">
                                    R$ <?= number_format($dados['saidas'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold <?= $dados['saldo'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' ?>">
                                    R$ <?= number_format($dados['saldo'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-400">
                                    <?= count($dados['movimentacoes']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50 font-semibold">
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">TOTAL</td>
                            <td class="px-6 py-4 text-right text-sm text-green-600 dark:text-green-400">
                                R$ <?= number_format($totais['entradas'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-red-600 dark:text-red-400">
                                R$ <?= number_format($totais['saidas'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-blue-600 dark:text-blue-400">
                                R$ <?= number_format($totais['saldo_periodo'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (in_array($filters['visualizacao'], ['grafico', 'ambos'])): ?>
    const ctx = document.getElementById('fluxoCaixaChart');
    if (ctx) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#e5e7eb' : '#1f2937';
        const gridColor = isDark ? '#374151' : '#e5e7eb';
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $grafico['labels'] ?>,
                datasets: [
                    {
                        label: 'Entradas',
                        data: <?= $grafico['entradas'] ?>,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Saídas',
                        data: <?= $grafico['saidas'] ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Saldo Acumulado',
                        data: <?= $grafico['saldo'] ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: gridColor,
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor,
                            display: true
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: gridColor,
                            display: true
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
