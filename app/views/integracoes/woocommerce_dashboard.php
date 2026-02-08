<?php $title = 'Dashboard WooCommerce'; ?>
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-start">
        <div>
            <a href="<?= $this->baseUrl('/integracoes/' . $integracaoId) ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                📊 Dashboard WooCommerce
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?= htmlspecialchars($config['url_site'] ?? '') ?></p>
        </div>
        <button onclick="atualizarDashboard(this)" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow transition-all">
            🔄 Atualizar
        </button>
    </div>

    <!-- Cards de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-l-yellow-500 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-3xl font-bold text-yellow-600" id="metricPendentes"><?= $metricas['jobs']['pendente'] ?? 0 ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Jobs Pendentes</div>
                </div>
                <span class="text-3xl">⏳</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-l-green-500 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-3xl font-bold text-green-600" id="metricConcluidos"><?= $metricas['jobs']['concluido'] ?? 0 ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Concluídos</div>
                </div>
                <span class="text-3xl">✅</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-l-blue-500 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-3xl font-bold text-blue-600" id="metricProdutos"><?= $metricas['produtos_hoje'] ?? 0 ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Produtos Hoje</div>
                </div>
                <span class="text-3xl">📦</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 border-l-4 border-l-red-500 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-3xl font-bold text-red-600" id="metricErros"><?= $metricas['jobs']['erro'] ?? 0 ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Erros (7 dias)</div>
                </div>
                <span class="text-3xl">⚠️</span>
            </div>
        </div>
    </div>

    <!-- Sincronização e Cache -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">🕐 Última Sincronização</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Produtos</div>
                    <div class="text-sm text-gray-500">
                        <?php if ($metricas['ultima_sync_produtos']): ?>
                            <?= date('d/m/Y H:i', strtotime($metricas['ultima_sync_produtos'])) ?>
                        <?php else: ?>
                            Nunca sincronizado
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pedidos</div>
                    <div class="text-sm text-gray-500">
                        <?php if ($metricas['ultima_sync_pedidos']): ?>
                            <?= date('d/m/Y H:i', strtotime($metricas['ultima_sync_pedidos'])) ?>
                        <?php else: ?>
                            Nunca sincronizado
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="criarJobSync('sync_produtos')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all">
                    📦 Sync Produtos
                </button>
                <button onclick="criarJobSync('sync_pedidos')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all">
                    🛒 Sync Pedidos
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">💾 Cache de Sincronização</h3>
            <div class="grid grid-cols-2 gap-4 text-center mb-4">
                <div>
                    <div class="text-3xl font-bold text-blue-600"><?= $estatisticasCache['produto'] ?? 0 ?></div>
                    <div class="text-sm text-gray-500">Produtos no Cache</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600"><?= $estatisticasCache['pedido'] ?? 0 ?></div>
                    <div class="text-sm text-gray-500">Pedidos no Cache</div>
                </div>
            </div>
            <p class="text-xs text-gray-500">
                💡 O cache permite sincronização incremental, importando apenas itens modificados.
            </p>
        </div>
    </div>

    <!-- Jobs Recentes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📋 Jobs Recentes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tipo</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tentativas</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Criado em</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tempo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($jobsRecentes)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhum job encontrado</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobsRecentes as $job): ?>
                            <?php
                                $statusClasses = [
                                    'pendente' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'processando' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'concluido' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'erro' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'cancelado' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
                                ];
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">#<?= $job['id'] ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <?= ucwords(str_replace('_', ' ', $job['tipo'])) ?>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusClasses[$job['status']] ?? '' ?>">
                                        <?= ucfirst($job['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600"><?= $job['tentativas'] ?>/<?= $job['max_tentativas'] ?></td>
                                <td class="px-6 py-3 text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($job['criado_em'])) ?></td>
                                <td class="px-6 py-3 text-sm text-gray-600"><?= $job['tempo_execucao'] ? number_format($job['tempo_execucao'], 2) . 's' : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Logs Recentes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📝 Logs Recentes</h3>
        </div>
        <div class="p-6 space-y-3">
            <?php if (empty($logsRecentes)): ?>
                <p class="text-center text-gray-500 py-8">Nenhum log encontrado</p>
            <?php else: ?>
                <?php foreach ($logsRecentes as $log): ?>
                    <?php
                        $logBorder = [
                            'sucesso' => 'border-l-green-500',
                            'erro' => 'border-l-red-500',
                            'aviso' => 'border-l-yellow-500',
                            'info' => 'border-l-blue-500',
                        ];
                    ?>
                    <div class="border-l-4 <?= $logBorder[$log['tipo']] ?? 'border-l-gray-300' ?> pl-4 py-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($log['mensagem']) ?></span>
                            <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($log['data'])) ?></span>
                        </div>
                        <?php if (!empty($log['detalhes'])): ?>
                            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(substr($log['detalhes'], 0, 150)) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const integracaoId = <?= $integracaoId ?>;

    function atualizarDashboard(btn) {
        if (btn) { btn.disabled = true; btn.textContent = '⏳ Atualizando...'; }

        fetch(`/integracoes/${integracaoId}/woocommerce/dashboard/metricas`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const m = data.data;
                    document.getElementById('metricPendentes').textContent = m.jobs?.pendente || 0;
                    document.getElementById('metricConcluidos').textContent = m.jobs?.concluido || 0;
                    document.getElementById('metricProdutos').textContent = m.produtos_hoje || 0;
                    document.getElementById('metricErros').textContent = m.jobs?.erro || 0;
                }
            })
            .finally(() => {
                if (btn) { btn.disabled = false; btn.textContent = '🔄 Atualizar'; }
            });
    }

    function criarJobSync(tipo) {
        fetch(`/integracoes/${integracaoId}/woocommerce/dashboard/jobs/criar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.error);
            }
        })
        .catch(err => alert('❌ Erro: ' + err.message));
    }

    // Auto-refresh a cada 30s
    setInterval(() => atualizarDashboard(null), 30000);
</script>
