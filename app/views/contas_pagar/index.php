<?php
// Carrega helpers
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';

$modoConsolidacao = modoConsolidacao();
$empresasAtivas = $modoConsolidacao ? count(empresasConsolidacao()) : 1;
?>

<div class="max-w-7xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Contas a Pagar</h1>
                <p class="text-red-100">Gerencie suas despesas e pagamentos</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/contas-pagar/deletados" class="bg-white/20 text-white px-4 py-2 rounded-xl font-medium hover:bg-white/30 transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Deletados</span>
                </a>
                <a href="/despesas-recorrentes" class="bg-white/20 text-white px-4 py-2 rounded-xl font-medium hover:bg-white/30 transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Recorrentes</span>
                </a>
                <a href="/contas-pagar/create" class="bg-white text-red-600 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Nova Conta</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Banner Transações Pendentes -->
    <?php if (($transacoes_pendentes_count ?? 0) > 0): ?>
    <div class="mb-6">
        <a href="/transacoes-pendentes" class="flex items-center justify-between bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-xl p-4 hover:border-amber-400 dark:hover:border-amber-600 transition-all group">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center shadow-sm">
                    <span class="text-lg font-bold text-white"><?= $transacoes_pendentes_count ?></span>
                </div>
                <div>
                    <p class="font-semibold text-amber-900 dark:text-amber-100">
                        Transaç<?= $transacoes_pendentes_count > 1 ? 'ões' : 'ão' ?> importada<?= $transacoes_pendentes_count > 1 ? 's' : '' ?> do banco aguardando aprovação
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-300">Clique para revisar e lançar como contas a pagar</p>
                </div>
            </div>
            <div class="flex-shrink-0 text-amber-600 dark:text-amber-400 group-hover:translate-x-1 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Filtros Avançados -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8" x-data="{ showFilters: <?= !empty(array_filter($filters ?? [])) ? 'true' : 'false' ?> }">
        <!-- Filtros Rápidos -->
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtros rápidos:</span>
            <a href="/contas-pagar?status=pendente" class="px-3 py-1.5 text-sm rounded-full <?= ($filters['status'] ?? '') == 'pendente' ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' ?> hover:opacity-80 transition-opacity">
                Pendentes
            </a>
            <a href="/contas-pagar?status=vencido" class="px-3 py-1.5 text-sm rounded-full <?= ($filters['status'] ?? '') == 'vencido' ? 'bg-red-500 text-white' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' ?> hover:opacity-80 transition-opacity">
                Vencidas
            </a>
            <a href="/contas-pagar?status=pago" class="px-3 py-1.5 text-sm rounded-full <?= ($filters['status'] ?? '') == 'pago' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' ?> hover:opacity-80 transition-opacity">
                Pagas
            </a>
            <a href="/contas-pagar?data_inicio=<?= date('Y-m-01') ?>&data_fim=<?= date('Y-m-t') ?>" class="px-3 py-1.5 text-sm rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 hover:opacity-80 transition-opacity">
                Este Mês
            </a>
            <a href="/contas-pagar?data_inicio=<?= date('Y-m-d') ?>&data_fim=<?= date('Y-m-d', strtotime('+7 days')) ?>" class="px-3 py-1.5 text-sm rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 hover:opacity-80 transition-opacity">
                Próximos 7 dias
            </a>
            <div class="flex-1"></div>
            <button @click="showFilters = !showFilters" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span x-text="showFilters ? 'Ocultar Filtros Avançados' : 'Filtros Avançados'"></span>
            </button>
        </div>
        
        <form method="GET" action="/contas-pagar" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="pendente" <?= ($filters['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="vencido" <?= ($filters['status'] ?? '') == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="parcial" <?= ($filters['status'] ?? '') == 'parcial' ? 'selected' : '' ?>>Parcialmente Pago</option>
                        <option value="pago" <?= ($filters['status'] ?? '') == 'pago' ? 'selected' : '' ?>>Pago</option>
                        <option value="cancelado" <?= ($filters['status'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>

                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                    <select name="empresa_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= ($filters['empresa_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fornecedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fornecedor</label>
                    <?php
                    // Agrupa fornecedores por nome para consolidação
                    $fornecedoresAgrupados = [];
                    foreach ($fornecedores as $f) {
                        $nome = $f['nome_razao_social'];
                        if (!isset($fornecedoresAgrupados[$nome])) {
                            $fornecedoresAgrupados[$nome] = ['ids' => [], 'nome' => $nome];
                        }
                        $fornecedoresAgrupados[$nome]['ids'][] = $f['id'];
                    }
                    ?>
                    <select name="fornecedor_nome" id="filter_fornecedor" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedoresAgrupados as $nome => $data): ?>
                            <?php $qtdEmpresas = count($data['ids']); ?>
                            <option value="<?= htmlspecialchars($nome) ?>" 
                                    data-ids="<?= implode(',', $data['ids']) ?>"
                                    <?= ($filters['fornecedor_nome'] ?? '') == $nome ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nome) ?><?= $qtdEmpresas > 1 ? " ({$qtdEmpresas})" : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria</label>
                    <?php
                    // Agrupa categorias por nome para consolidação
                    $categoriasAgrupadas = [];
                    foreach ($categorias as $c) {
                        $nome = $c['nome'];
                        if (!isset($categoriasAgrupadas[$nome])) {
                            $categoriasAgrupadas[$nome] = ['ids' => [], 'nome' => $nome];
                        }
                        $categoriasAgrupadas[$nome]['ids'][] = $c['id'];
                    }
                    ?>
                    <select name="categoria_nome" id="filter_categoria" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas</option>
                        <?php foreach ($categoriasAgrupadas as $nome => $data): ?>
                            <?php $qtdEmpresas = count($data['ids']); ?>
                            <option value="<?= htmlspecialchars($nome) ?>"
                                    data-ids="<?= implode(',', $data['ids']) ?>"
                                    <?= ($filters['categoria_nome'] ?? '') == $nome ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nome) ?><?= $qtdEmpresas > 1 ? " ({$qtdEmpresas})" : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Custo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Custo</label>
                    <select name="tipo_custo" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="variavel" <?= ($filters['tipo_custo'] ?? '') == 'variavel' ? 'selected' : '' ?>>Variável</option>
                        <option value="fixo" <?= ($filters['tipo_custo'] ?? '') == 'fixo' ? 'selected' : '' ?>>Fixo</option>
                    </select>
                </div>

                <!-- Centro de Custo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Centro de Custo</label>
                    <?php
                    // Agrupa centros de custo por nome para consolidação
                    $centrosCustoAgrupados = [];
                    foreach ($centrosCusto ?? [] as $cc) {
                        $nome = $cc['nome'];
                        if (!isset($centrosCustoAgrupados[$nome])) {
                            $centrosCustoAgrupados[$nome] = ['ids' => [], 'nome' => $nome];
                        }
                        $centrosCustoAgrupados[$nome]['ids'][] = $cc['id'];
                    }
                    ?>
                    <select name="centro_custo_nome" id="filter_centro_custo" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <?php foreach ($centrosCustoAgrupados as $nome => $data): ?>
                            <?php $qtdEmpresas = count($data['ids']); ?>
                            <option value="<?= htmlspecialchars($nome) ?>"
                                    data-ids="<?= implode(',', $data['ids']) ?>"
                                    <?= ($filters['centro_custo_nome'] ?? '') == $nome ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nome) ?><?= $qtdEmpresas > 1 ? " ({$qtdEmpresas})" : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Parcelamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parcelamento</label>
                    <select name="parcelamento" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="sim" <?= ($filters['parcelamento'] ?? '') == 'sim' ? 'selected' : '' ?>>Parceladas</option>
                        <option value="nao" <?= ($filters['parcelamento'] ?? '') == 'nao' ? 'selected' : '' ?>>Não Parceladas</option>
                    </select>
                </div>

                <!-- Forma de Pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Pagamento</label>
                    <select name="forma_pagamento_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas</option>
                        <?php foreach ($formasPagamento ?? [] as $forma): ?>
                            <option value="<?= $forma['id'] ?>" <?= ($filters['forma_pagamento_id'] ?? '') == $forma['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($forma['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Vencimento - Início -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vencimento De</label>
                    <input type="date" name="data_inicio" value="<?= $filters['data_inicio'] ?? '' ?>" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Data Vencimento - Fim -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vencimento Até</label>
                    <input type="date" name="data_fim" value="<?= $filters['data_fim'] ?? '' ?>" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Valor Mínimo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Mínimo (R$)</label>
                    <input type="number" name="valor_min" value="<?= $filters['valor_min'] ?? '' ?>" step="0.01" min="0"
                           placeholder="0,00"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Valor Máximo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Máximo (R$)</label>
                    <input type="number" name="valor_max" value="<?= $filters['valor_max'] ?? '' ?>" step="0.01" min="0"
                           placeholder="0,00"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Ordenar Por -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ordenar Por</label>
                    <select name="ordenar" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="vencimento_asc" <?= ($filters['ordenar'] ?? '') == 'vencimento_asc' ? 'selected' : '' ?>>Vencimento (mais próximo)</option>
                        <option value="vencimento_desc" <?= ($filters['ordenar'] ?? '') == 'vencimento_desc' ? 'selected' : '' ?>>Vencimento (mais distante)</option>
                        <option value="valor_asc" <?= ($filters['ordenar'] ?? '') == 'valor_asc' ? 'selected' : '' ?>>Valor (menor primeiro)</option>
                        <option value="valor_desc" <?= ($filters['ordenar'] ?? '') == 'valor_desc' ? 'selected' : '' ?>>Valor (maior primeiro)</option>
                        <option value="cadastro_desc" <?= ($filters['ordenar'] ?? '') == 'cadastro_desc' ? 'selected' : '' ?>>Mais recentes</option>
                        <option value="fornecedor_asc" <?= ($filters['ordenar'] ?? '') == 'fornecedor_asc' ? 'selected' : '' ?>>Fornecedor (A-Z)</option>
                    </select>
                </div>

                <!-- Itens por Página -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Itens por Página</label>
                    <select name="por_pagina" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="25" <?= ($filters['por_pagina'] ?? '25') == '25' ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($filters['por_pagina'] ?? '') == '50' ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($filters['por_pagina'] ?? '') == '100' ? 'selected' : '' ?>>100</option>
                        <option value="todos" <?= ($filters['por_pagina'] ?? '') == 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>

                <!-- Busca -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                    <input type="text" name="search" value="<?= $filters['search'] ?? '' ?>" 
                           placeholder="Descrição, nº documento, observações..."
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <?php 
                    $filtrosAtivos = count(array_filter($filters ?? []));
                    if ($filtrosAtivos > 0): 
                    ?>
                        <span class="font-medium text-blue-600"><?= $filtrosAtivos ?> filtro(s) ativo(s)</span>
                    <?php endif; ?>
                </div>
                <div class="flex space-x-4">
                    <a href="/contas-pagar" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                        Limpar Filtros
                    </a>
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Contas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-data="{ selecionados: [], showCategoriaModal: false, showBaixaModal: false }"
         x-init="$watch('selecionados.length', v => { if (v === 0) { showCategoriaModal = false; showBaixaModal = false; } })">
        <!-- Barra de ações em massa -->
        <div x-show="selecionados.length > 0" x-cloak x-transition
             class="sticky top-0 z-10 bg-red-600 dark:bg-red-700 px-6 py-3 flex items-center justify-between gap-4">
            <span class="text-white font-medium" x-text="selecionados.length + ' conta(s) selecionada(s)'"></span>
            <div class="flex items-center gap-3">
                <button type="button" @click="showBaixaModal = true"
                        class="px-4 py-2 bg-white text-red-600 rounded-lg font-medium hover:bg-red-50 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Dar Baixa
                </button>
                <button type="button" @click="showCategoriaModal = true"
                        class="px-4 py-2 bg-white text-red-600 rounded-lg font-medium hover:bg-red-50 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Alterar Categoria
                </button>
                <button type="button" @click="selecionados = []"
                        class="px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors">
                    Desmarcar
                </button>
            </div>
        </div>

        <!-- Modal alterar categoria em massa -->
        <div x-show="showCategoriaModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @keydown.escape.window="showCategoriaModal = false">
            <div @click.self="showCategoriaModal = false" class="absolute inset-0"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 max-w-md w-full">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Alterar categoria em massa</h3>
                <form method="POST" action="/contas-pagar/atualizar-categoria-massa">
                    <?php 
                    $urlParams = array_filter($filters ?? [], fn($v) => $v !== '' && $v !== null);
                    foreach ($urlParams as $k => $v): 
                        if (!is_array($v)):
                    ?><input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"><?php 
                        endif;
                    endforeach; 
                    ?>
                    <template x-for="id in selecionados" :key="id">
                        <input type="hidden" :name="'ids[]'" :value="id">
                    </template>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nova categoria</label>
                        <select name="categoria_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?><?= !empty($cat['empresa_nome']) ? ' (' . htmlspecialchars($cat['empresa_nome']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCategoriaModal = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Aplicar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Dar Baixa em massa -->
        <div x-show="showBaixaModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @keydown.escape.window="showBaixaModal = false">
            <div @click.self="showBaixaModal = false" class="absolute inset-0"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 max-w-md w-full">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Dar Baixa em Massa</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pagar as contas selecionadas. Contas já pagas ou de outra empresa serão ignoradas.</p>
                <form method="POST" action="/contas-pagar/efetuar-baixa-massa">
                    <?php
                    $urlParams = array_filter($filters ?? [], fn($v) => $v !== '' && $v !== null);
                    foreach ($urlParams as $k => $v):
                        if (!is_array($v)):
                    ?><input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"><?php
                        endif;
                    endforeach;
                    ?>
                    <template x-for="id in selecionados" :key="id">
                        <input type="hidden" :name="'ids[]'" :value="id">
                    </template>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Pagamento</label>
                            <input type="date" name="data_pagamento" required value="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Pagamento</label>
                            <select name="forma_pagamento_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Selecione...</option>
                                <?php foreach ($formasPagamento ?? [] as $fp): ?>
                                <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['nome']) ?><?= !empty($fp['empresa_nome']) ? ' (' . htmlspecialchars($fp['empresa_nome']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conta Bancária</label>
                            <select name="conta_bancaria_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Selecione...</option>
                                <?php foreach ($contasBancarias ?? [] as $cb): ?>
                                <option value="<?= $cb['id'] ?>"><?= htmlspecialchars(($cb['banco_nome'] ?? 'Conta') . ' - Ag: ' . ($cb['agencia'] ?? '') . ' Cc: ' . ($cb['conta'] ?? $cb['numero_conta'] ?? '')) ?><?= !empty($cb['empresa_nome']) ? ' (' . htmlspecialchars($cb['empresa_nome']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showBaixaModal = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Dar Baixa</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($contasPagar)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-red-600 to-rose-600">
                        <tr>
                            <th class="px-4 py-4 text-left">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500"
                                       :checked="selecionados.length === <?= count($contasPagar) ?>"
                                       @change="selecionados = $event.target.checked ? [<?= implode(',', array_map(fn($c) => $c['id'], $contasPagar)) ?>] : []">
                            </th>
                            <?php if ($modoConsolidacao): ?>
                            <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Empresa</th>
                            <?php endif; ?>
                            <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Fornecedor</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-white uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-white uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($contasPagar as $conta): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500"
                                       :checked="selecionados.includes(<?= $conta['id'] ?>)"
                                       @change="selecionados.includes(<?= $conta['id'] ?>) ? selecionados = selecionados.filter(i => i !== <?= $conta['id'] ?>) : selecionados.push(<?= $conta['id'] ?>)">
                            </td>
                            <?php if ($modoConsolidacao): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($conta['empresa_nome']) ?>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <?= formatarData($conta['data_vencimento']) ?>
                                <?php if (estaVencido($conta['data_vencimento']) && $conta['status'] != 'pago'): ?>
                                    <span class="ml-2 text-red-600 font-bold">VENCIDO</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($conta['fornecedor_nome'] ?? 'Sem fornecedor') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div><?= htmlspecialchars(truncarTexto($conta['descricao'], 50)) ?></div>
                                <div class="text-xs text-gray-500">Doc: <?= htmlspecialchars($conta['numero_documento']) ?></div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <?php if (!empty($conta['eh_parcelado']) && $conta['eh_parcelado']): ?>
                                        <span class="inline-block px-2 py-0.5 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 rounded-full">
                                            Parcela <?= $conta['parcela_numero'] ?>/<?= $conta['total_parcelas'] ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (($conta['tipo_custo'] ?? 'variavel') === 'fixo'): ?>
                                        <span class="inline-block px-2 py-0.5 text-xs bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400 rounded-full">
                                            Fixo
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($conta['tem_rateio']): ?>
                                        <span class="inline-block px-2 py-0.5 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full">
                                            Rateado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <?= htmlspecialchars($conta['categoria_nome']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                                <?= formatarMoeda($conta['valor_total']) ?>
                                <?php if ($conta['status'] == 'parcial'): ?>
                                    <div class="text-xs text-gray-500">Pago: <?= formatarMoeda($conta['valor_pago']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?= formatarStatusBadge($conta['status']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="/contas-pagar/<?= $conta['id'] ?>" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400" title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <?php if ($conta['status'] != 'pago' && $conta['status'] != 'cancelado'): ?>
                                        <a href="/contas-pagar/<?= $conta['id'] ?>?acao=baixar" class="text-green-600 hover:text-green-900 dark:hover:text-green-400" title="Baixar/Pagar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/contas-pagar/<?= $conta['id'] ?>/historico" class="text-purple-600 hover:text-purple-900 dark:hover:text-purple-400" title="Histórico">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </a>
                                    <?php if ($conta['status'] != 'cancelado'): ?>
                                        <a href="/contas-pagar/<?= $conta['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-900 dark:hover:text-yellow-400" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>/delete" class="inline" onsubmit="return confirm('Tem certeza que deseja DELETAR esta conta?\n\nEla será movida para Registros Deletados e poderá ser restaurada.')">
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" title="Deletar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando 
                            <span class="font-medium"><?= min($paginacao['offset'] + 1, $paginacao['total_registros']) ?></span>
                            até
                            <span class="font-medium"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
                            de
                            <span class="font-medium"><?= $paginacao['total_registros'] ?></span>
                            registros
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <?php
                            $urlParams = $filters;
                            unset($urlParams['pagina']);
                            
                            // Remover parâmetros vazios
                            $urlParams = array_filter($urlParams, function($value) {
                                return $value !== '' && $value !== null;
                            });
                            
                            $urlBase = '/contas-pagar' . (!empty($urlParams) ? '?' . http_build_query($urlParams) : '');
                            $separador = empty($urlParams) ? '?' : '&';
                            $range = 2;
                            $inicio = max(1, $paginacao['pagina_atual'] - $range);
                            $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + $range);
                            ?>
                            
                            <?php if ($paginacao['pagina_atual'] > 1): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=1" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Primeira</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] - 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Anterior</a>
                            <?php endif; ?>
                            
                            <?php if ($inicio > 1): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                                <?php if ($i == $paginacao['pagina_atual']): ?>
                                    <span class="px-4 py-2 text-sm bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-lg font-medium"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= $urlBase . $separador ?>pagina=<?= $i ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($fim < $paginacao['total_paginas']): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] + 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Próxima</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['total_paginas'] ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Última</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-xl text-gray-600 dark:text-gray-400 mb-4">Nenhuma conta a pagar encontrada</p>
                <a href="/contas-pagar/create" class="inline-block px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-lg hover:from-red-700 hover:to-rose-700 transition-all font-medium">
                    Criar Primeira Conta
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
