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

    <!-- Validação de Pedidos Faltantes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">🔍 Validar Pedidos Faltantes</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Identifique pedidos do WooCommerce que não foram sincronizados</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Período</label>
                    <select id="periodoValidacao" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="7dias">Últimos 7 dias</option>
                        <option value="30dias" selected>Últimos 30 dias</option>
                        <option value="90dias">Últimos 90 dias</option>
                        <option value="custom">Período customizado</option>
                    </select>
                </div>
                <div id="dataInicioDiv" style="display: none;">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                    <input type="date" id="dataInicio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div id="dataFimDiv" style="display: none;">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                    <input type="date" id="dataFim" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
            <button onclick="validarPedidosFaltantes()" id="btnValidar" class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-lg shadow transition-all">
                🔍 Validar Pedidos
            </button>
            
            <!-- Resultado da validação -->
            <div id="resultadoValidacao" style="display: none;" class="mt-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Resultado da Validação</h4>
                    <button onclick="fecharResultado()" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-600" id="totalWoo">0</div>
                        <div class="text-xs text-gray-600">WooCommerce</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600" id="totalLocal">0</div>
                        <div class="text-xs text-gray-600">Sistema</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-600" id="totalFaltantes">0</div>
                        <div class="text-xs text-gray-600">Faltantes</div>
                    </div>
                </div>
                
                <div id="pedidosFaltantesLista" style="display: none;">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Pedidos Faltantes</h5>
                        <button onclick="sincronizarTodosFaltantes()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all">
                            ⬇️ Sincronizar Todos
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Pedido #</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Data</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Cliente</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Valor</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaPedidosFaltantes">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                            <span class="font-medium text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($log['mensagem'] ?? '') ?></span>
                            <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($log['data_execucao'] ?? 'now')) ?></span>
                        </div>
                        <?php if (!empty($log['dados'])): ?>
                            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(substr($log['dados'], 0, 150)) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const integracaoId = <?= $integracaoId ?>;
    let pedidosFaltantesData = [];

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

    // Mostra/esconde campos de data customizada
    document.getElementById('periodoValidacao').addEventListener('change', function() {
        const custom = this.value === 'custom';
        document.getElementById('dataInicioDiv').style.display = custom ? 'block' : 'none';
        document.getElementById('dataFimDiv').style.display = custom ? 'block' : 'none';
    });

    // Validar pedidos faltantes
    function validarPedidosFaltantes() {
        const btn = document.getElementById('btnValidar');
        btn.disabled = true;
        btn.textContent = '⏳ Validando...';

        const periodo = document.getElementById('periodoValidacao').value;
        const payload = { periodo };

        if (periodo === 'custom') {
            payload.data_inicio = document.getElementById('dataInicio').value;
            payload.data_fim = document.getElementById('dataFim').value;

            if (!payload.data_inicio || !payload.data_fim) {
                alert('Por favor, selecione as datas de início e fim');
                btn.disabled = false;
                btn.textContent = '🔍 Validar Pedidos';
                return;
            }
        }

        fetch(`/integracoes/${integracaoId}/woocommerce/dashboard/validar-pedidos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                pedidosFaltantesData = data.data.pedidos_faltantes || [];
                exibirResultadoValidacao(data.data);
            } else {
                alert('❌ ' + data.error);
            }
        })
        .catch(err => alert('❌ Erro: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '🔍 Validar Pedidos';
        });
    }

    function exibirResultadoValidacao(data) {
        document.getElementById('totalWoo').textContent = data.total_woo;
        document.getElementById('totalLocal').textContent = data.total_local;
        document.getElementById('totalFaltantes').textContent = data.total_faltantes;
        document.getElementById('resultadoValidacao').style.display = 'block';

        if (data.total_faltantes > 0) {
            document.getElementById('pedidosFaltantesLista').style.display = 'block';
            renderizarTabelaFaltantes(data.pedidos_faltantes);
        } else {
            document.getElementById('pedidosFaltantesLista').style.display = 'none';
        }
    }

    function renderizarTabelaFaltantes(pedidos) {
        const tbody = document.getElementById('tabelaPedidosFaltantes');
        tbody.innerHTML = '';

        pedidos.forEach(pedido => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';
            
            const dataFormatada = pedido.date_created ? new Date(pedido.date_created).toLocaleDateString('pt-BR') : '-';
            const valorFormatado = parseFloat(pedido.total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="checkbox" class="pedido-checkbox" value="${pedido.id}">
                </td>
                <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-100">#${pedido.number}</td>
                <td class="px-3 py-2 text-gray-600 dark:text-gray-400">${dataFormatada}</td>
                <td class="px-3 py-2 text-gray-600 dark:text-gray-400">${pedido.customer_name}</td>
                <td class="px-3 py-2 text-gray-900 dark:text-gray-100 font-semibold">${valorFormatado}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                        ${pedido.status}
                    </span>
                </td>
                <td class="px-3 py-2">
                    <button onclick="sincronizarPedido(${pedido.id})" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-all">
                        ⬇️ Sincronizar
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.pedido-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
    }

    function sincronizarPedido(wooId) {
        if (!confirm('Deseja sincronizar este pedido?')) return;
        sincronizarPedidos([wooId]);
    }

    function sincronizarTodosFaltantes() {
        const checkboxes = document.querySelectorAll('.pedido-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Selecione pelo menos um pedido para sincronizar');
            return;
        }

        const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (!confirm(`Deseja sincronizar ${ids.length} pedido(s) selecionado(s)?`)) return;
        
        sincronizarPedidos(ids);
    }

    function sincronizarPedidos(pedidosIds) {
        const loadingMsg = document.createElement('div');
        loadingMsg.id = 'syncLoading';
        loadingMsg.className = 'fixed top-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        loadingMsg.textContent = `⏳ Sincronizando ${pedidosIds.length} pedido(s)...`;
        document.body.appendChild(loadingMsg);

        fetch(`/integracoes/${integracaoId}/woocommerce/dashboard/sincronizar-faltantes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ pedidos_ids: pedidosIds })
        })
        .then(res => res.json())
        .then(data => {
            document.body.removeChild(loadingMsg);
            
            if (data.success) {
                let mensagem = `✅ ${data.message}`;
                
                if (data.data.erros && data.data.erros.length > 0) {
                    mensagem += '\n\n⚠️ Erros:\n' + data.data.erros.join('\n');
                }
                
                alert(mensagem);
                
                // Recarrega a validação para atualizar a lista
                validarPedidosFaltantes();
            } else {
                alert('❌ ' + data.error);
            }
        })
        .catch(err => {
            document.body.removeChild(loadingMsg);
            alert('❌ Erro: ' + err.message);
        });
    }

    function fecharResultado() {
        document.getElementById('resultadoValidacao').style.display = 'none';
    }

    // Auto-refresh a cada 30s
    setInterval(() => atualizarDashboard(null), 30000);
</script>
