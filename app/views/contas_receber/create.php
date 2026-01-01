<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
?>

<div class="max-w-5xl mx-auto animate-fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Nova Conta a Receber</h1>
            <p class="text-green-100 mt-2">Cadastre uma nova receita/recebimento</p>
        </div>

        <!-- Form -->
        <form method="POST" action="/contas-receber" class="p-8" x-data="contaReceberForm()">
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
                                <option value="<?= $empresa['id'] ?>" <?= ($old['empresa_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['empresa_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['empresa_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente</label>
                        <select name="cliente_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= ($old['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome_razao_social']) ?>
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
                                <option value="<?= $categoria['id'] ?>" <?= ($old['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $centro['id'] ?>" <?= ($old['centro_custo_id'] ?? '') == $centro['id'] ? 'selected' : '' ?>>
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
                        <input type="text" name="numero_documento" value="<?= htmlspecialchars($old['numero_documento'] ?? '') ?>" required
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
                        <input type="number" name="valor_total" x-model="valorTotal" @input="atualizarRateios" step="0.01" min="0.01" value="<?= $old['valor_total'] ?? '' ?>" required
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
                                  placeholder="Descreva a receita..."><?= htmlspecialchars($old['descricao'] ?? '') ?></textarea>
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
                        <input type="date" name="data_emissao" value="<?= $old['data_emissao'] ?? date('Y-m-d') ?>" required
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
                        <input type="date" name="data_competencia" x-model="dataCompetencia" value="<?= $old['data_competencia'] ?? date('Y-m-d') ?>" required
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
                        <input type="date" name="data_vencimento" value="<?= $old['data_vencimento'] ?? date('Y-m-d') ?>" required
                               class="w-full px-4 py-3 rounded-xl border <?= isset($errors['data_vencimento']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['data_vencimento'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['data_vencimento'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rateio entre Empresas -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Rateio entre Empresas</h2>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="tem_rateio" value="1" x-model="temRateio" @change="toggleRateio"
                               class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativar Rateio</span>
                    </label>
                </div>

                <div x-show="temRateio" x-transition class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-6">
                    <div class="space-y-4">
                        <template x-for="(rateio, index) in rateios" :key="index">
                            <div class="flex items-end space-x-4 bg-white dark:bg-gray-800 p-4 rounded-lg">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empresa</label>
                                    <select :name="'rateios[' + index + '][empresa_id]'" x-model="rateio.empresa_id"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_fantasia']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor</label>
                                    <input type="number" :name="'rateios[' + index + '][valor_rateio]'" x-model="rateio.valor" step="0.01" @input="calcularPercentual(index)"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                </div>
                                <div class="w-24">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">%</label>
                                    <input type="number" :name="'rateios[' + index + '][percentual]'" x-model="rateio.percentual" step="0.01" readonly
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Competência</label>
                                    <input type="date" :name="'rateios[' + index + '][data_competencia]'" x-model="rateio.data_competencia"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                </div>
                                <button type="button" @click="removerRateio(index)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="adicionarRateio" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        + Adicionar Empresa
                    </button>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>Total Rateado:</strong> R$ <span x-text="totalRateado.toFixed(2)"></span> 
                            (<span x-text="totalPercentual.toFixed(2)"></span>%)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações</label>
                <textarea name="observacoes" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                          placeholder="Observações adicionais..."><?= htmlspecialchars($old['observacoes'] ?? '') ?></textarea>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="/contas-receber" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-medium shadow-lg">
                    Salvar Conta a Receber
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function contaReceberForm() {
    return {
        temRateio: false,
        valorTotal: 0,
        dataCompetencia: '<?= date('Y-m-d') ?>',
        rateios: [],
        totalRateado: 0,
        totalPercentual: 0,
        
        toggleRateio() {
            if (this.temRateio && this.rateios.length === 0) {
                this.adicionarRateio();
            }
        },
        
        adicionarRateio() {
            this.rateios.push({
                empresa_id: '',
                valor: 0,
                percentual: 0,
                data_competencia: this.dataCompetencia
            });
        },
        
        removerRateio(index) {
            this.rateios.splice(index, 1);
            this.calcularTotais();
        },
        
        calcularPercentual(index) {
            if (this.valorTotal > 0) {
                this.rateios[index].percentual = (this.rateios[index].valor / this.valorTotal * 100).toFixed(2);
            }
            this.calcularTotais();
        },
        
        atualizarRateios() {
            this.rateios.forEach((rateio, index) => {
                this.calcularPercentual(index);
            });
        },
        
        calcularTotais() {
            this.totalRateado = this.rateios.reduce((sum, r) => sum + parseFloat(r.valor || 0), 0);
            this.totalPercentual = this.rateios.reduce((sum, r) => sum + parseFloat(r.percentual || 0), 0);
        },
        
        async carregarCategoriasECentros(empresaId) {
            if (!empresaId) {
                this.limparSelects();
                return;
            }
            
            // Carregar categorias de receita
            try {
                const respCategorias = await fetch(`/categorias?ajax=1&empresa_id=${empresaId}&tipo=receita`);
                const dataCategorias = await respCategorias.json();
                
                const selectCategoria = document.getElementById('categoria_id');
                selectCategoria.innerHTML = '<option value="">Selecione...</option>';
                
                if (dataCategorias.success && dataCategorias.categorias) {
                    dataCategorias.categorias.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nome;
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
