<!-- Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            <?= htmlspecialchars($perfil['nome']) ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Detalhes do perfil de consolidação
        </p>
    </div>
    
    <div class="flex gap-3">
        <!-- Aplicar Perfil -->
        <form action="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/aplicar') ?>" method="POST">
            <button 
                type="submit"
                class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Aplicar Perfil
            </button>
        </form>
        
        <?php if ($perfil['usuario_id'] == $_SESSION['usuario_id'] || $perfil['usuario_id'] === null): ?>
        <!-- Editar -->
        <a 
            href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/edit') ?>" 
            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-colors"
        >
            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </a>
        <?php endif; ?>
        
        <!-- Voltar -->
        <a 
            href="<?= $this->baseUrl('/perfis-consolidacao') ?>" 
            class="px-5 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-colors"
        >
            Voltar
        </a>
    </div>
</div>

<!-- Informações do Perfil -->
<div class="grid grid-cols-1 gap-6 mb-6">
    <!-- Card de Informações -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Informações Gerais
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    Nome do Perfil
                </label>
                <p class="text-lg text-gray-900 dark:text-white font-medium">
                    <?= htmlspecialchars($perfil['nome']) ?>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    Status
                </label>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full <?= $perfil['ativo'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' ?>">
                    <?= $perfil['ativo'] ? 'Ativo' : 'Inativo' ?>
                </span>
            </div>
            
            <?php if (!empty($perfil['descricao'])): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    Descrição
                </label>
                <p class="text-gray-900 dark:text-white">
                    <?= htmlspecialchars($perfil['descricao']) ?>
                </p>
            </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    Tipo
                </label>
                <p class="text-gray-900 dark:text-white">
                    <?= $perfil['usuario_id'] === null ? 'Compartilhado (Sistema)' : 'Pessoal' ?>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                    Total de Empresas
                </label>
                <p class="text-lg text-gray-900 dark:text-white font-bold">
                    <?= count($empresas) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Empresas do Perfil -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Empresas Consolidadas
        </h2>
    </div>
    
    <div class="p-6">
        <?php if (!empty($empresas)): ?>
            <div class="space-y-4">
                <?php foreach ($empresas as $empresa): ?>
                    <div class="flex items-start p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                            <?= strtoupper(substr($empresa['nome'], 0, 1)) ?>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?= htmlspecialchars($empresa['nome']) ?>
                            </h3>
                            <div class="mt-1 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <?= htmlspecialchars($empresa['codigo']) ?>
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    CNPJ: <?= htmlspecialchars($empresa['cnpj']) ?>
                                </span>
                            </div>
                        </div>
                        <span class="flex-shrink-0 px-3 py-1 text-xs font-semibold rounded-full <?= $empresa['ativo'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' ?>">
                            <?= $empresa['ativo'] ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    Nenhuma empresa vinculada a este perfil
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
