<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Ponto de Equilíbrio</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Análise de custos fixos, variáveis, margem de contribuição e break-even por período</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <form method="GET" action="/ponto-equilibrio" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                    <select name="empresa_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                        <option value="">Todas (Consolidado)</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['empresa_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Início (Pagamento/Recebimento)</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filters['data_fim']) ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Calcular
                    </button>
                    <a href="/ponto-equilibrio" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php
    $c = $consolidado ?? [];
    $receitas = (float)($c['receitas'] ?? 0);
    $custosFixos = (float)($c['custos_fixos'] ?? 0);
    $custosVariaveis = (float)($c['custos_variaveis'] ?? 0);
    $pe = (float)($c['ponto_equilibrio'] ?? 0);
    $mcPct = (float)($c['margem_contribuicao_pct'] ?? 0);
    $margemSeguranca = (float)($c['margem_seguranca'] ?? 0);
    $margemSegurancaPct = (float)($c['margem_seguranca_pct'] ?? 0);
    $acimaEquilibrio = $c['acima_equilibrio'] ?? ($receitas >= $pe);
    ?>

    <!-- Cards de resumo consolidado -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border-2 border-green-200 dark:border-green-700">
            <p class="text-sm font-semibold text-green-700 dark:text-green-400">Receitas</p>
            <p class="text-2xl font-bold text-green-800 dark:text-green-300">R$ <?= number_format($receitas, 2, ',', '.') ?></p>
            <p class="text-xs text-green-600 dark:text-green-400 mt-1"><?= $periodo_label ?? '' ?></p>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl p-6 border-2 border-amber-200 dark:border-amber-700">
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Custos Fixos</p>
            <p class="text-2xl font-bold text-amber-800 dark:text-amber-300">R$ <?= number_format($custosFixos, 2, ',', '.') ?></p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl p-6 border-2 border-orange-200 dark:border-orange-700">
            <p class="text-sm font-semibold text-orange-700 dark:text-orange-400">Custos Variáveis</p>
            <p class="text-2xl font-bold text-orange-800 dark:text-orange-300">R$ <?= number_format($custosVariaveis, 2, ',', '.') ?></p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-6 border-2 border-purple-200 dark:border-purple-700">
            <p class="text-sm font-semibold text-purple-700 dark:text-purple-400">Ponto de Equilíbrio</p>
            <p class="text-2xl font-bold text-purple-800 dark:text-purple-300">R$ <?= number_format($pe, 2, ',', '.') ?></p>
            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Mínimo para não ter prejuízo</p>
        </div>
        <div class="bg-gradient-to-br from-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-50 to-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-100 dark:from-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-900/20 dark:to-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-800/20 rounded-xl p-6 border-2 border-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-200 dark:border-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-700">
            <p class="text-sm font-semibold text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-700 dark:text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-400">Margem de Segurança</p>
            <p class="text-2xl font-bold text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-800 dark:text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-300"><?= number_format($margemSegurancaPct, 1) ?>%</p>
            <p class="text-xs text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-600 dark:text-<?= $acimaEquilibrio ? 'emerald' : 'red' ?>-400 mt-1">R$ <?= number_format($margemSeguranca, 2, ',', '.') ?></p>
        </div>
    </div>

    <!-- Margem de Contribuição e Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Margem de Contribuição</h3>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= number_format($mcPct, 1) ?>%</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">(Receitas - Custos Variáveis) / Receitas × 100</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Situação no Período</h3>
            <p class="text-lg font-bold <?= $acimaEquilibrio ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' ?>">
                <?= $acimaEquilibrio ? '✓ Acima do equilíbrio (lucro)' : '✗ Abaixo do equilíbrio (prejuízo)' ?>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Receitas: R$ <?= number_format($receitas, 2, ',', '.') ?> |
                PE: R$ <?= number_format($pe, 2, ',', '.') ?>
            </p>
        </div>
    </div>

    <!-- Gráfico PE por Empresa -->
    <?php if (!empty($grafico['labels']) && count($grafico['labels']) > 1): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Ponto de Equilíbrio por Empresa (R$)</h3>
        <div class="overflow-x-auto">
            <div class="flex items-end gap-2 min-w-max" style="height: 200px;">
                <?php 
                $maxPE = max(array_filter($grafico['valores_pe'] ?? [], fn($v) => $v > 0)) ?: 1;
                foreach ($grafico['valores_pe'] as $i => $valor): 
                    $altura = $maxPE > 0 ? min(100, ($valor / $maxPE) * 90) : 0;
                ?>
                <div class="flex flex-col items-center gap-1">
                    <div class="text-xs text-gray-600 dark:text-gray-400 font-medium text-center truncate max-w-[80px]" title="<?= htmlspecialchars($grafico['labels'][$i] ?? '') ?>">
                        <?= htmlspecialchars(mb_substr($grafico['labels'][$i] ?? '', 0, 10)) ?>...
                    </div>
                    <div class="w-12 bg-purple-500 dark:bg-purple-600 rounded-t transition-all" style="height: <?= $altura ?>px;" title="R$ <?= number_format($valor, 2, ',', '.') ?>"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= number_format($valor / 1000, 1) ?>k</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Blocos por Empresa -->
    <?php if (!empty($por_empresa)): ?>
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Por Empresa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($por_empresa as $empId => $d): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">
                    <?= htmlspecialchars($d['empresa_nome'] ?? "Empresa #$empId") ?>
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Receitas</span>
                        <span class="font-medium">R$ <?= number_format($d['receitas'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Custos Fixos</span>
                        <span class="font-medium">R$ <?= number_format($d['custos_fixos'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Custos Variáveis</span>
                        <span class="font-medium">R$ <?= number_format($d['custos_variaveis'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-purple-600 dark:text-purple-400 font-medium">Ponto Equilíbrio</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">R$ <?= number_format($d['ponto_equilibrio'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Margem Contrib. %</span>
                        <span class="font-medium"><?= number_format($d['margem_contribuicao_pct'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Margem Segurança</span>
                        <span class="font-medium <?= ($d['acima_equilibrio'] ?? false) ? 'text-emerald-600' : 'text-red-600' ?>">
                            <?= number_format($d['margem_seguranca_pct'] ?? 0, 1) ?>%
                        </span>
                    </div>
                </div>
                <a href="/ponto-equilibrio?empresa_id=<?= $empId ?>" class="mt-4 inline-block text-sm text-purple-600 dark:text-purple-400 hover:underline">
                    Ver detalhes →
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabelas de Custos Fixos e Variáveis por Categoria -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                Custos Fixos por Categoria
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="text-left px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</th>
                            <th class="text-right px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($custos_fixos_detalhe)): ?>
                        <tr><td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Nenhum custo fixo no período</td></tr>
                        <?php else: ?>
                        <?php foreach ($custos_fixos_detalhe as $row): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($row['categoria_nome'] ?? '') ?></td>
                            <td class="px-6 py-3 text-right font-medium">R$ <?= number_format($row['total'] ?? 0, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                Custos Variáveis por Categoria
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="text-left px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</th>
                            <th class="text-right px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($custos_variaveis_detalhe)): ?>
                        <tr><td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Nenhum custo variável no período</td></tr>
                        <?php else: ?>
                        <?php foreach ($custos_variaveis_detalhe as $row): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($row['categoria_nome'] ?? '') ?></td>
                            <td class="px-6 py-3 text-right font-medium">R$ <?= number_format($row['total'] ?? 0, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ajuda rápida -->
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Como interpretar o Ponto de Equilíbrio</h4>
        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
            <li><strong>Ponto de Equilíbrio:</strong> Valor mínimo que você precisa faturar para cobrir todos os custos (fixos + variáveis).</li>
            <li><strong>Margem de Contribuição:</strong> Percentual das receitas que sobra após pagar os custos variáveis. Quanto maior, melhor.</li>
            <li><strong>Margem de Segurança:</strong> Quanto suas receitas estão acima do PE. Positivo = lucro; negativo = prejuízo.</li>
            <li><strong>Custos fixos:</strong> Despesas que não variam com o volume (aluguel, salários, etc.). Configure <code>tipo_custo = fixo</code> nas contas.</li>
            <li><strong>Custos variáveis:</strong> Despesas proporcionais ao volume (matéria-prima, comissões). Configure <code>tipo_custo = variavel</code>.</li>
        </ul>
    </div>
</div>
