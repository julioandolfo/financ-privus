<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pedidos Vinculados</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gestão de pedidos de todas as origens</p>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="abrirModalRecalculo()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl shadow-lg flex items-center space-x-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Recalcular Pedidos</span>
            </button>
            <a href="/pedidos/create" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl shadow-lg flex items-center space-x-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Novo Pedido</span>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="/pedidos" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Itens por Página</label>
                <select name="por_pagina" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="25" <?= ($filters['por_pagina'] ?? '25') == '25' ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($filters['por_pagina'] ?? '') == '50' ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($filters['por_pagina'] ?? '') == '100' ? 'selected' : '' ?>>100</option>
                    <option value="todos" <?= ($filters['por_pagina'] ?? '') == 'todos' ? 'selected' : '' ?>>Todos</option>
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
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">#<?= htmlspecialchars($pedido['numero_pedido']) ?></span>
                                    <?php if (!empty($pedido['bonificado'])): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400" title="Pedido Bonificado<?= !empty($pedido['pedido_pai_numero']) ? ' - Pai: #' . $pedido['pedido_pai_numero'] : '' ?>">
                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                            BONIF
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($pedido['total_filhos']) && $pedido['total_filhos'] > 0): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400" title="<?= $pedido['total_filhos'] ?> pedido(s) bonificado(s) vinculado(s)">
                                            <?= $pedido['total_filhos'] ?> bonif.
                                        </span>
                                    <?php endif; ?>
                                </div>
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
        
        <!-- Paginação -->
        <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando 
                        <span class="font-medium"><?= min($paginacao['offset'] + 1, $paginacao['total_registros']) ?></span>
                        até
                        <span class="font-medium"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
                        de
                        <span class="font-medium"><?= $paginacao['total_registros'] ?></span>
                        registros
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <?php
                        // Construir URL base com todos os filtros exceto 'pagina'
                        $urlParams = $filters;
                        unset($urlParams['pagina']);
                        
                        // Remover parâmetros vazios
                        $urlParams = array_filter($urlParams, function($value) {
                            return $value !== '' && $value !== null;
                        });
                        
                        $urlBase = '/pedidos' . (!empty($urlParams) ? '?' . http_build_query($urlParams) : '');
                        $separador = empty($urlParams) ? '?' : '&';
                        
                        // Calcular range de páginas para exibir
                        $range = 2; // Quantas páginas mostrar antes e depois da atual
                        $inicio = max(1, $paginacao['pagina_atual'] - $range);
                        $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + $range);
                        ?>
                        
                        <!-- Primeira página -->
                        <?php if ($paginacao['pagina_atual'] > 1): ?>
                            <a href="<?= $urlBase . $separador ?>pagina=1" 
                               class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Primeira
                            </a>
                            <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] - 1 ?>" 
                               class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Anterior
                            </a>
                        <?php endif; ?>
                        
                        <!-- Páginas numeradas -->
                        <?php if ($inicio > 1): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                        
                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                            <?php if ($i == $paginacao['pagina_atual']): ?>
                                <span class="px-4 py-2 text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg font-medium">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $i ?>" 
                                   class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($fim < $paginacao['total_paginas']): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                        
                        <!-- Última página -->
                        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                            <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] + 1 ?>" 
                               class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Próxima
                            </a>
                            <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['total_paginas'] ?>" 
                               class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                Última
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Recálculo de Pedidos -->
<div id="modalRecalculo" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Recalcular Pedidos
                </h3>
                <button onclick="fecharModalRecalculo()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" action="/pedidos/recalcular" class="p-6 space-y-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Esta função irá recalcular os valores totais e custos dos pedidos selecionados com base nos custos atuais dos produtos.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Origem dos Pedidos</label>
                    <select name="origem_filtro" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas as origens</option>
                        <option value="manual">Manual</option>
                        <option value="woocommerce">WooCommerce</option>
                        <option value="externo">Externo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status dos Pedidos</label>
                    <select name="status_filtro" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos os status</option>
                        <option value="pendente">Pendente</option>
                        <option value="processando">Processando</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Inicial</label>
                        <input type="date" name="data_inicio_filtro" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Final</label>
                        <input type="date" name="data_fim_filtro" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Atenção: Esta ação irá atualizar os valores dos pedidos. Certifique-se de que os custos dos produtos estão corretos antes de prosseguir.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="fecharModalRecalculo()" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-all shadow-lg">
                    Recalcular Pedidos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalRecalculo() {
    document.getElementById('modalRecalculo').classList.remove('hidden');
}

function fecharModalRecalculo() {
    document.getElementById('modalRecalculo').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalRecalculo')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalRecalculo();
    }
});
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
