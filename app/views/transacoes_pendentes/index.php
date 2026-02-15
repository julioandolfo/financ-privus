<?php
$old = $this->session->get('old') ?? [];
$errors = $this->session->get('errors') ?? [];
?>

<div class="max-w-7xl mx-auto" x-data="transacoesPendentes()">
    <!-- Seletor de Empresa -->
    <?php if (!empty($empresas_usuario) && count($empresas_usuario) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" action="/transacoes-pendentes" class="flex items-center gap-4">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
                <select name="empresa_id" onchange="this.form.submit()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <?php foreach ($empresas_usuario as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                📋 Transações Pendentes
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Revise e aprove transações importadas automaticamente dos bancos
            </p>
        </div>
        <div class="flex gap-3">
            <a href="/conexoes-bancarias" class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Gerenciar Conexões
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="/transacoes-pendentes" class="space-y-4">
            <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($empresa_id_selecionada ?? '') ?>">
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <!-- Filtro: Tipo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Tipo</label>
                    <select name="tipo" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="debito" <?= ($filtros['tipo'] ?? '') === 'debito' ? 'selected' : '' ?>>Despesas (débitos)</option>
                        <option value="credito" <?= ($filtros['tipo'] ?? '') === 'credito' ? 'selected' : '' ?>>Receitas (créditos)</option>
                    </select>
                </div>

                <!-- Filtro: Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Status</label>
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="pendente" <?= ($filtros['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="aprovada" <?= ($filtros['status'] ?? '') === 'aprovada' ? 'selected' : '' ?>>Aprovadas</option>
                        <option value="ignorada" <?= ($filtros['status'] ?? '') === 'ignorada' ? 'selected' : '' ?>>Ignoradas</option>
                        <option value="" <?= ($filtros['status'] ?? 'pendente') === '' ? 'selected' : '' ?>>Todas</option>
                    </select>
                </div>

                <!-- Filtro: Banco/Origem -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Banco</label>
                    <select name="banco" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="sicoob" <?= ($filtros['banco'] ?? '') === 'sicoob' ? 'selected' : '' ?>>Sicoob</option>
                        <option value="sicredi" <?= ($filtros['banco'] ?? '') === 'sicredi' ? 'selected' : '' ?>>Sicredi</option>
                        <option value="itau" <?= ($filtros['banco'] ?? '') === 'itau' ? 'selected' : '' ?>>Itaú</option>
                        <option value="bradesco" <?= ($filtros['banco'] ?? '') === 'bradesco' ? 'selected' : '' ?>>Bradesco</option>
                        <option value="mercadopago" <?= ($filtros['banco'] ?? '') === 'mercadopago' ? 'selected' : '' ?>>Mercado Pago</option>
                        <option value="ofx" <?= ($filtros['banco'] ?? '') === 'ofx' ? 'selected' : '' ?>>OFX (importação)</option>
                    </select>
                </div>

                <!-- Filtro: Data Início -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">De</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" onchange="this.form.submit()"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Filtro: Data Fim -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Até</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" onchange="this.form.submit()"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <?php 
            $temFiltroAtivo = !empty($filtros['tipo']) || !empty($filtros['banco']) || !empty($filtros['data_inicio']) || !empty($filtros['data_fim']) || ($filtros['status'] ?? 'pendente') !== 'pendente';
            if ($temFiltroAtivo): ?>
            <div class="flex items-center gap-2 pt-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Filtros ativos:</span>
                <?php if (!empty($filtros['tipo'])): ?>
                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium">
                        <?= $filtros['tipo'] === 'debito' ? 'Despesas' : 'Receitas' ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filtros['banco'])): ?>
                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs font-medium">
                        <?= ucfirst($filtros['banco']) ?>
                    </span>
                <?php endif; ?>
                <a href="/transacoes-pendentes?empresa_id=<?= $empresa_id_selecionada ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline ml-2">Limpar filtros</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2"><?= $estatisticas['pendentes'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aprovadas</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2"><?= $estatisticas['aprovadas'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Débitos</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-2">R$ <?= number_format($estatisticas['total_debitos'] ?? 0, 2, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Créditos</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">R$ <?= number_format($estatisticas['total_creditos'] ?? 0, 2, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Transações -->
    <?php if (empty($transacoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Nenhuma Transação Pendente</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                Ótimo! Todas as transações foram processadas ou não há transações novas dos seus bancos.
            </p>
            <a href="/conexoes-bancarias" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                Sincronizar Agora
            </a>
        </div>
    <?php else: ?>
        <!-- Ações em lote -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6" x-show="selecionadas.length > 0" x-cloak>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    <strong x-text="selecionadas.length"></strong> transação(ões) selecionada(s)
                </span>
                <div class="flex gap-3">
                    <button @click="aprovarLote()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                        Aprovar Selecionadas
                    </button>
                    <button @click="selecionadas = []" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                        Desmarcar Todas
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <?php foreach ($transacoes as $transacao): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-start gap-4">
                        <!-- Checkbox -->
                        <input 
                            type="checkbox" 
                            :value="<?= $transacao['id'] ?>"
                            x-model="selecionadas"
                            class="mt-1 w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        >

                        <!-- Conteúdo Principal -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $transacao['tipo'] === 'debito' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' ?>">
                                            <?= $transacao['tipo'] === 'debito' ? 'SAÍDA' : 'ENTRADA' ?>
                                        </span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($transacao['banco'] ?? '') ?> - <?= htmlspecialchars($transacao['identificacao'] ?? 'Sem identificação') ?></span>
                                        <?php if ($transacao['confianca_ia']): ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                IA: <?= number_format($transacao['confianca_ia'], 0) ?>% confiança
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                                        <?= htmlspecialchars($transacao['descricao_original']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Data: <?= date('d/m/Y', strtotime($transacao['data_transacao'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold <?= $transacao['tipo'] === 'debito' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                                        R$ <?= number_format(abs($transacao['valor']), 2, ',', '.') ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Sugestões da IA -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Classificação Sugerida:</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-blue-700 dark:text-blue-300 font-medium">Categoria:</span>
                                        <span class="text-gray-900 dark:text-gray-100 ml-2"><?= htmlspecialchars($transacao['categoria_sugerida_nome'] ?? 'Não sugerida') ?></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 dark:text-blue-300 font-medium">Centro de Custo:</span>
                                        <span class="text-gray-900 dark:text-gray-100 ml-2"><?= htmlspecialchars($transacao['centro_custo_sugerido_nome'] ?? 'Não sugerido') ?></span>
                                    </div>
                                </div>
                                <?php if ($transacao['justificativa_ia']): ?>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">💡 <?= htmlspecialchars($transacao['justificativa_ia']) ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Ações -->
                            <div class="flex gap-3">
                                <button 
                                    @click="aprovar(<?= $transacao['id'] ?>)" 
                                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Aprovar e Lançar
                                </button>
                                <a 
                                    href="/transacoes-pendentes/<?= $transacao['id'] ?>" 
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Editar
                                </a>
                                <button 
                                    @click="ignorar(<?= $transacao['id'] ?>)" 
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Ignorar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function transacoesPendentes() {
    return {
        selecionadas: [],
        
        async aprovar(id) {
            if (!confirm('Deseja aprovar esta transação e lançá-la nas contas a pagar/receber?')) return;
            
            try {
                const res = await fetch(`/transacoes-pendentes/${id}/aprovar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (err) {
                alert('Erro: ' + err.message);
            }
        },
        
        async ignorar(id) {
            if (!confirm('Deseja ignorar esta transação?')) return;
            
            try {
                const res = await fetch(`/transacoes-pendentes/${id}/ignorar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (err) {
                alert('Erro: ' + err.message);
            }
        },
        
        async aprovarLote() {
            if (this.selecionadas.length === 0) return;
            if (!confirm(`Deseja aprovar ${this.selecionadas.length} transações selecionadas?`)) return;
            
            try {
                const res = await fetch('/transacoes-pendentes/aprovar-lote', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ transacoes: this.selecionadas })
                });
                const data = await res.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (err) {
                alert('Erro: ' + err.message);
            }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
