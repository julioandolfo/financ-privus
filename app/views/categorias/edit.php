<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/categorias') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            ✏️ Editar Categoria Financeira
        </h1>
    </div>

    <!-- Formulário -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <form method="POST" action="<?= $this->baseUrl('/categorias/' . $categoria['id']) ?>" class="space-y-6">
            <?php
            $old = $this->session->get('old') ?? [];
            ?>

            <!-- Empresa -->
            <div>
                <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa *
                </label>
                <select id="empresa_id" 
                        name="empresa_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['empresa_id']) ? 'border-red-500' : '' ?>" 
                        required>
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= (($old['empresa_id'] ?? $categoria['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo *
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($old['tipo'] ?? $categoria['tipo']) === 'receita' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="receita" 
                               <?= ($old['tipo'] ?? $categoria['tipo']) === 'receita' ? 'checked' : '' ?>
                               class="w-5 h-5 text-green-600 focus:ring-green-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">💰 Receita</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Entradas financeiras</div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($old['tipo'] ?? $categoria['tipo']) === 'despesa' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="despesa" 
                               <?= ($old['tipo'] ?? $categoria['tipo']) === 'despesa' ? 'checked' : '' ?>
                               class="w-5 h-5 text-red-600 focus:ring-red-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">💸 Despesa</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Saídas financeiras</div>
                        </div>
                    </label>
                </div>
                <?php if (isset($this->session->get('errors')['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Código e Nome -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <?php 
                    $codigoObrigatorio = \App\Models\Configuracao::get('categorias.codigo_obrigatorio', false);
                    $codigoAutoGerado = \App\Models\Configuracao::get('categorias.codigo_auto_gerado', true);
                    $isCodigoRequired = $codigoObrigatorio || !$codigoAutoGerado;
                    ?>
                    <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Código <?= $isCodigoRequired ? '*' : '(Opcional)' ?>
                    </label>
                    <input type="text" 
                           id="codigo" 
                           name="codigo" 
                           value="<?= htmlspecialchars($old['codigo'] ?? $categoria['codigo']) ?>"
                           maxlength="20"
                           pattern="[A-Z0-9._-]+"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['codigo']) ? 'border-red-500' : '' ?>" 
                           <?= $isCodigoRequired ? 'required' : '' ?>>
                    <?php if (isset($this->session->get('errors')['codigo'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['codigo'] ?></p>
                    <?php elseif ($codigoAutoGerado && empty($categoria['codigo'])): ?>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Código gerado automaticamente
                            </span>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome *
                    </label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="<?= htmlspecialchars($old['nome'] ?? $categoria['nome']) ?>"
                           minlength="3"
                           maxlength="255"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($this->session->get('errors')['nome'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categoria Pai -->
            <div>
                <label for="categoria_pai_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Categoria Pai (Opcional)
                </label>
                <select id="categoria_pai_id" 
                        name="categoria_pai_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['categoria_pai_id']) ? 'border-red-500' : '' ?>">
                    <option value="">Nenhuma (Categoria Principal)</option>
                    <?php foreach ($categoriasPai as $categoriaPai): ?>
                        <option value="<?= $categoriaPai['id'] ?>" <?= (($old['categoria_pai_id'] ?? $categoria['categoria_pai_id']) == $categoriaPai['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoriaPai['codigo']) ?> - <?= htmlspecialchars($categoriaPai['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->session->get('errors')['categoria_pai_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['categoria_pai_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($old['ativo'] ?? $categoria['ativo']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Categoria Ativa
                </label>
            </div>

            <!-- Botões -->
            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Salvar Alterações
                </button>
                <a href="<?= $this->baseUrl('/categorias') ?>" 
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

