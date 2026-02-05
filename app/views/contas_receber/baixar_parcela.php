<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';

$saldoRestante = $parcela['valor_parcela'] - ($parcela['valor_recebido'] ?? 0);
?>

<div class="max-w-4xl mx-auto animate-fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Baixar/Receber Parcela</h1>
            <p class="text-blue-100 mt-2">Registre o recebimento da parcela <?= $parcela['numero_parcela'] ?></p>
        </div>

        <!-- Informações da Conta -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-8 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Conta a Receber</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($conta['descricao']) ?></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Doc: <?= htmlspecialchars($conta['numero_documento']) ?></p>
                </div>
                <a href="/contas-receber/<?= $conta['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ← Voltar para conta
                </a>
            </div>
        </div>

        <!-- Informações da Parcela -->
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 px-8 py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Parcela</label>
                    <p class="text-2xl font-bold text-indigo-600"><?= $parcela['numero_parcela'] ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Vencimento</label>
                    <p class="text-lg font-semibold <?= estaVencido($parcela['data_vencimento']) ? 'text-amber-600' : 'text-gray-900 dark:text-gray-100' ?>">
                        <?= formatarData($parcela['data_vencimento']) ?>
                        <?php if (estaVencido($parcela['data_vencimento'])): ?>
                            <span class="text-xs font-bold">VENCIDA</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Valor da Parcela</label>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= formatarMoeda($parcela['valor_parcela']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Saldo a Receber</label>
                    <p class="text-2xl font-bold text-green-600"><?= formatarMoeda($saldoRestante) ?></p>
                </div>
            </div>
            
            <?php if (($parcela['valor_recebido'] ?? 0) > 0): ?>
            <div class="mt-4 pt-4 border-t border-indigo-200 dark:border-indigo-700">
                <p class="text-sm text-indigo-700 dark:text-indigo-300">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Já foi recebido: <strong><?= formatarMoeda($parcela['valor_recebido']) ?></strong>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Form -->
        <form method="POST" action="/contas-receber/<?= $conta['id'] ?>/parcela/<?= $parcela['id'] ?>/baixar" class="p-8" x-data="{ valorRecebimento: <?= $saldoRestante ?>, saldoRestante: <?= $saldoRestante ?> }">
            <div class="space-y-6">
                <!-- Data do Recebimento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data do Recebimento <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="data_recebimento" value="<?= $old['data_recebimento'] ?? date('Y-m-d') ?>" required
                           class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_recebimento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    <?php if (isset($errors['data_recebimento'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $errors['data_recebimento'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Valor do Recebimento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Valor do Recebimento <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="valor_recebimento" x-model="valorRecebimento" step="0.01" min="0.01" max="<?= $saldoRestante ?>" value="<?= $old['valor_recebimento'] ?? $saldoRestante ?>" required
                           class="w-full px-4 py-3 rounded-xl border <?= isset($errors['valor_recebimento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    <?php if (isset($errors['valor_recebimento'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $errors['valor_recebimento'] ?></p>
                    <?php endif; ?>
                    <div class="mt-2 flex space-x-2">
                        <button type="button" @click="valorRecebimento = saldoRestante" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Receber Total
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Forma de Recebimento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Forma de Recebimento <span class="text-red-500">*</span>
                        </label>
                        <select name="forma_recebimento_id" required
                                class="w-full px-4 py-3 rounded-xl border <?= isset($errors['forma_recebimento_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($formasRecebimento as $forma): ?>
                                <option value="<?= $forma['id'] ?>" <?= ($old['forma_recebimento_id'] ?? '') == $forma['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($forma['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['forma_recebimento_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['forma_recebimento_id'] ?></p>
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

                <!-- Observações do Recebimento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações do Recebimento</label>
                    <textarea name="observacoes_recebimento" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                              placeholder="Informações adicionais sobre o recebimento..."><?= htmlspecialchars($old['observacoes_recebimento'] ?? '') ?></textarea>
                </div>

                <!-- Alerta de Recebimento -->
                <div x-show="valorRecebimento >= saldoRestante" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-800 dark:text-green-300">Recebimento Total da Parcela</h4>
                            <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                A parcela será marcada como "Recebida".
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4 mt-8">
                <a href="/contas-receber/<?= $conta['id'] ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all font-medium shadow-lg">
                    Confirmar Recebimento
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
