<div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= $this->baseUrl('/usuarios') ?>" 
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                ➕ Novo Usuário
            </h1>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <form method="POST" action="<?= $this->baseUrl('/usuarios/store') ?>" class="space-y-6">
                <!-- Nome -->
                <div>
                    <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome Completo *
                    </label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($this->session->get('errors')['nome'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Email *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($this->session->get('old')['email'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['email']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($this->session->get('errors')['email'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['email'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Senha -->
                <div>
                    <label for="senha" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Senha *
                    </label>
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['senha']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($this->session->get('errors')['senha'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['senha'] ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Mínimo de 6 caracteres</p>
                    <?php endif; ?>
                </div>

                <!-- Empresa -->
                <div>
                    <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Empresa Principal (Opcional)
                    </label>
                    <select id="empresa_id" 
                            name="empresa_id" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= (($this->session->get('old')['empresa_id'] ?? '') == $empresa['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           <?= ($this->session->get('old')['ativo'] ?? '1') ? 'checked' : '' ?>
                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Usuário Ativo
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Criar Usuário
                    </button>
                    <a href="<?= $this->baseUrl('/usuarios') ?>" 
                       class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
</div>

<?php 
// Limpa old e errors após exibição
$this->session->delete('old');
$this->session->delete('errors');
?>

