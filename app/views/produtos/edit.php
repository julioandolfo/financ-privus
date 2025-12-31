<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Editar Produto</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do produto</p>
    </div>

    <!-- Formulário -->
    <form method="POST" action="/produtos/<?= $produto['id'] ?>" x-data="produtoForm(<?= $produto['custo_unitario'] ?>, <?= $produto['preco_venda'] ?>)">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6">
            
            <!-- Código e Nome -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Código *
                    </label>
                    <input type="text" name="codigo" required
                           value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? $produto['codigo']) ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['codigo']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase">
                    <?php if (isset($this->session->get('errors')['codigo'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['codigo'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nome -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Nome *
                    </label>
                    <input type="text" name="nome" required
                           value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $produto['nome']) ?>"
                           class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($this->session->get('errors')['nome'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Descrição
                </label>
                <textarea name="descricao" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($this->session->get('old')['descricao'] ?? $produto['descricao'] ?? '') ?></textarea>
            </div>

            <!-- Valores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Custo Unitário -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Custo Unitário *
                    </label>
                    <input type="text" name="custo_unitario" required
                           x-model="custoUnitario"
                           @input="calcularMargem()"
                           data-mask="currency"
                           value="<?= number_format($this->session->get('old')['custo_unitario'] ?? $produto['custo_unitario'], 2, ',', '.') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Preço de Venda -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Preço de Venda *
                    </label>
                    <input type="text" name="preco_venda" required
                           x-model="precoVenda"
                           @input="calcularMargem()"
                           data-mask="currency"
                           value="<?= number_format($this->session->get('old')['preco_venda'] ?? $produto['preco_venda'], 2, ',', '.') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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

            <!-- Unidade de Medida -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Unidade de Medida *
                    </label>
                    <select name="unidade_medida" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php 
                        $unidadeAtual = $this->session->get('old')['unidade_medida'] ?? $produto['unidade_medida'];
                        $unidades = [
                            'UN' => 'Unidade (UN)', 'KG' => 'Quilograma (KG)', 'G' => 'Grama (G)',
                            'L' => 'Litro (L)', 'ML' => 'Mililitro (ML)', 'M' => 'Metro (M)',
                            'M2' => 'Metro Quadrado (M²)', 'M3' => 'Metro Cúbico (M³)',
                            'CX' => 'Caixa (CX)', 'PC' => 'Peça (PC)', 'PAR' => 'Par (PAR)', 'DZ' => 'Dúzia (DZ)'
                        ];
                        foreach ($unidades as $valor => $nome):
                        ?>
                            <option value="<?= $valor ?>" <?= $unidadeAtual === $valor ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="/produtos/<?= $produto['id'] ?>" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    Atualizar Produto
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function produtoForm(custoInicial, precoInicial) {
    return {
        custoUnitario: custoInicial,
        precoVenda: precoInicial,
        margem: 0,
        
        init() {
            this.calcularMargem();
        },
        
        calcularMargem() {
            const custo = this.parseValor(this.custoUnitario);
            const preco = this.parseValor(this.precoVenda);
            
            if (custo > 0) {
                const lucro = preco - custo;
                this.margem = (lucro / custo) * 100;
            } else {
                this.margem = 0;
            }
        },
        
        parseValor(valor) {
            if (typeof valor === 'number') return valor;
            if (!valor) return 0;
            
            valor = valor.toString().replace(/[^\d,]/g, '');
            valor = valor.replace(',', '.');
            
            return parseFloat(valor) || 0;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof applyMasks === 'function') {
        applyMasks();
    }
});
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
