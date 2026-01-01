<?php
$old = $this->session->get('old') ?? [];
$errors = $this->session->get('errors') ?? [];
?>

<div class="max-w-7xl mx-auto" x-data="{ showModalExplicacao: false }">
    <!-- Seletor de Empresa -->
    <?php if (!empty($empresas_usuario) && count($empresas_usuario) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" action="/conexoes-bancarias" class="flex items-center gap-4">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Empresa:</label>
                <select name="empresa_id" onchange="this.form.submit()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <?php foreach ($empresas_usuario as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($empresa_id_selecionada == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nome_fantasia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Header com botão de ajuda -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent inline-flex items-center">
                🏦 Sincronização Bancária Inteligente
                <button @click="showModalExplicacao = true" class="ml-3 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 cursor-pointer transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Conecte suas contas bancárias e cartões de crédito para importação automática de transações
            </p>
        </div>
        <a href="/conexoes-bancarias/create" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Conexão
        </a>
    </div>

    <!-- Modal Explicativo -->
    <div x-show="showModalExplicacao" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="showModalExplicacao" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" 
                 @click="showModalExplicacao = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="showModalExplicacao"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Como Funciona a Sincronização Bancária?
                        </h3>
                        <button @click="showModalExplicacao = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                    <!-- Como Funciona -->
                    <div class="mb-8">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <span class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">1</span>
                            O que é?
                        </h4>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                            A <strong>Sincronização Bancária Inteligente</strong> conecta diretamente às APIs dos bancos brasileiros usando o <strong>Open Banking</strong>, permitindo que você:
                        </p>
                        <ul class="space-y-2 ml-6">
                            <li class="flex items-start text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Importe automaticamente extratos bancários e transações de cartão de crédito
                            </li>
                            <li class="flex items-start text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Classifique transações automaticamente usando <strong>Inteligência Artificial</strong>
                            </li>
                            <li class="flex items-start text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aprove, edite ou rejeite transações antes de lançá-las como contas a pagar/receber
                            </li>
                            <li class="flex items-start text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Evite lançamentos duplicados e erros manuais
                            </li>
                        </ul>
                    </div>

                    <!-- Fluxo de Funcionamento -->
                    <div class="mb-8">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <span class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">2</span>
                            Fluxo de Funcionamento
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-1 flex-shrink-0 font-bold">A</div>
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100">Autorização Segura (OAuth 2.0)</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Você será redirecionado ao site oficial do seu banco para autorizar o acesso. Suas credenciais bancárias <strong>nunca</strong> passam pelo nosso sistema.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-1 flex-shrink-0 font-bold">B</div>
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100">Sincronização Automática</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Conforme a frequência escolhida (diária/semanal), o sistema busca novas transações automaticamente.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-1 flex-shrink-0 font-bold">C</div>
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100">Classificação Inteligente</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">A IA analisa a descrição da transação e sugere categoria, centro de custo e fornecedor/cliente.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-1 flex-shrink-0 font-bold">D</div>
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100">Revisão e Aprovação</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Você revisa as transações pendentes, edita se necessário e aprova para lançamento no sistema.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passo a Passo para Obter Credenciais -->
                    <div class="mb-6">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <span class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">3</span>
                            Passo a Passo por Banco
                        </h4>

                        <!-- Sicredi -->
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h5 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sicredi
                            </h5>
                            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-decimal list-inside">
                                <li>Acesse <a href="https://developers.sicredi.com.br" target="_blank" class="text-blue-600 dark:text-blue-400 underline">developers.sicredi.com.br</a></li>
                                <li>Faça login com suas credenciais da cooperativa</li>
                                <li>Vá em <strong>Meus Apps</strong> → <strong>Criar Novo App</strong></li>
                                <li>Preencha o nome da aplicação (ex: "Sistema Financeiro")</li>
                                <li>Em <strong>Redirect URI</strong>, insira: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/conexoes-bancarias/callback</code></li>
                                <li>Selecione os escopos: <strong>accounts</strong> e <strong>transactions</strong></li>
                                <li>Após criar, copie o <strong>Client ID</strong> e <strong>Client Secret</strong></li>
                                <li>Volte aqui e clique em <strong>Nova Conexão</strong></li>
                            </ol>
                        </div>

                        <!-- Sicoob -->
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h5 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sicoob
                            </h5>
                            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-decimal list-inside">
                                <li>Acesse <a href="https://developers.sicoob.com.br" target="_blank" class="text-blue-600 dark:text-blue-400 underline">developers.sicoob.com.br</a></li>
                                <li>Crie uma conta de desenvolvedor (se não tiver)</li>
                                <li>No painel, clique em <strong>Criar Aplicação</strong></li>
                                <li>Escolha <strong>Open Banking</strong> como tipo</li>
                                <li>Defina a <strong>URL de Callback</strong>: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/conexoes-bancarias/callback</code></li>
                                <li>Ative os produtos: <strong>Contas</strong> e <strong>Transações</strong></li>
                                <li>Anote o <strong>Client ID</strong> e <strong>Client Secret</strong></li>
                                <li>Clique em <strong>Nova Conexão</strong> neste sistema</li>
                            </ol>
                        </div>

                        <!-- Bradesco -->
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h5 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Bradesco
                            </h5>
                            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-decimal list-inside">
                                <li>Acesse <a href="https://developer.bradesco.com" target="_blank" class="text-blue-600 dark:text-blue-400 underline">developer.bradesco.com</a></li>
                                <li>Faça login com Internet Banking (PF ou PJ)</li>
                                <li>No menu, vá em <strong>APIs</strong> → <strong>Open Banking</strong></li>
                                <li>Clique em <strong>Subscrever</strong> nas APIs de Contas e Transações</li>
                                <li>Em <strong>Minhas Aplicações</strong>, crie uma nova</li>
                                <li>Informe a <strong>Redirect URI</strong>: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/conexoes-bancarias/callback</code></li>
                                <li>Obtenha as credenciais <strong>Consumer Key</strong> e <strong>Consumer Secret</strong></li>
                                <li>Inicie a conexão aqui no sistema</li>
                            </ol>
                        </div>

                        <!-- Itaú -->
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <h5 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Itaú
                            </h5>
                            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-decimal list-inside">
                                <li>Acesse <a href="https://developers.itau.com.br" target="_blank" class="text-blue-600 dark:text-blue-400 underline">developers.itau.com.br</a></li>
                                <li>Cadastre-se como desenvolvedor (gratuito)</li>
                                <li>No portal, vá em <strong>Meus Apps</strong> → <strong>Novo App</strong></li>
                                <li>Escolha a categoria <strong>Open Banking</strong></li>
                                <li>Configure a <strong>URL de Redirecionamento</strong>: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/conexoes-bancarias/callback</code></li>
                                <li>Solicite acesso às APIs: <strong>Accounts</strong> e <strong>Transactions</strong></li>
                                <li>Aguarde aprovação (geralmente instantânea)</li>
                                <li>Copie o <strong>Client ID</strong> e <strong>Secret</strong></li>
                                <li>Volte aqui e configure a conexão</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Segurança -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4">
                        <h5 class="font-bold text-green-800 dark:text-green-300 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            100% Seguro
                        </h5>
                        <ul class="text-sm text-green-700 dark:text-green-300 space-y-1">
                            <li>✓ Seus tokens são <strong>criptografados</strong> no banco de dados</li>
                            <li>✓ Usamos <strong>OAuth 2.0</strong> padrão do Open Banking Brasil</li>
                            <li>✓ Suas credenciais bancárias <strong>nunca</strong> são armazenadas</li>
                            <li>✓ Você pode revogar o acesso a qualquer momento</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 flex justify-end">
                    <button @click="showModalExplicacao = false" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Entendi, vamos começar!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Conexões -->
    <?php if (empty($conexoes)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Nenhuma Conexão Bancária</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                Conecte suas contas bancárias e cartões de crédito para começar a importar transações automaticamente.
            </p>
            <a href="/conexoes-bancarias/create" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Conectar Banco
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($conexoes as $conexao): 
                $bancoNomes = [
                    'sicredi' => 'Sicredi',
                    'sicoob' => 'Sicoob',
                    'bradesco' => 'Bradesco',
                    'itau' => 'Itaú'
                ];
                
                $tipoNomes = [
                    'conta_corrente' => 'Conta Corrente',
                    'conta_poupanca' => 'Conta Poupança',
                    'cartao_credito' => 'Cartão de Crédito'
                ];
                
                $statusClass = $conexao['ativo'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($bancoNomes[$conexao['banco']] ?? $conexao['banco']) ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($tipoNomes[$conexao['tipo']] ?? $conexao['tipo']) ?></p>
                            <?php if ($conexao['identificacao']): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?= htmlspecialchars($conexao['identificacao']) ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="<?= $statusClass ?> text-xs font-semibold px-3 py-1 rounded-full">
                            <?= $conexao['ativo'] ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </div>

                    <?php if ($conexao['ultima_sincronizacao']): ?>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                            Última sinc: <?= date('d/m/Y H:i', strtotime($conexao['ultima_sincronizacao'])) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-4">Nunca sincronizada</p>
                    <?php endif; ?>

                    <div class="flex gap-2">
                        <a href="/conexoes-bancarias/<?= $conexao['id'] ?>" class="flex-1 text-center px-4 py-2 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors text-sm font-medium">
                            Ver Detalhes
                        </a>
                        <button onclick="sincronizar(<?= $conexao['id'] ?>)" class="px-4 py-2 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function sincronizar(id) {
    if (!confirm('Deseja sincronizar esta conexão agora?')) return;
    
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    
    fetch(`/conexoes-bancarias/${id}/sincronizar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(err => {
        alert('Erro ao sincronizar: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
