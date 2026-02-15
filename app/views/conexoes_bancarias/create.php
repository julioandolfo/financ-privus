<?php
$old = $this->session->get('old') ?? [];
$errors = $this->session->get('errors') ?? [];
$camposJson = json_encode($campos_por_banco ?? [], JSON_UNESCAPED_UNICODE);
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="/conexoes-bancarias" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Nova Conexão Bancária
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Conecte sua conta bancária via API direta para sincronizar saldos e extratos automaticamente.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8" x-data="conexaoForm()">
        <?php if (empty($empresas_usuario)): ?>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4 mb-4">
                <p class="text-sm text-yellow-800 dark:text-yellow-200 font-semibold">Nenhuma empresa encontrada.</p>
            </div>
        <?php endif; ?>

        <form action="/conexoes-bancarias/store" method="POST" enctype="multipart/form-data">
            <!-- Empresa -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa <span class="text-red-500">*</span>
                </label>
                <select name="empresa_id" required
                        onchange="window.location.href='/conexoes-bancarias/create?empresa_id=' + this.value"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Selecione...</option>
                    <?php foreach ($empresas_usuario as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Banco -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Banco / Instituição <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php foreach ($bancos_disponiveis as $key => $banco): ?>
                    <label class="relative cursor-pointer" :class="bancoSelecionado === '<?= $key ?>' ? 'ring-2 ring-blue-500' : ''">
                        <input type="radio" name="banco" value="<?= $key ?>" class="sr-only" 
                               x-model="bancoSelecionado" @change="onBancoChange('<?= $key ?>')"
                               <?= ($old['banco'] ?? '') === $key ? 'checked' : '' ?>>
                        <div class="p-4 rounded-xl border-2 transition-all text-center hover:border-blue-400 dark:hover:border-blue-500"
                             :class="bancoSelecionado === '<?= $key ?>' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700'">
                            <div class="text-2xl mb-1"><?= $banco['icone'] === 'wallet' ? '💳' : '🏦' ?></div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-gray-100"><?= $banco['nome'] ?></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['banco'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $errors['banco'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo de Conta -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo de Conta
                </label>
                <select name="tipo" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="conta_corrente">Conta Corrente</option>
                    <option value="conta_poupanca">Conta Poupança</option>
                    <option value="cartao_credito">Cartão de Crédito</option>
                </select>
            </div>

            <!-- Identificação -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Identificação (Apelido)
                </label>
                <input type="text" name="identificacao" value="<?= htmlspecialchars($old['identificacao'] ?? '') ?>"
                       placeholder="Ex: Conta Principal, Conta Fornecedores"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            <!-- Vincular a Conta Bancária do Sistema -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Vincular a Conta do Sistema (Opcional)
                </label>
                <?php if (!empty($contas_bancarias)): ?>
                <select name="conta_bancaria_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Nenhuma (não vincular)</option>
                    <?php foreach ($contas_bancarias as $cb): ?>
                        <option value="<?= $cb['id'] ?>">
                            <?= htmlspecialchars(($cb['banco_nome'] ?? 'Banco') . ' - Ag: ' . ($cb['agencia'] ?? '') . ' Cc: ' . ($cb['conta'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Vincula o saldo real da API ao saldo da conta no sistema</p>
                <?php else: ?>
                <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma conta bancária cadastrada para esta empresa.
                    </p>
                    <a href="/contas-bancarias/create" class="inline-flex items-center gap-1 mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Criar conta bancária primeiro
                    </a>
                </div>
                <input type="hidden" name="conta_bancaria_id" value="">
                <?php endif; ?>
            </div>

            <!-- ============================================ -->
            <!-- Campos Dinâmicos por Banco (via Alpine.js) -->
            <!-- ============================================ -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6" x-show="bancoSelecionado" x-transition>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                    Credenciais <span x-text="bancoLabel" class="text-blue-600 dark:text-blue-400"></span>
                </h3>

                <template x-for="(campo, idx) in camposVisiveis" :key="campo.name">
                    <div class="mb-4">
                        <!-- Separador para seção de cobrança -->
                        <template x-if="campo.section === 'cobranca' && (idx === 0 || camposVisiveis[idx-1]?.section !== 'cobranca')">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 mb-3">
                                <h4 class="text-md font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Cobranca Bancaria (Boletos)
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Preencha para habilitar a emissao de boletos por esta conexao</p>
                            </div>
                        </template>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2" x-text="campo.label"></label>
                        
                        <!-- Text / Password -->
                        <template x-if="campo.type === 'text' || campo.type === 'password'">
                            <div>
                                <input :type="campo.type" :name="campo.name" :placeholder="campo.placeholder || ''"
                                       :required="campo.required"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </template>
                        
                        <!-- Textarea (certificados) -->
                        <template x-if="campo.type === 'textarea'">
                            <div>
                                <textarea :name="campo.name" rows="4" :placeholder="campo.placeholder || ''"
                                          :required="campo.required"
                                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono text-xs"></textarea>
                            </div>
                        </template>
                        
                        <!-- File upload (certificados PFX) -->
                        <template x-if="campo.type === 'file'">
                            <div x-data="{ fileName: '', hasFile: false }">
                                <label class="flex flex-col items-center justify-center w-full px-4 py-6 rounded-xl border-2 border-dashed cursor-pointer transition-all"
                                       :class="hasFile ? 'border-green-400 bg-green-50 dark:border-green-500 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20'">
                                    <div class="flex flex-col items-center" x-show="!hasFile">
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Clique para selecionar o certificado</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="campo.accept || '.pfx, .p12'"></span>
                                    </div>
                                    <div class="flex items-center gap-3" x-show="hasFile">
                                        <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-green-700 dark:text-green-400" x-text="fileName"></span>
                                        <span class="text-xs text-gray-400">(clique para trocar)</span>
                                    </div>
                                    <input type="file" :name="campo.name" :accept="campo.accept || '.pfx,.p12'"
                                           :required="campo.required" class="hidden"
                                           @change="if($event.target.files.length){ fileName = $event.target.files[0].name; hasFile = true; } else { hasFile = false; }">
                                </label>
                            </div>
                        </template>
                        
                        <!-- Select -->
                        <template x-if="campo.type === 'select'">
                            <div>
                                <select :name="campo.name" :required="campo.required"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <template x-for="(label, val) in campo.options" :key="val">
                                        <option :value="val" x-text="label" :selected="val === campo.default"></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                        
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="campo.help" x-text="campo.help"></p>
                    </div>
                </template>
            </div>

            <!-- Configurações de Sincronização -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Configurações de Sincronização</h3>
                
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_sync" value="1" checked
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Sincronização Automática</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Frequência</label>
                        <select name="frequencia_sync" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="manual">Manual</option>
                            <option value="horaria">A cada hora</option>
                            <option value="diaria" selected>Diária</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">O que sincronizar</label>
                        <select name="tipo_sync" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="ambos">Despesas e Receitas</option>
                            <option value="apenas_despesas">Apenas Despesas (débitos)</option>
                            <option value="apenas_receitas">Apenas Receitas (créditos)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Filtra quais transações serão importadas do extrato</p>
                    </div>
                </div>
            </div>

            <!-- Classificação Padrão -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Classificação Padrão</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Usadas quando a IA não conseguir classificar</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Categoria Padrão</label>
                        <select name="categoria_padrao_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Nenhuma</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['codigo'] . ' - ' . $cat['nome'] . ' (' . ucfirst($cat['tipo']) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Centro de Custo Padrão</label>
                        <select name="centro_custo_padrao_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Nenhum</option>
                            <?php foreach ($centros_custo as $cc): ?>
                                <option value="<?= $cc['id'] ?>"><?= htmlspecialchars($cc['codigo'] . ' - ' . $cc['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aprovacao_automatica" value="1"
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Aprovação Automática (transações com alta confiança)</span>
                    </label>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <a href="/conexoes-bancarias" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 text-center">
                    Cancelar
                </a>
                <button type="submit" :disabled="!bancoSelecionado"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    Criar Conexão
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function conexaoForm() {
    const todosOsCampos = <?= $camposJson ?>;
    const bancosLabels = <?= json_encode(array_map(fn($b) => $b['nome'], $bancos_disponiveis ?? []), JSON_UNESCAPED_UNICODE) ?>;
    
    return {
        bancoSelecionado: '<?= $old['banco'] ?? '' ?>',
        camposVisiveis: [],
        bancoLabel: '',
        
        onBancoChange(banco) {
            this.bancoSelecionado = banco;
            this.camposVisiveis = todosOsCampos[banco] || [];
            this.bancoLabel = bancosLabels[banco] || banco;
        },
        
        init() {
            if (this.bancoSelecionado) {
                this.onBancoChange(this.bancoSelecionado);
            }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
