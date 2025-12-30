<?php
/**
 * Formulário de edição de empresa
 */
?>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 animate-fade-in max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Empresa</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações da empresa</p>
    </div>

    <!-- Mensagens de erro -->
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium">Erros encontrados:</p>
                    <ul class="list-disc list-inside mt-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mensagem de erro geral -->
    <?php if (isset($error)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 p-4 rounded-lg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/empresas/<?= $empresa['id'] ?>" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Código <span class="text-red-500">*</span>
                </label>
                <input type="text" id="codigo" name="codigo" 
                       value="<?= htmlspecialchars($empresa['codigo'] ?? '') ?>" 
                       required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
            </div>

            <!-- CNPJ -->
            <div>
                <label for="cnpj" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    CNPJ
                </label>
                <input type="text" id="cnpj" name="cnpj" 
                       value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>" 
                       placeholder="00.000.000/0000-00"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
            </div>
        </div>

        <!-- Razão Social -->
        <div>
            <label for="razao_social" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Razão Social <span class="text-red-500">*</span>
            </label>
            <input type="text" id="razao_social" name="razao_social" 
                   value="<?= htmlspecialchars($empresa['razao_social'] ?? '') ?>" 
                   required
                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
        </div>

        <!-- Nome Fantasia -->
        <div>
            <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Nome Fantasia <span class="text-red-500">*</span>
            </label>
            <input type="text" id="nome_fantasia" name="nome_fantasia" 
                   value="<?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?>" 
                   required
                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
        </div>

        <!-- Grupo Empresarial -->
        <div>
            <label for="grupo_empresarial_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Grupo Empresarial
            </label>
            <input type="number" id="grupo_empresarial_id" name="grupo_empresarial_id" 
                   value="<?= htmlspecialchars($empresa['grupo_empresarial_id'] ?? '') ?>" 
                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
        </div>

        <!-- Status -->
        <div>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="ativo" value="1" 
                       <?= ($empresa['ativo'] ?? 1) ? 'checked' : '' ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Empresa ativa</span>
            </label>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-slate-700">
            <a href="/empresas" class="px-6 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                Atualizar Empresa
            </button>
        </div>
    </form>
</div>

