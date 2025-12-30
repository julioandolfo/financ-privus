<div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= $this->baseUrl('/empresas') ?>" 
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                ✏️ Editar Empresa
            </h1>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <form method="POST" action="<?= $this->baseUrl('/empresas/' . $empresa['id']) ?>" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Código -->
                    <div>
                        <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Código *
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="<?= htmlspecialchars($this->session->get('old')['codigo'] ?? $empresa['codigo']) ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                               required>
                    </div>

                    <!-- CNPJ -->
                    <div>
                        <label for="cnpj" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            CNPJ
                        </label>
                        <input type="text" 
                               id="cnpj" 
                               name="cnpj" 
                               data-mask="cnpj"
                               value="<?= htmlspecialchars($this->session->get('old')['cnpj'] ?? $empresa['cnpj']) ?>"
                               placeholder="00.000.000/0000-00"
                               maxlength="18"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Digite apenas números</p>
                    </div>
                </div>

                <!-- Razão Social -->
                <div>
                    <label for="razao_social" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Razão Social *
                    </label>
                    <input type="text" 
                           id="razao_social" 
                           name="razao_social" 
                           value="<?= htmlspecialchars($this->session->get('old')['razao_social'] ?? $empresa['razao_social']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           required>
                </div>

                <!-- Nome Fantasia -->
                <div>
                    <label for="nome_fantasia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome Fantasia *
                    </label>
                    <input type="text" 
                           id="nome_fantasia" 
                           name="nome_fantasia" 
                           value="<?= htmlspecialchars($this->session->get('old')['nome_fantasia'] ?? $empresa['nome_fantasia']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           required>
                </div>

                <!-- Grupo Empresarial -->
                <div>
                    <label for="grupo_empresarial_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Grupo Empresarial (Opcional)
                    </label>
                    <input type="number" 
                           id="grupo_empresarial_id" 
                           name="grupo_empresarial_id" 
                           value="<?= htmlspecialchars($this->session->get('old')['grupo_empresarial_id'] ?? $empresa['grupo_empresarial_id']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           <?= ($this->session->get('old')['ativo'] ?? $empresa['ativo']) ? 'checked' : '' ?>
                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Empresa Ativa
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Salvar Alterações
                    </button>
                    <a href="<?= $this->baseUrl('/empresas') ?>" 
                       class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
</div>

<script>
// Validação de CNPJ em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const cnpjField = document.getElementById('cnpj');
    if (cnpjField && window.maskManager) {
        cnpjField.addEventListener('blur', function() {
            const cnpj = this.value.replace(/\D/g, '');
            if (cnpj.length === 14 && !window.maskManager.isValidCNPJ(cnpj)) {
                this.setCustomValidity('CNPJ inválido');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });
    }
});
</script>

<?php 
// Limpa old após exibição
$this->session->delete('old');
?>
