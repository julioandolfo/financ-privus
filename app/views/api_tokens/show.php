<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/api-tokens') ?>" 
           class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2"><?= htmlspecialchars($token['nome']) ?></h1>
                <p class="text-gray-600 dark:text-gray-400">Detalhes e estatísticas do token</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= $this->baseUrl("/api-tokens/{$token['id']}/edit") ?>" 
                   class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Editar Token
                </a>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Requests -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Total de Requisições</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($stats['total_requests'] ?? 0) ?></p>
        </div>

        <!-- Success Rate -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Taxa de Sucesso</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <?php 
            $successRate = 0;
            if ($stats['total_requests'] > 0) {
                $successRate = ($stats['success_requests'] / $stats['total_requests']) * 100;
            }
            ?>
            <p class="text-4xl font-bold"><?= number_format($successRate, 1) ?>%</p>
        </div>

        <!-- Errors -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Erros</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($stats['error_requests'] ?? 0) ?></p>
        </div>

        <!-- Avg Response Time -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Tempo Médio</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold"><?= number_format($stats['avg_response_time'] ?? 0, 2) ?>s</p>
        </div>
    </div>

    <!-- Token Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Informações Gerais -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Informações do Token</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Usuário</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($token['usuario_nome']) ?></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($token['usuario_email']) ?></p>
                </div>
                
                <?php if ($token['empresa_nome']): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Empresa</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= htmlspecialchars($token['empresa_nome']) ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Rate Limit</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= number_format($token['rate_limit']) ?> req/hora</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                    <?php if ($token['ativo']): ?>
                    <span class="inline-flex px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-sm font-bold rounded-full">
                        Ativo
                    </span>
                    <?php else: ?>
                    <span class="inline-flex px-3 py-1 bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400 text-sm font-bold rounded-full">
                        Inativo
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($token['ultimo_uso']): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Último Uso</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i:s', strtotime($token['ultimo_uso'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($token['expira_em']): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Expira em</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i:s', strtotime($token['expira_em'])) ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">Criado em</label>
                    <p class="text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i:s', strtotime($token['created_at'])) ?></p>
                </div>
            </div>
        </div>

        <!-- Endpoints Mais Usados -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Top Endpoints</h2>
            <div class="space-y-4">
                <?php if (empty($topEndpoints)): ?>
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">Nenhuma requisição ainda</p>
                <?php else: ?>
                    <?php foreach (array_slice($topEndpoints, 0, 5) as $endpoint): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs font-bold rounded">
                                    <?= htmlspecialchars($endpoint['metodo']) ?>
                                </span>
                                <p class="text-sm text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($endpoint['endpoint']) ?></p>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <?php 
                                $maxCount = $topEndpoints[0]['count'] ?? 1;
                                $percentage = ($endpoint['count'] / $maxCount) * 100;
                                ?>
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                        <span class="ml-4 text-lg font-bold text-gray-900 dark:text-gray-100"><?= $endpoint['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Logs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Últimas Requisições</h2>
        
        <?php if (empty($logs)): ?>
        <p class="text-gray-600 dark:text-gray-400 text-center py-12">Nenhuma requisição registrada</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Horário</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Método</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Endpoint</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                            <?= date('d/m H:i:s', strtotime($log['created_at'])) ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs font-bold rounded">
                                <?= htmlspecialchars($log['metodo']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-mono">
                            <?= htmlspecialchars($log['endpoint']) ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php 
                            $statusColor = 'gray';
                            if ($log['status_code'] >= 200 && $log['status_code'] < 300) {
                                $statusColor = 'green';
                            } elseif ($log['status_code'] >= 400 && $log['status_code'] < 500) {
                                $statusColor = 'amber';
                            } elseif ($log['status_code'] >= 500) {
                                $statusColor = 'red';
                            }
                            ?>
                            <span class="px-2 py-1 bg-<?= $statusColor ?>-100 dark:bg-<?= $statusColor ?>-900/30 text-<?= $statusColor ?>-800 dark:text-<?= $statusColor ?>-400 text-xs font-bold rounded">
                                <?= $log['status_code'] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                            <?= number_format($log['tempo_resposta'] ?? 0, 3) ?>s
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 font-mono">
                            <?= htmlspecialchars($log['ip']) ?>
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
