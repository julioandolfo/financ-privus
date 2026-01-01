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
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">Análise de Margem</h1>
        <p class="text-gray-600 dark:text-gray-400">Margem de lucro e rentabilidade por produto</p>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Total de Produtos</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($totalProdutos) ?></p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Margem Média</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($margemMedia, 1) ?>%</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Com Preço Definido</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($totalProdutos) ?></p>
        </div>
    </div>

    <!-- Tabela de Produtos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Produtos por Margem de Lucro</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase">Produto</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase">Preço Custo</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase">Preço Venda</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase">Lucro Unitário</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase">Margem %</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                            Nenhum produto encontrado
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($produto['nome']) ?></div>
                                <?php if ($produto['codigo']): ?>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Código: <?= htmlspecialchars($produto['codigo']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900 dark:text-gray-100">
                                R$ <?= number_format($produto['preco_custo'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900 dark:text-gray-100 font-semibold">
                                R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold <?= $produto['lucro_unitario'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    R$ <?= number_format($produto['lucro_unitario'], 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php 
                                $margemColor = 'gray';
                                if ($produto['margem'] >= 30) {
                                    $margemColor = 'green';
                                } elseif ($produto['margem'] >= 15) {
                                    $margemColor = 'blue';
                                } elseif ($produto['margem'] >= 5) {
                                    $margemColor = 'amber';
                                } else {
                                    $margemColor = 'red';
                                }
                                ?>
                                <span class="inline-flex px-3 py-1 bg-<?= $margemColor ?>-100 dark:bg-<?= $margemColor ?>-900/30 text-<?= $margemColor ?>-800 dark:text-<?= $margemColor ?>-400 text-sm font-bold rounded-full">
                                    <?= number_format($produto['margem'], 1) ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($produto['ativo']): ?>
                                <span class="inline-flex px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-bold rounded-full">
                                    Ativo
                                </span>
                                <?php else: ?>
                                <span class="inline-flex px-3 py-1 bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400 text-xs font-bold rounded-full">
                                    Inativo
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legenda de Margem -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Legenda de Margem</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-green-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">≥ 30% - Excelente</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-blue-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">15-29% - Boa</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-amber-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">5-14% - Regular</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 bg-red-500 rounded"></span>
                <span class="text-gray-900 dark:text-gray-100">< 5% - Baixa</span>
            </div>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
