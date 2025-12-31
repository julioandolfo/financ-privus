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
            <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
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
            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
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
    </div>

    <!-- Em breve -->
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">🚀 Em Breve</h3>
        <p class="text-blue-700 dark:text-blue-300 text-sm">
            Mais integrações estão chegando: Bling, Mercado Livre, APIs REST personalizadas, Webhooks e muito mais!
        </p>
    </div>
</div>
