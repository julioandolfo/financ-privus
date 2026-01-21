<?php
$title = 'Revisar Transações do Extrato';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">📋 Revisar Transações</h1>
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
    <form id="cadastrarForm" method="POST" action="/extrato-bancario/cadastrar">
        <input type="hidden" name="empresa_id" value="<?= $empresaId ?>">
        
        <!-- Tabela de Transações -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider w-12">
                                <input type="checkbox" id="selectAll" class="rounded">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fornecedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Vencimento</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($transacoes as $index => $transacao): ?>
                            <?php 
                            $padrao = $transacao['padrao'] ?? null;
                            $temPadrao = !empty($padrao);
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" data-index="<?= $index ?>">
                                <!-- Checkbox -->
                                <td class="px-4 py-3">
                                    <input type="checkbox" 
                                           name="transacoes[<?= $index ?>][selecionada]" 
                                           value="1" 
                                           class="row-checkbox rounded"
                                           checked>
                                </td>
                                
                                <!-- Data -->
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <?= date('d/m/Y', strtotime($transacao['data'])) ?>
                                </td>
                                
                                <!-- Descrição -->
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($transacao['descricao']) ?>
                                    </div>
                                    <?php if ($temPadrao): ?>
                                        <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                            ✓ Padrão aplicado
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Valor -->
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    R$ <?= number_format($transacao['valor'], 2, ',', '.') ?>
                                </td>
                                
                                <!-- Categoria -->
                                <td class="px-4 py-3">
                                    <select name="transacoes[<?= $index ?>][categoria_id]" required
                                            class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id'] ?>" 
                                                    <?= ($padrao && $padrao['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                
                                <!-- Fornecedor -->
                                <td class="px-4 py-3">
                                    <select name="transacoes[<?= $index ?>][fornecedor_id]"
                                            class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($fornecedores as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" 
                                                    <?= ($padrao && $padrao['fornecedor_id'] == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_razao_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                
                                <!-- Vencimento -->
                                <td class="px-4 py-3">
                                    <input type="date" 
                                           name="transacoes[<?= $index ?>][data_vencimento]" 
                                           value="<?= $transacao['data'] ?>"
                                           required
                                           class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                </td>
                                
                                <!-- Ações -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" 
                                                onclick="excluirLinha(<?= $index ?>)"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                title="Excluir linha">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                onclick="salvarPadrao(<?= $index ?>)"
                                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Salvar como padrão">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Linha expandida com mais campos -->
                            <tr class="hidden expanded-row" data-parent="<?= $index ?>">
                                <td colspan="8" class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Centro de Custo -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Centro de Custo</label>
                                            <select name="transacoes[<?= $index ?>][centro_custo_id]"
                                                    class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
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
                                                    class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">Selecione...</option>
                                                <?php foreach ($contasBancarias as $cb): ?>
                                                    <option value="<?= $cb['id'] ?>" 
                                                            <?= ($padrao && $padrao['conta_bancaria_id'] == $cb['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cb['banco_nome'] ?? 'Conta') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Forma de Pagamento -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Forma de Pagamento</label>
                                            <select name="transacoes[<?= $index ?>][forma_pagamento_id]"
                                                    class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">Selecione...</option>
                                                <?php foreach ($formasPagamento as $fp): ?>
                                                    <option value="<?= $fp['id'] ?>" 
                                                            <?= ($padrao && $padrao['forma_pagamento_id'] == $fp['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($fp['nome']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Rateio -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                <input type="checkbox" 
                                                       name="transacoes[<?= $index ?>][tem_rateio]" 
                                                       value="1"
                                                       <?= ($padrao && $padrao['tem_rateio']) ? 'checked' : '' ?>
                                                       class="rounded">
                                                Tem Rateio
                                            </label>
                                        </div>
                                        
                                        <!-- Observações -->
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                                            <textarea name="transacoes[<?= $index ?>][observacoes]"
                                                      rows="2"
                                                      class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                      placeholder="Observações adicionais..."><?= htmlspecialchars($padrao['observacoes_padrao'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <!-- Salvar como Padrão -->
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                <input type="checkbox" 
                                                       name="transacoes[<?= $index ?>][salvar_padrao]" 
                                                       value="1"
                                                       class="rounded">
                                                Salvar configurações como padrão para esta descrição
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Botões de Ação -->
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span id="selectedCount"><?= count($transacoes) ?></span> transação(ões) selecionada(s)
            </div>
            <div class="flex space-x-4">
                <button type="button" 
                        onclick="window.location.href='/extrato-bancario'"
                        class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        id="cadastrarBtn"
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Cadastrar Selecionadas</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selecionar todas
    document.getElementById('selectAll').addEventListener('change', function(e) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = e.target.checked;
        });
        atualizarContador();
    });
    
    // Atualizar contador
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', atualizarContador);
    });
    
    function atualizarContador() {
        const selected = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = selected;
    }
    
    // Expandir linha ao clicar
    document.querySelectorAll('tbody tr').forEach(row => {
        if (!row.classList.contains('expanded-row')) {
            row.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'BUTTON') {
                    const index = this.dataset.index;
                    const expandedRow = document.querySelector(`tr.expanded-row[data-parent="${index}"]`);
                    if (expandedRow) {
                        expandedRow.classList.toggle('hidden');
                    }
                }
            });
        }
    });
});

// Excluir linha
function excluirLinha(index) {
    if (confirm('Deseja realmente excluir esta linha?')) {
        fetch('/extrato-bancario/excluir-linha', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ indice: index })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Salvar padrão
function salvarPadrao(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const expandedRow = document.querySelector(`tr.expanded-row[data-parent="${index}"]`);
    
    if (!row) {
        alert('Linha não encontrada');
        return;
    }
    
    const formData = new FormData();
    
    formData.append('indice', index);
    formData.append('empresa_id', <?= $empresaId ?>);
    
    // Campos da linha principal
    const categoriaSelect = row.querySelector('select[name*="[categoria_id]"]');
    const fornecedorSelect = row.querySelector('select[name*="[fornecedor_id]"]');
    
    if (!categoriaSelect || !categoriaSelect.value) {
        alert('Selecione uma categoria antes de salvar o padrão');
        return;
    }
    
    formData.append('categoria_id', categoriaSelect.value);
    formData.append('fornecedor_id', fornecedorSelect?.value || '');
    
    // Campos da linha expandida (se existir)
    if (expandedRow) {
        formData.append('centro_custo_id', expandedRow.querySelector('select[name*="[centro_custo_id]"]')?.value || '');
        formData.append('conta_bancaria_id', expandedRow.querySelector('select[name*="[conta_bancaria_id]"]')?.value || '');
        formData.append('forma_pagamento_id', expandedRow.querySelector('select[name*="[forma_pagamento_id]"]')?.value || '');
        formData.append('tem_rateio', expandedRow.querySelector('input[name*="[tem_rateio]"]')?.checked ? '1' : '0');
        formData.append('observacoes', expandedRow.querySelector('textarea[name*="[observacoes]"]')?.value || '');
    } else {
        formData.append('centro_custo_id', '');
        formData.append('conta_bancaria_id', '');
        formData.append('forma_pagamento_id', '');
        formData.append('tem_rateio', '0');
        formData.append('observacoes', '');
    }
    
    fetch('/extrato-bancario/salvar-padrao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Padrão salvo com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Erro ao salvar padrão'));
        }
    })
    .catch(error => {
        alert('Erro ao salvar padrão: ' + error.message);
    });
}

// Submeter formulário
document.getElementById('cadastrarForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const selected = document.querySelectorAll('.row-checkbox:checked').length;
    if (selected === 0) {
        alert('Selecione pelo menos uma transação para cadastrar');
        return;
    }
    
    if (!confirm(`Deseja cadastrar ${selected} transação(ões)?`)) {
        return;
    }
    
    const btn = document.getElementById('cadastrarBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Cadastrando...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/extrato-bancario/cadastrar', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '/contas-pagar';
        } else {
            alert('Erro: ' + (data.error || 'Erro ao cadastrar contas'));
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>Cadastrar Selecionadas</span>';
        }
    } catch (error) {
        alert('Erro ao cadastrar: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>Cadastrar Selecionadas</span>';
    }
});
</script>
