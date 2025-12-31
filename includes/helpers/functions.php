<?php
/**
 * Funções auxiliares gerais do sistema
 */

/**
 * Redireciona para uma URL
 * 
 * @param string $url URL de destino
 * @param int $statusCode Código HTTP (default: 302)
 * @return void
 */
function redirecionar($url, $statusCode = 302) {
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Retorna a URL base do sistema
 * 
 * @param string $path Caminho adicional
 * @return string
 */
function urlBase($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host;
    
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Retorna o caminho para assets
 * 
 * @param string $asset Caminho do asset
 * @return string
 */
function asset($asset) {
    return urlBase('assets/' . ltrim($asset, '/'));
}

/**
 * Imprime variável de forma formatada (debug)
 * 
 * @param mixed $var Variável a imprimir
 * @param bool $die Se deve parar execução
 * @return void
 */
function dd($var, $die = true) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Retorna valor de variável ou default se não existir
 * 
 * @param mixed $var Variável a verificar
 * @param mixed $default Valor default
 * @return mixed
 */
function valor($var, $default = null) {
    return $var ?? $default;
}

/**
 * Verifica se usuário está autenticado
 * 
 * @return bool
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Retorna ID do usuário logado
 * 
 * @return int|null
 */
function usuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna dados do usuário logado
 * 
 * @param string|null $campo Campo específico a retornar
 * @return mixed
 */
function usuario($campo = null) {
    if (!estaAutenticado()) {
        return null;
    }
    
    if ($campo) {
        return $_SESSION['usuario'][$campo] ?? null;
    }
    
    return $_SESSION['usuario'] ?? null;
}

/**
 * Retorna ID da empresa ativa na sessão
 * 
 * @return int|null
 */
function empresaId() {
    return $_SESSION['empresa_id'] ?? null;
}

/**
 * Define empresa ativa na sessão
 * 
 * @param int $empresaId ID da empresa
 * @return void
 */
function setEmpresaAtiva($empresaId) {
    $_SESSION['empresa_id'] = $empresaId;
}

/**
 * Retorna empresas selecionadas para consolidação
 * 
 * @return array
 */
function empresasConsolidacao() {
    return $_SESSION['empresas_consolidacao'] ?? [];
}

/**
 * Define empresas para consolidação
 * 
 * @param array $empresasIds Array de IDs das empresas
 * @return void
 */
function setEmpresasConsolidacao($empresasIds) {
    $_SESSION['empresas_consolidacao'] = $empresasIds;
}

/**
 * Verifica se está em modo consolidação
 * 
 * @return bool
 */
function modoConsolidacao() {
    return !empty($_SESSION['empresas_consolidacao']) && count($_SESSION['empresas_consolidacao']) >= 2;
}

/**
 * Limpa modo consolidação
 * 
 * @return void
 */
function limparConsolidacao() {
    unset($_SESSION['empresas_consolidacao']);
}

/**
 * Gera token CSRF
 * 
 * @return string
 */
function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 * 
 * @param string $token Token a validar
 * @return bool
 */
function validarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Adiciona mensagem flash
 * 
 * @param string $tipo Tipo da mensagem (success, error, warning, info)
 * @param string $mensagem Mensagem
 * @return void
 */
function flash($tipo, $mensagem) {
    $_SESSION[$tipo] = $mensagem;
}

/**
 * Retorna e limpa mensagem flash
 * 
 * @param string $tipo Tipo da mensagem
 * @return string|null
 */
function getFlash($tipo) {
    $mensagem = $_SESSION[$tipo] ?? null;
    unset($_SESSION[$tipo]);
    return $mensagem;
}

/**
 * Gera array de anos para select
 * 
 * @param int $anoInicio Ano inicial
 * @param int $anoFim Ano final (default: ano atual + 5)
 * @return array
 */
function gerarAnosSelect($anoInicio = null, $anoFim = null) {
    $anoInicio = $anoInicio ?? (date('Y') - 10);
    $anoFim = $anoFim ?? (date('Y') + 5);
    
    $anos = [];
    for ($ano = $anoFim; $ano >= $anoInicio; $ano--) {
        $anos[$ano] = $ano;
    }
    
    return $anos;
}

/**
 * Gera array de meses para select
 * 
 * @return array
 */
function gerarMesesSelect() {
    return [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];
}

/**
 * Retorna primeiro dia do mês
 * 
 * @param int|null $mes Mês (1-12)
 * @param int|null $ano Ano
 * @return string Data no formato Y-m-d
 */
function primeiroDiaMes($mes = null, $ano = null) {
    $mes = $mes ?? date('m');
    $ano = $ano ?? date('Y');
    return date('Y-m-01', strtotime("{$ano}-{$mes}-01"));
}

/**
 * Retorna último dia do mês
 * 
 * @param int|null $mes Mês (1-12)
 * @param int|null $ano Ano
 * @return string Data no formato Y-m-d
 */
function ultimoDiaMes($mes = null, $ano = null) {
    $mes = $mes ?? date('m');
    $ano = $ano ?? date('Y');
    return date('Y-m-t', strtotime("{$ano}-{$mes}-01"));
}

/**
 * Calcula diferença entre datas em dias
 * 
 * @param string $data1 Data inicial
 * @param string $data2 Data final
 * @return int Dias de diferença
 */
function diferencaDias($data1, $data2) {
    $timestamp1 = strtotime($data1);
    $timestamp2 = strtotime($data2);
    $diferenca = abs($timestamp2 - $timestamp1);
    return floor($diferenca / 86400);
}

/**
 * Verifica se data está vencida
 * 
 * @param string $dataVencimento Data de vencimento
 * @return bool
 */
function estaVencido($dataVencimento) {
    return strtotime($dataVencimento) < strtotime(date('Y-m-d'));
}

/**
 * Carrega arquivo de helper
 * 
 * @param string $helper Nome do helper (sem .php)
 * @return void
 */
function carregarHelper($helper) {
    $caminho = __DIR__ . "/{$helper}.php";
    if (file_exists($caminho)) {
        require_once $caminho;
    }
}

/**
 * Log de erro personalizado
 * 
 * @param string $mensagem Mensagem de erro
 * @param string $tipo Tipo de erro
 * @return void
 */
function logErro($mensagem, $tipo = 'ERROR') {
    $data = date('Y-m-d H:i:s');
    $log = "[{$data}] [{$tipo}] {$mensagem}" . PHP_EOL;
    error_log($log, 3, __DIR__ . '/../../storage/logs/error.log');
}

/**
 * Retorna extensão de arquivo
 * 
 * @param string $nomeArquivo Nome do arquivo
 * @return string
 */
function obterExtensao($nomeArquivo) {
    return strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
}

/**
 * Verifica se extensão é permitida
 * 
 * @param string $extensao Extensão do arquivo
 * @param array $extensoesPermitidas Array de extensões permitidas
 * @return bool
 */
function extensaoPermitida($extensao, $extensoesPermitidas) {
    return in_array(strtolower($extensao), $extensoesPermitidas);
}

/**
 * Gera nome único para arquivo
 * 
 * @param string $nomeOriginal Nome original do arquivo
 * @return string
 */
function gerarNomeArquivoUnico($nomeOriginal) {
    $extensao = obterExtensao($nomeOriginal);
    return uniqid() . '_' . time() . '.' . $extensao;
}
