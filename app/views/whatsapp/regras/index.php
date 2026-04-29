<div class="max-w-7xl mx-auto">
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
            <a href="<?= $this->baseUrl('/whatsapp') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">Regras de Relatório WhatsApp</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Cada regra define <em>o que</em> enviar, <em>quando</em> e <em>para quem</em>.</p>
        </div>
        <a href="<?= $this->baseUrl('/whatsapp/regras/create') ?>" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg">+ Nova regra</a>
    </div>

    <?php if (empty($regras)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 mb-3">Nenhuma regra criada ainda.</p>
            <a href="<?= $this->baseUrl('/whatsapp/regras/create') ?>" class="text-emerald-600 hover:underline font-semibold">Criar primeira regra</a>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Regra</th>
                            <th class="px-6 py-3 text-left">Empresa</th>
                            <th class="px-6 py-3 text-left">Tipo</th>
                            <th class="px-6 py-3 text-left">Quando</th>
                            <th class="px-6 py-3 text-left">Próximo</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($regras as $r): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3">
                                    <a href="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}") ?>" class="font-semibold text-gray-900 dark:text-gray-100 hover:text-emerald-600">
                                        <?= htmlspecialchars($r['nome']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($r['empresa_nome'] ?? '') ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($tipos[$r['tipo_relatorio']] ?? $r['tipo_relatorio']) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($r['frequencia']) ?>
                                    <?php if ($r['horario']): ?>
                                        · <?= substr($r['horario'], 0, 5) ?>
                                    <?php endif; ?>
                                </td>
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
                                <td class="px-6 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}/toggle") ?>" class="inline">
                                        <button class="text-sm text-yellow-600 hover:text-yellow-700 font-semibold mr-3"><?= $r['ativo'] ? 'Pausar' : 'Ativar' ?></button>
                                    </form>
                                    <a href="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}/edit") ?>" class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-300 font-semibold mr-3">Editar</a>
                                    <a href="<?= $this->baseUrl("/whatsapp/regras/{$r['id']}") ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
