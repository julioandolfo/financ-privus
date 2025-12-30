<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/categorias') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <div class="flex items-center justify-between">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                📊 Detalhes da Categoria
            </h1>
            <a href="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/edit') ?>" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header do Card -->
        <div class="bg-gradient-to-r <?= $categoria['tipo'] === 'receita' ? 'from-green-600 to-emerald-600' : 'from-red-600 to-rose-600' ?> px-8 py-6">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-3xl font-bold shadow-lg">
                    <?= $categoria['tipo'] === 'receita' ? '💰' : '💸' ?>
                </div>
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($categoria['nome']) ?></h2>
                    <p class="text-white/80 mt-1 font-mono"><?= htmlspecialchars($categoria['codigo']) ?></p>
                </div>
                <?php if ($categoria['ativo']): ?>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-white/20 text-white backdrop-blur-sm">
                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                        Ativa
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-white/20 text-white backdrop-blur-sm">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        Inativa
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breadcrumb -->
        <?php if (!empty($categoria['path']) && count($categoria['path']) > 1): ?>
            <div class="px-8 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <nav class="flex items-center gap-2 text-sm">
                    <?php foreach ($categoria['path'] as $index => $pathItem): ?>
                        <?php if ($index > 0): ?>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        <?php endif; ?>
                        <?php if ($pathItem['id'] == $categoria['id']): ?>
                            <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pathItem['nome']) ?></span>
                        <?php else: ?>
                            <a href="<?= $this->baseUrl('/categorias/' . $pathItem['id']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($pathItem['nome']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
        <?php endif; ?>

        <!-- Corpo do Card -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        ID da Categoria
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        #<?= $categoria['id'] ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Status
                    </label>
                    <div class="text-lg font-semibold">
                        <?php if ($categoria['ativo']): ?>
                            <span class="text-green-600 dark:text-green-400">✓ Ativa</span>
                        <?php else: ?>
                            <span class="text-red-600 dark:text-red-400">✗ Inativa</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tipo -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Tipo
                    </label>
                    <div class="text-lg font-semibold">
                        <?php if ($categoria['tipo'] === 'receita'): ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                💰 Receita
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                💸 Despesa
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Código -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Código
                    </label>
                    <div class="text-lg font-semibold font-mono text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($categoria['codigo']) ?>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Empresa
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <?php if (!empty($categoria['empresa'])): ?>
                            <a href="<?= $this->baseUrl('/empresas/' . $categoria['empresa']['id']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($categoria['empresa']['nome_fantasia']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500 dark:text-gray-400">Nenhuma empresa vinculada</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Categoria Pai -->
                <?php if (!empty($categoria['categoria_pai'])): ?>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Categoria Pai
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <a href="<?= $this->baseUrl('/categorias/' . $categoria['categoria_pai']['id']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($categoria['categoria_pai']['codigo']) ?> - <?= htmlspecialchars($categoria['categoria_pai']['nome']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Data de Cadastro -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Data de Cadastro
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <?= date('d/m/Y \à\s H:i', strtotime($categoria['data_cadastro'])) ?>
                    </div>
                </div>
            </div>

            <!-- Categorias Filhas -->
            <?php if (!empty($categoria['filhas'])): ?>
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Subcategorias</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($categoria['filhas'] as $filha): ?>
                            <a href="<?= $this->baseUrl('/categorias/' . $filha['id']) ?>" 
                               class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-mono text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($filha['codigo']) ?></div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($filha['nome']) ?></div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer com Ações -->
        <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <form method="POST" action="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/delete') ?>" 
                      onsubmit="return confirm('⚠️ Tem certeza que deseja excluir esta categoria?\n\nEsta ação não pode ser desfeita!')">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Excluir Categoria
                    </button>
                </form>
                
                <a href="<?= $this->baseUrl('/categorias/' . $categoria['id'] . '/edit') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Categoria
                </a>
            </div>
        </div>
    </div>
</div>

