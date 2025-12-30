/**
 * Sistema de Gerenciamento de Temas
 * Suporta: light, dark e system (detecta preferência do sistema)
 */

class ThemeManager {
    constructor() {
        this.theme = this.getStoredTheme() || 'system';
        this.init();
    }

    /**
     * Inicializa o sistema de temas
     */
    init() {
        this.applyTheme();
        this.setupThemeToggle();
        this.watchSystemPreference();
    }

    /**
     * Retorna o tema armazenado no localStorage
     */
    getStoredTheme() {
        return localStorage.getItem('theme') || 'system';
    }

    /**
     * Salva o tema no localStorage
     */
    setStoredTheme(theme) {
        localStorage.setItem('theme', theme);
        this.theme = theme;
    }

    /**
     * Retorna a preferência do sistema (dark ou light)
     */
    getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    /**
     * Retorna o tema efetivo (resolvendo 'system')
     */
    getEffectiveTheme() {
        return this.theme === 'system' ? this.getSystemPreference() : this.theme;
    }

    /**
     * Aplica o tema ao documento
     */
    applyTheme() {
        const effectiveTheme = this.getEffectiveTheme();
        const html = document.documentElement;
        
        // Remove classes anteriores
        html.classList.remove('light', 'dark');
        
        // Adiciona a classe do tema efetivo
        html.classList.add(effectiveTheme);
        
        // Atualiza o ícone do toggle se existir
        this.updateThemeIcon();
    }

    /**
     * Alterna entre os temas
     */
    toggleTheme() {
        const themes = ['light', 'dark', 'system'];
        const currentIndex = themes.indexOf(this.theme);
        const nextIndex = (currentIndex + 1) % themes.length;
        this.setTheme(themes[nextIndex]);
    }

    /**
     * Define um tema específico
     */
    setTheme(theme) {
        if (!['light', 'dark', 'system'].includes(theme)) {
            console.error('Tema inválido:', theme);
            return;
        }
        
        this.setStoredTheme(theme);
        this.applyTheme();
        
        // Dispara evento customizado
        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    }

    /**
     * Atualiza o ícone do botão de alternância
     */
    updateThemeIcon() {
        const icons = {
            light: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>`,
            dark: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>`,
            system: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>`
        };

        const buttons = document.querySelectorAll('[data-theme-toggle]');
        buttons.forEach(button => {
            const iconContainer = button.querySelector('.theme-icon');
            if (iconContainer) {
                iconContainer.innerHTML = icons[this.theme] || icons.system;
            }
            button.setAttribute('aria-label', `Tema: ${this.getThemeLabel(this.theme)}`);
        });

        // Atualiza labels de seleção
        const selects = document.querySelectorAll('[data-theme-select]');
        selects.forEach(select => {
            select.value = this.theme;
        });
    }

    /**
     * Retorna o label do tema
     */
    getThemeLabel(theme) {
        const labels = {
            light: 'Claro',
            dark: 'Escuro',
            system: 'Sistema'
        };
        return labels[theme] || theme;
    }

    /**
     * Configura o botão de alternância de tema
     */
    setupThemeToggle() {
        // Botões de toggle (alterna entre os 3 temas)
        document.querySelectorAll('[data-theme-toggle]').forEach(button => {
            button.addEventListener('click', () => this.toggleTheme());
        });

        // Selects de seleção
        document.querySelectorAll('[data-theme-select]').forEach(select => {
            select.addEventListener('change', (e) => this.setTheme(e.target.value));
        });

        // Itens do dropdown (se existir)
        document.querySelectorAll('[data-theme-select-item]').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const theme = e.currentTarget.getAttribute('data-theme-select-item');
                this.setTheme(theme);
                // Fecha dropdown usando Alpine.js se disponível
                const dropdown = e.currentTarget.closest('[x-data]');
                if (dropdown) {
                    // Tenta fechar via Alpine.js
                    setTimeout(() => {
                        try {
                            if (window.Alpine && Alpine.$data) {
                                const alpineData = Alpine.$data(dropdown);
                                if (alpineData && typeof alpineData.open !== 'undefined') {
                                    alpineData.open = false;
                                }
                            }
                        } catch (e) {
                            // Alpine pode não estar disponível ainda
                        }
                    }, 100);
                }
            });
        });
    }

    /**
     * Observa mudanças na preferência do sistema
     */
    watchSystemPreference() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', () => {
            if (this.theme === 'system') {
                this.applyTheme();
            }
        });
    }
}

// Inicializa quando o DOM estiver pronto
(function() {
    function initTheme() {
        window.themeManager = new ThemeManager();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
