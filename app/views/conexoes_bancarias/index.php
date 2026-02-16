<?php
use App\Models\ConexaoBancaria;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                Conexões Bancárias
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie suas conexões com APIs bancárias diretas</p>
        </div>
        <a href="/conexoes-bancarias/create<?= !empty($empresa_id_selecionada) ? '?empresa_id=' . $empresa_id_selecionada : '' ?>"
           class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Conexão
        </a>
    </div>

    <!-- Seleção de Empresa -->
    <?php if (count($empresas_usuario ?? []) > 0): ?>
    <div class="mb-6">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
            <select name="empresa_id" onchange="this.form.submit()"
                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                <option value="todas" <?= ($empresa_id_selecionada === 'todas') ? 'selected' : '' ?>>
                    Todas as Empresas
                </option>
                <?php foreach ($empresas_usuario as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id'] && $empresa_id_selecionada !== 'todas') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nome_fantasia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Saldo Total -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-green-100 text-sm font-medium">Saldo Total (API)</p>
                    <p class="text-3xl font-bold mt-2">
                        R$ <?= number_format(($saldo_total['saldo_total'] ?? 0), 2, ',', '.') ?>
                    </p>
                </div>
                <div class="p-3 bg-white/20 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-green-100 text-xs mt-3">
                <?= ($saldo_total['total_contas'] ?? 0) ?> conta(s) com saldo |
                <?php if (!empty($saldo_total['saldo_mais_antigo'])): ?>
                    Desde <?= date('d/m H:i', strtotime($saldo_total['saldo_mais_antigo'])) ?>
                <?php else: ?>
                    Sem saldo atualizado
                <?php endif; ?>
            </p>
        </div>

        <!-- Conexões Ativas -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Conexões Ativas</p>
                    <p class="text-3xl font-bold mt-2"><?= count($conexoes ?? []) ?></p>
                </div>
                <div class="p-3 bg-white/20 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </div>
            </div>
            <p class="text-blue-100 text-xs mt-3">
                <?php
                $statusCounts = ['ativa' => 0, 'erro' => 0, 'expirada' => 0];
                foreach ($conexoes as $c) {
                    $status = $c['status_conexao'] ?? 'ativa';
                    if (isset($statusCounts[$status])) $statusCounts[$status]++;
                }
                ?>
                <?= $statusCounts['ativa'] ?> ativa(s) |
                <?php if ($statusCounts['erro'] > 0): ?>
                    <span class="text-yellow-200"><?= $statusCounts['erro'] ?> com erro</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Transações Pendentes -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-amber-100 text-sm font-medium">Transações Pendentes</p>
                    <p class="text-3xl font-bold mt-2"><?= $transacoes_pendentes ?? 0 ?></p>
                </div>
                <div class="p-3 bg-white/20 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <?php if (($transacoes_pendentes ?? 0) > 0): ?>
                <a href="/transacoes-pendentes" class="inline-block mt-3 text-amber-100 text-xs hover:text-white underline">
                    Revisar transações &rarr;
                </a>
            <?php else: ?>
                <p class="text-amber-100 text-xs mt-3">Nenhuma pendência</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (!empty($this->session->get('success'))): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4 mb-6">
            <p class="text-sm text-green-800 dark:text-green-200"><?= $this->session->get('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($this->session->get('error'))): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl p-4 mb-6">
            <p class="text-sm text-red-800 dark:text-red-200"><?= $this->session->get('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- Lista de Conexões -->
    <?php if (empty($conexoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="text-6xl mb-4">🏦</div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Nenhuma conexão bancária</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Conecte suas contas bancárias para sincronizar saldos e extratos automaticamente.
            </p>
            <a href="/conexoes-bancarias/create<?= !empty($empresa_id_selecionada) ? '?empresa_id=' . $empresa_id_selecionada : '' ?>"
               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Criar Primeira Conexão
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($conexoes as $conexao):
                $bancoInfo = ConexaoBancaria::getBancoInfo($conexao['banco']);
                $statusConexao = $conexao['status_conexao'] ?? 'ativa';
                $statusColors = [
                    'ativa' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                    'erro' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                    'expirada' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'desconectada' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                ];
                $statusColor = $statusColors[$statusConexao] ?? $statusColors['ativa'];
                $statusLabels = ['ativa' => 'Ativa', 'erro' => 'Erro', 'expirada' => 'Expirada', 'desconectada' => 'Desconectada'];
            ?>
            <?php
                $saldoContabilCard = $conexao['saldo_banco'] ?? 0;
                $saldoLimiteCard = $conexao['saldo_limite'] ?? 0;
                $saldoDisponivelCard = $saldoContabilCard + $saldoLimiteCard;
                $txFuturasCard = $conexao['tx_futuras'] ?? 0;
                $somaFuturosDebitoCard = $conexao['soma_futuros_debito'] ?? 0;
                $somaFuturosCredCard = $conexao['soma_futuros_credito'] ?? 0;
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow"
                 x-data="{ 
                    saldo: '<?= number_format($saldoContabilCard, 2, ',', '.') ?>', 
                    saldoDisponivel: '<?= number_format($saldoDisponivelCard, 2, ',', '.') ?>',
                    saldoLimite: '<?= number_format($saldoLimiteCard, 2, ',', '.') ?>',
                    txFuturas: <?= (int)$txFuturasCard ?>,
                    somaFuturosDebito: '<?= number_format($somaFuturosDebitoCard, 2, ',', '.') ?>',
                    somaFuturosCredito: <?= (float)$somaFuturosCredCard ?>,
                    saldoDetalhes: <?= ($txFuturasCard > 0) ? 'true' : 'false' ?>,
                    carregando: false, 
                    sincronizando: false,
                    resultadoSync: null,
                    erroSync: null,
                    showResultado: false,
                    showOpcoes: false,
                    dataInicio: new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0],
                    dataFim: new Date().toISOString().split('T')[0],
                    periodoPreset: '7dias'
                 }">
                
                <!-- Header do Card -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <div class="text-3xl"><?= $bancoInfo['logo'] ?></div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($bancoInfo['nome']) ?>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($conexao['identificacao'] ?: ($conexao['banco_conta_id'] ?: 'Conta ' . $conexao['id'])) ?>
                                </p>
                                <?php if (!empty($conexao['empresa_nome'])): ?>
                                <p class="text-xs text-blue-500 dark:text-blue-400 font-medium mt-0.5">
                                    <?= htmlspecialchars($conexao['empresa_nome']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>">
                            <?= $statusLabels[$statusConexao] ?? ucfirst($statusConexao) ?>
                        </span>
                    </div>
                </div>

                <!-- Saldo -->
                <?php
                    $bancosApenasCobranca = ['sicredi', 'bradesco', 'itau'];
                    $somenteCobranca = in_array(strtolower($conexao['banco'] ?? ''), $bancosApenasCobranca);
                ?>
                <div class="px-6 py-4">
                <?php if ($somenteCobranca): ?>
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">Integração de Cobrança</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                    <?= ucfirst($conexao['banco']) ?> não oferece API de conta corrente (saldo/extrato). 
                                    Esta conexão é utilizada apenas para <strong>emissão e gestão de boletos</strong>.
                                </p>
                                <?php if (\Includes\Services\CobrancaServiceFactory::isSuportado($conexao['banco'] ?? '')): ?>
                                <a href="/boletos?conexao_bancaria_id=<?= $conexao['id'] ?>" class="inline-flex items-center gap-1 mt-2 text-xs font-medium text-amber-700 dark:text-amber-300 hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Ver boletos
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Saldo Atual</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                                R$ <span x-text="saldo"></span>
                            </p>
                            <?php if (!empty($conexao['saldo_atualizado_em'])): ?>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Atualizado em <?= date('d/m/Y H:i', strtotime($conexao['saldo_atualizado_em'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <button @click="carregando = true; fetch('/api/conexoes-bancarias/<?= $conexao['id'] ?>/saldo').then(r => r.json()).then(d => { if(d.saldo_formatado) { saldo = d.saldo_formatado.replace('R$ ',''); if(d.saldo_disponivel_formatado) saldoDisponivel = d.saldo_disponivel_formatado.replace('R$ ',''); if(d.saldo_limite_formatado) saldoLimite = d.saldo_limite_formatado.replace('R$ ',''); if(d.tx_futuras !== undefined) txFuturas = d.tx_futuras; if(d.soma_futuros_debito_formatado) somaFuturosDebito = d.soma_futuros_debito_formatado.replace('R$ ',''); if(d.soma_futuros_credito !== undefined) somaFuturosCredito = d.soma_futuros_credito; saldoDetalhes = true; } else if(d.error) alert(d.error); }).catch(e => alert('Erro ao atualizar saldo')).finally(() => carregando = false)"
                                :disabled="carregando"
                                class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition"
                                title="Atualizar saldo">
                            <svg class="w-5 h-5" :class="carregando && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>

                    <?php if (!$somenteCobranca): ?>
                    <!-- Detalhes: Limite + Disponível + Agendamentos -->
                    <?php if ($saldoLimiteCard > 0): ?>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="p-2 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase">Limite</p>
                            <p class="text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="'R$ ' + saldoLimite"></p>
                        </div>
                        <div class="p-2 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase">Disponível</p>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="'R$ ' + saldoDisponivel"></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Agendamentos futuros -->
                    <template x-if="saldoDetalhes && txFuturas > 0">
                        <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200/50 dark:border-amber-800/30 rounded-lg">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-[10px] font-medium text-amber-700 dark:text-amber-300" x-text="txFuturas + ' agendamento(s)'"></p>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <p class="text-[10px] text-amber-600 dark:text-amber-400">
                                    Débito: <span class="font-semibold" x-text="'R$ ' + somaFuturosDebito"></span>
                                </p>
                                <template x-if="somaFuturosCredito > 0">
                                    <p class="text-[10px] text-green-600 dark:text-green-400">
                                        Crédito: <span class="font-semibold" x-text="'R$ ' + Number(somaFuturosCredito).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                    </template>
                    <?php endif; /* if (!$somenteCobranca) - detalhes saldo */ ?>

                    <?php if (!empty($conexao['ultimo_erro'])): ?>
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                            <p class="text-xs text-red-600 dark:text-red-400 truncate" title="<?= htmlspecialchars($conexao['ultimo_erro']) ?>">
                                <?= htmlspecialchars(mb_substr($conexao['ultimo_erro'], 0, 80)) ?>...
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/30 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Tipo:</span>
                        <span class="text-gray-900 dark:text-gray-100 ml-1"><?= ucfirst(str_replace('_', ' ', $conexao['tipo'] ?? 'Conta Corrente')) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Sync:</span>
                        <span class="text-gray-900 dark:text-gray-100 ml-1"><?= ucfirst($conexao['frequencia_sync'] ?? 'manual') ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Ambiente:</span>
                        <span class="text-gray-900 dark:text-gray-100 ml-1"><?= ucfirst($conexao['ambiente'] ?? 'sandbox') ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Última Sync:</span>
                        <span class="text-gray-900 dark:text-gray-100 ml-1">
                            <?= !empty($conexao['ultima_sincronizacao']) ? date('d/m H:i', strtotime($conexao['ultima_sincronizacao'])) : 'Nunca' ?>
                        </span>
                    </div>
                </div>

                <!-- Resultado da Sincronização (inline) -->
                <template x-if="showResultado && resultadoSync">
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-green-50 dark:bg-green-900/20">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-green-800 dark:text-green-300" x-text="resultadoSync.message"></p>
                                <div class="mt-2 grid grid-cols-3 sm:grid-cols-5 gap-1.5">
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
                                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="resultadoSync.resumo?.total_banco ?? 0"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Do banco</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
                                        <p class="text-lg font-bold text-green-600 dark:text-green-400" x-text="resultadoSync.resumo?.novas ?? 0"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Novas</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center">
                                        <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400" x-text="resultadoSync.resumo?.duplicadas ?? 0"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Já pendentes</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center" x-show="(resultadoSync.resumo?.ja_lancadas ?? 0) > 0">
                                        <p class="text-lg font-bold text-purple-600 dark:text-purple-400" x-text="resultadoSync.resumo?.ja_lancadas ?? 0"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Já lançadas</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-2 text-center" x-show="resultadoSync.resumo?.saldo">
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="resultadoSync.resumo?.saldo ?? '---'"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Saldo</p>
                                    </div>
                                </div>
                                <template x-if="resultadoSync.detalhes && resultadoSync.detalhes.length > 0">
                                    <details class="mt-2">
                                        <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                            Ver detalhes
                                        </summary>
                                        <ul class="mt-1 space-y-0.5">
                                            <template x-for="detalhe in resultadoSync.detalhes" :key="detalhe">
                                                <li class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                                    <span class="w-1 h-1 bg-gray-400 rounded-full flex-shrink-0"></span>
                                                    <span x-text="detalhe"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </details>
                                </template>
                            </div>
                            <button @click="showResultado = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Resultado de Erro (inline) -->
                <template x-if="showResultado && erroSync">
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-red-50 dark:bg-red-900/20">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300">Erro na sincronização</p>
                                <pre class="text-xs text-red-600 dark:text-red-400 mt-1 whitespace-pre-wrap break-words font-sans" x-text="erroSync"></pre>
                            </div>
                            <button @click="showResultado = false; erroSync = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <?php if (!$somenteCobranca): ?>
                <!-- Opções de Sincronização -->
                <template x-if="showOpcoes">
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Período de Sincronização</p>
                            <button @click="showOpcoes = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Presets rápidos -->
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <button @click="periodoPreset='hoje'; dataInicio=dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='hoje' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">Hoje</button>
                            <button @click="periodoPreset='7dias'; dataInicio=new Date(Date.now()-7*86400000).toISOString().split('T')[0]; dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='7dias' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">7 dias</button>
                            <button @click="periodoPreset='15dias'; dataInicio=new Date(Date.now()-15*86400000).toISOString().split('T')[0]; dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='15dias' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">15 dias</button>
                            <button @click="periodoPreset='30dias'; dataInicio=new Date(Date.now()-30*86400000).toISOString().split('T')[0]; dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='30dias' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">30 dias</button>
                            <button @click="periodoPreset='60dias'; dataInicio=new Date(Date.now()-60*86400000).toISOString().split('T')[0]; dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='60dias' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">60 dias</button>
                            <button @click="periodoPreset='90dias'; dataInicio=new Date(Date.now()-90*86400000).toISOString().split('T')[0]; dataFim=new Date().toISOString().split('T')[0]"
                                    :class="periodoPreset==='90dias' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">90 dias</button>
                            <button @click="periodoPreset='custom'"
                                    :class="periodoPreset==='custom' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg transition">Personalizado</button>
                        </div>
                        
                        <!-- Campos de data -->
                        <div class="grid grid-cols-2 gap-2" x-show="periodoPreset==='custom'" x-transition>
                            <div>
                                <label class="text-[10px] text-gray-500 dark:text-gray-400">De</label>
                                <input type="date" x-model="dataInicio"
                                       class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 dark:text-gray-400">Até</label>
                                <input type="date" x-model="dataFim"
                                       class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>
                        
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">
                            Máx. 90 dias (limitação da API). Transações já lançadas em contas a pagar/receber serão ignoradas automaticamente.
                        </p>
                    </div>
                </template>

                <?php endif; /* if (!$somenteCobranca) - Opções de Sincronização */ ?>

                <!-- Ações -->
                <div class="px-6 py-4 flex gap-2 flex-wrap"
                     x-data="{ importandoExtrato: false, resultadoExtrato: null, erroExtrato: null, showResultadoExtrato: false }">
                    <?php if (!$somenteCobranca): ?>
                    <button @click="
                        sincronizando = true; 
                        showResultado = false; 
                        resultadoSync = null; 
                        erroSync = null;
                        fetch('/conexoes-bancarias/<?= $conexao['id'] ?>/sincronizar', {
                            method:'POST', 
                            headers:{
                                'X-Requested-With':'XMLHttpRequest',
                                'Content-Type':'application/json'
                            },
                            body: JSON.stringify({
                                data_inicio: dataInicio,
                                data_fim: dataFim
                            })
                        })
                        .then(r => r.json())
                        .then(d => { 
                            if(d.success) { 
                                resultadoSync = d; 
                                if(d.resumo && d.resumo.saldo) { 
                                    saldo = d.resumo.saldo.replace('R$ ', ''); 
                                }
                            } else { 
                                erroSync = d.error || 'Erro desconhecido';
                                if(d.detalhes) erroSync += '\n\nDetalhes:\n' + d.detalhes.join('\n');
                            }
                            showResultado = true;
                        })
                        .catch(e => { 
                            erroSync = 'Erro de comunicação com o servidor: ' + e.message; 
                            showResultado = true; 
                        })
                        .finally(() => sincronizando = false)
                    "
                            :disabled="sincronizando || importandoExtrato"
                            class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" :class="sincronizando && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="sincronizando ? 'Sincronizando...' : 'Sincronizar'"></span>
                    </button>
                    
                    <!-- Importar Extrato Completo (apenas visualização) -->
                    <button @click="
                        importandoExtrato = true; 
                        showResultadoExtrato = false; 
                        resultadoExtrato = null; 
                        erroExtrato = null;
                        fetch('/conexoes-bancarias/<?= $conexao['id'] ?>/importar-extrato', {
                            method:'POST', 
                            headers:{
                                'X-Requested-With':'XMLHttpRequest',
                                'Content-Type':'application/json'
                            },
                            body: JSON.stringify({
                                data_inicio: dataInicio,
                                data_fim: dataFim
                            })
                        })
                        .then(r => r.json())
                        .then(d => { 
                            if(d.success) { 
                                resultadoExtrato = d;
                            } else { 
                                erroExtrato = d.error || 'Erro desconhecido';
                                if(d.detalhes) erroExtrato += '\n\nDetalhes:\n' + d.detalhes.join('\n');
                            }
                            showResultadoExtrato = true;
                        })
                        .catch(e => { 
                            erroExtrato = 'Erro: ' + e.message; 
                            showResultadoExtrato = true; 
                        })
                        .finally(() => importandoExtrato = false)
                    "
                            :disabled="sincronizando || importandoExtrato"
                            class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2"
                            title="Importar extrato completo (créditos e débitos) apenas para visualização">
                        <svg class="w-4 h-4" :class="importandoExtrato && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span x-text="importandoExtrato ? 'Importando...' : 'Extrato'"></span>
                    </button>
                    
                    <!-- Resultado da Importação de Extrato -->
                    <template x-if="showResultadoExtrato">
                        <div class="w-full mt-2 rounded-xl p-3" :class="erroExtrato ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800'">
                            <template x-if="resultadoExtrato">
                                <div>
                                    <p class="text-sm font-semibold text-purple-800 dark:text-purple-200 mb-2" x-text="resultadoExtrato.message"></p>
                                    <div class="grid grid-cols-4 gap-2 text-center mb-2">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
                                            <p class="text-lg font-bold text-purple-600" x-text="resultadoExtrato.resumo?.total || 0"></p>
                                            <p class="text-[10px] text-gray-500">Total</p>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
                                            <p class="text-lg font-bold text-red-600" x-text="resultadoExtrato.resumo?.debitos || 0"></p>
                                            <p class="text-[10px] text-gray-500">Débitos</p>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
                                            <p class="text-lg font-bold text-green-600" x-text="resultadoExtrato.resumo?.creditos || 0"></p>
                                            <p class="text-[10px] text-gray-500">Créditos</p>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-2">
                                            <p class="text-lg font-bold text-blue-600" x-text="resultadoExtrato.resumo?.novas || 0"></p>
                                            <p class="text-[10px] text-gray-500">Novas</p>
                                        </div>
                                    </div>
                                    <a href="/extrato-api?conexao_bancaria_id=<?= $conexao['id'] ?>" 
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 dark:text-purple-300 hover:underline">
                                        Ver extrato completo →
                                    </a>
                                </div>
                            </template>
                            <template x-if="erroExtrato">
                                <div>
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-300">Erro ao importar extrato</p>
                                    <pre class="text-xs text-red-600 dark:text-red-400 mt-1 whitespace-pre-wrap" x-text="erroExtrato"></pre>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <button @click="showOpcoes = !showOpcoes" 
                            :class="showOpcoes ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="px-3 py-2.5 hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-semibold rounded-xl transition" title="Opções de período">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                    <a href="/extrato-api?conexao_bancaria_id=<?= $conexao['id'] ?>" 
                       class="px-4 py-2.5 bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-sm font-semibold rounded-xl transition"
                       title="Ver extrato importado">
                        Ver Extrato
                    </a>
                    <?php endif; /* if (!$somenteCobranca) - botões de sync/extrato */ ?>
                    <a href="/conexoes-bancarias/<?= $conexao['id'] ?>" 
                       class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl transition">
                        Detalhes
                    </a>
                    <?php if ($somenteCobranca && \Includes\Services\CobrancaServiceFactory::isSuportado($conexao['banco'] ?? '')): ?>
                    <a href="/boletos/criar?conexao_bancaria_id=<?= $conexao['id'] ?>" 
                       class="px-4 py-2.5 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 text-sm font-semibold rounded-xl transition">
                        Emitir Boleto
                    </a>
                    <?php endif; ?>
                    <button @click="fetch('/conexoes-bancarias/<?= $conexao['id'] ?>/testar', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r => r.json()).then(d => alert(d.message || d.error)).catch(e => alert('Erro ao testar'))"
                            class="px-4 py-2.5 bg-indigo-100 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-semibold rounded-xl transition">
                        Testar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$this->session->delete('success');
$this->session->delete('error');
?>
