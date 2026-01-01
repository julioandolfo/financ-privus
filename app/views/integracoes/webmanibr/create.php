<?php
$empresaModel = new \App\Models\Empresa();
$empresas = $empresaModel->findAll();

// Se estiver editando
$integracao = $integracao ?? null;
$config = null;
$transportadoras = [];
$formasPagamento = [];

if ($integracao) {
    $webmanibrModel = new \App\Models\IntegracaoWebmaniBR();
    $transportadoraModel = new \App\Models\WebmaniBRTransportadora();
    $formaPagamentoModel = new \App\Models\WebmaniBRFormaPagamento();
    
    $config = $webmanibrModel->findByIntegracao($integracao['id']);
    $transportadoras = $transportadoraModel->findByIntegracao($integracao['id']);
    $formasPagamento = $formaPagamentoModel->findByIntegracao($integracao['id']);
}

$isEdit = !empty($integracao);
$pageTitle = $isEdit ? 'Editar Integração WebmaniaBR' : 'Nova Integração WebmaniaBR';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="webmanibrForm()">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes') ?>" 
           class="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Integrações
        </a>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <?= $pageTitle ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Configure a integração com WebmaniaBR para emissão de NF-e e NFS-e</p>
    </div>

    <!-- Form -->
    <form method="POST" action="<?= $isEdit ? $this->baseUrl('/integracoes/webmanibr/' . $integracao['id']) : $this->baseUrl('/integracoes/webmanibr') ?>" enctype="multipart/form-data">
        
        <!-- Informações Básicas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informações Básicas
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Nome da Integração *
                    </label>
                    <input type="text" name="nome" required
                           value="<?= htmlspecialchars($this->session->get('old')['nome'] ?? $integracao['nome'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Ex: WebmaniaBR - Empresa Principal">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Empresa *
                    </label>
                    <select name="empresa_id" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Selecione a empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" 
                                    <?= ($this->session->get('old')['empresa_id'] ?? $integracao['empresa_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Descrição (Opcional)
                </label>
                <textarea name="descricao" rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                          placeholder="Descreva o propósito desta integração..."><?= htmlspecialchars($this->session->get('old')['descricao'] ?? $integracao['descricao'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Credenciais NF-e (API 1.0) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Credenciais de Acesso (Nota Fiscal de Produto)
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Informe os acessos da sua aplicação – API 1.0</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Chave do Consumidor (Consumer Key) *
                    </label>
                    <input type="text" name="consumer_key" required
                           value="<?= htmlspecialchars($this->session->get('old')['consumer_key'] ?? $config['consumer_key'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                           placeholder="Ex: G5hBCtb94mXtSep8btCElafaXZf2AAIB">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Consumidor Secreto (Consumer Secret) *
                    </label>
                    <input type="text" name="consumer_secret" required
                           value="<?= htmlspecialchars($this->session->get('old')['consumer_secret'] ?? $config['consumer_secret'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                           placeholder="Ex: mrAKfvP3OXQZF3fZfNRFohaU93lYPEbX99CTPKbg12sjlMhy">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Token de Acesso (Access Token) *
                    </label>
                    <input type="text" name="access_token" required
                           value="<?= htmlspecialchars($this->session->get('old')['access_token'] ?? $config['access_token'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                           placeholder="Ex: 4634-oE500yV7qUOcOGtgpC14OTSHPA9cooQReicV1PIwhRq9TxOk">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Access Token Secret *
                    </label>
                    <input type="text" name="access_token_secret" required
                           value="<?= htmlspecialchars($this->session->get('old')['access_token_secret'] ?? $config['access_token_secret'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                           placeholder="Ex: naaUmk3cklTVzsq0BqvHjeHE8dhR30R9zq2ZPSoycVpcGJbJ">
                </div>
            </div>
        </div>

        <!-- Credenciais NFS-e (API 2.0) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Credenciais de Acesso (Nota Fiscal de Serviço)
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Informe os acessos da sua aplicação – API 2.0</p>

            <div>
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Bearer Access Token (Opcional)
                </label>
                <input type="text" name="bearer_token"
                       value="<?= htmlspecialchars($this->session->get('old')['bearer_token'] ?? $config['bearer_token'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                       placeholder="Ex: 9oQUKIonDwp71B1zAGsLMuupGYPGRldtqRZ2ZdST">
                <p class="mt-1 text-xs text-gray-500">Necessário apenas se for emitir NFS-e</p>
            </div>
        </div>

        <!-- Ambiente de Emissão -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Ambiente de Emissão</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Informe o ambiente de emissão. Para validade fiscal (produção) ou para testes (desenvolvimento).</p>

            <div class="space-y-4">
                <label class="flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <input type="radio" name="ambiente" value="producao" required
                           <?= ($this->session->get('old')['ambiente'] ?? $config['ambiente'] ?? 'homologacao') == 'producao' ? 'checked' : '' ?>
                           class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-3">
                        <span class="block font-semibold text-gray-900 dark:text-white">Produção</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Notas fiscais com validade jurídica</span>
                    </span>
                </label>

                <label class="flex items-center p-4 border-2 border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <input type="radio" name="ambiente" value="homologacao" required
                           <?= ($this->session->get('old')['ambiente'] ?? $config['ambiente'] ?? 'homologacao') == 'homologacao' ? 'checked' : '' ?>
                           class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-3">
                        <span class="block font-semibold text-gray-900 dark:text-white">Desenvolvimento (Testes)</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Para testes e homologação</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Configuração Padrão -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Configuração Padrão</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">A configuração padrão será utilizada para todos os produtos. Caso deseje a configuração também pode ser personalizada em cada produto.</p>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Emissão Automática
                    </label>
                    <select name="emitir_automatico"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="nao" <?= ($this->session->get('old')['emitir_automatico'] ?? $config['emitir_automatico'] ?? 'nao') == 'nao' ? 'selected' : '' ?>>
                            Não emitir automaticamente
                        </option>
                        <option value="processando" <?= ($this->session->get('old')['emitir_automatico'] ?? $config['emitir_automatico'] ?? '') == 'processando' ? 'selected' : '' ?>>
                            Sempre que o status do pedido é alterado para Processando (Pagamento confirmado)
                        </option>
                        <option value="concluido" <?= ($this->session->get('old')['emitir_automatico'] ?? $config['emitir_automatico'] ?? '') == 'concluido' ? 'selected' : '' ?>>
                            Sempre que o status do pedido é alterado para Concluído
                        </option>
                    </select>
                </div>

                <div>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="enviar_email_cliente" value="1"
                               <?= ($this->session->get('old')['enviar_email_cliente'] ?? $config['enviar_email_cliente'] ?? 1) ? 'checked' : '' ?>
                               class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500">
                        <span class="font-semibold text-gray-900 dark:text-white">Envio automático de E-mail</span>
                    </label>
                    <p class="ml-8 text-sm text-gray-600 dark:text-gray-400">Enviar e-mail para o cliente após a emissão da Nota Fiscal</p>
                </div>

                <div>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="emitir_data_pedido" value="1"
                               <?= ($this->session->get('old')['emitir_data_pedido'] ?? $config['emitir_data_pedido'] ?? 0) ? 'checked' : '' ?>
                               class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500">
                        <span class="font-semibold text-gray-900 dark:text-white">Emissão com Data do Pedido</span>
                    </label>
                    <p class="ml-8 text-sm text-gray-600 dark:text-gray-400">Emissão de Nota Fiscal com a data do pedido (retroativa)</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Notificação de Erros
                    </label>
                    <input type="email" name="email_notificacao"
                           value="<?= htmlspecialchars($this->session->get('old')['email_notificacao'] ?? $config['email_notificacao'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="email@exemplo.com">
                    <p class="mt-1 text-xs text-gray-500">Informe um e-mail para notificações de erros na emissão</p>
                </div>
            </div>
        </div>

        <!-- Configurações NFS-e -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Configurações (Nota Fiscal de Serviço)</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Configuração de campos específicos para a emissão de NFS-e.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Classe de Imposto (NFS-e)
                    </label>
                    <input type="text" name="nfse_classe_imposto"
                           value="<?= htmlspecialchars($this->session->get('old')['nfse_classe_imposto'] ?? $config['nfse_classe_imposto'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Ex: REF219973407">
                    <p class="mt-1 text-xs text-gray-500">Referência de classe de imposto pré-configurada no WebmaniaBR</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Tipo de Desconto
                    </label>
                    <select name="nfse_tipo_desconto"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="nenhum" <?= ($this->session->get('old')['nfse_tipo_desconto'] ?? $config['nfse_tipo_desconto'] ?? 'nenhum') == 'nenhum' ? 'selected' : '' ?>>Nenhum</option>
                        <option value="condicional" <?= ($this->session->get('old')['nfse_tipo_desconto'] ?? $config['nfse_tipo_desconto'] ?? '') == 'condicional' ? 'selected' : '' ?>>Condicional</option>
                        <option value="incondicional" <?= ($this->session->get('old')['nfse_tipo_desconto'] ?? $config['nfse_tipo_desconto'] ?? '') == 'incondicional' ? 'selected' : '' ?>>Incondicional</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="nfse_incluir_taxas" value="1"
                               <?= ($this->session->get('old')['nfse_incluir_taxas'] ?? $config['nfse_incluir_taxas'] ?? 0) ? 'checked' : '' ?>
                               class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="font-semibold text-gray-900 dark:text-white">Incluir taxas no valor do serviço</span>
                    </label>
                    <p class="ml-8 text-sm text-gray-600 dark:text-gray-400">Incluir o valor das taxas do pedido no valor do serviço da NFS-e</p>
                </div>
            </div>
        </div>

        <!-- Configurações NF-e -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Configurações (Nota Fiscal de Produto)</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Configuração de campos específicos para a emissão de NF-e.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Natureza da Operação
                    </label>
                    <input type="text" name="natureza_operacao"
                           value="<?= htmlspecialchars($this->session->get('old')['natureza_operacao'] ?? $config['natureza_operacao'] ?? 'Venda') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: Venda">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Classe de Imposto (NF-e)
                    </label>
                    <input type="text" name="nfe_classe_imposto"
                           value="<?= htmlspecialchars($this->session->get('old')['nfe_classe_imposto'] ?? $config['nfe_classe_imposto'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: REF219973407">
                    <p class="mt-1 text-xs text-gray-500">Referência de classe de imposto pré-configurada no WebmaniaBR</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Código NCM Padrão
                    </label>
                    <input type="text" name="ncm_padrao" maxlength="8"
                           value="<?= htmlspecialchars($this->session->get('old')['ncm_padrao'] ?? $config['ncm_padrao'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: 95030099">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Código CEST Padrão
                    </label>
                    <input type="text" name="cest_padrao" maxlength="7"
                           value="<?= htmlspecialchars($this->session->get('old')['cest_padrao'] ?? $config['cest_padrao'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: 2806400">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Origem dos Produtos
                    </label>
                    <select name="origem_padrao"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="0" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 0 ? 'selected' : '' ?>>0 - Nacional, exceto as indicadas nos códigos 3, 4, 5 e 8</option>
                        <option value="1" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 1 ? 'selected' : '' ?>>1 - Estrangeira - Importação direta</option>
                        <option value="2" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 2 ? 'selected' : '' ?>>2 - Estrangeira - Adquirida no mercado interno</option>
                        <option value="3" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 3 ? 'selected' : '' ?>>3 - Nacional com conteúdo de importação > 40%</option>
                        <option value="4" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 4 ? 'selected' : '' ?>>4 - Nacional - Processo produtivo básico</option>
                        <option value="5" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 5 ? 'selected' : '' ?>>5 - Nacional com conteúdo de importação ≤ 40%</option>
                        <option value="6" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 6 ? 'selected' : '' ?>>6 - Estrangeira - Importação direta sem similar</option>
                        <option value="7" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 7 ? 'selected' : '' ?>>7 - Estrangeira - Adquirida no mercado interno sem similar</option>
                        <option value="8" <?= ($this->session->get('old')['origem_padrao'] ?? $config['origem_padrao'] ?? 0) == 8 ? 'selected' : '' ?>>8 - Nacional com conteúdo de importação > 70%</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Intermediador -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Indicativo de Intermediador</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Campos para indicar o intermediador da operação.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Intermediador da Operação
                    </label>
                    <select name="intermediador"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="0" <?= ($this->session->get('old')['intermediador'] ?? $config['intermediador'] ?? 0) == 0 ? 'selected' : '' ?>>0 - Operação sem intermediador (em site ou plataforma própria)</option>
                        <option value="1" <?= ($this->session->get('old')['intermediador'] ?? $config['intermediador'] ?? 0) == 1 ? 'selected' : '' ?>>1 - Operação em site ou plataforma de terceiros (intermediadores/marketplace)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        CNPJ do Intermediador
                    </label>
                    <input type="text" name="intermediador_cnpj" data-mask="cnpj"
                           value="<?= htmlspecialchars($this->session->get('old')['intermediador_cnpj'] ?? $config['intermediador_cnpj'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="00.000.000/0000-00">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        ID do Intermediador
                    </label>
                    <input type="text" name="intermediador_id"
                           value="<?= htmlspecialchars($this->session->get('old')['intermediador_id'] ?? $config['intermediador_id'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="ID do intermediador">
                </div>
            </div>
        </div>

        <!-- Informações Complementares -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Informações Complementares (Opcional)</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Informações fiscais complementares.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Informações ao Fisco
                    </label>
                    <textarea name="informacoes_fisco" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Informações de interesse do Fisco..."><?= htmlspecialchars($this->session->get('old')['informacoes_fisco'] ?? $config['informacoes_fisco'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Informações Complementares ao Consumidor
                    </label>
                    <textarea name="informacoes_complementares" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Informações complementares para o consumidor..."><?= htmlspecialchars($this->session->get('old')['informacoes_complementares'] ?? $config['informacoes_complementares'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Descrição Complementar do Serviço
                    </label>
                    <textarea name="descricao_complementar_servico" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Descrição complementar para NFS-e..."><?= htmlspecialchars($this->session->get('old')['descricao_complementar_servico'] ?? $config['descricao_complementar_servico'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Certificado Digital A1 -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Certificado Digital A1</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Upload do certificado digital A1 (.pfx ou .p12)</p>

            <?php if ($config && !empty($config['certificado_digital'])): ?>
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <p class="text-sm text-green-800 dark:text-green-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Certificado Digital Cadastrado
                        <?php if ($config['certificado_validade']): ?>
                            - Válido até <?= date('d/m/Y', strtotime($config['certificado_validade'])) ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Arquivo do Certificado (.pfx ou .p12)
                    </label>
                    <input type="file" name="certificado_arquivo" accept=".pfx,.p12"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Deixe em branco para manter o certificado atual</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Senha do Certificado
                    </label>
                    <input type="password" name="certificado_senha"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Senha do certificado">
                    <p class="mt-1 text-xs text-gray-500">Necessário apenas se estiver enviando novo certificado</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Data de Validade do Certificado
                    </label>
                    <input type="date" name="certificado_validade"
                           value="<?= htmlspecialchars($this->session->get('old')['certificado_validade'] ?? $config['certificado_validade'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-4">
            <a href="<?= $this->baseUrl('/integracoes') ?>" 
               class="px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors font-semibold">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-8 py-4 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl hover:from-emerald-700 hover:to-green-700 transition-all shadow-lg font-semibold">
                💾 <?= $isEdit ? 'Atualizar' : 'Salvar' ?> Integração
            </button>
        </div>
    </form>
</div>

<script src="/assets/js/masks.js"></script>
<script>
function webmanibrForm() {
    return {
        init() {
            // Aplicar máscaras
            if (typeof MaskManager !== 'undefined') {
                MaskManager.applyMasks();
            }
        }
    }
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
