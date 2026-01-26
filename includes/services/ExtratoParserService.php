<?php
namespace includes\services;

/**
 * Service para fazer parsing de arquivos de extratos bancários
 * Suporta: OFX, CSV, TXT
 */
class ExtratoParserService
{
    /**
     * Processa arquivo de extrato e retorna itens normalizados
     */
    public static function processar($arquivo)
    {
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        switch ($extensao) {
            case 'ofx':
                return self::processarOFX($arquivo['tmp_name']);
            case 'csv':
                return self::processarCSV($arquivo['tmp_name']);
            case 'txt':
                return self::processarTXT($arquivo['tmp_name']);
            default:
                throw new \Exception("Formato de arquivo não suportado: {$extensao}");
        }
    }
    
    /**
     * Processa arquivo OFX (Open Financial Exchange)
     */
    private static function processarOFX($caminho)
    {
        $conteudo = file_get_contents($caminho);
        
        if (!$conteudo) {
            throw new \Exception("Não foi possível ler o arquivo OFX");
        }
        
        $itens = [];
        
        // Remover cabeçalhos SGML
        $conteudo = preg_replace('/<\?.*?\?>/', '', $conteudo);
        $conteudo = preg_replace('/OFXHEADER:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/DATA:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/VERSION:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/SECURITY:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/ENCODING:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/CHARSET:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/COMPRESSION:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/OLDFILEUID:.*?\n/', '', $conteudo);
        $conteudo = preg_replace('/NEWFILEUID:.*?\n/', '', $conteudo);
        
        // Extrair transações
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $conteudo, $transacoes);
        
        foreach ($transacoes[1] as $transacao) {
            $item = [];
            
            // Tipo de transação
            if (preg_match('/<TRNTYPE>(.*?)</', $transacao, $match)) {
                $trnType = trim($match[1]);
                $item['tipo'] = in_array($trnType, ['CREDIT', 'DEP', 'INT']) ? 'credito' : 'debito';
                $item['tipo_original'] = $trnType;
            }
            
            // Data
            if (preg_match('/<DTPOSTED>(.*?)</', $transacao, $match)) {
                $data = trim($match[1]);
                // Formato OFX: YYYYMMDD ou YYYYMMDDHHMMSS (pode ter timezone)
                $data = preg_replace('/\[.*\]/', '', $data); // Remove timezone
                $ano = substr($data, 0, 4);
                $mes = substr($data, 4, 2);
                $dia = substr($data, 6, 2);
                $item['data'] = "$ano-$mes-$dia";
            }
            
            // Valor
            if (preg_match('/<TRNAMT>(.*?)</', $transacao, $match)) {
                $valor = trim($match[1]);
                $item['valor'] = abs((float)$valor);
                $item['valor_original'] = (float)$valor;
                
                // Se valor original era negativo, é débito
                if ((float)$valor < 0) {
                    $item['tipo'] = 'debito';
                }
            }
            
            // ID da transação
            if (preg_match('/<FITID>(.*?)</', $transacao, $match)) {
                $item['fitid'] = trim($match[1]);
            }
            
            // Número do cheque/documento
            if (preg_match('/<CHECKNUM>(.*?)</', $transacao, $match)) {
                $checknum = trim($match[1]);
                if ($checknum !== '0') {
                    $item['numero_documento'] = $checknum;
                }
            }
            
            // Referência
            if (preg_match('/<REFNUM>(.*?)</', $transacao, $match)) {
                $item['referencia'] = trim($match[1]);
            }
            
            // Nome (geralmente contém o beneficiário/pagador)
            $nome = '';
            if (preg_match('/<NAME>(.*?)</', $transacao, $match)) {
                $nome = trim($match[1]);
                $item['nome'] = $nome;
            }
            
            // Memo (descrição da transação)
            $memo = '';
            if (preg_match('/<MEMO>(.*?)</', $transacao, $match)) {
                $memo = trim($match[1]);
                $item['memo'] = $memo;
            }
            
            // Construir descrição enriquecida
            $descricaoParts = [];
            
            // Primeiro o MEMO (tipo da operação)
            if (!empty($memo)) {
                $descricaoParts[] = $memo;
            }
            
            // Depois o NAME (beneficiário/pagador)
            if (!empty($nome)) {
                // Extrair CNPJ/CPF se presente no nome
                if (preg_match('/(\d{2}\.\d{3}\.\d{3}[\/ ]\d{4}[-]?\d{2})/', $nome, $cnpjMatch)) {
                    $item['cnpj_cpf'] = preg_replace('/[^\d]/', '', $cnpjMatch[1]);
                }
                $descricaoParts[] = $nome;
            }
            
            // Adicionar referência se for relevante (não for "Pix" ou "0")
            if (!empty($item['referencia']) && !in_array($item['referencia'], ['Pix', '0', $item['numero_documento'] ?? ''])) {
                $descricaoParts[] = "Ref: " . $item['referencia'];
            }
            
            // Combinar partes da descrição
            if (!empty($descricaoParts)) {
                $item['descricao'] = implode(' | ', $descricaoParts);
            } else {
                $item['descricao'] = 'Transação sem descrição';
            }
            
            // Guardar descrição curta (só memo) para identificação de padrões
            $item['descricao_curta'] = $memo ?: $nome ?: 'Sem descrição';
            
            // Identificar tipo de pagamento
            $item['metodo_pagamento'] = self::identificarMetodoPagamento($memo, $nome, $item['referencia'] ?? '');
            
            // Validar se tem dados mínimos
            if (!empty($item['data']) && !empty($item['valor'])) {
                if (empty($item['descricao'])) {
                    $item['descricao'] = 'Transação ' . ($item['tipo'] === 'debito' ? 'Débito' : 'Crédito');
                }
                $itens[] = $item;
            }
        }
        
        return $itens;
    }
    
    /**
     * Identifica o método de pagamento baseado na descrição
     */
    private static function identificarMetodoPagamento($memo, $nome, $referencia)
    {
        $texto = strtoupper($memo . ' ' . $nome . ' ' . $referencia);
        
        if (strpos($texto, 'PIX') !== false) {
            return 'PIX';
        }
        if (strpos($texto, 'TED') !== false) {
            return 'TED';
        }
        if (strpos($texto, 'DOC') !== false) {
            return 'DOC';
        }
        if (strpos($texto, 'BOLETO') !== false || strpos($texto, 'TÍTULO') !== false || strpos($texto, 'TIT.COMPE') !== false) {
            return 'Boleto';
        }
        if (strpos($texto, 'DÉBITO AUTOMÁTICO') !== false || strpos($texto, 'DEB.AUT') !== false) {
            return 'Débito Automático';
        }
        if (strpos($texto, 'TARIFA') !== false) {
            return 'Tarifa Bancária';
        }
        if (strpos($texto, 'IOF') !== false) {
            return 'IOF';
        }
        if (strpos($texto, 'JUROS') !== false) {
            return 'Juros';
        }
        if (strpos($texto, 'CHEQUE') !== false) {
            return 'Cheque';
        }
        if (strpos($texto, 'SAQUE') !== false) {
            return 'Saque';
        }
        if (strpos($texto, 'CARTÃO') !== false || strpos($texto, 'CARTAO') !== false) {
            return 'Cartão';
        }
        
        return 'Outros';
    }
    
    /**
     * Processa arquivo CSV
     */
    private static function processarCSV($caminho)
    {
        $itens = [];
        $handle = fopen($caminho, 'r');
        
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo CSV");
        }
        
        $headers = [];
        $linhaAtual = 0;
        
        while (($linha = fgetcsv($handle, 1000, ';')) !== FALSE) {
            $linhaAtual++;
            
            // Primeira linha é o cabeçalho
            if ($linhaAtual === 1) {
                $headers = array_map('strtolower', array_map('trim', $linha));
                continue;
            }
            
            // Mapear dados
            $dados = array_combine($headers, $linha);
            
            // Tentar identificar campos (bancos usam nomes diferentes)
            $item = [];
            
            // Data
            foreach (['data', 'data_lancamento', 'dt_lancamento', 'date'] as $campo) {
                if (isset($dados[$campo])) {
                    $item['data'] = self::normalizarData($dados[$campo]);
                    break;
                }
            }
            
            // Valor
            foreach (['valor', 'valor_lancamento', 'vl_lancamento', 'amount'] as $campo) {
                if (isset($dados[$campo])) {
                    $valor = self::normalizarValor($dados[$campo]);
                    $item['valor'] = abs($valor);
                    break;
                }
            }
            
            // Tipo (crédito/débito)
            if (isset($dados['tipo'])) {
                $tipo = strtolower(trim($dados['tipo']));
                $item['tipo'] = in_array($tipo, ['credito', 'crédito', 'c', 'credit']) ? 'credito' : 'debito';
            } elseif (isset($item['valor'])) {
                // Se não tem tipo, tentar identificar por sinais
                foreach (['valor', 'valor_lancamento', 'vl_lancamento', 'amount'] as $campo) {
                    if (isset($dados[$campo])) {
                        $valorOriginal = self::normalizarValor($dados[$campo]);
                        $item['tipo'] = $valorOriginal >= 0 ? 'credito' : 'debito';
                        break;
                    }
                }
            }
            
            // Descrição
            foreach (['descricao', 'historico', 'description', 'memo'] as $campo) {
                if (isset($dados[$campo]) && !empty(trim($dados[$campo]))) {
                    $item['descricao'] = trim($dados[$campo]);
                    break;
                }
            }
            
            // Validar se tem dados mínimos
            if (!empty($item['data']) && !empty($item['valor']) && !empty($item['descricao'])) {
                $itens[] = $item;
            }
        }
        
        fclose($handle);
        
        return $itens;
    }
    
    /**
     * Processa arquivo TXT (geralmente formato fixo de bancos)
     */
    private static function processarTXT($caminho)
    {
        $conteudo = file_get_contents($caminho);
        $linhas = explode("\n", $conteudo);
        
        $itens = [];
        
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            
            if (empty($linha)) {
                continue;
            }
            
            // Tentar identificar padrão: data + descrição + valor
            // Exemplo: "15/12/2024    PAGAMENTO PIX    R$ 150,00"
            
            // Regex flexível para capturar data, descrição e valor
            if (preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{4})\s+(.+?)\s+([\+\-]?\s*R?\$?\s*[\d\.,]+)/', $linha, $matches)) {
                $data = self::normalizarData($matches[1]);
                $descricao = trim($matches[2]);
                $valor = self::normalizarValor($matches[3]);
                
                $itens[] = [
                    'data' => $data,
                    'descricao' => $descricao,
                    'valor' => abs($valor),
                    'tipo' => $valor >= 0 ? 'credito' : 'debito'
                ];
            }
        }
        
        return $itens;
    }
    
    /**
     * Normaliza data para formato Y-m-d
     */
    private static function normalizarData($data)
    {
        // Remover caracteres extras
        $data = trim($data);
        
        // Tentar vários formatos
        $formatos = [
            'd/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d',
            'd/m/y', 'd-m-y', 'y-m-d', 'y/m/d',
            'dmY', 'Ymd'
        ];
        
        foreach ($formatos as $formato) {
            $dateObj = \DateTime::createFromFormat($formato, $data);
            if ($dateObj !== false) {
                return $dateObj->format('Y-m-d');
            }
        }
        
        // Se não conseguiu, retorna a data atual
        return date('Y-m-d');
    }
    
    /**
     * Normaliza valor para float
     */
    private static function normalizarValor($valor)
    {
        // Remover símbolos
        $valor = str_replace(['R$', '$', ' '], '', $valor);
        
        // Trocar vírgula por ponto
        $valor = str_replace(',', '.', $valor);
        
        // Remover pontos de milhares (se houver mais de um ponto)
        $pontosCount = substr_count($valor, '.');
        if ($pontosCount > 1) {
            $valor = str_replace('.', '', $valor);
            $valor = preg_replace('/(\d+)\.(\d{2})$/', '$1.$2', $valor);
        }
        
        return (float)$valor;
    }
    
    /**
     * Detecta o saldo final do extrato (se disponível)
     */
    public static function extrairSaldoFinal($arquivo)
    {
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if ($extensao === 'ofx') {
            $conteudo = file_get_contents($arquivo['tmp_name']);
            
            // Procurar tag de saldo
            if (preg_match('/<BALAMT>(.*?)</', $conteudo, $match)) {
                return abs((float)trim($match[1]));
            }
        }
        
        return null;
    }
}
