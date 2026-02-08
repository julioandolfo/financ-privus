<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Movimentações de Caixa</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie entradas e saídas de caixa</p>
        </div>
        <a href="/movimentacoes-caixa/create" 
           class="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Nova Movimentação</span>
        </a>
    </div>

    <!-- Cards de Totais -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Entradas -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-green-700 dark:text-green-400">Entradas</span>
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-green-800 dark:text-green-300">
                R$ <?= number_format($totais['entradas'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Total Saídas -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-6 border border-red-200 dark:border-red-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-red-700 dark:text-red-400">Saídas</span>
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-red-800 dark:text-red-300">
                R$ <?= number_format($totais['saidas'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Saldo -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">Saldo do Período</span>
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold <?= $totais['saldo'] >= 0 ? 'text-blue-800 dark:text-blue-300' : 'text-red-800 dark:text-red-300' ?>">
                R$ <?= number_format($totais['saldo'], 2, ',', '.') ?>
            </p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtros</h2>
            <a href="/movimentacoes-caixa" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                Limpar Filtros
            </a>
        </div>

        <form method="GET" action="/movimentacoes-caixa" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Empresa -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Empresa
                </label>
                <select name="empresa_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= $filters['empresa_id'] == $empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tipo
                </label>
                <select name="tipo" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="entrada" <?= $filters['tipo'] === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                    <option value="saida" <?= $filters['tipo'] === 'saida' ? 'selected' : '' ?>>Saída</option>
                </select>
            </div>

            <!-- Conta Bancária -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Conta Bancária
                </label>
                <select name="conta_bancaria_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todas</option>
                    <?php foreach ($contasBancarias as $conta): ?>
                        <option value="<?= $conta['id'] ?>" <?= $filters['conta_bancaria_id'] == $conta['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($conta['banco_nome']) ?> - <?= htmlspecialchars($conta['agencia']) ?>/<?= htmlspecialchars($conta['numero_conta']) ?>
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
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Data Fim -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Data Fim
                </label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($filters['data_fim']) ?>"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Conciliado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Conciliação
                </label>
                <select name="conciliado" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todas</option>
                    <option value="1" <?= $filters['conciliado'] === '1' ? 'selected' : '' ?>>Conciliadas</option>
                    <option value="0" <?= $filters['conciliado'] === '0' ? 'selected' : '' ?>>Não Conciliadas</option>
                </select>
            </div>
            
            <!-- Itens por Página -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Itens por Página
                </label>
                <select name="por_pagina" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="25" <?= ($filters['por_pagina'] ?? '25') == '25' ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($filters['por_pagina'] ?? '') == '50' ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($filters['por_pagina'] ?? '') == '100' ? 'selected' : '' ?>>100</option>
                    <option value="todos" <?= ($filters['por_pagina'] ?? '') == 'todos' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>

            <!-- Botão Filtrar -->
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Movimentações -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($movimentacoes)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Nenhuma movimentação encontrada</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Comece criando uma nova movimentação</p>
                <a href="/movimentacoes-caixa/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Nova Movimentação
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Data</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Empresa</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Conta</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($movimentacoes as $mov): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <?= date('d/m/Y', strtotime($mov['data_movimentacao'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($mov['tipo'] === 'entrada'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            Entrada
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                            Saída
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($mov['empresa_nome']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="max-w-xs truncate">
                                        <?= htmlspecialchars($mov['descricao']) ?>
                                    </div>
                                    <?php if ($mov['referencia_id']): ?>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Vinculado: <?= ucfirst(str_replace('_', ' ', $mov['referencia_tipo'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($mov['categoria_nome']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($mov['banco_nome']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold <?= $mov['tipo'] === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    R$ <?= number_format($mov['valor'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($mov['conciliado']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Conciliado
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Pendente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="/movimentacoes-caixa/<?= $mov['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" 
                                           title="Visualizar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        <?php if (!$mov['referencia_id'] && !$mov['conciliado']): ?>
                                            <a href="/movimentacoes-caixa/<?= $mov['id'] ?>/edit" 
                                               class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300" 
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            
                                            <form method="POST" action="/movimentacoes-caixa/<?= $mov['id'] ?>/delete" class="inline-block" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação?');">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Excluir">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400 dark:text-gray-600" title="Não editável">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando <span class="font-medium"><?= min($paginacao['offset'] + 1, $paginacao['total_registros']) ?></span>
                            até <span class="font-medium"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
                            de <span class="font-medium"><?= $paginacao['total_registros'] ?></span> registros
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <?php
                            $urlParams = $filters ?? [];
                            unset($urlParams['pagina']);
                            $urlBase = '/movimentacoes-caixa?' . http_build_query($urlParams);
                            $separador = empty($urlParams) ? '?' : '&';
                            $range = 2;
                            $inicio = max(1, $paginacao['pagina_atual'] - $range);
                            $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + $range);
                            ?>
                            
                            <?php if ($paginacao['pagina_atual'] > 1): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=1" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Primeira</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] - 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Anterior</a>
                            <?php endif; ?>
                            
                            <?php if ($inicio > 1): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                                <?php if ($i == $paginacao['pagina_atual']): ?>
                                    <span class="px-4 py-2 text-sm bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-medium"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= $urlBase . $separador ?>pagina=<?= $i ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($fim < $paginacao['total_paginas']): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] + 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Próxima</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['total_paginas'] ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Última</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Resumo -->
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                        Total de <?= isset($paginacao) ? $paginacao['total_registros'] : count($movimentacoes) ?> movimentação(ões)
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Limpar mensagens de sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
