<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/api-tokens') ?>" 
           class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">Editar Token de API</h1>
        <p class="text-gray-600 dark:text-gray-400">Atualize as configurações do token</p>
    </div>

    <!-- Alerta sobre o token -->
    <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-500 rounded-2xl p-4">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 class="font-bold text-amber-900 dark:text-amber-100">Por Segurança</h3>
                <p class="text-sm text-amber-800 dark:text-amber-200">O token completo não é exibido. Para obter um novo token, use a opção "Regenerar Token".</p>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <form method="POST" action="<?= $this->baseUrl("/api-tokens/{$token['id']}") ?>" 
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        
        <!-- Nome -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Nome do Token <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nome" required
                   value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $token['nome']) ?>"
                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>">
            <?php if (isset($this->session->get('errors')['nome'])): ?>
                <p class="mt-1 text-sm text-red-500"><?= $this->session->get('errors')['nome'] ?></p>
            <?php endif; ?>
        </div>

        <!-- Rate Limit -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Rate Limit (requisições/hora) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="rate_limit" min="1" required
                   value="<?= htmlspecialchars($this->session->get('old')['rate_limit'] ?? $token['rate_limit']) ?>"
                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($this->session->get('errors')['rate_limit']) ? 'border-red-500' : '' ?>">
            <?php if (isset($this->session->get('errors')['rate_limit'])): ?>
                <p class="mt-1 text-sm text-red-500"><?= $this->session->get('errors')['rate_limit'] ?></p>
            <?php endif; ?>
        </div>

        <!-- Data de Expiração -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Data de Expiração (Opcional)
            </label>
            <?php 
            $expiraEm = $this->session->get('old')['expira_em'] ?? $token['expira_em'];
            if ($expiraEm) {
                $expiraEm = date('Y-m-d\TH:i', strtotime($expiraEm));
            }
            ?>
            <input type="datetime-local" name="expira_em"
                   value="<?= htmlspecialchars($expiraEm ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Se deixar vazio, o token nunca expira</p>
        </div>

        <!-- IP Whitelist -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                IP Whitelist (Opcional)
            </label>
            <?php 
            $ipWhitelist = $this->session->get('old')['ip_whitelist_text'] ?? '';
            if (empty($ipWhitelist) && !empty($token['ip_whitelist'])) {
                $ipWhitelist = implode("\n", $token['ip_whitelist']);
            }
            ?>
            <textarea name="ip_whitelist_text" rows="4" placeholder="192.168.1.1&#10;10.0.0.1&#10;Um IP por linha..."
                      class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($ipWhitelist) ?></textarea>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Um IP por linha. Se deixar vazio, qualquer IP pode usar o token</p>
        </div>

        <!-- Permissões -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                Permissões (Opcional)
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php 
                $modules = [
                    'contas_pagar' => 'Contas a Pagar',
                    'contas_receber' => 'Contas a Receber',
                    'produtos' => 'Produtos',
                    'clientes' => 'Clientes',
                    'fornecedores' => 'Fornecedores',
                    'pedidos' => 'Pedidos',
                    'movimentacoes' => 'Movimentações'
                ];
                
                $actions = ['read', 'create', 'update', 'delete'];
                $actionLabels = ['read' => 'Ler', 'create' => 'Criar', 'update' => 'Atualizar', 'delete' => 'Excluir'];
                
                foreach ($modules as $module => $label): 
                ?>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-3"><?= $label ?></h4>
                    <div class="space-y-2">
                        <?php foreach ($actions as $action): 
                            $checked = isset($token['permissoes'][$module]) && in_array($action, $token['permissoes'][$module]);
                        ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="permissoes[<?= $module ?>][]" value="<?= $action ?>"
                                   <?= $checked ? 'checked' : '' ?>
                                   class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"><?= $actionLabels[$action] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Se não selecionar nada, o token terá acesso total</p>
        </div>

        <!-- Ativo -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="ativo" value="1" <?= $token['ativo'] ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500">
                <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Token Ativo</span>
            </label>
        </div>

        <!-- Botões -->
        <div class="flex justify-between">
            <div class="space-x-4">
                <a href="<?= $this->baseUrl('/api-tokens') ?>" 
                   class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors inline-block">
                    Cancelar
                </a>
            </div>
            <div class="space-x-4">
                <button type="button" onclick="regenerarToken()"
                        class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-colors">
                    Regenerar Token
                </button>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Salvar Alterações
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function regenerarToken() {
    if (confirm('Tem certeza que deseja regenerar o token? O token atual será invalidado e um novo será gerado.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $this->baseUrl("/api-tokens/{$token['id']}/regenerate") ?>';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
