<div class="max-w-7xl mx-auto" x-data="{ abaAtiva: '<?= $abaAtiva ?>' }">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configurações do Sistema</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Personalize o comportamento de cada módulo</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl border border-gray-200 dark:border-gray-700 border-b-0">
        <div class="flex overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            <?php
            $abas = [
                'empresas' => ['nome' => 'Empresas', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>'],
                'usuarios' => ['nome' => 'Usuários', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>'],
                'fornecedores' => ['nome' => 'Fornecedores', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>'],
                'clientes' => ['nome' => 'Clientes', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>'],
                'categorias' => ['nome' => 'Categorias', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>'],
                'centros_custo' => ['nome' => 'Centros de Custo', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>'],
                'contas_bancarias' => ['nome' => 'Contas Bancárias', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'],
                'contas_pagar' => ['nome' => 'Contas a Pagar', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>'],
                'contas_receber' => ['nome' => 'Contas a Receber', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>'],
                'movimentacoes' => ['nome' => 'Movimentações', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>'],
                'api' => ['nome' => 'API e IA', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>'],
                'sistema' => ['nome' => 'Sistema', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>']
            ];
            foreach ($abas as $key => $aba):
            ?>
                <button 
                    @click="abaAtiva = '<?= $key ?>'"
                    :class="abaAtiva === '<?= $key ?>' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'"
                    class="flex items-center space-x-2 px-6 py-4 font-medium transition-colors whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $aba['icon'] ?>
                    </svg>
                    <span><?= $aba['nome'] ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content Area -->
    <div class="bg-white dark:bg-gray-800 rounded-b-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
        <?php foreach ($grupos as $grupo): ?>
            <div x-show="abaAtiva === '<?= $grupo ?>'" x-cloak>
                <form method="POST" action="/configuracoes/salvar">
                    <input type="hidden" name="grupo" value="<?= $grupo ?>">
                    
                    <div class="space-y-6">
                        <?php if (isset($configuracoes[$grupo])): ?>
                            <?php foreach ($configuracoes[$grupo] as $chave => $config): ?>
                                <div class="flex items-start justify-between py-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                    <div class="flex-1">
                                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                            <?= htmlspecialchars($config['descricao']) ?>
                                        </label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($chave) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="ml-4">
                                        <?php if ($config['tipo'] === 'boolean'): ?>
                                            <!-- Toggle Switch -->
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="<?= htmlspecialchars($chave) ?>" value="true" 
                                                       <?= $config['valor'] ? 'checked' : '' ?>
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                            </label>
                                        <?php elseif ($config['tipo'] === 'number'): ?>
                                            <input type="number" name="<?= htmlspecialchars($chave) ?>" 
                                                   value="<?= htmlspecialchars($config['valor']) ?>"
                                                   class="w-32 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                                        <?php else: ?>
                                            <input type="text" name="<?= htmlspecialchars($chave) ?>" 
                                                   value="<?= htmlspecialchars($config['valor']) ?>"
                                                   placeholder="<?= strpos($chave, 'key') !== false || strpos($chave, 'senha') !== false ? '••••••••••' : '' ?>"
                                                   class="w-64 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                                Nenhuma configuração disponível neste módulo
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($configuracoes[$grupo]) && !empty($configuracoes[$grupo])): ?>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" 
                                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                                Salvar Configurações
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Ajuda -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-6">
        <div class="flex">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-blue-900 dark:text-blue-100 font-semibold mb-2">Sobre as Configurações</h3>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    As configurações definidas aqui afetam o comportamento dos formulários e validações em todo o sistema. 
                    Por exemplo, ao marcar um campo como obrigatório, o sistema irá validar sua presença em todos os formulários relacionados.
                </p>
                <ul class="mt-3 text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <li>• <strong>Campos Obrigatórios:</strong> Define quais campos devem ser preenchidos obrigatoriamente</li>
                    <li>• <strong>Auto-Geração:</strong> Gera códigos automaticamente quando habilitado</li>
                    <li>• <strong>API OpenAI:</strong> Necessária para funcionalidades de IA (sugestões automáticas, análises, etc)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>

<style>
[x-cloak] { display: none !important; }
</style>
