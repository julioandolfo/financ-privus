<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-start">
        <div>
            <a href="<?= $this->baseUrl('/integracoes') ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                <?= htmlspecialchars($integracao['nome']) ?>
            </h1>
        </div>
        <button onclick="sincronizar()" id="btnSincronizar" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg transition-all">
            🔄 Sincronizar Agora
        </button>
    </div>

    <script>
    function sincronizar() {
        const btn = document.getElementById('btnSincronizar');
        btn.disabled = true;
        btn.innerHTML = '⏳ Sincronizando...';
        
        fetch('<?= $this->baseUrl('/integracoes/' . $integracao['id'] . '/sincronizar') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(r => r.json())
        .then(d => {
            if (d.sucesso) {
                alert('✓ Sincronização concluída com sucesso!');
                location.reload();
            } else {
                alert('✗ Erro: ' + (d.erro || 'Erro desconhecido'));
                btn.disabled = false;
                btn.innerHTML = '🔄 Sincronizar Agora';
            }
        })
        .catch(e => {
            alert('Erro ao sincronizar: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '🔄 Sincronizar Agora';
        });
    }
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informações -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Detalhes da Integração</h2>
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tipo</dt>
                    <dd class="mt-1">
                        <?php
                        $badges = [
                            'woocommerce' => '<span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 text-sm font-semibold rounded-full">🛒 WooCommerce</span>',
                            'banco_dados' => '<span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 text-sm font-semibold rounded-full">💾 Banco de Dados</span>'
                        ];
                        echo $badges[$integracao['tipo']] ?? $integracao['tipo'];
                        ?>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <?= $integracao['ativo'] ? '<span class="text-green-600 font-semibold">✓ Ativa</span>' : '<span class="text-gray-600">○ Inativa</span>' ?>
                    </dd>
                </div>

                <?php if ($configuracao): ?>
                    <?php if ($integracao['tipo'] === 'woocommerce'): ?>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">URL do Site</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($configuracao['url_site']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Sincronizações</dt>
                            <dd class="mt-1 flex gap-2">
                                <?= $configuracao['sincronizar_produtos'] ? '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Produtos</span>' : '' ?>
                                <?= $configuracao['sincronizar_pedidos'] ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Pedidos</span>' : '' ?>
                            </dd>
                        </div>
                    <?php elseif ($integracao['tipo'] === 'banco_dados'): ?>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Conexão</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars(strtoupper($configuracao['tipo_banco'])) ?> - 
                                <?= htmlspecialchars($configuracao['host']) ?>:<?= $configuracao['porta'] ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Tabelas</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($configuracao['tabela_origem']) ?> → <?= htmlspecialchars($configuracao['tabela_destino']) ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div>
                    <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Última Sincronização</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        <?= $integracao['ultima_sincronizacao'] ? date('d/m/Y H:i:s', strtotime($integracao['ultima_sincronizacao'])) : 'Nunca' ?>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400">Intervalo</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100"><?= $integracao['intervalo_sincronizacao'] ?> minutos</dd>
                </div>
            </dl>
        </div>

        <!-- Estatísticas de Logs -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Estatísticas (30 dias)</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Total</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100"><?= $estatisticasLogs['total'] ?? 0 ?></span>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-green-600">Sucessos</span>
                        <span class="font-bold text-green-600"><?= $estatisticasLogs['sucessos'] ?? 0 ?></span>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-red-600">Erros</span>
                        <span class="font-bold text-red-600"><?= $estatisticasLogs['erros'] ?? 0 ?></span>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-amber-600">Avisos</span>
                        <span class="font-bold text-amber-600"><?= $estatisticasLogs['avisos'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Logs de Sincronização</h2>
        </div>

        <?php if (empty($logs)): ?>
            <div class="p-12 text-center text-gray-500">Nenhum log encontrado</div>
        <?php else: ?>
            <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                <?php foreach ($logs as $log): ?>
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex items-start gap-3">
                            <?php
                            $icones = [
                                'sucesso' => '<div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>',
                                'erro' => '<div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg></div>',
                                'aviso' => '<div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg></div>'
                            ];
                            echo $icones[$log['tipo']] ?? '';
                            ?>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($log['mensagem']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= date('d/m/Y H:i:s', strtotime($log['data_execucao'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
