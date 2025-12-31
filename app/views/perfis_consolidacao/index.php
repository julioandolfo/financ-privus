<div class="max-w-7xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-3xl font-bold text-white mb-2">Perfis de Consolidação</h1>
                        <!-- Botão de Ajuda -->
                        <button 
                            onclick="document.getElementById('modalHelp').classList.remove('hidden')"
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 transition-colors mb-2"
                            title="O que são Perfis de Consolidação?"
                        >
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-blue-100">Gerencie perfis de consolidação de múltiplas empresas</p>
                </div>
            </div>
            <a href="<?= $this->baseUrl('/perfis-consolidacao/create') ?>" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Novo Perfil
            </a>
        </div>
    </div>

    <?php if (!empty($perfisUsuario)): ?>
    <!-- Perfis do Usuário -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Meus Perfis</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($perfisUsuario as $perfil): 
                $empresasIds = json_decode($perfil['empresas_ids'], true);
                $numEmpresas = count($empresasIds);
            ?>
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                <?= htmlspecialchars($perfil['nome']) ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <?= $numEmpresas ?> <?= $numEmpresas == 1 ? 'empresa' : 'empresas' ?>
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/aplicar?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" 
                               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                Aplicar
                            </a>
                            <a href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id']) ?>" 
                               class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/edit') ?>" 
                               class="p-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form method="POST" action="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/delete') ?>" class="inline" 
                                  onsubmit="return confirm('Tem certeza que deseja excluir este perfil?')">
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($perfisCompartilhados)): ?>
    <!-- Perfis Compartilhados -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Perfis Compartilhados</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($perfisCompartilhados as $perfil): 
                $empresasIds = json_decode($perfil['empresas_ids'], true);
                $numEmpresas = count($empresasIds);
            ?>
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                <?= htmlspecialchars($perfil['nome']) ?>
                                <span class="ml-2 px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full">
                                    Compartilhado
                                </span>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <?= $numEmpresas ?> <?= $numEmpresas == 1 ? 'empresa' : 'empresas' ?>
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id'] . '/aplicar?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" 
                               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                Aplicar
                            </a>
                            <a href="<?= $this->baseUrl('/perfis-consolidacao/' . $perfil['id']) ?>" 
                               class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($perfisUsuario) && empty($perfisCompartilhados)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-4">Nenhum perfil de consolidação cadastrado</p>
        <a href="<?= $this->baseUrl('/perfis-consolidacao/create') ?>" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium">
            Criar Primeiro Perfil
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de Ajuda -->
<div 
    id="modalHelp"
    class="fixed inset-0 z-50 overflow-y-auto hidden"
>
    <!-- Overlay -->
    <div 
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        onclick="document.getElementById('modalHelp').classList.add('hidden')"
    ></div>
    
    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div 
            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full animate-fade-in"
            onclick="event.stopPropagation()"
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        O que são Perfis de Consolidação?
                    </h2>
                </div>
                <button 
                    onclick="document.getElementById('modalHelp').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Definição -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        O que é?
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Os <strong>Perfis de Consolidação</strong> permitem que você agrupe múltiplas empresas para visualizar dados financeiros consolidados em um único relatório. Isso é essencial para gestores que precisam ter uma visão unificada de diferentes unidades de negócio.
                    </p>
                </div>

                <!-- Para que serve -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Para que serve?
                    </h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong>Visão consolidada:</strong> Veja receitas, despesas e fluxo de caixa de várias empresas simultaneamente</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong>Economia de tempo:</strong> Não precisa filtrar empresa por empresa manualmente</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong>Perfis reutilizáveis:</strong> Crie uma vez e aplique quando necessário</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong>Análises comparativas:</strong> Compare desempenho entre diferentes grupos de empresas</span>
                        </li>
                    </ul>
                </div>

                <!-- Como funciona -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Como funciona?
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold mr-3">1</span>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Crie um perfil:</strong> Clique em "Novo Perfil", dê um nome e selecione 2 ou mais empresas</p>
                        </div>
                        <div class="flex items-start bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold mr-3">2</span>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Aplique o perfil:</strong> Clique em "Aplicar" para ativar a consolidação</p>
                        </div>
                        <div class="flex items-start bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-sm font-bold mr-3">3</span>
                            <p class="text-gray-700 dark:text-gray-300"><strong>Visualize os dados:</strong> Todos os relatórios mostrarão dados consolidados das empresas selecionadas</p>
                        </div>
                    </div>
                </div>

                <!-- Exemplo -->
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Exemplo Prático
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>Cenário:</strong> Você tem 3 empresas (Matriz, Filial SP e Filial RJ). Crie um perfil chamado "Consolidado Nacional" incluindo as 3 empresas. Ao aplicar este perfil, o dashboard mostrará o total de receitas e despesas das 3 empresas somadas, facilitando a análise do desempenho geral do grupo empresarial.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl">
                <button 
                    onclick="document.getElementById('modalHelp').classList.add('hidden')"
                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    Entendi!
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Limpar sessão
$this->session->delete('old');
$this->session->delete('errors');
?>
