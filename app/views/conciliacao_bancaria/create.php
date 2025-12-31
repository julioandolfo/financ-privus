<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Nova Conciliação Bancária</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Inicie uma nova conciliação para reconciliar seus extratos</p>
    </div>

    <!-- Formulário -->
    <form method="POST" action="/conciliacao-bancaria/store" x-data="{ 
        saldoExtrato: 0,
        itens: [],
        adicionarItem() {
            this.itens.push({ descricao: '', valor: '', data: '', tipo: 'credito' });
        },
        removerItem(index) {
            this.itens.splice(index, 1);
        },
        calcularTotal() {
            return this.itens.reduce((total, item) => {
                let valor = parseFloat(item.valor) || 0;
                return total + (item.tipo === 'credito' ? valor : -valor);
            }, 0);
        }
    }">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6">
            
            <!-- Dados Básicos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Conta Bancária -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Conta Bancária *
                    </label>
                    <select name="conta_bancaria_id" required
                            class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['conta_bancaria_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione uma conta</option>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= ($this->session->get('old')['conta_bancaria_id'] ?? '') == $conta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco'] . ' - ' . $conta['agencia'] . '/' . $conta['conta'] . ' - ' . $conta['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($this->session->get('errors')['conta_bancaria_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['conta_bancaria_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Data Início -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Data Início *
                    </label>
                    <input type="date" name="data_inicio" required
                           value="<?= htmlspecialchars($this->session->get('old')['data_inicio'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['data_inicio']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($this->session->get('errors')['data_inicio'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['data_inicio'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Data Fim -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Data Fim *
                    </label>
                    <input type="date" name="data_fim" required
                           value="<?= htmlspecialchars($this->session->get('old')['data_fim'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['data_fim']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($this->session->get('errors')['data_fim'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['data_fim'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Saldo do Extrato -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Saldo do Extrato *
                    </label>
                    <input type="text" name="saldo_extrato" required
                           x-model="saldoExtrato"
                           data-mask="currency"
                           value="<?= htmlspecialchars($this->session->get('old')['saldo_extrato'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['saldo_extrato']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="R$ 0,00">
                    <?php if (isset($this->session->get('errors')['saldo_extrato'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['saldo_extrato'] ?></p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe o saldo final do extrato bancário</p>
                </div>

                <!-- Observações -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Observações
                    </label>
                    <textarea name="observacoes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Informações adicionais sobre esta conciliação..."><?= htmlspecialchars($this->session->get('old')['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção de Itens do Extrato (Opcional) -->
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Itens do Extrato (Opcional)</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Adicione lançamentos do extrato bancário para conciliação posterior</p>
                    </div>
                    <button type="button" @click="adicionarItem()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Adicionar Item</span>
                    </button>
                </div>

                <div x-show="itens.length > 0" class="space-y-4">
                    <template x-for="(item, index) in itens" :key="index">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <div class="md:col-span-2">
                                    <input type="text" :name="'itens[' + index + '][descricao]'" x-model="item.descricao"
                                           placeholder="Descrição do lançamento"
                                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <input type="text" :name="'itens[' + index + '][valor]'" x-model="item.valor"
                                           placeholder="R$ 0,00" data-mask="currency"
                                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <select :name="'itens[' + index + '][tipo]'" x-model="item.tipo"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                        <option value="credito">Crédito (+)</option>
                                        <option value="debito">Débito (-)</option>
                                    </select>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="date" :name="'itens[' + index + '][data]'" x-model="item.data"
                                           class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                    <button type="button" @click="removerItem(index)"
                                            class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="itens.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm">Nenhum item adicionado. Você pode adicionar itens agora ou após criar a conciliação.</p>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="/conciliacao-bancaria" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Criar Conciliação
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Aplicar máscaras de moeda
document.addEventListener('DOMContentLoaded', function() {
    if (typeof applyMasks === 'function') {
        applyMasks();
    }
});
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
