<?php
namespace Includes\Services;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\RegraClassificacao;

/**
 * Serviço de classificação automática usando OpenAI
 * 
 * Ordem de classificação:
 * 1. Regras fixas do usuário (grátis, instantâneo)
 * 2. Histórico de aprovações anteriores (pattern matching)
 * 3. OpenAI (se configurado e nenhuma regra casar)
 * 4. Classificação básica por palavras-chave (fallback)
 */
class ClassificadorIAService
{
    private $empresaId;
    private $openaiKey;
    private $model = 'gpt-4o-mini'; // Mais barato e eficiente
    
    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
        $this->openaiKey = \App\Models\Configuracao::get('api.openai_key');
        
        $modelConfig = \App\Models\Configuracao::get('api.openai_model', 'gpt-4o-mini');
        if ($modelConfig) {
            $this->model = $modelConfig;
        }
    }
    
    /**
     * Analisar e classificar transação.
     * Tenta regras fixas primeiro, depois IA, depois fallback básico.
     */
    public function analisar($transacao)
    {
        $descricao = $transacao['descricao_original'] ?? '';
        $tipo = $transacao['tipo'] ?? 'debito';

        // 1. Tentar regras fixas do usuário (grátis, instantâneo)
        try {
            $regraModel = new RegraClassificacao();
            $classificacaoRegra = $regraModel->classificar($descricao, $tipo, $this->empresaId);
            
            if ($classificacaoRegra !== null) {
                return $classificacaoRegra;
            }
        } catch (\Exception $e) {
            error_log("Erro ao aplicar regras de classificação: " . $e->getMessage());
        }

        // 2. Tentar histórico de aprovações anteriores
        try {
            $classificacaoHistorico = $this->classificarPorHistorico($descricao, $tipo);
            if ($classificacaoHistorico !== null) {
                return $classificacaoHistorico;
            }
        } catch (\Exception $e) {
            error_log("Erro ao classificar por histórico: " . $e->getMessage());
        }

        // 3. Tentar OpenAI (se configurado)
        if ($this->openaiKey) {
            $contexto = $this->obterContextoEmpresa();
            $prompt = $this->montarPrompt($transacao, $contexto);
            
            try {
                $response = $this->chamarOpenAI($prompt);
                return $this->processarResposta($response, $transacao);
            } catch (\Exception $e) {
                error_log("Erro ao classificar com IA: " . $e->getMessage());
            }
        }

        // 4. Fallback: classificação básica por palavras-chave
        return $this->classificacaoBasica($transacao);
    }

    /**
     * Classifica transação baseado no histórico de aprovações anteriores.
     * Procura transações aprovadas com descrição similar.
     */
    private function classificarPorHistorico(string $descricao, string $tipo): ?array
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Buscar transações aprovadas com descrição similar (últimos 6 meses)
        $sql = "SELECT tp.categoria_sugerida_id, tp.centro_custo_sugerido_id,
                       tp.fornecedor_sugerido_id, tp.cliente_sugerido_id,
                       tp.descricao_original,
                       COUNT(*) as vezes
                FROM transacoes_pendentes tp
                WHERE tp.empresa_id = :empresa_id
                  AND tp.status = 'aprovada'
                  AND tp.categoria_sugerida_id IS NOT NULL
                  AND tp.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  AND tp.tipo = :tipo
                GROUP BY tp.categoria_sugerida_id, tp.centro_custo_sugerido_id,
                         tp.fornecedor_sugerido_id, tp.cliente_sugerido_id,
                         tp.descricao_original
                HAVING vezes >= 1
                ORDER BY vezes DESC
                LIMIT 50";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $this->empresaId,
            'tipo' => $tipo
        ]);
        $historico = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $descricaoUpper = mb_strtoupper($descricao);

        foreach ($historico as $item) {
            $itemDescUpper = mb_strtoupper($item['descricao_original']);
            
            // Verificar similaridade (mesmas palavras-chave)
            $similarity = similar_text($descricaoUpper, $itemDescUpper, $percent);
            
            if ($percent >= 70) {
                return [
                    'categoria_id' => $item['categoria_sugerida_id'],
                    'centro_custo_id' => $item['centro_custo_sugerido_id'],
                    'fornecedor_id' => $item['fornecedor_sugerido_id'],
                    'cliente_id' => $item['cliente_sugerido_id'],
                    'confianca' => min(90, 60 + ($item['vezes'] * 5)),
                    'justificativa' => "Baseado em {$item['vezes']} transação(ões) anterior(es) similar(es) ({$percent}% similar)"
                ];
            }
        }

        return null;
    }
    
    /**
     * Classificação básica (sem IA) baseada em palavras-chave
     */
    private function classificacaoBasica($transacao)
    {
        $descricao = strtolower($transacao['descricao_original']);
        $tipo = $transacao['tipo']; // debito ou credito
        
        $categoriaModel = new CategoriaFinanceira();
        $categorias = $categoriaModel->findAll($this->empresaId);
        
        // Palavras-chave comuns
        $keywords = [
            'alimentacao' => ['mercado', 'supermercado', 'padaria', 'restaurante', 'ifood', 'uber eats'],
            'combustivel' => ['posto', 'gasolina', 'shell', 'ipiranga', 'petrobras'],
            'fornecedores' => ['fornecedor', 'ltda', 'industria', 'comercio'],
            'vendas' => ['pix recebido', 'deposito', 'pagamento recebido'],
            'salarios' => ['salario', 'folha', 'inss', 'fgts'],
            'aluguel' => ['aluguel', 'locacao'],
            'servicos' => ['servico', 'consultoria', 'manutencao']
        ];
        
        // Tentar encontrar categoria por palavra-chave
        foreach ($keywords as $tipo_cat => $palavras) {
            foreach ($palavras as $palavra) {
                if (strpos($descricao, $palavra) !== false) {
                    // Buscar categoria que contenha esse tipo
                    foreach ($categorias as $cat) {
                        if (stripos($cat['nome'], $tipo_cat) !== false) {
                            return [
                                'categoria_id' => $cat['id'],
                                'centro_custo_id' => null,
                                'fornecedor_id' => null,
                                'cliente_id' => null,
                                'confianca' => 60,
                                'justificativa' => "Detectada palavra-chave '{$palavra}' na descrição"
                            ];
                        }
                    }
                }
            }
        }
        
        // Se não encontrou nada, retornar primeira categoria do tipo correto
        $tipoCategoria = $tipo === 'debito' ? 'despesa' : 'receita';
        foreach ($categorias as $cat) {
            if ($cat['tipo'] === $tipoCategoria) {
                return [
                    'categoria_id' => $cat['id'],
                    'centro_custo_id' => null,
                    'fornecedor_id' => null,
                    'cliente_id' => null,
                    'confianca' => 30,
                    'justificativa' => 'Classificação padrão (nenhuma regra específica encontrada)'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Obter contexto da empresa (categorias, fornecedores, etc)
     */
    private function obterContextoEmpresa()
    {
        $categoriaModel = new CategoriaFinanceira();
        $centroCustoModel = new CentroCusto();
        $fornecedorModel = new Fornecedor();
        $clienteModel = new Cliente();
        
        return [
            'categorias' => $categoriaModel->findAll($this->empresaId),
            'centros_custo' => $centroCustoModel->findAll($this->empresaId),
            'fornecedores' => $fornecedorModel->findAll(['empresa_id' => $this->empresaId]),
            'clientes' => $clienteModel->findAll(['empresa_id' => $this->empresaId])
        ];
    }
    
    /**
     * Montar prompt para OpenAI
     */
    private function montarPrompt($transacao, $contexto)
    {
        $categoriasList = array_map(function($cat) {
            return "- {$cat['codigo']}: {$cat['nome']} ({$cat['tipo']})";
        }, $contexto['categorias']);
        
        $centrosList = array_map(function($cc) {
            return "- {$cc['codigo']}: {$cc['nome']}";
        }, $contexto['centros_custo']);
        
        $prompt = "Você é um assistente financeiro. Analise esta transação bancária e classifique-a:\n\n";
        $prompt .= "TRANSAÇÃO:\n";
        $prompt .= "- Data: {$transacao['data_transacao']}\n";
        $prompt .= "- Descrição: {$transacao['descricao_original']}\n";
        $prompt .= "- Valor: R$ " . number_format($transacao['valor'], 2, ',', '.') . "\n";
        $prompt .= "- Tipo: " . ($transacao['tipo'] === 'debito' ? 'SAÍDA (Despesa)' : 'ENTRADA (Receita)') . "\n\n";
        
        $prompt .= "CATEGORIAS DISPONÍVEIS:\n";
        $prompt .= implode("\n", $categoriasList) . "\n\n";
        
        $prompt .= "CENTROS DE CUSTO DISPONÍVEIS:\n";
        $prompt .= implode("\n", $centrosList) . "\n\n";
        
        $prompt .= "Retorne APENAS um JSON no formato:\n";
        $prompt .= '{"categoria_codigo": "codigo_da_categoria", "centro_custo_codigo": "codigo_do_centro", "confianca": 0-100, "justificativa": "explicacao_breve"}';
        
        return $prompt;
    }
    
    /**
     * Chamar API OpenAI
     */
    private function chamarOpenAI($prompt)
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente financeiro especializado em classificação de transações bancárias.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 200
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro ao chamar OpenAI: HTTP {$httpCode}");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Processar resposta da OpenAI
     */
    private function processarResposta($response, $transacao)
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \Exception("Resposta inválida da OpenAI");
        }
        
        $content = $response['choices'][0]['message']['content'];
        
        // Extrair JSON da resposta
        preg_match('/\{.*\}/s', $content, $matches);
        if (empty($matches)) {
            throw new \Exception("JSON não encontrado na resposta");
        }
        
        $resultado = json_decode($matches[0], true);
        
        // Converter códigos para IDs
        $categoriaModel = new CategoriaFinanceira();
        $centroCustoModel = new CentroCusto();
        
        $categoria = $categoriaModel->findByCodigo($resultado['categoria_codigo'], $this->empresaId);
        $centroCusto = null;
        
        if (!empty($resultado['centro_custo_codigo'])) {
            $centroCusto = $centroCustoModel->findByCodigo($resultado['centro_custo_codigo'], $this->empresaId);
        }
        
        return [
            'categoria_id' => $categoria['id'] ?? null,
            'centro_custo_id' => $centroCusto['id'] ?? null,
            'fornecedor_id' => null,
            'cliente_id' => null,
            'confianca' => $resultado['confianca'] ?? 50,
            'justificativa' => $resultado['justificativa'] ?? 'Classificado por IA'
        ];
    }
}
