<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                📊 Gerenciar Categorias Financeiras
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Organize receitas e despesas em categorias hierárquicas
            </p>
        </div>
        <a href="<?= $this->baseUrl('/categorias/create') ?>" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Categoria
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6 border border-gray-200 dark:border-gray-700">
        <form method="GET" action="<?= $this->baseUrl('/categorias') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa
                </label>
                <select id="empresa_id" 
                        name="empresa_id" 
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= ($filters['empresa_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="tipo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo
                </label>
                <select id="tipo" 
                        name="tipo" 
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Todos</option>
                    <option value="receita" <?= ($filters['tipo'] ?? '') === 'receita' ? 'selected' : '' ?>>Receita</option>
                    <option value="despesa" <?= ($filters['tipo'] ?? '') === 'despesa' ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div>
                <label for="view" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Visualização
                </label>
                <select id="view" 
                        name="view" 
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="flat" <?= ($viewMode ?? 'flat') === 'flat' ? 'selected' : '' ?>>Lista</option>
                    <option value="tree" <?= ($viewMode ?? 'flat') === 'tree' ? 'selected' : '' ?>>Árvore</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela/Árvore -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <?php if (empty($categorias)): ?>
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Nenhuma categoria cadastrada</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Comece criando sua primeira categoria</p>
                <a href="<?= $this->baseUrl('/categorias/create') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Categoria
                </a>
            </div>
        <?php else: ?>
            <?php if ($viewMode === 'tree'): ?>
                <!-- Visualização em Árvore -->
                <div class="p-6">
                    <?php
                    $renderTree = function($categorias, $level = 0) use (&$renderTree) {
                        foreach ($categorias as $categoria):
                            $indent = $level * 24;
                            $hasChildren = !empty($categoria['children']);
                    ?>
                        <div class="mb-2" style="padding-left: <?= $indent ?>px;">
                            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border border-gray-200 dark:border-gray-700">
                                <?php if ($hasChildren): ?>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                <?php else: ?>
                                    <span class="w-5"></span>
                                <?php endif; ?>
                                
                                <div class="flex-1 flex items-center gap-3">
                                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($categoria['codigo']) ?></span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($categoria['nome']) ?></span>
                                    <?php if ($categoria['tipo'] === 'receita'): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            💰 Receita
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            💸 Despesa
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!$categoria['ativo']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Inativa
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <a href="<?= $this->baseUrl('/categorias/' . $categoria['id']) ?>" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" 
                                       title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/edit') ?>" 
                                       class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" 
                                       title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/delete') ?>" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?')" 
                                          class="inline">
                                        <button type="submit" 
                                                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" 
                                                title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($hasChildren) {
                            $renderTree($categoria['children'], $level + 1);
                        }
                        ?>
                    <?php endforeach;
                    };
                    $renderTree($categorias);
                    ?>
                </div>
            <?php else: ?>
                <!-- Visualização em Lista -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Código</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Nome</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Tipo</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Categoria Pai</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($categorias as $categoria): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($categoria['codigo']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($categoria['nome']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($categoria['tipo'] === 'receita'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                💰 Receita
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                💸 Despesa
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <?= $categoria['categoria_pai_id'] ? 'Subcategoria' : '<span class="text-gray-400">—</span>' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($categoria['ativo']): ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                Ativa
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                Inativa
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= $this->baseUrl('/categorias/' . $categoria['id']) ?>" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" 
                                               title="Visualizar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/edit') ?>" 
                                               class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" 
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/delete') ?>" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?')" 
                                                  class="inline">
                                                <button type="submit" 
                                                        class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" 
                                                        title="Excluir">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

