<?php
namespace Includes\Services;

use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoLog;
use App\Models\Produto;
use App\Models\PedidoVinculado;
use App\Models\Cliente;

class WooCommerceService
{
    private $integracaoModel;
    private $woocommerceModel;
    private $logModel;
    private $produtoModel;
    private $pedidoModel;
    private $clienteModel;
    
    public function __construct()
    {
        $this->integracaoModel = new IntegracaoConfig();
        $this->woocommerceModel = new IntegracaoWooCommerce();
        $this->logModel = new IntegracaoLog();
        $this->produtoModel = new Produto();
        $this->pedidoModel = new PedidoVinculado();
        $this->clienteModel = new Cliente();
    }
    
    /**
     * Executa sincronização completa com opções
     */
    public function sincronizar($integracaoId, $opcoes = [])
    {
        $integracao = $this->integracaoModel->findById($integracaoId);
        
        if (!$integracao || $integracao['tipo'] !== 'woocommerce') {
            return ['sucesso' => false, 'erro' => 'Integração inválida'];
        }
        
        $config = $this->woocommerceModel->findByIntegracaoId($integracaoId);
        
        if (!$config) {
            return ['sucesso' => false, 'erro' => 'Configuração não encontrada'];
        }
        
        $resultados = [
            'produtos' => 0,
            'pedidos' => 0,
            'erros' => []
        ];
        
        try {
            // Define o que sincronizar
            $sincProdutos = isset($opcoes['sincronizar_produtos']) ? $opcoes['sincronizar_produtos'] : $config['sincronizar_produtos'];
            $sincPedidos = isset($opcoes['sincronizar_pedidos']) ? $opcoes['sincronizar_pedidos'] : $config['sincronizar_pedidos'];
            
            // Sincroniza produtos
            if ($sincProdutos) {
                $resultProdutos = $this->sincronizarProdutos($config, $integracao['empresa_id'], $opcoes);
                $resultados['produtos'] = $resultProdutos['total'];
                if (!empty($resultProdutos['erros'])) {
                    $resultados['erros'] = array_merge($resultados['erros'], $resultProdutos['erros']);
                }
            }
            
            // Sincroniza pedidos
            if ($sincPedidos) {
                $resultPedidos = $this->sincronizarPedidos($config, $integracao['empresa_id'], $opcoes);
                $resultados['pedidos'] = $resultPedidos['total'];
                if (!empty($resultPedidos['erros'])) {
                    $resultados['erros'] = array_merge($resultados['erros'], $resultPedidos['erros']);
                }
            }
            
            // Atualiza data de sincronização
            $proximaSinc = date('Y-m-d H:i:s', strtotime("+{$integracao['intervalo_sincronizacao']} minutes"));
            $this->integracaoModel->updateUltimaSincronizacao($integracaoId, $proximaSinc);
            
            // Log
            $mensagem = "Sincronização concluída: {$resultados['produtos']} produtos, {$resultados['pedidos']} pedidos";
            $tipoLog = empty($resultados['erros']) ? IntegracaoLog::TIPO_SUCESSO : IntegracaoLog::TIPO_AVISO;
            $this->logModel->create($integracaoId, $tipoLog, $mensagem, $resultados);
            
            return ['sucesso' => true, 'resultados' => $resultados];
            
        } catch (\Exception $e) {
            $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 'Erro na sincronização: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Sincroniza produtos
     */
    private function sincronizarProdutos($config, $empresaId, $opcoes = [])
    {
        $total = 0;
        $erros = [];
        
        try {
            $produtos = $this->buscarProdutosWooCommerce($config, $opcoes);
            
            foreach ($produtos as $prodWoo) {
                try {
                    // Verifica se produto já existe (por SKU)
                    $produtoExistente = null; // Implementar busca por SKU
                    
                    $dados = [
                        'empresa_id' => $empresaId,
                        'nome' => $prodWoo['name'],
                        'descricao' => $prodWoo['description'] ?? null,
                        'sku' => $prodWoo['sku'] ?? null,
                        'preco_venda' => $prodWoo['price'] ?? 0,
                        'estoque' => $prodWoo['stock_quantity'] ?? 0,
                        'ativo' => $prodWoo['status'] === 'publish' ? 1 : 0
                    ];
                    
                    if ($produtoExistente) {
                        $this->produtoModel->update($produtoExistente['id'], $dados);
                    } else {
                        $this->produtoModel->create($dados);
                    }
                    
                    $total++;
                } catch (\Exception $e) {
                    $erros[] = "Produto {$prodWoo['name']}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $erros[] = "Erro ao buscar produtos: " . $e->getMessage();
        }
        
        return ['total' => $total, 'erros' => $erros];
    }
    
    /**
     * Sincroniza pedidos
     */
    private function sincronizarPedidos($config, $empresaId, $opcoes = [])
    {
        $total = 0;
        $erros = [];
        
        try {
            $pedidos = $this->buscarPedidosWooCommerce($config, $opcoes);
            
            foreach ($pedidos as $pedWoo) {
                try {
                    // Busca ou cria cliente
                    $clienteId = $this->buscarOuCriarCliente($pedWoo['billing'], $empresaId);
                    
                    $dados = [
                        'empresa_id' => $empresaId,
                        'cliente_id' => $clienteId,
                        'numero_pedido' => $pedWoo['number'],
                        'data_pedido' => $pedWoo['date_created'],
                        'status' => $this->mapearStatus($pedWoo['status']),
                        'valor_total' => $pedWoo['total'],
                        'origem' => 'woocommerce'
                    ];
                    
                    $this->pedidoModel->create($dados);
                    $total++;
                } catch (\Exception $e) {
                    $erros[] = "Pedido {$pedWoo['number']}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $erros[] = "Erro ao buscar pedidos: " . $e->getMessage();
        }
        
        return ['total' => $total, 'erros' => $erros];
    }
    
    /**
     * Busca produtos da API WooCommerce com filtros
     */
    private function buscarProdutosWooCommerce($config, $opcoes = [])
    {
        $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products';
        $params = [];
        
        // Limite de registros
        if (isset($opcoes['limite']) && $opcoes['limite'] > 0) {
            $params[] = 'per_page=' . intval($opcoes['limite']);
        } else {
            $params[] = 'per_page=100';
        }
        
        // Filtro por período
        if (isset($opcoes['periodo'])) {
            $dataFim = date('Y-m-d\TH:i:s');
            $dataInicio = null;
            
            switch ($opcoes['periodo']) {
                case '7dias':
                    $dataInicio = date('Y-m-d\TH:i:s', strtotime('-7 days'));
                    break;
                case '30dias':
                    $dataInicio = date('Y-m-d\TH:i:s', strtotime('-30 days'));
                    break;
                case 'custom':
                    if (isset($opcoes['data_inicio'])) {
                        $dataInicio = date('Y-m-d\T00:00:00', strtotime($opcoes['data_inicio']));
                    }
                    if (isset($opcoes['data_fim'])) {
                        $dataFim = date('Y-m-d\T23:59:59', strtotime($opcoes['data_fim']));
                    }
                    break;
            }
            
            if ($dataInicio) {
                $params[] = 'after=' . urlencode($dataInicio);
                $params[] = 'before=' . urlencode($dataFim);
            }
        }
        
        if (!empty($params)) {
            $url .= '?' . implode('&', $params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}");
        }
        
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Busca pedidos da API WooCommerce com filtros
     */
    private function buscarPedidosWooCommerce($config, $opcoes = [])
    {
        $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/orders';
        $params = [];
        
        // Limite de registros
        if (isset($opcoes['limite']) && $opcoes['limite'] > 0) {
            $params[] = 'per_page=' . intval($opcoes['limite']);
        } else {
            $params[] = 'per_page=50';
        }
        
        // Filtro por período
        if (isset($opcoes['periodo'])) {
            $dataFim = date('Y-m-d\TH:i:s');
            $dataInicio = null;
            
            switch ($opcoes['periodo']) {
                case '7dias':
                    $dataInicio = date('Y-m-d\TH:i:s', strtotime('-7 days'));
                    break;
                case '30dias':
                    $dataInicio = date('Y-m-d\TH:i:s', strtotime('-30 days'));
                    break;
                case 'custom':
                    if (isset($opcoes['data_inicio'])) {
                        $dataInicio = date('Y-m-d\T00:00:00', strtotime($opcoes['data_inicio']));
                    }
                    if (isset($opcoes['data_fim'])) {
                        $dataFim = date('Y-m-d\T23:59:59', strtotime($opcoes['data_fim']));
                    }
                    break;
            }
            
            if ($dataInicio) {
                $params[] = 'after=' . urlencode($dataInicio);
                $params[] = 'before=' . urlencode($dataFim);
            }
        }
        
        if (!empty($params)) {
            $url .= '?' . implode('&', $params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}");
        }
        
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Busca ou cria cliente
     */
    private function buscarOuCriarCliente($billing, $empresaId)
    {
        // Implementar busca por email
        // Por enquanto, retorna 1 (exemplo)
        return 1;
    }
    
    /**
     * Mapeia status WooCommerce para status do sistema
     */
    private function mapearStatus($statusWoo)
    {
        $mapa = [
            'pending' => 'pendente',
            'processing' => 'em_processamento',
            'completed' => 'concluido',
            'cancelled' => 'cancelado'
        ];
        
        return $mapa[$statusWoo] ?? 'pendente';
    }
    
    /**
     * Processa webhook do WooCommerce
     */
    public function processarWebhook($integracaoId, $topic, $data, $empresaId)
    {
        try {
            switch ($topic) {
                case 'product.created':
                case 'product.updated':
                    return $this->processarWebhookProduto($data, $empresaId);
                    
                case 'product.deleted':
                    return $this->processarWebhookProdutoDeletado($data, $empresaId);
                    
                case 'order.created':
                case 'order.updated':
                    return $this->processarWebhookPedido($data, $empresaId);
                    
                case 'order.deleted':
                    return $this->processarWebhookPedidoDeletado($data, $empresaId);
                    
                default:
                    return ['sucesso' => true, 'mensagem' => 'Evento não tratado'];
            }
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de produto
     */
    private function processarWebhookProduto($prodWoo, $empresaId)
    {
        try {
            // Busca produto por SKU
            $produtoExistente = null; // Implementar busca por SKU
            
            $dados = [
                'empresa_id' => $empresaId,
                'nome' => $prodWoo['name'],
                'descricao' => $prodWoo['description'] ?? null,
                'sku' => $prodWoo['sku'] ?? null,
                'preco_venda' => $prodWoo['price'] ?? 0,
                'estoque' => $prodWoo['stock_quantity'] ?? 0,
                'ativo' => $prodWoo['status'] === 'publish' ? 1 : 0
            ];
            
            if ($produtoExistente) {
                $this->produtoModel->update($produtoExistente['id'], $dados);
                return ['sucesso' => true, 'mensagem' => 'Produto atualizado'];
            } else {
                $this->produtoModel->create($dados);
                return ['sucesso' => true, 'mensagem' => 'Produto criado'];
            }
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de produto deletado
     */
    private function processarWebhookProdutoDeletado($prodWoo, $empresaId)
    {
        try {
            // Implementar exclusão/desativação
            return ['sucesso' => true, 'mensagem' => 'Produto desativado'];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de pedido
     */
    private function processarWebhookPedido($pedWoo, $empresaId)
    {
        try {
            $clienteId = $this->buscarOuCriarCliente($pedWoo['billing'], $empresaId);
            
            $dados = [
                'empresa_id' => $empresaId,
                'cliente_id' => $clienteId,
                'numero_pedido' => $pedWoo['number'],
                'data_pedido' => $pedWoo['date_created'],
                'status' => $this->mapearStatus($pedWoo['status']),
                'valor_total' => $pedWoo['total'],
                'origem' => 'woocommerce'
            ];
            
            // Busca pedido existente
            // Implementar busca por numero_pedido
            
            $this->pedidoModel->create($dados);
            return ['sucesso' => true, 'mensagem' => 'Pedido criado'];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de pedido deletado
     */
    private function processarWebhookPedidoDeletado($pedWoo, $empresaId)
    {
        try {
            // Implementar exclusão/desativação
            return ['sucesso' => true, 'mensagem' => 'Pedido removido'];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
}
