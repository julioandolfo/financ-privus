<div class="max-w-7xl mx-auto">
    <!-- Flash messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-xl">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-400 bg-clip-text text-transparent">
                💬 WhatsApp · Relatórios Automáticos
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Envie relatórios financeiros via WhatsApp (Evolution API) com regras agendadas
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= $this->baseUrl('/whatsapp/conexoes') ?>"
               class="px-5 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-emerald-500 text-gray-700 dark:text-gray-200 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                ⚙️ Conexões
            </a>
            <a href="<?= $this->baseUrl('/whatsapp/regras/create') ?>"
               class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                ➕ Nova Regra
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Conexões</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1"><?= count($conexoes) ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <?= count(array_filter($conexoes, fn($c) => !empty($c['ativo']))) ?> ativa(s)
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Regras</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1"><?= (int)($estatisticasRegras['total'] ?? 0) ?></p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                <?= (int)($estatisticasRegras['ativas'] ?? 0) ?> ativa(s)
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Enviados (7d)</p>
            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                <?= (int)($estatisticasEnvios['enviados'] ?? 0) ?>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                de <?= (int)($estatisticasEnvios['total'] ?? 0) ?> tentativas
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Falhas (7d)</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">
                <?= (int)($estatisticasEnvios['falhas'] ?? 0) ?>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <?= (int)($estatisticasEnvios['aguardando'] ?? 0) ?> aguardando
            </p>
        </div>
    </div>

    <!-- Conexões Evolution -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Conexões Evolution API</h2>
            <a href="<?= $this->baseUrl('/whatsapp/conexoes/create') ?>"
               class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold">+ Nova conexão</a>
        </div>
        <?php if (empty($conexoes)): ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Nenhuma conexão cadastrada.
                <a href="<?= $this->baseUrl('/whatsapp/conexoes/create') ?>" class="text-emerald-600 hover:underline">Cadastrar agora</a>.
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($conexoes as $c): ?>
                    <?php
                    $statusCor = match ($c['status_conexao']) {
                        'conectado' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                        'desconectado' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                        'erro' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    };
                    ?>
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($c['nome']) ?></span>
                                <?php $providerLabel = ($c['provider'] ?? 'evolution') === 'quepasa' ? 'QuePasa' : 'Evolution'; ?>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"><?= $providerLabel ?></span>
                                <span class="px-2 py-0.5 text-xs rounded-full <?= $statusCor ?>"><?= htmlspecialchars($c['status_conexao']) ?></span>
                                <?php if (!$c['ativo']): ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inativo</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?php if (($c['provider'] ?? 'evolution') === 'evolution' && !empty($c['instance_name'])): ?>
                                    <span class="font-mono"><?= htmlspecialchars($c['instance_name']) ?></span> ·
                                <?php endif; ?>
                                <?= htmlspecialchars($c['base_url']) ?>
                                <?php if ($c['ultima_verificacao']): ?>
                                    · checado <?= date('d/m H:i', strtotime($c['ultima_verificacao'])) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button"
                                onclick="testarConexao(<?= $c['id'] ?>, this)"
                                class="px-3 py-1.5 text-sm bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50 rounded-lg font-semibold">Testar</button>
                            <a href="<?= $this->baseUrl("/whatsapp/conexoes/{$c['id']}/edit") ?>"
                               class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-semibold">Editar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Regras -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Regras de Relatório</h2>
            <a href="<?= $this->baseUrl('/whatsapp/regras') ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold">Ver todas →</a>
        </div>
        <?php if (empty($regras)): ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Nenhuma regra criada ainda.
                <a href="<?= $this->baseUrl('/whatsapp/regras/create') ?>" class="text-emerald-600 hover:underline">Criar primeira regra</a>.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Regra</th>
                            <th class="px-6 py-3 text-left">Tipo</th>
                            <th class="px-6 py-3 text-left">Frequência</th>
                            <th class="px-6 py-3 text-left">Próximo envio</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach (array_slice($regras, 0, 8) as $r): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3">
                                    <a href="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}") ?>" class="font-semibold text-gray-900 dark:text-gray-100 hover:text-emerald-600">
                                        <?= htmlspecialchars($r['nome']) ?>
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($r['empresa_nome'] ?? '') ?></p>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($tipos[$r['tipo_relatorio']] ?? $r['tipo_relatorio']) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($r['frequencia']) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                    <?= $r['proxima_execucao'] ? date('d/m H:i', strtotime($r['proxima_execucao'])) : '—' ?>
                                </td>
                                <td class="px-6 py-3">
                                    <?php if (!$r['ativo']): ?>
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inativa</span>
                                    <?php else: ?>
                                        <?php
                                        $cor = match ($r['ultimo_status']) {
                                            'sucesso' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'falha' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            'sem_dados' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                        ?>
                                        <span class="px-2 py-0.5 text-xs rounded-full <?= $cor ?>"><?= htmlspecialchars($r['ultimo_status'] ?: 'aguardando') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}") ?>" class="text-emerald-600 hover:text-emerald-700 font-semibold">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Últimos envios -->
    <?php if (!empty($ultimosEnvios)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Últimos envios</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($ultimosEnvios as $e): ?>
                <div class="px-6 py-3 flex items-center justify-between text-sm">
                    <div>
                        <span class="font-mono text-gray-700 dark:text-gray-300"><?= htmlspecialchars($e['numero']) ?></span>
                        · <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($e['regra_nome']) ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php
                        $cor = match ($e['status']) {
                            'enviado' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'falha' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            'aguardando' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        };
                        ?>
                        <span class="px-2 py-0.5 text-xs rounded-full <?= $cor ?>"><?= htmlspecialchars($e['status']) ?></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?= date('d/m H:i', strtotime($e['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function testarConexao(id, btn) {
    btn.disabled = true;
    const orig = btn.innerText;
    btn.innerText = 'Testando…';

    fetch('<?= $this->baseUrl("/whatsapp/conexoes/") ?>' + id + '/testar', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(j => {
        let msg = j.ok
            ? `OK · estado=${j.estado || '?'} (HTTP ${j.status_http})`
            : `Falha: ${j.error || 'erro desconhecido'}`;
        alert(msg);
        location.reload();
    })
    .catch(e => alert('Erro: ' + e.message))
    .finally(() => { btn.disabled = false; btn.innerText = orig; });
}
</script>
