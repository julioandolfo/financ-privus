<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Logs do Sistema</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Total: <strong><?= number_format($total) ?></strong> registros
                </p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="/logs/limpar" class="inline" onsubmit="return confirm('Tem certeza que deseja limpar os logs?')">
                    <input type="hidden" name="dias" value="7">
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                        Limpar +7 dias
                    </button>
                </form>
                <form method="POST" action="/logs/limpar" class="inline" onsubmit="return confirm('Tem certeza que deseja limpar TODOS os logs?')">
                    <input type="hidden" name="dias" value="0">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                        Limpar Todos
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 mb-6">
        <form method="GET" action="/logs" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="debug" <?= ($filters['tipo'] ?? '') === 'debug' ? 'selected' : '' ?>>Debug</option>
                    <option value="info" <?= ($filters['tipo'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="warning" <?= ($filters['tipo'] ?? '') === 'warning' ? 'selected' : '' ?>>Warning</option>
                    <option value="error" <?= ($filters['tipo'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Módulo</label>
                <input type="text" name="modulo" value="<?= htmlspecialchars($filters['modulo'] ?? '') ?>" 
                       placeholder="Ex: Permissao"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ação</label>
                <input type="text" name="acao" value="<?= htmlspecialchars($filters['acao'] ?? '') ?>" 
                       placeholder="Ex: saveBatch"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Busca</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                       placeholder="Texto..."
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    Filtrar
                </button>
                <a href="/logs" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm">
                    Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Lista de Logs -->
    <div class="space-y-2">
        <?php if (empty($logs)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">Nenhum log encontrado.</p>
            </div>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <?php
                $bgColor = match($log['tipo']) {
                    'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                    'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                    'debug' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
                    default => 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                };
                $typeColor = match($log['tipo']) {
                    'error' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                    'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                    'debug' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
                    'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                };
                ?>
                <div class="<?= $bgColor ?> rounded-lg border p-4" x-data="{ expanded: false }">
                    <div class="flex items-start gap-3">
                        <!-- Tipo -->
                        <span class="px-2 py-1 rounded text-xs font-medium <?= $typeColor ?> uppercase">
                            <?= htmlspecialchars($log['tipo']) ?>
                        </span>
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($log['modulo']) ?></span>
                                <span>›</span>
                                <span><?= htmlspecialchars($log['acao']) ?></span>
                                <span class="mx-2">|</span>
                                <span><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></span>
                                <?php if (!empty($log['usuario_nome'])): ?>
                                    <span class="mx-2">|</span>
                                    <span>👤 <?= htmlspecialchars($log['usuario_nome']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-sm text-gray-800 dark:text-gray-200">
                                <?= htmlspecialchars($log['mensagem']) ?>
                            </p>
                            
                            <?php if (!empty($log['dados'])): ?>
                                <button type="button" 
                                        @click="expanded = !expanded" 
                                        class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    <span x-text="expanded ? '▼ Ocultar dados' : '▶ Ver dados'"></span>
                                </button>
                                
                                <div x-show="expanded" x-transition class="mt-2">
                                    <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto max-h-96"><?= htmlspecialchars(json_encode(json_decode($log['dados']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- IP -->
                        <div class="text-xs text-gray-400 font-mono">
                            <?= htmlspecialchars($log['ip'] ?? '') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" 
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    ← Anterior
                </a>
            <?php endif; ?>
            
            <span class="px-4 py-2 text-gray-600 dark:text-gray-400">
                Página <?= $page ?> de <?= $totalPages ?>
            </span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" 
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Próxima →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
