<?php
/**
 * Página de login
 */
?>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-8 animate-fade-in">
    <!-- Logo e Título -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sistema Financeiro</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Entre com suas credenciais</p>
    </div>

    <!-- Mensagens de erro -->
    <?php if (isset($error)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mensagens de sucesso -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 dark:border-green-400 text-green-800 dark:text-green-200 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Formulário de Login -->
    <form method="POST" action="/login" class="space-y-6">
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email
            </label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="<?= htmlspecialchars($email ?? '') ?>" 
                   required
                   autofocus
                   class="w-full px-4 py-3 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white transition-all"
                   placeholder="seu@email.com">
        </div>

        <!-- Senha -->
        <div>
            <label for="senha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Senha
            </label>
            <input type="password" 
                   id="senha" 
                   name="senha" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white transition-all"
                   placeholder="••••••••">
        </div>

        <!-- Lembrar-me -->
        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600">
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Lembrar-me</span>
            </label>
            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                Esqueceu a senha?
            </a>
        </div>

        <!-- Botão de Login -->
        <button type="submit" 
                class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
            Entrar
        </button>
    </form>

    <!-- Rodapé -->
    <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>© <?= date('Y') ?> Sistema Financeiro Empresarial</p>
    </div>
</div>

