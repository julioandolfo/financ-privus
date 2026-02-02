<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';
?>

<div class="max-w-6xl mx-auto animate-fade-in" x-data="{ showCancelModal: false }" @keydown.escape.window="showCancelModal = false">
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
                <?php if ($conta['status'] == 'recebido' || $conta['status'] == 'parcial'): ?>
                    <button @click="showCancelModal = true" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        <span>Cancelar Recebimento</span>
                    </button>
                <?php endif; ?>
                <?php if ($conta['status'] != 'cancelado'): ?>
                    <a href="/contas-receber/<?= $conta['id'] ?>/edit" class="bg-white text-green-600 px-6 py-3 rounded-xl font-semibold hover:bg-green-50 transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                <?php endif; ?>
                <a href="/contas-receber/<?= $conta['id'] ?>/historico" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Histórico</span>
                </a>
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Recebido</p>
                        <p class="text-xl font-bold text-green-600"><?= formatarMoeda($resumoParcelas['valor_recebido']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Restante</p>
                        <p class="text-xl font-bold text-red-600"><?= formatarMoeda($resumoParcelas['valor_restante']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Progresso</p>
                        <p class="text-xl font-bold text-indigo-600"><?= $resumoParcelas['parcelas_recebidas'] ?>/<?= $resumoParcelas['total_parcelas'] ?></p>
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
                                    <?php if (estaVencido($parcela['data_vencimento']) && $parcela['status'] != 'recebido'): ?>
                                        <span class="ml-1 text-red-600 text-xs font-bold">VENCIDA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($parcela['valor_total']) ?></td>
                                <td class="py-3 text-center"><?= formatarStatusBadge($parcela['status']) ?></td>
                                <td class="py-3 text-center">
                                    <?php if ($parcela['id'] != $conta['id']): ?>
                                        <a href="/contas-receber/<?= $parcela['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm">
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

    <!-- Modal de Cancelar Recebimento -->
    <div x-show="showCancelModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="showCancelModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white">⚠️ Cancelar Recebimento</h3>
                </div>
                
                <form method="POST" action="/contas-receber/<?= $conta['id'] ?>/cancelar-recebimento" class="p-6">
                    <div class="mb-6">
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            Tem certeza que deseja cancelar o recebimento desta conta?
                        </p>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Atenção:</strong> Esta ação irá:
                            </p>
                            <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
                                <li>Reverter o status para "Pendente"</li>
                                <li>Zerar o valor recebido</li>
                                <li>Remover a data de recebimento</li>
                                <li>Registrar no histórico de auditoria</li>
                            </ul>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo do Cancelamento <span class="text-red-600">*</span>
                        </label>
                        <textarea name="motivo" rows="3" required
                                  placeholder="Ex: Recebimento duplicado, erro no valor, etc."
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
</div>
