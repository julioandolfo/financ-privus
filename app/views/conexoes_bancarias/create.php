<?php
$old = $this->session->get('old') ?? [];
$errors = $this->session->get('errors') ?? [];
?>

<div class="max-w-3xl mx-auto">
    <?php if (!empty($needsEmpresa)): ?>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-bold text-yellow-800 dark:text-yellow-200 mb-2">
                Selecione uma empresa para continuar
            </h2>
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                Use o filtro de empresas no topo do dashboard e selecione pelo menos uma empresa.
            </p>
        </div>
    <?php endif; ?>
    <div class="mb-8">
        <a href="/conexoes-bancarias" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            🏦 Nova Conexão Bancária
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Configure uma nova conexão com seu banco ou cartão de crédito
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <form action="/conexoes-bancarias/iniciar-consentimento" method="POST" x-data="conexaoForm()">
            
            <!-- Banco -->
            <div class="mb-6">
                <label for="banco" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Banco <span class="text-red-500">*</span>
                </label>
                <select name="banco" id="banco" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($errors['banco']) ? 'border-red-500' : '' ?>">
                    <option value="">Selecione o banco...</option>
                    <option value="sicredi" <?= ($old['banco'] ?? '') == 'sicredi' ? 'selected' : '' ?>>Sicredi</option>
                    <option value="sicoob" <?= ($old['banco'] ?? '') == 'sicoob' ? 'selected' : '' ?>>Sicoob</option>
                    <option value="bradesco" <?= ($old['banco'] ?? '') == 'bradesco' ? 'selected' : '' ?>>Bradesco</option>
                    <option value="itau" <?= ($old['banco'] ?? '') == 'itau' ? 'selected' : '' ?>>Itaú</option>
                </select>
                <?php if (isset($errors['banco'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $errors['banco'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo -->
            <div class="mb-6">
                <label for="tipo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo de Conta <span class="text-red-500">*</span>
                </label>
                <select name="tipo" id="tipo" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($errors['tipo']) ? 'border-red-500' : '' ?>">
                    <option value="">Selecione o tipo...</option>
                    <option value="conta_corrente" <?= ($old['tipo'] ?? '') == 'conta_corrente' ? 'selected' : '' ?>>Conta Corrente</option>
                    <option value="conta_poupanca" <?= ($old['tipo'] ?? '') == 'conta_poupanca' ? 'selected' : '' ?>>Conta Poupança</option>
                    <option value="cartao_credito" <?= ($old['tipo'] ?? '') == 'cartao_credito' ? 'selected' : '' ?>>Cartão de Crédito</option>
                </select>
                <?php if (isset($errors['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $errors['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Identificação -->
            <div class="mb-6">
                <label for="identificacao" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Identificação (Opcional)
                </label>
                <input type="text" id="identificacao" name="identificacao" 
                       value="<?= htmlspecialchars($old['identificacao'] ?? '') ?>"
                       placeholder="Ex: Conta Principal, Cartão *1234"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Apelido para identificar esta conexão</p>
            </div>

            <!-- Configurações de Sincronização -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Configurações de Sincronização</h3>
                
                <!-- Auto Sync -->
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_sync" value="1" <?= ($old['auto_sync'] ?? '1') == '1' ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Sincronização Automática</span>
                    </label>
                    <p class="ml-8 mt-1 text-xs text-gray-500 dark:text-gray-400">O sistema buscará novas transações automaticamente</p>
                </div>

                <!-- Frequência -->
                <div class="mb-4">
                    <label for="frequencia_sync" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Frequência de Sincronização
                    </label>
                    <select name="frequencia_sync" id="frequencia_sync"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="manual" <?= ($old['frequencia_sync'] ?? 'diaria') == 'manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="diaria" <?= ($old['frequencia_sync'] ?? 'diaria') == 'diaria' ? 'selected' : '' ?>>Diária</option>
                        <option value="semanal" <?= ($old['frequencia_sync'] ?? 'diaria') == 'semanal' ? 'selected' : '' ?>>Semanal</option>
                    </select>
                </div>
            </div>

            <!-- Configurações Padrão -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Classificação Padrão</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Estas configurações serão usadas caso a IA não consiga classificar uma transação</p>

                <!-- Categoria Padrão -->
                <div class="mb-4">
                    <label for="categoria_padrao_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Categoria Padrão (Opcional)
                    </label>
                    <select name="categoria_padrao_id" id="categoria_padrao_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Nenhuma</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" <?= ($old['categoria_padrao_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['codigo'] . ' - ' . $categoria['nome'] . ' (' . ucfirst($categoria['tipo']) . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Centro de Custo Padrão -->
                <div class="mb-4">
                    <label for="centro_custo_padrao_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Centro de Custo Padrão (Opcional)
                    </label>
                    <select name="centro_custo_padrao_id" id="centro_custo_padrao_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Nenhum</option>
                        <?php foreach ($centros_custo as $centro): ?>
                            <option value="<?= $centro['id'] ?>" <?= ($old['centro_custo_padrao_id'] ?? '') == $centro['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($centro['codigo'] . ' - ' . $centro['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Aprovação Automática -->
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aprovacao_automatica" value="1" <?= ($old['aprovacao_automatica'] ?? '0') == '1' ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Aprovação Automática</span>
                    </label>
                    <p class="ml-8 mt-1 text-xs text-red-500 dark:text-red-400">⚠️ Atenção: transações classificadas com alta confiança serão aprovadas automaticamente</p>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <a href="/conexoes-bancarias" 
                   class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 text-center">
                    Cancelar
                </a>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    Conectar Banco
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function conexaoForm() {
    return {
        // Futuras funcionalidades
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
