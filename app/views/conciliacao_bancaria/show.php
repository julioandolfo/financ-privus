<div class="max-w-7xl mx-auto" x-data="conciliacaoData()">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Conciliação #<?= $conciliacao['id'] ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?= htmlspecialchars($conciliacao['banco'] . ' - ' . $conciliacao['conta_descricao']) ?> 
                (<?= date('d/m/Y', strtotime($conciliacao['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($conciliacao['data_fim'])) ?>)
            </p>
        </div>
        <div class="flex space-x-3">
            <?php if ($conciliacao['status'] === 'aberta'): ?>
                <form method="POST" action="/conciliacao-bancaria/<?= $conciliacao['id'] ?>/fechar" class="inline" onsubmit="return confirm('Tem certeza que deseja fechar esta conciliação? Todas as movimentações serão marcadas como conciliadas.')">
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all shadow-lg">
                        Fechar Conciliação
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="/conciliacao-bancaria/<?= $conciliacao['id'] ?>/reabrir" class="inline">
                    <button type="submit" class="px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all shadow-lg">
                        Reabrir Conciliação
                    </button>
                </form>
            <?php endif; ?>
            <a href="/conciliacao-bancaria" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Saldo Extrato -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Extrato</span>
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($conciliacao['saldo_extrato'], 2, ',', '.') ?></p>
        </div>

        <!-- Saldo Sistema -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Sistema</span>
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($conciliacao['saldo_sistema'], 2, ',', '.') ?></p>
        </div>

        <!-- Diferença -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Diferença</span>
                <svg class="w-8 h-8 <?= abs($conciliacao['diferenca']) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold <?= abs($conciliacao['diferenca']) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                R$ <?= number_format($conciliacao['diferenca'], 2, ',', '.') ?>
            </p>
        </div>

        <!-- Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</span>
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                <?php if ($conciliacao['status'] === 'fechada'): ?>
                    <span class="text-green-600 dark:text-green-400">Fechada</span>
                <?php else: ?>
                    <span class="text-yellow-600 dark:text-yellow-400">Aberta</span>
                <?php endif; ?>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <?= ($estatisticas['vinculados'] ?? 0) ?> de <?= ($estatisticas['total'] ?? 0) ?> itens vinculados
            </p>
        </div>
    </div>

    <!-- Área de Conciliação -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Itens do Extrato -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Itens do Extrato
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Clique em um item para vinculá-lo a uma movimentação</p>
            </div>
            <div class="p-6 max-h-[600px] overflow-y-auto space-y-3">
                <?php if (empty($itens)): ?>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Nenhum item do extrato adicionado</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($itens as $item): ?>
                        <div @click="<?= $item['vinculado'] ? '' : 'selecionarItemExtrato(' . htmlspecialchars(json_encode($item)) . ')' ?>" 
                             class="p-4 rounded-lg border <?= $item['vinculado'] ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20' ?> transition-all"
                             :class="itemExtratoSelecionado && itemExtratoSelecionado.id === <?= $item['id'] ?> ? 'ring-2 ring-blue-500' : ''">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['descricao_extrato']) ?></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        <?= date('d/m/Y', strtotime($item['data_extrato'])) ?>
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-bold <?= $item['tipo_extrato'] === 'credito' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                        <?= $item['tipo_extrato'] === 'credito' ? '+' : '-' ?> R$ <?= number_format($item['valor_extrato'], 2, ',', '.') ?>
                                    </p>
                                    <?php if ($item['vinculado']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 mt-2">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Vinculado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Movimentações Não Conciliadas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    Movimentações do Sistema
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    <span x-text="itemExtratoSelecionado ? 'Clique na movimentação para vincular' : 'Selecione um item do extrato primeiro'"></span>
                </p>
            </div>
            <div class="p-6 max-h-[600px] overflow-y-auto space-y-3">
                <?php if (empty($movimentacoes)): ?>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Todas as movimentações foram conciliadas!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($movimentacoes as $mov): ?>
                        <div @click="vincularMovimentacao(<?= $mov['id'] ?>)"
                             class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 transition-all"
                             :class="itemExtratoSelecionado ? 'cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20' : 'opacity-50 cursor-not-allowed'">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($mov['descricao_completa']) ?></p>
                                    <div class="flex items-center space-x-3 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span><?= date('d/m/Y', strtotime($mov['data_movimentacao'])) ?></span>
                                        <?php if ($mov['forma_pagamento_nome']): ?>
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs">
                                                <?= htmlspecialchars($mov['forma_pagamento_nome']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-bold <?= $mov['tipo'] === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                        <?= $mov['tipo'] === 'entrada' ? '+' : '-' ?> R$ <?= number_format($mov['valor'], 2, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($conciliacao['observacoes']): ?>
        <!-- Observações -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Observações</h3>
            <p class="text-blue-800 dark:text-blue-200"><?= nl2br(htmlspecialchars($conciliacao['observacoes'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
function conciliacaoData() {
    return {
        itemExtratoSelecionado: null,
        
        selecionarItemExtrato(item) {
            if (item.vinculado) return;
            this.itemExtratoSelecionado = item;
        },
        
        async vincularMovimentacao(movimentacaoId) {
            if (!this.itemExtratoSelecionado) {
                alert('Selecione um item do extrato primeiro!');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('item_id', this.itemExtratoSelecionado.id);
                formData.append('movimentacao_id', movimentacaoId);
                formData.append('conciliacao_id', <?= $conciliacao['id'] ?>);
                
                const response = await fetch('/conciliacao-bancaria/vincular', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Item vinculado com sucesso!');
                    window.location.reload();
                } else {
                    alert('Erro ao vincular: ' + result.message);
                }
            } catch (error) {
                alert('Erro ao processar vinculação');
                console.error(error);
            }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
