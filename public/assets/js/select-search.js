/**
 * Script para inicialização de selects com busca (Tom Select)
 * Adicione a classe "select-search" a qualquer select para ativar
 */

// Armazena instâncias do Tom Select para gerenciamento
window.selectSearchInstances = {};

/**
 * Inicializa Tom Select em um elemento
 * @param {HTMLElement} element - Elemento select para inicializar
 * @param {Object} options - Opções customizadas (opcional)
 * @returns {TomSelect} Instância do Tom Select
 */
function initSelectSearch(element, options = {}) {
    // Se já existe uma instância, destrua primeiro
    if (element.tomselect) {
        element.tomselect.destroy();
    }
    
    const defaultOptions = {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        placeholder: element.getAttribute('data-placeholder') || 'Selecione...',
        allowEmptyOption: true,
        maxOptions: null, // Mostra todas as opções
        render: {
            option: function(data, escape) {
                return '<div class="option">' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div class="item">' + escape(data.text) + '</div>';
            },
            no_results: function(data, escape) {
                return '<div class="no-results">Nenhum resultado encontrado para "' + escape(data.input) + '"</div>';
            }
        },
        onInitialize: function() {
            // Adiciona classe para identificar que foi inicializado
            this.wrapper.classList.add('select-search-initialized');
        },
        onChange: function(value) {
            // Sincroniza o valor de volta para o select original
            // Isso garante que FormData capture o valor correto
            const originalSelect = this.$input;
            if (originalSelect) {
                originalSelect.value = value || '';
                // Dispara evento change para listeners
                originalSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    };
    
    // Merge opções
    const finalOptions = { ...defaultOptions, ...options };
    
    // Cria instância
    const instance = new TomSelect(element, finalOptions);
    
    // Armazena referência
    const id = element.id || element.name || Math.random().toString(36).substr(2, 9);
    window.selectSearchInstances[id] = instance;
    
    return instance;
}

/**
 * Inicializa todos os selects com classe "select-search" na página
 */
function initAllSelectSearch() {
    document.querySelectorAll('select.select-search:not(.tomselected)').forEach(function(select) {
        initSelectSearch(select);
    });
}

/**
 * Reinicializa um select específico (útil após carregar opções via AJAX)
 * @param {string|HTMLElement} selector - ID do elemento ou o próprio elemento
 * @param {Array} options - Array de opções [{value: '', text: ''}]
 * @param {string} selectedValue - Valor a ser selecionado (opcional)
 */
function refreshSelectSearch(selector, options = null, selectedValue = null) {
    const element = typeof selector === 'string' ? document.getElementById(selector) : selector;
    
    if (!element) {
        console.warn('Select não encontrado:', selector);
        return;
    }
    
    // Se tem instância Tom Select
    if (element.tomselect) {
        const instance = element.tomselect;
        
        // Se novas opções foram fornecidas
        if (options !== null) {
            // Limpa opções existentes
            instance.clear();
            instance.clearOptions();
            
            // Adiciona novas opções
            options.forEach(function(opt) {
                instance.addOption({
                    value: opt.value,
                    text: opt.text
                });
            });
            
            // Atualiza a lista
            instance.refreshOptions(false);
        }
        
        // Seleciona valor se fornecido
        if (selectedValue !== null) {
            instance.setValue(selectedValue, true);
        }
    } else {
        // Se não tem Tom Select, verifica se deve inicializar
        if (element.classList.contains('select-search')) {
            // Atualiza opções HTML primeiro
            if (options !== null) {
                element.innerHTML = options.map(function(opt) {
                    return '<option value="' + opt.value + '">' + opt.text + '</option>';
                }).join('');
            }
            
            // Inicializa
            const instance = initSelectSearch(element);
            
            // Seleciona valor
            if (selectedValue !== null) {
                instance.setValue(selectedValue, true);
            }
        }
    }
}

/**
 * Destrói instância do Tom Select de um elemento
 * @param {string|HTMLElement} selector - ID do elemento ou o próprio elemento
 */
function destroySelectSearch(selector) {
    const element = typeof selector === 'string' ? document.getElementById(selector) : selector;
    
    if (element && element.tomselect) {
        element.tomselect.destroy();
    }
}

/**
 * Obtém a instância do Tom Select de um elemento
 * @param {string|HTMLElement} selector - ID do elemento ou o próprio elemento
 * @returns {TomSelect|null} Instância ou null
 */
function getSelectSearchInstance(selector) {
    const element = typeof selector === 'string' ? document.getElementById(selector) : selector;
    return element ? element.tomselect : null;
}

// Inicializa quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    initAllSelectSearch();
});

// Re-inicializa quando Alpine.js terminar de processar (para componentes dinâmicos)
document.addEventListener('alpine:initialized', function() {
    setTimeout(initAllSelectSearch, 100);
});
