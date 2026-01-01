<?php
namespace Includes\Services;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Fornecedor;
use App\Models\Cliente;

/**
 * Serviço de classificação automática usando OpenAI
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
     * Analisar e classificar transação
     */
    public function analisar($transacao)
    {
        // Se não tiver chave OpenAI, usar classificação básica
        if (!$this->openaiKey) {
            return $this->classificacaoBasica($transacao);
        }
        
        // Buscar contexto da empresa
        $contexto = $this->obterContextoEmpresa();
        
        // Montar prompt para a IA
        $prompt = $this->montarPrompt($transacao, $contexto);
        
        try {
            $response = $this->chamarOpenAI($prompt);
            return $this->processarResposta($response, $transacao);
        } catch (\Exception $e) {
            // Em caso de erro, usar classificação básica
            error_log("Erro ao classificar com IA: " . $e->getMessage());
            return $this->classificacaoBasica($transacao);
        }
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
