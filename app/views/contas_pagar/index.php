<?php
// Carrega helpers
require_once __DIR__ . '/../../../includes/helpers/formata_dados.php';
require_once __DIR__ . '/../../../includes/helpers/functions.php';

$modoConsolidacao = modoConsolidacao();
$empresasAtivas = $modoConsolidacao ? count(empresasConsolidacao()) : 1;
?>

<div class="max-w-7xl mx-auto animate-fade-in">
    <!-- Header com Seletor de Consolidação -->
    <div class="bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Contas a Pagar</h1>
                <p class="text-red-100">
                    <?php if ($modoConsolidacao): ?>
                        Visualizando <?= $empresasAtivas ?> empresas consolidadas
                    <?php else: ?>
                        Gerencie suas despesas e pagamentos
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <?php include __DIR__ . '/../components/seletor-consolidacao.php'; ?>
                <a href="/contas-pagar/create" class="bg-white text-red-600 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Nova Conta</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros Avançados -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8" x-data="{ showFilters: false }">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Filtros</h2>
            <button @click="showFilters = !showFilters" class="text-blue-600 hover:text-blue-700 font-medium">
                <span x-show="!showFilters">Mostrar Filtros</span>
                <span x-show="showFilters">Ocultar Filtros</span>
            </button>
        </div>
        
        <form method="GET" action="/contas-pagar" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="pendente" <?= ($filters['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="vencido" <?= ($filters['status'] ?? '') == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="parcial" <?= ($filters['status'] ?? '') == 'parcial' ? 'selected' : '' ?>>Parcial</option>
                        <option value="pago" <?= ($filters['status'] ?? '') == 'pago' ? 'selected' : '' ?>>Pago</option>
                        <option value="cancelado" <?= ($filters['status'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>

                <?php if (!$modoConsolidacao): ?>
                <!-- Empresa (só aparece se não estiver em modo consolidação) -->
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
                <?php endif; ?>

                <!-- Fornecedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fornecedor</label>
                    <select name="fornecedor_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <option value="<?= $fornecedor['id'] ?>" <?= ($filters['fornecedor_id'] ?? '') == $fornecedor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fornecedor['nome_razao_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria</label>
                    <select name="categoria_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Início -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                    <input type="date" name="data_inicio" value="<?= $filters['data_inicio'] ?? '' ?>" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Data Fim -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                    <input type="date" name="data_fim" value="<?= $filters['data_fim'] ?? '' ?>" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Busca -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                    <input type="text" name="search" value="<?= $filters['search'] ?? '' ?>" 
                           placeholder="Descrição ou número do documento..."
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="/contas-pagar" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Limpar
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Contas -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if (!empty($contasPagar)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-red-600 to-rose-600">
                        <tr>
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
                                <?php if ($conta['tem_rateio']): ?>
                                    <span class="inline-block mt-1 px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full">
                                        Rateado
                                    </span>
                                <?php endif; ?>
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
                                        <a href="/contas-pagar/<?= $conta['id'] ?>/baixar" class="text-green-600 hover:text-green-900 dark:hover:text-green-400" title="Baixar/Pagar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </a>
                                        <a href="/contas-pagar/<?= $conta['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-900 dark:hover:text-yellow-400" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="/contas-pagar/<?= $conta['id'] ?>/delete" class="inline" onsubmit="return confirm('Tem certeza que deseja cancelar esta conta?')">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" title="Cancelar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
