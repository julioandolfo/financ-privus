<?php
$old = $old ?? [];
?>
        <!-- Dados Básicos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Dados da Receita
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa <span class="text-red-500">*</span></label>
                    <select name="empresa_id" id="select_empresa" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= ($old['empresa_id'] ?? $empresaId ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente</label>
                    <select name="cliente_id" id="select_cliente" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione (opcional)...</option>
                        <?php foreach ($clientes ?? [] as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= ($old['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nome_razao_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria <span class="text-red-500">*</span></label>
                    <select name="categoria_id" id="select_categoria" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias ?? [] as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" <?= ($old['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Centro de Custo</label>
                    <select name="centro_custo_id" id="select_centro_custo" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione (opcional)...</option>
                        <?php foreach ($centrosCusto ?? [] as $cc): ?>
                            <option value="<?= $cc['id'] ?>" <?= ($old['centro_custo_id'] ?? '') == $cc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cc['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao" value="<?= htmlspecialchars($old['descricao'] ?? '') ?>" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="Ex: Mensalidade cliente ABC">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor <span class="text-red-500">*</span></label>
                    <input type="number" name="valor" value="<?= htmlspecialchars($old['valor'] ?? '') ?>" x-model="valorBase" @input="atualizarPercentuaisRateios" step="0.01" min="0.01" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="0,00">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações</label>
                    <textarea name="observacoes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"><?= htmlspecialchars($old['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Configuração de Recorrência -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Configuração de Recorrência
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequência <span class="text-red-500">*</span></label>
                    <select name="frequencia" x-model="frequencia" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="mensal">Mensal</option>
                        <option value="quinzenal">Quinzenal</option>
                        <option value="semanal">Semanal</option>
                        <option value="diaria">Diária</option>
                        <option value="bimestral">Bimestral</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>
                
                <div x-show="['mensal', 'bimestral', 'trimestral', 'semestral', 'anual'].includes(frequencia)">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia do Mês <span class="text-red-500">*</span></label>
                    <select name="dia_mes" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <?php foreach ($dias as $dia): ?>
                            <option value="<?= $dia ?>" <?= ($old['dia_mes'] ?? 1) == $dia ? 'selected' : '' ?>><?= $dia ?></option>
                        <?php endforeach; ?>
                        <option value="0" <?= ($old['dia_mes'] ?? '') === '0' ? 'selected' : '' ?>>Último dia do mês</option>
                    </select>
                </div>
                
                <div x-show="frequencia === 'semanal'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia da Semana <span class="text-red-500">*</span></label>
                    <select name="dia_semana" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="1">Segunda-feira</option>
                        <option value="2">Terça-feira</option>
                        <option value="3">Quarta-feira</option>
                        <option value="4">Quinta-feira</option>
                        <option value="5">Sexta-feira</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                </div>
                
                <div x-show="frequencia === 'personalizado'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">A cada X dias <span class="text-red-500">*</span></label>
                    <input type="number" name="intervalo_dias" min="1" max="365" value="<?= $old['intervalo_dias'] ?? 30 ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data de Início <span class="text-red-500">*</span></label>
                    <input type="date" name="data_inicio" value="<?= $old['data_inicio'] ?? date('Y-m-d') ?>" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data de Fim</label>
                    <input type="date" name="data_fim" value="<?= $old['data_fim'] ?? '' ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="Deixe vazio para sem fim">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Máximo de Ocorrências</label>
                    <input type="number" name="max_ocorrencias" min="1" value="<?= $old['max_ocorrencias'] ?? '' ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="Deixe vazio para ilimitado">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Antecedência de Criação</label>
                    <select name="antecedencia_dias" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <?php foreach ([1, 3, 5, 7, 10, 15, 30] as $ant): ?>
                            <option value="<?= $ant ?>" <?= ($old['antecedencia_dias'] ?? 5) == $ant ? 'selected' : '' ?>><?= $ant ?> dias antes</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fim de Semana</label>
                    <select name="ajuste_fim_semana" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="manter" <?= ($old['ajuste_fim_semana'] ?? '') == 'manter' ? 'selected' : '' ?>>Manter data</option>
                        <option value="antecipar" <?= ($old['ajuste_fim_semana'] ?? '') == 'antecipar' ? 'selected' : '' ?>>Antecipar para sexta</option>
                        <option value="postergar" <?= ($old['ajuste_fim_semana'] ?? '') == 'postergar' ? 'selected' : '' ?>>Postergar para segunda</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Inicial</label>
                    <select name="status_inicial" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="pendente" <?= ($old['status_inicial'] ?? 'pendente') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="recebido" <?= ($old['status_inicial'] ?? '') == 'recebido' ? 'selected' : '' ?>>Já Recebido (baixa automática)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Reajuste Anual -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Reajuste Anual
                </h2>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="reajuste_ativo" value="1" x-model="reajusteAtivo" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Ativar reajuste automático</span>
                </label>
            </div>
            
            <div x-show="reajusteAtivo" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Reajuste</label>
                    <select name="reajuste_tipo" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="percentual" <?= ($old['reajuste_tipo'] ?? 'percentual') == 'percentual' ? 'selected' : '' ?>>Percentual (%)</option>
                        <option value="valor_fixo" <?= ($old['reajuste_tipo'] ?? '') == 'valor_fixo' ? 'selected' : '' ?>>Valor Fixo (R$)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor do Reajuste</label>
                    <input type="number" name="reajuste_valor" step="0.01" value="<?= $old['reajuste_valor'] ?? '' ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="Ex: 5.5 para 5,5%">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mês do Reajuste</label>
                    <select name="reajuste_mes" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <?php foreach ($meses as $num => $nome): ?>
                            <option value="<?= $num ?>" <?= ($old['reajuste_mes'] ?? 1) == $num ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Rateio entre Empresas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3M5 21h5m-5 0v-5m0 5v5"></path>
                    </svg>
                    Rateio entre Empresas
                </h2>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="tem_rateio" value="1" x-model="temRateio" @change="toggleRateio"
                           class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativar rateio nas gerações futuras</span>
                </label>
            </div>
            <div x-show="temRateio" x-transition class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Cada conta gerada terá o valor rateado entre as empresas indicadas abaixo.</p>
                <template x-for="(rateio, index) in rateios" :key="index">
                    <div class="flex items-end space-x-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                            <select :name="'rateios[' + index + '][empresa_id]'" x-model="rateio.empresa_id"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Selecione...</option>
                                <?php foreach ($empresas as $empresa): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_fantasia']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor</label>
                            <input type="number" :name="'rateios[' + index + '][valor_rateio]'" x-model="rateio.valor_rateio" step="0.01" @input="calcularPercentual(index)"
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">%</label>
                            <input type="number" :name="'rateios[' + index + '][percentual]'" x-model="rateio.percentual" step="0.01" readonly
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Competência</label>
                            <input type="date" :name="'rateios[' + index + '][data_competencia]'" x-model="rateio.data_competencia"
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                        <button type="button" @click="removerRateio(index)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="adicionarRateio" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    + Adicionar Empresa
                </button>
                <p class="text-sm text-gray-600 dark:text-gray-400" x-show="temRateio">
                    Total: R$ <span x-text="totalRateado.toFixed(2)"></span>
                </p>
            </div>
        </div>

        <!-- Recebimento Automático -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Recebimento Automático (opcional)
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Se o status inicial for "Já Recebido", configure como será registrado o recebimento</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Recebimento</label>
                    <select name="forma_pagamento_id" id="select_forma_pagamento" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($formasPagamento ?? [] as $forma): ?>
                            <option value="<?= $forma['id'] ?>" <?= ($old['forma_pagamento_id'] ?? '') == $forma['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($forma['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conta Bancária</label>
                    <select name="conta_bancaria_id" id="select_conta_bancaria" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($contasBancarias ?? [] as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= ($old['conta_bancaria_id'] ?? '') == $conta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco_nome'] . ' - Ag: ' . $conta['agencia'] . ' Cc: ' . $conta['conta']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
