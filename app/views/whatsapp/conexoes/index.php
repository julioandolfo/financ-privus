<div class="max-w-6xl mx-auto">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-xl"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="mb-8 flex justify-between items-center">
        <div>
            <a href="<?= $this->baseUrl('/whatsapp') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Voltar ao dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Conexões Evolution API</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Cadastre suas instâncias da Evolution API para enviar mensagens.</p>
        </div>
        <a href="<?= $this->baseUrl('/whatsapp/conexoes/create') ?>" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg">+ Nova conexão</a>
    </div>

    <?php if (empty($conexoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Nenhuma conexão cadastrada.</p>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Nome</th>
                            <th class="px-6 py-3 text-left">Instance</th>
                            <th class="px-6 py-3 text-left">URL</th>
                            <th class="px-6 py-3 text-left">Empresa</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($conexoes as $c): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($c['nome']) ?>
                                    <?php if (!$c['ativo']): ?>
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 font-mono text-gray-700 dark:text-gray-300"><?= htmlspecialchars($c['instance_name']) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300 truncate max-w-xs"><?= htmlspecialchars($c['base_url']) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= $c['empresa_id'] ? '#' . $c['empresa_id'] : '— global —' ?></td>
                                <td class="px-6 py-3">
                                    <?php
                                    $cor = match ($c['status_conexao']) {
                                        'conectado' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'desconectado' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'erro' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                    ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full <?= $cor ?>"><?= htmlspecialchars($c['status_conexao']) ?></span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button onclick="testarConexao(<?= $c['id'] ?>, this)" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold mr-3">Testar</button>
                                    <button onclick="abrirQrCode(<?= $c['id'] ?>)" class="text-sm text-blue-600 hover:text-blue-700 font-semibold mr-3">QR Code</button>
                                    <a href="<?= $this->baseUrl("/whatsapp/conexoes/{$c['id']}/edit") ?>" class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-300 font-semibold mr-3">Editar</a>
                                    <form method="POST" action="<?= $this->baseUrl("/whatsapp/conexoes/{$c['id']}/delete") ?>" class="inline" onsubmit="return confirm('Remover esta conexão?');">
                                        <button class="text-sm text-red-600 hover:text-red-700 font-semibold">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal QR Code -->
<div id="qrModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Conectar instância</h3>
            <button onclick="fecharQrCode()" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div id="qrContent" class="text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">Carregando QR Code…</p>
        </div>
    </div>
</div>

<script>
function testarConexao(id, btn) {
    btn.disabled = true; const orig = btn.innerText; btn.innerText = '…';
    fetch('<?= $this->baseUrl("/whatsapp/conexoes/") ?>' + id + '/testar', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(j => { alert(j.ok ? `OK · estado=${j.estado || '?'}` : `Falha: ${j.error || 'erro'}`); location.reload(); })
        .finally(() => { btn.disabled = false; btn.innerText = orig; });
}
function abrirQrCode(id) {
    document.getElementById('qrModal').classList.remove('hidden');
    document.getElementById('qrContent').innerHTML = '<p class="text-sm text-gray-500">Carregando…</p>';
    fetch('<?= $this->baseUrl("/whatsapp/conexoes/") ?>' + id + '/qrcode')
        .then(r => r.json())
        .then(j => {
            const body = j.body || {};
            const base64 = body.base64 || body.qrcode?.base64 || (body.qrcode && body.qrcode.base64);
            const code = body.code || body.qrcode?.code;
            let html = '';
            if (base64) {
                const src = base64.startsWith('data:') ? base64 : 'data:image/png;base64,' + base64;
                html = `<img src="${src}" class="mx-auto max-w-full"/><p class="text-xs text-gray-500 mt-3">Escaneie no WhatsApp</p>`;
            } else if (code) {
                html = `<pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-auto">${code}</pre>`;
            } else if (j.error) {
                html = `<p class="text-red-600">${j.error}</p>`;
            } else {
                html = `<pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-auto">${JSON.stringify(body, null, 2)}</pre>`;
            }
            document.getElementById('qrContent').innerHTML = html;
        })
        .catch(e => { document.getElementById('qrContent').innerHTML = `<p class="text-red-600">${e.message}</p>`; });
}
function fecharQrCode() { document.getElementById('qrModal').classList.add('hidden'); }
</script>
