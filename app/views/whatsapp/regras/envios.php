<div class="max-w-7xl mx-auto">
    <a href="<?= $this->baseUrl("/whatsapp/regras/{$regra['id']}") ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Voltar à regra</a>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2 mb-1">Histórico · <?= htmlspecialchars($regra['nome']) ?></h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6"><?= count($envios) ?> envio(s) registrado(s) (mais recentes primeiro).</p>

    <?php if (empty($envios)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-500 dark:text-gray-400">
            Nenhum envio realizado ainda.
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Data/Hora</th>
                            <th class="px-6 py-3 text-left">Destinatário</th>
                            <th class="px-6 py-3 text-left">Tipo</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Tentativas</th>
                            <th class="px-6 py-3 text-left">Mensagem</th>
                            <th class="px-6 py-3 text-left">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($envios as $e): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 align-top">
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($e['created_at'])) ?></td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($e['destinatario_nome'] ?? '—') ?>
                                    <p class="text-xs font-mono text-gray-500"><?= htmlspecialchars($e['numero']) ?></p>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($e['tipo_envio']) ?></td>
                                <td class="px-6 py-3">
                                    <?php
                                    $cor = match ($e['status']) {
                                        'enviado' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'falha' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    };
                                    ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full <?= $cor ?>"><?= htmlspecialchars($e['status']) ?></span>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300"><?= (int)$e['tentativas'] ?></td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-400 max-w-md">
                                    <details>
                                        <summary class="cursor-pointer text-emerald-600 hover:text-emerald-700">ver mensagem</summary>
                                        <pre class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-3 rounded whitespace-pre-wrap"><?= htmlspecialchars($e['mensagem']) ?></pre>
                                    </details>
                                </td>
                                <td class="px-6 py-3 text-xs text-red-600 dark:text-red-400 max-w-xs"><?= htmlspecialchars($e['erro'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
