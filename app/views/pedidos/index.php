<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pedidos Vinculados</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gestão de pedidos de todas as origens</p>
        </div>
        <a href="/pedidos/create" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl shadow-lg flex items-center space-x-2 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Novo Pedido</span>
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="/pedidos" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número do Pedido</label>
                <input type="text" name="numero_pedido" value="<?= htmlspecialchars($filters['numero_pedido'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Buscar...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Origem</label>
                <select name="origem" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    <option value="manual" <?= ($filters['origem'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="woocommerce" <?= ($filters['origem'] ?? '') === 'woocommerce' ? 'selected' : '' ?>>WooCommerce</option>
                    <option value="externo" <?= ($filters['origem'] ?? '') === 'externo' ? 'selected' : '' ?>>Externo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="pendente" <?= ($filters['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="processando" <?= ($filters['status'] ?? '') === 'processando' ? 'selected' : '' ?>>Processando</option>
                    <option value="concluido" <?= ($filters['status'] ?? '') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                    <option value="cancelado" <?= ($filters['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Tabela -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-purple-600 to-pink-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Número</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Data</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Origem</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Itens</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-white uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Nenhum pedido encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): 
                            $statusColors = [
                                'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'processando' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                'concluido' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                'cancelado' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                'reembolsado' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                            ];
                            $statusColor = $statusColors[$pedido['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">#<?= htmlspecialchars($pedido['numero_pedido']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pedido['cliente_nome'] ?? 'Sem cliente') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs uppercase font-semibold text-gray-600 dark:text-gray-400"><?= htmlspecialchars($pedido['origem']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>"><?= ucfirst($pedido['status']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700 dark:text-gray-300">
                                <?= $pedido['total_itens'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                                R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="/pedidos/<?= $pedido['id'] ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Ver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
