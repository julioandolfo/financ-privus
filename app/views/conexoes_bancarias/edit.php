<?php
use App\Models\ConexaoBancaria;
$bancoInfo = ConexaoBancaria::getBancoInfo($conexao['banco']);
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="/conexoes-bancarias/<?= $conexao['id'] ?>" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Editar Conexão Bancária
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            <?= $bancoInfo['logo'] ?> <?= htmlspecialchars($bancoInfo['nome']) ?> — <?= htmlspecialchars($conexao['identificacao'] ?: 'Conta ' . $conexao['id']) ?>
        </p>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl p-4 mb-6">
            <p class="text-sm text-red-800 dark:text-red-200 font-semibold"><?= $_SESSION['error'] ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4 mb-6">
            <p class="text-sm text-green-800 dark:text-green-200 font-semibold"><?= $_SESSION['success'] ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <form action="/conexoes-bancarias/<?= $conexao['id'] ?>" method="POST" enctype="multipart/form-data">

            <!-- Identificação -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Identificação (Apelido)
                </label>
                <input type="text" name="identificacao" value="<?= htmlspecialchars($conexao['identificacao'] ?? '') ?>"
                       placeholder="Ex: Conta Principal, Conta Fornecedores"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            <!-- Número da Conta no Banco -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Número da Conta Corrente (API)
                </label>
                <input type="text" name="banco_conta_id" value="<?= htmlspecialchars($conexao['banco_conta_id'] ?? '') ?>"
                       placeholder="Somente números, sem pontos ou hífens"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <strong>Importante:</strong> Informe apenas números (sem pontos, traços ou dígito verificador separado). 
                    Exemplo: se a conta é 23.008-5, digite <strong>230085</strong>
                </p>
            </div>

            <!-- Vincular a Conta Bancária do Sistema -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Vincular a Conta do Sistema (Opcional)
                </label>
                <select name="conta_bancaria_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Nenhuma (não vincular)</option>
                    <?php foreach ($contas_bancarias as $cb): ?>
                        <option value="<?= $cb['id'] ?>" <?= ($conexao['conta_bancaria_id'] ?? '') == $cb['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cb['banco_nome'] . ' - Ag: ' . $cb['agencia'] . ' Cc: ' . $cb['conta']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Vincula o saldo real da API ao saldo da conta no sistema</p>
            </div>

            <!-- Credenciais -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                    Credenciais <?= htmlspecialchars($bancoInfo['nome']) ?>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Deixe em branco para manter os valores atuais</p>

                <!-- Client ID -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Client ID</label>
                    <input type="text" name="client_id" value="<?= htmlspecialchars($conexao['client_id'] ?? '') ?>"
                           placeholder="Client ID da aplicação"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <?php if ($conexao['banco'] === 'sicoob'): ?>
                <!-- Cooperativa (Sicoob) -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Número da Cooperativa</label>
                    <input type="text" name="cooperativa" value="<?= htmlspecialchars($conexao['cooperativa'] ?? '') ?>"
                           placeholder="Ex: 3125"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <?php endif; ?>

                <!-- Certificado PFX -->
                <div class="mb-4" x-data="{ fileName: '', hasFile: false, hasExisting: <?= !empty($conexao['cert_pfx']) ? 'true' : 'false' ?> }">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Certificado Digital (.pfx)</label>
                    
                    <template x-if="hasExisting && !hasFile">
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-900/20 mb-2">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-sm text-green-700 dark:text-green-400 font-medium">Certificado PFX já configurado</span>
                            <span class="text-xs text-gray-500">(envie novo para substituir)</span>
                        </div>
                    </template>
                    
                    <label class="flex flex-col items-center justify-center w-full px-4 py-6 rounded-xl border-2 border-dashed cursor-pointer transition-all"
                           :class="hasFile ? 'border-green-400 bg-green-50 dark:border-green-500 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20'">
                        <div class="flex flex-col items-center" x-show="!hasFile">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400" x-text="hasExisting ? 'Clique para substituir o certificado' : 'Clique para selecionar o certificado'"></span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">.pfx, .p12</span>
                        </div>
                        <div class="flex items-center gap-3" x-show="hasFile">
                            <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400" x-text="fileName"></span>
                            <span class="text-xs text-gray-400">(clique para trocar)</span>
                        </div>
                        <input type="file" name="cert_pfx" accept=".pfx,.p12" class="hidden"
                               @change="if($event.target.files.length){ fileName = $event.target.files[0].name; hasFile = true; } else { hasFile = false; }">
                    </label>
                </div>

                <!-- Senha do Certificado -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Senha do Certificado</label>
                    <input type="password" name="cert_password" placeholder="Deixe em branco para manter a atual"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Ambiente -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ambiente</label>
                    <select name="ambiente" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="producao" <?= ($conexao['ambiente'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                        <option value="sandbox" <?= ($conexao['ambiente'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (testes)</option>
                    </select>
                </div>

                <!-- Certificados PEM (alternativa) -->
                <details class="mb-4">
                    <summary class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                        Certificados PEM (alternativa ao PFX)
                    </summary>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Certificado PEM / CER</label>
                            <textarea name="cert_pem" rows="3" placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"
                                      class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono text-xs"><?= !empty($conexao['cert_pem']) ? 'Certificado PEM configurado (cole novo para substituir)' : '' ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Chave Privada .KEY</label>
                            <textarea name="key_pem" rows="3" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"
                                      class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono text-xs"><?= !empty($conexao['key_pem']) ? 'Chave privada configurada (cole nova para substituir)' : '' ?></textarea>
                        </div>
                    </div>
                </details>
            </div>

            <!-- Cobrança Bancária (Boletos) -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cobranca Bancaria (Boletos)
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Preencha para habilitar a emissao de boletos por esta conexao</p>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Numero do Cliente (Cobranca)</label>
                    <input type="text" name="numero_cliente_banco" value="<?= htmlspecialchars($conexao['numero_cliente_banco'] ?? '') ?>"
                           placeholder="Ex: 25546454"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="mt-1 text-xs text-gray-500">Numero que identifica o beneficiario na plataforma de cobranca da cooperativa.</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Modalidade de Cobranca</label>
                    <select name="codigo_modalidade_cobranca" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="1" <?= ($conexao['codigo_modalidade_cobranca'] ?? 1) == 1 ? 'selected' : '' ?>>1 - Simples com Registro</option>
                        <option value="3" <?= ($conexao['codigo_modalidade_cobranca'] ?? '') == 3 ? 'selected' : '' ?>>3 - Caucionada</option>
                        <option value="4" <?= ($conexao['codigo_modalidade_cobranca'] ?? '') == 4 ? 'selected' : '' ?>>4 - Vinculada</option>
                        <option value="5" <?= ($conexao['codigo_modalidade_cobranca'] ?? '') == 5 ? 'selected' : '' ?>>5 - Carne de Pagamentos</option>
                        <option value="6" <?= ($conexao['codigo_modalidade_cobranca'] ?? '') == 6 ? 'selected' : '' ?>>6 - Indexada</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Conta Corrente (Cobranca)</label>
                    <input type="text" name="conta_corrente_cobranca" value="<?= htmlspecialchars($conexao['conta_corrente_cobranca'] ?? '') ?>"
                           placeholder="Se diferente da conta do extrato"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="mt-1 text-xs text-gray-500">Conta para credito da liquidacao de boletos. Se vazio, usa a conta do extrato.</p>
                </div>
            </div>

            <!-- Configurações de Sincronização -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Configurações de Sincronização</h3>
                
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_sync" value="1" <?= ($conexao['auto_sync'] ?? 0) ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Sincronização Automática</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Frequência</label>
                        <select name="frequencia_sync" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="manual" <?= ($conexao['frequencia_sync'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="10min" <?= ($conexao['frequencia_sync'] ?? '') === '10min' ? 'selected' : '' ?>>A cada 10 minutos</option>
                            <option value="30min" <?= ($conexao['frequencia_sync'] ?? '') === '30min' ? 'selected' : '' ?>>A cada 30 minutos</option>
                            <option value="horaria" <?= ($conexao['frequencia_sync'] ?? '') === 'horaria' ? 'selected' : '' ?>>A cada hora</option>
                            <option value="diaria" <?= ($conexao['frequencia_sync'] ?? '') === 'diaria' ? 'selected' : '' ?>>Diária</option>
                            <option value="semanal" <?= ($conexao['frequencia_sync'] ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">O que sincronizar</label>
                        <select name="tipo_sync" class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="ambos" <?= ($conexao['tipo_sync'] ?? 'ambos') === 'ambos' ? 'selected' : '' ?>>Despesas e Receitas</option>
                            <option value="apenas_despesas" <?= ($conexao['tipo_sync'] ?? '') === 'apenas_despesas' ? 'selected' : '' ?>>Apenas Despesas (débitos)</option>
                            <option value="apenas_receitas" <?= ($conexao['tipo_sync'] ?? '') === 'apenas_receitas' ? 'selected' : '' ?>>Apenas Receitas (créditos)</option>
                        </select>
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
                                <option value="<?= $cat['id'] ?>" <?= ($conexao['categoria_padrao_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $cc['id'] ?>" <?= ($conexao['centro_custo_padrao_id'] ?? '') == $cc['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cc['codigo'] . ' - ' . $cc['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aprovacao_automatica" value="1" <?= ($conexao['aprovacao_automatica'] ?? 0) ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Aprovação Automática (transações com alta confiança)</span>
                    </label>
                </div>
            </div>

            <!-- Status -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Status</h3>
                <div class="flex items-center gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" value="1" <?= ($conexao['ativo'] ?? 1) ? 'checked' : '' ?>
                               class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Conexão Ativa</span>
                    </label>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <a href="/conexoes-bancarias/<?= $conexao['id'] ?>" class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 text-center">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$this->session->delete('success');
$this->session->delete('error');
?>
