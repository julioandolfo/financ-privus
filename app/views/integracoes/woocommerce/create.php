<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes/create') ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
            🛒 Integração WooCommerce
        </h1>
    </div>

    <form method="POST" action="<?= $this->baseUrl('/integracoes/woocommerce') ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700 space-y-6" id="formWooCommerce">
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Empresa *</label>
            <select name="empresa_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 <?= isset($this->session->get('errors')['empresa_id']) ? 'border-red-500' : '' ?>">
                <option value="">Selecione</option>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($this->session->get('old')['empresa_id'] ?? $empresaId) == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nome_fantasia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= $this->session->get('errors')['empresa_id'] ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nome da Integração *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">URL do Site WooCommerce *</label>
            <input type="url" name="url_site" value="<?= htmlspecialchars($this->session->get('old')['url_site'] ?? '') ?>" placeholder="https://seuloja.com.br" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 <?= isset($this->session->get('errors')['url_site']) ? 'border-red-500' : '' ?>">
            <p class="mt-1 text-xs text-gray-500">URL completa da sua loja WooCommerce</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Consumer Key *</label>
            <input type="text" name="consumer_key" value="<?= htmlspecialchars($this->session->get('old')['consumer_key'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 font-mono text-sm <?= isset($this->session->get('errors')['consumer_key']) ? 'border-red-500' : '' ?>">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Consumer Secret *</label>
            <input type="password" name="consumer_secret" value="<?= htmlspecialchars($this->session->get('old')['consumer_secret'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 font-mono text-sm <?= isset($this->session->get('errors')['consumer_secret']) ? 'border-red-500' : '' ?>">
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações de Sincronização</h3>
            
            <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" name="sincronizar_produtos" value="1" checked class="w-5 h-5 text-purple-600 rounded">
                <label class="text-sm text-gray-700 dark:text-gray-300">Sincronizar Produtos</label>
            </div>
            
            <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" name="sincronizar_pedidos" value="1" checked class="w-5 h-5 text-purple-600 rounded">
                <label class="text-sm text-gray-700 dark:text-gray-300">Sincronizar Pedidos</label>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Intervalo de Sincronização (minutos)</label>
                <input type="number" name="intervalo_sincronizacao" value="<?= $this->session->get('old')['intervalo_sincronizacao'] ?? '60' ?>" min="5" max="1440" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="ativo" value="1" checked class="w-5 h-5 text-purple-600 rounded">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Integração Ativa</label>
        </div>

        <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="testarConexao()" class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl">
                🧪 Testar Conexão
            </button>
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl">
                Salvar Integração
            </button>
        </div>
    </form>
</div>

<script>
function testarConexao() {
    const form = document.getElementById('formWooCommerce');
    const data = {
        url_site: form.url_site.value,
        consumer_key: form.consumer_key.value,
        consumer_secret: form.consumer_secret.value
    };
    
    if (!data.url_site || !data.consumer_key || !data.consumer_secret) {
        alert('Preencha todos os campos obrigatórios primeiro');
        return;
    }
    
    fetch('<?= $this->baseUrl('/integracoes/testar-woocommerce') ?>', {
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

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
