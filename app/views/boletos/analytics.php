<?php
$kpis = $analytics['kpis'] ?? [];
$evolucao = $analytics['evolucao_mensal'] ?? [];
$distribuicao = $analytics['distribuicao_situacao'] ?? [];
$topInad = $analytics['top_inadimplentes'] ?? [];
$est = $estatisticas ?? [];
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Analytics de Boletos</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Visao completa da carteira de cobranca</p>
        </div>
        <a href="/boletos?empresa_id=<?= $empresaId ?>" class="inline-flex items-center px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 transition-all text-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Voltar para Boletos
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/boletos/analytics" class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">De</label>
                <input type="date" name="periodo_inicio" value="<?= $periodoInicio ?? '' ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ate</label>
                <input type="date" name="periodo_fim" value="<?= $periodoFim ?? '' ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Banco</label>
                <select name="conexao_bancaria_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                    <option value="">Todos</option>
                    <?php foreach ($conexoes as $cx): ?>
                        <option value="<?= $cx['id'] ?>" <?= ($conexaoId ?? '') == $cx['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cx['identificacao'] ?? $cx['banco']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase">Total Emitido</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">R$ <?= number_format($kpis['valor_total_emitido'] ?? 0, 2, ',', '.') ?></div>
            <div class="text-xs text-gray-400 mt-1"><?= number_format($kpis['total_emitidos'] ?? 0) ?> boleto(s)</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs font-semibold text-green-600 uppercase">Total Recebido</div>
            <div class="text-2xl font-bold text-green-600 mt-1">R$ <?= number_format($kpis['valor_total_recebido'] ?? 0, 2, ',', '.') ?></div>
            <div class="text-xs text-gray-400 mt-1"><?= number_format($kpis['total_liquidados'] ?? 0) ?> liquidado(s)</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs font-semibold text-red-600 uppercase">Inadimplencia</div>
            <div class="text-2xl font-bold text-red-600 mt-1"><?= number_format($kpis['taxa_inadimplencia'] ?? 0, 1) ?>%</div>
            <div class="text-xs text-gray-400 mt-1">R$ <?= number_format($kpis['valor_inadimplente'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase">Ticket Medio</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">R$ <?= number_format($kpis['ticket_medio'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase">Prazo Medio Receb.</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1"><?= number_format($kpis['prazo_medio_recebimento'] ?? 0, 0) ?> dias</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Grafico: Evolucao Mensal -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Evolucao Mensal</h3>
            <canvas id="chartEvolucao" height="250"></canvas>
        </div>

        <!-- Grafico: Distribuicao por Situacao -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Distribuicao por Situacao</h3>
            <canvas id="chartDistribuicao" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Inadimplentes -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Top 10 Inadimplentes</h3>
            <?php if (empty($topInad)): ?>
                <p class="text-gray-400 text-sm">Nenhum inadimplente encontrado.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topInad as $i => $inad): ?>
                    <div class="flex items-center justify-between p-3 bg-red-50/50 dark:bg-red-900/10 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold"><?= $i + 1 ?></span>
                            <div>
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($inad['pagador_nome']) ?></div>
                                <div class="text-xs text-gray-500"><?= $inad['pagador_cpf_cnpj'] ?> | <?= $inad['qtd_boletos'] ?> boleto(s)</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-red-600">R$ <?= number_format($inad['valor_total'], 2, ',', '.') ?></div>
                            <div class="text-xs text-gray-400">desde <?= date('d/m/Y', strtotime($inad['vencimento_mais_antigo'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ultimos Eventos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Ultimos Eventos</h3>
            <?php if (empty($ultimosEventos)): ?>
                <p class="text-gray-400 text-sm">Nenhum evento registrado.</p>
            <?php else: ?>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <?php foreach ($ultimosEventos as $evt): ?>
                    <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-750 rounded-lg text-sm">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                <?php
                                $evtCores = ['entrada' => 'bg-blue-100 text-blue-700', 'liquidacao' => 'bg-green-100 text-green-700', 'baixa' => 'bg-gray-100 text-gray-700', 'protesto' => 'bg-orange-100 text-orange-700', 'negativacao' => 'bg-purple-100 text-purple-700', 'erro' => 'bg-red-100 text-red-700', 'cancelamento' => 'bg-yellow-100 text-yellow-700', 'alteracao' => 'bg-indigo-100 text-indigo-700'];
                                echo $evtCores[$evt['tipo_evento']] ?? 'bg-gray-100 text-gray-700';
                                ?>
                            "><?= ucfirst($evt['tipo_evento']) ?></span>
                            <span class="ml-2 text-gray-600 dark:text-gray-400">Boleto #<?= $evt['nosso_numero'] ?? '' ?></span>
                            <span class="ml-1 text-gray-400">- <?= htmlspecialchars(substr($evt['pagador_nome'] ?? '', 0, 30)) ?></span>
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0"><?= date('d/m H:i', strtotime($evt['created_at'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Boletos Vencidos -->
    <?php if (!empty($inadimplentes)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-red-600 mb-4">Boletos Vencidos (Inadimplentes)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Nosso N.</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Pagador</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Valor</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Vencimento</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Dias Atraso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($inadimplentes as $inad): ?>
                    <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10">
                        <td class="px-3 py-2"><a href="/boletos/<?= $inad['id'] ?>" class="font-mono text-blue-600 hover:underline"><?= $inad['nosso_numero'] ?></a></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($inad['pagador_nome']) ?></td>
                        <td class="px-3 py-2 font-semibold">R$ <?= number_format($inad['valor'], 2, ',', '.') ?></td>
                        <td class="px-3 py-2 text-red-600"><?= date('d/m/Y', strtotime($inad['data_vencimento'])) ?></td>
                        <td class="px-3 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"><?= $inad['dias_atraso'] ?> dias</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9CA3AF' : '#6B7280';
    const gridColor = isDark ? 'rgba(75,85,99,0.3)' : 'rgba(229,231,235,0.8)';

    // Evolucao Mensal
    const evolucao = <?= json_encode($evolucao) ?>;
    if (evolucao.length > 0) {
        new Chart(document.getElementById('chartEvolucao'), {
            type: 'bar',
            data: {
                labels: evolucao.map(e => e.mes),
                datasets: [
                    { label: 'Emitido (R$)', data: evolucao.map(e => parseFloat(e.valor_emitido)), backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4 },
                    { label: 'Recebido (R$)', data: evolucao.map(e => parseFloat(e.valor_recebido)), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: textColor }, grid: { display: false } },
                    y: { ticks: { color: textColor, callback: v => 'R$ ' + v.toLocaleString('pt-BR') }, grid: { color: gridColor } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            }
        });
    }

    // Distribuicao
    const dist = <?= json_encode($distribuicao) ?>;
    const sitCores = { em_aberto: '#3B82F6', liquidado: '#10B981', baixado: '#9CA3AF', vencido: '#EF4444', protestado: '#F97316', negativado: '#8B5CF6', erro: '#DC2626' };
    if (dist.length > 0) {
        new Chart(document.getElementById('chartDistribuicao'), {
            type: 'doughnut',
            data: {
                labels: dist.map(d => d.situacao.replace('_', ' ')),
                datasets: [{
                    data: dist.map(d => parseInt(d.quantidade)),
                    backgroundColor: dist.map(d => sitCores[d.situacao] || '#6B7280'),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: textColor, padding: 15 } }
                }
            }
        });
    }
});
</script>
