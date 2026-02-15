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
                
                <?php 
                $temApi = !empty($contaBancaria['tem_conexao_api']);
                $saldoReal = (float) $contaBancaria['saldo_atual'];
                $saldoCalc = (float) ($contaBancaria['saldo_calculado'] ?? $contaBancaria['saldo_atual']);
                $diferenca = $saldoReal - $saldoCalc;
                ?>

                <div class="grid grid-cols-1 <?= $temApi ? 'md:grid-cols-4' : 'md:grid-cols-2' ?> gap-4">
                    <!-- Saldo Inicial -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                        <label class="block text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">Saldo Inicial</label>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            R$ <?= number_format($contaBancaria['saldo_inicial'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <!-- Saldo Real (API ou calculado) -->
                    <div class="<?= $saldoReal >= 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' ?> rounded-xl p-6 border">
                        <label class="block text-sm font-semibold <?= $saldoReal >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' ?> mb-2">
                            Saldo Real
                            <?php if ($temApi): ?>
                                <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200">API</span>
                            <?php endif; ?>
                        </label>
                        <p class="text-2xl font-bold <?= $saldoReal >= 0 ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100' ?>">
                            R$ <?= number_format($saldoReal, 2, ',', '.') ?>
                        </p>
                        <?php if ($temApi && !empty($contaBancaria['saldo_api_atualizado_em'])): ?>
                            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                                Atualizado <?= date('d/m/Y H:i', strtotime($contaBancaria['saldo_api_atualizado_em'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($temApi): ?>
                    <!-- Saldo Calculado (Movimentações) -->
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Saldo Calculado (Sistema)</label>
                        <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                            R$ <?= number_format($saldoCalc, 2, ',', '.') ?>
                        </p>
                        <p class="text-xs mt-1 text-gray-400 dark:text-gray-500">Baseado em receitas e despesas</p>
                    </div>

                    <!-- Diferença -->
                    <div class="<?= abs($diferenca) < 0.01 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' ?> rounded-xl p-6 border">
                        <label class="block text-sm font-semibold <?= abs($diferenca) < 0.01 ? 'text-green-700 dark:text-green-300' : 'text-amber-700 dark:text-amber-300' ?> mb-2">Diferença</label>
                        <?php if (abs($diferenca) < 0.01): ?>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">Conciliado</p>
                            <p class="text-xs mt-1 text-green-600 dark:text-green-400">Saldo real = calculado</p>
                        <?php else: ?>
                            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">
                                <?= $diferenca >= 0 ? '+' : '' ?>R$ <?= number_format($diferenca, 2, ',', '.') ?>
                            </p>
                            <p class="text-xs mt-1 text-amber-600 dark:text-amber-400">
                                <?= $diferenca > 0 ? 'Banco tem mais que o sistema' : 'Sistema tem mais que o banco' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($temApi): ?>
                <div class="mt-4 flex gap-3">
                    <a href="<?= $this->baseUrl('/conexoes-bancarias/' . $contaBancaria['conexao_id']) ?>" 
                       class="px-4 py-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 text-sm font-semibold rounded-xl transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Ver Conexão API
                    </a>
                </div>
                <?php endif; ?>
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
