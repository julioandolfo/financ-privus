<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';

$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
$saldoRestante = $conta['valor_total'] - $conta['valor_pago'];
?>

<div class="max-w-6xl mx-auto animate-fade-in" x-data="{
    showBaixaModal: <?= !empty($openBaixaModal) ? 'true' : 'false' ?>,
    showCancelModal: false,
    saldoRestante: <?= (float) $saldoRestante ?>,
    valorPagamento: <?= isset($old['valor_pagamento']) ? (float) $old['valor_pagamento'] : (float) $saldoRestante ?>
}" @keydown.escape.window="showBaixaModal = false; showCancelModal = false">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Detalhes da Conta a Pagar</h1>
                <p class="text-red-100">Visualize todas as informações da despesa</p>
            </div>
            <div class="flex items-center space-x-3">
                <?php if ($conta['status'] != 'pago' && $conta['status'] != 'cancelado'): ?>
                    <a href="/contas-pagar/<?= $conta['id'] ?>?acao=baixar" @click.prevent="showBaixaModal = true" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Baixar/Pagar</span>
                    </a>
                <?php endif; ?>
                <?php if ($conta['status'] == 'pago' || $conta['status'] == 'parcial'): ?>
                    <button @click="showCancelModal = true" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        <span>Cancelar Pagamento</span>
                    </button>
                <?php endif; ?>
                <?php if ($conta['status'] != 'cancelado'): ?>
                    <a href="/contas-pagar/<?= $conta['id'] ?>/edit" class="bg-white text-red-600 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                <?php endif; ?>
                <a href="/contas-pagar/<?= $conta['id'] ?>/historico" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Histórico</span>
                </a>
                <a href="/contas-pagar" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Informações Gerais -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Informações Gerais</h2>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Empresa</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['empresa_nome']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fornecedor</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['fornecedor_nome'] ?? 'Não informado') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Categoria</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['categoria_nome']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Centro de Custo</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['centro_custo_nome'] ?? 'Não informado') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tipo de Custo</label>
                        <?php 
                        $tipoCusto = $conta['tipo_custo'] ?? 'variavel';
                        $badgeClass = $tipoCusto === 'fixo' 
                            ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' 
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                        ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badgeClass ?>">
                            <?= $tipoCusto === 'fixo' ? 'Custo Fixo' : 'Custo Variável' ?>
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Número do Documento</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['numero_documento']) ?></p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Descrição</label>
                        <p class="text-base text-gray-900 dark:text-gray-100"><?= nl2br(htmlspecialchars($conta['descricao'])) ?></p>
                    </div>
                    
                    <?php if (!empty($conta['observacoes'])): ?>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Observações</label>
                        <p class="text-base text-gray-600 dark:text-gray-400"><?= nl2br(htmlspecialchars($conta['observacoes'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Datas -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Datas</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Emissão</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= formatarData($conta['data_emissao']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Competência</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= formatarData($conta['data_competencia']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Vencimento</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 <?= estaVencido($conta['data_vencimento']) && $conta['status'] != 'pago' ? 'text-red-600' : '' ?>">
                            <?= formatarData($conta['data_vencimento']) ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($conta['data_pagamento'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Pagamento</label>
                        <p class="text-lg font-semibold text-green-600"><?= formatarData($conta['data_pagamento']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parcelamento -->
            <?php if (!empty($resumoParcelas)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                    <span class="inline-flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Parcelamento
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            (Parcela <?= $conta['parcela_numero'] ?> de <?= $conta['total_parcelas'] ?>)
                        </span>
                    </span>
                </h2>
                
                <!-- Resumo -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total do Parcelamento</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($resumoParcelas['valor_total']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Pago</p>
                        <p class="text-xl font-bold text-green-600"><?= formatarMoeda($resumoParcelas['valor_pago']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Restante</p>
                        <p class="text-xl font-bold text-red-600"><?= formatarMoeda($resumoParcelas['valor_restante']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Progresso</p>
                        <p class="text-xl font-bold text-indigo-600"><?= $resumoParcelas['parcelas_pagas'] ?>/<?= $resumoParcelas['total_parcelas'] ?></p>
                    </div>
                </div>
                
                <!-- Lista de Parcelas -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Parcela</th>
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Vencimento</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Valor</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($resumoParcelas['parcelas'] as $parcela): ?>
                            <tr class="<?= $parcela['id'] == $conta['id'] ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' ?>">
                                <td class="py-3 text-gray-900 dark:text-gray-100">
                                    <span class="font-semibold"><?= $parcela['parcela_numero'] ?>/<?= $parcela['total_parcelas'] ?></span>
                                    <?php if ($parcela['id'] == $conta['id']): ?>
                                        <span class="ml-2 text-xs bg-indigo-600 text-white px-2 py-0.5 rounded">Atual</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-gray-600 dark:text-gray-400">
                                    <?= formatarData($parcela['data_vencimento']) ?>
                                    <?php if (estaVencido($parcela['data_vencimento']) && $parcela['status'] != 'pago'): ?>
                                        <span class="ml-1 text-red-600 text-xs font-bold">VENCIDA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($parcela['valor_total']) ?></td>
                                <td class="py-3 text-center"><?= formatarStatusBadge($parcela['status']) ?></td>
                                <td class="py-3 text-center">
                                    <?php if ($parcela['id'] != $conta['id']): ?>
                                        <a href="/contas-pagar/<?= $parcela['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Ver
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rateios -->
            <?php if (!empty($rateios)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Rateio entre Empresas</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Empresa</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Valor</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Percentual</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Competência</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($rateios as $rateio): ?>
                            <tr>
                                <td class="py-3 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($rateio['empresa_nome']) ?></td>
                                <td class="py-3 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($rateio['valor_rateio']) ?></td>
                                <td class="py-3 text-right text-gray-600 dark:text-gray-400"><?= formatarPercentual($rateio['percentual']) ?></td>
                                <td class="py-3 text-center text-gray-600 dark:text-gray-400"><?= formatarData($rateio['data_competencia']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Coluna Lateral -->
        <div class="space-y-8">
            <!-- Card de Status e Valores -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Status e Valores</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Status</label>
                        <?= formatarStatusBadge($conta['status']) ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Total</label>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($conta['valor_total']) ?></p>
                    </div>
                    
                    <?php if ($conta['status'] == 'parcial'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Pago</label>
                        <p class="text-2xl font-bold text-green-600"><?= formatarMoeda($conta['valor_pago']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Saldo Restante</label>
                        <p class="text-2xl font-bold text-red-600"><?= formatarMoeda($conta['valor_total'] - $conta['valor_pago']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($conta['status'] == 'pago'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Pago</label>
                        <p class="text-2xl font-bold text-green-600"><?= formatarMoeda($conta['valor_pago']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico de Pagamentos -->
            <?php if (!empty($movimentacoes)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Histórico de Pagamentos</h3>
                
                <div class="space-y-4">
                    <?php foreach ($movimentacoes as $mov): ?>
                    <div class="flex justify-between items-start p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($mov['valor']) ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= formatarData($mov['data_movimento']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500"><?= htmlspecialchars($mov['forma_pagamento']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($mov['conta_bancaria']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Auditoria -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Auditoria</h3>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 mb-1">Criado em</label>
                        <p class="text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($conta['created_at'])) ?></p>
                    </div>
                    
                    <?php if (!empty($conta['updated_at'])): ?>
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 mb-1">Atualizado em</label>
                        <p class="text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($conta['updated_at'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Baixa -->
    <div x-show="showBaixaModal" x-transition.opacity x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/50" @click="showBaixaModal = false"></div>
        <div class="relative w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Baixar/Pagar Conta</h2>
                        <p class="text-green-100 mt-1">Registre o pagamento da despesa</p>
                    </div>
                    <button type="button" class="text-white/80 hover:text-white" @click="showBaixaModal = false" aria-label="Fechar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>/baixar" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fornecedor</label>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['fornecedor_nome'] ?? 'Não informado') ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Documento</label>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['numero_documento']) ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Saldo Restante</label>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($saldoRestante) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data do Pagamento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_pagamento" value="<?= $old['data_pagamento'] ?? date('Y-m-d') ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_pagamento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['data_pagamento'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['data_pagamento'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Valor do Pagamento <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="valor_pagamento" x-model="valorPagamento" step="0.01" min="0.01" max="<?= $saldoRestante ?>" value="<?= $old['valor_pagamento'] ?? $saldoRestante ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['valor_pagamento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['valor_pagamento'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['valor_pagamento'] ?></p>
                        <?php endif; ?>
                        <div class="mt-2 flex space-x-2">
                            <button type="button" @click="valorPagamento = saldoRestante" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Pagar Total
                            </button>
                            <button type="button" @click="valorPagamento = (saldoRestante / 2).toFixed(2)" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Pagar Metade
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Forma de Pagamento <span class="text-red-500">*</span>
                        </label>
                        <select name="forma_pagamento_id" required
                                class="w-full px-4 py-3 rounded-xl border <?= isset($errors['forma_pagamento_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($formasPagamento as $forma): ?>
                                <option value="<?= $forma['id'] ?>" <?= ($old['forma_pagamento_id'] ?? '') == $forma['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($forma['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['forma_pagamento_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['forma_pagamento_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Conta Bancária <span class="text-red-500">*</span>
                        </label>
                        <select name="conta_bancaria_id" required
                                class="w-full px-4 py-3 rounded-xl border <?= isset($errors['conta_bancaria_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($contasBancarias as $conta_banc): ?>
                                <option value="<?= $conta_banc['id'] ?>" <?= ($old['conta_bancaria_id'] ?? '') == $conta_banc['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($conta_banc['banco_nome'] . ' - Ag: ' . $conta_banc['agencia'] . ' Cc: ' . $conta_banc['conta']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['conta_bancaria_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['conta_bancaria_id'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações do Pagamento</label>
                    <textarea name="observacoes_pagamento" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                              placeholder="Informações adicionais sobre o pagamento..."><?= htmlspecialchars($old['observacoes_pagamento'] ?? '') ?></textarea>
                </div>

                <div x-show="valorPagamento < saldoRestante" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Pagamento Parcial</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                Você está realizando um pagamento parcial. O status da conta será alterado para "Parcial".
                            </p>
                        </div>
                    </div>
                </div>

                <div x-show="valorPagamento >= saldoRestante" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-800 dark:text-green-300">Pagamento Total</h4>
                            <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                Você está quitando a conta completamente. O status será alterado para "Pago".
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-2">
                    <button type="button" @click.prevent="showBaixaModal = false" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-medium shadow-lg">
                        Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Cancelar Pagamento -->
<div x-show="showCancelModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @click.self="showCancelModal = false">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white">⚠️ Cancelar Pagamento</h3>
        </div>
        
        <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>/cancelar-pagamento" class="p-6">
            <div class="mb-6">
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Tem certeza que deseja cancelar o pagamento desta conta?
                </p>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Atenção:</strong> Esta ação irá:
                    </p>
                    <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
                        <li>Reverter o status para "Pendente"</li>
                        <li>Zerar o valor pago</li>
                        <li>Remover a data de pagamento</li>
                        <li>Registrar no histórico de auditoria</li>
                    </ul>
                </div>
                
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Motivo do Cancelamento <span class="text-red-600">*</span>
                </label>
                <textarea name="motivo" rows="3" required
                          placeholder="Ex: Pagamento duplicado, erro no valor, etc."
                          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-yellow-500"></textarea>
            </div>
            
            <div class="flex items-center justify-end space-x-3">
                <button type="button" @click="showCancelModal = false" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-yellow-600 to-orange-600 text-white rounded-lg hover:from-yellow-700 hover:to-orange-700 shadow-lg">
                    Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
