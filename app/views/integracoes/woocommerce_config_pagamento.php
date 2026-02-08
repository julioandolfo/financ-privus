<?php $title = 'Configurar Pagamentos - WooCommerce'; ?>
<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex justify-between items-start">
        <div>
            <a href="<?= $this->baseUrl('/integracoes/' . $integracaoId) ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                💳 Formas de Pagamento
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Defina ações automáticas para cada gateway de pagamento do WooCommerce</p>
        </div>
    </div>

    <!-- Buscar Formas de Pagamento -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">☁️ Buscar Formas de Pagamento</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Busca todos os gateways de pagamento ativos no WooCommerce
                </p>
            </div>
            <button onclick="atualizarFormasPagamento(this)" class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow transition-all">
                🔄 Atualizar
            </button>
        </div>
    </div>

    <!-- Formulário de Ações -->
    <form id="formAcoes" onsubmit="salvarAcoes(event)">
        <?php if (empty($formasPgtoWoo)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center border border-gray-200 dark:border-gray-700">
                <div class="text-6xl mb-4">💳</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Nenhuma forma de pagamento encontrada</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Clique em <strong>"Atualizar"</strong> acima para buscar do WooCommerce
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($formasPgtoWoo as $index => $gateway): ?>
                    <?php 
                        $chave = $gateway['chave'];
                        $acao = $acoesConfig[$chave] ?? [];
                    ?>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-l-4 <?= $gateway['ativo'] ? 'border-l-green-500' : 'border-l-gray-400' ?> border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <!-- Header -->
                        <div 
                            class="px-6 py-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-center justify-between"
                            onclick="toggleAccordion('pgto_<?= $index ?>')"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">💳</span>
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($gateway['nome']) ?></span>
                                    <code class="ml-2 text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded"><?= $chave ?></code>
                                </div>
                                <?php if ($gateway['ativo']): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 text-xs font-semibold rounded-full">Ativo</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-xs font-semibold rounded-full">Inativo</span>
                                <?php endif; ?>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" id="icon_pgto_<?= $index ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <!-- Conteúdo -->
                        <div id="pgto_<?= $index ?>" class="<?= $index === 0 ? '' : 'hidden' ?> border-t border-gray-200 dark:border-gray-700">
                            <div class="p-6 space-y-5">
                                <!-- Vincular -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">🔗 Vincular a Forma de Pagamento do Sistema</label>
                                    <select 
                                        class="w-full md:w-1/2 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500"
                                        name="acoes[<?= $chave ?>][forma_pagamento_id]"
                                    >
                                        <option value="">-- Não vincular --</option>
                                        <?php foreach ($formasPgtoSistema as $forma): ?>
                                            <option 
                                                value="<?= $forma['id'] ?>"
                                                <?= (isset($acao['forma_pagamento_id']) && $acao['forma_pagamento_id'] == $forma['id']) ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($forma['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Baixar Automaticamente -->
                                <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                                    <input 
                                        type="checkbox"
                                        class="mt-1 w-5 h-5 rounded text-green-600 focus:ring-green-500"
                                        name="acoes[<?= $chave ?>][baixar_automaticamente]"
                                        value="1"
                                        <?= !empty($acao['baixar_automaticamente']) ? 'checked' : '' ?>
                                    >
                                    <div>
                                        <label class="font-semibold text-gray-900 dark:text-gray-100">✅ Baixar automaticamente (marcar como recebido)</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Recomendado para PIX e pagamentos instantâneos confirmados</p>
                                    </div>
                                </div>

                                <!-- Criar Parcelas -->
                                <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                    <input 
                                        type="checkbox"
                                        class="mt-1 w-5 h-5 rounded text-blue-600 focus:ring-blue-500"
                                        id="criar_parcelas_<?= $chave ?>"
                                        name="acoes[<?= $chave ?>][criar_parcelas]"
                                        value="1"
                                        <?= !empty($acao['criar_parcelas']) ? 'checked' : '' ?>
                                        onchange="toggleParcelas('<?= $chave ?>')"
                                    >
                                    <div class="flex-1">
                                        <label class="font-semibold text-gray-900 dark:text-gray-100">📅 Criar Parcelas</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Divide o valor em parcelas separadas</p>
                                        
                                        <!-- Opções de Parcelas -->
                                        <div id="opcoes_parcelas_<?= $chave ?>" class="<?= !empty($acao['criar_parcelas']) ? '' : 'hidden' ?> mt-4 space-y-3">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Nº Parcelas</label>
                                                    <select 
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                                                        name="acoes[<?= $chave ?>][numero_parcelas]"
                                                    >
                                                        <option value="auto" <?= ($acao['numero_parcelas'] ?? '') === 'auto' ? 'selected' : '' ?>>Automático (do pedido)</option>
                                                        <?php for ($i = 2; $i <= 12; $i++): ?>
                                                            <option value="<?= $i ?>" <?= (isset($acao['numero_parcelas']) && $acao['numero_parcelas'] == $i) ? 'selected' : '' ?>><?= $i ?>x</option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Valor 1ª Parcela</label>
                                                    <input 
                                                        type="text" 
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                                                        name="acoes[<?= $chave ?>][valor_primeira_parcela]"
                                                        placeholder="Ex: 50% ou 500.00"
                                                        value="<?= htmlspecialchars($acao['valor_primeira_parcela'] ?? '') ?>"
                                                    >
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <input 
                                                    type="checkbox"
                                                    class="w-4 h-4 rounded text-blue-600"
                                                    name="acoes[<?= $chave ?>][baixar_primeira_parcela]"
                                                    value="1"
                                                    <?= !empty($acao['baixar_primeira_parcela']) ? 'checked' : '' ?>
                                                >
                                                <label class="text-sm text-gray-700 dark:text-gray-300">Baixar primeira parcela automaticamente (entrada)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Observações -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">📝 Observações</label>
                                    <textarea 
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500"
                                        name="acoes[<?= $chave ?>][observacoes]"
                                        rows="2"
                                        placeholder="Observações sobre esta forma de pagamento..."
                                    ><?= htmlspecialchars($acao['observacoes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Exemplos -->
            <div class="mt-6 p-5 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200 dark:border-green-800">
                <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-3">💡 Exemplos de Configuração:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="text-green-600 mt-0.5">✅</span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>PIX:</strong> Baixar automaticamente, sem parcelas</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-blue-600 mt-0.5">📅</span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>Cartão:</strong> Criar parcelas (automático), não baixar</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-orange-600 mt-0.5">💰</span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>50%:</strong> 2 parcelas, baixar primeira (entrada)</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-gray-600 mt-0.5">⏳</span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>Boleto:</strong> Sem parcelas, sem baixar (manual)</span>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow transition-all">
                    ✅ Salvar Configurações
                </button>
                <a href="<?= $this->baseUrl('/integracoes/' . $integracaoId) ?>" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all">
                    Concluir
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    const integracaoId = <?= $integracaoId ?>;

    function toggleAccordion(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('icon_' + id);
        el.classList.toggle('hidden');
        icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    function toggleParcelas(chave) {
        const checkbox = document.getElementById('criar_parcelas_' + chave);
        const opcoes = document.getElementById('opcoes_parcelas_' + chave);
        opcoes.classList.toggle('hidden', !checkbox.checked);
    }

    function atualizarFormasPagamento(btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Buscando...';

        fetch(`/integracoes/${integracaoId}/woocommerce/config/pagamentos/atualizar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message + '\nTotal: ' + data.total + ' formas de pagamento');
                location.reload();
            } else {
                alert('❌ Erro: ' + data.error);
            }
        })
        .catch(err => alert('❌ Erro: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '🔄 Atualizar';
        });
    }

    function salvarAcoes(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const acoes = {};
        
        for (let [key, value] of formData.entries()) {
            const match = key.match(/acoes\[(.*?)\]\[(.*?)\]/);
            if (match) {
                const gateway = match[1];
                const campo = match[2];
                
                if (!acoes[gateway]) acoes[gateway] = {};
                
                if (['criar_parcelas', 'baixar_primeira_parcela', 'baixar_automaticamente'].includes(campo)) {
                    acoes[gateway][campo] = value === '1';
                } else {
                    acoes[gateway][campo] = value;
                }
            }
        }

        fetch(`/integracoes/${integracaoId}/woocommerce/config/pagamentos/salvar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ acoes })
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
