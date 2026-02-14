<?php
$fotoModel = new \App\Models\ProdutoFoto();
$variacaoModel = new \App\Models\ProdutoVariacao();
$categoriaModel = new \App\Models\CategoriaProduto();

$fotos = $fotoModel->findByProduto($produto['id']);
$variacoes = $variacaoModel->findByProduto($produto['id']);
$fotoPrincipal = $fotoModel->findPrincipal($produto['id']);
$categoria = $produto['categoria_id'] ? $categoriaModel->findById($produto['categoria_id']) : null;

// Calcula margem
$margem = 0;
if ($produto['custo_unitario'] > 0) {
    $margem = (($produto['preco_venda'] - $produto['custo_unitario']) / $produto['custo_unitario']) * 100;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="<?= $this->baseUrl('/produtos') ?>" 
               class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($produto['nome']) ?></h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Código: <?= htmlspecialchars($produto['codigo']) ?></p>
            </div>
            <a href="<?= $this->baseUrl('/produtos/' . $produto['id'] . '/edit') ?>" 
               class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                ✏️ Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Esquerda: Fotos -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden sticky top-8">
                <!-- Foto Principal -->
                <div class="aspect-square bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                    <?php if ($fotoPrincipal): ?>
                        <img src="<?= htmlspecialchars($fotoPrincipal['caminho']) ?>" 
                             alt="<?= htmlspecialchars($produto['nome']) ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    <?php endif; ?>
                </div>

                <!-- Miniaturas -->
                <?php if (count($fotos) > 1): ?>
                    <div class="p-4 grid grid-cols-4 gap-2">
                        <?php foreach ($fotos as $foto): ?>
                            <div class="aspect-square rounded-lg overflow-hidden border-2 <?= $foto['principal'] ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' ?> cursor-pointer hover:border-blue-400 transition-colors">
                                <img src="<?= htmlspecialchars($foto['caminho']) ?>" 
                                     alt="Miniatura"
                                     class="w-full h-full object-cover">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coluna Direita: Informações -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Informações Gerais -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Informações Gerais</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Categoria -->
                    <?php if ($categoria): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Categoria</label>
                            <div class="flex items-center gap-2">
                                <?php if ($categoria['icone']): ?>
                                    <span><?= htmlspecialchars($categoria['icone']) ?></span>
                                <?php endif; ?>
                                <?php if ($categoria['cor']): ?>
                                    <span class="w-4 h-4 rounded-full" style="background-color: <?= htmlspecialchars($categoria['cor']) ?>"></span>
                                <?php endif; ?>
                                <span class="text-gray-900 dark:text-white font-medium"><?= htmlspecialchars($categoria['nome']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- SKU -->
                    <?php if (!empty($produto['sku'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">SKU</label>
                            <p class="text-gray-900 dark:text-white font-mono font-medium"><?= htmlspecialchars($produto['sku']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Código de Barras -->
                    <?php if (!empty($produto['codigo_barras'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Código de Barras</label>
                            <p class="text-gray-900 dark:text-white font-mono font-medium"><?= htmlspecialchars($produto['codigo_barras']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Cod Fornecedor -->
                    <?php if (!empty($produto['cod_fornecedor'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Cód. Fornecedor</label>
                            <p class="text-gray-900 dark:text-white font-mono font-medium">
                                <?= htmlspecialchars($produto['cod_fornecedor']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Unidade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Unidade de Medida</label>
                        <p class="text-gray-900 dark:text-white font-medium"><?= htmlspecialchars($produto['unidade_medida']) ?></p>
                    </div>

                    <!-- Estoque -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Estoque</label>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-bold <?= $produto['estoque'] <= $produto['estoque_minimo'] ? 'text-red-600' : 'text-green-600' ?>">
                                <?= $produto['estoque'] ?>
                            </span>
                            <span class="text-sm text-gray-500">/ Mín: <?= $produto['estoque_minimo'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Descrição -->
                <?php if ($produto['descricao']): ?>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Descrição</label>
                        <p class="text-gray-700 dark:text-gray-300"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Precificação -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Precificação</h2>
                
                <?php if (empty($produto['custo_unitario']) || $produto['custo_unitario'] == 0): ?>
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start gap-3">
                            <span class="text-amber-500 text-xl">⚠️</span>
                            <div>
                                <p class="font-semibold text-amber-800 dark:text-amber-200">Custo não informado</p>
                                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    <?php 
                                    $codForn = $produto['cod_fornecedor'] ?? '';
                                    // Verifica se é referência ACF inválida (field_xxxxx)
                                    $isAcfRef = preg_match('/^field_[a-f0-9]{10,}$/i', $codForn);
                                    ?>
                                    <?php if (!empty($codForn) && !$isAcfRef): ?>
                                        O cód. fornecedor <strong><?= htmlspecialchars($codForn) ?></strong> foi encontrado, mas não há preço correspondente na tabela de custos.
                                        Verifique se o cod_fornecedor está correto na tabela <code>custo_produtos_personizi</code>.
                                    <?php else: ?>
                                        Este produto não possui <strong>cod_fornecedor</strong> válido do WooCommerce. O custo não pode ser buscado automaticamente.
                                        <?php if ($isAcfRef): ?>
                                            <br><span class="text-xs">(Encontrado referência ACF <code><?= htmlspecialchars($codForn) ?></code> no lugar do código real)</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Custo Unitário</label>
                        <p class="text-2xl font-bold <?= ($produto['custo_unitario'] > 0) ? 'text-gray-900 dark:text-white' : 'text-red-500' ?>">
                            R$ <?= number_format($produto['custo_unitario'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Preço de Venda</label>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Margem de Lucro</label>
                        <p class="text-2xl font-bold <?= $margem > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= number_format($margem, 1, ',', '.') ?>%
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Lucro Unitário</label>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            R$ <?= number_format($produto['preco_venda'] - $produto['custo_unitario'], 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Variações -->
            <?php if (!empty($variacoes)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Variações (<?= count($variacoes) ?>)</h2>
                    
                    <div class="space-y-4">
                        <?php foreach ($variacoes as $variacao): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($variacao['nome']) ?></h4>
                                        
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <?php if ($variacao['sku']): ?>
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded">
                                                    SKU: <?= htmlspecialchars($variacao['sku']) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($variacao['codigo_barras']): ?>
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded">
                                                    📊 <?= htmlspecialchars($variacao['codigo_barras']) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($variacao['atributos'])): ?>
                                                <?php foreach ($variacao['atributos'] as $key => $value): ?>
                                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded">
                                                        <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right ml-4">
                                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                            R$ <?= number_format($variacao['preco_venda'], 2, ',', '.') ?>
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Estoque: <span class="font-bold <?= $variacao['estoque'] <= $variacao['estoque_minimo'] ? 'text-red-600' : 'text-green-600' ?>">
                                                <?= $variacao['estoque'] ?>
                                            </span>
                                        </p>
                                        <?php if ($variacao['peso']): ?>
                                            <p class="text-xs text-gray-500 mt-1">Peso: <?= number_format($variacao['peso'], 3, ',', '.') ?> kg</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Valor em Estoque</p>
                            <p class="text-3xl font-bold">R$ <?= number_format($produto['preco_venda'] * $produto['estoque'], 2, ',', '.') ?></p>
                        </div>
                        <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Lucro Potencial</p>
                            <p class="text-3xl font-bold">R$ <?= number_format(($produto['preco_venda'] - $produto['custo_unitario']) * $produto['estoque'], 2, ',', '.') ?></p>
                        </div>
                        <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm mb-1">Total de Fotos</p>
                            <p class="text-3xl font-bold"><?= count($fotos) ?></p>
                        </div>
                        <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
