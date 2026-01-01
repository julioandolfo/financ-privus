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
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">Relatório de Inadimplência</h1>
        <p class="text-gray-600 dark:text-gray-400">Contas vencidas e análise de devedores</p>
    </div>

    <!-- Filtros -->
    <form method="GET" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Valor Vencido</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">R$ <?= number_format($valorVencido, 2, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Total a Receber</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">R$ <?= number_format($valorTotal, 2, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Taxa de Inadimplência</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold"><?= number_format($taxaInadimplencia, 1) ?>%</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Clientes Inadimplentes</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold"><?= number_format($totalClientes) ?></p>
        </div>
    </div>

    <!-- Maiores Devedores -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Maiores Devedores</h2>
        <div class="space-y-4">
            <?php if (empty($contasPorCliente)): ?>
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">Nenhum cliente inadimplente</p>
            <?php else: ?>
                <?php 
                $maxValor = max(array_column($contasPorCliente, 'total_vencido'));
                $count = 0;
                foreach ($contasPorCliente as $clienteId => $cliente): 
                    if ($count >= 10) break; // Mostra apenas top 10
                    $count++;
                    $percentual = $maxValor > 0 ? ($cliente['total_vencido'] / $maxValor) * 100 : 0;
                ?>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($cliente['cliente_nome']) ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= $cliente['quantidade'] ?> conta(s) vencida(s)</p>
                        </div>
                        <span class="text-red-600 dark:text-red-400 font-bold text-xl">R$ <?= number_format($cliente['total_vencido'], 2, ',', '.') ?></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full transition-all" style="width: <?= $percentual ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalhamento de Contas Vencidas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detalhamento de Contas Vencidas</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-red-600 to-red-700 text-white">
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase">Descrição</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase">Vencimento</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase">Dias Atraso</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($contasVencidas)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                            Nenhuma conta vencida
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($contasVencidas as $conta): 
                            $diasAtraso = (new DateTime())->diff(new DateTime($conta['data_vencimento']))->days;
                            $corAtraso = 'gray';
                            if ($diasAtraso > 90) {
                                $corAtraso = 'red';
                            } elseif ($diasAtraso > 60) {
                                $corAtraso = 'orange';
                            } elseif ($diasAtraso > 30) {
                                $corAtraso = 'amber';
                            } else {
                                $corAtraso = 'yellow';
                            }
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['cliente_nome'] ?? 'N/A') ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($conta['descricao']) ?>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-900 dark:text-gray-100">
                                <?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-3 py-1 bg-<?= $corAtraso ?>-100 dark:bg-<?= $corAtraso ?>-900/30 text-<?= $corAtraso ?>-800 dark:text-<?= $corAtraso ?>-400 text-sm font-bold rounded-full">
                                    <?= $diasAtraso ?> dias
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-red-600 dark:text-red-400">
                                    R$ <?= number_format($conta['valor'], 2, ',', '.') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legenda de Atraso -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Legenda de Atraso</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-yellow-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">Até 30 dias</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-amber-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">31-60 dias</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-orange-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">61-90 dias</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-red-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">> 90 dias</span>
            </div>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
