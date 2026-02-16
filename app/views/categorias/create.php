<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/categorias') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            ➕ Nova Categoria Financeira
        </h1>
    </div>

    <!-- Formulário -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <form method="POST" action="<?= $this->baseUrl('/categorias') ?>" class="space-y-6" id="formCategoria">
            <!-- Empresas (Múltipla Seleção) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresas * <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Selecione uma ou mais)</span>
                </label>
                <div class="space-y-2 max-h-60 overflow-y-auto p-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                    <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer transition-colors">
                        <input type="checkbox" 
                               id="select_all_empresas"
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <span class="font-semibold text-blue-600 dark:text-blue-400">✓ Selecionar Todas</span>
                    </label>
                    <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>
                    <?php 
                    $empresasIds = $this->session->get('old')['empresa_ids'] ?? [];
                    foreach ($empresas as $empresa): 
                    ?>
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer transition-colors empresa-checkbox">
                            <input type="checkbox" 
                                   name="empresa_ids[]" 
                                   value="<?= $empresa['id'] ?>"
                                   <?= in_array($empresa['id'], $empresasIds) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 empresa-item">
                            <span class="text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($this->session->get('errors')['empresa_ids'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_ids'] ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">A categoria será criada para todas as empresas selecionadas</p>
                <?php endif; ?>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo *
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($this->session->get('old')['tipo'] ?? $defaultTipo ?? '') === 'receita' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="receita" 
                               <?= ($this->session->get('old')['tipo'] ?? $defaultTipo ?? '') === 'receita' ? 'checked' : '' ?>
                               class="w-5 h-5 text-green-600 focus:ring-green-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">💰 Receita</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Entradas financeiras</div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($this->session->get('old')['tipo'] ?? $defaultTipo ?? '') === 'despesa' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="despesa" 
                               <?= ($this->session->get('old')['tipo'] ?? $defaultTipo ?? '') === 'despesa' ? 'checked' : '' ?>
                               class="w-5 h-5 text-red-600 focus:ring-red-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">💸 Despesa</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Saídas financeiras</div>
                        </div>
                    </label>
                </div>
                <?php if (isset($this->session->get('errors')['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Código e Nome -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <?php 
                    $codigoObrigatorio = \App\Models\Configuracao::get('categorias.codigo_obrigatorio', false);
                    $codigoAutoGerado = \App\Models\Configuracao::get('categorias.codigo_auto_gerado', true);
                    $isCodigoRequired = $codigoObrigatorio || !$codigoAutoGerado;
                    ?>
                    <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Código <?= $isCodigoRequired ? '*' : '(Opcional)' ?>
                    </label>
                    <input type="text" 
                           id="codigo" 
                           name="codigo" 
                           value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? '') ?>"
                           maxlength="20"
                           pattern="[A-Z0-9._-]+"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['codigo']) ? 'border-red-500' : '' ?>" 
                           <?= $isCodigoRequired ? 'required' : '' ?>>
                    <?php if (isset($this->session->get('errors')['codigo'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['codigo'] ?></p>
                    <?php elseif ($codigoAutoGerado): ?>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Deixe em branco para gerar automaticamente (001, 002, 003...)
                            </span>
                        </p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ex: REC001, DESP001</p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome *
                    </label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? '') ?>"
                           minlength="3"
                           maxlength="255"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['nome']) ? 'border-red-500' : '' ?>" 
                           required>
                    <?php if (isset($this->session->get('errors')['nome'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categoria Pai -->
            <div>
                <label for="categoria_pai_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Categoria Pai (Opcional)
                </label>
                <select id="categoria_pai_id" 
                        name="categoria_pai_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['categoria_pai_id']) ? 'border-red-500' : '' ?>">
                    <option value="">Nenhuma (Categoria Principal)</option>
                    <?php foreach ($categoriasPai as $categoriaPai): ?>
                        <option value="<?= $categoriaPai['id'] ?>" <?= (($this->session->get('old')['categoria_pai_id'] ?? '') == $categoriaPai['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoriaPai['codigo']) ?> - <?= htmlspecialchars($categoriaPai['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Alerta para múltiplas empresas com categoria pai -->
                <div id="alerta-categoria-pai" class="hidden mt-3 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-semibold text-amber-800 dark:text-amber-300 mb-1">⚠️ Atenção: Múltiplas Empresas</h4>
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                A categoria pai selecionada pode não existir em todas as empresas. 
                                Nesse caso, a categoria será criada como <strong>principal</strong> nas empresas onde o pai não existe.
                            </p>
                            <p class="text-xs text-amber-600 dark:text-amber-500 mt-2">
                                💡 <strong>Dica:</strong> Para garantir a hierarquia em todas as empresas, selecione apenas uma empresa por vez.
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($this->session->get('errors')['categoria_pai_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['categoria_pai_id'] ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione uma categoria pai para criar uma subcategoria</p>
                <?php endif; ?>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($this->session->get('old')['ativo'] ?? '1') ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Categoria Ativa
                </label>
            </div>

            <!-- Incluir no Ponto de Equilíbrio -->
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       id="incluir_ponto_equilibrio" 
                       name="incluir_ponto_equilibrio" 
                       value="1"
                       <?= ($this->session->get('old')['incluir_ponto_equilibrio'] ?? '1') ? 'checked' : '' ?>
                       class="w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2">
                <label for="incluir_ponto_equilibrio" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Incluir no Ponto de Equilíbrio
                </label>
                <span class="text-xs text-gray-500 dark:text-gray-400">(Desmarque para excluir desta categoria do PE)</span>
            </div>

            <!-- Botões -->
            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Criar Categoria
                </button>
                <a href="<?= $this->baseUrl('/categorias') ?>" 
                   class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Atualiza lista de categorias pai quando empresa ou tipo mudar
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select_all_empresas');
    const empresaCheckboxes = document.querySelectorAll('.empresa-item');
    const tipoInputs = document.querySelectorAll('input[name="tipo"]');
    const categoriaPaiSelect = document.getElementById('categoria_pai_id');
    
    // Funcionalidade "Selecionar Todas"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            empresaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateCategoriasPai();
        });
    }
    
    // Atualiza estado do "Selecionar Todas" quando checkboxes de empresa mudarem
    empresaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Atualiza estado do "Selecionar Todas"
            const allChecked = Array.from(empresaCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(empresaCheckboxes).some(cb => cb.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
    });
    
    function updateCategoriasPai() {
        const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
        
        if (!tipo) {
            // Se não tiver tipo, mantém as categorias que já vieram do servidor
            return;
        }
        
        // Faz requisição AJAX para buscar categorias disponíveis do tipo selecionado
        // Não filtra por empresa porque agora são múltiplas empresas
        fetch(`<?= $this->baseUrl('/categorias') ?>?tipo=${tipo}&ajax=1`)
            .then(response => response.json())
            .then(data => {
                categoriaPaiSelect.innerHTML = '<option value="">Nenhuma (Categoria Principal)</option>';
                if (data.categorias && data.categorias.length > 0) {
                    data.categorias.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        // Mostra também a empresa da categoria para facilitar identificação
                        const empresaNome = cat.empresa_nome ? ` [${cat.empresa_nome}]` : '';
                        option.textContent = `${cat.codigo} - ${cat.nome}${empresaNome}`;
                        categoriaPaiSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Nenhuma categoria encontrada para este tipo';
                    categoriaPaiSelect.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar categorias:', error);
                categoriaPaiSelect.innerHTML = '<option value="">Erro ao carregar categorias</option>';
            });
    }
    
    tipoInputs.forEach(input => {
        input.addEventListener('change', updateCategoriasPai);
    });
    
    // Carrega categorias pai automaticamente se já tiver tipo selecionado ao carregar a página
    setTimeout(() => {
        const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
        if (tipo) {
            updateCategoriasPai();
        }
    }, 100);
    
    // Controle do alerta de múltiplas empresas com categoria pai
    const alertaCategoriaPai = document.getElementById('alerta-categoria-pai');
    
    function verificarAlertaCategoriaPai() {
        const empresasIds = Array.from(empresaCheckboxes).filter(cb => cb.checked);
        const categoriaPaiId = categoriaPaiSelect?.value;
        
        // Mostra alerta se: múltiplas empresas selecionadas E categoria pai escolhida
        if (empresasIds.length > 1 && categoriaPaiId) {
            alertaCategoriaPai?.classList.remove('hidden');
        } else {
            alertaCategoriaPai?.classList.add('hidden');
        }
    }
    
    // Monitora mudanças que podem afetar o alerta
    empresaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', verificarAlertaCategoriaPai);
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', verificarAlertaCategoriaPai);
    }
    
    if (categoriaPaiSelect) {
        categoriaPaiSelect.addEventListener('change', verificarAlertaCategoriaPai);
    }
    
    // Verifica ao carregar a página
    setTimeout(verificarAlertaCategoriaPai, 100);
    
    // Validação do formulário antes de submeter
    const form = document.getElementById('formCategoria');
    if (form) {
        form.addEventListener('submit', function(e) {
            const empresasIds = Array.from(empresaCheckboxes).filter(cb => cb.checked);
            
            if (empresasIds.length === 0) {
                e.preventDefault();
                alert('❌ Por favor, selecione pelo menos uma empresa!');
                
                // Scroll para o campo de empresas
                document.querySelector('.empresa-checkbox')?.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                return false;
            }
        });
    }
});
</script>

<?php 
// Limpa old e errors após exibição
$this->session->delete('old');
$this->session->delete('errors');
?>

