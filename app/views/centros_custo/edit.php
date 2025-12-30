<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= baseUrl('/centros-custo') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            ✏️ Editar Centro de Custo
        </h1>
    </div>

    <!-- Formulário -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <form method="POST" action="<?= baseUrl('/centros-custo/' . $centroCusto['id']) ?>" class="space-y-6">
            <?php
            $old = $session->get('old') ?? [];
            ?>

            <!-- Empresa -->
            <div>
                <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa *
                </label>
                <select id="empresa_id" 
                        name="empresa_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['empresa_id']) ? 'border-red-500' : '' ?>" 
                        required>
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= (($old['empresa_id'] ?? $centroCusto['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($session(\->get('errors') ?? [])['empresa_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['empresa_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Código e Nome -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Código *
                    </label>
                    <input type="text" 
                           id="codigo" 
                           name="codigo" 
                           value="<?= htmlspecialchars($old['codigo'] ?? $centroCusto['codigo']) ?>"
                           maxlength="20"
                           pattern="[A-Z0-9._-]+"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['codigo']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($session(\->get('errors') ?? [])['codigo'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['codigo'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome *
                    </label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="<?= htmlspecialchars($old['nome'] ?? $centroCusto['nome']) ?>"
                           minlength="3"
                           maxlength="255"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['nome']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($session(\->get('errors') ?? [])['nome'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['nome'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Centro Pai -->
            <div>
                <label for="centro_pai_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Centro Pai (Opcional)
                </label>
                <select id="centro_pai_id" 
                        name="centro_pai_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['centro_pai_id']) ? 'border-red-500' : '' ?>">
                    <option value="">Nenhum (Centro Principal)</option>
                    <?php foreach ($centrosPai as $centroPai): ?>
                        <option value="<?= $centroPai['id'] ?>" <?= (($old['centro_pai_id'] ?? $centroCusto['centro_pai_id']) == $centroPai['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($centroPai['codigo']) ?> - <?= htmlspecialchars($centroPai['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($session(\->get('errors') ?? [])['centro_pai_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['centro_pai_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($old['ativo'] ?? $centroCusto['ativo']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Centro de Custo Ativo
                </label>
            </div>

            <!-- Botões -->
            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Salvar Alterações
                </button>
                <a href="<?= baseUrl('/centros-custo') ?>" 
                   class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Limpa old e errors após exibição
$session->delete('old');
$session->delete('errors');
?>

