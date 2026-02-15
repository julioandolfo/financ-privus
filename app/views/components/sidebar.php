<?php
/**
 * Sidebar de navegação do sistema - REORGANIZADO
 * Suporta tema dark/light/system e menus/submenus
 */
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Função helper para verificar se rota está ativa
$isActive = function($path) use ($currentPath, $basePath) {
    $fullPath = $basePath . $path;
    return strpos($currentPath, $fullPath) === 0 || $currentPath === $fullPath;
};

// Menu items organizado hierarquicamente
$menuItems = [
    [
        'title' => 'Dashboard',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
        'path' => '/',
        'active' => $isActive('/') && !$isActive('/empresas') && !$isActive('/usuarios')
    ],
    [
        'title' => 'Cadastros',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
        'submenu' => [
            [
                'title' => 'Empresas',
                'path' => '/empresas',
                'active' => $isActive('/empresas')
            ],
            [
                'title' => 'Usuários',
                'path' => '/usuarios',
                'active' => $isActive('/usuarios')
            ],
            [
                'title' => 'Fornecedores',
                'path' => '/fornecedores',
                'active' => $isActive('/fornecedores')
            ],
            [
                'title' => 'Clientes',
                'path' => '/clientes',
                'active' => $isActive('/clientes')
            ],
        ]
    ],
    [
        'title' => 'Pedidos / Produtos',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
        'submenu' => [
            [
                'title' => 'Pedidos',
                'path' => '/pedidos',
                'active' => $isActive('/pedidos')
            ],
            [
                'title' => 'Produtos',
                'path' => '/produtos',
                'active' => $isActive('/produtos')
            ],
            [
                'title' => 'Categorias',
                'path' => '/categorias-produtos',
                'active' => $isActive('/categorias-produtos')
            ],
        ]
    ],
    [
        'title' => 'Financeiro',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
        'submenu' => [
            [
                'title' => 'Categorias Financeiras',
                'path' => '/categorias',
                'active' => $isActive('/categorias')
            ],
            [
                'title' => 'Centros de Custo',
                'path' => '/centros-custo',
                'active' => $isActive('/centros-custo')
            ],
            [
                'title' => 'Formas de Pagamento',
                'path' => '/formas-pagamento',
                'active' => $isActive('/formas-pagamento')
            ],
            [
                'title' => 'Contas Bancárias',
                'path' => '/contas-bancarias',
                'active' => $isActive('/contas-bancarias')
            ],
        ]
    ],
    [
        'title' => 'Contas',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'submenu' => [
            [
                'title' => 'Contas a Pagar',
                'path' => '/contas-pagar',
                'active' => $isActive('/contas-pagar')
            ],
            [
                'title' => 'Importar Extrato',
                'path' => '/extrato-bancario',
                'active' => $isActive('/extrato-bancario')
            ],
            [
                'title' => 'Contas a Receber',
                'path' => '/contas-receber',
                'active' => $isActive('/contas-receber')
            ],
            [
                'title' => 'Despesas Recorrentes',
                'path' => '/despesas-recorrentes',
                'active' => $isActive('/despesas-recorrentes')
            ],
            [
                'title' => 'Receitas Recorrentes',
                'path' => '/receitas-recorrentes',
                'active' => $isActive('/receitas-recorrentes')
            ],
            [
                'title' => 'Movimentações de Caixa',
                'path' => '/movimentacoes-caixa',
                'active' => $isActive('/movimentacoes-caixa')
            ],
            [
                'title' => 'Conciliação Bancária',
                'path' => '/conciliacao-bancaria',
                'active' => $isActive('/conciliacao-bancaria')
            ],
            [
                'title' => 'NF-es (Notas Fiscais)',
                'path' => '/nfes',
                'active' => $isActive('/nfes')
            ],
        ]
    ],
    [
        'title' => 'Relatórios',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'submenu' => [
            [
                'title' => 'Fluxo de Caixa',
                'path' => '/fluxo-caixa',
                'active' => $isActive('/fluxo-caixa')
            ],
            [
                'title' => 'DRE',
                'path' => '/dre',
                'active' => $isActive('/dre')
            ],
            [
                'title' => 'DFC',
                'path' => '/dfc',
                'active' => $isActive('/dfc')
            ],
            [
                'title' => 'Conciliação',
                'path' => '/conciliacao',
                'active' => $isActive('/conciliacao')
            ],
            [
                'title' => 'Outros',
                'path' => '/relatorios',
                'active' => $isActive('/relatorios')
            ],
        ]
    ],
    [
        'title' => 'Integrações',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
        'submenu' => [
            [
                'title' => 'Integrações',
                'path' => '/integracoes',
                'active' => $isActive('/integracoes')
            ],
            [
                'title' => 'Sincronização Bancária',
                'path' => '/conexoes-bancarias',
                'active' => $isActive('/conexoes-bancarias')
            ],
            [
                'title' => 'Transações Pendentes',
                'path' => '/transacoes-pendentes',
                'active' => $isActive('/transacoes-pendentes')
            ],
            [
                'title' => 'Extrato Bancário (API)',
                'path' => '/extrato-api',
                'active' => $isActive('/extrato-api')
            ],
        ]
    ],
    [
        'title' => 'API Tokens',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>',
        'path' => '/api-tokens',
        'active' => $isActive('/api-tokens')
    ],
    [
        'title' => 'Documentação da API',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>',
        'path' => '/api/docs',
        'active' => $isActive('/api/docs')
    ],
    [
        'title' => 'Logs do Sistema',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'path' => '/sistema/registros',
        'active' => $isActive('/sistema/registros')
    ],
    [
        'title' => 'Configurações',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
        'path' => '/configuracoes',
        'active' => $isActive('/configuracoes')
    ],
];
?>

<!-- Sidebar -->
<aside 
    id="sidebar"
    x-data="{ openMenus: {} }"
    x-bind:class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-700 transform transition-transform duration-300 ease-in-out flex flex-col"
>
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-slate-700 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                    Financeiro
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Empresarial</div>
            </div>
        </div>
        <!-- Botão fechar mobile -->
        <button 
            @click="$store.sidebar.toggle()"
            class="lg:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 min-h-0">
        <ul class="space-y-1">
            <?php foreach ($menuItems as $index => $item): ?>
                <li>
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Item com submenu -->
                        <div x-data="{ open: <?= array_reduce($item['submenu'], function($carry, $sub) { return $carry || $sub['active']; }, false) ? 'true' : 'false' ?> }">
                            <button 
                                @click="open = !open"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0"><?= $item['icon'] ?></span>
                                    <span><?= htmlspecialchars($item['title']) ?></span>
                                </div>
                                <svg 
                                    class="w-4 h-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Submenu -->
                            <ul 
                                x-show="open" 
                                x-collapse
                                class="mt-1 space-y-1 pl-4"
                            >
                                <?php foreach ($item['submenu'] as $subitem): ?>
                                    <li>
                                        <a 
                                            href="<?= htmlspecialchars($basePath . $subitem['path']) ?>"
                                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all duration-200 <?= $subitem['active'] 
                                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md font-medium' 
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:text-gray-900 dark:hover:text-gray-200' 
                                            ?>"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full <?= $subitem['active'] ? 'bg-white' : 'bg-gray-400 dark:bg-gray-600' ?>"></span>
                                            <span><?= htmlspecialchars($subitem['title']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Item simples -->
                        <a 
                            href="<?= htmlspecialchars($basePath . $item['path']) ?>"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 <?= $item['active'] 
                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg' 
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800' 
                            ?>"
                        >
                            <span class="flex-shrink-0"><?= $item['icon'] ?></span>
                            <span><?= htmlspecialchars($item['title']) ?></span>
                            <?php if ($item['active']): ?>
                                <span class="ml-auto w-2 h-2 bg-white rounded-full"></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Footer Sidebar -->
    <div class="border-t border-gray-200 dark:border-slate-700 p-4 space-y-2 flex-shrink-0">
        <a href="<?= $basePath ?>/minha-conta" 
           class="flex items-center gap-3 px-2 py-2 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors <?= $isActive('/minha-conta') ? 'bg-blue-50 dark:bg-blue-900/20' : '' ?>">
            <?php 
            $avatar = $_SESSION['usuario_avatar'] ?? null;
            if ($avatar): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" 
                     alt="Avatar" 
                     class="w-10 h-10 rounded-full object-cover border-2 border-blue-500 dark:border-blue-400">
            <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                    <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    <?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</aside>

<!-- Overlay Mobile -->
<div 
    id="sidebar-overlay"
    x-show="$store.sidebar.open"
    @click="$store.sidebar.toggle()"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
    style="display: none;"
></div>

<!-- Botão Toggle Mobile -->
<button 
    @click="$store.sidebar.toggle()"
    class="fixed top-4 left-4 z-40 lg:hidden p-2 rounded-lg bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 shadow-lg border border-gray-200 dark:border-slate-700"
    aria-label="Toggle menu"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<style>
    /* Scrollbar customizada para o sidebar */
    #sidebar nav::-webkit-scrollbar {
        width: 6px;
    }
    
    #sidebar nav::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #sidebar nav::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.5);
        border-radius: 3px;
    }
    
    #sidebar nav::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.7);
    }
    
    .dark #sidebar nav::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.5);
    }
    
    .dark #sidebar nav::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.7);
    }
    
    /* Firefox */
    #sidebar nav {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    }
    
    .dark #sidebar nav {
        scrollbar-color: rgba(100, 116, 139, 0.5) transparent;
    }
</style>

<script>
    // Inicializa store do Alpine para controlar sidebar
    document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
            open: window.innerWidth >= 1024,
            toggle() {
                this.open = !this.open;
            }
        });
    });
</script>
