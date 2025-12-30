<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            👤 Minha Conta
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Gerencie suas informações pessoais e preferências</p>
    </div>

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Formulário -->
        <form method="POST" action="<?= $this->baseUrl('/minha-conta') ?>" enctype="multipart/form-data" class="p-8 space-y-6">
            <!-- Avatar -->
            <div class="flex flex-col items-center mb-8">
                <div class="relative">
                    <?php if (!empty($usuario['avatar'])): ?>
                        <img src="<?= htmlspecialchars($usuario['avatar']) ?>" 
                             alt="Avatar" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-blue-500 dark:border-blue-400 shadow-lg">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-blue-500 dark:border-blue-400 shadow-lg">
                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <label for="avatar" class="absolute bottom-0 right-0 w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-all transform hover:scale-110">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input type="file" 
                               id="avatar" 
                               name="avatar" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               class="hidden"
                               onchange="previewAvatar(this)">
                    </label>
                </div>
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Clique no ícone para alterar sua foto</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Formatos aceitos: JPG, PNG, GIF, WEBP (máx. 2MB)</p>
            </div>

            <!-- Nome -->
            <div>
                <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nome Completo *
                </label>
                <input type="text" 
                       id="nome" 
                       name="nome" 
                       value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $usuario['nome']) ?>"
                       data-mask="letters"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>" 
                       required>
                <?php if (isset($this->session->get('errors')['nome'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
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
                       value="<?= htmlspecialchars($this->session->get('old')['email'] ?? $usuario['email']) ?>"
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['email']) ? 'border-red-500' : '' ?>" 
                       required>
                <?php if (isset($this->session->get('errors')['email'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['email'] ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Digite um email válido</p>
                <?php endif; ?>
            </div>

            <!-- Senha Atual (para alterar senha) -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Alterar Senha</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Deixe em branco se não desejar alterar a senha</p>
                
                <div class="space-y-4">
                    <!-- Nova Senha -->
                    <div>
                        <label for="senha" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Nova Senha
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="senha" 
                                   name="senha" 
                                   minlength="8"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['senha']) ? 'border-red-500' : '' ?>">
                            <button type="button" 
                                    onclick="togglePassword('senha')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg id="eye-senha" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-off-senha" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($this->session->get('errors')['senha'])): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['senha'] ?></p>
                        <?php else: ?>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mínimo 8 caracteres</p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label for="senha_confirm" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Confirmar Nova Senha
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="senha_confirm" 
                                   name="senha_confirm" 
                                   minlength="8"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <button type="button" 
                                    onclick="togglePassword('senha_confirm')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg id="eye-senha_confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-off-senha_confirm" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p id="senha-match" class="mt-1 text-xs hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Informações Adicionais</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Empresa -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Empresa Principal
                        </label>
                        <div class="px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100">
                            <?php if (!empty($usuario['empresa'])): ?>
                                <?= htmlspecialchars($usuario['empresa']['nome_fantasia']) ?>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">Nenhuma empresa vinculada</span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Entre em contato com o administrador para alterar</p>
                    </div>

                    <!-- Data de Cadastro -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Data de Cadastro
                        </label>
                        <div class="px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100">
                            <?= date('d/m/Y \à\s H:i', strtotime($usuario['data_cadastro'])) ?>
                        </div>
                    </div>

                    <!-- Último Acesso -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Último Acesso
                        </label>
                        <div class="px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100">
                            <?php if ($usuario['ultimo_acesso']): ?>
                                <?= date('d/m/Y \à\s H:i', strtotime($usuario['ultimo_acesso'])) ?>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">Nunca acessou</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                            <?php if ($usuario['ativo']): ?>
                                <span class="inline-flex items-center gap-2 text-green-600 dark:text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                    Ativo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-2 text-red-600 dark:text-red-400">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    Inativo
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= $this->baseUrl('/') ?>" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById('eye-' + fieldId);
    const eyeOff = document.getElementById('eye-off-' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.add('hidden');
        eyeOff.classList.remove('hidden');
    } else {
        field.type = 'password';
        eye.classList.remove('hidden');
        eyeOff.classList.add('hidden');
    }
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.querySelector('.relative img');
            if (img) {
                img.src = e.target.result;
            } else {
                const div = document.querySelector('.relative div');
                if (div) {
                    div.innerHTML = '<img src="' + e.target.result + '" class="w-32 h-32 rounded-full object-cover border-4 border-blue-500 dark:border-blue-400 shadow-lg">';
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Validação de senha em tempo real
document.getElementById('senha_confirm')?.addEventListener('input', function() {
    const senha = document.getElementById('senha').value;
    const senhaConfirm = this.value;
    const matchMsg = document.getElementById('senha-match');
    
    if (senhaConfirm.length > 0) {
        if (senha === senhaConfirm) {
            matchMsg.textContent = '✓ Senhas coincidem';
            matchMsg.className = 'mt-1 text-xs text-green-600 dark:text-green-400';
            matchMsg.classList.remove('hidden');
        } else {
            matchMsg.textContent = '✗ Senhas não coincidem';
            matchMsg.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
            matchMsg.classList.remove('hidden');
        }
    } else {
        matchMsg.classList.add('hidden');
    }
});

// Validação antes de enviar
document.querySelector('form')?.addEventListener('submit', function(e) {
    const senha = document.getElementById('senha').value;
    const senhaConfirm = document.getElementById('senha_confirm').value;
    
    if (senha && senha !== senhaConfirm) {
        e.preventDefault();
        alert('As senhas não coincidem!');
        return false;
    }
});
</script>

<?php 
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>

