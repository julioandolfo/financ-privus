<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                🏢 Gerenciar Centros de Custo
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Organize centros de custo em estrutura hierárquica
            </p>
        </div>
        <a href="<?= $this->baseUrl('/centros-custo/create') ?>" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Centro de Custo
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6 border border-gray-200 dark:border-gray-700">
        <form method="GET" action="<?= $this->baseUrl('/centros-custo') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <?php if (empty($centrosCusto)): ?>
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Nenhum centro de custo cadastrado</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Comece criando seu primeiro centro de custo</p>
                <a href="<?= $this->baseUrl('/centros-custo/create') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Centro de Custo
                </a>
            </div>
        <?php else: ?>
            <?php if ($viewMode === 'tree'): ?>
                <!-- Visualização em Árvore -->
                <div class="p-6">
                    <?php
                    $renderTree = function($centrosCusto, $level = 0) use (&$renderTree) {
                        foreach ($centrosCusto as $centroCusto):
                            $indent = $level * 24;
                            $hasChildren = !empty($centroCusto['children']);
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
                                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($centroCusto['codigo']) ?></span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($centroCusto['nome']) ?></span>
                                    <?php if (!$centroCusto['ativo']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Inativo
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <a href="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id']) ?>" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" 
                                       title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id'] . '/edit') ?>" 
                                       class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" 
                                       title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id'] . '/delete') ?>" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir este centro de custo?')" 
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
                            $renderTree($centroCusto['children'], $level + 1);
                        }
                        ?>
                    <?php endforeach;
                    };
                    $renderTree($centrosCusto);
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
                                <th class="px-6 py-4 text-left text-sm font-semibold">Centro Pai</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($centrosCusto as $centroCusto): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($centroCusto['codigo']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($centroCusto['nome']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <?= $centroCusto['centro_pai_id'] ? 'Subcentro' : '<span class="text-gray-400">—</span>' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($centroCusto['ativo']): ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                Inativo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id']) ?>" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" 
                                               title="Visualizar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id'] . '/edit') ?>" 
                                               class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" 
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="<?= $this->baseUrl('/centros-custo/' . $centroCusto['id'] . '/delete') ?>" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este centro de custo?')" 
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

