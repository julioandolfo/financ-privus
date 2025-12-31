<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 text-sm text-gray-600 dark:text-gray-400 mb-4">
            <a href="/movimentacoes-caixa" class="hover:text-blue-600 dark:hover:text-blue-400">Movimentações</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-900 dark:text-gray-100 font-medium">Editar Movimentação</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Editar Movimentação #<?= $movimentacao['id'] ?></h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Altere os dados da movimentação manual</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <form method="POST" action="/movimentacoes-caixa/<?= $movimentacao['id'] ?>">
            <!-- Tipo (Destaque) -->
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Tipo de Movimentação *
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex items-center justify-center p-6 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-2 border-green-300 dark:border-green-700 rounded-xl cursor-pointer hover:shadow-lg transition-all group">
                        <input type="radio" name="tipo" value="entrada" required
                               class="sr-only peer"
                               <?= ($this->session->get('old')['tipo'] ?? $movimentacao['tipo']) === 'entrada' ? 'checked' : '' ?>>
                        <div class="text-center peer-checked:scale-105 transition-transform">
                            <svg class="w-12 h-12 mx-auto text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                            <span class="text-lg font-bold text-green-800 dark:text-green-300">Entrada</span>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Recebimento / Crédito</p>
                        </div>
                        <div class="absolute inset-0 rounded-xl ring-4 ring-green-500 ring-opacity-0 peer-checked:ring-opacity-50 transition-all"></div>
                    </label>

                    <label class="relative flex items-center justify-center p-6 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-2 border-red-300 dark:border-red-700 rounded-xl cursor-pointer hover:shadow-lg transition-all group">
                        <input type="radio" name="tipo" value="saida" required
                               class="sr-only peer"
                               <?= ($this->session->get('old')['tipo'] ?? $movimentacao['tipo']) === 'saida' ? 'checked' : '' ?>>
                        <div class="text-center peer-checked:scale-105 transition-transform">
                            <svg class="w-12 h-12 mx-auto text-red-600 dark:text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                            <span class="text-lg font-bold text-red-800 dark:text-red-300">Saída</span>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Pagamento / Débito</p>
                        </div>
                        <div class="absolute inset-0 rounded-xl ring-4 ring-red-500 ring-opacity-0 peer-checked:ring-opacity-50 transition-all"></div>
                    </label>
                </div>
                <?php if (isset($this->session->get('errors')['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Empresa *
                    </label>
                    <select name="empresa_id" required
                            class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['empresa_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione...</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= ($this->session->get('old')['empresa_id'] ?? $movimentacao['empresa_id']) == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Categoria *
                    </label>
                    <select name="categoria_id" required
                            class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['categoria_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" <?= ($this->session->get('old')['categoria_id'] ?? $movimentacao['categoria_id']) == $categoria['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome']) ?> (<?= ucfirst($categoria['tipo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($this->session->get('errors')['categoria_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['categoria_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Centro de Custo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Centro de Custo
                    </label>
                    <select name="centro_custo_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Nenhum</option>
                        <?php foreach ($centrosCusto as $centro): ?>
                            <option value="<?= $centro['id'] ?>" <?= ($this->session->get('old')['centro_custo_id'] ?? $movimentacao['centro_custo_id']) == $centro['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($centro['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Conta Bancária -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Conta Bancária *
                    </label>
                    <select name="conta_bancaria_id" required
                            class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['conta_bancaria_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione...</option>
                        <?php foreach ($contasBancarias as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= ($this->session->get('old')['conta_bancaria_id'] ?? $movimentacao['conta_bancaria_id']) == $conta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco_nome']) ?> - Ag. <?= htmlspecialchars($conta['agencia']) ?> / Conta <?= htmlspecialchars($conta['numero_conta']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($this->session->get('errors')['conta_bancaria_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['conta_bancaria_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Forma de Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Forma de Pagamento
                    </label>
                    <select name="forma_pagamento_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Nenhuma</option>
                        <?php foreach ($formasPagamento as $forma): ?>
                            <option value="<?= $forma['id'] ?>" <?= ($this->session->get('old')['forma_pagamento_id'] ?? $movimentacao['forma_pagamento_id']) == $forma['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($forma['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Valor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Valor *
                    </label>
                    <input type="number" name="valor" step="0.01" min="0.01" required
                           value="<?= htmlspecialchars($this->session->get('old')['valor'] ?? $movimentacao['valor']) ?>"
                           placeholder="0,00"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['valor']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($this->session->get('errors')['valor'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['valor'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Data de Movimentação -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data de Movimentação *
                    </label>
                    <input type="date" name="data_movimentacao" required
                           value="<?= htmlspecialchars($this->session->get('old')['data_movimentacao'] ?? $movimentacao['data_movimentacao']) ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['data_movimentacao']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($this->session->get('errors')['data_movimentacao'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['data_movimentacao'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Data de Competência -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data de Competência
                    </label>
                    <input type="date" name="data_competencia"
                           value="<?= htmlspecialchars($this->session->get('old')['data_competencia'] ?? $movimentacao['data_competencia']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Opcional - Para relatórios por competência</p>
                </div>
            </div>

            <!-- Descrição -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descrição *
                </label>
                <textarea name="descricao" rows="3" required
                          placeholder="Descreva a movimentação..."
                          class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['descricao']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($this->session->get('old')['descricao'] ?? $movimentacao['descricao']) ?></textarea>
                <?php if (isset($this->session->get('errors')['descricao'])): ?>
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['descricao'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Observações -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observações
                </label>
                <textarea name="observacoes" rows="2"
                          placeholder="Observações adicionais..."
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($this->session->get('old')['observacoes'] ?? $movimentacao['observacoes']) ?></textarea>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="/movimentacoes-caixa" 
                   class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Atualizar Movimentação
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
