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
     * Executa sincronização completa
     */
    public function sincronizar($integracaoId)
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
            // Sincroniza produtos
            if ($config['sincronizar_produtos']) {
                $resultProdutos = $this->sincronizarProdutos($config, $integracao['empresa_id']);
                $resultados['produtos'] = $resultProdutos['total'];
                if (!empty($resultProdutos['erros'])) {
                    $resultados['erros'] = array_merge($resultados['erros'], $resultProdutos['erros']);
                }
            }
            
            // Sincroniza pedidos
            if ($config['sincronizar_pedidos']) {
                $resultPedidos = $this->sincronizarPedidos($config, $integracao['empresa_id']);
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
    private function sincronizarProdutos($config, $empresaId)
    {
        $total = 0;
        $erros = [];
        
        try {
            $produtos = $this->buscarProdutosWooCommerce($config);
            
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
    private function sincronizarPedidos($config, $empresaId)
    {
        $total = 0;
        $erros = [];
        
        try {
            $pedidos = $this->buscarPedidosWooCommerce($config);
            
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
     * Busca produtos da API WooCommerce
     */
    private function buscarProdutosWooCommerce($config)
    {
        $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products';
        
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
     * Busca pedidos da API WooCommerce
     */
    private function buscarPedidosWooCommerce($config)
    {
        $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/orders?per_page=50';
        
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
}
