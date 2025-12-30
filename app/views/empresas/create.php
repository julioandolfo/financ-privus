<div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= baseUrl('/empresas') ?>" 
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                ➕ Nova Empresa
            </h1>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <form method="POST" action="<?= baseUrl('/empresas') ?>" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Código -->
                    <div>
                        <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Código *
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="<?= htmlspecialchars($session->get('old')['codigo'] ?? '') ?>"
                               pattern="[A-Za-z0-9_-]+"
                               minlength="2"
                               maxlength="20"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                               required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Apenas letras, números, hífen e underscore (2-20 caracteres)</p>
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
                               value="<?= htmlspecialchars($session->get('old')['cnpj'] ?? '') ?>"
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
                           value="<?= htmlspecialchars($session->get('old')['razao_social'] ?? '') ?>"
                           minlength="3"
                           maxlength="255"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mínimo 3 caracteres</p>
                </div>

                <!-- Nome Fantasia -->
                <div>
                    <label for="nome_fantasia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Nome Fantasia *
                    </label>
                    <input type="text" 
                           id="nome_fantasia" 
                           name="nome_fantasia" 
                           value="<?= htmlspecialchars($session->get('old')['nome_fantasia'] ?? '') ?>"
                           minlength="3"
                           maxlength="255"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mínimo 3 caracteres</p>
                </div>

                <!-- Grupo Empresarial -->
                <div>
                    <label for="grupo_empresarial_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Grupo Empresarial (Opcional)
                    </label>
                    <input type="number" 
                           id="grupo_empresarial_id" 
                           name="grupo_empresarial_id" 
                           value="<?= htmlspecialchars($session->get('old')['grupo_empresarial_id'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Telefone e Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="telefone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Telefone
                        </label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               data-mask="telefone"
                               value="<?= htmlspecialchars($session->get('old')['telefone'] ?? '') ?>"
                               placeholder="(00) 00000-0000"
                               maxlength="15"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($session->get('old')['email'] ?? '') ?>"
                               placeholder="contato@empresa.com.br"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Site e Inscrições -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="site" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Site
                        </label>
                        <input type="url" 
                               id="site" 
                               name="site" 
                               value="<?= htmlspecialchars($session->get('old')['site'] ?? '') ?>"
                               placeholder="https://www.empresa.com.br"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="inscricao_estadual" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Inscrição Estadual
                        </label>
                        <input type="text" 
                               id="inscricao_estadual" 
                               name="inscricao_estadual" 
                               data-mask="number"
                               value="<?= htmlspecialchars($session->get('old')['inscricao_estadual'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="inscricao_municipal" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Inscrição Municipal
                        </label>
                        <input type="text" 
                               id="inscricao_municipal" 
                               name="inscricao_municipal" 
                               data-mask="number"
                               value="<?= htmlspecialchars($session->get('old')['inscricao_municipal'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Endereço -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Endereço</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="cep" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                CEP
                            </label>
                            <input type="text" 
                                   id="cep" 
                                   name="cep" 
                                   data-mask="cep"
                                   value="<?= htmlspecialchars($session->get('old')['cep'] ?? '') ?>"
                                   placeholder="00000-000"
                                   maxlength="9"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label for="logradouro" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Logradouro
                            </label>
                            <input type="text" 
                                   id="logradouro" 
                                   name="logradouro" 
                                   value="<?= htmlspecialchars($session->get('old')['logradouro'] ?? '') ?>"
                                   placeholder="Rua, Avenida, etc."
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
                        <div>
                            <label for="numero" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Número
                            </label>
                            <input type="text" 
                                   id="numero" 
                                   name="numero" 
                                   value="<?= htmlspecialchars($session->get('old')['numero'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label for="complemento" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Complemento
                            </label>
                            <input type="text" 
                                   id="complemento" 
                                   name="complemento" 
                                   value="<?= htmlspecialchars($session->get('old')['complemento'] ?? '') ?>"
                                   placeholder="Apto, Sala, etc."
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="bairro" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Bairro
                            </label>
                            <input type="text" 
                                   id="bairro" 
                                   name="bairro" 
                                   value="<?= htmlspecialchars($session->get('old')['bairro'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="cidade" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Cidade
                            </label>
                            <input type="text" 
                                   id="cidade" 
                                   name="cidade" 
                                   value="<?= htmlspecialchars($session->get('old')['cidade'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Estado (UF)
                            </label>
                            <select id="estado" 
                                    name="estado" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Selecione</option>
                                <option value="AC" <?= ($session->get('old')['estado'] ?? '') === 'AC' ? 'selected' : '' ?>>Acre</option>
                                <option value="AL" <?= ($session->get('old')['estado'] ?? '') === 'AL' ? 'selected' : '' ?>>Alagoas</option>
                                <option value="AP" <?= ($session->get('old')['estado'] ?? '') === 'AP' ? 'selected' : '' ?>>Amapá</option>
                                <option value="AM" <?= ($session->get('old')['estado'] ?? '') === 'AM' ? 'selected' : '' ?>>Amazonas</option>
                                <option value="BA" <?= ($session->get('old')['estado'] ?? '') === 'BA' ? 'selected' : '' ?>>Bahia</option>
                                <option value="CE" <?= ($session->get('old')['estado'] ?? '') === 'CE' ? 'selected' : '' ?>>Ceará</option>
                                <option value="DF" <?= ($session->get('old')['estado'] ?? '') === 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                                <option value="ES" <?= ($session->get('old')['estado'] ?? '') === 'ES' ? 'selected' : '' ?>>Espírito Santo</option>
                                <option value="GO" <?= ($session->get('old')['estado'] ?? '') === 'GO' ? 'selected' : '' ?>>Goiás</option>
                                <option value="MA" <?= ($session->get('old')['estado'] ?? '') === 'MA' ? 'selected' : '' ?>>Maranhão</option>
                                <option value="MT" <?= ($session->get('old')['estado'] ?? '') === 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                                <option value="MS" <?= ($session->get('old')['estado'] ?? '') === 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                                <option value="MG" <?= ($session->get('old')['estado'] ?? '') === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                                <option value="PA" <?= ($session->get('old')['estado'] ?? '') === 'PA' ? 'selected' : '' ?>>Pará</option>
                                <option value="PB" <?= ($session->get('old')['estado'] ?? '') === 'PB' ? 'selected' : '' ?>>Paraíba</option>
                                <option value="PR" <?= ($session->get('old')['estado'] ?? '') === 'PR' ? 'selected' : '' ?>>Paraná</option>
                                <option value="PE" <?= ($session->get('old')['estado'] ?? '') === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                                <option value="PI" <?= ($session->get('old')['estado'] ?? '') === 'PI' ? 'selected' : '' ?>>Piauí</option>
                                <option value="RJ" <?= ($session->get('old')['estado'] ?? '') === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                                <option value="RN" <?= ($session->get('old')['estado'] ?? '') === 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                                <option value="RS" <?= ($session->get('old')['estado'] ?? '') === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                                <option value="RO" <?= ($session->get('old')['estado'] ?? '') === 'RO' ? 'selected' : '' ?>>Rondônia</option>
                                <option value="RR" <?= ($session->get('old')['estado'] ?? '') === 'RR' ? 'selected' : '' ?>>Roraima</option>
                                <option value="SC" <?= ($session->get('old')['estado'] ?? '') === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                                <option value="SP" <?= ($session->get('old')['estado'] ?? '') === 'SP' ? 'selected' : '' ?>>São Paulo</option>
                                <option value="SE" <?= ($session->get('old')['estado'] ?? '') === 'SE' ? 'selected' : '' ?>>Sergipe</option>
                                <option value="TO" <?= ($session->get('old')['estado'] ?? '') === 'TO' ? 'selected' : '' ?>>Tocantins</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div>
                    <label for="observacoes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Observações
                    </label>
                    <textarea id="observacoes" 
                              name="observacoes" 
                              rows="4"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"><?= htmlspecialchars($session->get('old')['observacoes'] ?? '') ?></textarea>
                </div>

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           <?= ($session->get('old')['ativo'] ?? '1') ? 'checked' : '' ?>
                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Empresa Ativa
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Criar Empresa
                    </button>
                    <a href="<?= baseUrl('/empresas') ?>" 
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
    if (cnpjField) {
        cnpjField.addEventListener('blur', function() {
            const cnpj = this.value.replace(/\D/g, '');
            if (cnpj.length === 14 && window.maskManager && !window.maskManager.isValidCNPJ(cnpj)) {
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
$session->delete('old');
?>
