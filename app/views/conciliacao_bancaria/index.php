<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Conciliação Bancária</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Reconcilie suas movimentações com extratos bancários</p>
        </div>
        <a href="/conciliacao-bancaria/create" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Nova Conciliação</span>
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="/conciliacao-bancaria" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conta Bancária</label>
                <select name="conta_bancaria_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?= $conta['id'] ?>" <?= ($filters['conta_bancaria_id'] ?? '') == $conta['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($conta['banco'] . ' - ' . $conta['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="aberta" <?= ($filters['status'] ?? '') === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                    <option value="fechada" <?= ($filters['status'] ?? '') === 'fechada' ? 'selected' : '' ?>>Fechada</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Conciliações -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($conciliacoes)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhuma conciliação bancária encontrada</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Crie sua primeira conciliação para começar</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Conta</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Período</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Saldo Extrato</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Saldo Sistema</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Diferença</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($conciliacoes as $conciliacao): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    #<?= $conciliacao['id'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="font-medium"><?= htmlspecialchars($conciliacao['banco']) ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($conciliacao['conta_descricao']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <?= date('d/m/Y', strtotime($conciliacao['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($conciliacao['data_fim'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    R$ <?= number_format($conciliacao['saldo_extrato'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    R$ <?= number_format($conciliacao['saldo_sistema'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold <?= abs($conciliacao['diferenca']) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    R$ <?= number_format($conciliacao['diferenca'], 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($conciliacao['status'] === 'fechada'): ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            Fechada
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                            Aberta
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center space-x-2">
                                        <a href="/conciliacao-bancaria/<?= $conciliacao['id'] ?>" 
                                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" 
                                           title="Ver Detalhes">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        <?php if ($conciliacao['status'] === 'aberta'): ?>
                                            <form method="POST" action="/conciliacao-bancaria/<?= $conciliacao['id'] ?>/delete" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta conciliação?')">
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Excluir">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
