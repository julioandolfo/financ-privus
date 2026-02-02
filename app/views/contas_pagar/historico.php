<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';

function formatarAcao($acao) {
    $acoes = [
        'create' => ['texto' => 'Criação', 'cor' => 'blue', 'icone' => 'M12 4v16m8-8H4'],
        'update' => ['texto' => 'Atualização', 'cor' => 'yellow', 'icone' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
        'delete' => ['texto' => 'Exclusão', 'cor' => 'red', 'icone' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
        'restore' => ['texto' => 'Restauração', 'cor' => 'green', 'icone' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        'cancel_payment' => ['texto' => 'Cancelamento de Pagamento', 'cor' => 'orange', 'icone' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
        'make_payment' => ['texto' => 'Pagamento Realizado', 'cor' => 'green', 'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    return $acoes[$acao] ?? ['texto' => ucfirst($acao), 'cor' => 'gray', 'icone' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
}
?>

<div class="max-w-6xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">📋 Histórico de Auditoria</h1>
                <p class="text-purple-100">Conta a Pagar #<?= $conta['id'] ?> - <?= htmlspecialchars($conta['descricao']) ?></p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="/contas-pagar/<?= $conta['id'] ?>" class="bg-white text-purple-600 px-6 py-3 rounded-xl font-semibold hover:bg-purple-50 transition-all shadow-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Ver Conta</span>
                </a>
                <a href="/contas-pagar" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Resumo da Conta -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Valor Total</label>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">R$ <?= formatarMoeda($conta['valor_total']) ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold
                    <?= $conta['status'] == 'pago' ? 'bg-green-100 text-green-800' : 
                        ($conta['status'] == 'pendente' ? 'bg-yellow-100 text-yellow-800' : 
                        ($conta['status'] == 'cancelado' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                    <?= ucfirst($conta['status']) ?>
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fornecedor</label>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['fornecedor_nome'] ?? 'N/A') ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Vencimento</label>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= formatarData($conta['data_vencimento']) ?></p>
            </div>
        </div>
    </div>

    <!-- Timeline de Histórico -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Timeline de Eventos</h2>
        
        <?php if (empty($historico)): ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Nenhum histórico encontrado</p>
            </div>
        <?php else: ?>
            <div class="relative">
                <!-- Linha vertical -->
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                
                <div class="space-y-6">
                    <?php foreach ($historico as $index => $evento): 
                        $info = formatarAcao($evento['acao']);
                    ?>
                        <div class="relative pl-20">
                            <!-- Ícone -->
                            <div class="absolute left-0 w-16 h-16 bg-<?= $info['cor'] ?>-100 dark:bg-<?= $info['cor'] ?>-900/30 rounded-xl flex items-center justify-center border-4 border-white dark:border-gray-800">
                                <svg class="w-8 h-8 text-<?= $info['cor'] ?>-600 dark:text-<?= $info['cor'] ?>-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $info['icone'] ?>"></path>
                                </svg>
                            </div>
                            
                            <!-- Conteúdo -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= $info['texto'] ?></h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?= date('d/m/Y H:i:s', strtotime($evento['created_at'])) ?>
                                        </p>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-<?= $info['cor'] ?>-100 text-<?= $info['cor'] ?>-800 dark:bg-<?= $info['cor'] ?>-900/50 dark:text-<?= $info['cor'] ?>-300">
                                        <?= ucfirst($evento['acao']) ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Usuário:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100 ml-2"><?= htmlspecialchars($evento['usuario_nome'] ?? 'Sistema') ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">IP:</span>
                                        <span class="font-mono text-gray-900 dark:text-gray-100 ml-2"><?= htmlspecialchars($evento['ip'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($evento['motivo'])): ?>
                                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            <strong>Motivo:</strong> <?= htmlspecialchars($evento['motivo']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($evento['dados_antes']) || !empty($evento['dados_depois'])): ?>
                                    <details class="mt-4">
                                        <summary class="cursor-pointer text-sm font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700">
                                            Ver dados técnicos
                                        </summary>
                                        <div class="mt-3 space-y-3">
                                            <?php if (!empty($evento['dados_antes'])): ?>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Dados Anteriores:</p>
                                                    <pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded-lg overflow-x-auto"><?= json_encode(json_decode($evento['dados_antes'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($evento['dados_depois'])): ?>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Dados Posteriores:</p>
                                                    <pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded-lg overflow-x-auto"><?= json_encode(json_decode($evento['dados_depois'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
