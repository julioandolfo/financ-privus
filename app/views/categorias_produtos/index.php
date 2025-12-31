<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Categorias de Produtos</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Organize seus produtos em categorias hierárquicas</p>
            </div>
            <div class="flex gap-3">
                <!-- Toggle View -->
                <div class="flex bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <a href="<?= $this->baseUrl('/categorias-produtos?view=tree') ?>" 
                       class="px-4 py-2 rounded-l-lg <?= $view === 'tree' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                        </svg>
                    </a>
                    <a href="<?= $this->baseUrl('/categorias-produtos?view=flat') ?>" 
                       class="px-4 py-2 rounded-r-lg border-l border-gray-200 dark:border-gray-700 <?= $view === 'flat' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </a>
                </div>
                
                <a href="<?= $this->baseUrl('/categorias-produtos/create') ?>" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nova Categoria
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Categorias -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($categorias)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Nenhuma categoria cadastrada</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Comece criando sua primeira categoria de produtos.</p>
                <div class="mt-6">
                    <a href="<?= $this->baseUrl('/categorias-produtos/create') ?>" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Criar Primeira Categoria
                    </a>
                </div>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase tracking-wider">Produtos</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if ($view === 'tree'): ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <?php $this->renderCategoria($categoria, 0); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($categoria['level'] > 0): ?>
                                            <span class="text-gray-400 dark:text-gray-600 mr-2"><?= str_repeat('—', $categoria['level']) ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($categoria['icone']): ?>
                                            <span class="mr-2"><?= htmlspecialchars($categoria['icone']) ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($categoria['cor']): ?>
                                            <span class="w-4 h-4 rounded-full mr-2" style="background-color: <?= htmlspecialchars($categoria['cor']) ?>"></span>
                                        <?php endif; ?>
                                        
                                        <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($categoria['nome']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($categoria['descricao'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php
                                    $categoriaModel = new \App\Models\CategoriaProduto();
                                    $totalProdutos = $categoriaModel->countProdutos($categoria['id']);
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                        <?= $totalProdutos ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($categoria['ativo']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                            Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                            Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= $this->baseUrl('/categorias-produtos/' . $categoria['id'] . '/edit') ?>" 
                                           class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        
                                        <form method="POST" action="<?= $this->baseUrl('/categorias-produtos/' . $categoria['id'] . '/delete') ?>" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Excluir">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
// Função helper para renderizar categoria em árvore
function renderCategoria($categoria, $level) {
    $categoriaModel = new \App\Models\CategoriaProduto();
    $totalProdutos = $categoriaModel->countProdutos($categoria['id']);
    ?>
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <td class="px-6 py-4">
            <div class="flex items-center" style="padding-left: <?= $level * 24 ?>px;">
                <?php if ($categoria['tem_filhos']): ?>
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                <?php endif; ?>
                
                <?php if ($categoria['icone']): ?>
                    <span class="mr-2"><?= htmlspecialchars($categoria['icone']) ?></span>
                <?php endif; ?>
                
                <?php if ($categoria['cor']): ?>
                    <span class="w-4 h-4 rounded-full mr-2" style="background-color: <?= htmlspecialchars($categoria['cor']) ?>"></span>
                <?php endif; ?>
                
                <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($categoria['nome']) ?></span>
            </div>
        </td>
        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
            <?= htmlspecialchars($categoria['descricao'] ?? '—') ?>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                <?= $totalProdutos ?>
            </span>
        </td>
        <td class="px-6 py-4 text-center">
            <?php if ($categoria['ativo']): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                    Ativo
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    Inativo
                </span>
            <?php endif; ?>
        </td>
        <td class="px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-2">
                <a href="<?= $this->baseUrl('/categorias-produtos/' . $categoria['id'] . '/edit') ?>" 
                   class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" title="Editar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
                
                <form method="POST" action="<?= $this->baseUrl('/categorias-produtos/' . $categoria['id'] . '/delete') ?>" 
                      onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Excluir">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    <?php
    if (!empty($categoria['filhos'])) {
        foreach ($categoria['filhos'] as $filho) {
            renderCategoria($filho, $level + 1);
        }
    }
}

$this->session->delete('old');
$this->session->delete('errors');
?>
