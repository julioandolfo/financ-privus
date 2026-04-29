<div class="max-w-6xl mx-auto" x-data="regraShow()" x-init="init()">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-xl"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <a href="<?= $this->baseUrl('/whatsapp/regras') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Regras</a>

    <div class="mt-2 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($regra['nome']) ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                <?= htmlspecialchars($tipos[$regra['tipo_relatorio']] ?? $regra['tipo_relatorio']) ?>
                · Frequência <?= htmlspecialchars($regra['frequencia']) ?>
                <?php if ($regra['horario']): ?> · <?= substr($regra['horario'], 0, 5) ?><?php endif; ?>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/edit") ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl">Editar</a>
            <button @click="preview()" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-semibold rounded-xl">👁 Pré-visualizar</button>
            <button @click="executarAgora()" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl">▶ Executar agora</button>
            <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/toggle") ?>" class="inline">
                <button class="px-4 py-2 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300 font-semibold rounded-xl">
                    <?= $regra['ativo'] ? 'Pausar' : 'Ativar' ?>
                </button>
            </form>
            <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/delete") ?>" class="inline" onsubmit="return confirm('Remover esta regra? Os destinatários e histórico serão perdidos.');">
                <button class="px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-semibold rounded-xl">Remover</button>
            </form>
        </div>
    </div>

    <!-- Cards principais -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Status</p>
            <p class="text-xl font-bold mt-1 <?= $regra['ativo'] ? 'text-emerald-600' : 'text-gray-500' ?>">
                <?= $regra['ativo'] ? 'Ativa' : 'Pausada' ?>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Próximo envio: <strong><?= $regra['proxima_execucao'] ? date('d/m/Y H:i', strtotime($regra['proxima_execucao'])) : '—' ?></strong></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Último envio: <strong><?= $regra['ultima_execucao'] ? date('d/m/Y H:i', strtotime($regra['ultima_execucao'])) : '—' ?></strong></p>
            <?php if ($regra['ultimo_erro']): ?>
                <p class="text-xs text-red-600 mt-2"><?= htmlspecialchars($regra['ultimo_erro']) ?></p>
            <?php endif; ?>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Conexão Evolution</p>
            <p class="text-lg font-bold mt-1 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($regra['evolution_nome'] ?? '—') ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono"><?= htmlspecialchars($regra['instance_name'] ?? '') ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Empresa</p>
            <p class="text-lg font-bold mt-1 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($regra['empresa_nome'] ?? '') ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Limite lista: <?= (int)$regra['limite_itens_lista'] ?></p>
        </div>
    </div>

    <!-- Filtros -->
    <?php if (!empty($regra['filtros'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-8">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">Filtros</h2>
        <pre class="text-xs bg-gray-50 dark:bg-gray-900 p-3 rounded-lg overflow-auto"><?= htmlspecialchars(json_encode($regra['filtros'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>
    <?php endif; ?>

    <!-- Destinatários -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">📱 Destinatários</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400"><?= count($destinatarios) ?> contato(s)</span>
        </div>

        <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/destinatarios") ?>" class="grid grid-cols-12 gap-2 mb-5">
            <input type="text" name="nome" placeholder="Rótulo (ex: Diretor)" class="col-span-12 md:col-span-4 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg" required>
            <input type="text" name="numero" placeholder="5511999999999" class="col-span-12 md:col-span-6 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono" required>
            <button type="submit" class="col-span-12 md:col-span-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg">+ Adicionar</button>
        </form>

        <?php if (empty($destinatarios)): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Nenhum destinatário cadastrado.</p>
        <?php else: ?>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($destinatarios as $d): ?>
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($d['nome']) ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-mono"><?= htmlspecialchars($d['numero']) ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <?php if (!$d['ativo']): ?>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inativo</span>
                            <?php endif; ?>
                            <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/destinatarios/{$d['id']}") ?>" class="inline">
                                <input type="hidden" name="nome" value="<?= htmlspecialchars($d['nome']) ?>">
                                <input type="hidden" name="numero" value="<?= htmlspecialchars($d['numero']) ?>">
                                <input type="hidden" name="ativo" value="<?= $d['ativo'] ? 0 : 1 ?>">
                                <button class="text-sm text-yellow-600 hover:text-yellow-700 font-semibold"><?= $d['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                            </form>
                            <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/destinatarios/{$d['id']}/delete") ?>" class="inline" onsubmit="return confirm('Remover este destinatário?');">
                                <button class="text-sm text-red-600 hover:text-red-700 font-semibold">Remover</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Teste -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">🧪 Enviar teste</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Envia o relatório atual para um número específico (sem reagendar).</p>
        <div class="flex gap-2">
            <input x-model="testeNumero" type="text" placeholder="5511999999999" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono">
            <button @click="testar()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl">Enviar teste</button>
        </div>
    </div>

    <!-- Preview -->
    <div x-show="previewMsg" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">Pré-visualização</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="previewInfo"></p>
        <pre class="text-sm bg-gray-50 dark:bg-gray-900 p-4 rounded-xl whitespace-pre-wrap font-sans" x-text="previewMsg"></pre>
    </div>

    <!-- Histórico -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Histórico de envios</h2>
            <a href="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/envios") ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold">Ver completo →</a>
        </div>
        <?php if (empty($envios)): ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">Nenhum envio realizado ainda.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Quando</th>
                            <th class="px-6 py-3 text-left">Destinatário</th>
                            <th class="px-6 py-3 text-left">Tipo</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach (array_slice($envios, 0, 25) as $e): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-2 text-gray-700 dark:text-gray-300"><?= date('d/m H:i:s', strtotime($e['created_at'])) ?></td>
                                <td class="px-6 py-2 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($e['destinatario_nome'] ?? '—') ?> <span class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($e['numero']) ?></span></td>
                                <td class="px-6 py-2 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($e['tipo_envio']) ?></td>
                                <td class="px-6 py-2">
                                    <?php
                                    $cor = match ($e['status']) {
                                        'enviado' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'falha' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    };
                                    ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full <?= $cor ?>"><?= htmlspecialchars($e['status']) ?></span>
                                </td>
                                <td class="px-6 py-2 text-xs text-red-600 dark:text-red-400 max-w-xs truncate"><?= htmlspecialchars($e['erro'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function regraShow() {
    return {
        previewMsg: '',
        previewInfo: '',
        testeNumero: '',
        init() {},
        preview() {
            this.previewMsg = 'Carregando…';
            fetch('<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/preview") ?>')
                .then(r => r.json())
                .then(j => {
                    if (j.ok) {
                        this.previewMsg = j.mensagem;
                        this.previewInfo = `${j.titulo || ''} · ${j.total} item(ns) · R$ ${(j.valor_total || 0).toFixed(2)}`;
                    } else {
                        this.previewMsg = 'Erro: ' + (j.error || 'desconhecido');
                    }
                });
        },
        executarAgora() {
            if (!confirm('Disparar este relatório agora para todos os destinatários ativos?')) return;
            fetch('<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/executar") ?>', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(j => {
                    if (j.ok) {
                        alert(`OK · ${j.resultado.enviados} enviado(s), ${j.resultado.falhas} falha(s).`);
                        location.reload();
                    } else {
                        alert('Erro: ' + (j.error || 'desconhecido'));
                    }
                });
        },
        testar() {
            const num = this.testeNumero.replace(/\D/g, '');
            if (num.length < 10) { alert('Número inválido (use 5511999999999).'); return; }
            const fd = new FormData();
            fd.append('numero', num);
            fetch('<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}/testar") ?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(j => alert(j.ok ? 'Mensagem enviada!' : 'Erro: ' + (j.error || 'desconhecido')));
        }
    };
}
</script>
