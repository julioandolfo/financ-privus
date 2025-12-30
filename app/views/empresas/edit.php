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
                ✏️ Editar Empresa
            </h1>
        </div>

        <!-- Formulário -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
            <form method="POST" action="<?= baseUrl('/empresas/' . $empresa['id']) ?>" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Código -->
                    <div>
                        <label for="codigo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Código *
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="<?= htmlspecialchars($session->get('old')['codigo'] ?? $empresa['codigo']) ?>"
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
                               value="<?= htmlspecialchars($session->get('old')['cnpj'] ?? $empresa['cnpj']) ?>"
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
                           value="<?= htmlspecialchars($session->get('old')['razao_social'] ?? $empresa['razao_social']) ?>"
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
                           value="<?= htmlspecialchars($session->get('old')['nome_fantasia'] ?? $empresa['nome_fantasia']) ?>"
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
                           value="<?= htmlspecialchars($session->get('old')['grupo_empresarial_id'] ?? $empresa['grupo_empresarial_id']) ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <?php
                // Carrega configurações do JSON
                $config = [];
                if (!empty($empresa['configuracoes'])) {
                    $config = is_string($empresa['configuracoes']) ? json_decode($empresa['configuracoes'], true) : $empresa['configuracoes'];
                }
                $old = $session->get('old') ?? [];
                ?>

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
                               value="<?= htmlspecialchars($old['telefone'] ?? $config['telefone'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($old['email'] ?? $config['email'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($old['site'] ?? $config['site'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($old['inscricao_estadual'] ?? $config['inscricao_estadual'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($old['inscricao_municipal'] ?? $config['inscricao_municipal'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['cep'] ?? $config['endereco']['cep'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['logradouro'] ?? $config['endereco']['logradouro'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['numero'] ?? $config['endereco']['numero'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label for="complemento" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Complemento
                            </label>
                            <input type="text" 
                                   id="complemento" 
                                   name="complemento" 
                                   value="<?= htmlspecialchars($old['complemento'] ?? $config['endereco']['complemento'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['bairro'] ?? $config['endereco']['bairro'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['cidade'] ?? $config['endereco']['cidade'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Estado (UF)
                            </label>
                            <?php $estadoAtual = $old['estado'] ?? $config['endereco']['estado'] ?? ''; ?>
                            <select id="estado" 
                                    name="estado" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Selecione</option>
                                <option value="AC" <?= $estadoAtual === 'AC' ? 'selected' : '' ?>>Acre</option>
                                <option value="AL" <?= $estadoAtual === 'AL' ? 'selected' : '' ?>>Alagoas</option>
                                <option value="AP" <?= $estadoAtual === 'AP' ? 'selected' : '' ?>>Amapá</option>
                                <option value="AM" <?= $estadoAtual === 'AM' ? 'selected' : '' ?>>Amazonas</option>
                                <option value="BA" <?= $estadoAtual === 'BA' ? 'selected' : '' ?>>Bahia</option>
                                <option value="CE" <?= $estadoAtual === 'CE' ? 'selected' : '' ?>>Ceará</option>
                                <option value="DF" <?= $estadoAtual === 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                                <option value="ES" <?= $estadoAtual === 'ES' ? 'selected' : '' ?>>Espírito Santo</option>
                                <option value="GO" <?= $estadoAtual === 'GO' ? 'selected' : '' ?>>Goiás</option>
                                <option value="MA" <?= $estadoAtual === 'MA' ? 'selected' : '' ?>>Maranhão</option>
                                <option value="MT" <?= $estadoAtual === 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                                <option value="MS" <?= $estadoAtual === 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                                <option value="MG" <?= $estadoAtual === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                                <option value="PA" <?= $estadoAtual === 'PA' ? 'selected' : '' ?>>Pará</option>
                                <option value="PB" <?= $estadoAtual === 'PB' ? 'selected' : '' ?>>Paraíba</option>
                                <option value="PR" <?= $estadoAtual === 'PR' ? 'selected' : '' ?>>Paraná</option>
                                <option value="PE" <?= $estadoAtual === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                                <option value="PI" <?= $estadoAtual === 'PI' ? 'selected' : '' ?>>Piauí</option>
                                <option value="RJ" <?= $estadoAtual === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                                <option value="RN" <?= $estadoAtual === 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                                <option value="RS" <?= $estadoAtual === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                                <option value="RO" <?= $estadoAtual === 'RO' ? 'selected' : '' ?>>Rondônia</option>
                                <option value="RR" <?= $estadoAtual === 'RR' ? 'selected' : '' ?>>Roraima</option>
                                <option value="SC" <?= $estadoAtual === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                                <option value="SP" <?= $estadoAtual === 'SP' ? 'selected' : '' ?>>São Paulo</option>
                                <option value="SE" <?= $estadoAtual === 'SE' ? 'selected' : '' ?>>Sergipe</option>
                                <option value="TO" <?= $estadoAtual === 'TO' ? 'selected' : '' ?>>Tocantins</option>
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
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"><?= htmlspecialchars($old['observacoes'] ?? $config['observacoes'] ?? '') ?></textarea>
                </div>

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           <?= ($session->get('old')['ativo'] ?? $empresa['ativo']) ? 'checked' : '' ?>
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
$session->delete('old');
?>
