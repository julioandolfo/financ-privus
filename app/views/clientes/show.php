<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= baseUrl('/clientes') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
        </a>
        <div class="flex items-center justify-between">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                👥 Detalhes do Cliente
            </h1>
            <a href="<?= baseUrl('/clientes/' . $cliente['id'] . '/edit') ?>" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header do Card -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-blue-600 dark:text-blue-400 text-3xl font-bold shadow-lg">
                    <?= strtoupper(substr($cliente['nome_razao_social'], 0, 1)) ?>
                </div>
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($cliente['nome_razao_social']) ?></h2>
                    <?php if ($cliente['email']): ?>
                        <p class="text-blue-100"><?= htmlspecialchars($cliente['email']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($cliente['ativo']): ?>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-green-500 text-white">
                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                        Ativo
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-red-500 text-white">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        Inativo
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Corpo do Card -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        ID do Cliente
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        #<?= $cliente['id'] ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Status
                    </label>
                    <div class="text-lg font-semibold">
                        <?php if ($cliente['ativo']): ?>
                            <span class="text-green-600 dark:text-green-400">✓ Ativo</span>
                        <?php else: ?>
                            <span class="text-red-600 dark:text-red-400">✗ Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tipo -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Tipo
                    </label>
                    <div class="text-lg font-semibold">
                        <?php if ($cliente['tipo'] === 'fisica'): ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                👤 Pessoa Física
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                🏢 Pessoa Jurídica
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CPF/CNPJ -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        CPF / CNPJ
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <?php
                        if ($cliente['cpf_cnpj']) {
                            $doc = $cliente['cpf_cnpj'];
                            if (strlen($doc) === 11) {
                                echo preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
                            } else {
                                echo preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
                            }
                        } else {
                            echo '<span class="text-gray-500 dark:text-gray-400">Não informado</span>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Empresa
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <?php if (!empty($cliente['empresa'])): ?>
                            <a href="<?= baseUrl('/empresas/' . $cliente['empresa']['id']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($cliente['empresa']['nome_fantasia']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500 dark:text-gray-400">Nenhuma empresa vinculada</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Telefone -->
                <?php if ($cliente['telefone']): ?>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Telefone
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?php
                            $telefone = $cliente['telefone'];
                            $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
                            if (strlen($telefoneLimpo) === 10) {
                                echo preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefoneLimpo);
                            } elseif (strlen($telefoneLimpo) === 11) {
                                echo preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefoneLimpo);
                            } else {
                                echo htmlspecialchars($telefone);
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Email -->
                <?php if ($cliente['email']): ?>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Email
                        </label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>" 
                               class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                <?= htmlspecialchars($cliente['email']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Data de Cadastro -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Data de Cadastro
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <?= date('d/m/Y \à\s H:i', strtotime($cliente['data_cadastro'])) ?>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <?php if (!empty($cliente['endereco']) && is_array($cliente['endereco'])): ?>
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Endereço</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (!empty($cliente['endereco']['logradouro'])): ?>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Logradouro
                                </label>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($cliente['endereco']['logradouro']) ?>
                                    <?php if (!empty($cliente['endereco']['numero'])): ?>
                                        , <?= htmlspecialchars($cliente['endereco']['numero']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($cliente['endereco']['complemento'])): ?>
                                        - <?= htmlspecialchars($cliente['endereco']['complemento']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cliente['endereco']['bairro'])): ?>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Bairro
                                </label>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($cliente['endereco']['bairro']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cliente['endereco']['cidade'])): ?>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Cidade / Estado
                                </label>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars($cliente['endereco']['cidade']) ?>
                                    <?php if (!empty($cliente['endereco']['estado'])): ?>
                                        / <?= htmlspecialchars($cliente['endereco']['estado']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cliente['endereco']['cep'])): ?>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    CEP
                                </label>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    <?php
                                    $cep = $cliente['endereco']['cep'];
                                    $cepLimpo = preg_replace('/[^0-9]/', '', $cep);
                                    if (strlen($cepLimpo) === 8) {
                                        echo preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cepLimpo);
                                    } else {
                                        echo htmlspecialchars($cep);
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer com Ações -->
        <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <form method="POST" action="<?= baseUrl('/clientes/' . $cliente['id'] . '/delete') ?>" 
                      onsubmit="return confirm('⚠️ Tem certeza que deseja excluir este cliente?\n\nEsta ação não pode ser desfeita!')">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Excluir Cliente
                    </button>
                </form>
                
                <a href="<?= baseUrl('/clientes/' . $cliente['id'] . '/edit') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Cliente
                </a>
            </div>
        </div>
    </div>
</div>

