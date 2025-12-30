<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/fornecedores') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            ✏️ Editar Fornecedor
        </h1>
    </div>

    <!-- Formulário -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
        <form method="POST" action="<?= $this->baseUrl('/fornecedores/' . $fornecedor['id']) ?>" class="space-y-6">
            <?php
            $old = $this->session->get('old') ?? [];
            $endereco = $old['endereco'] ?? $fornecedor['endereco'] ?? [];
            ?>

            <!-- Empresa -->
            <div>
                <label for="empresa_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Empresa *
                </label>
                <select id="empresa_id" 
                        name="empresa_id" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['empresa_id']) ? 'border-red-500' : '' ?>" 
                        required>
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" <?= (($old['empresa_id'] ?? $fornecedor['empresa_id']) == $empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($this->session->get('errors')['empresa_id'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['empresa_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Tipo *
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($old['tipo'] ?? $fornecedor['tipo']) === 'fisica' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="fisica" 
                               <?= ($old['tipo'] ?? $fornecedor['tipo']) === 'fisica' ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 focus:ring-blue-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">👤 Pessoa Física</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">CPF</div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all <?= ($old['tipo'] ?? $fornecedor['tipo']) === 'juridica' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-700' ?>">
                        <input type="radio" 
                               name="tipo" 
                               value="juridica" 
                               <?= ($old['tipo'] ?? $fornecedor['tipo']) === 'juridica' ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 focus:ring-blue-500" 
                               required>
                        <div class="ml-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">🏢 Pessoa Jurídica</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">CNPJ</div>
                        </div>
                    </label>
                </div>
                <?php if (isset($this->session->get('errors')['tipo'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['tipo'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Nome/Razão Social -->
            <div>
                <label for="nome_razao_social" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Nome / Razão Social *
                </label>
                <input type="text" 
                       id="nome_razao_social" 
                       name="nome_razao_social" 
                       value="<?= htmlspecialchars($old['nome_razao_social'] ?? $fornecedor['nome_razao_social']) ?>"
                       minlength="3"
                       maxlength="255"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['nome_razao_social']) ? 'border-red-500' : '' ?>" 
                       required>
                <?php if (isset($this->session->get('errors')['nome_razao_social'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['nome_razao_social'] ?></p>
                <?php endif; ?>
            </div>

            <!-- CPF/CNPJ -->
            <div>
                <label for="cpf_cnpj" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    CPF / CNPJ *
                </label>
                <?php
                $cpfCnpj = $old['cpf_cnpj'] ?? $fornecedor['cpf_cnpj'] ?? '';
                // Aplica máscara se já tiver valor
                if ($cpfCnpj) {
                    $cpfCnpjLimpo = preg_replace('/[^0-9]/', '', $cpfCnpj);
                    if (strlen($cpfCnpjLimpo) === 11) {
                        $cpfCnpj = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfCnpjLimpo);
                    } elseif (strlen($cpfCnpjLimpo) === 14) {
                        $cpfCnpj = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cpfCnpjLimpo);
                    }
                }
                ?>
                <input type="text" 
                       id="cpf_cnpj" 
                       name="cpf_cnpj" 
                       value="<?= htmlspecialchars($cpfCnpj) ?>"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['cpf_cnpj']) ? 'border-red-500' : '' ?>" 
                       required>
                <?php if (isset($this->session->get('errors')['cpf_cnpj'])): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['cpf_cnpj'] ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="cpf_cnpj_hint">Digite apenas números</p>
                <?php endif; ?>
            </div>

            <!-- Email e Telefone -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($old['email'] ?? $fornecedor['email'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?= isset($this->session->get('errors')['email']) ? 'border-red-500' : '' ?>">
                    <?php if (isset($this->session->get('errors')['email'])): ?>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $this->session->get('errors')['email'] ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="telefone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Telefone
                    </label>
                    <?php
                    $telefone = $old['telefone'] ?? $fornecedor['telefone'] ?? '';
                    // Aplica máscara se já tiver valor
                    if ($telefone) {
                        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
                        if (strlen($telefoneLimpo) === 10) {
                            $telefone = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefoneLimpo);
                        } elseif (strlen($telefoneLimpo) === 11) {
                            $telefone = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefoneLimpo);
                        }
                    }
                    ?>
                    <input type="text" 
                           id="telefone" 
                           name="telefone" 
                           data-mask="telefone"
                           value="<?= htmlspecialchars($telefone) ?>"
                           placeholder="(00) 00000-0000"
                           maxlength="15"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Endereço -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Endereço (Opcional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="cep" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            CEP
                        </label>
                        <input type="text" 
                               id="cep" 
                               name="endereco[cep]" 
                               data-mask="cep"
                               data-cep
                               data-cep-prefix="endereco["
                               value="<?= htmlspecialchars($endereco['cep'] ?? '') ?>"
                               placeholder="00000-000"
                               maxlength="9"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Digite o CEP para buscar o endereço automaticamente</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="logradouro" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Logradouro
                        </label>
                        <input type="text" 
                               id="logradouro" 
                               name="endereco[logradouro]" 
                               value="<?= htmlspecialchars($endereco['logradouro'] ?? '') ?>"
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
                               name="endereco[numero]" 
                               value="<?= htmlspecialchars($endereco['numero'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label for="complemento" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Complemento
                        </label>
                        <input type="text" 
                               id="complemento" 
                               name="endereco[complemento]" 
                               value="<?= htmlspecialchars($endereco['complemento'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="bairro" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Bairro
                        </label>
                        <input type="text" 
                               id="bairro" 
                               name="endereco[bairro]" 
                               value="<?= htmlspecialchars($endereco['bairro'] ?? '') ?>"
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
                               name="endereco[cidade]" 
                               value="<?= htmlspecialchars($endereco['cidade'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="estado" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Estado (UF)
                        </label>
                        <?php
                        $estados = ['AC'=>'Acre', 'AL'=>'Alagoas', 'AP'=>'Amapá', 'AM'=>'Amazonas', 'BA'=>'Bahia', 'CE'=>'Ceará', 'DF'=>'Distrito Federal', 'ES'=>'Espírito Santo', 'GO'=>'Goiás', 'MA'=>'Maranhão', 'MT'=>'Mato Grosso', 'MS'=>'Mato Grosso do Sul', 'MG'=>'Minas Gerais', 'PA'=>'Pará', 'PB'=>'Paraíba', 'PR'=>'Paraná', 'PE'=>'Pernambuco', 'PI'=>'Piauí', 'RJ'=>'Rio de Janeiro', 'RN'=>'Rio Grande do Norte', 'RS'=>'Rio Grande do Sul', 'RO'=>'Rondônia', 'RR'=>'Roraima', 'SC'=>'Santa Catarina', 'SP'=>'São Paulo', 'SE'=>'Sergipe', 'TO'=>'Tocantins'];
                        $estadoAtual = $endereco['estado'] ?? '';
                        ?>
                        <select id="estado" 
                                name="endereco[estado]" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Selecione</option>
                            <?php foreach ($estados as $uf => $nome): ?>
                                <option value="<?= $uf ?>" <?= $estadoAtual === $uf ? 'selected' : '' ?>><?= $nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       id="ativo" 
                       name="ativo" 
                       value="1"
                       <?= ($old['ativo'] ?? $fornecedor['ativo']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="ativo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Fornecedor Ativo
                </label>
            </div>

            <!-- Botões -->
            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Salvar Alterações
                </button>
                <a href="<?= $this->baseUrl('/fornecedores') ?>" 
                   class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-center transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Aplica máscara dinâmica de CPF/CNPJ baseado no tipo selecionado
document.addEventListener('DOMContentLoaded', function() {
    const tipoInputs = document.querySelectorAll('input[name="tipo"]');
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const cpfCnpjHint = document.getElementById('cpf_cnpj_hint');
    
    function updateMask() {
        const tipoSelecionado = document.querySelector('input[name="tipo"]:checked')?.value;
        
        if (tipoSelecionado === 'fisica') {
            cpfCnpjInput.setAttribute('data-mask', 'cpf');
            cpfCnpjInput.setAttribute('maxlength', '14');
            cpfCnpjInput.setAttribute('placeholder', '000.000.000-00');
            if (cpfCnpjHint) cpfCnpjHint.textContent = 'Digite apenas números (CPF)';
        } else if (tipoSelecionado === 'juridica') {
            cpfCnpjInput.setAttribute('data-mask', 'cnpj');
            cpfCnpjInput.setAttribute('maxlength', '18');
            cpfCnpjInput.setAttribute('placeholder', '00.000.000/0000-00');
            if (cpfCnpjHint) cpfCnpjHint.textContent = 'Digite apenas números (CNPJ)';
        }
        
        // Reaplica máscara se já houver valor
        if (cpfCnpjInput.value && window.maskManager) {
            if (tipoSelecionado === 'fisica') {
                window.maskManager.maskCPF({ target: cpfCnpjInput });
            } else if (tipoSelecionado === 'juridica') {
                window.maskManager.maskCNPJ({ target: cpfCnpjInput });
            }
        }
    }
    
    tipoInputs.forEach(input => {
        input.addEventListener('change', updateMask);
    });
    
    // Aplica máscara inicial se já houver tipo selecionado
    updateMask();
});
</script>

<?php 
// Limpa old e errors após exibição
$this->session->delete('old');
$this->session->delete('errors');
?>

