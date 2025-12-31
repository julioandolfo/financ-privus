<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes/create') ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
            🔗 Integração Webhook
        </h1>
    </div>

    <form method="POST" action="<?= $this->baseUrl('/integracoes/webhook') ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700 space-y-6" x-data="{ autenticacao: '<?= $this->session->get('old')['autenticacao'] ?? 'none' ?>' }">
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Empresa *</label>
            <select name="empresa_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                <option value="">Selecione</option>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($this->session->get('old')['empresa_id'] ?? $empresaId) == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nome_fantasia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nome da Integração *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nome do Webhook *</label>
            <input type="text" name="nome_webhook" value="<?= htmlspecialchars($this->session->get('old')['nome_webhook'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">URL do Webhook *</label>
                <input type="url" name="url_webhook" value="<?= htmlspecialchars($this->session->get('old')['url_webhook'] ?? '') ?>" placeholder="https://api.exemplo.com/webhook" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Método HTTP *</label>
                <select name="metodo" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                    <option value="POST" selected>POST</option>
                    <option value="GET">GET</option>
                    <option value="PUT">PUT</option>
                    <option value="PATCH">PATCH</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Autenticação</label>
            <select name="autenticacao" x-model="autenticacao" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                <option value="none">Sem Autenticação</option>
                <option value="basic">Basic Auth</option>
                <option value="bearer">Bearer Token</option>
                <option value="api_key">API Key</option>
            </select>
        </div>

        <div x-show="autenticacao === 'basic'" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Usuário</label>
                <input type="text" name="auth_usuario" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Senha</label>
                <input type="password" name="auth_senha" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div x-show="autenticacao === 'bearer'">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Bearer Token</label>
            <input type="text" name="auth_token" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 font-mono text-sm">
        </div>

        <div x-show="autenticacao === 'api_key'" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Header Name</label>
                <input type="text" name="api_key_header" placeholder="X-API-Key" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">API Key Value</label>
                <input type="text" name="api_key_value" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Eventos que disparam o Webhook</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input type="checkbox" name="eventos_disparo[]" value="produto_criado" class="w-4 h-4 text-green-600 rounded">
                    <span class="text-sm">Produto Criado</span>
                </label>
                <label class="flex items-center gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input type="checkbox" name="eventos_disparo[]" value="pedido_criado" class="w-4 h-4 text-green-600 rounded">
                    <span class="text-sm">Pedido Criado</span>
                </label>
                <label class="flex items-center gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input type="checkbox" name="eventos_disparo[]" value="cliente_criado" class="w-4 h-4 text-green-600 rounded">
                    <span class="text-sm">Cliente Criado</span>
                </label>
                <label class="flex items-center gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input type="checkbox" name="eventos_disparo[]" value="pagamento_recebido" class="w-4 h-4 text-green-600 rounded">
                    <span class="text-sm">Pagamento Recebido</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="ativo" value="1" checked class="w-5 h-5 text-green-600 rounded">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Webhook Ativo</label>
        </div>

        <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl">
                Salvar Webhook
            </button>
            <a href="<?= $this->baseUrl('/integracoes/create') ?>" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>
<?php $this->session->delete('old'); $this->session->delete('errors'); ?>
