<?php
$isEdit = !empty($conexao);
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$val = function ($field, $default = '') use ($conexao, $old) {
    if (!empty($old[$field])) return $old[$field];
    if ($conexao && array_key_exists($field, $conexao)) return $conexao[$field];
    return $default;
};
$action = $isEdit ? $this->baseUrl("/whatsapp/conexoes/{$conexao['id']}") : $this->baseUrl('/whatsapp/conexoes');
?>
<?php
$providerAtual = $val('provider', 'evolution');
?>
<div class="max-w-3xl mx-auto" x-data="{ provider: '<?= htmlspecialchars($providerAtual) ?>' }">
    <a href="<?= $this->baseUrl('/whatsapp/conexoes') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Voltar</a>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2 mb-6">
        <?= $isEdit ? 'Editar Conexão' : 'Nova Conexão WhatsApp' ?>
    </h1>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $action ?>" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Provedor *</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach ($providers as $key => $label): ?>
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:border-emerald-500 transition"
                           :class="provider === '<?= $key ?>' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10' : ''">
                        <input type="radio" name="provider" value="<?= $key ?>" x-model="provider" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($label) ?></p>
                            <?php if ($key === 'evolution'): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Self-hosted. Usa <code>apikey</code> + nome da instância.</p>
                            <?php elseif ($key === 'quepasa'): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">QuePasa v4. Autentica via <code>X-QUEPASA-USER</code> (usuário do bot).</p>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nome da conexão *</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($val('nome')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ex: WhatsApp Empresa Principal">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    <span x-show="provider === 'evolution'">URL Evolution API *</span>
                    <span x-show="provider === 'quepasa'">URL QuePasa API *</span>
                </label>
                <input type="url" name="base_url" required value="<?= htmlspecialchars($val('base_url')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg"
                    :placeholder="provider === 'quepasa' ? 'https://whats.autoprivus.com.br' : 'https://evo.exemplo.com'">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    <span x-show="provider === 'evolution'">Instance Name *</span>
                    <span x-show="provider === 'quepasa'">WID / Token do bot <span class="text-gray-400 font-normal">(opcional)</span></span>
                </label>
                <input type="text" name="instance_name" :required="provider === 'evolution'" value="<?= htmlspecialchars($val('instance_name')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono"
                    :placeholder="provider === 'quepasa' ? '5511999999999 (WID do bot)' : 'financeiro-empresa-1'">
                <p x-show="provider === 'quepasa'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Use quando seu usuário tem mais de um bot. O WID identifica qual bot enviar a mensagem (resolve <em>"whatsapp server was not found"</em>).
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                <span x-show="provider === 'evolution'">API Key <?= $isEdit ? '(deixe em branco para manter)' : '*' ?></span>
                <span x-show="provider === 'quepasa'">Usuário (X-QUEPASA-USER) <?= $isEdit ? '(deixe em branco para manter)' : '*' ?></span>
            </label>
            <input type="password" name="api_key" <?= $isEdit ? '' : 'required' ?> value="<?= htmlspecialchars($val('api_key_decrypted')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono" autocomplete="new-password">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span x-show="provider === 'evolution'">Armazenado criptografado (AES-256-CBC).</span>
                <span x-show="provider === 'quepasa'">Identificador do bot enviado no header <code>X-QUEPASA-USER</code>. Armazenado criptografado.</span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                <select name="empresa_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                    <option value="">— Global (todas as empresas) —</option>
                    <?php foreach ($empresas as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= ($val('empresa_id') == $e['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nome_fantasia'] ?? $e['razao_social'] ?? ('Empresa #' . $e['id'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Número remetente (opcional)</label>
                <input type="text" name="numero_remetente" value="<?= htmlspecialchars($val('numero_remetente')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg" placeholder="5511999999999">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Webhook URL (opcional)</label>
            <input type="url" name="webhook_url" value="<?= htmlspecialchars($val('webhook_url')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg" placeholder="https://seu-sistema.com/webhook/evolution">
        </div>

        <label class="flex items-center gap-3">
            <input type="checkbox" name="ativo" value="1" <?= $val('ativo', 1) ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Conexão ativa</span>
        </label>

        <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg">
                <?= $isEdit ? 'Salvar alterações' : 'Cadastrar conexão' ?>
            </button>
            <a href="<?= $this->baseUrl('/whatsapp/conexoes') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl">Cancelar</a>
        </div>
    </form>

    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-sm text-blue-800 dark:text-blue-200">
        <p class="font-semibold mb-1">💡 Dicas por provedor</p>
        <p x-show="provider === 'evolution'" class="text-xs">A Evolution requer <code>base_url</code> + <code>instance_name</code> + API key. O <em>QR Code</em> está em <code>/instance/connect/{instance}</code>.</p>
        <p x-show="provider === 'quepasa'" class="text-xs">QuePasa: a URL padrão da nossa instância é <code>https://whats.autoprivus.com.br</code>. Autenticação só por <code>X-QUEPASA-USER</code> (usuário do bot) — não há Bearer token nem instance. QR em <code>/scan</code>.</p>
    </div>
</div>
