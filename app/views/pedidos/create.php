<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Novo Pedido Manual</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Crie um pedido manualmente</p>
            </div>
            <a href="/pedidos" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </a>
        </div>

        <form method="POST" action="/pedidos" x-data="pedidoForm()">
            <!-- Informações Básicas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número do Pedido *</label>
                    <input type="text" name="numero_pedido" required value="<?= $this->session->get('old')['numero_pedido'] ?? '' ?>" class="w-full px-4 py-3 rounded-xl border <?= isset($this->session->get('errors')['numero_pedido']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <?php if (isset($this->session->get('errors')['numero_pedido'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $this->session->get('errors')['numero_pedido'] ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data do Pedido *</label>
                    <input type="datetime-local" name="data_pedido" required value="<?= $this->session->get('old')['data_pedido'] ?? date('Y-m-d\TH:i') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente</label>
                    <select name="cliente_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= ($this->session->get('old')['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="pendente">Pendente</option>
                        <option value="processando">Processando</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Itens do Pedido -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Itens do Pedido</h3>
                    <button type="button" @click="adicionarItem" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">+ Adicionar Item</button>
                </div>

                <template x-for="(item, index) in itens" :key="index">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Produto</label>
                                <select :name="`itens[${index}][produto_id]`" @change="carregarProduto(index, $event.target.value)" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?= $produto['id'] ?>" data-nome="<?= htmlspecialchars($produto['nome']) ?>" data-preco="<?= $produto['preco_venda'] ?>" data-custo="<?= $produto['custo_unitario'] ?>">
                                            <?= htmlspecialchars($produto['codigo'] ? $produto['codigo'] . ' - ' : '') ?><?= htmlspecialchars($produto['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" :name="`itens[${index}][nome_produto]`" x-model="item.nome_produto">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Qtd</label>
                                <input type="number" :name="`itens[${index}][quantidade]`" x-model="item.quantidade" @input="calcularTotal(index)" step="0.001" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Unit.</label>
                                <input type="number" :name="`itens[${index}][valor_unitario]`" x-model="item.valor_unitario" @input="calcularTotal(index)" step="0.01" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <input type="hidden" :name="`itens[${index}][custo_unitario]`" x-model="item.custo_unitario">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removerItem(index)" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg">Remover</button>
                            </div>
                        </div>
                        <div class="mt-2 text-right">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total: </span>
                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">R$ <span x-text="formatarValor(item.total)"></span></span>
                        </div>
                    </div>
                </template>

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-right">
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">Valor Total do Pedido: </span>
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">R$ <span x-text="formatarValor(valorTotal)"></span></span>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <a href="/pedidos" class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-xl">Cancelar</a>
                <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-3 rounded-xl shadow-lg">Salvar Pedido</button>
            </div>
        </form>
    </div>
</div>

<script>
function pedidoForm() {
    return {
        itens: [{ produto_id: '', nome_produto: '', quantidade: 1, valor_unitario: 0, custo_unitario: 0, total: 0 }],
        
        get valorTotal() {
            return this.itens.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
        },
        
        adicionarItem() {
            this.itens.push({ produto_id: '', nome_produto: '', quantidade: 1, valor_unitario: 0, custo_unitario: 0, total: 0 });
        },
        
        removerItem(index) {
            if (this.itens.length > 1) {
                this.itens.splice(index, 1);
            }
        },
        
        carregarProduto(index, produtoId) {
            if (!produtoId) return;
            const select = event.target;
            const option = select.options[select.selectedIndex];
            this.itens[index].nome_produto = option.dataset.nome;
            this.itens[index].valor_unitario = parseFloat(option.dataset.preco || 0);
            this.itens[index].custo_unitario = parseFloat(option.dataset.custo || 0);
            this.calcularTotal(index);
        },
        
        calcularTotal(index) {
            const item = this.itens[index];
            item.total = (parseFloat(item.quantidade) || 0) * (parseFloat(item.valor_unitario) || 0);
        },
        
        formatarValor(valor) {
            return parseFloat(valor || 0).toFixed(2).replace('.', ',');
        }
    };
}
</script>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
