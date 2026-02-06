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
            </div>

            <!-- Itens do Pedido -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Itens do Pedido</h3>
                <div class="space-y-4">
                    <?php foreach ($itens as $item): ?>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nome_produto']) ?></h4>
                                    <?php if ($item['codigo_produto_origem']): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Código: <?= htmlspecialchars($item['codigo_produto_origem']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?= number_format($item['quantidade'], 2, ',', '.') ?> x R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?>
                                    </p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        R$ <?= number_format($item['valor_total'], 2, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
            <form method="POST" action="/pedidos/<?= $pedido['id'] ?>/delete" onsubmit="return confirm('Tem certeza que deseja excluir este pedido?')">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">Excluir Pedido</button>
            </form>
        </div>
    </div>
</div>
