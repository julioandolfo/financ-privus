<div class="max-w-7xl mx-auto" x-data="{ showInfoModal: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div class="flex items-start gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                        🏢 Gerenciar Centros de Custo
                    </h1>
                    <button @click="showInfoModal = true" 
                            class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Organize centros de custo em estrutura hierárquica
                </p>
            </div>
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

    <!-- Modal Explicativo sobre Centros de Custo -->
    <template x-teleport="body">
        <div x-show="showInfoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @click.away="showInfoModal = false"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-3xl w-full mx-auto relative z-10 overflow-hidden"
                 @click.stop>
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">O que é Centro de Custo?</h3>
                            <p class="text-sm opacity-90 mt-1">Entenda como organizar suas despesas e receitas</p>
                        </div>
                    </div>
                    <button @click="showInfoModal = false" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Definição -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-5 rounded-xl border border-blue-100 dark:border-blue-700">
                        <h4 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Definição
                        </h4>
                        <p class="text-blue-800 dark:text-blue-200 leading-relaxed">
                            <strong>Centro de Custo</strong> é uma unidade organizacional que agrupa despesas e receitas relacionadas a uma área específica da empresa. Ele permite analisar a rentabilidade e eficiência de cada departamento, projeto, filial ou atividade de forma independente.
                        </p>
                    </div>

                    <!-- Para que serve -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Para que serve?
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">📊</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Controle Financeiro</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Acompanhe gastos e receitas de cada setor</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">💰</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Análise de Rentabilidade</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Identifique áreas mais e menos lucrativas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">🎯</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Planejamento Estratégico</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Base para decisões de investimento</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">📈</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Responsabilização</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gestores respondem por seus resultados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exemplos Práticos -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            Exemplos Práticos
                        </h4>
                        <div class="bg-gray-100 dark:bg-gray-700 p-5 rounded-xl">
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <span class="text-blue-600 dark:text-blue-400 font-bold mr-3">•</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Por Departamento</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Comercial, Marketing, TI, RH, Financeiro, Produção</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="text-green-600 dark:text-green-400 font-bold mr-3">•</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Por Projeto</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Projeto A, Projeto B, Campanha X, Lançamento Y</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="text-purple-600 dark:text-purple-400 font-bold mr-3">•</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Por Localização</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Filial SP, Filial RJ, Loja Centro, Loja Shopping</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="text-orange-600 dark:text-orange-400 font-bold mr-3">•</span>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Por Atividade</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Vendas Online, Vendas Presenciais, Eventos, Treinamentos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hierarquia -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            Estrutura Hierárquica
                        </h4>
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-5 rounded-xl border border-amber-200 dark:border-amber-700">
                            <p class="text-gray-700 dark:text-gray-300 mb-3">Os centros de custo podem ser organizados em níveis hierárquicos:</p>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg font-mono text-sm">
                                <div class="text-blue-600 dark:text-blue-400">📁 Comercial (Centro Pai)</div>
                                <div class="ml-4 text-green-600 dark:text-green-400">├─ 🏢 Vendas Internas</div>
                                <div class="ml-4 text-green-600 dark:text-green-400">├─ 🚗 Vendas Externas</div>
                                <div class="ml-4 text-green-600 dark:text-green-400">└─ 📞 Televendas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Diferença vs Categoria -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Centro de Custo vs Categoria
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                                <p class="font-bold text-blue-900 dark:text-blue-100 mb-2">🏢 Centro de Custo</p>
                                <p class="text-sm text-blue-800 dark:text-blue-200"><strong>ONDE</strong> o dinheiro foi gasto/recebido</p>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">Ex: Departamento TI, Filial SP</p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                                <p class="font-bold text-purple-900 dark:text-purple-100 mb-2">🏷️ Categoria</p>
                                <p class="text-sm text-purple-800 dark:text-purple-200"><strong>PARA QUÊ</strong> o dinheiro foi gasto/recebido</p>
                                <p class="text-xs text-purple-700 dark:text-purple-300 mt-2">Ex: Salários, Aluguel, Vendas</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dica -->
                    <div class="bg-green-50 dark:bg-green-900/20 p-5 rounded-xl border-l-4 border-green-500">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-green-900 dark:text-green-100 mb-1">💡 Dica Importante</p>
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    Uma mesma despesa pode ter <strong>Categoria</strong> (O QUÊ) e <strong>Centro de Custo</strong> (ONDE). Exemplo: "Aluguel" (categoria) do "Departamento de TI" (centro de custo).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end">
                    <button @click="showInfoModal = false"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all font-semibold">
                        Entendi!
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

