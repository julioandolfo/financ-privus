<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4 mb-4">
            <a href="<?= $this->baseUrl('/contas-bancarias') ?>" 
               class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Conta Bancária</h1>
        </div>
        <p class="text-gray-600 dark:text-gray-400">Atualize os dados da conta bancária</p>
    </div>

    <!-- Formulário -->
    <form method="POST" action="<?= $this->baseUrl('/contas-bancarias/' . $contaBancaria['id']) ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Empresa -->
            <div class="md:col-span-2">
                <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa *
                </label>
                <select id="empresa_id" 
                        name="empresa_id" 
                        required
                        class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['empresa_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= (($this->session->get('old')['empresa_id'] ?? $contaBancaria['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Banco Código -->
            <div>
                <label for="banco_codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Código do Banco *
                </label>
                <input type="text" 
                       id="banco_codigo" 
                       name="banco_codigo" 
                       maxlength="10"
                       value="<?= htmlspecialchars($this->session->get('old')['banco_codigo'] ?? $contaBancaria['banco_codigo']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['banco_codigo']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['banco_codigo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['banco_codigo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Banco Nome -->
            <div>
                <label for="banco_nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nome do Banco *
                </label>
                <input type="text" 
                       id="banco_nome" 
                       name="banco_nome" 
                       maxlength="255"
                       value="<?= htmlspecialchars($this->session->get('old')['banco_nome'] ?? $contaBancaria['banco_nome']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['banco_nome']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['banco_nome'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['banco_nome'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Agência -->
            <div>
                <label for="agencia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Agência *
                </label>
                <input type="text" 
                       id="agencia" 
                       name="agencia" 
                       maxlength="20"
                       value="<?= htmlspecialchars($this->session->get('old')['agencia'] ?? $contaBancaria['agencia']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['agencia']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['agencia'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['agencia'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Conta -->
            <div>
                <label for="conta" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Número da Conta *
                </label>
                <input type="text" 
                       id="conta" 
                       name="conta" 
                       maxlength="20"
                       value="<?= htmlspecialchars($this->session->get('old')['conta'] ?? $contaBancaria['conta']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['conta']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['conta'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['conta'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo de Conta -->
            <div>
                <label for="tipo_conta" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo de Conta *
                </label>
                <select id="tipo_conta" 
                        name="tipo_conta" 
                        required
                        class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['tipo_conta']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="corrente" <?= ($this->session->get('old')['tipo_conta'] ?? $contaBancaria['tipo_conta']) == 'corrente' ? 'selected' : '' ?>>Conta Corrente</option>
                    <option value="poupanca" <?= ($this->session->get('old')['tipo_conta'] ?? $contaBancaria['tipo_conta']) == 'poupanca' ? 'selected' : '' ?>>Poupança</option>
                    <option value="investimento" <?= ($this->session->get('old')['tipo_conta'] ?? $contaBancaria['tipo_conta']) == 'investimento' ? 'selected' : '' ?>>Investimento</option>
                </select>
                <?php if (isset($this->session->get('errors')['tipo_conta'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo_conta'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Saldo Inicial (somente leitura na edição) -->
            <div>
                <label for="saldo_inicial" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Saldo Inicial
                </label>
                <input type="number" 
                       id="saldo_inicial" 
                       name="saldo_inicial" 
                       step="0.01"
                       value="<?= htmlspecialchars($contaBancaria['saldo_inicial']) ?>"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 cursor-not-allowed"
                       readonly>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">O saldo inicial não pode ser alterado</p>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3 md:col-span-2">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($this->session->get('old')['ativo'] ?? $contaBancaria['ativo']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Conta bancária ativa
                </label>
            </div>
        </div>

        <!-- Informação sobre Saldo Atual -->
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-blue-800 dark:text-blue-200">
                    Saldo Atual: <strong>R$ <?= number_format($contaBancaria['saldo_atual'], 2, ',', '.') ?></strong> (atualizado automaticamente pelas movimentações)
                </span>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= $this->baseUrl('/contas-bancarias') ?>" 
               class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                Atualizar
            </button>
        </div>
    </form>
</div>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
