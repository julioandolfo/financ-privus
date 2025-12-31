<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="<?= $this->baseUrl('/formas-pagamento') ?>" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detalhes da Forma de Pagamento</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1"><?= htmlspecialchars($formaPagamento['nome']) ?></p>
                </div>
            </div>
            <a href="<?= $this->baseUrl('/formas-pagamento/' . $formaPagamento['id'] . '/edit') ?>" 
               class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Editar</span>
            </a>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Código</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($formaPagamento['codigo']) ?>
                    </p>
                </div>

                <!-- Nome -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Nome</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($formaPagamento['nome']) ?>
                    </p>
                </div>

                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Empresa</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= isset($formaPagamento['empresa']) ? htmlspecialchars($formaPagamento['empresa']['nome_fantasia']) : 'N/A' ?>
                    </p>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Tipo</label>
                    <?php 
                    $tipoColors = [
                        'pagamento' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                        'recebimento' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                        'ambos' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                    ];
                    $tipoLabels = [
                        'pagamento' => 'Pagamento',
                        'recebimento' => 'Recebimento',
                        'ambos' => 'Ambos (Pagamento e Recebimento)'
                    ];
                    $colorClass = $tipoColors[$formaPagamento['tipo']] ?? $tipoColors['ambos'];
                    $label = $tipoLabels[$formaPagamento['tipo']] ?? 'Ambos';
                    ?>
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full <?= $colorClass ?>">
                        <?= $label ?>
                    </span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Status</label>
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full <?= $formaPagamento['ativo'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' ?>">
                        <?= $formaPagamento['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </div>

                <!-- Data de Cadastro -->
                <?php if (isset($formaPagamento['data_cadastro'])): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Data de Cadastro</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= date('d/m/Y H:i', strtotime($formaPagamento['data_cadastro'])) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ações -->
        <div class="bg-gray-50 dark:bg-gray-700/50 px-8 py-4 flex items-center justify-end space-x-4">
            <a href="<?= $this->baseUrl('/formas-pagamento') ?>" 
               class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                Voltar
            </a>
            <a href="<?= $this->baseUrl('/formas-pagamento/' . $formaPagamento['id'] . '/edit') ?>" 
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Editar
            </a>
            <form method="POST" action="<?= $this->baseUrl('/formas-pagamento/' . $formaPagamento['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta forma de pagamento?');">
                <button type="submit" 
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Excluir
                </button>
            </form>
        </div>
    </div>
</div>
