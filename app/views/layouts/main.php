<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema Financeiro' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    },
                    animation: {
                        'gradient': 'gradient 8s linear infinite',
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-up': 'slideUp 0.5s ease-out',
                    },
                    keyframes: {
                        gradient: {
                            '0%, 100%': {
                                'background-size': '200% 200%',
                                'background-position': 'left center'
                            },
                            '50%': {
                                'background-size': '200% 200%',
                                'background-position': 'right center'
                            }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    <?php
    // Usa caminho absoluto para assets (o .htaccess redireciona /assets/ para public/assets/)
    $assetPath = '/assets/js/theme.js';
    $masksPath = '/assets/js/masks.js';
    ?>
    <script>
        // Define base URL para uso nos scripts
        const BASE_URL = '<?= $this->baseUrl() ?>';
    </script>
    <script>
        // Aplica tema imediatamente antes do conteúdo ser renderizado (evita flash)
        (function() {
            const theme = localStorage.getItem('theme') || 'system';
            const effectiveTheme = theme === 'system' 
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;
            document.documentElement.classList.add(effectiveTheme);
        })();
    </script>
    <script src="<?= htmlspecialchars($assetPath) ?>"></script>
    <script src="<?= htmlspecialchars($masksPath) ?>"></script>
    <script src="/assets/js/cep.js"></script>
    <script src="/assets/js/cnpj.js"></script>
    <script src="/assets/js/select-search.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tom Select (alternativa moderna ao Select2) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        /* Alpine.js x-cloak - esconde elementos até Alpine carregar */
        [x-cloak] { 
            display: none !important; 
        }
        
        body { 
            font-family: 'Inter', sans-serif;
        }
        html {
            height: 100%;
            overflow-x: hidden;
        }
        body {
            min-height: 100%;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }
        /* Background fixo */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(to bottom right, rgb(248 250 252), rgb(239 246 255), rgb(238 242 255));
            background-attachment: fixed;
            background-size: cover;
            z-index: -1;
        }
        .dark body::before {
            background-image: linear-gradient(to bottom right, rgb(15 23 42), rgb(30 41 59), rgb(15 23 42));
        }
        /* Garantir que o main ocupe o espaço disponível */
        main {
            flex: 1;
        }
        
        /* Tom Select - Estilos customizados para integração com Tailwind e Dark Mode */
        .ts-wrapper {
            font-family: 'Inter', sans-serif;
        }
        .ts-wrapper .ts-control {
            border-radius: 0.75rem;
            border: 1px solid rgb(209 213 219);
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            background-color: white !important;
            color: rgb(17 24 39) !important;
            min-height: 48px;
            transition: all 0.15s ease;
        }
        .ts-wrapper .ts-control:hover {
            border-color: rgb(156 163 175);
        }
        .ts-wrapper.focus .ts-control {
            border-color: rgb(59 130 246);
            box-shadow: 0 0 0 2px rgb(59 130 246 / 0.2);
            outline: none;
        }
        .ts-wrapper .ts-control input,
        .ts-wrapper .ts-control input[type="text"] {
            color: rgb(17 24 39) !important;
            background: transparent !important;
        }
        .ts-wrapper .ts-control input::placeholder {
            color: rgb(107 114 128) !important;
        }
        .ts-dropdown {
            border-radius: 0.75rem;
            border: 1px solid rgb(209 213 219);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            margin-top: 4px;
            background-color: white !important;
        }
        .ts-dropdown .ts-dropdown-content {
            max-height: 250px;
            padding: 0.25rem;
        }
        .ts-dropdown .option {
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: rgb(55 65 81) !important;
            cursor: pointer;
        }
        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background-color: rgb(239 246 255) !important;
            color: rgb(29 78 216) !important;
        }
        .ts-dropdown .option.selected {
            background-color: rgb(59 130 246) !important;
            color: white !important;
        }
        .ts-wrapper .ts-control .item {
            background: transparent !important;
            color: rgb(17 24 39) !important;
        }
        .ts-dropdown .no-results {
            padding: 0.625rem 0.875rem;
            color: rgb(107 114 128) !important;
            font-size: 0.875rem;
        }
        
        /* Dark Mode para Tom Select */
        .dark .ts-wrapper .ts-control {
            background-color: rgb(55 65 81) !important;
            border-color: rgb(75 85 99);
            color: rgb(243 244 246) !important;
        }
        .dark .ts-wrapper .ts-control:hover {
            border-color: rgb(107 114 128);
        }
        .dark .ts-wrapper.focus .ts-control {
            border-color: rgb(59 130 246);
        }
        .dark .ts-wrapper .ts-control input,
        .dark .ts-wrapper .ts-control input[type="text"] {
            color: rgb(243 244 246) !important;
            background: transparent !important;
        }
        .dark .ts-wrapper .ts-control input::placeholder {
            color: rgb(156 163 175) !important;
        }
        .dark .ts-dropdown {
            background-color: rgb(55 65 81) !important;
            border-color: rgb(75 85 99);
        }
        .dark .ts-dropdown .option {
            color: rgb(229 231 235) !important;
        }
        .dark .ts-dropdown .option:hover,
        .dark .ts-dropdown .option.active {
            background-color: rgb(75 85 99) !important;
            color: rgb(147 197 253) !important;
        }
        .dark .ts-dropdown .option.selected {
            background-color: rgb(59 130 246) !important;
            color: white !important;
        }
        .dark .ts-wrapper .ts-control .item {
            color: rgb(243 244 246) !important;
        }
        .dark .ts-dropdown .no-results {
            color: rgb(156 163 175) !important;
        }
    </style>
</head>
<body class="transition-colors duration-300">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Sidebar -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <?php endif; ?>

    <!-- Navbar Moderna -->
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-slate-700/50 shadow-sm sticky top-0 z-40 transition-colors duration-300 <?= isset($_SESSION['usuario_id']) ? 'lg:ml-64' : '' ?>">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <?php if (!isset($_SESSION['usuario_id'])): ?>
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <a href="/" class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                                Sistema Financeiro
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Empresarial</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                    <!-- Sino de Notificações -->
                    <div class="relative" x-data="notificacoesDropdown()" x-init="init()">
                        <button 
                            @click="toggle()"
                            class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors duration-200 relative"
                            aria-label="Notificações"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <!-- Badge contador -->
                            <span 
                                x-show="naoLidas > 0" 
                                x-text="naoLidas > 99 ? '99+' : naoLidas"
                                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[18px]"
                            ></span>
                        </button>
                        
                        <!-- Dropdown de Notificações -->
                        <div 
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-200 dark:border-slate-700 overflow-hidden z-50"
                            style="display: none;"
                        >
                            <!-- Header -->
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between bg-gray-50 dark:bg-slate-900">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Notificações</h3>
                                <button 
                                    @click="marcarTodasLidas()" 
                                    x-show="naoLidas > 0"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                >
                                    Marcar todas como lidas
                                </button>
                            </div>
                            
                            <!-- Lista de Notificações -->
                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="notificacoes.length === 0">
                                    <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <p class="text-sm">Nenhuma notificação</p>
                                    </div>
                                </template>
                                
                                <template x-for="notif in notificacoes" :key="notif.id">
                                    <div 
                                        :class="notif.lida == 0 ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
                                        class="px-4 py-3 border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer"
                                        @click="abrirNotificacao(notif)"
                                    >
                                        <div class="flex items-start space-x-3">
                                            <div :class="notif.cor_classe" class="p-2 rounded-full flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="notif.icone_classe"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="notif.titulo"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2" x-text="notif.mensagem"></p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="notif.tempo_relativo"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Footer -->
                            <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
                                <a href="/notificacoes" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium flex items-center justify-center">
                                    Ver todas as notificações
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Toggle de Tema com Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            data-theme-toggle
                            class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors duration-200 relative"
                            aria-label="Alternar tema"
                        >
                            <span class="theme-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </span>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div 
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-gray-200 dark:border-slate-700 overflow-hidden z-50"
                            style="display: none;"
                        >
                            <button 
                                data-theme-select-item="light"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center space-x-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span>Claro</span>
                            </button>
                            <button 
                                data-theme-select-item="dark"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center space-x-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <span>Escuro</span>
                            </button>
                            <button 
                                data-theme-select-item="system"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center space-x-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>Sistema</span>
                            </button>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <form method="POST" action="/logout" class="inline">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg">
                                Sair
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                            Login
                        </a>
                        <a href="/login" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            Entrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="<?= isset($_SESSION['usuario_id']) ? 'lg:ml-64' : '' ?> py-8 px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Alertas com animação -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 animate-slide-up bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 dark:border-green-400 text-green-800 dark:text-green-200 p-4 rounded-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($success)): ?>
            <div class="mb-6 animate-slide-up bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 dark:border-green-400 text-green-800 dark:text-green-200 p-4 rounded-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?= htmlspecialchars($success) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 animate-slide-up bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 p-4 rounded-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php elseif (isset($error)): ?>
            <div class="mb-6 animate-slide-up bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 p-4 rounded-lg shadow-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <!-- Footer Moderno -->
    <footer class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-gray-200/50 dark:border-slate-700/50 mt-auto transition-colors duration-300 <?= isset($_SESSION['usuario_id']) ? 'lg:ml-64' : '' ?>">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    &copy; <?= date('Y') ?> Sistema Financeiro Empresarial. Todos os direitos reservados.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Container de Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col space-y-2 max-w-sm"></div>

    <!-- Scripts de Notificações -->
    <script>
    // Sistema de Notificações Dropdown
    function notificacoesDropdown() {
        return {
            open: false,
            notificacoes: [],
            naoLidas: 0,
            pollingInterval: null,
            
            init() {
                this.carregarNotificacoes();
                // Polling a cada 60 segundos
                this.pollingInterval = setInterval(() => {
                    this.atualizarContador();
                }, 60000);
            },
            
            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.carregarNotificacoes();
                }
            },
            
            async carregarNotificacoes() {
                try {
                    const response = await fetch('/notificacoes/dropdown');
                    const data = await response.json();
                    this.notificacoes = data.notificacoes || [];
                    this.naoLidas = data.nao_lidas || 0;
                } catch (error) {
                    console.error('Erro ao carregar notificações:', error);
                }
            },
            
            async atualizarContador() {
                try {
                    const response = await fetch('/notificacoes/contar');
                    const data = await response.json();
                    const novoCount = data.count || 0;
                    
                    // Se tiver novas notificações, mostra toast
                    if (novoCount > this.naoLidas) {
                        this.carregarNotificacoes();
                        window.toastNotify && window.toastNotify('Nova notificação', 'Você tem novas notificações', 'info');
                    }
                    
                    this.naoLidas = novoCount;
                } catch (error) {
                    console.error('Erro ao atualizar contador:', error);
                }
            },
            
            async marcarTodasLidas() {
                try {
                    await fetch('/notificacoes/marcar-todas-lidas', { method: 'POST' });
                    this.naoLidas = 0;
                    this.notificacoes = this.notificacoes.map(n => ({ ...n, lida: 1 }));
                } catch (error) {
                    console.error('Erro ao marcar como lidas:', error);
                }
            },
            
            async abrirNotificacao(notif) {
                // Marca como lida
                if (notif.lida == 0) {
                    await fetch('/notificacoes/' + notif.id + '/lida', { method: 'POST' });
                    this.naoLidas = Math.max(0, this.naoLidas - 1);
                    notif.lida = 1;
                }
                
                // Redireciona se tiver link
                if (notif.link_url) {
                    window.location.href = notif.link_url;
                }
                
                this.open = false;
            }
        }
    }
    
    // Sistema de Toast Notifications
    window.toastNotify = function(titulo, mensagem, tipo = 'info', duracao = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const cores = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500'
        };
        
        const icones = {
            info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
        };
        
        const toast = document.createElement('div');
        toast.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 flex items-start p-4 rounded-xl shadow-2xl ${cores[tipo]} text-white min-w-[300px]`;
        toast.innerHTML = `
            <div class="flex-shrink-0 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icones[tipo]}"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm">${titulo}</p>
                <p class="text-sm opacity-90 mt-0.5">${mensagem}</p>
            </div>
            <button class="flex-shrink-0 ml-3 focus:outline-none hover:opacity-75" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Anima entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });
        
        // Remove após duração
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, duracao);
        
        // Limita quantidade de toasts
        const toasts = container.children;
        while (toasts.length > 3) {
            toasts[0].remove();
        }
    };
    
    // Mostra toast de sessão se existir
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['toast_success'])): ?>
            window.toastNotify('Sucesso', '<?= addslashes($_SESSION['toast_success']) ?>', 'success');
            <?php unset($_SESSION['toast_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['toast_error'])): ?>
            window.toastNotify('Erro', '<?= addslashes($_SESSION['toast_error']) ?>', 'error');
            <?php unset($_SESSION['toast_error']); ?>
        <?php endif; ?>
        
        // Sistema de Web Push Notifications
        <?php if (isset($_SESSION['usuario_id'])): ?>
        initWebPush();
        <?php endif; ?>
    });
    
    // Inicializa Web Push Notifications
    async function initWebPush() {
        // Verifica se o navegador suporta notificações
        if (!('Notification' in window)) {
            console.log('Este navegador não suporta notificações');
            return;
        }
        
        // Verifica se Service Workers são suportados
        if (!('serviceWorker' in navigator)) {
            console.log('Service Workers não são suportados');
            return;
        }
        
        // Se já tem permissão, não faz nada
        if (Notification.permission === 'granted') {
            console.log('Notificações já autorizadas');
            return;
        }
        
        // Se foi negado, não pergunta novamente
        if (Notification.permission === 'denied') {
            console.log('Notificações foram bloqueadas pelo usuário');
            return;
        }
        
        // Aguarda 3 segundos antes de mostrar o prompt (para não ser intrusivo)
        setTimeout(() => {
            mostrarPromptNotificacao();
        }, 3000);
    }
    
    function mostrarPromptNotificacao() {
        // Cria o banner de solicitação customizado
        const banner = document.createElement('div');
        banner.id = 'notification-banner';
        banner.className = 'fixed bottom-20 right-4 z-[9998] max-w-sm bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 animate-slide-in';
        banner.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Ativar Notificações</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Receba alertas sobre vencimentos, pagamentos e outras novidades importantes.</p>
                    <div class="flex space-x-2 mt-3">
                        <button onclick="solicitarPermissaoNotificacao()" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            Ativar
                        </button>
                        <button onclick="fecharBannerNotificacao()" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                            Agora não
                        </button>
                    </div>
                </div>
                <button onclick="fecharBannerNotificacao()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(banner);
        
        // Adiciona animação CSS se não existir
        if (!document.getElementById('notification-banner-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-banner-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                .animate-slide-in { animation: slideIn 0.3s ease-out; }
            `;
            document.head.appendChild(style);
        }
    }
    
    function fecharBannerNotificacao() {
        const banner = document.getElementById('notification-banner');
        if (banner) {
            banner.style.transform = 'translateX(100%)';
            banner.style.opacity = '0';
            banner.style.transition = 'all 0.3s ease-in';
            setTimeout(() => banner.remove(), 300);
        }
        // Salva no localStorage para não mostrar novamente nesta sessão
        localStorage.setItem('notificationBannerDismissed', Date.now());
    }
    
    async function solicitarPermissaoNotificacao() {
        fecharBannerNotificacao();
        
        try {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                window.toastNotify('Notificações ativadas!', 'Você receberá alertas importantes.', 'success');
                
                // Aqui poderia registrar o service worker e a subscription
                // Para implementação completa de Web Push
                console.log('Permissão concedida para notificações');
            } else if (permission === 'denied') {
                window.toastNotify('Notificações bloqueadas', 'Você pode reativar nas configurações do navegador.', 'warning');
            }
        } catch (error) {
            console.error('Erro ao solicitar permissão:', error);
        }
    }
    </script>
</body>
</html>
