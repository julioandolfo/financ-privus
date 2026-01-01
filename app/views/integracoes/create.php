<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/integracoes') ?>" 
           class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
            ➕ Nova Integração
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Escolha o tipo de integração que deseja configurar</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- WooCommerce -->
        <a href="<?= $this->baseUrl('/integracoes/create/woocommerce') ?>" 
           class="group bg-white dark:bg-gray-800 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all shadow-lg hover:shadow-2xl transform hover:-translate-y-1">
            <div class="flex justify-between items-start mb-4">
                <div class="w-16 h-16 bg-purple-500 dark:bg-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="relative group/tooltip">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <div class="invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 absolute right-0 top-10 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-xl p-4 shadow-2xl z-50 transition-all duration-200">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-gray-900 dark:bg-gray-950 transform rotate-45"></div>
                        <p class="font-semibold mb-2">Como funciona:</p>
                        <ul class="space-y-1 text-xs text-gray-300">
                            <li>• Conecta via API REST do WooCommerce</li>
                            <li>• Sincroniza produtos, pedidos e clientes</li>
                            <li>• Webhooks em tempo real</li>
                            <li>• Atualização bidirecional de estoque</li>
                            <li>• Sincronização automática configurável</li>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">WooCommerce</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Integre sua loja WooCommerce para sincronizar produtos, pedidos e clientes automaticamente.
            </p>
            <div class="flex items-center text-purple-600 dark:text-purple-400 font-semibold">
                Configurar agora
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>

        <!-- Banco de Dados -->
        <a href="<?= $this->baseUrl('/integracoes/create/banco-dados') ?>" 
           class="group bg-white dark:bg-gray-800 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700 hover:border-amber-500 dark:hover:border-amber-500 transition-all shadow-lg hover:shadow-2xl transform hover:-translate-y-1">
            <div class="flex justify-between items-start mb-4">
                <div class="w-16 h-16 bg-amber-500 dark:bg-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </div>
                <div class="relative group/tooltip">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <div class="invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 absolute right-0 top-10 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-xl p-4 shadow-2xl z-50 transition-all duration-200">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-gray-900 dark:bg-gray-950 transform rotate-45"></div>
                        <p class="font-semibold mb-2">Como funciona:</p>
                        <ul class="space-y-1 text-xs text-gray-300">
                            <li>• Conecta diretamente ao banco externo</li>
                            <li>• Suporta MySQL, PostgreSQL, SQL Server</li>
                            <li>• Importa dados por queries SQL</li>
                            <li>• Mapeamento de colunas personalizável</li>
                            <li>• Sincronização agendada automática</li>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Banco de Dados</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Conecte a bancos de dados externos (MySQL, PostgreSQL, SQL Server) para importar dados automaticamente.
            </p>
            <div class="flex items-center text-amber-600 dark:text-amber-400 font-semibold">
                Configurar agora
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>
        <!-- Webhook -->
        <a href="<?= $this->baseUrl('/integracoes/create/webhook') ?>" 
           class="group bg-white dark:bg-gray-800 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500 transition-all shadow-lg hover:shadow-2xl transform hover:-translate-y-1">
            <div class="flex justify-between items-start mb-4">
                <div class="w-16 h-16 bg-green-500 dark:bg-green-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="relative group/tooltip">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <div class="invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 absolute right-0 top-10 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-xl p-4 shadow-2xl z-50 transition-all duration-200">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-gray-900 dark:bg-gray-950 transform rotate-45"></div>
                        <p class="font-semibold mb-2">Como funciona:</p>
                        <ul class="space-y-1 text-xs text-gray-300">
                            <li>• Envia dados em tempo real via HTTP</li>
                            <li>• Dispara eventos (pedido, produto, cliente)</li>
                            <li>• Suporta autenticação (Basic, Bearer, API Key)</li>
                            <li>• Payload personalizado (JSON/XML)</li>
                            <li>• Sistema de retry automático</li>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Webhook</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Envie dados em tempo real para URLs externas quando eventos ocorrem no sistema (criar pedido, atualizar produto, etc).
            </p>
            <div class="flex items-center text-green-600 dark:text-green-400 font-semibold">
                Configurar agora
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>

        <!-- API REST -->
        <a href="<?= $this->baseUrl('/integracoes/create/api') ?>" 
           class="group bg-white dark:bg-gray-800 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 transition-all shadow-lg hover:shadow-2xl transform hover:-translate-y-1">
            <div class="flex justify-between items-start mb-4">
                <div class="w-16 h-16 bg-orange-500 dark:bg-orange-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="relative group/tooltip">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <div class="invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 absolute right-0 top-10 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-xl p-4 shadow-2xl z-50 transition-all duration-200">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-gray-900 dark:bg-gray-950 transform rotate-45"></div>
                        <p class="font-semibold mb-2">Como funciona:</p>
                        <ul class="space-y-1 text-xs text-gray-300">
                            <li>• Consome APIs REST, GraphQL ou SOAP</li>
                            <li>• Autenticação avançada (OAuth 2.0)</li>
                            <li>• Mapeamento de endpoints customizável</li>
                            <li>• Headers personalizados por request</li>
                            <li>• Ideal para ERPs, CRMs e sistemas externos</li>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">API REST</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Consuma APIs REST, GraphQL ou SOAP externas. Perfeito para integrar com ERPs, CRMs e outros sistemas.
            </p>
            <div class="flex items-center text-orange-600 dark:text-orange-400 font-semibold">
                Configurar agora
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>

        <!-- WebmaniaBR (NF-e / NFS-e) -->
        <a href="<?= $this->baseUrl('/integracoes/create/webmanibr') ?>" 
           class="group bg-white dark:bg-gray-800 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 transition-all shadow-lg hover:shadow-2xl transform hover:-translate-y-1">
            <div class="flex justify-between items-start mb-4">
                <div class="w-16 h-16 bg-emerald-500 dark:bg-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="relative group/tooltip">
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <div class="invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 absolute right-0 top-10 w-72 bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-xl p-4 shadow-2xl z-50 transition-all duration-200">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-gray-900 dark:bg-gray-950 transform rotate-45"></div>
                        <p class="font-semibold mb-2">Como funciona:</p>
                        <ul class="space-y-1 text-xs text-gray-300">
                            <li>• Emissão de NF-e e NFS-e automatizada</li>
                            <li>• Integração oficial com SEFAZ</li>
                            <li>• Geração de DANFE e XML</li>
                            <li>• Cancelamento e consulta de status</li>
                            <li>• Classes de tributos pré-configuradas</li>
                            <li>• Envio automático por e-mail</li>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">WebmaniaBR (NF-e)</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Emita Notas Fiscais Eletrônicas (NF-e) e Notas Fiscais de Serviço (NFS-e) automaticamente. Integração oficial com SEFAZ.
            </p>
            <div class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold">
                Configurar agora
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </a>
    </div>
</div>
