<?php
namespace Includes\Services;

use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoLog;
use App\Models\Produto;
use App\Models\PedidoVinculado;
use App\Models\Cliente;
use App\Models\IntegracaoJob;
use App\Models\ProdutoImagem;
use App\Models\WooCommerceMetadata;
use Includes\Services\WooCommerceValidator;

class WooCommerceService
{
    private $integracaoModel;
    private $woocommerceModel;
    private $logModel;
    private $produtoModel;
    private $pedidoModel;
    private $clienteModel;
    private $jobModel;
    private $imagemModel;
    private $metadataModel;
    private $validator;
    
    public function __construct()
    {
        $this->integracaoModel = new IntegracaoConfig();
        $this->woocommerceModel = new IntegracaoWooCommerce();
        $this->logModel = new IntegracaoLog();
        $this->produtoModel = new Produto();
        $this->pedidoModel = new PedidoVinculado();
        $this->clienteModel = new Cliente();
        $this->jobModel = new IntegracaoJob();
        $this->imagemModel = new ProdutoImagem();
        $this->metadataModel = new WooCommerceMetadata();
        $this->validator = new WooCommerceValidator();
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
    
    // ========================================
    // NOVOS MÉTODOS - SISTEMA MELHORADO
    // ========================================
    
    /**
     * Busca todos os status do WooCommerce (incluindo customizados)
     */
    public function buscarStatusWooCommerce($config)
    {
        try {
            $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/reports/orders/totals';
            
            $response = $this->requestWooCommerce('GET', $url, [], $config);
            $data = json_decode($response, true);
            
            $status = [];
            
            if ($data && is_array($data)) {
                foreach ($data as $statusItem) {
                    $slug = $statusItem['slug'] ?? '';
                    $nome = $statusItem['name'] ?? '';
                    
                    if ($slug && $nome) {
                        $status[$slug] = $nome;
                    }
                }
            }
            
            // Armazena no metadata
            foreach ($status as $slug => $nome) {
                $this->metadataModel->createOrUpdate([
                    'integracao_id' => $config['integracao_id'],
                    'tipo' => WooCommerceMetadata::TIPO_STATUS,
                    'chave' => $slug,
                    'nome' => $nome,
                    'ativo' => true
                ]);
            }
            
            return ['sucesso' => true, 'status' => $status];
            
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Busca todas as formas de pagamento do WooCommerce
     */
    public function buscarFormasPagamentoWooCommerce($config)
    {
        try {
            $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/payment_gateways';
            
            $response = $this->requestWooCommerce('GET', $url, [], $config);
            $data = json_decode($response, true);
            
            $formasPagamento = [];
            
            if ($data && is_array($data)) {
                foreach ($data as $gateway) {
                    $id = $gateway['id'] ?? '';
                    $title = $gateway['title'] ?? '';
                    $enabled = $gateway['enabled'] ?? false;
                    
                    if ($id && $title) {
                        $formasPagamento[$id] = [
                            'titulo' => $title,
                            'ativo' => $enabled,
                            'descricao' => $gateway['description'] ?? ''
                        ];
                        
                        // Armazena no metadata
                        $this->metadataModel->createOrUpdate([
                            'integracao_id' => $config['integracao_id'],
                            'tipo' => WooCommerceMetadata::TIPO_PAYMENT_GATEWAY,
                            'chave' => $id,
                            'nome' => $title,
                            'dados_extras' => [
                                'descricao' => $gateway['description'] ?? '',
                                'method_title' => $gateway['method_title'] ?? ''
                            ],
                            'ativo' => $enabled
                        ]);
                    }
                }
            }
            
            return ['sucesso' => true, 'formas_pagamento' => $formasPagamento];
            
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Busca categorias do WooCommerce
     */
    public function buscarCategoriasWooCommerce($config)
    {
        try {
            $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products/categories';
            $url .= '?per_page=100';
            
            $response = $this->requestWooCommerce('GET', $url, [], $config);
            $data = json_decode($response, true);
            
            $categorias = [];
            
            if ($data && is_array($data)) {
                foreach ($data as $categoria) {
                    $id = $categoria['id'] ?? '';
                    $slug = $categoria['slug'] ?? '';
                    $nome = $categoria['name'] ?? '';
                    
                    if ($slug && $nome) {
                        $categorias[$slug] = $nome;
                        
                        // Armazena no metadata
                        $this->metadataModel->createOrUpdate([
                            'integracao_id' => $config['integracao_id'],
                            'tipo' => WooCommerceMetadata::TIPO_CATEGORIA,
                            'chave' => $slug,
                            'nome' => $nome,
                            'dados_extras' => [
                                'woo_id' => $id,
                                'parent' => $categoria['parent'] ?? 0
                            ],
                            'ativo' => true
                        ]);
                    }
                }
            }
            
            return ['sucesso' => true, 'categorias' => $categorias];
            
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Requisição genérica à API WooCommerce
     */
    private function requestWooCommerce($method, $url, $data = [], $config = null)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}");
        }
        
        return $response;
    }
    
    /**
     * Sincronização incremental de produtos (apenas modificados)
     */
    public function sincronizarProdutosIncremental($config, $empresaId, $opcoes = [])
    {
        $inicioExecucao = microtime(true);
        $total = 0;
        $atualizados = 0;
        $criados = 0;
        $ignorados = 0;
        $erros = [];
        
        try {
            // Busca última sincronização
            $ultimaSync = $config['ultima_sync_produtos'] ?? null;
            
            // Adiciona filtro de data modificada
            if ($ultimaSync) {
                $opcoes['modified_after'] = $ultimaSync;
            }
            
            $produtos = $this->buscarProdutosWooCommerceAPI($config, $opcoes);
            
            foreach ($produtos as $prodWoo) {
                try {
                    // Valida dados se configurado
                    if ($config['validar_dados']) {
                        $validacao = $this->validator->validarProduto($prodWoo);
                        if (!$validacao['valido']) {
                            $erros[] = "SKU {$prodWoo['sku']}: " . implode(', ', $validacao['erros']);
                            $ignorados++;
                            continue;
                        }
                    }
                    
                    // Sanitiza dados
                    $dadosSanitizados = $this->validator->sanitizarProduto($prodWoo);
                    
                    // Busca produto existente por SKU
                    $produtoExistente = $this->produtoModel->findBySku($dadosSanitizados['sku'], $empresaId);
                    
                    // Calcula hash para detectar mudanças
                    $hashNovo = md5(json_encode($prodWoo));
                    
                    // Verifica cache se existe
                    $cacheExistente = $this->buscarCache($config['integracao_id'], 'produto', $prodWoo['sku']);
                    
                    if ($cacheExistente && $cacheExistente['hash_dados'] === $hashNovo) {
                        $ignorados++; // Não mudou, ignora
                        continue;
                    }
                    
                    $dados = [
                        'empresa_id' => $empresaId,
                        'nome' => $dadosSanitizados['nome'],
                        'descricao' => $dadosSanitizados['descricao'],
                        'sku' => $dadosSanitizados['sku'],
                        'preco_venda' => $dadosSanitizados['preco_venda'],
                        'custo_unitario' => $dadosSanitizados['preco_custo'],
                        'estoque' => $dadosSanitizados['estoque'],
                        'codigo_barras' => $dadosSanitizados['codigo_barras'],
                        'ativo' => $dadosSanitizados['ativo']
                    ];
                    
                    // Mapear categoria se configurado
                    if (!empty($prodWoo['categories']) && !empty($config['mapeamento_categorias'])) {
                        $categoriasWoo = json_decode($config['mapeamento_categorias'], true);
                        $categoriaSlug = $prodWoo['categories'][0]['slug'] ?? null;
                        
                        if ($categoriaSlug && isset($categoriasWoo[$categoriaSlug])) {
                            $dados['categoria_id'] = $categoriasWoo[$categoriaSlug];
                        } elseif (isset($categoriasWoo['_default'])) {
                            $dados['categoria_id'] = $categoriasWoo['_default'];
                        }
                    }
                    
                    if ($produtoExistente) {
                        $this->produtoModel->update($produtoExistente['id'], $dados);
                        $produtoId = $produtoExistente['id'];
                        $atualizados++;
                    } else {
                        // Gerar código se não existir
                        if (empty($dados['codigo'])) {
                            $dados['codigo'] = 'WOO-' . $dadosSanitizados['sku'];
                        }
                        $produtoId = $this->produtoModel->create($dados);
                        $criados++;
                    }
                    
                    // Atualiza cache
                    $this->atualizarCache($config['integracao_id'], 'produto', $prodWoo['sku'], $produtoId, $hashNovo);
                    
                    // Importar imagens se configurado
                    if ($config['importar_imagens'] && !empty($prodWoo['images'])) {
                        $this->importarImagensProduto($prodWoo['images'], $produtoId);
                    }
                    
                    $total++;
                    
                } catch (\Exception $e) {
                    $erros[] = "Produto {$prodWoo['name']}: " . $e->getMessage();
                }
            }
            
            // Atualiza última sincronização
            $this->atualizarUltimaSyncProdutos($config['integracao_id']);
            
        } catch (\Exception $e) {
            $erros[] = "Erro geral: " . $e->getMessage();
        }
        
        $tempoExecucao = round(microtime(true) - $inicioExecucao, 3);
        
        return [
            'total' => $total,
            'criados' => $criados,
            'atualizados' => $atualizados,
            'ignorados' => $ignorados,
            'erros' => $erros,
            'tempo_execucao' => $tempoExecucao
        ];
    }
    
    /**
     * Busca produtos via API (com suporte a modified_after)
     */
    private function buscarProdutosWooCommerceAPI($config, $opcoes = [])
    {
        $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products';
        $params = [];
        
        $params[] = 'per_page=' . ($opcoes['limite'] ?? 100);
        
        // Filtro incremental por data de modificação
        if (!empty($opcoes['modified_after'])) {
            $params[] = 'modified_after=' . urlencode($opcoes['modified_after']);
        }
        
        // Outros filtros
        if (isset($opcoes['periodo'])) {
            // Implementar filtros de período
        }
        
        if (!empty($params)) {
            $url .= '?' . implode('&', $params);
        }
        
        $response = $this->requestWooCommerce('GET', $url, [], $config);
        return json_decode($response, true) ?: [];
    }
    
    /**
     * Importa imagens de produtos
     */
    private function importarImagensProduto($images, $produtoId)
    {
        if (empty($images)) {
            return;
        }
        
        $ordem = 0;
        
        foreach ($images as $img) {
            try {
                $urlOriginal = $img['src'] ?? '';
                
                if (empty($urlOriginal)) {
                    continue;
                }
                
                // Verifica se já foi importada
                if ($this->imagemModel->existePorUrl($produtoId, $urlOriginal)) {
                    continue;
                }
                
                // Faz download da imagem
                $conteudo = @file_get_contents($urlOriginal);
                
                if ($conteudo === false) {
                    continue;
                }
                
                // Define nome do arquivo
                $extensao = pathinfo(parse_url($urlOriginal, PHP_URL_PATH), PATHINFO_EXTENSION);
                $nomeArquivo = 'produto_' . $produtoId . '_' . time() . '_' . $ordem . '.' . $extensao;
                
                // Diretório de upload
                $diretorio = __DIR__ . '/../../public/uploads/produtos/';
                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0755, true);
                }
                
                $caminhoCompleto = $diretorio . $nomeArquivo;
                
                // Salva arquivo
                if (file_put_contents($caminhoCompleto, $conteudo)) {
                    // Pega dimensões
                    $info = @getimagesize($caminhoCompleto);
                    
                    // Salva no banco
                    $this->imagemModel->create([
                        'produto_id' => $produtoId,
                        'url_original' => $urlOriginal,
                        'caminho_local' => 'produtos/' . $nomeArquivo,
                        'ordem' => $ordem,
                        'principal' => $ordem === 0, // Primeira é principal
                        'largura' => $info[0] ?? null,
                        'altura' => $info[1] ?? null,
                        'tamanho_bytes' => filesize($caminhoCompleto)
                    ]);
                }
                
                $ordem++;
                
            } catch (\Exception $e) {
                // Ignora erros de imagem individual
                continue;
            }
        }
    }
    
    /**
     * Busca cache de sincronização
     */
    private function buscarCache($integracaoId, $tipo, $referenciaExterna)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM integracao_sync_cache 
                WHERE integracao_id = :integracao_id 
                AND tipo = :tipo 
                AND referencia_externa = :referencia_externa 
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo,
            'referencia_externa' => $referenciaExterna
        ]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Atualiza cache de sincronização
     */
    private function atualizarCache($integracaoId, $tipo, $referenciaExterna, $referenciaInterna, $hashDados)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO integracao_sync_cache 
                (integracao_id, tipo, referencia_externa, referencia_interna, hash_dados) 
                VALUES 
                (:integracao_id, :tipo, :referencia_externa, :referencia_interna, :hash_dados)
                ON DUPLICATE KEY UPDATE
                referencia_interna = VALUES(referencia_interna),
                hash_dados = VALUES(hash_dados),
                ultima_atualizacao = CURRENT_TIMESTAMP";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo,
            'referencia_externa' => $referenciaExterna,
            'referencia_interna' => $referenciaInterna,
            'hash_dados' => $hashDados
        ]);
    }
    
    /**
     * Atualiza timestamp de última sincronização de produtos
     */
    private function atualizarUltimaSyncProdutos($integracaoId)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "UPDATE integracoes_woocommerce 
                SET ultima_sync_produtos = NOW() 
                WHERE integracao_id = :integracao_id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute(['integracao_id' => $integracaoId]);
    }
    
    /**
     * Cria job na fila
     */
    public function criarJob($integracaoId, $tipo, $payload = [], $prioridade = IntegracaoJob::PRIORIDADE_NORMAL)
    {
        return $this->jobModel->create([
            'integracao_id' => $integracaoId,
            'tipo' => $tipo,
            'payload' => $payload,
            'prioridade' => $prioridade
        ]);
    }
    
    /**
     * Processa próximo job da fila
     */
    public function processarProximoJob()
    {
        $job = $this->jobModel->buscarProximo();
        
        if (!$job) {
            return ['sucesso' => false, 'mensagem' => 'Nenhum job pendente'];
        }
        
        $inicioExecucao = microtime(true);
        
        // Marca como processando
        $this->jobModel->marcarProcessando($job['id']);
        
        try {
            $resultado = null;
            
            switch ($job['tipo']) {
                case IntegracaoJob::TIPO_SYNC_PRODUTOS:
                    $resultado = $this->executarJobSyncProdutos($job);
                    break;
                    
                case IntegracaoJob::TIPO_SYNC_PEDIDOS:
                    $resultado = $this->executarJobSyncPedidos($job);
                    break;
                    
                case IntegracaoJob::TIPO_IMPORTAR_IMAGENS:
                    $resultado = $this->executarJobImportarImagens($job);
                    break;
                    
                default:
                    throw new \Exception("Tipo de job desconhecido: {$job['tipo']}");
            }
            
            $tempoExecucao = round(microtime(true) - $inicioExecucao, 3);
            
            $this->jobModel->marcarConcluido($job['id'], $tempoExecucao);
            
            return ['sucesso' => true, 'resultado' => $resultado];
            
        } catch (\Exception $e) {
            $tempoExecucao = round(microtime(true) - $inicioExecucao, 3);
            
            $this->jobModel->marcarErro($job['id'], $e->getMessage(), $tempoExecucao);
            
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Executa job de sincronização de produtos
     */
    private function executarJobSyncProdutos($job)
    {
        $integracao = $this->integracaoModel->findById($job['integracao_id']);
        $config = $this->woocommerceModel->findByIntegracaoId($job['integracao_id']);
        
        $opcoes = $job['payload'] ?? [];
        
        return $this->sincronizarProdutosIncremental($config, $integracao['empresa_id'], $opcoes);
    }
    
    /**
     * Executa job de sincronização de pedidos
     */
    private function executarJobSyncPedidos($job)
    {
        $integracao = $this->integracaoModel->findById($job['integracao_id']);
        $config = $this->woocommerceModel->findByIntegracaoId($job['integracao_id']);
        
        $opcoes = $job['payload'] ?? [];
        
        return $this->sincronizarPedidos($config, $integracao['empresa_id'], $opcoes);
    }
    
    /**
     * Executa job de importação de imagens
     */
    private function executarJobImportarImagens($job)
    {
        $payload = $job['payload'];
        $produtoId = $payload['produto_id'] ?? null;
        $images = $payload['images'] ?? [];
        
        if (!$produtoId || empty($images)) {
            throw new \Exception('Produto ou imagens não informados');
        }
        
        $this->importarImagensProduto($images, $produtoId);
        
        return ['produto_id' => $produtoId, 'imagens_importadas' => count($images)];
    }
    
    /**
     * Obtém métricas para dashboard
     */
    public function obterMetricasDashboard($integracaoId, $dias = 7)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Contagem de jobs por status
        $jobsPorStatus = $this->jobModel->contarPorStatus($integracaoId);
        
        // Logs recentes
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM integracao_logs 
                WHERE integracao_id = :integracao_id 
                AND data >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                GROUP BY tipo";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId, 'dias' => $dias]);
        $logsPorTipo = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        // Produtos sincronizados hoje
        $sql = "SELECT COUNT(*) 
                FROM integracao_sync_cache 
                WHERE integracao_id = :integracao_id 
                AND tipo = 'produto'
                AND DATE(ultima_atualizacao) = CURDATE()";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        $produtosHoje = $stmt->fetchColumn();
        
        // Configuração
        $config = $this->woocommerceModel->findByIntegracaoId($integracaoId);
        
        return [
            'jobs' => $jobsPorStatus,
            'logs' => $logsPorTipo,
            'produtos_hoje' => $produtosHoje,
            'ultima_sync_produtos' => $config['ultima_sync_produtos'] ?? null,
            'ultima_sync_pedidos' => $config['ultima_sync_pedidos'] ?? null
        ];
    }
}
