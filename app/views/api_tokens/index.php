<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">API Tokens</h1>
            <p class="text-gray-600 dark:text-gray-400">Gerencie tokens de acesso à API REST</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= $this->baseUrl('/api/docs') ?>" 
               class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                📖 Ver Documentação
            </a>
            <a href="<?= $this->baseUrl('/api-tokens/create') ?>" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Token
            </a>
        </div>
    </div>

    <!-- Novo Token Alert -->
    <?php if (isset($_SESSION['new_token'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-2xl p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-bold text-green-900 dark:text-green-100 mb-2">Token Criado com Sucesso!</h3>
                <p class="text-green-800 dark:text-green-200 mb-3">Copie o token abaixo. Por segurança, ele não será exibido novamente.</p>
                <div class="flex items-center space-x-2">
                    <input type="text" readonly value="<?= htmlspecialchars($_SESSION['new_token']) ?>" 
                           id="newToken"
                           class="flex-1 px-4 py-3 bg-white dark:bg-gray-800 border-2 border-green-500 rounded-xl font-mono text-sm">
                    <button onclick="copyToken()" 
                            class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php 
        unset($_SESSION['new_token']);
    endif; 
    ?>

    <!-- Cards de Tokens -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($tokens)): ?>
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Nenhum Token Criado</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Crie seu primeiro token para acessar a API REST.</p>
                <a href="<?= $this->baseUrl('/api-tokens/create') ?>" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all">
                    Criar Token
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($tokens as $token): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-2xl transition-all">
                <!-- Status Badge -->
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($token['nome']) ?></h3>
                    <?php if ($token['ativo']): ?>
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-bold rounded-full">
                        Ativo
                    </span>
                    <?php else: ?>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400 text-xs font-bold rounded-full">
                        Inativo
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="space-y-2 mb-4">
                    <?php if ($token['empresa_nome']): ?>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <?= htmlspecialchars($token['empresa_nome']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <?= htmlspecialchars($token['usuario_nome']) ?>
                    </div>

                    <?php if ($token['ultimo_uso']): ?>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Último uso: <?= date('d/m/Y H:i', strtotime($token['ultimo_uso'])) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Stats -->
                <?php if (!empty($token['stats'])): ?>
                <div class="grid grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            <?= number_format($token['stats']['total_requests'] ?? 0) ?>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Requisições</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            <?= number_format($token['stats']['success_requests'] ?? 0) ?>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Sucesso</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Ações -->
                <div class="flex space-x-2">
                    <a href="<?= $this->baseUrl("/api-tokens/{$token['id']}") ?>" 
                       class="flex-1 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-sm font-semibold rounded-xl hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-center">
                        Ver Detalhes
                    </a>
                    <a href="<?= $this->baseUrl("/api-tokens/{$token['id']}/edit") ?>" 
                       class="px-4 py-2 bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400 text-sm font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-900/50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function copyToken() {
    const token = document.getElementById('newToken');
    token.select();
    document.execCommand('copy');
    alert('Token copiado para a área de transferência!');
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
