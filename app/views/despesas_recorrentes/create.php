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

<div class="container mx-auto max-w-5xl" x-data="despesaRecorrenteForm()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Nova Despesa Recorrente</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure uma despesa que se repete automaticamente</p>
        </div>
        <a href="/despesas-recorrentes" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
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

    <form method="POST" action="/despesas-recorrentes">
        <!-- Dados Básicos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Dados da Despesa
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fornecedor</label>
                    <select name="fornecedor_id" id="select_fornecedor" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione (opcional)...</option>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <option value="<?= $fornecedor['id'] ?>" <?= ($old['fornecedor_id'] ?? '') == $fornecedor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fornecedor['nome_razao_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria <span class="text-red-500">*</span></label>
                    <select name="categoria_id" id="select_categoria" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $categoria): ?>
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
                        <?php foreach ($centrosCusto as $cc): ?>
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
                           placeholder="Ex: Aluguel do escritório">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor <span class="text-red-500">*</span></label>
                    <input type="number" name="valor" value="<?= $old['valor'] ?? '' ?>" step="0.01" min="0.01" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="0,00">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Custo</label>
                    <div class="flex space-x-4 mt-3">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="tipo_custo" value="variavel" <?= ($old['tipo_custo'] ?? 'variavel') == 'variavel' ? 'checked' : '' ?> class="mr-2">
                            <span class="text-gray-700 dark:text-gray-300">Variável</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="tipo_custo" value="fixo" <?= ($old['tipo_custo'] ?? '') == 'fixo' ? 'checked' : '' ?> class="mr-2">
                            <span class="text-gray-700 dark:text-gray-300">Fixo</span>
                        </label>
                    </div>
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
                        <?php foreach ([1, 3, 5, 7, 10, 15, 30] as $dias): ?>
                            <option value="<?= $dias ?>" <?= ($old['antecedencia_dias'] ?? 5) == $dias ? 'selected' : '' ?>><?= $dias ?> dias antes</option>
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
                        <option value="pago" <?= ($old['status_inicial'] ?? '') == 'pago' ? 'selected' : '' ?>>Já Pago (baixa automática)</option>
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

        <!-- Pagamento Automático -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Pagamento Automático (opcional)
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Se o status inicial for "Já Pago", configure como será registrado o pagamento</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Pagamento</label>
                    <select name="forma_pagamento_id" id="select_forma_pagamento" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($formasPagamento as $forma): ?>
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
                        <?php foreach ($contasBancarias as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= ($old['conta_bancaria_id'] ?? '') == $conta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($conta['banco_nome'] . ' - Ag: ' . $conta['agencia'] . ' Cc: ' . $conta['conta']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-4">
            <a href="/despesas-recorrentes" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl hover:from-red-700 hover:to-rose-700 transition-all font-medium shadow-lg">
                Criar Despesa Recorrente
            </button>
        </div>
    </form>
</div>

<script>
function despesaRecorrenteForm() {
    return {
        frequencia: '<?= $old['frequencia'] ?? 'mensal' ?>',
        reajusteAtivo: <?= isset($old['reajuste_ativo']) && $old['reajuste_ativo'] ? 'true' : 'false' ?>
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
        '#select_fornecedor',
        '#select_categoria',
        '#select_centro_custo',
        '#select_forma_pagamento',
        '#select_conta_bancaria'
    ];
    
    const tomSelectInstances = {};
    selectFields.forEach(selector => {
        const el = document.querySelector(selector);
        if (el && typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(el, {
                ...tomSelectConfig,
                allowEmptyOption: true
            });
            tomSelectInstances[selector] = ts;
        }
    });
    
    // Ao trocar empresa, recarrega para atualizar categorias e centros de custo da empresa selecionada
    const tsEmpresa = tomSelectInstances['#select_empresa'];
    if (tsEmpresa) {
        tsEmpresa.on('change', function(val) {
            if (val && val !== '') {
                window.location = '/despesas-recorrentes/create?empresa_id=' + encodeURIComponent(val);
            }
        });
    } else {
        const empresaSelect = document.querySelector('#select_empresa');
        if (empresaSelect) {
            empresaSelect.addEventListener('change', function() {
                if (this.value) window.location = '/despesas-recorrentes/create?empresa_id=' + encodeURIComponent(this.value);
            });
        }
    }
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
