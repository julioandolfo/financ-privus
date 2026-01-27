<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';
?>

<div class="max-w-6xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Detalhes da Conta a Receber</h1>
                <p class="text-green-100">Visualize todas as informações da receita</p>
            </div>
            <div class="flex items-center space-x-3">
                <?php if ($conta['status'] != 'recebido' && $conta['status'] != 'cancelado'): ?>
                    <a href="/contas-receber/<?= $conta['id'] ?>/baixar" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Baixar/Receber</span>
                    </a>
                <?php endif; ?>
                <?php if ($conta['status'] != 'cancelado'): ?>
                    <a href="/contas-receber/<?= $conta['id'] ?>/edit" class="bg-white text-green-600 px-6 py-3 rounded-xl font-semibold hover:bg-green-50 transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                <?php endif; ?>
                <a href="/contas-receber" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center space-x-2">
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
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Cliente</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['cliente_nome'] ?? 'Não informado') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Categoria</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['categoria_nome']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Centro de Custo</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['centro_custo_nome'] ?? 'Não informado') ?></p>
                    </div>
                    
                    <div class="col-span-2">
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
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 <?= estaVencido($conta['data_vencimento']) && $conta['status'] != 'recebido' ? 'text-amber-600' : '' ?>">
                            <?= formatarData($conta['data_vencimento']) ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($conta['data_recebimento'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Recebimento</label>
                        <p class="text-lg font-semibold text-blue-600"><?= formatarData($conta['data_recebimento']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

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
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Recebido</label>
                        <p class="text-2xl font-bold text-blue-600"><?= formatarMoeda($conta['valor_recebido']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Saldo Restante</label>
                        <p class="text-2xl font-bold text-green-600"><?= formatarMoeda($conta['valor_total'] - $conta['valor_recebido']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($conta['status'] == 'recebido'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Recebido</label>
                        <p class="text-2xl font-bold text-blue-600"><?= formatarMoeda($conta['valor_recebido']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico de Recebimentos -->
            <?php if (!empty($movimentacoes)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Histórico de Recebimentos</h3>
                
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
</div>
