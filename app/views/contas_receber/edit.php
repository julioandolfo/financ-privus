<?php
$errors = $this->session->get('errors') ?? [];
$old = $this->session->get('old') ?? [];
?>

<div class="max-w-5xl mx-auto animate-fade-in">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Editar Conta a Receber</h1>
            <p class="text-green-100 mt-2">Atualize as informações da receita</p>
        </div>

        <!-- Form -->
        <form method="POST" action="/contas-receber/<?= $conta['id'] ?>" class="p-8" x-data="contaReceberForm()">
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

                    <!-- Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente</label>
                        <select name="cliente_id" id="cliente_id" data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select name="categoria_id" id="categoria_id" required data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
                        </select>
                        <?php if (isset($errors['categoria_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $errors['categoria_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Centro de Custo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Centro de Custo</label>
                        <select name="centro_custo_id" id="centro_custo_id" data-placeholder="Carregando..."
                                class="select-search w-full">
                            <option value="">Carregando...</option>
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
                                  placeholder="Descreva a receita..."><?= htmlspecialchars($old['descricao'] ?? $conta['descricao']) ?></textarea>
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

            <!-- Rateio entre Empresas -->
            <?php if (!empty($rateios)): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Rateio entre Empresas</h2>
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-6">
                    <div class="space-y-4">
                        <?php foreach ($rateios as $index => $rateio): ?>
                        <div class="flex items-center space-x-4 bg-white dark:bg-gray-800 p-4 rounded-lg">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Empresa</label>
                                <p class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($rateio['empresa_nome'] ?? 'N/A') ?></p>
                            </div>
                            <div class="w-32">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Valor</label>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">R$ <?= number_format($rateio['valor_rateio'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">%</label>
                                <p class="font-semibold text-gray-900 dark:text-gray-100"><?= number_format($rateio['percentual'] ?? 0, 2, ',', '.') ?>%</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Para alterar o rateio, exclua a conta e crie uma nova.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Parcelas Existentes -->
            <?php if (!empty($parcelas)): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Parcelas da Conta</h2>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-indigo-200 dark:border-indigo-700">
                                    <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Parcela</th>
                                    <th class="text-left py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Vencimento</th>
                                    <th class="text-right py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Valor</th>
                                    <th class="text-center py-2 px-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parcelas as $parcela): ?>
                                <tr class="border-b border-indigo-100 dark:border-indigo-800">
                                    <td class="py-2 px-3"><?= $parcela['numero_parcela'] ?>/<?= count($parcelas) ?></td>
                                    <td class="py-2 px-3"><?= date('d/m/Y', strtotime($parcela['data_vencimento'])) ?></td>
                                    <td class="py-2 px-3 text-right font-semibold">R$ <?= number_format($parcela['valor_parcela'], 2, ',', '.') ?></td>
                                    <td class="py-2 px-3 text-center">
                                        <?php
                                        $statusClass = match($parcela['status'] ?? 'pendente') {
                                            'recebido' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'parcial' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'vencido' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        };
                                        $statusLabel = match($parcela['status'] ?? 'pendente') {
                                            'recebido' => 'Recebido',
                                            'parcial' => 'Parcial',
                                            'vencido' => 'Vencido',
                                            default => 'Pendente'
                                        };
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-sm text-indigo-600 dark:text-indigo-400">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        As parcelas podem ser gerenciadas individualmente na visualização da conta.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Observações -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações</label>
                <textarea name="observacoes" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                          placeholder="Observações adicionais..."><?= htmlspecialchars($old['observacoes'] ?? $conta['observacoes']) ?></textarea>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="/contas-receber/<?= $conta['id'] ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-medium shadow-lg">
                    Atualizar Conta a Receber
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function contaReceberForm() {
    return {
        valorTotal: <?= $old['valor_total'] ?? $conta['valor_total'] ?>,
        dataCompetencia: '<?= $old['data_competencia'] ?? $conta['data_competencia'] ?>',
        categoriaIdAtual: <?= json_encode($old['categoria_id'] ?? $conta['categoria_id'] ?? '') ?>,
        centroCustoIdAtual: <?= json_encode($old['centro_custo_id'] ?? $conta['centro_custo_id'] ?? '') ?>,
        clienteIdAtual: <?= json_encode($old['cliente_id'] ?? $conta['cliente_id'] ?? '') ?>,
        
        init() {
            // Carregar categorias, centros e clientes automaticamente ao abrir a página
            const empresaId = document.querySelector('select[name="empresa_id"]').value;
            if (empresaId) {
                this.carregarDadosEmpresa(empresaId);
            }
        },
        
        atualizarRateios() {
            // Placeholder para lógica de rateio se necessário
        },
        
        async carregarDadosEmpresa(empresaId) {
            if (!empresaId) {
                this.limparSelects();
                return;
            }
            
            // Carregar categorias de receita
            try {
                const respCategorias = await fetch(`/categorias?ajax=1&empresa_id=${empresaId}&tipo=receita`);
                const dataCategorias = await respCategorias.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataCategorias.success && dataCategorias.categorias) {
                    dataCategorias.categorias.forEach(cat => {
                        options.push({value: cat.id, text: cat.nome});
                    });
                }
                refreshSelectSearch('categoria_id', options, this.categoriaIdAtual);
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
            }
            
            // Carregar centros de custo
            try {
                const respCentros = await fetch(`/centros-custo?ajax=1&empresa_id=${empresaId}`);
                const dataCentros = await respCentros.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataCentros.success && dataCentros.centros) {
                    dataCentros.centros.forEach(centro => {
                        options.push({value: centro.id, text: centro.nome});
                    });
                }
                refreshSelectSearch('centro_custo_id', options, this.centroCustoIdAtual);
            } catch (error) {
                console.error('Erro ao carregar centros de custo:', error);
            }
            
            // Carregar clientes
            try {
                const respClientes = await fetch(`/clientes?ajax=1&empresa_id=${empresaId}`);
                const dataClientes = await respClientes.json();
                
                const options = [{value: '', text: 'Selecione...'}];
                if (dataClientes.success && dataClientes.clientes) {
                    dataClientes.clientes.forEach(cliente => {
                        options.push({value: cliente.id, text: cliente.nome_razao_social});
                    });
                }
                refreshSelectSearch('cliente_id', options, this.clienteIdAtual);
            } catch (error) {
                console.error('Erro ao carregar clientes:', error);
            }
        },
        
        carregarCategoriasECentros(empresaId) {
            // Limpar valores atuais ao trocar empresa
            this.categoriaIdAtual = '';
            this.centroCustoIdAtual = '';
            this.clienteIdAtual = '';
            this.carregarDadosEmpresa(empresaId);
        },
        
        limparSelects() {
            const emptyOptions = [{value: '', text: 'Selecione uma empresa primeiro...'}];
            refreshSelectSearch('categoria_id', emptyOptions);
            refreshSelectSearch('centro_custo_id', emptyOptions);
            refreshSelectSearch('cliente_id', emptyOptions);
        }
    }
}
</script>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
