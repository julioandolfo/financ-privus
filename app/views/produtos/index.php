<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Produtos</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie o catálogo de produtos da sua empresa</p>
        </div>
        <a href="/produtos/create" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Novo Produto</span>
        </a>
    </div>

    <!-- Cards de Estatísticas -->
    <?php if (!empty($estatisticas)): ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total de Produtos -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Produtos</span>
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $estatisticas['total_produtos'] ?? 0 ?></p>
            </div>

            <!-- Preço Médio -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Preço Médio</span>
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($estatisticas['preco_medio'] ?? 0, 2, ',', '.') ?></p>
            </div>

            <!-- Custo Médio -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Custo Médio</span>
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($estatisticas['custo_medio'] ?? 0, 2, ',', '.') ?></p>
            </div>

            <!-- Margem Média -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Margem Média</span>
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <?php
                $custoMedio = $estatisticas['custo_medio'] ?? 0;
                $precoMedio = $estatisticas['preco_medio'] ?? 0;
                $margemMedia = 0;
                if ($custoMedio > 0) {
                    $margemMedia = (($precoMedio - $custoMedio) / $custoMedio) * 100;
                }
                ?>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($margemMedia, 1, ',', '.') ?>%</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="/produtos" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                <input type="text" name="busca" value="<?= htmlspecialchars($filters['busca'] ?? '') ?>" 
                       placeholder="Código ou nome do produto..."
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Buscar
            </button>
            <?php if (!empty($filters['busca'])): ?>
                <a href="/produtos" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lista de Produtos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($produtos)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhum produto encontrado</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Comece adicionando seu primeiro produto</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Código</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Custo</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Preço Venda</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Margem</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Unidade</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($produtos as $produto): ?>
                            <?php
                            $margem = 0;
                            if ($produto['custo_unitario'] > 0) {
                                $margem = (($produto['preco_venda'] - $produto['custo_unitario']) / $produto['custo_unitario']) * 100;
                            }
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($produto['codigo']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="font-medium"><?= htmlspecialchars($produto['nome']) ?></div>
                                    <?php if ($produto['descricao']): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <?= htmlspecialchars(substr($produto['descricao'], 0, 60)) ?><?= strlen($produto['descricao']) > 60 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700 dark:text-gray-300">
                                    R$ <?= number_format($produto['custo_unitario'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold <?= $margem > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    <?= number_format($margem, 1, ',', '.') ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium">
                                        <?= htmlspecialchars($produto['unidade_medida']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center space-x-2">
                                        <a href="/produtos/<?= $produto['id'] ?>" 
                                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" 
                                           title="Ver Detalhes">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="/produtos/<?= $produto['id'] ?>/edit" 
                                           class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300" 
                                           title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="/produtos/<?= $produto['id'] ?>/delete" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Excluir">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
