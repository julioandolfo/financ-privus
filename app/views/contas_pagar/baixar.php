<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';

$saldoRestante = $conta['valor_total'] - $conta['valor_pago'];
?>

<div class="max-w-4xl mx-auto animate-fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Baixar/Pagar Conta</h1>
            <p class="text-green-100 mt-2">Registre o pagamento da despesa</p>
        </div>

        <!-- Informações da Conta -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-8 py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fornecedor</label>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['fornecedor_nome'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Documento</label>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['numero_documento']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Vencimento</label>
                    <p class="text-lg font-semibold <?= estaVencido($conta['data_vencimento']) ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' ?>">
                        <?= formatarData($conta['data_vencimento']) ?>
                    </p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Valor Total</label>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($conta['valor_total']) ?></p>
                    </div>
                    <?php if ($conta['status'] == 'parcial'): ?>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Já Pago</label>
                        <p class="text-2xl font-bold text-green-600"><?= formatarMoeda($conta['valor_pago']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="text-right">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Saldo Restante</label>
                        <p class="text-2xl font-bold text-red-600"><?= formatarMoeda($saldoRestante) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>/baixar" class="p-8" x-data="{ valorPagamento: <?= $saldoRestante ?>, saldoRestante: <?= $saldoRestante ?> }">
            <div class="space-y-6">
                <!-- Data do Pagamento -->
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

                <!-- Valor do Pagamento -->
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Forma de Pagamento -->
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

                    <!-- Conta Bancária -->
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

                <!-- Observações do Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações do Pagamento</label>
                    <textarea name="observacoes_pagamento" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                              placeholder="Informações adicionais sobre o pagamento..."><?= htmlspecialchars($old['observacoes_pagamento'] ?? '') ?></textarea>
                </div>

                <!-- Alerta de Pagamento Parcial -->
                <div x-show="valorPagamento < saldoRestante" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Pagamento Parcial</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                Você está realizando um pagamento parcial. O status da conta será alterado para "Parcial" e você poderá realizar novos pagamentos posteriormente.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Alerta de Pagamento Total -->
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
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4 mt-8">
                <a href="/contas-pagar/<?= $conta['id'] ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-medium shadow-lg">
                    Confirmar Pagamento
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
