<?php
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';
?>

<div class="max-w-6xl mx-auto animate-fade-in" x-data="{ showCancelModal: false, showPedidoModal: false }" @keydown.escape.window="showCancelModal = false; showPedidoModal = false">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
            <div class="flex-shrink-0">
                <h1 class="text-2xl lg:text-3xl font-bold text-white mb-1">Detalhes da Conta a Receber</h1>
                <p class="text-green-100 text-sm">Doc: <?= htmlspecialchars($conta['numero_documento']) ?></p>
            </div>
            
            <!-- Botões organizados em grupos -->
            <div class="flex flex-col gap-3">
                <!-- Linha 1: Ações principais -->
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (!empty($pedidoVinculado)): ?>
                        <button @click="showPedidoModal = true" class="<?= !empty($pedidoVinculado['bonificado']) ? 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600' : 'bg-purple-500 hover:bg-purple-600' ?> text-white px-4 py-2 rounded-lg font-medium transition-all shadow-lg flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>Pedido #<?= htmlspecialchars($pedidoVinculado['numero_pedido'] ?? $pedidoVinculado['id']) ?></span>
                            <?php if (!empty($pedidoVinculado['bonificado'])): ?>
                                <span class="bg-white/20 px-1.5 py-0.5 rounded text-xs">BONIF</span>
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($conta['status'] != 'recebido' && $conta['status'] != 'cancelado'): ?>
                        <a href="/contas-receber/<?= $conta['id'] ?>/baixar" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-all shadow-lg flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Baixar/Receber</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($conta['status'] != 'cancelado'): ?>
                        <a href="/contas-receber/<?= $conta['id'] ?>/edit" class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-green-50 transition-all shadow-lg flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>Editar</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Linha 2: Ações secundárias -->
                <div class="flex flex-wrap items-center gap-2">
                    <?php if ($conta['status'] == 'recebido' || $conta['status'] == 'parcial'): ?>
                        <button @click="showCancelModal = true" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-all shadow flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                            <span>Cancelar Recebimento</span>
                        </button>
                    <?php endif; ?>
                    
                    <a href="/contas-receber/<?= $conta['id'] ?>/historico" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Histórico</span>
                    </a>
                    
                    <a href="/contas-receber" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Voltar</span>
                    </a>
                </div>
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
                        <?php if (!empty($conta['cliente_id'])): ?>
                            <a href="/clientes/<?= $conta['cliente_id'] ?>" class="text-lg font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline flex items-center space-x-2">
                                <span><?= htmlspecialchars($conta['cliente_nome'] ?? 'Cliente #' . $conta['cliente_id']) ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                            <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                Cód: <?= !empty($conta['cliente_codigo']) ? htmlspecialchars($conta['cliente_codigo']) : 'Não informado' ?>
                            </span>
                        <?php else: ?>
                            <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">Não informado</p>
                        <?php endif; ?>
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

            <!-- Parcelas da Conta (tabela parcelas_receber) -->
            <?php if (!empty($parcelas) && count($parcelas) > 0): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                    <span class="inline-flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Parcelas
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            (<?= count($parcelas) ?> parcela<?= count($parcelas) > 1 ? 's' : '' ?>)
                        </span>
                    </span>
                </h2>
                
                <!-- Resumo das Parcelas -->
                <?php if (!empty($resumoParcelasTabela)): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Parcelas</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($resumoParcelasTabela['valor_total'] ?? 0) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Recebido</p>
                        <p class="text-xl font-bold text-green-600"><?= formatarMoeda($resumoParcelasTabela['total_recebido'] ?? 0) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pendentes</p>
                        <p class="text-xl font-bold text-amber-600"><?= $resumoParcelasTabela['parcelas_pendentes'] ?? 0 ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Recebidas</p>
                        <p class="text-xl font-bold text-blue-600"><?= $resumoParcelasTabela['parcelas_recebidas'] ?? 0 ?>/<?= $resumoParcelasTabela['total_parcelas'] ?? 0 ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Lista de Parcelas -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Parcela</th>
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Vencimento</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Valor</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Recebido</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Forma Receb.</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($parcelas as $parcela): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="py-3 text-gray-900 dark:text-gray-100">
                                    <span class="font-semibold"><?= $parcela['numero_parcela'] ?>/<?= count($parcelas) ?></span>
                                </td>
                                <td class="py-3 text-gray-600 dark:text-gray-400">
                                    <?= formatarData($parcela['data_vencimento']) ?>
                                    <?php if (strtotime($parcela['data_vencimento']) < time() && $parcela['status'] == 'pendente'): ?>
                                        <span class="ml-1 text-red-600 text-xs font-bold">VENCIDA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($parcela['valor_parcela']) ?></td>
                                <td class="py-3 text-right text-green-600 font-semibold"><?= formatarMoeda($parcela['valor_recebido'] ?? 0) ?></td>
                                <td class="py-3 text-center"><?= formatarStatusBadge($parcela['status']) ?></td>
                                <td class="py-3 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($parcela['forma_recebimento_nome'] ?? '-') ?></td>
                                <td class="py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <?php if ($parcela['status'] != 'recebido' && $parcela['status'] != 'cancelado'): ?>
                                            <a href="/contas-receber/<?= $conta['id'] ?>/parcela/<?= $parcela['id'] ?>/baixar" 
                                               class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors"
                                               title="Baixar/Receber esta parcela">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                Baixar
                                            </a>
                                        <?php elseif ($parcela['status'] == 'recebido'): ?>
                                            <form action="/contas-receber/<?= $conta['id'] ?>/parcela/<?= $parcela['id'] ?>/reverter" method="POST" 
                                                  onsubmit="return confirm('Tem certeza que deseja reverter o pagamento desta parcela? Ela voltará para Pendente.')">
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded-lg transition-colors"
                                                        title="Reverter pagamento desta parcela">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                    </svg>
                                                    Reverter
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php if (!empty($parcela['observacoes'])): ?>
                            <tr class="bg-gray-50/50 dark:bg-gray-700/30">
                                <td colspan="7" class="py-2 px-4">
                                    <div class="flex items-start gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span><strong class="text-gray-600 dark:text-gray-300">Obs:</strong> <?= htmlspecialchars($parcela['observacoes']) ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-700 dark:text-blue-300 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Para contas parceladas, realize a baixa em cada parcela individualmente.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cliente Vinculado -->
            <?php if (!empty($clienteVinculado)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        <span class="inline-flex items-center">
                            <svg class="w-6 h-6 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Cliente Vinculado
                        </span>
                    </h2>
                    <a href="/clientes/<?= $clienteVinculado['id'] ?>" class="text-cyan-600 hover:text-cyan-800 text-sm font-semibold flex items-center space-x-1">
                        <span>Ver ficha completa</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
                
                <!-- Info do Cliente -->
                <div class="space-y-4">
                    <!-- Linha 1: Código + Nome -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-cyan-50 dark:bg-cyan-900/20 rounded-xl">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Código</p>
                            <p class="text-lg font-bold text-cyan-600 dark:text-cyan-400">
                                <?= !empty($clienteVinculado['codigo_cliente']) ? htmlspecialchars($clienteVinculado['codigo_cliente']) : '<span class="text-gray-400">-</span>' ?>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Nome / Razão Social</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($clienteVinculado['nome_razao_social']) ?></p>
                        </div>
                    </div>
                    
                    <!-- Linha 2: CPF/CNPJ + Telefone + E-mail -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">CPF / CNPJ</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                <?php if (!empty($clienteVinculado['cpf_cnpj'])): ?>
                                    <?= formatarCpfCnpj($clienteVinculado['cpf_cnpj']) ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Não informado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Telefone</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                <?php if (!empty($clienteVinculado['telefone'])): ?>
                                    <?= formatarTelefone($clienteVinculado['telefone']) ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Não informado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">E-mail</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 break-all">
                                <?php if (!empty($clienteVinculado['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($clienteVinculado['email']) ?>" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($clienteVinculado['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">Não informado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
            </div>
            <?php endif; ?>

            <!-- Pedido Vinculado (Resumo) -->
            <?php if (!empty($pedidoVinculado)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        <span class="inline-flex items-center">
                            <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Pedido Vinculado
                        </span>
                    </h2>
                    <div class="flex items-center gap-3">
                        <?php if ($pedidoVinculado['origem'] === 'woocommerce' && !empty($pedidoVinculado['origem_id'])): ?>
                            <?php
                            // Buscar URL da loja WooCommerce
                            $integracaoWooModel = new \App\Models\IntegracaoWooCommerce();
                            $integracaoWoo = $integracaoWooModel->findByEmpresaId($pedidoVinculado['empresa_id']);
                            if ($integracaoWoo && !empty($integracaoWoo['url_site'])):
                                $urlPedidoWoo = rtrim($integracaoWoo['url_site'], '/') . '/wp-admin/post.php?post=' . $pedidoVinculado['origem_id'] . '&action=edit';
                            ?>
                            <a href="<?= htmlspecialchars($urlPedidoWoo) ?>" 
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm font-semibold rounded-lg shadow transition-all"
                               title="Ver pedido no WooCommerce">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Ver no WooCommerce
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <button @click="showPedidoModal = true" class="text-purple-600 hover:text-purple-800 text-sm font-semibold flex items-center space-x-1">
                            <span>Ver detalhes completos</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Info do Pedido -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Número</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pedidoVinculado['numero_pedido'] ?? $pedidoVinculado['id']) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Valor Total</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($pedidoVinculado['valor_total'] ?? 0) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Lucro</p>
                        <p class="text-lg font-bold text-green-600"><?= formatarMoeda($pedidoVinculado['lucro'] ?? 0) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Margem</p>
                        <p class="text-lg font-bold text-blue-600"><?= number_format($pedidoVinculado['margem_lucro'] ?? 0, 2, ',', '.') ?>%</p>
                    </div>
                </div>
                
                <!-- Frete e Desconto (Resumo) -->
                <div class="flex flex-wrap gap-4 mb-4">
                    <div class="inline-flex items-center px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Frete: <strong class="ml-1"><?= formatarMoeda($pedidoVinculado['frete'] ?? 0) ?></strong>
                    </div>
                    <div class="inline-flex items-center px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        Desconto: <strong class="ml-1"><?= formatarMoeda($pedidoVinculado['desconto'] ?? 0) ?></strong>
                    </div>
                </div>
                
                <!-- Lista de Itens do Pedido -->
                <?php if (!empty($itensPedido)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Produto</th>
                                <th class="text-center pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Qtd</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Valor Unit.</th>
                                <th class="text-right pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($itensPedido as $item): ?>
                            <tr>
                                <td class="py-3 text-gray-900 dark:text-gray-100">
                                    <span class="font-semibold"><?= htmlspecialchars($item['nome_produto']) ?></span>
                                    <?php if (!empty($item['produto_codigo'])): ?>
                                        <span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars($item['produto_codigo']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center text-gray-600 dark:text-gray-400"><?= number_format($item['quantidade'], 2, ',', '.') ?></td>
                                <td class="py-3 text-right text-gray-600 dark:text-gray-400"><?= formatarMoeda($item['valor_unitario']) ?></td>
                                <td class="py-3 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($item['valor_total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
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

    <!-- Modal de Pedido Vinculado -->
    <?php if (!empty($pedidoVinculado)): ?>
    <div x-show="showPedidoModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="showPedidoModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden max-h-[90vh] flex flex-col">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">
                        <span class="inline-flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Pedido #<?= htmlspecialchars($pedidoVinculado['numero_pedido'] ?? $pedidoVinculado['id']) ?>
                        </span>
                    </h3>
                    <button @click="showPedidoModal = false" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1">
                    <!-- Informações do Pedido -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Data do Pedido</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= formatarData($pedidoVinculado['data_pedido'] ?? '') ?></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Origem</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= ucfirst(htmlspecialchars($pedidoVinculado['origem'] ?? 'manual')) ?></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                <?= ucfirst(htmlspecialchars($pedidoVinculado['status'] ?? 'pendente')) ?>
                                <?php if (!empty($pedidoVinculado['bonificado'])): ?>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                        </svg>
                                        BONIFICADO
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pedidoVinculado['cliente_nome'] ?? $conta['cliente_nome'] ?? 'N/A') ?></p>
                        </div>
                    </div>

                    <!-- Resumo Financeiro -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Valor Total</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($pedidoVinculado['valor_total'] ?? 0) ?></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Custo Total</p>
                            <p class="text-xl font-bold text-red-600"><?= formatarMoeda($pedidoVinculado['valor_custo_total'] ?? 0) ?></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Lucro</p>
                            <p class="text-xl font-bold text-green-600"><?= formatarMoeda($pedidoVinculado['lucro'] ?? 0) ?></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Margem de Lucro</p>
                            <p class="text-xl font-bold text-blue-600"><?= number_format($pedidoVinculado['margem_lucro'] ?? 0, 2, ',', '.') ?>%</p>
                        </div>
                    </div>
                    
                    <!-- Frete e Desconto -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-700">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Frete
                            </p>
                            <p class="text-xl font-bold text-amber-600"><?= formatarMoeda($pedidoVinculado['frete'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500">(Deduzido do lucro)</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Desconto
                            </p>
                            <p class="text-xl font-bold text-orange-600"><?= formatarMoeda($pedidoVinculado['desconto'] ?? 0) ?></p>
                        </div>
                    </div>

                    <!-- Itens do Pedido -->
                    <?php if (!empty($itensPedido)): ?>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Itens do Pedido (<?= count($itensPedido) ?> item<?= count($itensPedido) > 1 ? 's' : '' ?>)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                    <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Produto</th>
                                    <th class="text-center py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Qtd</th>
                                    <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Valor Unit.</th>
                                    <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Custo Unit.</th>
                                    <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Lucro</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($itensPedido as $item): 
                                    $lucroItem = ($item['valor_total'] ?? 0) - ($item['custo_total'] ?? 0);
                                    $margemItem = ($item['valor_total'] ?? 0) > 0 ? ($lucroItem / $item['valor_total']) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                                        <span class="font-semibold"><?= htmlspecialchars($item['nome_produto']) ?></span>
                                        <?php if (!empty($item['produto_codigo'])): ?>
                                            <span class="block text-xs text-gray-500"><?= htmlspecialchars($item['produto_codigo']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['codigo_produto_origem'])): ?>
                                            <span class="block text-xs text-gray-400">SKU: <?= htmlspecialchars($item['codigo_produto_origem']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400"><?= number_format($item['quantidade'], 2, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400"><?= formatarMoeda($item['valor_unitario']) ?></td>
                                    <td class="py-3 px-4 text-right text-red-500"><?= formatarMoeda($item['custo_unitario'] ?? 0) ?></td>
                                    <td class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-gray-100"><?= formatarMoeda($item['valor_total']) ?></td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="font-semibold text-green-600"><?= formatarMoeda($lucroItem) ?></span>
                                        <span class="block text-xs text-gray-500">(<?= number_format($margemItem, 1, ',', '.') ?>%)</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                                <?php if (($pedidoVinculado['frete'] ?? 0) > 0): ?>
                                <tr class="text-amber-600">
                                    <td class="py-2 px-4" colspan="4">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                        Frete
                                    </td>
                                    <td class="py-2 px-4 text-right">-</td>
                                    <td class="py-2 px-4 text-right font-semibold">- <?= formatarMoeda($pedidoVinculado['frete']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (($pedidoVinculado['desconto'] ?? 0) > 0): ?>
                                <tr class="text-orange-600">
                                    <td class="py-2 px-4" colspan="4">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        Desconto
                                    </td>
                                    <td class="py-2 px-4 text-right font-semibold"><?= formatarMoeda($pedidoVinculado['desconto']) ?></td>
                                    <td class="py-2 px-4 text-right">-</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="font-bold border-t border-gray-300 dark:border-gray-600">
                                    <td class="py-3 px-4 text-gray-900 dark:text-gray-100" colspan="2">TOTAL</td>
                                    <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400">-</td>
                                    <td class="py-3 px-4 text-right text-red-600"><?= formatarMoeda($pedidoVinculado['valor_custo_total'] ?? 0) ?></td>
                                    <td class="py-3 px-4 text-right text-gray-900 dark:text-gray-100"><?= formatarMoeda($pedidoVinculado['valor_total'] ?? 0) ?></td>
                                    <td class="py-3 px-4 text-right text-green-600"><?= formatarMoeda($pedidoVinculado['lucro'] ?? 0) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p>Nenhum item encontrado neste pedido.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end">
                    <button @click="showPedidoModal = false" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
