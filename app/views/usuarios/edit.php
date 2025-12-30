<div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= baseUrl('/usuarios') ?>" 
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                ✏️ Editar Usuário
            </h1>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <form method="POST" action="<?= baseUrl('/usuarios/' . $usuario['id']) ?>" class="space-y-6">
                <!-- Nome -->
                <div>
                    <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome Completo *
                    </label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="<?= htmlspecialchars($session->get('old')['nome'] ?? $usuario['nome']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['nome']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($session(\->get('errors') ?? [])['nome'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['nome'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Email *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($session->get('old')['email'] ?? $usuario['email']) ?>"
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['email']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($session(\->get('errors') ?? [])['email'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['email'] ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Digite um email válido</p>
                    <?php endif; ?>
                </div>

                <!-- Senha -->
                <div>
                    <label for="senha" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nova Senha (deixe em branco para não alterar)
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="senha" 
                               name="senha" 
                               minlength="6"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($session(\->get('errors') ?? [])['senha']) ? 'border-red-500' : '' ?>">
                        <button type="button" 
                                onclick="togglePassword('senha')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                                title="Mostrar/Ocultar senha">
                            <svg id="icon-senha-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="icon-senha-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.487 5.197m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($session(\->get('errors') ?? [])['senha'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $session(\->get('errors') ?? [])['senha'] ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Mínimo de 6 caracteres se preencher</p>
                    <?php endif; ?>
                </div>

                <!-- Empresa -->
                <div>
                    <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Empresa Principal (Opcional)
                    </label>
                    <select id="empresa_id" 
                            name="empresa_id" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= (($session->get('old')['empresa_id'] ?? $usuario['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           <?= ($session->get('old')['ativo'] ?? $usuario['ativo']) ? 'checked' : '' ?>
                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Usuário Ativo
                    </label>
                </div>

                <!-- Permissões -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Permissões de Acesso</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Selecione quais ações o usuário pode realizar em cada módulo do sistema.
                    </p>
                    
                    <div class="space-y-4">
                        <?php foreach ($modulos as $moduloKey => $moduloNome): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($moduloNome) ?></h4>
                                    <button type="button" 
                                            onclick="toggleAllPermissions('<?= $moduloKey ?>')"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        Selecionar Todas
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <?php foreach ($acoes as $acaoKey => $acaoNome): ?>
                                        <?php 
                                        $permissaoKey = "{$moduloKey}_{$acaoKey}";
                                        $checked = isset($permissoes[$moduloKey]['acoes'][$acaoKey]['permitido']) && $permissoes[$moduloKey]['acoes'][$acaoKey]['permitido'];
                                        ?>
                                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                            <input type="checkbox" 
                                                   name="permissoes[]" 
                                                   value="<?= $permissaoKey ?>"
                                                   <?= $checked ? 'checked' : '' ?>
                                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                                   data-modulo="<?= $moduloKey ?>">
                                            <span class="text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($acaoNome) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Salvar Alterações
                    </button>
                    <a href="<?= baseUrl('/usuarios') ?>" 
                       class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(`icon-${fieldId}-eye`);
    const eyeOffIcon = document.getElementById(`icon-${fieldId}-eye-off`);
    
    if (field && eyeIcon && eyeOffIcon) {
        if (field.type === 'password') {
            field.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            field.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
}

// Toggle todas as permissões de um módulo
function toggleAllPermissions(modulo) {
    const checkboxes = document.querySelectorAll(`input[data-modulo="${modulo}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
}
</script>

<?php 
// Limpa old e errors após exibição
$session->delete('old');
$session->delete('errors');
?>

