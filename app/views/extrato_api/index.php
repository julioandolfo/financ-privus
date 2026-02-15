<?php
$totalDebitos = $resumo['debito']['total'] ?? 0;
$totalCreditos = $resumo['credito']['total'] ?? 0;
$qtdDebitos = $resumo['debito']['quantidade'] ?? 0;
$qtdCreditos = $resumo['credito']['quantidade'] ?? 0;
$saldoPeriodo = $totalCreditos - $totalDebitos;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Extrato Bancário (API)</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Visualização completa do extrato importado dos bancos — apenas leitura</p>
        </div>
        <div class="flex gap-2">
            <a href="/conexoes-bancarias" class="px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Conexões
            </a>
        </div>
    </div>
    
    <!-- Seletor de Empresa -->
    <?php if (!empty($empresas_usuario) && count($empresas_usuario) > 0): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/extrato-api" class="flex items-center gap-4">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
            <select name="empresa_id" onchange="this.form.submit()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <?php foreach ($empresas_usuario as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nome_fantasia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Badge informativo -->
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-3 mb-6 flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <p class="text-sm text-purple-800 dark:text-purple-200">
            Este extrato é <strong>apenas para visualização</strong>. Ele não afeta contas a pagar, contas a receber ou transações pendentes. 
            Para importar transações para aprovação, use o botão "Sincronizar" nas conexões bancárias.
        </p>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/extrato-api">
            <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($empresa_id_selecionada ?? '') ?>">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase">Conexão</label>
                    <select name="conexao_bancaria_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todas</option>
                        <?php foreach ($conexoes as $con): ?>
                            <option value="<?= $con['id'] ?>" <?= ($filtros['conexao_bancaria_id'] == $con['id']) ? 'selected' : '' ?>>
                                <?= ucfirst($con['banco']) ?> - <?= htmlspecialchars($con['identificacao'] ?: $con['banco_conta_id'] ?: 'Conta ' . $con['id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase">Tipo</label>
                    <select name="tipo" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todos</option>
                        <option value="debito" <?= ($filtros['tipo'] ?? '') === 'debito' ? 'selected' : '' ?>>Débitos (saídas)</option>
                        <option value="credito" <?= ($filtros['tipo'] ?? '') === 'credito' ? 'selected' : '' ?>>Créditos (entradas)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase">De</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" onchange="this.form.submit()"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase">Até</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" onchange="this.form.submit()"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase">Busca</label>
                    <div class="flex gap-1">
                        <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" placeholder="Descrição..."
                               class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <button type="submit" class="px-3 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Cards Resumo -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Registros</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1"><?= number_format($paginacao['total_registros']) ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Saídas (<?= $qtdDebitos ?>)</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">R$ <?= number_format($totalDebitos, 2, ',', '.') ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Entradas (<?= $qtdCreditos ?>)</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">R$ <?= number_format($totalCreditos, 2, ',', '.') ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo Período</p>
            <p class="text-xl font-bold mt-1 <?= $saldoPeriodo >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                R$ <?= number_format(abs($saldoPeriodo), 2, ',', '.') ?>
                <?= $saldoPeriodo < 0 ? ' (-)' : '' ?>
            </p>
        </div>
    </div>

    <!-- Tabela de Extrato -->
    <?php if (empty($transacoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="text-5xl mb-4">📄</div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Nenhuma transação no extrato</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Importe o extrato via botão "Extrato" na página de conexões bancárias.</p>
            <a href="/conexoes-bancarias" class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition">
                Ir para Conexões
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Banco</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Valor</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <?php 
                        $dataAnterior = '';
                        foreach ($transacoes as $t): 
                            $isDebito = $t['tipo'] === 'debito';
                            $dataAtual = date('d/m/Y', strtotime($t['data_transacao']));
                            $novoDia = $dataAtual !== $dataAnterior;
                            $dataAnterior = $dataAtual;
                        ?>
                        <?php if ($novoDia): ?>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td colspan="6" class="px-4 py-2">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    <?= $dataAtual ?> 
                                    <span class="text-gray-400 dark:text-gray-500 normal-case font-normal ml-1">
                                        (<?= ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'][date('w', strtotime($t['data_transacao']))] ?>)
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <?= date('d/m/Y', strtotime($t['data_transacao'])) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php 
                                    $descPartes = array_map('trim', explode('|', $t['descricao']));
                                    $descPrincipal = $descPartes[0] ?? $t['descricao'];
                                    $descDetalhes = array_filter(array_slice($descPartes, 1));
                                ?>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($descPrincipal) ?>
                                </div>
                                <?php if (!empty($descDetalhes)): ?>
                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                        <?php foreach ($descDetalhes as $det): ?>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-1.5 py-0.5 rounded">
                                                <?= htmlspecialchars($det) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($t['metodo_pagamento'])): ?>
                                    <span class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($t['metodo_pagamento']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <?= ucfirst($t['banco'] ?? $t['origem'] ?? 'API') ?>
                                <?php if (!empty($t['identificacao'])): ?>
                                    <br><span class="text-gray-400"><?= htmlspecialchars(substr($t['identificacao'], 0, 20)) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $isDebito ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' ?>">
                                    <?= $isDebito ? 'SAÍDA' : 'ENTRADA' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap <?= $isDebito ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                                <?= $isDebito ? '-' : '+' ?> R$ <?= number_format(abs($t['valor']), 2, ',', '.') ?>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <?php if ($t['saldo_apos'] !== null): ?>
                                    R$ <?= number_format($t['saldo_apos'], 2, ',', '.') ?>
                                <?php else: ?>
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($paginacao['total_paginas'] > 1): ?>
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Mostrando <?= (($paginacao['pagina_atual'] - 1) * $paginacao['por_pagina']) + 1 ?> 
                    a <?= min($paginacao['pagina_atual'] * $paginacao['por_pagina'], $paginacao['total_registros']) ?> 
                    de <?= number_format($paginacao['total_registros']) ?> registros
                </p>
                <div class="flex gap-1">
                    <?php 
                    $queryBase = http_build_query(array_filter(array_merge($filtros, ['empresa_id' => $empresa_id_selecionada])));
                    $inicio = max(1, $paginacao['pagina_atual'] - 3);
                    $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 3);
                    ?>
                    
                    <?php if ($paginacao['pagina_atual'] > 1): ?>
                    <a href="/extrato-api?<?= $queryBase ?>&pagina=<?= $paginacao['pagina_atual'] - 1 ?>" 
                       class="px-3 py-1 text-xs rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        ← Anterior
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($p = $inicio; $p <= $fim; $p++): ?>
                    <a href="/extrato-api?<?= $queryBase ?>&pagina=<?= $p ?>" 
                       class="px-3 py-1 text-xs rounded-lg <?= $p === $paginacao['pagina_atual'] ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <?= $p ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                    <a href="/extrato-api?<?= $queryBase ?>&pagina=<?= $paginacao['pagina_atual'] + 1 ?>" 
                       class="px-3 py-1 text-xs rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        Próxima →
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
