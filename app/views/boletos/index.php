<?php
$situacoes = [
    'em_aberto' => ['label' => 'Em Aberto', 'cor' => 'blue', 'icone' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    'liquidado' => ['label' => 'Liquidado', 'cor' => 'green', 'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    'baixado' => ['label' => 'Baixado', 'cor' => 'gray', 'icone' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    'vencido' => ['label' => 'Vencido', 'cor' => 'red', 'icone' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z'],
    'protestado' => ['label' => 'Protestado', 'cor' => 'orange', 'icone' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
    'negativado' => ['label' => 'Negativado', 'cor' => 'purple', 'icone' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
];
$est = $estatisticas ?? [];
?>

<div class="max-w-7xl mx-auto" x-data="{ mostrarFiltros: true }">
    <!-- Alerta Migration Pendente -->
    <?php if (!empty($migrationPendente)): ?>
    <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-6 flex items-center gap-3">
        <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
        <div>
            <p class="font-semibold text-amber-800 dark:text-amber-200">Migration pendente</p>
            <p class="text-sm text-amber-700 dark:text-amber-300">As tabelas de boletos ainda não foram criadas no banco de dados. Execute a migration <code class="bg-amber-100 dark:bg-amber-800 px-1 rounded">2026_02_15_boletos.sql</code> para habilitar o módulo de cobrança.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Seletor de Empresa -->
    <?php if (!empty($empresasUsuario) && count($empresasUsuario) > 1): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/boletos" class="flex items-center gap-4">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
            <select name="empresa_id" onchange="this.form.submit()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <?php foreach ($empresasUsuario as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($empresaId == $emp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($emp['nome_fantasia'] ?? $emp['razao_social'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Boletos Bancarios</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie boletos emitidos, acompanhe pagamentos e inadimplencia</p>
        </div>
        <div class="flex gap-3">
            <a href="/boletos/analytics?empresa_id=<?= $empresaId ?>" class="inline-flex items-center px-4 py-2.5 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition-all text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Analytics
            </a>
            <a href="/boletos/create?empresa_id=<?= $empresaId ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all text-sm shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Emitir Boleto
            </a>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Em Aberto</div>
            <div class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($est['em_aberto'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">R$ <?= number_format($est['valor_em_aberto'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Liquidados</div>
            <div class="text-2xl font-bold text-green-600 mt-1"><?= number_format($est['liquidados'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">R$ <?= number_format($est['valor_liquidado'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Vencidos</div>
            <div class="text-2xl font-bold text-red-600 mt-1"><?= number_format($est['vencidos'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">R$ <?= number_format($est['valor_vencido'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Inadimplentes</div>
            <div class="text-2xl font-bold text-orange-600 mt-1"><?= number_format($est['qtd_inadimplente'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">R$ <?= number_format($est['valor_inadimplente'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Baixados</div>
            <div class="text-2xl font-bold text-gray-500 mt-1"><?= number_format($est['baixados'] ?? 0) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1"><?= number_format($est['total'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/boletos">
            <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Situacao</label>
                    <select name="situacao" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todas</option>
                        <?php foreach ($situacoes as $key => $sit): ?>
                            <option value="<?= $key ?>" <?= ($filtros['situacao'] ?? '') === $key ? 'selected' : '' ?>><?= $sit['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Banco</label>
                    <select name="conexao_bancaria_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todos</option>
                        <?php foreach ($conexoes as $cx): ?>
                            <option value="<?= $cx['id'] ?>" <?= ($filtros['conexao_bancaria_id'] ?? '') == $cx['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cx['identificacao'] ?? $cx['banco']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">De</label>
                    <input type="date" name="data_inicio" value="<?= $filtros['data_inicio'] ?? '' ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Ate</label>
                    <input type="date" name="data_fim" value="<?= $filtros['data_fim'] ?? '' ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Busca</label>
                    <div class="flex gap-2">
                        <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Nome, CPF/CNPJ, nosso numero..." class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Filtrar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Boletos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (empty($boletos)): ?>
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Nenhum boleto encontrado</h3>
                <p class="text-gray-400 dark:text-gray-500 mt-1">Emita seu primeiro boleto ou ajuste os filtros</p>
                <a href="/boletos/create?empresa_id=<?= $empresaId ?>" class="inline-flex items-center mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Emitir Boleto
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nosso N.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pagador</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Vencimento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Situacao</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Banco</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($boletos as $b): ?>
                            <?php
                            $sit = $situacoes[$b['situacao']] ?? ['label' => ucfirst($b['situacao']), 'cor' => 'gray'];
                            $corBg = [
                                'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            ][$sit['cor']] ?? 'bg-gray-100 text-gray-700';
                            $vencido = $b['situacao'] === 'em_aberto' && strtotime($b['data_vencimento']) < time();
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors <?= $vencido ? 'bg-red-50/50 dark:bg-red-900/10' : '' ?>">
                                <td class="px-4 py-3">
                                    <a href="/boletos/<?= $b['id'] ?>" class="font-mono font-semibold text-blue-600 dark:text-blue-400 hover:underline"><?= $b['nosso_numero'] ?? '-' ?></a>
                                    <?php if ($b['seu_numero']): ?>
                                        <div class="text-xs text-gray-400">Ref: <?= htmlspecialchars($b['seu_numero']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($b['pagador_nome'] ?? '') ?></div>
                                    <div class="text-xs text-gray-500"><?= preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $b['pagador_cpf_cnpj'] ?? '') ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">R$ <?= number_format($b['valor'] ?? 0, 2, ',', '.') ?></div>
                                    <?php if ($b['situacao'] === 'liquidado' && $b['valor_recebido']): ?>
                                        <div class="text-xs text-green-600">Recebido: R$ <?= number_format($b['valor_recebido'], 2, ',', '.') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="<?= $vencido ? 'text-red-600 font-semibold' : 'text-gray-700 dark:text-gray-300' ?>">
                                        <?= date('d/m/Y', strtotime($b['data_vencimento'])) ?>
                                    </div>
                                    <?php if ($b['data_pagamento']): ?>
                                        <div class="text-xs text-green-600">Pago: <?= date('d/m/Y', strtotime($b['data_pagamento'])) ?></div>
                                    <?php elseif ($vencido): ?>
                                        <div class="text-xs text-red-500"><?= (int)((time() - strtotime($b['data_vencimento'])) / 86400) ?> dias atraso</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $corBg ?>">
                                        <?= $sit['label'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                    <?= ucfirst($b['banco'] ?? '') ?>
                                    <div class="text-gray-400"><?= htmlspecialchars($b['conexao_nome'] ?? '') ?></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="/boletos/<?= $b['id'] ?>" title="Detalhes" class="p-1.5 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <?php if ($b['pdf_boleto']): ?>
                                        <button onclick="window.open('data:application/pdf;base64,<?= substr($b['pdf_boleto'], 0, 50) ?>...','_blank')" title="PDF" class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginacao -->
            <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500"><?= $total ?> boleto(s) encontrado(s)</div>
                <div class="flex gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $params = array_merge($filtros, ['empresa_id' => $empresaId, 'page' => $i]);
                        $params = array_filter($params, fn($v) => $v !== null && $v !== '');
                        ?>
                        <a href="/boletos?<?= http_build_query($params) ?>" class="px-3 py-1 rounded-lg text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
