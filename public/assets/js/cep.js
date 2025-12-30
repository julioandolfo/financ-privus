/**
 * Integração com API ViaCEP para busca automática de endereços
 * Suporta múltiplos formulários na mesma página
 */

(function() {
    'use strict';

    /**
     * Busca endereço pelo CEP usando API ViaCEP
     * @param {string} cep - CEP sem formatação (apenas números)
     * @returns {Promise<Object>} Dados do endereço
     */
    async function buscarCEP(cep) {
        // Remove formatação do CEP
        cep = cep.replace(/\D/g, '');
        
        // Valida se tem 8 dígitos
        if (cep.length !== 8) {
            throw new Error('CEP deve conter 8 dígitos');
        }
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                throw new Error('CEP não encontrado');
            }
            
            return {
                logradouro: data.logradouro || '',
                complemento: data.complemento || '',
                bairro: data.bairro || '',
                cidade: data.localidade || '',
                estado: data.uf || '',
                cep: data.cep || cep
            };
        } catch (error) {
            throw new Error('Erro ao buscar CEP: ' + error.message);
        }
    }

    /**
     * Preenche campos de endereço com os dados retornados
     * @param {Object} endereco - Dados do endereço
     * @param {string} prefix - Prefixo dos campos (ex: 'endereco[' ou '')
     */
    function preencherEndereco(endereco, prefix = 'endereco[') {
        const campos = {
            logradouro: 'logradouro',
            complemento: 'complemento',
            bairro: 'bairro',
            cidade: 'cidade',
            estado: 'estado'
        };
        
        Object.keys(campos).forEach(campo => {
            const campoId = campos[campo];
            
            // Tenta diferentes formatos de nome
            let input = null;
            if (prefix) {
                // Formato com prefixo: endereco[logradouro]
                input = document.querySelector(`[name="${prefix}${campoId}]"]`);
            } else {
                // Formato sem prefixo: logradouro
                input = document.querySelector(`[name="${campoId}"]`);
            }
            
            // Fallback para IDs
            if (!input) {
                input = document.getElementById(`endereco_${campoId}`) || 
                        document.getElementById(campoId);
            }
            
            if (input && endereco[campo]) {
                // Se for select, seleciona a opção
                if (input.tagName === 'SELECT') {
                    input.value = endereco[campo];
                    // Remove readonly/disabled se existir
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.classList.remove('bg-gray-50', 'dark:bg-gray-700/50', 'cursor-not-allowed');
                    input.classList.add('bg-white', 'dark:bg-gray-700');
                } else {
                    input.value = endereco[campo];
                    // Remove readonly se existir
                    if (input.hasAttribute('readonly')) {
                        input.removeAttribute('readonly');
                        input.classList.remove('bg-gray-50', 'dark:bg-gray-700/50', 'cursor-not-allowed');
                        input.classList.add('bg-white', 'dark:bg-gray-700');
                    }
                }
                
                // Adiciona classe de sucesso temporariamente
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
                
                setTimeout(() => {
                    input.classList.remove('border-green-500');
                }, 2000);
            }
        });
    }

    /**
     * Mostra mensagem de erro
     * @param {HTMLElement} cepInput - Campo de CEP
     * @param {string} mensagem - Mensagem de erro
     */
    function mostrarErro(cepInput, mensagem) {
        // Remove mensagem anterior se existir
        const erroAnterior = cepInput.parentElement.querySelector('.cep-error');
        if (erroAnterior) {
            erroAnterior.remove();
        }
        
        // Adiciona classe de erro
        cepInput.classList.add('border-red-500');
        
        // Cria elemento de erro
        const erroDiv = document.createElement('p');
        erroDiv.className = 'cep-error mt-1 text-sm text-red-600 dark:text-red-400';
        erroDiv.textContent = mensagem;
        cepInput.parentElement.appendChild(erroDiv);
        
        // Remove erro após 5 segundos
        setTimeout(() => {
            erroDiv.remove();
            cepInput.classList.remove('border-red-500');
        }, 5000);
    }

    /**
     * Remove mensagem de erro
     * @param {HTMLElement} cepInput - Campo de CEP
     */
    function removerErro(cepInput) {
        const erroDiv = cepInput.parentElement.querySelector('.cep-error');
        if (erroDiv) {
            erroDiv.remove();
        }
        cepInput.classList.remove('border-red-500');
    }

    /**
     * Mostra indicador de carregamento
     * @param {HTMLElement} cepInput - Campo de CEP
     */
    function mostrarCarregamento(cepInput) {
        // Remove indicador anterior se existir
        const loaderAnterior = cepInput.parentElement.querySelector('.cep-loader');
        if (loaderAnterior) {
            loaderAnterior.remove();
        }
        
        // Cria indicador de carregamento
        const loader = document.createElement('div');
        loader.className = 'cep-loader mt-1 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400';
        loader.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Buscando endereço...</span>
        `;
        cepInput.parentElement.appendChild(loader);
    }

    /**
     * Remove indicador de carregamento
     * @param {HTMLElement} cepInput - Campo de CEP
     */
    function removerCarregamento(cepInput) {
        const loader = cepInput.parentElement.querySelector('.cep-loader');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Inicializa busca de CEP em um campo específico
     * @param {HTMLElement} cepInput - Campo de CEP
     * @param {string} prefix - Prefixo dos campos de endereço
     */
    function inicializarBuscaCEP(cepInput, prefix = 'endereco[') {
        let timeoutId = null;
        
        cepInput.addEventListener('input', function() {
            // Limpa timeout anterior
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Remove erros anteriores
            removerErro(cepInput);
            removerCarregamento(cepInput);
            
            const cep = cepInput.value.replace(/\D/g, '');
            
            // Aguarda usuário terminar de digitar (debounce)
            timeoutId = setTimeout(async () => {
                if (cep.length === 8) {
                    mostrarCarregamento(cepInput);
                    
                    try {
                        const endereco = await buscarCEP(cep);
                        preencherEndereco(endereco, prefix);
                        removerCarregamento(cepInput);
                        
                        // Mostra mensagem de sucesso
                        const sucessoDiv = document.createElement('p');
                        sucessoDiv.className = 'cep-success mt-1 text-sm text-green-600 dark:text-green-400';
                        sucessoDiv.textContent = '✓ Endereço encontrado!';
                        cepInput.parentElement.appendChild(sucessoDiv);
                        
                        setTimeout(() => {
                            sucessoDiv.remove();
                        }, 3000);
                    } catch (error) {
                        removerCarregamento(cepInput);
                        mostrarErro(cepInput, error.message);
                    }
                } else if (cep.length > 0 && cep.length < 8) {
                    mostrarErro(cepInput, 'CEP incompleto');
                }
            }, 500); // Aguarda 500ms após parar de digitar
        });
        
        // Também busca quando campo perde o foco e tem 8 dígitos
        cepInput.addEventListener('blur', async function() {
            const cep = cepInput.value.replace(/\D/g, '');
            
            if (cep.length === 8) {
                mostrarCarregamento(cepInput);
                
                try {
                    const endereco = await buscarCEP(cep);
                    preencherEndereco(endereco, prefix);
                    removerCarregamento(cepInput);
                } catch (error) {
                    removerCarregamento(cepInput);
                    mostrarErro(cepInput, error.message);
                }
            }
        });
    }

    /**
     * Inicializa busca de CEP em todos os campos com data-cep
     */
    function inicializarTodos() {
        // Busca campos com atributo data-cep
        const camposCEP = document.querySelectorAll('[data-cep]');
        
        camposCEP.forEach(campo => {
            const prefix = campo.getAttribute('data-cep-prefix') || 'endereco[';
            inicializarBuscaCEP(campo, prefix);
        });
        
        // Também busca campos com name="endereco[cep]" ou name="cep" ou id="cep"
        const camposAlternativos = document.querySelectorAll(
            'input[name*="endereco[cep]"], input[name*="endereco_cep"], input[name="cep"], input#cep, input#endereco_cep'
        );
        
        camposAlternativos.forEach(campo => {
            if (!campo.hasAttribute('data-cep')) {
                // Tenta detectar o prefixo do name
                const name = campo.getAttribute('name') || '';
                let prefix = '';
                
                if (name.includes('[')) {
                    // Formato: endereco[cep] -> prefix = "endereco["
                    prefix = name.substring(0, name.indexOf('[') + 1);
                } else if (name === 'cep') {
                    // Formato direto: cep -> prefix = ""
                    prefix = '';
                } else {
                    // Fallback
                    prefix = 'endereco[';
                }
                
                inicializarBuscaCEP(campo, prefix);
            }
        });
    }

    // Inicializa quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarTodos);
    } else {
        inicializarTodos();
    }

    // Exporta funções para uso global se necessário
    window.CEP = {
        buscar: buscarCEP,
        preencher: preencherEndereco,
        inicializar: inicializarBuscaCEP
    };
})();

