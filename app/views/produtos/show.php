<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($produto['nome']) ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Código: <?= htmlspecialchars($produto['codigo']) ?></p>
        </div>
        <div class="flex space-x-3">
            <a href="/produtos/<?= $produto['id'] ?>/edit" class="px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-colors shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Editar</span>
            </a>
            <a href="/produtos" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informações Básicas -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações do Produto
                </h2>

                <div class="space-y-4">
                    <!-- Código -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 pb-4">
                        <div class="w-1/3">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Código:</span>
                        </div>
                        <div class="w-2/3">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-lg"><?= htmlspecialchars($produto['codigo']) ?></span>
                        </div>
                    </div>

                    <!-- Nome -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 pb-4">
                        <div class="w-1/3">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Nome:</span>
                        </div>
                        <div class="w-2/3">
                            <span class="text-sm text-gray-900 dark:text-gray-100 font-medium"><?= htmlspecialchars($produto['nome']) ?></span>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <?php if ($produto['descricao']): ?>
                        <div class="flex items-start border-b border-gray-200 dark:border-gray-700 pb-4">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Descrição:</span>
                            </div>
                            <div class="w-2/3">
                                <p class="text-sm text-gray-700 dark:text-gray-300"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Unidade -->
                    <div class="flex items-start">
                        <div class="w-1/3">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Unidade de Medida:</span>
                        </div>
                        <div class="w-2/3">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-3 py-1 rounded-full"><?= htmlspecialchars($produto['unidade_medida']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empresa -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Empresa
                </h3>
                <p class="text-purple-800 dark:text-purple-200"><?= htmlspecialchars($produto['empresa_nome']) ?></p>
            </div>
        </div>

        <!-- Sidebar - Valores -->
        <div class="space-y-6">
            <!-- Custo Unitário -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Custo Unitário</span>
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($produto['custo_unitario'], 2, ',', '.') ?></p>
            </div>

            <!-- Preço de Venda -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Preço de Venda</span>
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></p>
            </div>

            <!-- Margem de Lucro -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium opacity-90">Margem de Lucro</span>
                    <svg class="w-8 h-8 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <p class="text-4xl font-bold"><?= number_format($margem_lucro, 1, ',', '.') ?>%</p>
                <div class="mt-4 pt-4 border-t border-white/20">
                    <p class="text-sm opacity-90">Lucro por unidade:</p>
                    <p class="text-xl font-semibold mt-1">R$ <?= number_format($produto['preco_venda'] - $produto['custo_unitario'], 2, ',', '.') ?></p>
                </div>
            </div>

            <!-- Data de Cadastro -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cadastrado em</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            <?= date('d/m/Y \à\s H:i', strtotime($produto['data_cadastro'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
