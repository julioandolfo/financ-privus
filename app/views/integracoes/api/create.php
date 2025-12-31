<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes/create') ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
            🔌 Integração API REST
        </h1>
    </div>

    <form method="POST" action="<?= $this->baseUrl('/integracoes/api') ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700 space-y-6" x-data="{ autenticacao: '<?= $this->session->get('old')['autenticacao'] ?? 'none' ?>' }">
        
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
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nome da API *</label>
            <input type="text" name="nome_api" value="<?= htmlspecialchars($this->session->get('old')['nome_api'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Base URL *</label>
                <input type="url" name="base_url" value="<?= htmlspecialchars($this->session->get('old')['base_url'] ?? '') ?>" placeholder="https://api.exemplo.com" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tipo API</label>
                <select name="tipo_api" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                    <option value="rest" selected>REST</option>
                    <option value="graphql">GraphQL</option>
                    <option value="soap">SOAP</option>
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
                <option value="oauth2">OAuth 2.0</option>
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

        <div x-show="autenticacao === 'oauth2'" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Client ID</label>
                    <input type="text" name="oauth2_client_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Client Secret</label>
                    <input type="password" name="oauth2_client_secret" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Token URL</label>
                <input type="url" name="oauth2_token_url" placeholder="https://api.exemplo.com/oauth/token" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Scope (opcional)</label>
                <input type="text" name="oauth2_scope" placeholder="read write" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="ativo" value="1" checked class="w-5 h-5 text-orange-600 rounded">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Integração Ativa</label>
        </div>

        <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="testarConexao()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl">
                🧪 Testar Conexão
            </button>
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold rounded-xl">
                Salvar API
            </button>
        </div>
    </form>
</div>

<script>
function testarConexao() {
    const form = document.querySelector('form');
    const data = {
        base_url: form.base_url.value,
        autenticacao: form.autenticacao.value,
        authData: {}
    };
    
    if (data.autenticacao === 'basic') {
        data.authData = { usuario: form.auth_usuario.value, senha: form.auth_senha.value };
    } else if (data.autenticacao === 'bearer') {
        data.authData = { token: form.auth_token.value };
    } else if (data.autenticacao === 'api_key') {
        data.authData = { header: form.api_key_header.value, value: form.api_key_value.value };
    }
    
    fetch('<?= $this->baseUrl('/integracoes/testar-api') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        alert(d.sucesso ? '✓ ' + d.mensagem : '✗ ' + d.mensagem);
    })
    .catch(() => alert('Erro ao testar conexão'));
}
</script>

<?php $this->session->delete('old'); $this->session->delete('errors'); ?>
