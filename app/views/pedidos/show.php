<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?= $pedido['empresa_nome'] ?></p>
        </div>
        <div class="flex space-x-3">
            <a href="/pedidos" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl shadow-lg">Voltar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informações Principais -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Dados do Pedido -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Informações do Pedido
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Origem</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 uppercase"><?= htmlspecialchars($pedido['origem']) ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</label>
                        <?php
                        $statusColors = [
                            'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'processando' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                            'concluido' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'cancelado' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                        ];
                        $statusColor = $statusColors[$pedido['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <p>
                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $statusColor ?>"><?= ucfirst($pedido['status']) ?></span>
                            <?php if (!empty($pedido['bonificado'])): ?>
                                <span class="inline-block ml-2 px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                    </svg>
                                    BONIFICADO
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Data do Pedido</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Última Atualização</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($pedido['data_atualizacao'])) ?></p>
                    </div>
                </div>

                <?php if (!empty($pedido['pedido_pai_id'])): ?>
                    <div class="mt-4 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                                <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Pedido Pai (Principal):</span>
                                <a href="/pedidos/<?= $pedido['pedido_pai_id'] ?>" class="ml-2 text-purple-600 dark:text-purple-400 font-bold hover:underline">
                                    #<?= htmlspecialchars($pedido['pedido_pai_numero'] ?? $pedido['pedido_pai_id']) ?>
                                </a>
                            </div>
                            <?php if (!empty($pedido['pedido_pai_status'])): ?>
                                <?php
                                $paiStatusColors = [
                                    'pendente' => 'bg-yellow-100 text-yellow-800',
                                    'processando' => 'bg-blue-100 text-blue-800',
                                    'concluido' => 'bg-green-100 text-green-800',
                                    'cancelado' => 'bg-red-100 text-red-800'
                                ];
                                $paiStatusColor = $paiStatusColors[$pedido['pedido_pai_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-purple-600 dark:text-purple-400">
                                        R$ <?= number_format($pedido['pedido_pai_valor'] ?? 0, 2, ',', '.') ?>
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?= $paiStatusColor ?>">
                                        <?= ucfirst($pedido['pedido_pai_status']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Itens do Pedido -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center justify-between">
                    <span>Itens do Pedido</span>
                    <?php if (!empty($itens)): ?>
                        <span class="text-sm font-normal bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full"><?= count($itens) ?> item(ns)</span>
                    <?php endif; ?>
                </h3>
                
                <?php if (empty($itens)): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum item encontrado neste pedido.</p>
                        <?php if ($pedido['origem'] === 'woocommerce'): ?>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Sincronize novamente o pedido para importar os itens.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase pb-3">Produto</th>
                                    <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase pb-3">Qtd</th>
                                    <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase pb-3">Unitário</th>
                                    <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase pb-3">Custo</th>
                                    <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase pb-3">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php foreach ($itens as $item): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-3 pr-4">
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nome_produto']) ?></p>
                                                <div class="flex gap-2 mt-1">
                                                    <?php if (!empty($item['codigo_produto_origem'])): ?>
                                                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded"><?= htmlspecialchars($item['codigo_produto_origem']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['produto_codigo'])): ?>
                                                        <span class="text-xs bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded">SKU: <?= htmlspecialchars($item['produto_codigo']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center text-gray-900 dark:text-gray-100 font-medium">
                                            <?= number_format($item['quantidade'], 0, ',', '.') ?>
                                        </td>
                                        <td class="py-3 text-right text-gray-600 dark:text-gray-400">
                                            R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?>
                                        </td>
                                        <td class="py-3 text-right <?= ($item['custo_unitario'] > 0) ? 'text-orange-600 dark:text-orange-400' : 'text-red-400' ?>">
                                            R$ <?= number_format($item['custo_unitario'], 2, ',', '.') ?>
                                        </td>
                                        <td class="py-3 text-right font-bold text-gray-900 dark:text-gray-100">
                                            R$ <?= number_format($item['valor_total'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                    <td class="pt-3 font-bold text-gray-900 dark:text-gray-100">Total</td>
                                    <td class="pt-3 text-center font-bold text-gray-900 dark:text-gray-100">
                                        <?= number_format(array_sum(array_column($itens, 'quantidade')), 0, ',', '.') ?>
                                    </td>
                                    <td class="pt-3"></td>
                                    <td class="pt-3 text-right font-bold text-orange-600 dark:text-orange-400">
                                        R$ <?= number_format(array_sum(array_column($itens, 'custo_total')), 2, ',', '.') ?>
                                    </td>
                                    <td class="pt-3 text-right font-bold text-gray-900 dark:text-gray-100">
                                        R$ <?= number_format(array_sum(array_column($itens, 'valor_total')), 2, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pedidos Filhos (Bonificados) -->
            <?php if (!empty($pedidosFilhos)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-purple-200 dark:border-purple-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                        Pedidos Bonificados Vinculados
                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 rounded-full">
                            <?= count($pedidosFilhos) ?>
                        </span>
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($pedidosFilhos as $filho): 
                            $filhoStatusColors = [
                                'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'processando' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                'concluido' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                'cancelado' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                            ];
                            $filhoStatusColor = $filhoStatusColors[$filho['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                            <a href="/pedidos/<?= $filho['id'] ?>" class="block border border-purple-100 dark:border-purple-800 rounded-lg p-4 hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-colors">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                            </svg>
                                            BONIFICADO
                                        </span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                                            #<?= htmlspecialchars($filho['numero_pedido']) ?>
                                        </span>
                                        <?php if (!empty($filho['cliente_nome'])): ?>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">- <?= htmlspecialchars($filho['cliente_nome']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                                            R$ <?= number_format($filho['valor_total'], 2, ',', '.') ?>
                                        </span>
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $filhoStatusColor ?>">
                                            <?= ucfirst($filho['status']) ?>
                                        </span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <?php if (!empty($filho['observacoes'])): ?>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($filho['observacoes']) ?></p>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Totais -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 shadow-xl text-white">
                <h3 class="text-lg font-semibold mb-4">Valores do Pedido</h3>
                <?php 
                $frete = $pedido['frete'] ?? 0;
                $desconto = $pedido['desconto'] ?? 0;
                $lucro = ($pedido['valor_total'] ?? 0) - ($pedido['valor_custo_total'] ?? 0) - $frete;
                $margem = ($pedido['valor_total'] ?? 0) > 0 ? ($lucro / $pedido['valor_total']) * 100 : 0;
                ?>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-3 border-b border-white/20">
                        <span class="text-white/80">Valor Total:</span>
                        <span class="text-2xl font-bold">R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-white/80">Custo Total:</span>
                        <span class="text-lg font-semibold">R$ <?= number_format($pedido['valor_custo_total'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-white/80">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Frete:
                        </span>
                        <span class="text-lg font-semibold">R$ <?= number_format($frete, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-white/80">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Desconto:
                        </span>
                        <span class="text-lg font-semibold">R$ <?= number_format($desconto, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-white/20">
                        <span class="text-white/80">Lucro:</span>
                        <span class="text-lg font-semibold">R$ <?= number_format($lucro, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-white/80">Margem:</span>
                        <span class="text-lg font-semibold"><?= number_format($margem, 1, ',', '.') ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Cliente -->
            <?php if ($pedido['cliente_id']): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cliente</h3>
                    <div class="space-y-2">
                        <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pedido['cliente_nome']) ?></p>
                        <?php if (!empty($pedido['cliente_codigo'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    Cód: <?= htmlspecialchars($pedido['cliente_codigo']) ?>
                                </span>
                            </p>
                        <?php endif; ?>
                        <?php if ($pedido['cliente_email']): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($pedido['cliente_email']) ?></p>
                        <?php endif; ?>
                        <?php if ($pedido['cliente_telefone']): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($pedido['cliente_telefone']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Atualizar Status -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Atualizar Status</h3>
                <form method="POST" action="/pedidos/<?= $pedido['id'] ?>/status">
                    <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 mb-4">
                        <option value="pendente" <?= $pedido['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="processando" <?= $pedido['status'] === 'processando' ? 'selected' : '' ?>>Processando</option>
                        <option value="concluido" <?= $pedido['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        <option value="cancelado" <?= $pedido['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        <option value="reembolsado" <?= $pedido['status'] === 'reembolsado' ? 'selected' : '' ?>>Reembolsado</option>
                    </select>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Atualizar</button>
                </form>
            </div>

            <!-- Excluir -->
            <button type="button" onclick="abrirModalExcluir()" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">Excluir Pedido</button>
        </div>
    </div>
</div>

<!-- Modal Excluir Pedido -->
<div id="modalExcluir" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" onclick="fecharModalExcluir(event)">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6" onclick="event.stopPropagation()">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Excluir Pedido #<?= $pedido['numero_pedido'] ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Esta ação não pode ser desfeita</p>
            </div>
        </div>

        <form method="POST" action="/pedidos/<?= $pedido['id'] ?>/delete">
            <div class="space-y-3 mb-6">
                <label class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 cursor-pointer">
                    <input type="checkbox" name="excluir_receitas" value="1" checked class="mt-1 w-5 h-5 rounded text-red-600 focus:ring-red-500">
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">Excluir contas a receber vinculadas</span>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Remove todas as receitas/parcelas geradas por este pedido</p>
                    </div>
                </label>
                
                <input type="hidden" name="excluir_itens" value="1">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-all">
                    Sim, Excluir
                </button>
                <button type="button" onclick="fecharModalExcluir()" class="flex-1 px-4 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalExcluir() {
    document.getElementById('modalExcluir').classList.remove('hidden');
}
function fecharModalExcluir(event) {
    if (!event || event.target.id === 'modalExcluir') {
        document.getElementById('modalExcluir').classList.add('hidden');
    }
}
</script>
