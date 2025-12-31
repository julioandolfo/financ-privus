<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4 mb-4">
            <a href="<?= $this->baseUrl('/formas-pagamento') ?>" 
               class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Forma de Pagamento</h1>
        </div>
        <p class="text-gray-600 dark:text-gray-400">Atualize os dados da forma de pagamento</p>
    </div>

    <!-- Formulário -->
    <form method="POST" action="<?= $this->baseUrl('/formas-pagamento/' . $formaPagamento['id']) ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
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
                        <option value="<?= $empresa['id'] ?>" <?= (($this->session->get('old')['empresa_id'] ?? $formaPagamento['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Código *
                </label>
                <input type="text" 
                       id="codigo" 
                       name="codigo" 
                       maxlength="20"
                       value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? $formaPagamento['codigo']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['codigo']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['codigo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['codigo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Nome -->
            <div>
                <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nome *
                </label>
                <input type="text" 
                       id="nome" 
                       name="nome" 
                       maxlength="255"
                       value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $formaPagamento['nome']) ?>"
                       class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       required>
                <?php if (isset($this->session->get('errors')['nome'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo -->
            <div>
                <label for="tipo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo *
                </label>
                <select id="tipo" 
                        name="tipo" 
                        required
                        class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['tipo']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="ambos" <?= ($this->session->get('old')['tipo'] ?? $formaPagamento['tipo']) == 'ambos' ? 'selected' : '' ?>>Ambos (Pagamento e Recebimento)</option>
                    <option value="pagamento" <?= ($this->session->get('old')['tipo'] ?? $formaPagamento['tipo']) == 'pagamento' ? 'selected' : '' ?>>Pagamento</option>
                    <option value="recebimento" <?= ($this->session->get('old')['tipo'] ?? $formaPagamento['tipo']) == 'recebimento' ? 'selected' : '' ?>>Recebimento</option>
                </select>
                <?php if (isset($this->session->get('errors')['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3 md:col-span-2">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($this->session->get('old')['ativo'] ?? $formaPagamento['ativo']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Forma de pagamento ativa
                </label>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= $this->baseUrl('/formas-pagamento') ?>" 
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
