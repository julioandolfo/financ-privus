<?php
/**
 * Funções de validação para o sistema
 */

/**
 * Valida CPF
 * 
 * @param string $cpf CPF a validar
 * @return bool
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }
    
    // Calcula e verifica os dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Valida CNPJ
 * 
 * @param string $cnpj CNPJ a validar
 * @return bool
 */
function validarCNPJ($cnpj) {
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    // Verifica se tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cnpj)) {
        return false;
    }
    
    // Calcula e verifica os dígitos verificadores
    $tamanho = strlen($cnpj) - 2;
    $numeros = substr($cnpj, 0, $tamanho);
    $digitos = substr($cnpj, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) {
            $pos = 9;
        }
    }
    
    $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
    if ($resultado != $digitos[0]) {
        return false;
    }
    
    $tamanho = $tamanho + 1;
    $numeros = substr($cnpj, 0, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) {
            $pos = 9;
        }
    }
    
    $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
    if ($resultado != $digitos[1]) {
        return false;
    }
    
    return true;
}

/**
 * Valida email
 * 
 * @param string $email Email a validar
 * @return bool
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida data no formato Y-m-d
 * 
 * @param string $data Data a validar
 * @return bool
 */
function validarData($data) {
    if (empty($data)) {
        return false;
    }
    
    $partes = explode('-', $data);
    if (count($partes) != 3) {
        return false;
    }
    
    return checkdate($partes[1], $partes[2], $partes[0]);
}

/**
 * Valida se data está dentro de um período
 * 
 * @param string $data Data a validar
 * @param string $dataInicio Data inicial do período
 * @param string $dataFim Data final do período
 * @return bool
 */
function validarDataPeriodo($data, $dataInicio, $dataFim) {
    return $data >= $dataInicio && $data <= $dataFim;
}

/**
 * Valida valor monetário
 * 
 * @param mixed $valor Valor a validar
 * @return bool
 */
function validarValor($valor) {
    return is_numeric($valor) && $valor >= 0;
}

/**
 * Valida percentual (0-100)
 * 
 * @param mixed $percentual Percentual a validar
 * @return bool
 */
function validarPercentual($percentual) {
    return is_numeric($percentual) && $percentual >= 0 && $percentual <= 100;
}

/**
 * Valida telefone brasileiro
 * 
 * @param string $telefone Telefone a validar
 * @return bool
 */
function validarTelefone($telefone) {
    // Remove caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se tem 10 ou 11 dígitos (com DDD)
    $tamanho = strlen($telefone);
    return $tamanho == 10 || $tamanho == 11;
}

/**
 * Valida CEP brasileiro
 * 
 * @param string $cep CEP a validar
 * @return bool
 */
function validarCEP($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    // Verifica se tem 8 dígitos
    return strlen($cep) == 8;
}

/**
 * Valida senha forte
 * Mínimo 8 caracteres, pelo menos 1 maiúscula, 1 minúscula, 1 número
 * 
 * @param string $senha Senha a validar
 * @return array ['valida' => bool, 'mensagem' => string]
 */
function validarSenhaForte($senha) {
    $resultado = ['valida' => true, 'mensagem' => ''];
    
    if (strlen($senha) < 8) {
        $resultado['valida'] = false;
        $resultado['mensagem'] = 'A senha deve ter no mínimo 8 caracteres';
        return $resultado;
    }
    
    if (!preg_match('/[A-Z]/', $senha)) {
        $resultado['valida'] = false;
        $resultado['mensagem'] = 'A senha deve conter pelo menos uma letra maiúscula';
        return $resultado;
    }
    
    if (!preg_match('/[a-z]/', $senha)) {
        $resultado['valida'] = false;
        $resultado['mensagem'] = 'A senha deve conter pelo menos uma letra minúscula';
        return $resultado;
    }
    
    if (!preg_match('/[0-9]/', $senha)) {
        $resultado['valida'] = false;
        $resultado['mensagem'] = 'A senha deve conter pelo menos um número';
        return $resultado;
    }
    
    return $resultado;
}

/**
 * Valida se string contém apenas letras
 * 
 * @param string $texto Texto a validar
 * @param bool $permitirEspacos Se deve permitir espaços
 * @return bool
 */
function validarApenasLetras($texto, $permitirEspacos = true) {
    if ($permitirEspacos) {
        return preg_match('/^[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s]+$/', $texto);
    }
    return preg_match('/^[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ]+$/', $texto);
}

/**
 * Valida se string contém apenas números
 * 
 * @param string $texto Texto a validar
 * @return bool
 */
function validarApenasNumeros($texto) {
    return preg_match('/^[0-9]+$/', $texto);
}

/**
 * Valida tamanho mínimo de string
 * 
 * @param string $texto Texto a validar
 * @param int $minimo Tamanho mínimo
 * @return bool
 */
function validarTamanhoMinimo($texto, $minimo) {
    return strlen($texto) >= $minimo;
}

/**
 * Valida tamanho máximo de string
 * 
 * @param string $texto Texto a validar
 * @param int $maximo Tamanho máximo
 * @return bool
 */
function validarTamanhoMaximo($texto, $maximo) {
    return strlen($texto) <= $maximo;
}

/**
 * Valida se valor é único no banco (helper para controllers)
 * 
 * @param object $model Model a consultar
 * @param string $campo Nome do campo
 * @param mixed $valor Valor a verificar
 * @param int|null $excludeId ID a excluir da verificação (para updates)
 * @return bool True se é único
 */
function validarUnico($model, $campo, $valor, $excludeId = null) {
    $metodo = 'findBy' . ucfirst($campo);
    
    if (!method_exists($model, $metodo)) {
        return true; // Se não tem método, assume que é único
    }
    
    $resultado = $model->$metodo($valor);
    
    if (!$resultado) {
        return true; // Não encontrou, é único
    }
    
    if ($excludeId && $resultado['id'] == $excludeId) {
        return true; // É o próprio registro sendo editado
    }
    
    return false; // Encontrou outro registro, não é único
}

/**
 * Sanitiza string removendo caracteres especiais
 * 
 * @param string $texto Texto a sanitizar
 * @return string
 */
function sanitizarTexto($texto) {
    return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida formato de hora (HH:MM)
 * 
 * @param string $hora Hora a validar
 * @return bool
 */
function validarHora($hora) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora);
}

/**
 * Valida se data/hora é futura
 * 
 * @param string $dataHora Data/hora a validar
 * @return bool
 */
function validarDataFutura($dataHora) {
    return strtotime($dataHora) > time();
}

/**
 * Valida se data/hora é passada
 * 
 * @param string $dataHora Data/hora a validar
 * @return bool
 */
function validarDataPassada($dataHora) {
    return strtotime($dataHora) < time();
}
