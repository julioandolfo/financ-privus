<div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= $this->baseUrl('/usuarios') ?>" 
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
            <div class="flex items-center justify-between">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                    👤 Detalhes do Usuário
                </h1>
                <a href="<?= $this->baseUrl('/usuarios/edit/' . $usuario['id']) ?>" 
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
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-blue-600 dark:text-blue-400 text-3xl font-bold shadow-lg">
                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($usuario['nome']) ?></h2>
                        <p class="text-blue-100"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    <?php if ($usuario['ativo']): ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-green-500 text-white">
                            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                            Ativo
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-red-500 text-white">
                            <span class="w-2 h-2 bg-white rounded-full"></span>
                            Inativo
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Corpo do Card -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            ID do Usuário
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            #<?= $usuario['id'] ?>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Status
                        </label>
                        <div class="text-lg font-semibold">
                            <?php if ($usuario['ativo']): ?>
                                <span class="text-green-600 dark:text-green-400">✓ Ativo</span>
                            <?php else: ?>
                                <span class="text-red-600 dark:text-red-400">✗ Inativo</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Empresa -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Empresa Principal
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?php if (!empty($usuario['empresa'])): ?>
                                <a href="<?= $this->baseUrl('/empresas/show/' . $usuario['empresa']['id']) ?>" 
                                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                    <?= htmlspecialchars($usuario['empresa']['nome_fantasia']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">Nenhuma empresa vinculada</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Data de Cadastro -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Data de Cadastro
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?= date('d/m/Y \à\s H:i', strtotime($usuario['data_cadastro'])) ?>
                        </div>
                    </div>

                    <!-- Último Acesso -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Último Acesso
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?php if ($usuario['ultimo_acesso']): ?>
                                <?= date('d/m/Y \à\s H:i', strtotime($usuario['ultimo_acesso'])) ?>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">Nunca acessou</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Email
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <a href="mailto:<?= htmlspecialchars($usuario['email']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($usuario['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer com Ações -->
            <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <form method="POST" action="<?= $this->baseUrl('/usuarios/delete/' . $usuario['id']) ?>" 
                          onsubmit="return confirm('⚠️ Tem certeza que deseja excluir este usuário?\n\nEsta ação não pode ser desfeita!')">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Excluir Usuário
                        </button>
                    </form>
                    
                    <a href="<?= $this->baseUrl('/usuarios/edit/' . $usuario['id']) ?>" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar Usuário
                    </a>
                </div>
            </div>
        </div>
</div>

