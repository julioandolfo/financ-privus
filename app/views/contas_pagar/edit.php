<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
$dias = range(1, 31);
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<div class="max-w-5xl mx-auto animate-fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-600 to-rose-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Editar Conta a Pagar</h1>
            <p class="text-red-100 mt-2">Atualize as informações da despesa</p>
        </div>

        <!-- Form -->
        <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>" class="p-8" x-data="contaPagarForm()">
            <input type="hidden" name="_method" value="PUT">
            
            <!-- Dados Básicos -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Dados Básicos</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Empresa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Empresa <span class="text-red-500">*</span>
                        </label>
                        <select name="empresa_id" required
                                @change="carregarCategoriasECentros($event.target.value)"
                                class="w-full px-4 py-3 rounded-xl border <?= isset($errors['empresa_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?= $empresa['id'] ?>" <?= ($old['empresa_id'] ?? $conta['empresa_id']) == $empresa['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['empresa_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['empresa_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Fornecedor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fornecedor</label>
                        <select name="fornecedor_id" id="fornecedor_id" data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select name="categoria_id" id="categoria_id" required data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
                        </select>
                        <?php if (isset($errors['categoria_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['categoria_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Centro de Custo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Centro de Custo</label>
                        <select name="centro_custo_id" id="centro_custo_id" data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
                        </select>
                    </div>

                    <!-- Número do Documento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número do Documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="numero_documento" value="<?= htmlspecialchars($old['numero_documento'] ?? $conta['numero_documento']) ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['numero_documento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                               placeholder="Ex: NF-001234">
                        <?php if (isset($errors['numero_documento'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['numero_documento'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Valor Total -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Valor Total <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="valor_total" x-model="valorTotal" @input="atualizarRateios" step="0.01" min="0.01" value="<?= $old['valor_total'] ?? $conta['valor_total'] ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['valor_total']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                               placeholder="0,00">
                        <?php if (isset($errors['valor_total'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['valor_total'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tipo de Custo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Custo</label>
                        <div class="flex items-center space-x-6 mt-3">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="tipo_custo" value="variavel" <?= ($old['tipo_custo'] ?? $conta['tipo_custo'] ?? 'variavel') == 'variavel' ? 'checked' : '' ?>
                                       class="w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-gray-700 dark:text-gray-300">Variável</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="tipo_custo" value="fixo" <?= ($old['tipo_custo'] ?? $conta['tipo_custo'] ?? '') == 'fixo' ? 'checked' : '' ?>
                                       class="w-5 h-5 text-orange-600 focus:ring-2 focus:ring-orange-500">
                                <span class="text-gray-700 dark:text-gray-300">Fixo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descrição <span class="text-red-500">*</span>
                        </label>
                        <textarea name="descricao" rows="3" required
                                  class="w-full px-4 py-3 rounded-xl border <?= isset($errors['descricao']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                  placeholder="Descreva a despesa..."><?= htmlspecialchars($old['descricao'] ?? $conta['descricao']) ?></textarea>
                        <?php if (isset($errors['descricao'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['descricao'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Datas -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Datas</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Data de Emissão -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data de Emissão <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_emissao" value="<?= $old['data_emissao'] ?? $conta['data_emissao'] ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_emissao']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['data_emissao'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['data_emissao'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Data de Competência -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data de Competência <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_competencia" x-model="dataCompetencia" value="<?= $old['data_competencia'] ?? $conta['data_competencia'] ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_competencia']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['data_competencia'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['data_competencia'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Data de Vencimento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data de Vencimento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_vencimento" value="<?= $old['data_vencimento'] ?? $conta['data_vencimento'] ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_vencimento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['data_vencimento'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['data_vencimento'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações</label>
                <textarea name="observacoes" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                          placeholder="Observações adicionais..."><?= htmlspecialchars($old['observacoes'] ?? $conta['observacoes']) ?></textarea>
            </div>

            <!-- Tornar Recorrente -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Tornar Recorrente
                    </h2>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="tornar_recorrente" value="1" x-model="tornarRecorrente"
                               class="w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Criar despesa recorrente a partir desta conta</span>
                    </label>
                </div>

                <div x-show="tornarRecorrente" x-transition class="space-y-6">
                    <p class="text-sm text-purple-700 dark:text-purple-300">
                        Esta conta será usada como modelo. Uma despesa recorrente será criada e gerará automaticamente novas contas no futuro.
                    </p>

                    <!-- Configuração de Recorrência -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuração de Recorrência</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequência <span class="text-red-500">*</span></label>
                                <select name="recorrencia_frequencia" x-model="recorrenciaFrequencia" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
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
                            
                            <div x-show="['mensal', 'bimestral', 'trimestral', 'semestral', 'anual'].includes(recorrenciaFrequencia)">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia do Mês</label>
                                <select name="recorrencia_dia_mes" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <?php foreach ($dias as $dia): ?>
                                        <option value="<?= $dia ?>" <?= date('j', strtotime($conta['data_vencimento'] ?? 'now')) == $dia ? 'selected' : '' ?>><?= $dia ?></option>
                                    <?php endforeach; ?>
                                    <option value="0">Último dia do mês</option>
                                </select>
                            </div>
                            
                            <div x-show="recorrenciaFrequencia === 'semanal'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dia da Semana</label>
                                <select name="recorrencia_dia_semana" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="1">Segunda-feira</option>
                                    <option value="2">Terça-feira</option>
                                    <option value="3">Quarta-feira</option>
                                    <option value="4">Quinta-feira</option>
                                    <option value="5">Sexta-feira</option>
                                    <option value="6">Sábado</option>
                                    <option value="0">Domingo</option>
                                </select>
                            </div>
                            
                            <div x-show="recorrenciaFrequencia === 'personalizado'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">A cada X dias</label>
                                <input type="number" name="recorrencia_intervalo_dias" min="1" max="365" value="30"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data de Início <span class="text-red-500">*</span></label>
                                <input type="date" name="recorrencia_data_inicio" value="<?= htmlspecialchars($old['recorrencia_data_inicio'] ?? $conta['data_vencimento'] ?? date('Y-m-d')) ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data de Fim</label>
                                <input type="date" name="recorrencia_data_fim" value="<?= htmlspecialchars($old['recorrencia_data_fim'] ?? '') ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                       placeholder="Deixe vazio para sem fim">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Máx. Ocorrências</label>
                                <input type="number" name="recorrencia_max_ocorrencias" min="1" value="<?= htmlspecialchars($old['recorrencia_max_ocorrencias'] ?? '') ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                       placeholder="Deixe vazio para ilimitado">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Antecedência</label>
                                <select name="recorrencia_antecedencia_dias" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <?php foreach ([1, 3, 5, 7, 10, 15, 30] as $d): ?>
                                        <option value="<?= $d ?>" <?= ($old['recorrencia_antecedencia_dias'] ?? 5) == $d ? 'selected' : '' ?>><?= $d ?> dias antes</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fim de Semana</label>
                                <select name="recorrencia_ajuste_fim_semana" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="manter">Manter data</option>
                                    <option value="antecipar">Antecipar para sexta</option>
                                    <option value="postergar">Postergar para segunda</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Inicial</label>
                                <select name="recorrencia_status_inicial" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="pendente">Pendente</option>
                                    <option value="pago">Já Pago (baixa automática)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Reajuste Anual -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Reajuste Anual</h3>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="recorrencia_reajuste_ativo" value="1" x-model="recorrenciaReajusteAtivo"
                                       class="w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativar reajuste automático</span>
                            </label>
                        </div>
                        <div x-show="recorrenciaReajusteAtivo" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo</label>
                                <select name="recorrencia_reajuste_tipo" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="percentual">Percentual (%)</option>
                                    <option value="valor_fixo">Valor Fixo (R$)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor</label>
                                <input type="number" name="recorrencia_reajuste_valor" step="0.01" value=""
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                       placeholder="Ex: 5.5 para 5,5%">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mês</label>
                                <select name="recorrencia_reajuste_mes" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <?php foreach ($meses as $num => $nome): ?>
                                        <option value="<?= $num ?>"><?= $nome ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Forma de Pagamento (para status "Já Pago") -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pagamento Automático</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Pagamento</label>
                                <select name="recorrencia_forma_pagamento_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($formasPagamento as $forma): ?>
                                        <option value="<?= $forma['id'] ?>" <?= ($conta['forma_pagamento_id'] ?? '') == $forma['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($forma['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conta Bancária</label>
                                <select name="recorrencia_conta_bancaria_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($contasBancarias as $cb): ?>
                                        <option value="<?= $cb['id'] ?>" <?= ($conta['conta_bancaria_id'] ?? '') == $cb['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cb['banco_nome'] . ' - Ag: ' . $cb['agencia'] . ' Cc: ' . $cb['conta']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Rateio entre Empresas -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rateio entre Empresas</h3>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="recorrencia_tem_rateio" value="1" x-model="recorrenciaTemRateio" @change="toggleRecorrenciaRateio"
                                       class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aplicar rateio nas gerações futuras</span>
                            </label>
                        </div>
                        <div x-show="recorrenciaTemRateio" x-transition class="space-y-4">
                            <template x-for="(rateio, index) in rateiosRecorrencia" :key="index">
                                <div class="flex items-end space-x-4 bg-white dark:bg-gray-800 p-4 rounded-lg">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                                        <select :name="'recorrencia_rateios[' + index + '][empresa_id]'" x-model="rateio.empresa_id"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="">Selecione...</option>
                                            <?php foreach ($empresas as $empresa): ?>
                                                <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_fantasia']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor</label>
                                        <input type="number" :name="'recorrencia_rateios[' + index + '][valor_rateio]'" x-model="rateio.valor_rateio" step="0.01" @input="calcularPercentualRecorrencia(index)"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">%</label>
                                        <input type="number" :name="'recorrencia_rateios[' + index + '][percentual]'" x-model="rateio.percentual" step="0.01" readonly
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Competência</label>
                                        <input type="date" :name="'recorrencia_rateios[' + index + '][data_competencia]'" x-model="rateio.data_competencia"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </div>
                                    <button type="button" @click="removerRateioRecorrencia(index)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="adicionarRateioRecorrencia" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                + Adicionar Empresa
                            </button>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-show="recorrenciaTemRateio">
                                Total: R$ <span x-text="totalRateioRecorrencia.toFixed(2)"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="/contas-pagar/<?= $conta['id'] ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl hover:from-red-700 hover:to-rose-700 transition-all font-medium shadow-lg">
                    Atualizar Conta a Pagar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function contaPagarForm() {
    const rateiosIniciais = <?= json_encode(array_map(function($r) {
        return [
            'empresa_id' => $r['empresa_id'],
            'valor_rateio' => $r['valor_rateio'],
            'percentual' => $r['percentual'] ?? 0,
            'data_competencia' => $r['data_competencia'] ?? ''
        ];
    }, $rateios ?? [])) ?>;
    
    return {
        valorTotal: <?= $old['valor_total'] ?? $conta['valor_total'] ?>,
        dataCompetencia: '<?= $old['data_competencia'] ?? $conta['data_competencia'] ?>',
        categoriaIdAtual: <?= json_encode($old['categoria_id'] ?? $conta['categoria_id'] ?? '') ?>,
        centroCustoIdAtual: <?= json_encode($old['centro_custo_id'] ?? $conta['centro_custo_id'] ?? '') ?>,
        fornecedorIdAtual: <?= json_encode($old['fornecedor_id'] ?? $conta['fornecedor_id'] ?? '') ?>,
        
        tornarRecorrente: false,
        recorrenciaFrequencia: '<?= $old['recorrencia_frequencia'] ?? 'mensal' ?>',
        recorrenciaReajusteAtivo: false,
        recorrenciaTemRateio: <?= !empty($rateios) ? 'true' : 'false' ?>,
        rateiosRecorrencia: rateiosIniciais.length > 0 ? rateiosIniciais : [],
        
        init() {
            const empresaId = document.querySelector('select[name="empresa_id"]').value;
            if (empresaId) {
                this.carregarDadosEmpresa(empresaId);
            }
            if (this.recorrenciaTemRateio && this.rateiosRecorrencia.length === 0) {
                this.adicionarRateioRecorrencia();
            }
        },
        
        atualizarRateios() {},
        
        toggleRecorrenciaRateio() {
            if (this.recorrenciaTemRateio && this.rateiosRecorrencia.length === 0) {
                this.adicionarRateioRecorrencia();
            }
        },
        
        adicionarRateioRecorrencia() {
            const dataCompetencia = this.dataCompetencia || '<?= $conta['data_competencia'] ?? date('Y-m-d') ?>';
            this.rateiosRecorrencia.push({
                empresa_id: '',
                valor_rateio: '',
                percentual: 0,
                data_competencia: dataCompetencia
            });
        },
        
        removerRateioRecorrencia(index) {
            this.rateiosRecorrencia.splice(index, 1);
        },
        
        calcularPercentualRecorrencia(index) {
            const valorTotal = parseFloat(this.valorTotal) || 0;
            if (valorTotal > 0 && this.rateiosRecorrencia[index]) {
                this.rateiosRecorrencia[index].percentual = (parseFloat(this.rateiosRecorrencia[index].valor_rateio || 0) / valorTotal * 100).toFixed(2);
            }
        },
        
        get totalRateioRecorrencia() {
            return this.rateiosRecorrencia.reduce((sum, r) => sum + parseFloat(r.valor_rateio || 0), 0);
        },
        
        async carregarDadosEmpresa(empresaId) {
            if (!empresaId) {
                this.limparSelects();
                return;
            }
            
            // Carregar categorias de despesa
            try {
                const respCategorias = await fetch(`/categorias?ajax=1&empresa_id=${empresaId}&tipo=despesa`);
                const dataCategorias = await respCategorias.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataCategorias.success && dataCategorias.categorias) {
                    dataCategorias.categorias.forEach(cat => {
                        options.push({value: cat.id, text: cat.nome});
                    });
                }
                refreshSelectSearch('categoria_id', options, this.categoriaIdAtual);
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
            }
            
            // Carregar centros de custo
            try {
                const respCentros = await fetch(`/centros-custo?ajax=1&empresa_id=${empresaId}`);
                const dataCentros = await respCentros.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataCentros.success && dataCentros.centros) {
                    dataCentros.centros.forEach(centro => {
                        options.push({value: centro.id, text: centro.nome});
                    });
                }
                refreshSelectSearch('centro_custo_id', options, this.centroCustoIdAtual);
            } catch (error) {
                console.error('Erro ao carregar centros de custo:', error);
            }
            
            // Carregar fornecedores
            try {
                const respFornecedores = await fetch(`/fornecedores?ajax=1&empresa_id=${empresaId}`);
                const dataFornecedores = await respFornecedores.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataFornecedores.success && dataFornecedores.fornecedores) {
                    dataFornecedores.fornecedores.forEach(fornecedor => {
                        options.push({value: fornecedor.id, text: fornecedor.nome_razao_social});
                    });
                }
                refreshSelectSearch('fornecedor_id', options, this.fornecedorIdAtual);
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
            }
        },
        
        carregarCategoriasECentros(empresaId) {
            // Limpar valores atuais ao trocar empresa
            this.categoriaIdAtual = '';
            this.centroCustoIdAtual = '';
            this.fornecedorIdAtual = '';
            this.carregarDadosEmpresa(empresaId);
        },
        
        limparSelects() {
            const emptyOptions = [{value: '', text: 'Selecione uma empresa primeiro...'}];
            refreshSelectSearch('categoria_id', emptyOptions);
            refreshSelectSearch('centro_custo_id', emptyOptions);
            refreshSelectSearch('fornecedor_id', emptyOptions);
        }
    }
}
</script>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
