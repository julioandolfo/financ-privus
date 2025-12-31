<!-- Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Editar Perfil de Consolidação
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Atualize as informações do perfil
        </p>
    </div>
</div>

<!-- Formulário -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <form action="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id']) ?>" method="POST" class="p-8">
        <input type="hidden" name="_method" value="PUT">
        
        <!-- Nome do Perfil -->
        <div class="mb-6">
            <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Nome do Perfil <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="nome" 
                name="nome" 
                value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $perfil['nome']) ?>"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>" 
                placeholder="Ex: Matriz + Filial São Paulo"
                required
            >
            <?php if (isset($this->session->get('errors')['nome'])): ?>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    <?= $this->session->get('errors')['nome'] ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Descrição -->
        <div class="mb-6">
            <label for="descricao" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Descrição
            </label>
            <textarea 
                id="descricao" 
                name="descricao" 
                rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                placeholder="Descreva o propósito deste perfil de consolidação"
            ><?= htmlspecialchars($this->session->get('old')['descricao'] ?? $perfil['descricao']) ?></textarea>
        </div>

        <!-- Seleção de Empresas -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Empresas <span class="text-red-500">*</span>
            </label>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Selecione pelo menos 2 empresas para consolidação
            </p>
            
            <div class="space-y-2 max-h-80 overflow-y-auto bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                <?php 
                $oldEmpresas = $this->session->get('old')['empresas_ids'] ?? $perfil['empresas_ids'] ?? [];
                foreach ($empresas as $empresa): 
                ?>
                    <label class="flex items-start p-3 rounded-lg hover:bg-white dark:hover:bg-gray-700 cursor-pointer transition-colors group">
                        <input 
                            type="checkbox" 
                            name="empresas_ids[]" 
                            value="<?= $empresa['id'] ?>"
                            <?= in_array($empresa['id'], $oldEmpresas) ? 'checked' : '' ?>
                            class="mt-1 w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-600 dark:border-gray-500"
                        >
                        <div class="ml-3 flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($empresa['nome']) ?>
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                CNPJ: <?= htmlspecialchars($empresa['cnpj']) ?>
                            </span>
                        </div>
                    </label>
                <?php endforeach; ?>
                
                <?php if (empty($empresas)): ?>
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                        Nenhuma empresa cadastrada
                    </p>
                <?php endif; ?>
            </div>
            
            <?php if (isset($this->session->get('errors')['empresas_ids'])): ?>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    <?= $this->session->get('errors')['empresas_ids'] ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Status Ativo -->
        <div class="mb-6">
            <label class="flex items-center cursor-pointer">
                <input 
                    type="checkbox" 
                    name="ativo" 
                    value="1"
                    <?= ($this->session->get('old')['ativo'] ?? $perfil['ativo']) == '1' ? 'checked' : '' ?>
                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-600 dark:border-gray-500"
                >
                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Perfil Ativo
                </span>
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400 ml-8 mt-1">
                Perfis inativos não aparecem na lista de seleção rápida
            </p>
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button 
                type="submit" 
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Atualizar Perfil
            </button>
            
            <a 
                href="<?= $this->baseUrl('/perfis-consolidacao') ?>" 
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-colors"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
