/**
 * Sistema de Máscaras e Validações
 * Suporta: CNPJ, CPF, Telefone, CEP, etc.
 */

class MaskManager {
    constructor() {
        this.init();
    }

    init() {
        // Aplica máscaras quando o DOM estiver pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.applyMasks());
        } else {
            this.applyMasks();
        }
    }

    /**
     * Aplica máscaras em todos os campos com data-mask
     */
    applyMasks() {
        // CNPJ
        document.querySelectorAll('[data-mask="cnpj"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskCNPJ(e));
            input.addEventListener('blur', (e) => this.validateCNPJ(e));
        });

        // CPF
        document.querySelectorAll('[data-mask="cpf"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskCPF(e));
            input.addEventListener('blur', (e) => this.validateCPF(e));
        });

        // Telefone
        document.querySelectorAll('[data-mask="telefone"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskTelefone(e));
        });

        // CEP
        document.querySelectorAll('[data-mask="cep"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskCEP(e));
            input.addEventListener('blur', (e) => this.validateCEP(e));
        });

        // Apenas números
        document.querySelectorAll('[data-mask="number"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskNumber(e));
        });

        // Apenas letras
        document.querySelectorAll('[data-mask="letters"]').forEach(input => {
            input.addEventListener('input', (e) => this.maskLetters(e));
        });
    }

    /**
     * Máscara de CNPJ: 00.000.000/0000-00
     */
    maskCNPJ(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        }
    }

    /**
     * Valida CNPJ
     */
    validateCNPJ(e) {
        const cnpj = e.target.value.replace(/\D/g, '');
        if (cnpj.length === 14 && !this.isValidCNPJ(cnpj)) {
            this.showError(e.target, 'CNPJ inválido');
        } else {
            this.clearError(e.target);
        }
    }

    /**
     * Validação matemática de CNPJ
     */
    isValidCNPJ(cnpj) {
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1+$/.test(cnpj)) return false;

        let length = cnpj.length - 2;
        let numbers = cnpj.substring(0, length);
        let digits = cnpj.substring(length);
        let sum = 0;
        let pos = length - 7;

        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }

        let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(0)) return false;

        length = length + 1;
        numbers = cnpj.substring(0, length);
        sum = 0;
        pos = length - 7;

        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }

        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(1)) return false;

        return true;
    }

    /**
     * Máscara de CPF: 000.000.000-00
     */
    maskCPF(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        }
    }

    /**
     * Valida CPF
     */
    validateCPF(e) {
        const cpf = e.target.value.replace(/\D/g, '');
        if (cpf.length === 11 && !this.isValidCPF(cpf)) {
            this.showError(e.target, 'CPF inválido');
        } else {
            this.clearError(e.target);
        }
    }

    /**
     * Validação matemática de CPF
     */
    isValidCPF(cpf) {
        if (cpf.length !== 11) return false;
        if (/^(\d)\1+$/.test(cpf)) return false;

        let sum = 0;
        let remainder;

        for (let i = 1; i <= 9; i++) {
            sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }

        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(9, 10))) return false;

        sum = 0;
        for (let i = 1; i <= 10; i++) {
            sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }

        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(10, 11))) return false;

        return true;
    }

    /**
     * Máscara de Telefone: (00) 00000-0000 ou (00) 0000-0000
     */
    maskTelefone(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        }
    }

    /**
     * Máscara de CEP: 00000-000
     */
    maskCEP(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 8) {
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        }
    }

    /**
     * Valida CEP (formato básico)
     */
    validateCEP(e) {
        const cep = e.target.value.replace(/\D/g, '');
        if (cep.length === 8) {
            // Opcional: buscar CEP via API
            // this.buscarCEP(cep, e.target);
        }
    }

    /**
     * Apenas números
     */
    maskNumber(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    }

    /**
     * Apenas letras e espaços
     */
    maskLetters(e) {
        e.target.value = e.target.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
    }

    /**
     * Mostra erro no campo
     */
    showError(input, message) {
        input.classList.add('border-red-500', 'dark:border-red-500');
        input.classList.remove('border-gray-300', 'dark:border-gray-600');
        
        // Remove mensagem anterior se existir
        const existingError = input.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Adiciona mensagem de erro
        const errorDiv = document.createElement('p');
        errorDiv.className = 'mt-2 text-sm text-red-600 dark:text-red-400 error-message';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    }

    /**
     * Remove erro do campo
     */
    clearError(input) {
        input.classList.remove('border-red-500', 'dark:border-red-500');
        input.classList.add('border-gray-300', 'dark:border-gray-600');
        
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    /**
     * Valida email
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Valida senha forte
     */
    validatePassword(password) {
        // Mínimo 8 caracteres, pelo menos uma letra maiúscula, uma minúscula e um número
        const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return re.test(password);
    }
}

// Inicializa quando o DOM estiver pronto
(function() {
    window.maskManager = new MaskManager();
})();

