<?php
/**
 * Seções extras do Dashboard:
 * - Saúde Financeira (Score)
 * - Alertas Inteligentes
 * - Gráficos de Evolução (12 meses)
 * - Receitas/Despesas por Categoria (donut)
 * - Fluxo de Caixa Projetado (30 dias)
 * - Aging de Recebíveis
 * - Top Devedores / Maiores Despesas / Receitas
 * - Timeline de Vencimentos
 * - Mini DRE
 */

$saudeData = $saude_financeira ?? ['score' => 0, 'label' => 'N/A', 'componentes' => []];
$alertasData = $alertas ?? [];
$agingData = $aging ?? ['valores' => ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0], 'quantidade' => ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0]];
$topDevedoresData = $top_devedores ?? [];
$topDespesasData = $top_despesas ?? [];
$topReceitasData = $top_receitas ?? [];
$recCatData = $receitas_por_categoria ?? [];
$despCatData = $despesas_por_categoria ?? [];
$evolucaoData = $evolucao_mensal ?? [];
$vencimentosData = $vencimentos_proximos ?? [];
$fluxoData = $fluxo_projetado ?? [];
$dreData = $mini_dre ?? [];

// Cores do score
$scoreColor = 'gray';
if ($saudeData['score'] >= 80) $scoreColor = 'green';
elseif ($saudeData['score'] >= 60) $scoreColor = 'blue';
elseif ($saudeData['score'] >= 40) $scoreColor = 'yellow';
elseif ($saudeData['score'] >= 20) $scoreColor = 'orange';
else $scoreColor = 'red';

$scoreDeg = ($saudeData['score'] / 100) * 360;
?>

<!-- ========================================
     SAÚDE FINANCEIRA + ALERTAS
     ======================================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Indicador de Saúde Financeira -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-<?= $scoreColor ?>-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Saúde Financeira
        </h3>
        <div class="flex items-center justify-center mb-4">
            <div class="relative w-36 h-36">
                <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="10" class="text-gray-200 dark:text-gray-700"/>
                    <circle cx="60" cy="60" r="50" fill="none" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="<?= $saudeData['score'] * 3.14 ?> 314"
                            class="text-<?= $scoreColor ?>-500"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black text-<?= $scoreColor ?>-600 dark:text-<?= $scoreColor ?>-400"><?= $saudeData['score'] ?></span>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase"><?= $saudeData['label'] ?></span>
                </div>
            </div>
        </div>
        <div class="space-y-2">
            <?php foreach ($saudeData['componentes'] as $comp): ?>
            <div>
                <div class="flex justify-between text-xs mb-0.5">
                    <span class="text-gray-600 dark:text-gray-400"><?= $comp['label'] ?></span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100"><?= $comp['score'] ?>/<?= $comp['max'] ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-<?= $scoreColor ?>-500 h-1.5 rounded-full" style="width: <?= ($comp['score'] / $comp['max']) * 100 ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Alertas Inteligentes -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Alertas Inteligentes
            <?php if (count($alertasData) > 0): ?>
                <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"><?= count($alertasData) ?></span>
            <?php endif; ?>
        </h3>
        <?php if (empty($alertasData)): ?>
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhum alerta no momento</p>
                <p class="text-sm text-gray-400">Tudo está funcionando normalmente</p>
            </div>
        <?php else: ?>
            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                <?php foreach ($alertasData as $alerta):
                    $alertColors = [
                        'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300',
                        'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300',
                        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300',
                    ];
                    $alertColor = $alertColors[$alerta['tipo']] ?? $alertColors['info'];
                    $alertIcon = match($alerta['icone'] ?? '') {
                        'bank' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                        'alert' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
                        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'trending-up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                        'trending-down' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6',
                        'list' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                    };
                ?>
                <div class="flex items-start gap-3 p-3 rounded-xl border <?= $alertColor ?>">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $alertIcon ?>"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm"><?= htmlspecialchars($alerta['titulo']) ?></p>
                        <p class="text-xs opacity-80 mt-0.5"><?= htmlspecialchars($alerta['mensagem']) ?></p>
                    </div>
                    <?php if (!empty($alerta['link'])): ?>
                    <a href="<?= $alerta['link'] ?>" class="flex-shrink-0 text-xs font-semibold underline opacity-70 hover:opacity-100">Ver</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     GRÁFICOS: EVOLUÇÃO MENSAL + POR CATEGORIA
     ======================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Gráfico Evolução Mensal (12 meses) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            Evolução Mensal (12 meses)
        </h3>
        <div class="h-72">
            <canvas id="chartEvolucao"></canvas>
        </div>
    </div>

    <!-- Gráfico Fluxo de Caixa Projetado -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Fluxo de Caixa Projetado (30 dias)
            <a href="/fluxo-caixa" class="ml-auto text-xs text-cyan-600 hover:text-cyan-800 font-semibold">Ver completo</a>
        </h3>
        <div class="h-72">
            <canvas id="chartFluxo"></canvas>
        </div>
    </div>
</div>

<!-- Receitas/Despesas por Categoria -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
            Receitas por Categoria
        </h3>
        <?php if (empty($recCatData)): ?>
            <p class="text-center text-gray-400 py-8">Sem dados no período</p>
        <?php else: ?>
        <div class="h-56">
            <canvas id="chartReceitasCat"></canvas>
        </div>
        <?php endif; ?>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
            Despesas por Categoria
        </h3>
        <?php if (empty($despCatData)): ?>
            <p class="text-center text-gray-400 py-8">Sem dados no período</p>
        <?php else: ?>
        <div class="h-56">
            <canvas id="chartDespesasCat"></canvas>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     AGING + TOP DEVEDORES
     ======================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Aging de Recebíveis -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aging de Recebíveis
            <a href="/contas-receber?status=vencido" class="ml-auto text-xs text-orange-600 hover:text-orange-800 font-semibold">Ver todas</a>
        </h3>
        <?php
        $agingTotal = array_sum($agingData['valores']);
        $agingFaixas = [
            ['label' => '0-30 dias', 'key' => '0_30', 'color' => 'amber'],
            ['label' => '31-60 dias', 'key' => '31_60', 'color' => 'orange'],
            ['label' => '61-90 dias', 'key' => '61_90', 'color' => 'red'],
            ['label' => '90+ dias', 'key' => '90_plus', 'color' => 'rose'],
        ];
        ?>
        <?php if ($agingTotal == 0): ?>
            <div class="text-center py-8">
                <svg class="w-10 h-10 mx-auto text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum recebível vencido</p>
            </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($agingFaixas as $faixa):
                $val = $agingData['valores'][$faixa['key']] ?? 0;
                $qtd = $agingData['quantidade'][$faixa['key']] ?? 0;
                $pct = $agingTotal > 0 ? ($val / $agingTotal) * 100 : 0;
            ?>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600 dark:text-gray-400"><?= $faixa['label'] ?> <span class="text-xs text-gray-400">(<?= $qtd ?> conta<?= $qtd != 1 ? 's' : '' ?>)</span></span>
                    <span class="font-bold text-<?= $faixa['color'] ?>-600 dark:text-<?= $faixa['color'] ?>-400">R$ <?= number_format($val, 2, ',', '.') ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-<?= $faixa['color'] ?>-500 h-2.5 rounded-full transition-all" style="width: <?= number_format($pct, 1) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between font-bold text-sm">
                <span class="text-gray-900 dark:text-gray-100">Total Vencido</span>
                <span class="text-red-600 dark:text-red-400">R$ <?= number_format($agingTotal, 2, ',', '.') ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top 5 Devedores -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Top 5 Clientes Devedores
        </h3>
        <?php if (empty($topDevedoresData)): ?>
            <div class="text-center py-8">
                <svg class="w-10 h-10 mx-auto text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum devedor no momento</p>
            </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php $pos = 1; foreach ($topDevedoresData as $nome => $info): ?>
            <div class="flex items-center gap-3">
                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold flex items-center justify-center"><?= $pos ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($nome) ?></p>
                    <p class="text-xs text-gray-500"><?= $info['qtd'] ?> conta<?= $info['qtd'] != 1 ? 's' : '' ?> vencida<?= $info['qtd'] != 1 ? 's' : '' ?></p>
                </div>
                <span class="font-bold text-red-600 dark:text-red-400 text-sm">R$ <?= number_format($info['valor'], 2, ',', '.') ?></span>
            </div>
            <?php $pos++; endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     TOP DESPESAS + TOP RECEITAS
     ======================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top 5 Maiores Despesas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            </svg>
            Top 5 Maiores Despesas
        </h3>
        <?php if (empty($topDespesasData)): ?>
            <p class="text-center text-gray-400 py-6 text-sm">Sem despesas no período</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topDespesasData as $i => $d): ?>
            <div class="flex items-center gap-3">
                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold flex items-center justify-center"><?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($d['descricao']) ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($d['fornecedor']) ?> &bull; <?= $d['categoria'] ?> &bull; <?= date('d/m', strtotime($d['data'])) ?></p>
                </div>
                <span class="font-bold text-red-600 dark:text-red-400 text-sm whitespace-nowrap">R$ <?= number_format($d['valor'], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top 5 Maiores Receitas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Top 5 Maiores Receitas
        </h3>
        <?php if (empty($topReceitasData)): ?>
            <p class="text-center text-gray-400 py-6 text-sm">Sem receitas no período</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topReceitasData as $i => $r): ?>
            <div class="flex items-center gap-3">
                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold flex items-center justify-center"><?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($r['descricao']) ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($r['cliente']) ?> &bull; <?= $r['categoria'] ?> &bull; <?= date('d/m', strtotime($r['data'])) ?></p>
                </div>
                <span class="font-bold text-green-600 dark:text-green-400 text-sm whitespace-nowrap">R$ <?= number_format($r['valor'], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     TIMELINE VENCIMENTOS + MINI DRE
     ======================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Timeline de Vencimentos (7 dias) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Vencimentos - Próximos 7 dias
        </h3>
        <?php if (empty($vencimentosData)): ?>
            <div class="text-center py-8">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum vencimento nos próximos 7 dias</p>
            </div>
        <?php else: ?>
        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
            <?php 
            $lastDate = '';
            foreach ($vencimentosData as $v):
                $vDate = date('d/m/Y', strtotime($v['vencimento']));
                $isToday = $v['vencimento'] === date('Y-m-d');
                $isTomorrow = $v['vencimento'] === date('Y-m-d', strtotime('+1 day'));
                $dateLabel = $isToday ? 'Hoje' : ($isTomorrow ? 'Amanhã' : $vDate);
                
                if ($vDate !== $lastDate):
                    $lastDate = $vDate;
            ?>
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mt-2 first:mt-0 flex items-center gap-1">
                    <?php if ($isToday): ?><span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span><?php endif; ?>
                    <?= $dateLabel ?>
                    <?php if (!$isToday && !$isTomorrow): ?><span class="text-gray-400 normal-case"> (<?= ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'][date('w', strtotime($v['vencimento']))] ?>)</span><?php endif; ?>
                </div>
            <?php endif; ?>
                <div class="flex items-center gap-3 p-2 rounded-lg <?= $v['tipo'] === 'receber' ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10' ?>">
                    <span class="flex-shrink-0 w-2 h-8 rounded-full <?= $v['tipo'] === 'receber' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($v['descricao']) ?></p>
                        <p class="text-xs text-gray-500"><?= $v['tipo'] === 'receber' ? ($v['cliente'] ?? '') : ($v['fornecedor'] ?? '') ?></p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold <?= $v['tipo'] === 'receber' ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $v['tipo'] === 'receber' ? '+' : '-' ?> R$ <?= number_format($v['valor'], 2, ',', '.') ?>
                        </p>
                        <a href="/contas-<?= $v['tipo'] === 'receber' ? 'receber' : 'pagar' ?>/<?= $v['id'] ?>" class="text-xs text-blue-500 hover:underline">Ver</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Mini DRE -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Mini DRE do Período
            <a href="/dre" class="ml-auto text-xs text-purple-600 hover:text-purple-800 font-semibold">DRE Completo</a>
        </h3>
        <?php if (!empty($dreData)): ?>
        <div class="space-y-1 text-sm">
            <?php
            $dreLines = [
                ['label' => 'Receita Bruta', 'value' => $dreData['receita_bruta'], 'bold' => true, 'color' => 'green'],
                ['label' => '(-) Deduções (PIS/COFINS/ISS est.)', 'value' => -$dreData['deducoes'], 'bold' => false, 'color' => 'red'],
                ['label' => '(=) Receita Líquida', 'value' => $dreData['receita_liquida'], 'bold' => true, 'color' => 'blue', 'border' => true],
                ['label' => '(-) Custo dos Serviços/Produtos', 'value' => -$dreData['custo_servicos'], 'bold' => false, 'color' => 'red'],
                ['label' => '(=) Lucro Bruto', 'value' => $dreData['lucro_bruto'], 'bold' => true, 'color' => 'blue', 'border' => true],
                ['label' => '(-) Despesas Administrativas', 'value' => -$dreData['desp_administrativas'], 'bold' => false, 'color' => 'red'],
                ['label' => '(-) Despesas Comerciais', 'value' => -$dreData['desp_comerciais'], 'bold' => false, 'color' => 'red'],
                ['label' => '(=) Resultado Operacional', 'value' => $dreData['resultado_operacional'], 'bold' => true, 'color' => 'blue', 'border' => true],
                ['label' => '(+/-) Resultado Financeiro', 'value' => $dreData['resultado_financeiro'], 'bold' => false, 'color' => 'gray'],
                ['label' => '(=) Resultado Antes dos Tributos', 'value' => $dreData['resultado_antes_tributos'], 'bold' => true, 'color' => 'blue', 'border' => true],
                ['label' => '(-) IR/CSLL Estimados', 'value' => -$dreData['imposto_estimado'], 'bold' => false, 'color' => 'red'],
                ['label' => '(=) RESULTADO LÍQUIDO', 'value' => $dreData['resultado_liquido'], 'bold' => true, 'color' => $dreData['resultado_liquido'] >= 0 ? 'green' : 'red', 'border' => true, 'highlight' => true],
            ];
            foreach ($dreLines as $line):
                $isNeg = $line['value'] < 0;
            ?>
            <div class="flex justify-between items-center py-1.5 <?= !empty($line['border']) ? 'border-t border-gray-200 dark:border-gray-700 pt-2' : '' ?> <?= !empty($line['highlight']) ? 'bg-gray-50 dark:bg-gray-700/50 -mx-2 px-2 rounded-lg' : '' ?>">
                <span class="<?= $line['bold'] ? 'font-bold text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 pl-3' ?>"><?= $line['label'] ?></span>
                <span class="font-<?= $line['bold'] ? 'bold' : 'medium' ?> text-<?= $line['color'] ?>-600 dark:text-<?= $line['color'] ?>-400 whitespace-nowrap">
                    <?= $isNeg ? '(' : '' ?>R$ <?= number_format(abs($line['value']), 2, ',', '.') ?><?= $isNeg ? ')' : '' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-center text-gray-400 py-6 text-sm">Sem dados para gerar DRE</p>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================
     CHART.JS SCRIPTS
     ======================================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    
    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;
    
    // ---- EVOLUÇÃO MENSAL ----
    const evolucao = <?= json_encode($evolucaoData) ?>;
    if (evolucao.length > 0 && document.getElementById('chartEvolucao')) {
        new Chart(document.getElementById('chartEvolucao'), {
            type: 'bar',
            data: {
                labels: evolucao.map(e => e.mes),
                datasets: [
                    {
                        label: 'Receitas',
                        data: evolucao.map(e => e.receitas),
                        backgroundColor: 'rgba(34,197,94,0.7)',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        order: 2
                    },
                    {
                        label: 'Despesas',
                        data: evolucao.map(e => e.despesas),
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        order: 2
                    },
                    {
                        label: 'Lucro',
                        data: evolucao.map(e => e.lucro),
                        type: 'line',
                        borderColor: 'rgb(59,130,246)',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        borderWidth: 2,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': R$ ' + ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'R$ ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v.toFixed(0))
                        }
                    }
                }
            }
        });
    }
    
    // ---- FLUXO DE CAIXA PROJETADO ----
    const fluxo = <?= json_encode(array_map(function($f) { return ['dia' => date('d/m', strtotime($f['dia'])), 'saldo' => $f['saldo'], 'entradas' => $f['entradas'], 'saidas' => $f['saidas']]; }, $fluxoData)) ?>;
    if (fluxo.length > 0 && document.getElementById('chartFluxo')) {
        new Chart(document.getElementById('chartFluxo'), {
            type: 'line',
            data: {
                labels: fluxo.map(f => f.dia),
                datasets: [{
                    label: 'Saldo Projetado',
                    data: fluxo.map(f => f.saldo),
                    borderColor: 'rgb(6,182,212)',
                    backgroundColor: function(ctx) {
                        const chart = ctx.chart;
                        const {ctx: context, chartArea} = chart;
                        if (!chartArea) return 'rgba(6,182,212,0.1)';
                        const gradient = context.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(6,182,212,0.3)');
                        gradient.addColorStop(1, 'rgba(6,182,212,0.02)');
                        return gradient;
                    },
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHitRadius: 10,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Saldo: R$ ' + ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    x: { ticks: { maxTicksLimit: 10 } },
                    y: {
                        ticks: {
                            callback: v => 'R$ ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v.toFixed(0))
                        }
                    }
                }
            }
        });
    }
    
    // ---- RECEITAS POR CATEGORIA (Donut) ----
    const recCat = <?= json_encode($recCatData) ?>;
    const recLabels = Object.keys(recCat);
    const recValues = Object.values(recCat);
    const catColors = ['#22c55e','#3b82f6','#a855f7','#f59e0b','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
    
    if (recLabels.length > 0 && document.getElementById('chartReceitasCat')) {
        new Chart(document.getElementById('chartReceitasCat'), {
            type: 'doughnut',
            data: {
                labels: recLabels,
                datasets: [{
                    data: recValues,
                    backgroundColor: catColors.slice(0, recLabels.length),
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, padding: 8, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ': R$ ' + ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})
                        }
                    }
                }
            }
        });
    }
    
    // ---- DESPESAS POR CATEGORIA (Donut) ----
    const despCat = <?= json_encode($despCatData) ?>;
    const despLabels = Object.keys(despCat);
    const despValues = Object.values(despCat);
    const despColors = ['#ef4444','#f97316','#f59e0b','#a855f7','#3b82f6','#06b6d4','#ec4899','#84cc16','#22c55e','#6366f1'];
    
    if (despLabels.length > 0 && document.getElementById('chartDespesasCat')) {
        new Chart(document.getElementById('chartDespesasCat'), {
            type: 'doughnut',
            data: {
                labels: despLabels,
                datasets: [{
                    data: despValues,
                    backgroundColor: despColors.slice(0, despLabels.length),
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, padding: 8, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ': R$ ' + ctx.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})
                        }
                    }
                }
            }
        });
    }
});
</script>
