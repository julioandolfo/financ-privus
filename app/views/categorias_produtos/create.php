<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="<?= $this->baseUrl('/categorias-produtos') ?>" 
               class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nova Categoria</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Preencha os dados para criar uma nova categoria de produtos</p>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <form method="POST" action="<?= $this->baseUrl('/categorias-produtos') ?>" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            
            <!-- Nome -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nome da Categoria <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>"
                       required
                       maxlength="100"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>">
                <?php if (isset($this->session->get('errors')['nome'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $this->session->get('errors')['nome'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Categoria Pai -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categoria Pai
                    <span class="text-gray-500 text-xs">(deixe em branco para categoria raiz)</span>
                </label>
                <select name="categoria_pai_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Nenhuma (Categoria Raiz)</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" 
                                <?= (($this->session->get('old')['categoria_pai_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                            <?= str_repeat('—', $cat['level']) ?> <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Descrição -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descrição
                </label>
                <textarea name="descricao" 
                          rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($this->session->get('old')['descricao'] ?? '') ?></textarea>
            </div>

            <!-- Ícone e Cor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Ícone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ícone
                        <span class="text-gray-500 text-xs">(emoji ou símbolo)</span>
                    </label>
                    <input type="text" 
                           name="icone" 
                           value="<?= htmlspecialchars($this->session->get('old')['icone'] ?? '') ?>"
                           maxlength="50"
                           placeholder="Ex: 📦 🏷️ 📱"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Cor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cor
                        <span class="text-gray-500 text-xs">(formato hexadecimal)</span>
                    </label>
                    <input type="color" 
                           name="cor" 
                           value="<?= htmlspecialchars($this->session->get('old')['cor'] ?? '#3B82F6') ?>"
                           class="w-full h-12 px-4 py-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($this->session->get('errors')['cor']) ? 'border-red-500' : '' ?>">
                    <?php if (isset($this->session->get('errors')['cor'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $this->session->get('errors')['cor'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ordem e Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Ordem -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="<?= htmlspecialchars($this->session->get('old')['ordem'] ?? '0') ?>"
                           min="0"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status
                    </label>
                    <select name="ativo" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1" <?= (($this->session->get('old')['ativo'] ?? '1') == '1') ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= (($this->session->get('old')['ativo'] ?? '1') == '0') ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-4">
            <a href="<?= $this->baseUrl('/categorias-produtos') ?>" 
               class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                Criar Categoria
            </button>
        </div>
    </form>
</div>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
