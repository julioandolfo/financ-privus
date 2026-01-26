<?php
$title = 'Revisar Transações do Extrato';
$empresasJson = json_encode($empresas ?? []);
?>

<div class="max-w-7xl mx-auto" x-data="extratoRevisarForm()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Revisar Transações</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Arquivo: <strong><?= htmlspecialchars($arquivoNome) ?></strong> | 
                    Empresa: <strong><?= htmlspecialchars($empresa['nome_fantasia'] ?? 'N/A') ?></strong> |
                    Total: <strong><?= count($transacoes) ?> débitos</strong>
                </p>
            </div>
            <a href="/extrato-bancario" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                ← Voltar
            </a>
        </div>
    </div>
    
    <!-- Formulário de Cadastro em Massa -->
    <form id="cadastrarForm" method="POST" action="/extrato-bancario/cadastrar" @submit.prevent="submitForm">
        <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
        
        <!-- Cards de Transações -->
        <div class="space-y-4 mb-6">
            <?php foreach ($transacoes as $index => $transacao): ?>
                <?php 
                $padrao = $transacao['padrao'] ?? null;
                $temPadrao = !empty($padrao);
                ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                     x-data="{ expanded: false, temRateio: <?= ($padrao && $padrao['tem_rateio']) ? 'true' : 'false' ?>, rateios: [] }">
                    
                    <!-- Linha Principal -->
                    <div class="p-4">
                        <div class="flex items-start gap-4">
                            <!-- Checkbox -->
                            <div class="pt-2">
                                <input type="checkbox" 
                                       name="transacoes[<?= $index ?>][selecionada]" 
                                       value="1" 
                                       class="row-checkbox w-5 h-5 rounded text-blue-600"
                                       checked
                                       @change="updateSelectedCount()">
                            </div>
                            
                            <!-- Info da Transação -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-6 gap-4">
                                <!-- Data e Descrição -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium"><?= date('d/m/Y', strtotime($transacao['data'])) ?></span>
                                        <?php if (!empty($transacao['metodo_pagamento'])): ?>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                <?php 
                                                $metodo = $transacao['metodo_pagamento'];
                                                if ($metodo === 'PIX') echo 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400';
                                                elseif ($metodo === 'Boleto') echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400';
                                                elseif ($metodo === 'Tarifa Bancária' || $metodo === 'IOF' || $metodo === 'Juros') echo 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-400';
                                                else echo 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                                                ?>">
                                                <?= htmlspecialchars($metodo) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($temPadrao): ?>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Padrão
                                                <?php 
                                                $tipoPadraoEncontrado = $transacao['tipo_padrao_encontrado'] ?? null;
                                                if ($tipoPadraoEncontrado === 'cnpj_cpf') echo '(CNPJ)';
                                                elseif ($tipoPadraoEncontrado === 'nome') echo '(Beneficiário)';
                                                elseif ($tipoPadraoEncontrado === 'memo') echo '(Descrição)';
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Descrição principal (MEMO) -->
                                    <div class="font-medium text-gray-900 dark:text-gray-100 mt-1">
                                        <?= htmlspecialchars($transacao['memo'] ?? $transacao['descricao_curta'] ?? '') ?>
                                    </div>
                                    
                                    <!-- Nome/Beneficiário -->
                                    <?php if (!empty($transacao['nome'])): ?>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                            <span class="font-medium">→</span> <?= htmlspecialchars($transacao['nome']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- CNPJ/CPF se identificado -->
                                    <?php if (!empty($transacao['cnpj_cpf'])): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-0.5 font-mono">
                                            <?php 
                                            $doc = $transacao['cnpj_cpf'];
                                            if (strlen($doc) === 14) {
                                                echo substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
                                            } elseif (strlen($doc) === 11) {
                                                echo substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
                                            } else {
                                                echo $doc;
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Referência/Documento -->
                                    <?php if (!empty($transacao['numero_documento'])): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">
                                            Doc: <?= htmlspecialchars($transacao['numero_documento']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-lg font-bold text-red-600 dark:text-red-400 mt-2">
                                        R$ <?= number_format($transacao['valor'], 2, ',', '.') ?>
                                    </div>
                                    <input type="hidden" name="transacoes[<?= $index ?>][empresa_id]" value="<?= $empresaId ?>">
                                </div>
                                
                                <!-- Categoria -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria *</label>
                                    <select name="transacoes[<?= $index ?>][categoria_id]" 
                                            id="categoria_<?= $index ?>"
                                            required
                                            data-placeholder="Selecione..."
                                            class="select-search w-full">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id'] ?>" 
                                                    <?= ($padrao && $padrao['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Fornecedor -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
                                    <select name="transacoes[<?= $index ?>][fornecedor_id]"
                                            id="fornecedor_<?= $index ?>"
                                            data-placeholder="Selecione..."
                                            class="select-search w-full">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($fornecedores as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" 
                                                    <?= ($padrao && $padrao['fornecedor_id'] == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_razao_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Vencimento -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimento *</label>
                                    <input type="date" 
                                           name="transacoes[<?= $index ?>][data_vencimento]" 
                                           value="<?= $transacao['data'] ?>"
                                           required
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                </div>
                                
                                <!-- Ações -->
                                <div class="flex items-end gap-2">
                                    <button type="button" 
                                            @click="expanded = !expanded"
                                            class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center gap-1 transition-all"
                                            :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': expanded }">
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        <span x-text="expanded ? 'Menos' : 'Mais'"></span>
                                    </button>
                                    <button type="button" 
                                            @click="excluirLinha(<?= $index ?>)"
                                            class="px-3 py-2 text-sm bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50"
                                            title="Excluir linha">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barra de Expansão Visual -->
                    <div x-show="!expanded" 
                         @click="expanded = true"
                         class="px-4 py-2 bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-t border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                        <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <span>Clique para ver datas, centro de custo, rateio e mais opções</span>
                            <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Seção Expandida -->
                    <div x-show="expanded" x-transition class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4">
                        <!-- Datas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium">Datas Adicionais</span>
                            </div>
                            
                            <!-- Data de Competência -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Data de Competência
                                    <span class="text-gray-400 font-normal">(mês que pertence)</span>
                                </label>
                                <input type="date" 
                                       name="transacoes[<?= $index ?>][data_competencia]" 
                                       value="<?= $transacao['data'] ?>"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                            
                            <!-- Data de Pagamento -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Data de Pagamento
                                    <span class="text-gray-400 font-normal">(quando foi pago)</span>
                                </label>
                                <input type="date" 
                                       name="transacoes[<?= $index ?>][data_pagamento]" 
                                       value="<?= $transacao['data'] ?>"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>
                        
                        <!-- Classificação -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <!-- Centro de Custo -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Centro de Custo</label>
                                <select name="transacoes[<?= $index ?>][centro_custo_id]"
                                        id="centro_custo_<?= $index ?>"
                                        data-placeholder="Selecione..."
                                        class="select-search w-full">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($centrosCusto as $cc): ?>
                                        <option value="<?= $cc['id'] ?>" 
                                                <?= ($padrao && $padrao['centro_custo_id'] == $cc['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cc['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Conta Bancária -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Conta Bancária</label>
                                <select name="transacoes[<?= $index ?>][conta_bancaria_id]"
                                        id="conta_bancaria_<?= $index ?>"
                                        data-placeholder="Selecione..."
                                        class="select-search w-full">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($contasBancarias as $cb): ?>
                                        <option value="<?= $cb['id'] ?>" 
                                                <?= ($padrao && $padrao['conta_bancaria_id'] == $cb['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cb['banco_nome'] ?? $cb['nome'] ?? 'Conta ' . $cb['id']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Forma de Pagamento -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Forma de Pagamento</label>
                                <select name="transacoes[<?= $index ?>][forma_pagamento_id]"
                                        id="forma_pagamento_<?= $index ?>"
                                        data-placeholder="Selecione..."
                                        class="select-search w-full">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($formasPagamento as $fp): ?>
                                        <option value="<?= $fp['id'] ?>" 
                                                <?= ($padrao && $padrao['forma_pagamento_id'] == $fp['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($fp['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Observações -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                                <input type="text" 
                                       name="transacoes[<?= $index ?>][observacoes]"
                                       value="<?= htmlspecialchars($padrao['observacoes_padrao'] ?? '') ?>"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                       placeholder="Observações...">
                            </div>
                        </div>
                        
                        <!-- Rateio -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="transacoes[<?= $index ?>][tem_rateio]" 
                                           value="1"
                                           x-model="temRateio"
                                           @change="if(temRateio && rateios.length === 0) adicionarRateio()"
                                           class="w-4 h-4 rounded text-blue-600">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativar Rateio entre Empresas</span>
                                </label>
                            </div>
                            
                            <!-- Seção de Rateio -->
                            <div x-show="temRateio" x-transition class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <template x-for="(rateio, rIndex) in rateios" :key="rIndex">
                                    <div class="flex items-end gap-3 mb-3 bg-white dark:bg-gray-800 p-3 rounded-lg">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                                            <select :name="'transacoes[<?= $index ?>][rateios][' + rIndex + '][empresa_id]'" 
                                                    x-model="rateio.empresa_id"
                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">Selecione...</option>
                                                <?php foreach ($empresas ?? [] as $emp): ?>
                                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nome_fantasia']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="w-28">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                                            <input type="number" 
                                                   :name="'transacoes[<?= $index ?>][rateios][' + rIndex + '][valor]'"
                                                   x-model="rateio.valor"
                                                   @input="calcularPercentual(rIndex, <?= $transacao['valor'] ?>)"
                                                   step="0.01"
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </div>
                                        <div class="w-20">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">%</label>
                                            <input type="text" 
                                                   :name="'transacoes[<?= $index ?>][rateios][' + rIndex + '][percentual]'"
                                                   x-model="rateio.percentual"
                                                   readonly
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Competência</label>
                                            <input type="date" 
                                                   :name="'transacoes[<?= $index ?>][rateios][' + rIndex + '][data_competencia]'"
                                                   x-model="rateio.data_competencia"
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </div>
                                        <button type="button" 
                                                @click="removerRateio(rIndex)"
                                                class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                                
                                <button type="button" 
                                        @click="adicionarRateio()"
                                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    + Adicionar Empresa
                                </button>
                                
                                <div class="mt-3 text-sm text-blue-800 dark:text-blue-300">
                                    <strong>Total:</strong> R$ <span x-text="calcularTotalRateio().toFixed(2).replace('.', ',')"></span>
                                    (<span x-text="calcularPercentualTotal().toFixed(2)"></span>%)
                                    <span x-show="Math.abs(calcularTotalRateio() - <?= $transacao['valor'] ?>) > 0.01" class="text-red-600 dark:text-red-400 ml-2">
                                        ⚠ Diferença: R$ <span x-text="(<?= $transacao['valor'] ?> - calcularTotalRateio()).toFixed(2).replace('.', ',')"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Salvar como Padrão -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 mb-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Escolha como reconhecer este padrão:</p>
                                        
                                        <div class="space-y-2">
                                            <!-- Opção 1: Por MEMO (descrição curta) -->
                                            <?php if (!empty($transacao['memo'])): ?>
                                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                                <input type="radio" 
                                                       name="transacoes[<?= $index ?>][tipo_padrao]" 
                                                       value="memo"
                                                       checked
                                                       class="mt-0.5 text-green-600">
                                                <div>
                                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Por Descrição (MEMO)</span>
                                                    <p class="text-xs text-green-600 dark:text-green-500 font-mono">"<?= htmlspecialchars($transacao['memo']) ?>"</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reconhece todas transações com esta descrição, independente do beneficiário</p>
                                                </div>
                                            </label>
                                            <?php endif; ?>
                                            
                                            <!-- Opção 2: Por NAME (beneficiário) -->
                                            <?php if (!empty($transacao['nome'])): ?>
                                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                                <input type="radio" 
                                                       name="transacoes[<?= $index ?>][tipo_padrao]" 
                                                       value="nome"
                                                       <?= empty($transacao['memo']) ? 'checked' : '' ?>
                                                       class="mt-0.5 text-green-600">
                                                <div>
                                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Por Beneficiário (NAME)</span>
                                                    <p class="text-xs text-green-600 dark:text-green-500 font-mono">"<?= htmlspecialchars($transacao['nome']) ?>"</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reconhece apenas transações para este beneficiário específico</p>
                                                </div>
                                            </label>
                                            <?php endif; ?>
                                            
                                            <!-- Opção 3: Por CNPJ/CPF -->
                                            <?php if (!empty($transacao['cnpj_cpf'])): ?>
                                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                                <input type="radio" 
                                                       name="transacoes[<?= $index ?>][tipo_padrao]" 
                                                       value="cnpj_cpf"
                                                       class="mt-0.5 text-green-600">
                                                <div>
                                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Por CNPJ/CPF</span>
                                                    <p class="text-xs text-green-600 dark:text-green-500 font-mono">
                                                        <?php 
                                                        $doc = $transacao['cnpj_cpf'];
                                                        if (strlen($doc) === 14) {
                                                            echo substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
                                                        } elseif (strlen($doc) === 11) {
                                                            echo substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
                                                        } else {
                                                            echo $doc;
                                                        }
                                                        ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reconhece todas transações para este CNPJ/CPF (mais preciso para fornecedores)</p>
                                                </div>
                                            </label>
                                            <?php endif; ?>
                                            
                                            <!-- Opção 4: Descrição completa (fallback) -->
                                            <?php if (empty($transacao['memo']) && empty($transacao['nome'])): ?>
                                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                                <input type="radio" 
                                                       name="transacoes[<?= $index ?>][tipo_padrao]" 
                                                       value="descricao"
                                                       checked
                                                       class="mt-0.5 text-green-600">
                                                <div>
                                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Por Descrição Completa</span>
                                                    <p class="text-xs text-green-600 dark:text-green-500 font-mono">"<?= htmlspecialchars(substr($transacao['descricao'], 0, 60)) ?><?= strlen($transacao['descricao']) > 60 ? '...' : '' ?>"</p>
                                                </div>
                                            </label>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="transacoes[<?= $index ?>][salvar_padrao]" 
                                           value="1"
                                           class="w-4 h-4 rounded text-green-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Salvar ao cadastrar</span>
                                </label>
                                <button type="button" 
                                        @click="salvarPadrao(<?= $index ?>)"
                                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Salvar Padrão Agora
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Botões de Ação -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 rounded-t-xl shadow-lg">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="selectedCount"></span> transação(ões) selecionada(s)
                </div>
                <div class="flex space-x-4">
                    <a href="/extrato-bancario"
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2 disabled:opacity-50">
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Cadastrar Selecionadas
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Cadastrando...
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function extratoRevisarForm() {
    return {
        selectedCount: <?= count($transacoes) ?>,
        isSubmitting: false,
        empresas: <?= $empresasJson ?>,
        
        init() {
            this.updateSelectedCount();
            // Inicializar selects após Alpine renderizar
            this.$nextTick(() => {
                setTimeout(() => initAllSelectSearch(), 100);
            });
        },
        
        updateSelectedCount() {
            this.selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        },
        
        adicionarRateio() {
            this.rateios.push({
                empresa_id: '',
                valor: 0,
                percentual: 0,
                data_competencia: '<?= date('Y-m-d') ?>'
            });
        },
        
        removerRateio(index) {
            this.rateios.splice(index, 1);
        },
        
        calcularPercentual(index, valorTotal) {
            if (valorTotal > 0 && this.rateios[index]) {
                this.rateios[index].percentual = ((this.rateios[index].valor / valorTotal) * 100).toFixed(2);
            }
        },
        
        calcularTotalRateio() {
            return this.rateios.reduce((sum, r) => sum + parseFloat(r.valor || 0), 0);
        },
        
        calcularPercentualTotal() {
            return this.rateios.reduce((sum, r) => sum + parseFloat(r.percentual || 0), 0);
        },
        
        async excluirLinha(index) {
            if (!confirm('Deseja realmente excluir esta linha?')) return;
            
            try {
                const response = await fetch('/extrato-bancario/excluir-linha', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ indice: index })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Erro ao excluir: ' + error.message);
            }
        },
        
        async salvarPadrao(index) {
            const card = document.querySelector(`[data-index="${index}"]`) || document.querySelectorAll('.bg-white.dark\\:bg-gray-800.rounded-xl')[index];
            
            const formData = new FormData();
            formData.append('indice', index);
            formData.append('empresa_id', <?= $empresaId ?>);
            
            // Pegar valores dos selects
            const categoriaSelect = document.getElementById(`categoria_${index}`);
            const fornecedorSelect = document.getElementById(`fornecedor_${index}`);
            const centroSelect = document.getElementById(`centro_custo_${index}`);
            const contaSelect = document.getElementById(`conta_bancaria_${index}`);
            const formaSelect = document.getElementById(`forma_pagamento_${index}`);
            
            if (!categoriaSelect || !categoriaSelect.value) {
                alert('Selecione uma categoria antes de salvar o padrão');
                return;
            }
            
            formData.append('categoria_id', categoriaSelect.tomselect?.getValue() || categoriaSelect.value || '');
            formData.append('fornecedor_id', fornecedorSelect?.tomselect?.getValue() || fornecedorSelect?.value || '');
            formData.append('centro_custo_id', centroSelect?.tomselect?.getValue() || centroSelect?.value || '');
            formData.append('conta_bancaria_id', contaSelect?.tomselect?.getValue() || contaSelect?.value || '');
            formData.append('forma_pagamento_id', formaSelect?.tomselect?.getValue() || formaSelect?.value || '');
            formData.append('tem_rateio', '0');
            formData.append('observacoes', '');
            
            try {
                const response = await fetch('/extrato-bancario/salvar-padrao', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert('Padrão salvo com sucesso!');
                } else {
                    alert('Erro: ' + (data.error || 'Erro ao salvar padrão'));
                }
            } catch (error) {
                alert('Erro ao salvar padrão: ' + error.message);
            }
        },
        
        async submitForm() {
            if (this.selectedCount === 0) {
                alert('Selecione pelo menos uma transação para cadastrar');
                return;
            }
            
            if (!confirm(`Deseja cadastrar ${this.selectedCount} transação(ões)?`)) {
                return;
            }
            
            this.isSubmitting = true;
            
            try {
                const form = document.getElementById('cadastrarForm');
                const formData = new FormData(form);
                
                const response = await fetch('/extrato-bancario/cadastrar', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/contas-pagar';
                } else {
                    alert('Erro: ' + (data.error || 'Erro ao cadastrar contas'));
                    this.isSubmitting = false;
                }
            } catch (error) {
                alert('Erro ao cadastrar: ' + error.message);
                this.isSubmitting = false;
            }
        }
    }
}

// Inicializar selects quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => initAllSelectSearch(), 200);
});
</script>
