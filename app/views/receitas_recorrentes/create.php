<?php
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
$dias = range(1, 31);
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<div class="container mx-auto max-w-5xl" x-data="receitaRecorrenteForm()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Nova Receita Recorrente</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure uma receita que se repete automaticamente</p>
        </div>
        <a href="/receitas-recorrentes" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            ← Voltar
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 rounded-xl">
            <ul class="list-disc list-inside text-red-700 dark:text-red-300">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/receitas-recorrentes">
        <?php $old = $old ?? []; $empresaId = $empresaId ?? null; include __DIR__ . '/_form_body.php'; ?>

        <!-- Botões -->
        <div class="flex justify-end space-x-4">
            <a href="/receitas-recorrentes" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-medium shadow-lg">
                Criar Receita Recorrente
            </button>
        </div>
    </form>
</div>

<script>
function receitaRecorrenteForm() {
    const valorInicial = parseFloat('<?= str_replace(',', '.', $old['valor'] ?? '0') ?>') || 0;
    const dataInicio = '<?= $old['data_inicio'] ?? date('Y-m-d') ?>';
    return {
        frequencia: '<?= $old['frequencia'] ?? 'mensal' ?>',
        reajusteAtivo: <?= isset($old['reajuste_ativo']) && $old['reajuste_ativo'] ? 'true' : 'false' ?>,
        temRateio: false,
        valorBase: valorInicial,
        rateios: [],
        
        toggleRateio() {
            if (this.temRateio && this.rateios.length === 0) this.adicionarRateio();
        },
        adicionarRateio() {
            this.rateios.push({
                empresa_id: '',
                valor_rateio: '',
                percentual: 0,
                data_competencia: dataInicio
            });
        },
        removerRateio(index) {
            this.rateios.splice(index, 1);
        },
        calcularPercentual(index) {
            const valorTotal = parseFloat(this.valorBase) || 0;
            if (valorTotal > 0 && this.rateios[index]) {
                this.rateios[index].percentual = (parseFloat(this.rateios[index].valor_rateio || 0) / valorTotal * 100).toFixed(2);
            }
        },
        atualizarPercentuaisRateios() {
            this.rateios.forEach((_, i) => this.calcularPercentual(i));
        },
        get totalRateado() {
            return this.rateios.reduce((sum, r) => sum + parseFloat(r.valor_rateio || 0), 0);
        }
    }
}

// Inicializa Tom Select nos campos
document.addEventListener('DOMContentLoaded', function() {
    const tomSelectConfig = {
        create: false,
        sortField: { field: "text", direction: "asc" },
        plugins: ['dropdown_input'],
        render: {
            no_results: function(data, escape) {
                return '<div class="no-results p-2 text-gray-500">Nenhum resultado para "' + escape(data.input) + '"</div>';
            }
        }
    };
    
    // Campos para aplicar Tom Select
    const selectFields = [
        '#select_empresa',
        '#select_cliente',
        '#select_categoria',
        '#select_centro_custo',
        '#select_forma_pagamento',
        '#select_conta_bancaria'
    ];
    
    selectFields.forEach(selector => {
        const el = document.querySelector(selector);
        if (el && typeof TomSelect !== 'undefined') {
            new TomSelect(el, {
                ...tomSelectConfig,
                allowEmptyOption: true
            });
        }
    });
});
</script>

<style>
/* Estilo para Tom Select no modo escuro */
.dark .ts-control {
    background-color: rgb(55 65 81) !important;
    border-color: rgb(75 85 99) !important;
    color: rgb(243 244 246) !important;
}
.dark .ts-control input {
    color: rgb(243 244 246) !important;
}
.dark .ts-dropdown {
    background-color: rgb(55 65 81) !important;
    border-color: rgb(75 85 99) !important;
    color: rgb(243 244 246) !important;
}
.dark .ts-dropdown .option {
    color: rgb(243 244 246) !important;
}
.dark .ts-dropdown .option:hover,
.dark .ts-dropdown .option.active {
    background-color: rgb(75 85 99) !important;
}
.ts-control {
    padding: 0.75rem 1rem !important;
    border-radius: 0.75rem !important;
}
</style>
