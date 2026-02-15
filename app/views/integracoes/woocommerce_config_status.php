<?php $title = 'Configurar Status - WooCommerce'; ?>
<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex justify-between items-start">
        <div>
            <a href="<?= $this->baseUrl('/integracoes/' . $integracaoId) ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                🔄 Mapeamento de Status
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure como os status do WooCommerce serão interpretados no sistema</p>
        </div>
    </div>

    <!-- Buscar Status -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">☁️ Buscar Status do WooCommerce</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Busca todos os status cadastrados, incluindo plugins como Woo Status Order
                </p>
            </div>
            <button onclick="atualizarStatus(this)" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow transition-all">
                🔄 Atualizar Status
            </button>
        </div>
    </div>

    <!-- Formulário de Mapeamento -->
    <form id="formMapeamento" onsubmit="salvarMapeamento(event)">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status WooCommerce → Status do Sistema</h3>
            </div>
            <div class="p-6">
                <?php if (empty($statusWoo)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📋</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Nenhum status encontrado</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Clique em <strong>"Atualizar Status"</strong> acima para buscar do WooCommerce
                        </p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-3 font-semibold text-gray-700 dark:text-gray-300">Status WooCommerce</th>
                                    <th class="pb-3 font-semibold text-gray-700 dark:text-gray-300">Corresponde no Sistema</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php foreach ($statusWoo as $status): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="py-4 pr-4">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                <?= htmlspecialchars($status['nome']) ?>
                                            </div>
                                            <code class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                                <?= htmlspecialchars($status['chave']) ?>
                                            </code>
                                        </td>
                                        <td class="py-4">
                                            <select 
                                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                                name="mapeamento[<?= htmlspecialchars($status['chave']) ?>]"
                                            >
                                <option value="">-- Não mapear (usar padrão) --</option>
                                <?php foreach ($statusSistema as $key => $nome): ?>
                                    <option 
                                        value="<?= $key ?>"
                                        <?= (isset($mapeamento[$status['chave']]) && $mapeamento[$status['chave']] === $key) ? 'selected' : '' ?>
                                        <?= $key === 'nao_sincronizar' ? 'class="text-red-600 font-bold"' : '' ?>
                                    >
                                        <?= htmlspecialchars($nome) ?>
                                    </option>
                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                💡 <strong>Dica:</strong> Status customizados de plugins como "Woo Status Order" aparecem automaticamente aqui após clicar em "Atualizar Status".
                            </p>
                        </div>
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                🚫 <strong>"Não sincronizar":</strong> Pedidos com este status serão <strong>ignorados</strong> durante a sincronização. Útil para status como "Rascunho", "Lixeira" ou qualquer status que você não deseja importar para o sistema financeiro.
                            </p>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                            <p class="text-sm text-purple-700 dark:text-purple-300">
                                🏷️ <strong>"-- Não mapear --":</strong> Status sem mapeamento usarão o mapeamento padrão automático (ex: processing → Em Processamento).
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($statusWoo)): ?>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow transition-all">
                    ✅ Salvar Mapeamento
                </button>
                <a href="<?= $this->baseUrl('/integracoes/' . $integracaoId . '/woocommerce/config/pagamentos') ?>" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow transition-all">
                    Próximo: Formas de Pagamento →
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    const integracaoId = <?= $integracaoId ?>;

    function atualizarStatus(btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Buscando...';

        fetch(`/integracoes/${integracaoId}/woocommerce/config/status/atualizar`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message + '\nTotal: ' + data.total + ' status encontrados');
                location.reload();
            } else {
                alert('❌ Erro: ' + data.error);
            }
        })
        .catch(err => alert('❌ Erro ao buscar status: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '🔄 Atualizar Status';
        });
    }

    function salvarMapeamento(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const mapeamento = {};
        
        for (let [key, value] of formData.entries()) {
            const match = key.match(/mapeamento\[(.*?)\]/);
            if (match && value) {
                mapeamento[match[1]] = value;
            }
        }

        fetch(`/integracoes/${integracaoId}/woocommerce/config/status/salvar`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ mapeamento })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
            } else {
                alert('❌ Erro: ' + data.error);
            }
        })
        .catch(err => alert('❌ Erro ao salvar: ' + err.message));
    }
</script>
