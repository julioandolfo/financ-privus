<?php
$b = $boleto;
$situacoes = [
    'em_aberto' => ['label' => 'Em Aberto', 'cor' => 'blue'],
    'liquidado' => ['label' => 'Liquidado', 'cor' => 'green'],
    'baixado' => ['label' => 'Baixado', 'cor' => 'gray'],
    'vencido' => ['label' => 'Vencido', 'cor' => 'red'],
    'protestado' => ['label' => 'Protestado', 'cor' => 'orange'],
    'negativado' => ['label' => 'Negativado', 'cor' => 'purple'],
    'erro' => ['label' => 'Erro', 'cor' => 'red'],
];
$sit = $situacoes[$b['situacao']] ?? ['label' => ucfirst($b['situacao']), 'cor' => 'gray'];
$corBg = [
    'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
][$sit['cor']] ?? 'bg-gray-100 text-gray-700';

$eventIcons = [
    'entrada' => ['icon' => 'M12 4v16m8-8H4', 'cor' => 'blue'],
    'alteracao' => ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'cor' => 'yellow'],
    'liquidacao' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'cor' => 'green'],
    'baixa' => ['icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', 'cor' => 'gray'],
    'protesto' => ['icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', 'cor' => 'orange'],
    'negativacao' => ['icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 'cor' => 'purple'],
    'cancelamento' => ['icon' => 'M6 18L18 6M6 6l12 12', 'cor' => 'red'],
    'erro' => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'cor' => 'red'],
    'prorrogacao' => ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'cor' => 'indigo'],
    'tarifa' => ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'cor' => 'pink'],
];
?>

<div class="max-w-5xl mx-auto" x-data="boletoShow()">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="/boletos?empresa_id=<?= $empresaId ?>" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Boleto #<?= $b['nosso_numero'] ?? $b['id'] ?></h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm"><?= ucfirst($b['banco'] ?? '') ?> - <?= htmlspecialchars($b['conexao_nome'] ?? '') ?></p>
        </div>
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold <?= $corBg ?>"><?= $sit['label'] ?></span>
    </div>

    <!-- Alertas de acao -->
    <div x-show="msg" x-cloak class="mb-4 p-4 rounded-xl" :class="msgTipo === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'">
        <span x-text="msg"></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Dados do Boleto -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Dados do Boleto</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Valor</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">R$ <?= number_format($b['valor'] ?? 0, 2, ',', '.') ?></div>
                    </div>
                    <?php if ($b['valor_recebido']): ?>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Valor Recebido</div>
                        <div class="text-xl font-bold text-green-600">R$ <?= number_format($b['valor_recebido'], 2, ',', '.') ?></div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Emissao</div>
                        <div class="font-medium"><?= date('d/m/Y', strtotime($b['data_emissao'])) ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Vencimento</div>
                        <div class="font-medium <?= strtotime($b['data_vencimento']) < time() && $b['situacao'] !== 'liquidado' ? 'text-red-600' : '' ?>"><?= date('d/m/Y', strtotime($b['data_vencimento'])) ?></div>
                    </div>
                    <?php if ($b['data_pagamento']): ?>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Pagamento</div>
                        <div class="font-medium text-green-600"><?= date('d/m/Y', strtotime($b['data_pagamento'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Nosso Numero</div>
                        <div class="font-mono font-medium"><?= $b['nosso_numero'] ?? '-' ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Seu Numero</div>
                        <div class="font-mono"><?= htmlspecialchars($b['seu_numero'] ?? '-') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Especie</div>
                        <div><?= $b['especie_documento'] ?? 'DM' ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Parcela</div>
                        <div><?= $b['numero_parcela'] ?? 1 ?></div>
                    </div>
                </div>

                <!-- Linha Digitavel -->
                <?php if ($b['linha_digitavel']): ?>
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Linha Digitavel</div>
                    <div class="flex items-center gap-2">
                        <code class="text-sm font-mono break-all"><?= $b['linha_digitavel'] ?></code>
                        <button onclick="navigator.clipboard.writeText('<?= $b['linha_digitavel'] ?>')" title="Copiar" class="p-1 text-blue-500 hover:bg-blue-100 rounded flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Codigo de Barras -->
                <?php if ($b['codigo_barras']): ?>
                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Codigo de Barras</div>
                    <div class="flex items-center gap-2">
                        <code class="text-sm font-mono break-all"><?= $b['codigo_barras'] ?></code>
                        <button onclick="navigator.clipboard.writeText('<?= $b['codigo_barras'] ?>')" title="Copiar" class="p-1 text-blue-500 hover:bg-blue-100 rounded flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- PDF -->
                <?php if ($b['pdf_boleto']): ?>
                <div class="mt-4">
                    <a href="data:application/pdf;base64,<?= $b['pdf_boleto'] ?>" target="_blank" download="boleto_<?= $b['nosso_numero'] ?? $b['id'] ?>.pdf" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Download PDF
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Dados do Pagador -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Pagador</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Nome:</span> <strong><?= htmlspecialchars($b['pagador_nome']) ?></strong></div>
                    <div><span class="text-gray-500">CPF/CNPJ:</span> <strong class="font-mono"><?= $b['pagador_cpf_cnpj'] ?></strong></div>
                    <?php if ($b['pagador_endereco']): ?><div><span class="text-gray-500">Endereco:</span> <?= htmlspecialchars($b['pagador_endereco']) ?></div><?php endif; ?>
                    <?php if ($b['pagador_bairro']): ?><div><span class="text-gray-500">Bairro:</span> <?= htmlspecialchars($b['pagador_bairro']) ?></div><?php endif; ?>
                    <?php if ($b['pagador_cidade']): ?><div><span class="text-gray-500">Cidade:</span> <?= htmlspecialchars($b['pagador_cidade']) ?>/<?= $b['pagador_uf'] ?></div><?php endif; ?>
                    <?php if ($b['pagador_cep']): ?><div><span class="text-gray-500">CEP:</span> <?= $b['pagador_cep'] ?></div><?php endif; ?>
                    <?php if ($b['pagador_email']): ?><div><span class="text-gray-500">Email:</span> <?= htmlspecialchars($b['pagador_email']) ?></div><?php endif; ?>
                    <?php if ($b['cliente_nome_completo']): ?><div class="col-span-2"><span class="text-gray-500">Cliente vinculado:</span> <?= htmlspecialchars($b['cliente_nome_completo']) ?></div><?php endif; ?>
                </div>
            </div>

            <!-- Timeline de Historico -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Historico</h2>
                <?php if (empty($historico)): ?>
                    <p class="text-gray-400 text-sm">Nenhum evento registrado.</p>
                <?php else: ?>
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-4">
                            <?php foreach ($historico as $evt): ?>
                                <?php
                                $ei = $eventIcons[$evt['tipo_evento']] ?? ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'cor' => 'gray'];
                                $dotColor = ['blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'red' => 'bg-red-500', 'gray' => 'bg-gray-400', 'orange' => 'bg-orange-500', 'purple' => 'bg-purple-500', 'yellow' => 'bg-yellow-500', 'indigo' => 'bg-indigo-500', 'pink' => 'bg-pink-500'][$ei['cor']] ?? 'bg-gray-400';
                                ?>
                                <div class="relative pl-10">
                                    <div class="absolute left-2.5 top-1.5 w-3 h-3 rounded-full <?= $dotColor ?> ring-2 ring-white dark:ring-gray-800"></div>
                                    <div class="p-3 bg-gray-50 dark:bg-gray-750 rounded-lg">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= ucfirst($evt['tipo_evento']) ?></span>
                                            <span class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($evt['created_at'])) ?></span>
                                        </div>
                                        <?php if ($evt['descricao']): ?>
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($evt['descricao']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($evt['usuario_nome']): ?>
                                            <p class="text-xs text-gray-400 mt-1">por <?= htmlspecialchars($evt['usuario_nome']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coluna Lateral (Acoes) -->
        <div class="space-y-6">
            <!-- Acoes -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Acoes</h2>
                <div class="space-y-3">
                    <!-- Segunda Via -->
                    <button @click="segundaVia()" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        2a Via / Atualizar PDF
                    </button>

                    <?php if (in_array($b['situacao'], ['em_aberto', 'vencido'])): ?>
                    <!-- Baixar -->
                    <button @click="confirmarAcao('baixar', 'Tem certeza que deseja BAIXAR este boleto?')" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Comandar Baixa
                    </button>

                    <!-- Protestar -->
                    <button @click="confirmarAcao('protestar', 'Tem certeza que deseja PROTESTAR este boleto?')" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded-lg hover:bg-orange-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        Enviar para Protesto
                    </button>

                    <!-- Negativar -->
                    <button @click="confirmarAcao('negativar', 'Tem certeza que deseja NEGATIVAR este pagador (SERASA)?')" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-lg hover:bg-purple-100 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        Enviar para Negativacao
                    </button>
                    <?php endif; ?>

                    <?php if ($b['situacao'] === 'protestado'): ?>
                    <button @click="confirmarAcao('cancelar-protesto', 'Cancelar o protesto deste boleto?')" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-sm font-medium">
                        Cancelar Protesto
                    </button>
                    <?php endif; ?>

                    <?php if ($b['situacao'] === 'negativado'): ?>
                    <button @click="confirmarAcao('cancelar-negativacao', 'Cancelar a negativacao deste boleto?')" :disabled="carregando" class="w-full flex items-center gap-3 px-4 py-2.5 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors text-sm font-medium">
                        Cancelar Negativacao
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Vinculacao -->
            <?php if ($b['pedido_numero'] || $b['conta_receber_id']): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">Vinculos</h2>
                <?php if ($b['pedido_numero']): ?>
                <div class="text-sm mb-2"><span class="text-gray-500">Pedido:</span> <strong>#<?= $b['pedido_numero'] ?></strong> - <?= htmlspecialchars($b['pedido_descricao'] ?? '') ?></div>
                <?php endif; ?>
                <?php if ($b['conta_receber_id']): ?>
                <div class="text-sm"><span class="text-gray-500">Conta a Receber:</span> <a href="/contas-receber/<?= $b['conta_receber_id'] ?>" class="text-blue-600 hover:underline">#<?= $b['conta_receber_id'] ?></a></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Info Tecnica -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-3">Info Tecnica</h2>
                <div class="text-xs space-y-1 text-gray-500">
                    <div>ID: <?= $b['id'] ?></div>
                    <div>Conexao: <?= $b['conexao_bancaria_id'] ?></div>
                    <div>Modalidade: <?= $b['codigo_modalidade'] ?></div>
                    <div>PIX: <?= ['0' => 'Padrao', '1' => 'Com PIX', '2' => 'Sem PIX'][$b['codigo_cadastrar_pix'] ?? 0] ?? '-' ?></div>
                    <div>Criado: <?= date('d/m/Y H:i', strtotime($b['created_at'])) ?></div>
                    <?php if ($b['updated_at']): ?><div>Atualizado: <?= date('d/m/Y H:i', strtotime($b['updated_at'])) ?></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function boletoShow() {
    return {
        carregando: false,
        msg: '',
        msgTipo: 'success',

        async segundaVia() {
            this.carregando = true;
            this.msg = '';
            try {
                const r = await fetch('/boletos/<?= $b['id'] ?>/segunda-via', { method: 'POST', headers: {'Content-Type': 'application/json'} });
                const d = await r.json();
                if (d.success && d.pdf_boleto) {
                    const link = document.createElement('a');
                    link.href = 'data:application/pdf;base64,' + d.pdf_boleto;
                    link.download = 'boleto_<?= $b['nosso_numero'] ?? $b['id'] ?>.pdf';
                    link.click();
                    this.msg = 'PDF gerado com sucesso!';
                    this.msgTipo = 'success';
                } else {
                    this.msg = d.error || 'Erro ao gerar 2a via';
                    this.msgTipo = 'error';
                }
            } catch (e) {
                this.msg = 'Erro: ' + e.message;
                this.msgTipo = 'error';
            }
            this.carregando = false;
        },

        async confirmarAcao(acao, mensagem) {
            if (!confirm(mensagem)) return;
            this.carregando = true;
            this.msg = '';
            try {
                const r = await fetch('/boletos/<?= $b['id'] ?>/' + acao, { method: 'POST', headers: {'Content-Type': 'application/json'} });
                const d = await r.json();
                if (d.success) {
                    this.msg = d.message || 'Acao realizada com sucesso!';
                    this.msgTipo = 'success';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.msg = d.error || 'Erro na acao';
                    this.msgTipo = 'error';
                }
            } catch (e) {
                this.msg = 'Erro: ' + e.message;
                this.msgTipo = 'error';
            }
            this.carregando = false;
        }
    };
}
</script>
