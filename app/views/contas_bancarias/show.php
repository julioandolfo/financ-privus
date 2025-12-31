<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="<?= $this->baseUrl('/contas-bancarias') ?>" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detalhes da Conta Bancária</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1"><?= htmlspecialchars($contaBancaria['banco_nome']) ?></p>
                </div>
            </div>
            <a href="<?= $this->baseUrl('/contas-bancarias/' . $contaBancaria['id'] . '/edit') ?>" 
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
                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Empresa</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= isset($contaBancaria['empresa']) ? htmlspecialchars($contaBancaria['empresa']['nome_fantasia']) : 'N/A' ?>
                    </p>
                </div>

                <!-- Banco -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Banco</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($contaBancaria['banco_nome']) ?>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Código: <?= htmlspecialchars($contaBancaria['banco_codigo']) ?>
                    </p>
                </div>

                <!-- Agência -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Agência</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($contaBancaria['agencia']) ?>
                    </p>
                </div>

                <!-- Conta -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Número da Conta</label>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($contaBancaria['conta']) ?>
                    </p>
                </div>

                <!-- Tipo de Conta -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Tipo de Conta</label>
                    <?php 
                    $tipoColors = [
                        'corrente' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                        'poupanca' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                        'investimento' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
                    ];
                    $tipoLabels = [
                        'corrente' => 'Conta Corrente',
                        'poupanca' => 'Poupança',
                        'investimento' => 'Investimento'
                    ];
                    $colorClass = $tipoColors[$contaBancaria['tipo_conta']] ?? $tipoColors['corrente'];
                    $label = $tipoLabels[$contaBancaria['tipo_conta']] ?? 'Corrente';
                    ?>
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full <?= $colorClass ?>">
                        <?= $label ?>
                    </span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Status</label>
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full <?= $contaBancaria['ativo'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' ?>">
                        <?= $contaBancaria['ativo'] ? 'Ativa' : 'Inativa' ?>
                    </span>
                </div>
            </div>

            <!-- Saldos -->
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Saldos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Saldo Inicial -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                        <label class="block text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">Saldo Inicial</label>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            R$ <?= number_format($contaBancaria['saldo_inicial'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <!-- Saldo Atual -->
                    <div class="<?= $contaBancaria['saldo_atual'] >= 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' ?> rounded-xl p-6 border">
                        <label class="block text-sm font-semibold <?= $contaBancaria['saldo_atual'] >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' ?> mb-2">Saldo Atual</label>
                        <p class="text-2xl font-bold <?= $contaBancaria['saldo_atual'] >= 0 ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100' ?>">
                            R$ <?= number_format($contaBancaria['saldo_atual'], 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Data de Cadastro -->
            <?php if (isset($contaBancaria['data_cadastro'])): ?>
            <div class="mt-6">
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Data de Cadastro</label>
                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    <?= date('d/m/Y H:i', strtotime($contaBancaria['data_cadastro'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Ações -->
        <div class="bg-gray-50 dark:bg-gray-700/50 px-8 py-4 flex items-center justify-end space-x-4">
            <a href="<?= $this->baseUrl('/contas-bancarias') ?>" 
               class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                Voltar
            </a>
            <a href="<?= $this->baseUrl('/contas-bancarias/' . $contaBancaria['id'] . '/edit') ?>" 
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Editar
            </a>
            <form method="POST" action="<?= $this->baseUrl('/contas-bancarias/' . $contaBancaria['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta conta bancária?');">
                <button type="submit" 
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Excluir
                </button>
            </form>
        </div>
    </div>
</div>
