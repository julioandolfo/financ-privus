<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 text-sm text-gray-600 dark:text-gray-400 mb-4">
            <a href="/movimentacoes-caixa" class="hover:text-blue-600 dark:hover:text-blue-400">Movimentações</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900 dark:text-gray-100 font-medium">Detalhes da Movimentação</span>
        </div>
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Movimentação #<?= $movimentacao['id'] ?></h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <?= date('d/m/Y', strtotime($movimentacao['data_movimentacao'])) ?> às 
                    <?= date('H:i', strtotime($movimentacao['data_cadastro'])) ?>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <?php if (!$movimentacao['referencia_id'] && !$movimentacao['conciliado']): ?>
                    <a href="/movimentacoes-caixa/<?= $movimentacao['id'] ?>/edit" 
                       class="flex items-center space-x-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                <?php endif; ?>
                <a href="/movimentacoes-caixa" 
                   class="flex items-center space-x-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Card de Tipo e Valor -->
            <div class="bg-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-50 dark:bg-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-900/20 border-2 border-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-300 dark:border-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-700 rounded-2xl p-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-100 dark:bg-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-800 rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-600 dark:text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if ($movimentacao['tipo'] === 'entrada'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                <?php endif; ?>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-700 dark:text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-400 uppercase tracking-wider">
                                <?= $movimentacao['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?>
                            </p>
                            <p class="text-4xl font-bold text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-800 dark:text-<?= $movimentacao['tipo'] === 'entrada' ? 'green' : 'red' ?>-300">
                                R$ <?= number_format($movimentacao['valor'], 2, ',', '.') ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Status de Conciliação -->
                    <div>
                        <?php if ($movimentacao['conciliado']): ?>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Conciliado
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Pendente
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                    Descrição
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    <?= nl2br(htmlspecialchars($movimentacao['descricao'])) ?>
                </p>
            </div>

            <!-- Informações Financeiras -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Informações Financeiras
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Empresa</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($movimentacao['empresa_nome']) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Categoria</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($movimentacao['categoria_nome']) ?>
                        </p>
                    </div>
                    <?php if ($movimentacao['centro_custo_nome']): ?>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Centro de Custo</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($movimentacao['centro_custo_nome']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Conta Bancária</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($movimentacao['banco_nome']) ?>
                        </p>
                    </div>
                    <?php if ($movimentacao['forma_pagamento_nome']): ?>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Forma de Pagamento</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($movimentacao['forma_pagamento_nome']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Referência (se existir) -->
            <?php if ($referencia): ?>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Vinculado a <?= $referencia['tipo'] ?>
                    </h2>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Descrição da Conta</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <?= htmlspecialchars($referencia['descricao']) ?>
                        </p>
                        <a href="/<?= $movimentacao['referencia_tipo'] === 'conta_pagar' ? 'contas-pagar' : 'contas-receber' ?>/<?= $movimentacao['referencia_id'] ?>" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Ver conta completa
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <p class="mt-3 text-sm text-blue-700 dark:text-blue-300">
                        ℹ️ Esta movimentação foi criada automaticamente através da baixa de uma conta
                    </p>
                </div>
            <?php endif; ?>

            <!-- Observações -->
            <?php if ($movimentacao['observacoes']): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        Observações
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        <?= nl2br(htmlspecialchars($movimentacao['observacoes'])) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Coluna Lateral -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Card de Datas -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Datas
                </h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Data de Movimentação</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            <?= date('d/m/Y', strtotime($movimentacao['data_movimentacao'])) ?>
                        </p>
                    </div>
                    <?php if ($movimentacao['data_competencia']): ?>
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Data de Competência</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                <?= date('d/m/Y', strtotime($movimentacao['data_competencia'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Cadastrado em</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <?= date('d/m/Y \à\s H:i', strtotime($movimentacao['data_cadastro'])) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card de Ações -->
            <?php if (!$movimentacao['referencia_id'] && !$movimentacao['conciliado']): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ações</h2>
                    <div class="space-y-3">
                        <a href="/movimentacoes-caixa/<?= $movimentacao['id'] ?>/edit" 
                           class="flex items-center justify-center w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </a>
                        <form method="POST" action="/movimentacoes-caixa/<?= $movimentacao['id'] ?>/delete" 
                              onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação?');">
                            <button type="submit" 
                                    class="flex items-center justify-center w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-2xl p-6">
                    <h2 class="text-sm font-semibold text-yellow-900 dark:text-yellow-100 mb-2">
                        ⚠️ Movimentação Protegida
                    </h2>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        <?php if ($movimentacao['referencia_id']): ?>
                            Esta movimentação está vinculada a uma conta e não pode ser editada ou excluída diretamente.
                        <?php else: ?>
                            Esta movimentação está conciliada e não pode mais ser modificada.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
