<?php
$especiesDocs = [
    'DM' => 'Duplicata Mercantil', 'DMI' => 'Duplicata Mercantil Indicacao', 'DS' => 'Duplicata de Servico',
    'DSI' => 'Duplicata Servico Indicacao', 'DR' => 'Duplicata Rural', 'LC' => 'Letra de Cambio',
    'NCC' => 'Nota Credito Comercial', 'NCE' => 'Nota Credito Exportacao', 'NCI' => 'Nota Credito Industrial',
    'NCR' => 'Nota Credito Rural', 'NP' => 'Nota Promissoria', 'NPR' => 'Nota Promissoria Rural',
    'RC' => 'Recibo', 'FAT' => 'Fatura', 'ND' => 'Nota de Debito', 'NF' => 'Nota Fiscal',
    'DD' => 'Documento de Divida', 'OU' => 'Outros'
];
?>

<style>
.ts-wrapper { min-width: 100% !important; }
.ts-wrapper .ts-dropdown { z-index: 9999 !important; }
.ts-wrapper .ts-control { min-height: 42px !important; padding: 8px 12px !important; }
</style>

<div class="max-w-5xl mx-auto" x-data="boletoForm()">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="/boletos?empresa_id=<?= $empresaId ?>" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Emitir Boleto</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Preencha os dados para gerar um novo boleto bancario</p>
        </div>
    </div>

    <!-- Alerta sem conexao de cobranca -->
    <?php if (empty($conexoes)): ?>
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Nenhuma conexao de cobranca configurada</h3>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Para emitir boletos, configure o "Numero do Cliente (Cobranca)" na sua conexao bancaria.</p>
                <a href="/conexoes-bancarias" class="inline-flex items-center mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm hover:bg-yellow-700">Configurar Conexao</a>
            </div>
        </div>
    </div>
    <?php else: ?>

    <!-- Mensagens -->
    <div x-show="sucesso" x-cloak class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-green-700 dark:text-green-300 font-medium" x-text="sucesso"></span>
        </div>
        <div x-show="boletoGerado" class="mt-3 p-3 bg-green-100 dark:bg-green-900/40 rounded-lg">
            <div class="text-sm"><strong>Nosso Numero:</strong> <span x-text="boletoGerado?.nosso_numero"></span></div>
            <div class="text-sm mt-1"><strong>Linha Digitavel:</strong> <span class="font-mono text-xs" x-text="boletoGerado?.linha_digitavel"></span></div>
            <a :href="'/boletos/' + boletoGerado?.boleto_id" class="inline-flex items-center mt-2 text-sm text-green-700 hover:underline font-semibold">Ver detalhes do boleto &rarr;</a>
        </div>
    </div>
    <div x-show="erro" x-cloak class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="text-red-700 dark:text-red-300 font-medium" x-text="erro"></span>
        </div>
    </div>

    <form @submit.prevent="emitirBoleto()" class="space-y-6">

        <!-- Secao 1: Conexao e Tipo -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Conexao Bancaria
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Banco / Conexao *</label>
                    <select x-model="form.conexao_bancaria_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Selecione...</option>
                        <?php foreach ($conexoes as $cx): ?>
                            <option value="<?= $cx['id'] ?>"><?= htmlspecialchars($cx['identificacao'] ?? $cx['banco']) ?> (<?= ucfirst($cx['banco']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Especie Documento</label>
                    <select x-model="form.especie_documento" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <?php foreach ($especiesDocs as $cod => $desc): ?>
                            <option value="<?= $cod ?>" <?= $cod === 'DM' ? 'selected' : '' ?>><?= $cod ?> - <?= $desc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Secao 2: Pagador (Cliente) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Dados do Pagador
            </h2>
            
            <!-- Selecionar cliente existente -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Selecionar Cliente Cadastrado</label>
                <select id="select-cliente" x-model="form.cliente_id" @change="preencherCliente($event.target.value)" class="w-full">
                    <option value="">-- Preencher manualmente --</option>
                    <?php foreach ($clientes as $cli): ?>
                        <option value="<?= $cli['id'] ?>" data-cpf="<?= $cli['cpf_cnpj'] ?? '' ?>" data-nome="<?= htmlspecialchars($cli['nome_razao_social'] ?? '') ?>" data-endereco="<?= htmlspecialchars($cli['endereco'] ?? '') ?>" data-bairro="<?= htmlspecialchars($cli['bairro'] ?? '') ?>" data-cidade="<?= htmlspecialchars($cli['cidade'] ?? '') ?>" data-cep="<?= $cli['cep'] ?? '' ?>" data-uf="<?= $cli['uf'] ?? '' ?>" data-email="<?= $cli['email'] ?? '' ?>">
                            <?= htmlspecialchars($cli['nome_razao_social'] ?? '') ?> - <?= $cli['cpf_cnpj'] ?? '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Selecionar pedido -->
            <?php if (!empty($pedidos)): ?>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Vincular a Pedido</label>
                <select id="select-pedido" x-model="form.pedido_vinculado_id" @change="preencherPedido($event.target.value)" class="w-full">
                    <option value="">-- Sem pedido vinculado --</option>
                    <?php foreach ($pedidos as $ped): ?>
                        <option value="<?= $ped['id'] ?>" data-valor="<?= $ped['valor_total'] ?? 0 ?>" data-cliente="<?= $ped['cliente_id'] ?? '' ?>" data-desc="<?= htmlspecialchars($ped['descricao'] ?? '') ?>">
                            #<?= $ped['numero'] ?? $ped['id'] ?> - <?= htmlspecialchars(substr($ped['descricao'] ?? '', 0, 60)) ?> (R$ <?= number_format($ped['valor_total'] ?? 0, 2, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">CPF/CNPJ *</label>
                    <input type="text" x-model="form.pagador_cpf_cnpj" required maxlength="14" placeholder="Somente numeros" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nome Completo *</label>
                    <input type="text" x-model="form.pagador_nome" required maxlength="50" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Endereco</label>
                    <input type="text" x-model="form.pagador_endereco" maxlength="40" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Bairro</label>
                    <input type="text" x-model="form.pagador_bairro" maxlength="30" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cidade</label>
                    <input type="text" x-model="form.pagador_cidade" maxlength="40" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">CEP</label>
                        <input type="text" x-model="form.pagador_cep" maxlength="8" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">UF</label>
                        <input type="text" x-model="form.pagador_uf" maxlength="2" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" x-model="form.pagador_email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
        </div>

        <!-- Secao 3: Valores e Datas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Valores e Datas
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Valor do Boleto (R$) *</label>
                    <input type="number" x-model="form.valor" step="0.01" min="0.01" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-lg font-bold">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Data Vencimento *</label>
                    <input type="date" x-model="form.data_vencimento" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Data Emissao</label>
                    <input type="date" x-model="form.data_emissao" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Data Limite Pagamento</label>
                    <input type="date" x-model="form.data_limite_pagamento" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Seu Numero (Referencia)</label>
                    <input type="text" x-model="form.seu_numero" maxlength="18" placeholder="Identificacao interna" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Numero Parcela</label>
                    <input type="number" x-model="form.numero_parcela" min="1" max="99" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>
        </div>

        <!-- Secao 4: Configuracoes avancadas (toggle) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <button type="button" @click="showAdvanced = !showAdvanced" class="w-full flex items-center justify-between text-lg font-bold text-gray-800 dark:text-gray-200">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configuracoes Avancadas
                </span>
                <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': showAdvanced }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="showAdvanced" x-cloak class="mt-6 space-y-6">
                <!-- Desconto -->
                <div>
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Desconto</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Tipo Desconto</label>
                            <select x-model="form.tipo_desconto" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="0">Sem Desconto</option>
                                <option value="1">Valor Fixo ate Data</option>
                                <option value="2">Percentual ate Data</option>
                                <option value="3">Valor Antecipacao Dia Corrido</option>
                            </select>
                        </div>
                        <div x-show="form.tipo_desconto > 0">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Data 1o Desconto</label>
                            <input type="date" x-model="form.data_primeiro_desconto" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div x-show="form.tipo_desconto > 0">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Valor 1o Desconto</label>
                            <input type="number" x-model="form.valor_primeiro_desconto" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Multa -->
                <div>
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Multa</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Tipo Multa</label>
                            <select x-model="form.tipo_multa" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="0">Isento</option>
                                <option value="1">Valor Fixo</option>
                                <option value="2">Percentual</option>
                            </select>
                        </div>
                        <div x-show="form.tipo_multa > 0">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Data Multa</label>
                            <input type="date" x-model="form.data_multa" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div x-show="form.tipo_multa > 0">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Valor Multa</label>
                            <input type="number" x-model="form.valor_multa" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Juros -->
                <div>
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Juros de Mora</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Tipo Juros</label>
                            <select x-model="form.tipo_juros_mora" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="3">Isento</option>
                                <option value="1">Valor por Dia</option>
                                <option value="2">Taxa Mensal</option>
                            </select>
                        </div>
                        <div x-show="form.tipo_juros_mora < 3">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Data Juros</label>
                            <input type="date" x-model="form.data_juros_mora" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div x-show="form.tipo_juros_mora < 3">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Valor Juros</label>
                            <input type="number" x-model="form.valor_juros_mora" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Protesto e Negativacao -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Protesto</h3>
                        <div class="space-y-3">
                            <select x-model="form.codigo_protesto" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="3">Nao Protestar</option>
                                <option value="1">Protestar Dias Corridos</option>
                                <option value="2">Protestar Dias Uteis</option>
                            </select>
                            <div x-show="form.codigo_protesto < 3">
                                <input type="number" x-model="form.dias_protesto" min="1" placeholder="Dias para protesto" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Negativacao (SERASA)</h3>
                        <div class="space-y-3">
                            <select x-model="form.codigo_negativacao" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="3">Nao Negativar</option>
                                <option value="2">Negativar Dias Uteis</option>
                            </select>
                            <div x-show="form.codigo_negativacao == 2">
                                <input type="number" x-model="form.dias_negativacao" min="1" placeholder="Dias para negativacao" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PIX e Instrucoes -->
                <div>
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">PIX e Instrucoes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Boleto Hibrido (PIX)</label>
                            <select x-model="form.codigo_cadastrar_pix" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                <option value="0">Padrao (conforme cadastro)</option>
                                <option value="1">Com PIX</option>
                                <option value="2">Sem PIX</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <label class="block text-sm text-gray-600 dark:text-gray-400">Instrucoes (max 5 linhas, 40 caracteres cada)</label>
                        <template x-for="i in 5" :key="i">
                            <input type="text" :x-model="'form.instrucao_' + i" x-bind:name="'instrucao_' + i" maxlength="40" :placeholder="'Instrucao ' + i" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secao 5: Opcoes finais -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.criar_conta_receber" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Criar Conta a Receber vinculada</span>
                </label>
            </div>
        </div>

        <!-- Botao Submit -->
        <div class="flex justify-end gap-3">
            <a href="/boletos?empresa_id=<?= $empresaId ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">Cancelar</a>
            <button type="submit" :disabled="enviando" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <svg x-show="enviando" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="enviando ? 'Emitindo...' : 'Emitir Boleto'"></span>
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
function boletoForm() {
    return {
        form: {
            conexao_bancaria_id: '',
            especie_documento: 'DM',
            cliente_id: '',
            pedido_vinculado_id: '',
            pagador_cpf_cnpj: '',
            pagador_nome: '',
            pagador_endereco: '',
            pagador_bairro: '',
            pagador_cidade: '',
            pagador_cep: '',
            pagador_uf: '',
            pagador_email: '',
            valor: '',
            data_vencimento: '',
            data_emissao: new Date().toISOString().slice(0, 10),
            data_limite_pagamento: '',
            seu_numero: '',
            numero_parcela: 1,
            tipo_desconto: '0',
            data_primeiro_desconto: '',
            valor_primeiro_desconto: '',
            tipo_multa: '0',
            data_multa: '',
            valor_multa: '',
            tipo_juros_mora: '3',
            data_juros_mora: '',
            valor_juros_mora: '',
            codigo_protesto: '3',
            dias_protesto: '',
            codigo_negativacao: '3',
            dias_negativacao: '',
            codigo_cadastrar_pix: '1',
            instrucao_1: '', instrucao_2: '', instrucao_3: '', instrucao_4: '', instrucao_5: '',
            criar_conta_receber: false,
        },
        showAdvanced: false,
        enviando: false,
        sucesso: '',
        erro: '',
        boletoGerado: null,

        preencherCliente(clienteId) {
            if (!clienteId) return;
            const opt = document.querySelector(`#select-cliente option[value="${clienteId}"]`);
            if (opt) {
                this.form.pagador_cpf_cnpj = (opt.dataset.cpf || '').replace(/[^0-9]/g, '');
                this.form.pagador_nome = opt.dataset.nome || '';
                this.form.pagador_endereco = opt.dataset.endereco || '';
                this.form.pagador_bairro = opt.dataset.bairro || '';
                this.form.pagador_cidade = opt.dataset.cidade || '';
                this.form.pagador_cep = (opt.dataset.cep || '').replace(/[^0-9]/g, '');
                this.form.pagador_uf = opt.dataset.uf || '';
                this.form.pagador_email = opt.dataset.email || '';
            }
        },

        preencherPedido(pedidoId) {
            if (!pedidoId) return;
            const opt = document.querySelector(`#select-pedido option[value="${pedidoId}"]`);
            if (opt) {
                if (opt.dataset.valor) this.form.valor = parseFloat(opt.dataset.valor);
                if (opt.dataset.cliente) {
                    this.form.cliente_id = opt.dataset.cliente;
                    this.preencherCliente(opt.dataset.cliente);
                }
            }
        },

        async emitirBoleto() {
            this.enviando = true;
            this.sucesso = '';
            this.erro = '';
            this.boletoGerado = null;

            try {
                const resp = await fetch('/boletos', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.form)
                });
                const data = await resp.json();
                if (data.success) {
                    this.sucesso = data.message || 'Boleto emitido com sucesso!';
                    this.boletoGerado = data;
                } else {
                    this.erro = data.error || 'Erro ao emitir boleto';
                }
            } catch (e) {
                this.erro = 'Erro de conexao: ' + e.message;
            } finally {
                this.enviando = false;
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof TomSelect !== 'undefined') {
        new TomSelect('#select-cliente', { create: false, allowEmptyOption: true });
        const selPedido = document.getElementById('select-pedido');
        if (selPedido) new TomSelect('#select-pedido', { create: false, allowEmptyOption: true });
    }
});
</script>
