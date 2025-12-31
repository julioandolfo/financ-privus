<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes/create') ?>" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
            💾 Integração Banco de Dados
        </h1>
    </div>

    <form method="POST" action="<?= $this->baseUrl('/integracoes/banco-dados') ?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700 space-y-6" id="formBD">
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
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tipo de Banco *</label>
            <select name="tipo_banco" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                <option value="">Selecione</option>
                <option value="mysql" <?= ($this->session->get('old')['tipo_banco'] ?? '') == 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                <option value="postgresql" <?= ($this->session->get('old')['tipo_banco'] ?? '') == 'postgresql' ? 'selected' : '' ?>>PostgreSQL</option>
                <option value="sqlserver" <?= ($this->session->get('old')['tipo_banco'] ?? '') == 'sqlserver' ? 'selected' : '' ?>>SQL Server</option>
                <option value="oracle" <?= ($this->session->get('old')['tipo_banco'] ?? '') == 'oracle' ? 'selected' : '' ?>>Oracle</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Host *</label>
                <input type="text" name="host" value="<?= htmlspecialchars($this->session->get('old')['host'] ?? '') ?>" placeholder="localhost" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Porta *</label>
                <input type="number" name="porta" value="<?= $this->session->get('old')['porta'] ?? '3306' ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nome do Banco *</label>
            <input type="text" name="database" value="<?= htmlspecialchars($this->session->get('old')['database'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Usuário *</label>
                <input type="text" name="usuario" value="<?= htmlspecialchars($this->session->get('old')['usuario'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Senha *</label>
                <input type="password" name="senha" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuração de Importação</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tabela de Origem *</label>
                    <input type="text" name="tabela_origem" value="<?= htmlspecialchars($this->session->get('old')['tabela_origem'] ?? '') ?>" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Importar para *</label>
                    <select name="tabela_destino" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                        <option value="">Selecione</option>
                        <option value="produtos">Produtos</option>
                        <option value="clientes">Clientes</option>
                        <option value="fornecedores">Fornecedores</option>
                        <option value="pedidos_vinculados">Pedidos</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Intervalo de Sincronização (minutos)</label>
                <input type="number" name="intervalo_sincronizacao" value="<?= $this->session->get('old')['intervalo_sincronizacao'] ?? '60' ?>" min="5" max="1440" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="ativo" value="1" checked class="w-5 h-5 text-amber-600 rounded">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Integração Ativa</label>
        </div>

        <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="testarConexao()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl">
                🧪 Testar Conexão
            </button>
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold rounded-xl">
                Salvar Integração
            </button>
        </div>
    </form>
</div>

<script>
function testarConexao() {
    const form = document.getElementById('formBD');
    const data = {
        tipo_banco: form.tipo_banco.value,
        host: form.host.value,
        porta: form.porta.value,
        database: form.database.value,
        usuario: form.usuario.value,
        senha: form.senha.value
    };
    
    if (!data.tipo_banco || !data.host || !data.porta || !data.database || !data.usuario || !data.senha) {
        alert('Preencha todos os campos de conexão primeiro');
        return;
    }
    
    fetch('<?= $this->baseUrl('/integracoes/testar-banco-dados') ?>', {
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
