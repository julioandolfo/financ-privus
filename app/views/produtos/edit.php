<?php
$fotoModel = new \App\Models\ProdutoFoto();
$variacaoModel = new \App\Models\ProdutoVariacao();
$categoriaModel = new \App\Models\CategoriaProduto();

$fotos = $fotoModel->findByProduto($produto['id']);
$variacoes = $variacaoModel->findByProduto($produto['id']);
$empresaId = $this->session->get('empresa_id');
$categorias = $categoriaModel->getFlatList($empresaId);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="produtoEditForm()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="<?= $this->baseUrl('/produtos') ?>" 
               class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Produto</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Atualize as informações do produto</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button @click="currentTab = 'dados'" 
                        :class="currentTab === 'dados' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    📋 Dados Básicos
                </button>
                <button @click="currentTab = 'fotos'" 
                        :class="currentTab === 'fotos' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    📸 Fotos (<?= count($fotos) ?>)
                </button>
                <button @click="currentTab = 'variacoes'" 
                        :class="currentTab === 'variacoes' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    🎨 Variações (<?= count($variacoes) ?>)
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab: Dados Básicos -->
    <div x-show="currentTab === 'dados'" x-transition>
        <form method="POST" action="<?= $this->baseUrl('/produtos/' . $produto['id']) ?>">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 space-y-6">
                
                <!-- Código e Nome -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Código *
                        </label>
                        <input type="text" name="codigo" required
                               value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? $produto['codigo']) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Nome *
                        </label>
                        <input type="text" name="nome" required
                               value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $produto['nome']) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Categoria e Código de Barras -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Categoria
                        </label>
                        <select name="categoria_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($produto['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= str_repeat('—', $cat['level']) ?> <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Código de Barras (EAN-13)
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="codigo_barras" x-ref="codigoBarras"
                                   value="<?= htmlspecialchars($produto['codigo_barras'] ?? '') ?>"
                                   maxlength="13"
                                   class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button type="button" @click="gerarCodigoBarras()"
                                    class="px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                🔄
                            </button>
                            <button type="button" @click="scanearCodigoBarras()"
                                    class="px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700" title="Escanear código de barras">
                                📷
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
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
                </div>

                <!-- Valores -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Custo Unitário *
                        </label>
                        <input type="text" name="custo_unitario" required
                               x-model="custoUnitario"
                               @input="calcularMargem()"
                               data-mask="currency"
                               value="<?= htmlspecialchars($produto['custo_unitario']) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Preço de Venda *
                        </label>
                        <input type="text" name="preco_venda" required
                               x-model="precoVenda"
                               @input="calcularMargem()"
                               data-mask="currency"
                               value="<?= htmlspecialchars($produto['preco_venda']) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

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

                <!-- Unidade e Estoque -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Unidade *
                        </label>
                        <select name="unidade_medida" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="UN" <?= $produto['unidade_medida'] === 'UN' ? 'selected' : '' ?>>Unidade</option>
                            <option value="KG" <?= $produto['unidade_medida'] === 'KG' ? 'selected' : '' ?>>Quilograma</option>
                            <option value="L" <?= $produto['unidade_medida'] === 'L' ? 'selected' : '' ?>>Litro</option>
                            <option value="M" <?= $produto['unidade_medida'] === 'M' ? 'selected' : '' ?>>Metro</option>
                            <option value="CX" <?= $produto['unidade_medida'] === 'CX' ? 'selected' : '' ?>>Caixa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Estoque Atual
                        </label>
                        <input type="number" name="estoque" min="0"
                               value="<?= htmlspecialchars($produto['estoque'] ?? 0) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Estoque Mínimo
                        </label>
                        <input type="number" name="estoque_minimo" min="0"
                               value="<?= htmlspecialchars($produto['estoque_minimo'] ?? 0) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="<?= $this->baseUrl('/produtos') ?>" 
                       class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tab: Fotos -->
    <div x-show="currentTab === 'fotos'" x-transition>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            
            <!-- Upload Area -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Upload de Fotos</h3>
                
                <div @drop.prevent="handleDrop($event)" 
                     @dragover.prevent="dragover = true" 
                     @dragleave.prevent="dragover = false"
                     :class="dragover ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                     class="border-2 border-dashed rounded-xl p-8 text-center transition-colors">
                    
                    <input type="file" 
                           x-ref="fileInput" 
                           @change="uploadFoto($event)" 
                           accept="image/*" 
                           multiple
                           class="hidden">
                    
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 mb-2">Arraste fotos aqui ou</p>
                        <button type="button" 
                                @click="$refs.fileInput.click()"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Selecionar Arquivos
                        </button>
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF até 5MB</p>
                    </div>
                </div>
            </div>

            <!-- Galeria -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($fotos as $foto): ?>
                    <div class="relative group">
                        <img src="<?= htmlspecialchars($foto['caminho']) ?>" 
                             alt="Foto do produto"
                             class="w-full h-48 object-cover rounded-lg border-2 <?= $foto['principal'] ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' ?>">
                        
                        <?php if ($foto['principal']): ?>
                            <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                Principal
                            </span>
                        <?php endif; ?>
                        
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                            <?php if (!$foto['principal']): ?>
                                <button @click="setFotoPrincipal(<?= $foto['id'] ?>)"
                                        class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" title="Definir como principal">
                                    ⭐
                                </button>
                            <?php endif; ?>
                            <button @click="deleteFoto(<?= $foto['id'] ?>)"
                                    class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700" title="Excluir">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($fotos)): ?>
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma foto cadastrada</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab: Variações -->
    <div x-show="currentTab === 'variacoes'" x-transition>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            
            <!-- Botão Adicionar -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Variações do Produto</h3>
                <button @click="modalVariacao = true; variacaoEditando = null"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    + Nova Variação
                </button>
            </div>

            <!-- Lista de Variações -->
            <div class="space-y-4">
                <?php foreach ($variacoes as $variacao): ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($variacao['nome']) ?></h4>
                                <?php if ($variacao['sku']): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">SKU: <?= htmlspecialchars($variacao['sku']) ?></p>
                                <?php endif; ?>
                                <?php if ($variacao['codigo_barras']): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Código de Barras: <?= htmlspecialchars($variacao['codigo_barras']) ?></p>
                                <?php endif; ?>
                                
                                <!-- Atributos -->
                                <?php if (!empty($variacao['atributos'])): ?>
                                    <div class="flex gap-2 mt-2">
                                        <?php foreach ($variacao['atributos'] as $key => $value): ?>
                                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded">
                                                <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-right ml-4">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ <?= number_format($variacao['preco_venda'], 2, ',', '.') ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Estoque: <?= $variacao['estoque'] ?></p>
                                
                                <div class="flex gap-2 mt-2">
                                    <button class="text-blue-600 hover:text-blue-800" title="Editar">✏️</button>
                                    <form method="POST" action="<?= $this->baseUrl('/produtos/variacoes/' . $variacao['id'] . '/delete') ?>" class="inline">
                                        <button type="submit" onclick="return confirm('Excluir esta variação?')" class="text-red-600 hover:text-red-800" title="Excluir">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($variacoes)): ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma variação cadastrada</p>
                        <p class="text-sm text-gray-400 mt-2">Adicione variações como tamanhos, cores, etc.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Scanner -->
    <div x-show="showScanner" 
         x-transition
         class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4"
         @click.self="fecharScanner()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Scanner de Código de Barras</h3>
                <button @click="fecharScanner()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="scanner-container" class="relative w-full h-96 bg-black rounded-lg overflow-hidden">
                <video x-ref="scannerVideo" class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-64 h-32 border-2 border-red-500"></div>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4 text-center">
                Posicione o código de barras dentro do quadrado
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
<script>
function produtoEditForm() {
    return {
        currentTab: 'dados',
        custoUnitario: <?= $produto['custo_unitario'] ?>,
        precoVenda: <?= $produto['preco_venda'] ?>,
        margem: 0,
        dragover: false,
        showScanner: false,
        modalVariacao: false,
        variacaoEditando: null,
        
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
        },
        
        async gerarCodigoBarras() {
            try {
                const response = await fetch('<?= $this->baseUrl('/produtos/gerar-codigo-barras') ?>');
                const data = await response.json();
                if (data.success) {
                    this.$refs.codigoBarras.value = data.codigo;
                }
            } catch (error) {
                alert('Erro ao gerar código de barras');
            }
        },
        
        async scanearCodigoBarras() {
            this.showScanner = true;
            await this.$nextTick();
            
            try {
                const codeReader = new ZXing.BrowserMultiFormatReader();
                const videoElement = this.$refs.scannerVideo;
                
                codeReader.decodeFromVideoDevice(null, videoElement, (result, err) => {
                    if (result) {
                        this.$refs.codigoBarras.value = result.text;
                        this.fecharScanner();
                        codeReader.reset();
                    }
                });
            } catch (error) {
                console.error('Erro ao iniciar scanner:', error);
                alert('Erro ao acessar câmera');
                this.fecharScanner();
            }
        },
        
        fecharScanner() {
            this.showScanner = false;
        },
        
        handleDrop(e) {
            this.dragover = false;
            const files = e.dataTransfer.files;
            this.uploadFiles(files);
        },
        
        uploadFoto(e) {
            const files = e.target.files;
            this.uploadFiles(files);
        },
        
        async uploadFiles(files) {
            for (let file of files) {
                const formData = new FormData();
                formData.append('foto', file);
                
                try {
                    const response = await fetch('<?= $this->baseUrl('/produtos/' . $produto['id'] . '/upload-foto') ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro: ' + data.error);
                    }
                } catch (error) {
                    alert('Erro ao fazer upload');
                }
            }
        },
        
        async setFotoPrincipal(fotoId) {
            try {
                const response = await fetch(`<?= $this->baseUrl('/produtos/fotos/') ?>${fotoId}/principal`, {
                    method: 'POST'
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Erro ao definir foto principal');
            }
        },
        
        async deleteFoto(fotoId) {
            if (!confirm('Excluir esta foto?')) return;
            
            try {
                const response = await fetch(`<?= $this->baseUrl('/produtos/fotos/') ?>${fotoId}/delete`, {
                    method: 'POST'
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Erro ao excluir foto');
            }
        }
    }
}
</script>

<?php 
$this->session->delete('old');
$this->session->delete('errors');
?>
