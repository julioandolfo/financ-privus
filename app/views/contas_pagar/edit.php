<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
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
                        <select name="fornecedor_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= $fornecedor['id'] ?>" <?= ($old['fornecedor_id'] ?? $conta['fornecedor_id']) == $fornecedor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fornecedor['nome_razao_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select name="categoria_id" id="categoria_id" required
                                class="w-full px-4 py-3 rounded-xl border <?= isset($errors['categoria_id']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($old['categoria_id'] ?? $conta['categoria_id']) == $categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['categoria_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['categoria_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Centro de Custo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Centro de Custo</label>
                        <select name="centro_custo_id" id="centro_custo_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($centrosCusto as $centro): ?>
                                <option value="<?= $centro['id'] ?>" <?= ($old['centro_custo_id'] ?? $conta['centro_custo_id']) == $centro['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($centro['nome']) ?>
                                </option>
                            <?php endforeach; ?>
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
    return {
        valorTotal: <?= $old['valor_total'] ?? $conta['valor_total'] ?>,
        dataCompetencia: '<?= $old['data_competencia'] ?? $conta['data_competencia'] ?>',
        
        atualizarRateios() {
            // Placeholder para lógica de rateio se necessário
        },
        
        async carregarCategoriasECentros(empresaId) {
            if (!empresaId) {
                this.limparSelects();
                return;
            }
            
            const categoriaAtualId = document.getElementById('categoria_id').value;
            const centroAtualId = document.getElementById('centro_custo_id').value;
            
            // Carregar categorias de despesa
            try {
                const respCategorias = await fetch(`/categorias?ajax=1&empresa_id=${empresaId}&tipo=despesa`);
                const dataCategorias = await respCategorias.json();
                
                const selectCategoria = document.getElementById('categoria_id');
                selectCategoria.innerHTML = '<option value="">Selecione...</option>';
                
                if (dataCategorias.success && dataCategorias.categorias) {
                    dataCategorias.categorias.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nome;
                        if (cat.id == categoriaAtualId) option.selected = true;
                        selectCategoria.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
            }
            
            // Carregar centros de custo
            try {
                const respCentros = await fetch(`/centros-custo?ajax=1&empresa_id=${empresaId}`);
                const dataCentros = await respCentros.json();
                
                const selectCentro = document.getElementById('centro_custo_id');
                selectCentro.innerHTML = '<option value="">Selecione...</option>';
                
                if (dataCentros.success && dataCentros.centros) {
                    dataCentros.centros.forEach(centro => {
                        const option = document.createElement('option');
                        option.value = centro.id;
                        option.textContent = centro.nome;
                        if (centro.id == centroAtualId) option.selected = true;
                        selectCentro.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar centros de custo:', error);
            }
        },
        
        limparSelects() {
            document.getElementById('categoria_id').innerHTML = '<option value="">Selecione uma empresa primeiro...</option>';
            document.getElementById('centro_custo_id').innerHTML = '<option value="">Selecione uma empresa primeiro...</option>';
        }
    }
}
</script>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
