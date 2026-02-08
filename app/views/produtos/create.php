<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Novo Produto</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Cadastre um novo produto no seu catálogo</p>
    </div>

    <!-- Formulário -->
    <form method="POST" action="/produtos" x-data="produtoForm()" @submit="prepararSubmit($event)">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6">
            
            <!-- Código, SKU e Nome -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Código *
                    </label>
                    <input type="text" name="codigo" required
                           value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['codigo']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                           placeholder="Ex: PROD001">
                    <?php if (isset($this->session->get('errors')['codigo'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['codigo'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        SKU
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-normal ml-1">(Código único)</span>
                    </label>
                    <input type="text" name="sku"
                           value="<?= htmlspecialchars($this->session->get('old')['sku'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['sku']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                           placeholder="Ex: SKU-001">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Identificador único para integração via API</p>
                    <?php if (isset($this->session->get('errors')['sku'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['sku'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nome -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Nome *
                    </label>
                    <input type="text" name="nome" required
                           value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nome do produto">
                    <?php if (isset($this->session->get('errors')['nome'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categoria e Código de Barras -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Categoria
                    </label>
                    <select name="categoria_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione uma categoria</option>
                        <?php 
                        $categoriaModel = new \App\Models\CategoriaProduto();
                        $empresaId = $this->session->get('empresa_id');
                        $categorias = $categoriaModel->getFlatList($empresaId);
                        foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($this->session->get('old')['categoria_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= str_repeat('—', $cat['level']) ?> <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Código de Barras -->
                <div x-data="{ gerandoCodigo: false }">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Código de Barras (EAN-13)
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="codigo_barras" x-ref="codigoBarras"
                               value="<?= htmlspecialchars($this->session->get('old')['codigo_barras'] ?? '') ?>"
                               maxlength="13"
                               class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="7891234567890">
                        <button type="button" @click="gerarCodigoBarras()"
                                class="px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors whitespace-nowrap"
                                :disabled="gerandoCodigo">
                            <svg x-show="!gerandoCodigo" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg x-show="gerandoCodigo" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Descrição
                </label>
                <textarea name="descricao" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Descrição detalhada do produto..."><?= htmlspecialchars($this->session->get('old')['descricao'] ?? '') ?></textarea>
            </div>

            <!-- Valores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Custo Unitário -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Custo Unitário *
                    </label>
                    <input type="text" id="custo_unitario" name="custo_unitario" required
                           value="<?= htmlspecialchars($this->session->get('old')['custo_unitario'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['custo_unitario']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0,00">
                    <?php if (isset($this->session->get('errors')['custo_unitario'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['custo_unitario'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Preço de Venda -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Preço de Venda *
                    </label>
                    <input type="text" id="preco_venda" name="preco_venda" required
                           value="<?= htmlspecialchars($this->session->get('old')['preco_venda'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['preco_venda']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0,00">
                    <?php if (isset($this->session->get('errors')['preco_venda'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['preco_venda'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Margem (calculada) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Margem de Lucro
                    </label>
                    <div class="flex items-center h-[52px] px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900">
                        <span x-text="margem.toFixed(1) + '%'" 
                              :class="margem > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                              class="text-lg font-bold"></span>
                    </div>
                </div>
            </div>

            <!-- Unidade de Medida e Estoque -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Unidade de Medida *
                    </label>
                    <select name="unidade_medida" required
                            class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['unidade_medida']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="UN" <?= ($this->session->get('old')['unidade_medida'] ?? 'UN') === 'UN' ? 'selected' : '' ?>>Unidade (UN)</option>
                        <option value="KG" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'KG' ? 'selected' : '' ?>>Quilograma (KG)</option>
                        <option value="G" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'G' ? 'selected' : '' ?>>Grama (G)</option>
                        <option value="L" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'L' ? 'selected' : '' ?>>Litro (L)</option>
                        <option value="ML" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'ML' ? 'selected' : '' ?>>Mililitro (ML)</option>
                        <option value="M" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'M' ? 'selected' : '' ?>>Metro (M)</option>
                        <option value="M2" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'M2' ? 'selected' : '' ?>>Metro Quadrado (M²)</option>
                        <option value="M3" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'M3' ? 'selected' : '' ?>>Metro Cúbico (M³)</option>
                        <option value="CX" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'CX' ? 'selected' : '' ?>>Caixa (CX)</option>
                        <option value="PC" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'PC' ? 'selected' : '' ?>>Peça (PC)</option>
                        <option value="PAR" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'PAR' ? 'selected' : '' ?>>Par (PAR)</option>
                        <option value="DZ" <?= ($this->session->get('old')['unidade_medida'] ?? '') === 'DZ' ? 'selected' : '' ?>>Dúzia (DZ)</option>
                    </select>
                    <?php if (isset($this->session->get('errors')['unidade_medida'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['unidade_medida'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Estoque Atual -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Estoque Atual
                    </label>
                    <input type="number" name="estoque" min="0" step="1"
                           value="<?= htmlspecialchars($this->session->get('old')['estoque'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0">
                </div>

                <!-- Estoque Mínimo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Estoque Mínimo
                    </label>
                    <input type="number" name="estoque_minimo" min="0" step="1"
                           value="<?= htmlspecialchars($this->session->get('old')['estoque_minimo'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0">
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="/produtos" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Cadastrar Produto
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/imask@6.4.3/dist/imask.min.js"></script>
<script>
function produtoForm() {
    return {
        margem: 0,
        custoMask: null,
        precoMask: null,
        
        init() {
            this.initMasks();
        },
        
        initMasks() {
            const self = this;
            
            // Configuração comum para ambas as máscaras
            const maskOptions = {
                mask: Number,
                scale: 2,
                signed: false,
                thousandsSeparator: '.',
                padFractionalZeros: true,
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.'],
                min: 0,
                max: 999999999.99
            };
            
            // Máscara para custo unitário
            const custoElement = document.getElementById('custo_unitario');
            if (custoElement) {
                this.custoMask = IMask(custoElement, maskOptions);
                
                // Formata valor inicial
                const valorInicial = parseFloat(custoElement.value) || 0;
                this.custoMask.value = valorInicial.toFixed(2);
                
                // Listener para recalcular margem
                this.custoMask.on('accept', function() {
                    self.calcularMargem();
                });
            }
            
            // Máscara para preço de venda
            const precoElement = document.getElementById('preco_venda');
            if (precoElement) {
                this.precoMask = IMask(precoElement, maskOptions);
                
                // Formata valor inicial
                const valorInicial = parseFloat(precoElement.value) || 0;
                this.precoMask.value = valorInicial.toFixed(2);
                
                // Listener para recalcular margem
                this.precoMask.on('accept', function() {
                    self.calcularMargem();
                });
            }
        },
        
        calcularMargem() {
            let custo = 0;
            let preco = 0;
            
            if (this.custoMask) {
                custo = parseFloat(this.custoMask.typedValue) || 0;
            }
            
            if (this.precoMask) {
                preco = parseFloat(this.precoMask.typedValue) || 0;
            }
            
            if (custo > 0) {
                const lucro = preco - custo;
                this.margem = (lucro / custo) * 100;
            } else {
                this.margem = 0;
            }
        },
        
        prepararSubmit(event) {
            // Converte os valores de moeda para o formato correto antes de enviar
            const custoInput = document.getElementById('custo_unitario');
            const precoInput = document.getElementById('preco_venda');
            
            if (custoInput && this.custoMask) {
                const valorNumerico = this.custoMask.typedValue;
                custoInput.value = valorNumerico;
            }
            
            if (precoInput && this.precoMask) {
                const valorNumerico = this.precoMask.typedValue;
                precoInput.value = valorNumerico;
            }
        },
        
        async gerarCodigoBarras() {
            try {
                const response = await fetch('/produtos/gerar-codigo-barras');
                const data = await response.json();
                if (data.success) {
                    this.$refs.codigoBarras.value = data.codigo;
                }
            } catch (error) {
                console.error('Erro ao gerar código de barras:', error);
                alert('Erro ao gerar código de barras');
            }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
