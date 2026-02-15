<?php
$old = $this->session->get('old') ?? [];
$errors = $this->session->get('errors') ?? [];
?>

<style>
.ts-wrapper { min-width: 180px !important; }
.ts-wrapper .ts-dropdown { z-index: 9999 !important; position: absolute !important; }
.ts-wrapper .ts-control { min-height: 38px !important; padding: 6px 10px !important; }
.ts-wrapper .ts-control input { min-width: 80px !important; }
.card-transacao:focus-within { z-index: 9000 !important; }
</style>

<div class="max-w-7xl mx-auto" x-data="transacoesPendentes()">
    <!-- Seletor de Empresa -->
    <?php if (!empty($empresas_usuario) && count($empresas_usuario) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" action="/transacoes-pendentes" class="flex items-center gap-4">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
                <select name="empresa_id" onchange="this.form.submit()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <?php foreach ($empresas_usuario as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Transações Pendentes</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Revise, classifique e aprove transações importadas dos bancos</p>
        </div>
        <a href="/conexoes-bancarias" class="inline-flex items-center px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            Conexões
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="/transacoes-pendentes">
            <input type="hidden" name="empresa_id" value="<?= htmlspecialchars($empresa_id_selecionada ?? '') ?>">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Tipo</label>
                    <select name="tipo" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todos</option>
                        <option value="debito" <?= ($filtros['tipo'] ?? '') === 'debito' ? 'selected' : '' ?>>Despesas</option>
                        <option value="credito" <?= ($filtros['tipo'] ?? '') === 'credito' ? 'selected' : '' ?>>Receitas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Status</label>
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="pendente" <?= ($filtros['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                        <option value="aprovada" <?= ($filtros['status'] ?? '') === 'aprovada' ? 'selected' : '' ?>>Aprovadas</option>
                        <option value="ignorada" <?= ($filtros['status'] ?? '') === 'ignorada' ? 'selected' : '' ?>>Ignoradas</option>
                        <option value="" <?= ($filtros['status'] ?? 'pendente') === '' ? 'selected' : '' ?>>Todas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Banco</label>
                    <select name="banco" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">Todos</option>
                        <option value="sicoob" <?= ($filtros['banco'] ?? '') === 'sicoob' ? 'selected' : '' ?>>Sicoob</option>
                        <option value="sicredi" <?= ($filtros['banco'] ?? '') === 'sicredi' ? 'selected' : '' ?>>Sicredi</option>
                        <option value="itau" <?= ($filtros['banco'] ?? '') === 'itau' ? 'selected' : '' ?>>Itaú</option>
                        <option value="bradesco" <?= ($filtros['banco'] ?? '') === 'bradesco' ? 'selected' : '' ?>>Bradesco</option>
                        <option value="mercadopago" <?= ($filtros['banco'] ?? '') === 'mercadopago' ? 'selected' : '' ?>>Mercado Pago</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">De</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Até</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>
            <?php 
            $temFiltroAtivo = !empty($filtros['tipo']) || !empty($filtros['banco']) || !empty($filtros['data_inicio']) || !empty($filtros['data_fim']) || ($filtros['status'] ?? 'pendente') !== 'pendente';
            if ($temFiltroAtivo): ?>
            <div class="flex items-center gap-2 pt-2 mt-2 border-t border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-500">Filtros:</span>
                <?php if (!empty($filtros['tipo'])): ?><span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs"><?= $filtros['tipo'] === 'debito' ? 'Despesas' : 'Receitas' ?></span><?php endif; ?>
                <?php if (!empty($filtros['banco'])): ?><span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs"><?= ucfirst($filtros['banco']) ?></span><?php endif; ?>
                <a href="/transacoes-pendentes?empresa_id=<?= $empresa_id_selecionada ?>" class="text-xs text-red-600 hover:underline ml-2">Limpar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pendentes</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1"><?= $estatisticas['pendentes'] ?? 0 ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Aprovadas</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?= $estatisticas['aprovadas'] ?? 0 ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Débitos</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">R$ <?= number_format($estatisticas['total_debitos'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Créditos</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">R$ <?= number_format($estatisticas['total_creditos'] ?? 0, 2, ',', '.') ?></p>
        </div>
    </div>

    <?php if (empty($transacoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="text-5xl mb-4">✅</div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Nenhuma Transação Pendente</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Todas as transações foram processadas.</p>
            <a href="/conexoes-bancarias" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">Sincronizar Agora</a>
        </div>
    <?php else: ?>
        <!-- Barra de Ações em Lote (sticky) -->
        <div class="sticky top-0 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-3 mb-4 flex items-center justify-between" x-show="selecionadas.length > 0" x-cloak x-transition>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span x-text="selecionadas.length" class="text-blue-600 dark:text-blue-400"></span> selecionada(s)
                </span>
            </div>
            <div class="flex gap-2">
                <button @click="aprovarLote()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Aprovar Selecionadas
                </button>
                <button @click="ignorarLote()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Ignorar Selecionadas
                </button>
                <button @click="selecionadas = []" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition">
                    Desmarcar
                </button>
            </div>
        </div>

        <!-- Selecionar Todas -->
        <div class="flex items-center gap-3 mb-3 px-2">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" @change="toggleTodas($event)" class="w-4 h-4 rounded text-blue-600">
                Selecionar todas
            </label>
            <span class="text-xs text-gray-400">(<?= count($transacoes) ?> transações)</span>
        </div>

        <!-- Cards de Transações -->
        <div class="space-y-3">
            <?php foreach ($transacoes as $index => $transacao): 
                $zIndex = 1000 - $index;
                $isDebito = $transacao['tipo'] === 'debito';
            ?>
            <div class="card-transacao bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-visible relative"
                 style="z-index: <?= $zIndex ?>;"
                 x-data="{ expanded: false }">
                
                <!-- Linha Principal -->
                <div class="p-4">
                    <div class="flex items-start gap-3 mb-3">
                        <!-- Checkbox -->
                        <div class="pt-1">
                            <input type="checkbox" 
                                   value="<?= $transacao['id'] ?>"
                                   x-model="selecionadas"
                                   class="w-5 h-5 rounded text-blue-600">
                        </div>
                        
                        <!-- Data e Badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                <?= date('d/m/Y', strtotime($transacao['data_transacao'])) ?>
                            </span>
                            <span class="px-2 py-1 rounded text-xs font-semibold <?= $isDebito ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' ?>">
                                <?= $isDebito ? 'SAÍDA' : 'ENTRADA' ?>
                            </span>
                            <?php if (!empty($transacao['metodo_pagamento'])): ?>
                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400">
                                    <?= htmlspecialchars($transacao['metodo_pagamento']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                <?= ucfirst($transacao['origem'] ?? $transacao['banco'] ?? 'API') ?>
                            </span>
                            <?php if ($transacao['confianca_ia']): ?>
                                <span class="px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400">
                                    IA <?= number_format($transacao['confianca_ia'], 0) ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-1"></div>
                        
                        <!-- Valor -->
                        <div class="text-xl font-bold whitespace-nowrap <?= $isDebito ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                            R$ <?= number_format(abs($transacao['valor']), 2, ',', '.') ?>
                        </div>
                        
                        <!-- Toggle Expandir -->
                        <button type="button" @click="expanded = !expanded"
                                class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center gap-1 transition"
                                :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': expanded }">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <span x-text="expanded ? 'Menos' : 'Mais'"></span>
                        </button>
                    </div>
                    
                    <!-- Descrição -->
                    <div class="ml-8 mb-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-l-4 <?= $isDebito ? 'border-red-500' : 'border-green-500' ?>">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($transacao['descricao_original']) ?>
                        </div>
                        <?php if ($transacao['justificativa_ia']): ?>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">💡 <?= htmlspecialchars($transacao['justificativa_ia']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campos Principais (sempre visíveis) -->
                    <div class="ml-8 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Categoria -->
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria *</label>
                            <select name="cat_<?= $transacao['id'] ?>" id="cat_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="categoria_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($transacao['categoria_sugerida_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Fornecedor / Cliente -->
                        <?php if ($isDebito): ?>
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
                            <select name="forn_<?= $transacao['id'] ?>" id="forn_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="fornecedor_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= ($transacao['fornecedor_sugerido_id'] == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nome_razao_social']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                            <select name="cli_<?= $transacao['id'] ?>" id="cli_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="cliente_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($transacao['cliente_sugerido_id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome_razao_social']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Vencimento -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimento</label>
                            <input type="date" id="venc_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="data_vencimento"
                                   value="<?= $transacao['data_transacao'] ?>"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                </div>
                
                <!-- Expandir para mais opções -->
                <div x-show="!expanded" @click="expanded = true"
                     class="px-4 py-2 bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-t border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition group">
                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        Datas, centro de custo, conta bancária, forma de pagamento
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                
                <!-- Seção Expandida -->
                <div x-show="expanded" x-transition class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4">
                    <!-- Datas -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="font-medium">Datas Adicionais</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Competência</label>
                            <input type="date" id="comp_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="data_competencia"
                                   value="<?= $transacao['data_transacao'] ?>"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Pagamento</label>
                            <input type="date" id="pgto_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="data_pagamento"
                                   value="<?= $transacao['data_transacao'] ?>"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                    
                    <!-- Classificação Adicional -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Centro de Custo</label>
                            <select id="cc_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="centro_custo_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($centros_custo as $cc): ?>
                                    <option value="<?= $cc['id'] ?>" <?= ($transacao['centro_custo_sugerido_id'] == $cc['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cc['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Conta Bancária</label>
                            <select id="cb_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="conta_bancaria_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($contas_bancarias as $cb): ?>
                                    <option value="<?= $cb['id'] ?>">
                                        <?= htmlspecialchars($cb['banco_nome'] ?? 'Conta ' . $cb['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="relative" style="z-index: <?= 100 - $index ?>;">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Forma de Pagamento</label>
                            <select id="fp_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="forma_pagamento_id"
                                    class="select-search-tp w-full">
                                <option value="">Selecione...</option>
                                <?php foreach ($formas_pagamento as $fp): ?>
                                    <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                            <input type="text" id="obs_<?= $transacao['id'] ?>" data-transacao="<?= $transacao['id'] ?>" data-field="observacoes"
                                   placeholder="Observações..."
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                    
                    <!-- Ações individuais -->
                    <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="aprovarComDados(<?= $transacao['id'] ?>)"
                                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Aprovar e Lançar
                        </button>
                        <button type="button" @click="ignorar(<?= $transacao['id'] ?>)"
                                class="px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Ignorar
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function transacoesPendentes() {
    return {
        selecionadas: [],
        
        init() {
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof initAllSelectSearch === 'function') {
                        document.querySelectorAll('.select-search-tp').forEach(el => {
                            if (!el.tomselect && typeof TomSelect !== 'undefined') {
                                new TomSelect(el, { create: false, allowEmptyOption: true, plugins: ['dropdown_input'] });
                            }
                        });
                    }
                }, 200);
            });
        },
        
        toggleTodas(e) {
            if (e.target.checked) {
                this.selecionadas = <?= json_encode(array_map(fn($t) => (string)$t['id'], $transacoes)) ?>;
            } else {
                this.selecionadas = [];
            }
        },
        
        getValorCampo(id, prefix) {
            const el = document.getElementById(prefix + '_' + id);
            if (!el) return null;
            if (el.tomselect) return el.tomselect.getValue() || null;
            return el.value || null;
        },
        
        coletarDados(id) {
            return {
                categoria_id: this.getValorCampo(id, 'cat'),
                fornecedor_id: this.getValorCampo(id, 'forn'),
                cliente_id: this.getValorCampo(id, 'cli'),
                centro_custo_id: this.getValorCampo(id, 'cc'),
                conta_bancaria_id: this.getValorCampo(id, 'cb'),
                forma_pagamento_id: this.getValorCampo(id, 'fp'),
                data_vencimento: this.getValorCampo(id, 'venc'),
                data_competencia: this.getValorCampo(id, 'comp'),
                data_pagamento: this.getValorCampo(id, 'pgto'),
                observacoes: this.getValorCampo(id, 'obs')
            };
        },
        
        async aprovarComDados(id) {
            const dados = this.coletarDados(id);
            if (!dados.categoria_id) {
                alert('Selecione uma categoria antes de aprovar.');
                return;
            }
            if (!confirm('Aprovar e lançar esta transação?')) return;
            
            try {
                const res = await fetch(`/transacoes-pendentes/${id}/aprovar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                const data = await res.json();
                if (data.success) { location.reload(); }
                else { alert('Erro: ' + (data.error || 'Erro desconhecido')); }
            } catch (err) { alert('Erro: ' + err.message); }
        },
        
        async ignorar(id) {
            if (!confirm('Ignorar esta transação?')) return;
            try {
                const res = await fetch(`/transacoes-pendentes/${id}/ignorar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) { location.reload(); }
                else { alert('Erro: ' + (data.error || 'Erro desconhecido')); }
            } catch (err) { alert('Erro: ' + err.message); }
        },
        
        async aprovarLote() {
            if (this.selecionadas.length === 0) return;
            if (!confirm(`Aprovar ${this.selecionadas.length} transações com as classificações sugeridas?`)) return;
            
            try {
                const res = await fetch('/transacoes-pendentes/aprovar-lote', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ transacoes: this.selecionadas })
                });
                const data = await res.json();
                if (data.success) { alert(data.message); location.reload(); }
                else { alert('Erro: ' + (data.error || 'Erro desconhecido')); }
            } catch (err) { alert('Erro: ' + err.message); }
        },
        
        async ignorarLote() {
            if (this.selecionadas.length === 0) return;
            if (!confirm(`Ignorar ${this.selecionadas.length} transações selecionadas?`)) return;
            
            try {
                const res = await fetch('/transacoes-pendentes/ignorar-lote', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ transacoes: this.selecionadas })
                });
                const data = await res.json();
                if (data.success) { alert(data.message); location.reload(); }
                else { alert('Erro: ' + (data.error || 'Erro desconhecido')); }
            } catch (err) { alert('Erro: ' + err.message); }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
