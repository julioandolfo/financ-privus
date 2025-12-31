<?php
/**
 * Funções de formatação de dados para exibição
 */

/**
 * Formata valor monetário para exibição (R$ 1.234,56)
 * 
 * @param float $valor Valor a formatar
 * @param bool $simbolo Se deve incluir símbolo R$
 * @return string
 */
function formatarMoeda($valor, $simbolo = true) {
    $formatado = number_format($valor, 2, ',', '.');
    return $simbolo ? "R$ {$formatado}" : $formatado;
}

/**
 * Formata CPF (123.456.789-00)
 * 
 * @param string $cpf CPF a formatar
 * @return string
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return $cpf;
    }
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Formata CNPJ (12.345.678/0001-00)
 * 
 * @param string $cnpj CNPJ a formatar
 * @return string
 */
function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) {
        return $cnpj;
    }
    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

/**
 * Formata telefone ((11) 98765-4321 ou (11) 3456-7890)
 * 
 * @param string $telefone Telefone a formatar
 * @return string
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    $tamanho = strlen($telefone);
    
    if ($tamanho == 11) {
        // Celular: (11) 98765-4321
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
    } elseif ($tamanho == 10) {
        // Fixo: (11) 3456-7890
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
    }
    
    return $telefone;
}

/**
 * Formata CEP (12345-678)
 * 
 * @param string $cep CEP a formatar
 * @return string
 */
function formatarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) != 8) {
        return $cep;
    }
    return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
}

/**
 * Formata data para exibição (dd/mm/yyyy)
 * 
 * @param string $data Data no formato Y-m-d
 * @return string
 */
function formatarData($data) {
    if (empty($data) || $data == '0000-00-00') {
        return '';
    }
    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}

/**
 * Formata data e hora para exibição (dd/mm/yyyy HH:mm)
 * 
 * @param string $dataHora Data/hora no formato Y-m-d H:i:s
 * @return string
 */
function formatarDataHora($dataHora) {
    if (empty($dataHora) || $dataHora == '0000-00-00 00:00:00') {
        return '';
    }
    $timestamp = strtotime($dataHora);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Formata data no formato MySQL (Y-m-d) a partir de dd/mm/yyyy
 * 
 * @param string $data Data no formato dd/mm/yyyy
 * @return string Data no formato Y-m-d
 */
function formatarDataMySQL($data) {
    if (empty($data)) {
        return null;
    }
    
    // Se já está no formato correto, retorna
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return $data;
    }
    
    // Converte de dd/mm/yyyy para Y-m-d
    $partes = explode('/', $data);
    if (count($partes) == 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    
    return $data;
}

/**
 * Formata percentual (12,34%)
 * 
 * @param float $percentual Percentual a formatar
 * @param int $decimais Número de casas decimais
 * @return string
 */
function formatarPercentual($percentual, $decimais = 2) {
    return number_format($percentual, $decimais, ',', '.') . '%';
}

/**
 * Formata número sem casas decimais (1.234)
 * 
 * @param int $numero Número a formatar
 * @return string
 */
function formatarNumero($numero) {
    return number_format($numero, 0, ',', '.');
}

/**
 * Formata número com decimais (1.234,56)
 * 
 * @param float $numero Número a formatar
 * @param int $decimais Número de casas decimais
 * @return string
 */
function formatarDecimal($numero, $decimais = 2) {
    return number_format($numero, $decimais, ',', '.');
}

/**
 * Limpa formatação de valor monetário (R$ 1.234,56 -> 1234.56)
 * 
 * @param string $valor Valor formatado
 * @return float
 */
function limparMoeda($valor) {
    // Remove tudo exceto números, vírgula e ponto
    $valor = preg_replace('/[^0-9,.]/', '', $valor);
    // Substitui vírgula por ponto
    $valor = str_replace(',', '.', $valor);
    // Remove pontos exceto o último (separador decimal)
    $partes = explode('.', $valor);
    if (count($partes) > 2) {
        $valor = implode('', array_slice($partes, 0, -1)) . '.' . end($partes);
    }
    return floatval($valor);
}

/**
 * Trunca texto com reticências
 * 
 * @param string $texto Texto a truncar
 * @param int $limite Limite de caracteres
 * @param string $sufixo Sufixo a adicionar (default: ...)
 * @return string
 */
function truncarTexto($texto, $limite = 100, $sufixo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    return substr($texto, 0, $limite) . $sufixo;
}

/**
 * Formata tamanho de arquivo (bytes para KB, MB, GB)
 * 
 * @param int $bytes Tamanho em bytes
 * @param int $decimais Número de casas decimais
 * @return string
 */
function formatarTamanhoArquivo($bytes, $decimais = 2) {
    $tamanhos = ['B', 'KB', 'MB', 'GB', 'TB'];
    $fator = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimais}f", $bytes / pow(1024, $fator)) . ' ' . $tamanhos[$fator];
}

/**
 * Formata tempo decorrido (há X minutos, há X horas, etc)
 * 
 * @param string $dataHora Data/hora no formato Y-m-d H:i:s
 * @return string
 */
function formatarTempoDecorrido($dataHora) {
    $timestamp = strtotime($dataHora);
    $diferenca = time() - $timestamp;
    
    if ($diferenca < 60) {
        return 'agora mesmo';
    } elseif ($diferenca < 3600) {
        $minutos = floor($diferenca / 60);
        return "há {$minutos} " . ($minutos == 1 ? 'minuto' : 'minutos');
    } elseif ($diferenca < 86400) {
        $horas = floor($diferenca / 3600);
        return "há {$horas} " . ($horas == 1 ? 'hora' : 'horas');
    } elseif ($diferenca < 604800) {
        $dias = floor($diferenca / 86400);
        return "há {$dias} " . ($dias == 1 ? 'dia' : 'dias');
    } else {
        return formatarData($dataHora);
    }
}

/**
 * Formata status em badge HTML
 * 
 * @param string $status Status a formatar
 * @param array $cores Mapa de cores por status
 * @return string HTML do badge
 */
function formatarStatusBadge($status, $cores = []) {
    $coresPadrao = [
        'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        'pago' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'recebido' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'vencido' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        'cancelado' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        'parcial' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        'ativo' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'inativo' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    ];
    
    $cores = array_merge($coresPadrao, $cores);
    $classe = $cores[$status] ?? 'bg-gray-100 text-gray-800';
    $texto = ucfirst($status);
    
    return "<span class=\"px-2 py-1 text-xs font-medium rounded-full {$classe}\">{$texto}</span>";
}

/**
 * Formata nome próprio (primeira letra de cada palavra maiúscula)
 * 
 * @param string $nome Nome a formatar
 * @return string
 */
function formatarNomeProprio($nome) {
    return mb_convert_case(mb_strtolower($nome, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Remove acentos de uma string
 * 
 * @param string $texto Texto a limpar
 * @return string
 */
function removerAcentos($texto) {
    $acentos = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N'
    ];
    
    return strtr($texto, $acentos);
}

/**
 * Gera slug a partir de texto
 * 
 * @param string $texto Texto a converter
 * @return string
 */
function gerarSlug($texto) {
    $slug = removerAcentos($texto);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($slug));
    $slug = trim($slug, '-');
    return $slug;
}
