<?php
use App\Models\ConexaoBancaria;
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

// Calcular saldo contábil: saldo_banco já inclui o limite no Sicoob
$saldoBanco = $conexao['saldo_banco'] ?? 0;
$saldoLimiteVal = $conexao['saldo_limite'] ?? 0;
$saldoContabilVal = $saldoBanco - $saldoLimiteVal;
?>

<div class="max-w-5xl mx-auto" x-data="{
    saldo: '<?= $conexao['saldo_banco'] !== null ? number_format($conexao['saldo_banco'], 2, ',', '.') : '---' ?>',
    saldoContabil: '<?= number_format($saldoContabilVal, 2, ',', '.') ?>',
    saldoLimite: '<?= number_format($saldoLimiteVal, 2, ',', '.') ?>',
    dataReferencia: '',
    totalTransacoes: 0,
    txFuturas: 0,
    somaFuturosDebito: '',
    carregando: false,
    sincronizando: false,
    saldoCarregado: false,
    atualizadoEm: '<?= !empty($conexao['saldo_atualizado_em']) ? 'Atualizado em ' . date('d/m/Y H:i', strtotime($conexao['saldo_atualizado_em'])) : '' ?>',
    atualizarSaldo() {
        this.carregando = true;
        fetch('/api/conexoes-bancarias/<?= $conexao['id'] ?>/saldo')
            .then(r => r.json())
            .then(d => {
                if(d.saldo_formatado) {
                    this.saldo = d.saldo_formatado.replace('R$ ','');
                    if(d.saldo_contabil_formatado) this.saldoContabil = d.saldo_contabil_formatado.replace('R$ ','');
                    if(d.saldo_limite_formatado) this.saldoLimite = d.saldo_limite_formatado.replace('R$ ','');
                    if(d.data_referencia_formatada) this.dataReferencia = d.data_referencia_formatada;
                    if(d.total_transacoes !== undefined) this.totalTransacoes = d.total_transacoes;
                    if(d.tx_futuras !== undefined) this.txFuturas = d.tx_futuras;
                    if(d.soma_futuros_debito_formatado) this.somaFuturosDebito = d.soma_futuros_debito_formatado.replace('R$ ','');
                    let agora = new Date();
                    this.atualizadoEm = 'Atualizado em ' + agora.toLocaleDateString('pt-BR') + ' ' + agora.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
                    this.saldoCarregado = true;
                    if(d.conta_criada) { alert(d.message || 'Conta bancária criada e vinculada!'); location.reload(); }
                } else if(d.error) { if(!this.saldoCarregado) this.saldoCarregado = true; }
            })
            .catch(e => { if(!this.saldoCarregado) this.saldoCarregado = true; })
            .finally(() => this.carregando = false);
    },
    init() { this.atualizarSaldo(); }
}" x-init="init()">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="/conexoes-bancarias" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Conexões
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-5">
                <div class="text-5xl"><?= $bancoInfo['logo'] ?></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($bancoInfo['nome']) ?>
                        <?php if (!empty($conexao['identificacao'])): ?>
                            <span class="text-gray-400 font-normal">- <?= htmlspecialchars($conexao['identificacao']) ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        <?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?> |
                        <?= ucfirst(str_replace('_', ' ', $conexao['tipo'] ?? 'Conta Corrente')) ?>
                        <?php if (!empty($conexao['banco_conta_id'])): ?>
                            | Conta: <?= htmlspecialchars($conexao['banco_conta_id']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 text-sm font-semibold rounded-full <?= $statusColor ?>">
                    <?= $statusLabels[$statusConexao] ?? ucfirst($statusConexao) ?>
                </span>
                <span class="px-4 py-2 text-sm font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                    <?= ucfirst($conexao['ambiente'] ?? 'sandbox') ?>
                </span>
            </div>
        </div>

        <!-- Saldo em Destaque -->
        <div class="mt-8 p-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/50 dark:to-gray-900/30 rounded-2xl">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex-1">
                    <!-- Saldo da conta (dinheiro real, sem limite) -->
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Saldo da Conta</p>
                    <p class="text-4xl font-bold mt-2" :class="parseFloat(saldoContabil.replace('.','').replace(',','.')) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                        R$ <span x-text="saldoContabil"></span>
                    </p>

                    <!-- Detalhes -->
                    <template x-if="saldoCarregado">
                        <div class="mt-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-2.5 bg-white/60 dark:bg-gray-800/40 rounded-lg">
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Limite de crédito</p>
                                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-0.5" x-text="'R$ ' + saldoLimite"></p>
                                </div>
                                <div class="p-2.5 bg-white/60 dark:bg-gray-800/40 rounded-lg">
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Total disponível</p>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mt-0.5" x-text="'R$ ' + saldo"></p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">saldo + limite</p>
                                </div>
                            </div>

                            <!-- Info: agendamentos futuros detectados -->
                            <template x-if="txFuturas > 0">
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-amber-700 dark:text-amber-300" x-text="txFuturas + ' despesa(s) agendada(s) para o próximo dia útil'"></p>
                                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                                Total a debitar: <span class="font-semibold" x-text="'R$ ' + somaFuturosDebito"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Nota: API D-1 -->
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed">
                                Saldo via API Sicoob (D-1): reflete o fechamento do último dia útil. Pode haver pequenas diferenças em relação ao app do banco até o próximo processamento.
                            </p>
                        </div>
                    </template>

                    <!-- Loading placeholder -->
                    <template x-if="!saldoCarregado && carregando">
                        <div class="mt-4 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Carregando detalhes do saldo...</span>
                        </div>
                    </template>

                    <!-- Metadados -->
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <p class="text-xs text-gray-400 dark:text-gray-500" x-text="atualizadoEm">
                            <?= !empty($conexao['saldo_atualizado_em']) ? 'Atualizado em ' . date('d/m/Y H:i', strtotime($conexao['saldo_atualizado_em'])) : '' ?>
                        </p>
                        <template x-if="dataReferencia">
                            <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full" x-text="'Ref. ' + dataReferencia"></span>
                        </template>
                        <template x-if="totalTransacoes > 0">
                            <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-full" x-text="totalTransacoes + ' transações no mês'"></span>
                        </template>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button @click="atualizarSaldo()"
                            :disabled="carregando"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition flex items-center gap-2">
                        <svg class="w-4 h-4" :class="carregando && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="carregando ? 'Atualizando...' : 'Atualizar Saldo'"></span>
                    </button>
                    <button @click="sincronizando = true; fetch('/conexoes-bancarias/<?= $conexao['id'] ?>/sincronizar', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r => r.json()).then(d => { if(d.success) { alert(d.message); location.reload(); } else alert(d.error || 'Erro'); }).catch(e => alert('Erro')).finally(() => sincronizando = false)"
                            :disabled="sincronizando"
                            class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition flex items-center gap-2">
                        <svg class="w-4 h-4" :class="sincronizando && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="sincronizando ? 'Sincronizando...' : 'Sincronizar Extrato'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Informações da Conexão -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Informações da Conexão</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Banco</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($bancoInfo['nome']) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo Integração</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= ucfirst(str_replace('_', ' ', $conexao['tipo_integracao'] ?? 'API Direta')) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Ambiente</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= ucfirst($conexao['ambiente'] ?? 'sandbox') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Client ID</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= !empty($conexao['client_id']) ? mb_substr($conexao['client_id'], 0, 10) . '...' : 'N/A' ?></dd>
                </div>
                <?php if (!empty($conexao['banco_conta_id'])): ?>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Conta no Banco</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conexao['banco_conta_id']) ?></dd>
                </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Criado em</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($conexao['created_at'])) ?></dd>
                </div>
            </dl>

            <!-- Ações -->
            <div class="mt-6 flex gap-2">
                <a href="/conexoes-bancarias/<?= $conexao['id'] ?>/edit" class="flex-1 text-center px-4 py-2.5 bg-indigo-100 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-semibold rounded-xl transition">
                    Editar
                </a>
                <form action="/conexoes-bancarias/<?= $conexao['id'] ?>/delete" method="POST" class="flex-1"
                      onsubmit="return confirm('Deseja realmente desativar esta conexão?')">
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 text-sm font-semibold rounded-xl transition">
                        Desativar
                    </button>
                </form>
            </div>
        </div>

        <!-- Configurações de Sincronização -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Sincronização</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Auto Sync</dt>
                    <dd class="text-sm font-medium">
                        <?php if ($conexao['auto_sync']): ?>
                            <span class="text-green-600 dark:text-green-400">Ativa</span>
                        <?php else: ?>
                            <span class="text-gray-400">Desativada</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Frequência</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= ucfirst($conexao['frequencia_sync'] ?? 'manual') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Última Sincronização</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        <?= !empty($conexao['ultima_sincronizacao']) ? date('d/m/Y H:i', strtotime($conexao['ultima_sincronizacao'])) : 'Nunca' ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Aprovação Automática</dt>
                    <dd class="text-sm font-medium">
                        <?= $conexao['aprovacao_automatica'] ? '<span class="text-green-600 dark:text-green-400">Sim</span>' : '<span class="text-gray-400">Não</span>' ?>
                    </dd>
                </div>
            </dl>

            <?php if (!empty($conexao['ultimo_erro'])): ?>
            <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-700">
                <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">Último Erro:</p>
                <p class="text-xs text-red-600 dark:text-red-400"><?= htmlspecialchars($conexao['ultimo_erro']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Testar -->
            <div class="mt-6">
                <button @click="fetch('/conexoes-bancarias/<?= $conexao['id'] ?>/testar', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r => r.json()).then(d => alert(d.message || d.error)).catch(e => alert('Erro ao testar'))"
                        class="w-full px-4 py-2.5 bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-sm font-semibold rounded-xl transition">
                    Testar Conexão
                </button>
            </div>
        </div>
    </div>

    <!-- Últimas Transações Importadas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Últimas Transações Importadas</h2>
            <?php if (!empty($ultimas_transacoes)): ?>
                <a href="/transacoes-pendentes" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todas &rarr;</a>
            <?php endif; ?>
        </div>

        <?php if (empty($ultimas_transacoes)): ?>
            <div class="p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">Nenhuma transação importada ainda. Clique em "Sincronizar Extrato" para buscar.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($ultimas_transacoes as $txn): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                <?= date('d/m/Y', strtotime($txn['data_transacao'])) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                <?= htmlspecialchars($txn['descricao_original']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <?= htmlspecialchars($txn['metodo_pagamento'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-right whitespace-nowrap <?= ($txn['tipo'] ?? '') === 'debito' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                                <?= ($txn['tipo'] ?? '') === 'debito' ? '-' : '+' ?> R$ <?= number_format($txn['valor'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                $statusTxn = $txn['status'] ?? 'pendente';
                                $statusTxnColors = [
                                    'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'aprovada' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'ignorada' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                ];
                                ?>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $statusTxnColors[$statusTxn] ?? $statusTxnColors['pendente'] ?>">
                                    <?= ucfirst($statusTxn) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
