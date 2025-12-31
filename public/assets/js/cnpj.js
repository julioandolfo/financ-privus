/**
 * Integração com API ReceitaWS para busca automática de dados de CNPJ
 * Suporta múltiplos formulários na mesma página
 */

(function() {
    'use strict';

    /**
     * Busca dados do CNPJ usando API ReceitaWS
     * @param {string} cnpj - CNPJ sem formatação (apenas números)
     * @returns {Promise<Object>} Dados da empresa
     */
    async function buscarCNPJ(cnpj) {
        // Remove formatação do CNPJ
        cnpj = cnpj.replace(/\D/g, '');
        
        // Valida se tem 14 dígitos
        if (cnpj.length !== 14) {
            throw new Error('CNPJ deve conter 14 dígitos');
        }
        
        try {
            // API ReceitaWS (gratuita, sem autenticação)
            const response = await fetch(`https://www.receitaws.com.br/v1/${cnpj}`);
            
            if (!response.ok) {
                throw new Error('Erro ao consultar CNPJ');
            }
            
            const data = await response.json();
            
            if (data.status === 'ERROR' || data.status === 'ERROR') {
                throw new Error(data.message || 'CNPJ não encontrado ou inválido');
            }
            
            // Retorna dados formatados
            return {
                razao_social: data.nome || '',
                nome_fantasia: data.fantasia || '',
                telefone: data.telefone || '',
                email: data.email || '',
                situacao: data.situacao || '',
                logradouro: data.logradouro || '',
                numero: data.numero || '',
                complemento: data.complemento || '',
                bairro: data.bairro || '',
                cidade: data.municipio || '',
                estado: data.uf || '',
                cep: data.cep ? data.cep.replace(/\D/g, '') : '',
                inscricao_estadual: data.inscricao_estadual || ''
            };
        } catch (error) {
            throw new Error('Erro ao buscar CNPJ: ' + error.message);
        }
    }

    /**
     * Preenche campos do formulário com os dados retornados
     * @param {Object} dados - Dados da empresa
     * @param {HTMLElement} cnpjInput - Campo de CNPJ que disparou a busca
     */
    function preencherDados(dados, cnpjInput) {
        // Detecta o tipo de formulário baseado nos campos disponíveis
        const form = cnpjInput.closest('form');
        
        // Campos comuns para Empresas
        const campoRazaoSocial = form.querySelector('[name="razao_social"]');
        const campoNomeFantasia = form.querySelector('[name="nome_fantasia"]');
        const campoTelefone = form.querySelector('[name="telefone"]');
        const campoEmail = form.querySelector('[name="email"]');
        const campoInscricaoEstadual = form.querySelector('[name="inscricao_estadual"]');
        
        // Campos para Fornecedores/Clientes (nome_razao_social)
        const campoNomeRazaoSocial = form.querySelector('[name="nome_razao_social"]');
        
        // Campos de endereço - formato direto (empresas)
        const campoCep = form.querySelector('[name="cep"]');
        const campoLogradouro = form.querySelector('[name="logradouro"]');
        const campoNumero = form.querySelector('[name="numero"]');
        const campoComplemento = form.querySelector('[name="complemento"]');
        const campoBairro = form.querySelector('[name="bairro"]');
        const campoCidade = form.querySelector('[name="cidade"]');
        const campoEstado = form.querySelector('[name="estado"]');
        
        // Campos de endereço - formato com prefixo (fornecedores/clientes)
        const campoCepPrefixo = form.querySelector('[name="endereco[cep]"]');
        const campoLogradouroPrefixo = form.querySelector('[name="endereco[logradouro]"]');
        const campoNumeroPrefixo = form.querySelector('[name="endereco[numero]"]');
        const campoComplementoPrefixo = form.querySelector('[name="endereco[complemento]"]');
        const campoBairroPrefixo = form.querySelector('[name="endereco[bairro]"]');
        const campoCidadePrefixo = form.querySelector('[name="endereco[cidade]"]');
        const campoEstadoPrefixo = form.querySelector('[name="endereco[estado]"]');
        
        // Preenche Razão Social / Nome Razão Social
        if (campoRazaoSocial && dados.razao_social) {
            campoRazaoSocial.value = dados.razao_social;
            adicionarFeedbackSucesso(campoRazaoSocial);
        }
        
        if (campoNomeRazaoSocial && dados.razao_social) {
            campoNomeRazaoSocial.value = dados.razao_social;
            adicionarFeedbackSucesso(campoNomeRazaoSocial);
        }
        
        // Preenche Nome Fantasia
        if (campoNomeFantasia && dados.nome_fantasia) {
            campoNomeFantasia.value = dados.nome_fantasia;
            adicionarFeedbackSucesso(campoNomeFantasia);
        }
        
        // Preenche Telefone
        if (campoTelefone && dados.telefone) {
            campoTelefone.value = dados.telefone;
            // Aplica máscara se disponível
            if (window.maskManager && campoTelefone.hasAttribute('data-mask')) {
                campoTelefone.dispatchEvent(new Event('input'));
            }
            adicionarFeedbackSucesso(campoTelefone);
        }
        
        // Preenche Email
        if (campoEmail && dados.email) {
            campoEmail.value = dados.email;
            adicionarFeedbackSucesso(campoEmail);
        }
        
        // Preenche Inscrição Estadual
        if (campoInscricaoEstadual && dados.inscricao_estadual) {
            campoInscricaoEstadual.value = dados.inscricao_estadual;
            adicionarFeedbackSucesso(campoInscricaoEstadual);
        }
        
        // Preenche endereço - formato direto (empresas)
        if (campoCep && dados.cep) {
            campoCep.value = dados.cep;
            adicionarFeedbackSucesso(campoCep);
        }
        
        if (campoLogradouro && dados.logradouro) {
            campoLogradouro.value = dados.logradouro;
            adicionarFeedbackSucesso(campoLogradouro);
        }
        
        if (campoNumero && dados.numero) {
            campoNumero.value = dados.numero;
            adicionarFeedbackSucesso(campoNumero);
        }
        
        if (campoComplemento && dados.complemento) {
            campoComplemento.value = dados.complemento;
            adicionarFeedbackSucesso(campoComplemento);
        }
        
        if (campoBairro && dados.bairro) {
            campoBairro.value = dados.bairro;
            adicionarFeedbackSucesso(campoBairro);
        }
        
        if (campoCidade && dados.cidade) {
            campoCidade.value = dados.cidade;
            adicionarFeedbackSucesso(campoCidade);
        }
        
        if (campoEstado && dados.estado) {
            campoEstado.value = dados.estado;
            adicionarFeedbackSucesso(campoEstado);
        }
        
        // Preenche endereço - formato com prefixo (fornecedores/clientes)
        if (campoCepPrefixo && dados.cep) {
            campoCepPrefixo.value = dados.cep;
            adicionarFeedbackSucesso(campoCepPrefixo);
        }
        
        if (campoLogradouroPrefixo && dados.logradouro) {
            campoLogradouroPrefixo.value = dados.logradouro;
            adicionarFeedbackSucesso(campoLogradouroPrefixo);
        }
        
        if (campoNumeroPrefixo && dados.numero) {
            campoNumeroPrefixo.value = dados.numero;
            adicionarFeedbackSucesso(campoNumeroPrefixo);
        }
        
        if (campoComplementoPrefixo && dados.complemento) {
            campoComplementoPrefixo.value = dados.complemento;
            adicionarFeedbackSucesso(campoComplementoPrefixo);
        }
        
        if (campoBairroPrefixo && dados.bairro) {
            campoBairroPrefixo.value = dados.bairro;
            adicionarFeedbackSucesso(campoBairroPrefixo);
        }
        
        if (campoCidadePrefixo && dados.cidade) {
            campoCidadePrefixo.value = dados.cidade;
            adicionarFeedbackSucesso(campoCidadePrefixo);
        }
        
        if (campoEstadoPrefixo && dados.estado) {
            campoEstadoPrefixo.value = dados.estado;
            adicionarFeedbackSucesso(campoEstadoPrefixo);
        }
    }

    /**
     * Adiciona feedback visual de sucesso no campo
     * @param {HTMLElement} campo - Campo que foi preenchido
     */
    function adicionarFeedbackSucesso(campo) {
        campo.classList.remove('border-red-500');
        campo.classList.add('border-green-500');
        
        setTimeout(() => {
            campo.classList.remove('border-green-500');
        }, 2000);
    }

    /**
     * Mostra mensagem de erro
     * @param {HTMLElement} cnpjInput - Campo de CNPJ
     * @param {string} mensagem - Mensagem de erro
     */
    function mostrarErro(cnpjInput, mensagem) {
        // Remove mensagem anterior se existir
        const erroAnterior = cnpjInput.parentElement.querySelector('.cnpj-error');
        if (erroAnterior) {
            erroAnterior.remove();
        }
        
        // Adiciona classe de erro
        cnpjInput.classList.add('border-red-500');
        
        // Cria elemento de erro
        const erroDiv = document.createElement('p');
        erroDiv.className = 'cnpj-error mt-1 text-sm text-red-600 dark:text-red-400';
        erroDiv.textContent = mensagem;
        cnpjInput.parentElement.appendChild(erroDiv);
        
        // Remove erro após 5 segundos
        setTimeout(() => {
            erroDiv.remove();
            cnpjInput.classList.remove('border-red-500');
        }, 5000);
    }

    /**
     * Remove mensagem de erro
     * @param {HTMLElement} cnpjInput - Campo de CNPJ
     */
    function removerErro(cnpjInput) {
        const erroDiv = cnpjInput.parentElement.querySelector('.cnpj-error');
        if (erroDiv) {
            erroDiv.remove();
        }
        cnpjInput.classList.remove('border-red-500');
    }

    /**
     * Mostra indicador de carregamento
     * @param {HTMLElement} cnpjInput - Campo de CNPJ
     */
    function mostrarCarregamento(cnpjInput) {
        // Remove indicador anterior se existir
        const loaderAnterior = cnpjInput.parentElement.querySelector('.cnpj-loader');
        if (loaderAnterior) {
            loaderAnterior.remove();
        }
        
        // Cria indicador de carregamento
        const loader = document.createElement('div');
        loader.className = 'cnpj-loader mt-1 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400';
        loader.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Buscando dados do CNPJ...</span>
        `;
        cnpjInput.parentElement.appendChild(loader);
    }

    /**
     * Remove indicador de carregamento
     * @param {HTMLElement} cnpjInput - Campo de CNPJ
     */
    function removerCarregamento(cnpjInput) {
        const loader = cnpjInput.parentElement.querySelector('.cnpj-loader');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Verifica se o tipo selecionado é Pessoa Jurídica (para Fornecedores/Clientes)
     * @param {HTMLElement} form - Formulário
     * @returns {boolean} True se for pessoa jurídica
     */
    function isPessoaJuridica(form) {
        const tipoInput = form.querySelector('input[name="tipo"]:checked');
        return tipoInput && tipoInput.value === 'juridica';
    }

    /**
     * Inicializa busca de CNPJ em um campo específico
     * @param {HTMLElement} cnpjInput - Campo de CNPJ
     */
    function inicializarBuscaCNPJ(cnpjInput) {
        let timeoutId = null;
        
        // Função para processar busca
        const processarBusca = async () => {
            const cnpj = cnpjInput.value.replace(/\D/g, '');
            
            // Para Fornecedores/Clientes, só busca se for pessoa jurídica
            const form = cnpjInput.closest('form');
            if (form.querySelector('input[name="tipo"]')) {
                if (!isPessoaJuridica(form)) {
                    removerErro(cnpjInput);
                    removerCarregamento(cnpjInput);
                    return;
                }
            }
            
            if (cnpj.length === 14) {
                mostrarCarregamento(cnpjInput);
                
                try {
                    const dados = await buscarCNPJ(cnpj);
                    preencherDados(dados, cnpjInput);
                    removerCarregamento(cnpjInput);
                    
                    // Mostra mensagem de sucesso
                    const sucessoDiv = document.createElement('p');
                    sucessoDiv.className = 'cnpj-success mt-1 text-sm text-green-600 dark:text-green-400';
                    sucessoDiv.textContent = '✓ Dados do CNPJ encontrados!';
                    cnpjInput.parentElement.appendChild(sucessoDiv);
                    
                    setTimeout(() => {
                        sucessoDiv.remove();
                    }, 3000);
                } catch (error) {
                    removerCarregamento(cnpjInput);
                    mostrarErro(cnpjInput, error.message);
                }
            } else if (cnpj.length > 0 && cnpj.length < 14) {
                mostrarErro(cnpjInput, 'CNPJ incompleto');
            }
        };
        
        // Busca quando usuário para de digitar (debounce)
        cnpjInput.addEventListener('input', function() {
            // Limpa timeout anterior
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Remove erros anteriores
            removerErro(cnpjInput);
            removerCarregamento(cnpjInput);
            
            const cnpj = cnpjInput.value.replace(/\D/g, '');
            
            // Aguarda usuário terminar de digitar
            timeoutId = setTimeout(processarBusca, 800);
        });
        
        // Também busca quando campo perde o foco e tem 14 dígitos
        cnpjInput.addEventListener('blur', async function() {
            const cnpj = cnpjInput.value.replace(/\D/g, '');
            
            // Para Fornecedores/Clientes, só busca se for pessoa jurídica
            const form = cnpjInput.closest('form');
            if (form.querySelector('input[name="tipo"]')) {
                if (!isPessoaJuridica(form)) {
                    return;
                }
            }
            
            if (cnpj.length === 14) {
                await processarBusca();
            }
        });
        
        // Para Fornecedores/Clientes, monitora mudança de tipo
        const form = cnpjInput.closest('form');
        const tipoInputs = form.querySelectorAll('input[name="tipo"]');
        if (tipoInputs.length > 0) {
            tipoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Se mudou para pessoa jurídica e já tem CNPJ completo, busca
                    if (isPessoaJuridica(form)) {
                        const cnpj = cnpjInput.value.replace(/\D/g, '');
                        if (cnpj.length === 14) {
                            processarBusca();
                        }
                    }
                });
            });
        }
    }

    /**
     * Inicializa busca de CNPJ em todos os campos com data-cnpj
     */
    function inicializarTodos() {
        // Busca campos com atributo data-cnpj
        const camposCNPJ = document.querySelectorAll('[data-cnpj]');
        
        camposCNPJ.forEach(campo => {
            inicializarBuscaCNPJ(campo);
        });
        
        // Também busca campos com name="cnpj" ou name="cpf_cnpj" ou id="cnpj" ou id="cpf_cnpj"
        const camposAlternativos = document.querySelectorAll(
            'input[name="cnpj"], input[name="cpf_cnpj"], input#cnpj, input#cpf_cnpj'
        );
        
        camposAlternativos.forEach(campo => {
            if (!campo.hasAttribute('data-cnpj')) {
                // Adiciona atributo e inicializa
                campo.setAttribute('data-cnpj', '');
                inicializarBuscaCNPJ(campo);
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
    window.CNPJ = {
        buscar: buscarCNPJ,
        preencher: preencherDados,
        inicializar: inicializarBuscaCNPJ
    };
})();
