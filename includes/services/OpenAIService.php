<?php
namespace includes\services;

use App\Models\Configuracao;

/**
 * Service para integração com OpenAI
 */
class OpenAIService
{
    private static $apiKey;
    private static $model;
    private static $temperature;
    private static $maxTokens;
    private static $timeout;
    
    /**
     * Inicializa configurações
     */
    private static function init()
    {
        if (self::$apiKey === null) {
            self::$apiKey = Configuracao::get('api.openai_key', '');
            self::$model = Configuracao::get('api.openai_model', 'gpt-4o');
            self::$temperature = Configuracao::get('api.openai_temperatura', 0.7);
            self::$maxTokens = Configuracao::get('api.openai_max_tokens', 2000);
            self::$timeout = Configuracao::get('api.openai_timeout', 30);
        }
    }
    
    /**
     * Verifica se a API está configurada
     */
    public static function isConfigured()
    {
        self::init();
        return !empty(self::$apiKey) && strlen(self::$apiKey) > 10;
    }
    
    /**
     * Analisa conciliação bancária e retorna insights
     */
    public static function analisarConciliacao($dados)
    {
        self::init();
        
        if (!self::isConfigured()) {
            throw new \Exception("API OpenAI não configurada. Configure a chave API em Configurações.");
        }
        
        // Preparar prompt
        $prompt = self::gerarPromptConciliacao($dados);
        
        // Fazer chamada à API
        $response = self::chamarAPI($prompt);
        
        return $response;
    }
    
    /**
     * Gera prompt para análise de conciliação
     */
    private static function gerarPromptConciliacao($dados)
    {
        $prompt = "Você é um especialista em contabilidade e análise financeira. Analise a seguinte conciliação bancária e identifique:\n\n";
        $prompt .= "1. **Inconsistências** - Valores ou datas que não batem\n";
        $prompt .= "2. **Pontos de Atenção** - Transações suspeitas ou incomuns\n";
        $prompt .= "3. **Erros Potenciais** - Possíveis erros de lançamento\n";
        $prompt .= "4. **Recomendações** - Ações para melhorar o processo\n\n";
        
        $prompt .= "### Dados da Conciliação:\n\n";
        $prompt .= "**Período:** " . $dados['data_inicio'] . " a " . $dados['data_fim'] . "\n";
        $prompt .= "**Conta:** " . $dados['conta_descricao'] . "\n";
        $prompt .= "**Saldo Extrato:** R$ " . number_format($dados['saldo_extrato'], 2, ',', '.') . "\n";
        $prompt .= "**Saldo Sistema:** R$ " . number_format($dados['saldo_sistema'], 2, ',', '.') . "\n";
        $prompt .= "**Diferença:** R$ " . number_format($dados['diferenca'], 2, ',', '.') . "\n\n";
        
        // Itens do extrato não vinculados
        if (!empty($dados['itens_nao_vinculados'])) {
            $prompt .= "### Itens do Extrato NÃO Vinculados (" . count($dados['itens_nao_vinculados']) . "):\n\n";
            foreach ($dados['itens_nao_vinculados'] as $i => $item) {
                if ($i >= 20) {
                    $prompt .= "... e mais " . (count($dados['itens_nao_vinculados']) - 20) . " itens\n";
                    break;
                }
                $tipo = $item['tipo_extrato'] === 'credito' ? '+' : '-';
                $prompt .= "- " . $item['data_extrato'] . " | " . $item['descricao_extrato'] . " | " . $tipo . " R$ " . number_format($item['valor_extrato'], 2, ',', '.') . "\n";
            }
            $prompt .= "\n";
        }
        
        // Movimentações do sistema não conciliadas
        if (!empty($dados['movimentacoes_nao_conciliadas'])) {
            $prompt .= "### Movimentações do Sistema NÃO Conciliadas (" . count($dados['movimentacoes_nao_conciliadas']) . "):\n\n";
            foreach ($dados['movimentacoes_nao_conciliadas'] as $i => $mov) {
                if ($i >= 20) {
                    $prompt .= "... e mais " . (count($dados['movimentacoes_nao_conciliadas']) - 20) . " movimentações\n";
                    break;
                }
                $tipo = $mov['tipo'] === 'entrada' ? '+' : '-';
                $prompt .= "- " . $mov['data_movimentacao'] . " | " . $mov['descricao_completa'] . " | " . $tipo . " R$ " . number_format($mov['valor'], 2, ',', '.') . "\n";
            }
            $prompt .= "\n";
        }
        
        // Itens vinculados (resumo)
        if (!empty($dados['itens_vinculados'])) {
            $prompt .= "### Itens Vinculados: " . count($dados['itens_vinculados']) . " transações conciliadas com sucesso\n\n";
        }
        
        $prompt .= "### Sua Análise:\n";
        $prompt .= "Forneça uma análise detalhada em formato Markdown, com:\n";
        $prompt .= "- ✅ ou ❌ para indicar pontos positivos/negativos\n";
        $prompt .= "- Use **negrito** para destacar pontos importantes\n";
        $prompt .= "- Liste ações específicas recomendadas\n";
        $prompt .= "- Seja direto e objetivo\n";
        $prompt .= "- Se a diferença for zero ou próxima de zero, elogie a precisão\n";
        $prompt .= "- Se houver muitos itens não vinculados, sugira possíveis matches entre extrato e sistema\n";
        
        return $prompt;
    }
    
    /**
     * Chama API OpenAI com suporte a override de modelo
     * @param string $prompt
     * @param string|null $systemPrompt
     * @param string|null $modelOverride Modelo alternativo (ex: gpt-4o para insights)
     * @param int|null $maxTokensOverride
     * @return string
     */
    public static function chat($prompt, $systemPrompt = null, $modelOverride = null, $maxTokensOverride = null)
    {
        self::init();
        return self::chamarAPI($prompt, $systemPrompt, $modelOverride, $maxTokensOverride);
    }
    
    /**
     * Chama API OpenAI
     */
    private static function chamarAPI($prompt, $systemPrompt = null, $modelOverride = null, $maxTokensOverride = null)
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        
        $model = $modelOverride ?? self::$model;
        $maxTokens = $maxTokensOverride ?? self::$maxTokens;
        
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float)self::$temperature,
            'max_tokens' => (int)$maxTokens
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)self::$timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::$apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("Erro ao conectar com OpenAI: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
            throw new \Exception("Erro da API OpenAI ({$httpCode}): " . $errorMessage);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception("Resposta inválida da API OpenAI");
        }
        
        return $result['choices'][0]['message']['content'];
    }
    
    /**
     * Sugere categoria para uma transação
     */
    public static function sugerirCategoria($descricao, $valor, $tipo)
    {
        self::init();
        
        if (!self::isConfigured() || !Configuracao::get('ia.sugestao_categorias', false)) {
            return null;
        }
        
        $prompt = "Baseado na descrição abaixo, sugira UMA categoria financeira apropriada.\n\n";
        $prompt .= "Descrição: {$descricao}\n";
        $prompt .= "Valor: R$ " . number_format($valor, 2, ',', '.') . "\n";
        $prompt .= "Tipo: " . ($tipo === 'entrada' ? 'Receita' : 'Despesa') . "\n\n";
        $prompt .= "Responda APENAS com o nome da categoria, sem explicações.";
        
        try {
            return trim(self::chamarAPI($prompt));
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Detecta possíveis duplicatas
     */
    public static function detectarDuplicatas($transacoes)
    {
        self::init();
        
        if (!self::isConfigured() || !Configuracao::get('ia.deteccao_duplicatas', false)) {
            return [];
        }
        
        // Preparar dados
        $dados = [];
        foreach ($transacoes as $i => $t) {
            $dados[] = "#{$i} - " . $t['data'] . " | " . $t['descricao'] . " | R$ " . number_format($t['valor'], 2, ',', '.');
        }
        
        $prompt = "Analise as transações abaixo e identifique possíveis duplicatas.\n\n";
        $prompt .= implode("\n", $dados) . "\n\n";
        $prompt .= "Retorne APENAS os números (IDs) das transações duplicadas, separados por vírgula. Se não houver duplicatas, retorne 'NENHUMA'.";
        
        try {
            $response = trim(self::chamarAPI($prompt));
            
            if ($response === 'NENHUMA') {
                return [];
            }
            
            // Extrair IDs
            preg_match_all('/#?(\d+)/', $response, $matches);
            return array_map('intval', $matches[1]);
        } catch (\Exception $e) {
            return [];
        }
    }
}
