<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';
?>

<div class="max-w-7xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🗑️ Registros Deletados - Contas a Receber</h1>
                <p class="text-gray-300">Contas excluídas que podem ser restauradas</p>
            </div>
            <a href="/contas-receber" class="bg-white text-gray-900 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition-all shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Voltar para Contas</span>
            </a>
        </div>
    </div>

    <?php if (empty($contas)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Nenhum registro deletado</h3>
            <p class="text-gray-500 dark:text-gray-400">Todas as contas estão ativas ou não há registros excluídos</p>
        </div>
    <?php else: ?>
        <!-- Tabela de Registros Deletados -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Deletado</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($contas as $conta): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                <?= htmlspecialchars($conta['descricao']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                #<?= $conta['id'] ?> • <?= htmlspecialchars($conta['empresa_nome']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($conta['cliente_nome'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                        R$ <?= formatarMoeda($conta['valor_total']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        <?= formatarData($conta['data_vencimento']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= $conta['status'] == 'recebido' ? 'bg-green-100 text-green-800' : 
                                            ($conta['status'] == 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                                        <?= ucfirst($conta['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <?= date('d/m/Y H:i', strtotime($conta['deleted_at'])) ?>
                                        <?php if (!empty($conta['usuario_deletou_nome'])): ?>
                                            <div class="mt-1">Por: <?= htmlspecialchars($conta['usuario_deletou_nome']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($conta['deleted_reason'])): ?>
                                            <div class="mt-1 italic"><?= htmlspecialchars(substr($conta['deleted_reason'], 0, 40)) ?><?= strlen($conta['deleted_reason']) > 40 ? '...' : '' ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="/contas-receber/<?= $conta['id'] ?>/historico" 
                                           class="text-purple-600 hover:text-purple-900 dark:hover:text-purple-400" 
                                           title="Ver Histórico">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="/contas-receber/<?= $conta['id'] ?>/restore" 
                                              class="inline"
                                              onsubmit="return confirm('Tem certeza que deseja restaurar esta conta?')">
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900 dark:hover:text-green-400" 
                                                    title="Restaurar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
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
        </div>

        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">ℹ️ Informação sobre Restauração</p>
                    <p>Os registros aqui listados foram excluídos mas podem ser restaurados. Ao restaurar, a conta voltará para seu estado original e ficará visível novamente na listagem principal.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
