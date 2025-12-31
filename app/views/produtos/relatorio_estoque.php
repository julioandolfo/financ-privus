<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="<?= $this->baseUrl('/produtos') ?>" 
               class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Relatório de Estoque Baixo</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Produtos que precisam de atenção urgente</p>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm mb-1">Produtos Críticos</p>
                    <p class="text-4xl font-bold"><?= $totalEstoqueBaixo ?></p>
                </div>
                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Total de Produtos</p>
                    <p class="text-4xl font-bold"><?= $totalProdutos ?></p>
                </div>
                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-amber-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm mb-1">% Crítico</p>
                    <p class="text-4xl font-bold"><?= number_format($percentualCritico, 1, ',', '.') ?>%</p>
                </div>
                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-violet-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">Valor em Risco</p>
                    <p class="text-3xl font-bold">R$ <?= number_format($valorEmRisco, 2, ',', '.') ?></p>
                </div>
                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabela de Produtos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($produtos)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">✅ Tudo certo!</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nenhum produto com estoque crítico no momento.</p>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-red-600 to-pink-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase">Produto</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase">Categoria</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase">Estoque Atual</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase">Estoque Mínimo</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase">Déficit</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase">Preço Venda</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($produtos as $produto): ?>
                        <?php 
                        $deficit = $produto['estoque_minimo'] - $produto['estoque'];
                        $percentualDeficit = $produto['estoque_minimo'] > 0 ? ($deficit / $produto['estoque_minimo']) * 100 : 0;
                        $nivelCritico = $percentualDeficit >= 100 ? 'critico' : ($percentualDeficit >= 50 ? 'alerta' : 'atencao');
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($produto['nome']) ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Cód: <?= htmlspecialchars($produto['codigo']) ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($produto['categoria_nome'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    <?php if ($nivelCritico === 'critico'): ?>
                                        bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    <?php elseif ($nivelCritico === 'alerta'): ?>
                                        bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200
                                    <?php else: ?>
                                        bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    <?php endif; ?>">
                                    <?= $produto['estoque'] ?> <?= htmlspecialchars($produto['unidade_medida']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-700 dark:text-gray-300 font-medium">
                                    <?= $produto['estoque_minimo'] ?> <?= htmlspecialchars($produto['unidade_medida']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                    <?php if ($deficit > 0): ?>
                                        -<?= $deficit ?>
                                    <?php else: ?>
                                        0
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-900 dark:text-white font-medium">
                                    R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= $this->baseUrl('/produtos/' . $produto['id']) ?>" 
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" 
                                       title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="<?= $this->baseUrl('/produtos/' . $produto['id'] . '/edit') ?>" 
                                       class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" 
                                       title="Editar Estoque">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
