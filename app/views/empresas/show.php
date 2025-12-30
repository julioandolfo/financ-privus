<?php
/**
 * Detalhes da empresa
 */
?>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['razao_social']) ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Detalhes da empresa</p>
        </div>
        <div class="flex space-x-2">
            <a href="/empresas/<?= $empresa['id'] ?>/edit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Editar</span>
            </a>
            <a href="/empresas" class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações Básicas -->
        <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações Básicas</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['codigo']) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Razão Social</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['razao_social']) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome Fantasia</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['nome_fantasia']) ?></dd>
                </div>
                <?php if ($empresa['cnpj']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CNPJ</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['cnpj']) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Status e Datas -->
        <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status e Datas</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <?php if ($empresa['ativo']): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Ativa</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Inativa</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if ($empresa['grupo_empresarial_id']): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grupo Empresarial ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($empresa['grupo_empresarial_id']) ?></dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Cadastro</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                        <?= date('d/m/Y H:i', strtotime($empresa['data_cadastro'])) ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <?php if ($empresa['configuracoes']): ?>
        <div class="mt-6 bg-gray-50 dark:bg-slate-700/50 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Configurações</h2>
            <pre class="text-sm text-gray-700 dark:text-gray-300 overflow-x-auto"><?= htmlspecialchars(json_encode(json_decode($empresa['configuracoes']), JSON_PRETTY_PRINT)) ?></pre>
        </div>
    <?php endif; ?>
</div>

