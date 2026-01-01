<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">DFC - Demonstrativo de Fluxo de Caixa</h1>
        <p class="text-gray-600 dark:text-gray-400">Método Direto - Análise das movimentações de caixa</p>
    </div>

    <!-- Filtros -->
    <form method="GET" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                <select name="empresa_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= $empresaSelecionada == $empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="<?= $dataInicio ?>" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                <input type="date" name="data_fim" value="<?= $dataFim ?>" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Saldo Inicial</h3>
            <p class="text-3xl font-bold">R$ <?= number_format($dfc['resumo']['saldo_inicial'], 2, ',', '.') ?></p>
        </div>
        
        <div class="bg-gradient-to-br from-<?= $dfc['resumo']['aumento_caixa'] >= 0 ? 'green' : 'red' ?>-500 to-<?= $dfc['resumo']['aumento_caixa'] >= 0 ? 'green' : 'red' ?>-600 rounded-2xl shadow-xl p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2"><?= $dfc['resumo']['aumento_caixa'] >= 0 ? 'Aumento' : 'Redução' ?> de Caixa</h3>
            <p class="text-3xl font-bold">R$ <?= number_format($dfc['resumo']['aumento_caixa'], 2, ',', '.') ?></p>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Saldo Final</h3>
            <p class="text-3xl font-bold">R$ <?= number_format($dfc['resumo']['saldo_final'], 2, ',', '.') ?></p>
        </div>
    </div>

    <!-- Atividades Operacionais -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <span class="w-3 h-8 bg-blue-600 rounded mr-3"></span>
            Atividades Operacionais
        </h2>
        
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Recebimentos de Clientes</span>
                <span class="text-green-600 dark:text-green-400 font-bold">R$ <?= number_format($dfc['operacional']['recebimentos_clientes'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Pagamentos a Fornecedores</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['operacional']['pagamentos_fornecedores'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Pagamentos de Salários</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['operacional']['pagamentos_salarios'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Pagamentos de Impostos</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['operacional']['pagamentos_impostos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Outros Recebimentos</span>
                <span class="text-green-600 dark:text-green-400 font-bold">R$ <?= number_format($dfc['operacional']['outros_recebimentos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Outros Pagamentos</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['operacional']['outros_pagamentos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-blue-100 dark:bg-blue-900/30 rounded-xl border-2 border-blue-500">
                <span class="font-bold text-gray-900 dark:text-gray-100 text-lg">Caixa Líquido das Atividades Operacionais</span>
                <span class="text-blue-600 dark:text-blue-400 font-bold text-xl">R$ <?= number_format($dfc['operacional']['total'], 2, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- Atividades de Investimento -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <span class="w-3 h-8 bg-amber-600 rounded mr-3"></span>
            Atividades de Investimento
        </h2>
        
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Compra de Ativos</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['investimento']['compra_ativos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Venda de Ativos</span>
                <span class="text-green-600 dark:text-green-400 font-bold">R$ <?= number_format($dfc['investimento']['venda_ativos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-amber-100 dark:bg-amber-900/30 rounded-xl border-2 border-amber-500">
                <span class="font-bold text-gray-900 dark:text-gray-100 text-lg">Caixa Líquido das Atividades de Investimento</span>
                <span class="text-amber-600 dark:text-amber-400 font-bold text-xl">R$ <?= number_format($dfc['investimento']['total'], 2, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- Atividades de Financiamento -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <span class="w-3 h-8 bg-purple-600 rounded mr-3"></span>
            Atividades de Financiamento
        </h2>
        
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Empréstimos Obtidos</span>
                <span class="text-green-600 dark:text-green-400 font-bold">R$ <?= number_format($dfc['financiamento']['emprestimos_obtidos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Pagamento de Empréstimos</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['financiamento']['pagamento_emprestimos'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Distribuição de Lucros</span>
                <span class="text-red-600 dark:text-red-400 font-bold">R$ <?= number_format($dfc['financiamento']['distribuicao_lucros'], 2, ',', '.') ?></span>
            </div>
            
            <div class="flex justify-between items-center p-4 bg-purple-100 dark:bg-purple-900/30 rounded-xl border-2 border-purple-500">
                <span class="font-bold text-gray-900 dark:text-gray-100 text-lg">Caixa Líquido das Atividades de Financiamento</span>
                <span class="text-purple-600 dark:text-purple-400 font-bold text-xl">R$ <?= number_format($dfc['financiamento']['total'], 2, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- Total Final -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="flex justify-between items-center">
            <span class="font-bold text-2xl">Aumento/Redução Líquida de Caixa</span>
            <span class="font-bold text-4xl">R$ <?= number_format($dfc['resumo']['aumento_caixa'], 2, ',', '.') ?></span>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
