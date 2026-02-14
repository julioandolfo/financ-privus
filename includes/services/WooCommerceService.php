<?php
namespace Includes\Services;

use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoLog;
use App\Models\Produto;
use App\Models\PedidoVinculado;
use App\Models\PedidoItem;
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
    private $pedidoItemModel;
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
        $this->pedidoItemModel = new PedidoItem();
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
            $isPedidoUnico = !empty($opcoes['pedido_unico_id']);
            
            // Define o que sincronizar
            $sincProdutos = isset($opcoes['sincronizar_produtos']) ? $opcoes['sincronizar_produtos'] : $config['sincronizar_produtos'];
            $sincPedidos = isset($opcoes['sincronizar_pedidos']) ? $opcoes['sincronizar_pedidos'] : $config['sincronizar_pedidos'];
            
            // Se é pedido único, pula sincronização de produtos em lote 
            // (os produtos do pedido serão criados automaticamente)
            if ($isPedidoUnico) {
                $sincProdutos = false;
                $sincPedidos = true;
            }
            
            // Sincroniza produtos (em lote)
            if ($sincProdutos) {
                $resultProdutos = $this->sincronizarProdutos($config, $integracao['empresa_id'], $opcoes);
                $resultados['produtos'] = $resultProdutos['total'];
                if (!empty($resultProdutos['erros'])) {
                    $resultados['erros'] = array_merge($resultados['erros'], $resultProdutos['erros']);
                }
            }
            
            // Sincroniza pedidos (com produtos vinculados)
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
            if ($isPedidoUnico) {
                $mensagem = "Sincronização de pedido único #{$opcoes['pedido_unico_id']} concluída: {$resultados['pedidos']} pedido(s) processado(s)";
            } else {
                $mensagem = "Sincronização concluída: {$resultados['produtos']} produtos, {$resultados['pedidos']} pedidos";
            }
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
        $totalNovos = 0;
        $totalAtualizados = 0;
        $totalPulados = 0;
        $erros = [];
        $contaReceberModel = new \App\Models\ContaReceber();
        
        // Carrega configurações de ações por forma de pagamento
        $acoesFormasPagamento = !empty($config['acoes_formas_pagamento']) 
            ? json_decode($config['acoes_formas_pagamento'], true) 
            : [];
        
        // Carrega mapeamento de status
        $mapeamentoStatus = !empty($config['mapeamento_status']) 
            ? json_decode($config['mapeamento_status'], true) 
            : [];
        
        // Status que indicam que o pagamento foi confirmado
        $statusPagamentoConfirmado = ['em_processamento', 'concluido'];
        
        $isPedidoUnico = !empty($opcoes['pedido_unico_id']);
        
        // Limite desejado de pedidos NOVOS a importar
        $limiteNovos = isset($opcoes['limite']) && $opcoes['limite'] > 0 ? intval($opcoes['limite']) : 0;
        
        try {
            if ($isPedidoUnico) {
                // Pedido único: busca direto, sem paginação
                $pedidos = $this->buscarPedidosWooCommerce($config, $opcoes);
                
                \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                    'Sincronização de pedido único - Pedidos encontrados: ' . count($pedidos), 
                    ['empresa_id' => $empresaId]);
                
                foreach ($pedidos as $pedWoo) {
                    $resultado = $this->processarPedidoWoo($pedWoo, $empresaId, $config, $mapeamentoStatus, $acoesFormasPagamento, $statusPagamentoConfirmado, $contaReceberModel);
                    if ($resultado['sucesso']) {
                        $total++;
                        if ($resultado['novo']) $totalNovos++;
                        else $totalAtualizados++;
                    } else {
                        $erros[] = $resultado['erro'];
                    }
                }
            } else {
                // Sincronização em lote: busca pedidos paginados e pula os já importados
                $pagina = 1;
                $porPagina = 50; // Busca blocos de 50 por vez na API
                $maxPaginas = 20; // Limite de segurança para não ficar em loop infinito
                $continuarBuscando = true;
                
                // Se não há limite definido, usa o per_page original
                if ($limiteNovos <= 0) {
                    $limiteNovos = 0; // 0 = sem limite, importa tudo que encontrar
                }
                
                \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                    "Sincronização em lote - Limite de novos pedidos: " . ($limiteNovos > 0 ? $limiteNovos : 'sem limite'),
                    ['empresa_id' => $empresaId]);
                
                while ($continuarBuscando && $pagina <= $maxPaginas) {
                    // Busca uma página de pedidos do WooCommerce
                    $opcoesPage = $opcoes;
                    $opcoesPage['limite'] = $porPagina;
                    $opcoesPage['pagina'] = $pagina;
                    
                    $pedidos = $this->buscarPedidosWooCommerce($config, $opcoesPage);
                    
                    if (empty($pedidos)) {
                        \App\Models\LogSistema::debug('WooCommerce', 'sincronizarPedidos', 
                            "Página {$pagina}: nenhum pedido retornado, encerrando busca.");
                        break;
                    }
                    
                    \App\Models\LogSistema::debug('WooCommerce', 'sincronizarPedidos', 
                        "Página {$pagina}: " . count($pedidos) . " pedidos recebidos da API");
                    
                    foreach ($pedidos as $pedWoo) {
                        $numeroPedido = $pedWoo['number'] ?? $pedWoo['id'];
                        
                        // Verifica se pedido já existe no sistema
                        $pedidoExistente = $this->buscarPedidoExistente($numeroPedido, $empresaId, $pedWoo['id']);
                        
                        if ($pedidoExistente) {
                            $totalPulados++;
                            \App\Models\LogSistema::debug('WooCommerce', 'sincronizarPedidos', 
                                "Pedido #{$numeroPedido}: já importado (ID: {$pedidoExistente['id']}), pulando...");
                            continue;
                        }
                        
                        // Pedido novo: processar
                        $resultado = $this->processarPedidoWoo($pedWoo, $empresaId, $config, $mapeamentoStatus, $acoesFormasPagamento, $statusPagamentoConfirmado, $contaReceberModel);
                        if ($resultado['sucesso']) {
                            $total++;
                            $totalNovos++;
                            
                            // Se atingiu o limite de novos, para
                            if ($limiteNovos > 0 && $totalNovos >= $limiteNovos) {
                                \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                                    "Limite de {$limiteNovos} pedidos novos atingido, encerrando.");
                                $continuarBuscando = false;
                                break;
                            }
                        } else {
                            $erros[] = $resultado['erro'];
                        }
                    }
                    
                    // Se retornou menos que o solicitado, não há mais páginas
                    if (count($pedidos) < $porPagina) {
                        $continuarBuscando = false;
                    }
                    
                    $pagina++;
                }
            }
        } catch (\Exception $e) {
            $erros[] = "Erro ao buscar pedidos: " . $e->getMessage();
            \App\Models\LogSistema::error('WooCommerce', 'sincronizarPedidos', 
                "Erro geral ao buscar pedidos: " . $e->getMessage());
        }
        
        \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
            "Sincronização concluída: {$totalNovos} novos, {$totalAtualizados} atualizados, {$totalPulados} pulados (já existiam), " . count($erros) . " erros");
        
        return ['total' => $total, 'novos' => $totalNovos, 'atualizados' => $totalAtualizados, 'pulados' => $totalPulados, 'erros' => $erros];
    }
    
    /**
     * Processa um pedido individual do WooCommerce (criar ou atualizar)
     * Retorna ['sucesso' => bool, 'novo' => bool, 'erro' => string|null]
     */
    private function processarPedidoWoo($pedWoo, $empresaId, $config, $mapeamentoStatus, $acoesFormasPagamento, $statusPagamentoConfirmado, $contaReceberModel)
    {
        try {
            $numeroPedido = $pedWoo['number'] ?? $pedWoo['id'];
            
            \App\Models\LogSistema::debug('WooCommerce', 'sincronizarPedidos', 
                "=== Processando pedido #{$numeroPedido} ===",
                [
                    'woo_id' => $pedWoo['id'],
                    'status' => $pedWoo['status'],
                    'total' => $pedWoo['total'],
                    'payment_method' => $pedWoo['payment_method'] ?? 'N/A',
                    'cliente' => ($pedWoo['billing']['first_name'] ?? '') . ' ' . ($pedWoo['billing']['last_name'] ?? ''),
                    'itens' => count($pedWoo['line_items'] ?? [])
                ]);
            
            // =============================================
            // PASSO 1: BUSCAR OU CRIAR CLIENTE
            // =============================================
            $metaData = $pedWoo['meta_data'] ?? [];
            $clienteId = $this->buscarOuCriarCliente($pedWoo['billing'], $empresaId, $metaData);
            
            \App\Models\LogSistema::debug('WooCommerce', 'sincronizarPedidos', 
                "Pedido #{$numeroPedido}: cliente_id = {$clienteId}");
            
            // =============================================
            // PASSO 2: MAPEAR STATUS
            // =============================================
            $statusWoo = $pedWoo['status'];
            $statusSistema = $mapeamentoStatus['wc-' . $statusWoo] 
                ?? $mapeamentoStatus[$statusWoo] 
                ?? $this->mapearStatus($statusWoo);
            
            // =============================================
            // PASSO 3: IDENTIFICAR FORMA DE PAGAMENTO
            // =============================================
            $formaPagamentoWoo = $pedWoo['payment_method'] ?? '';
            $formaPagamentoTitulo = $pedWoo['payment_method_title'] ?? $formaPagamentoWoo;
            $acaoFormaPgto = $acoesFormasPagamento[$formaPagamentoWoo] ?? [];
            
            // =============================================
            // PASSO 4: CRIAR OU ATUALIZAR PEDIDO
            // =============================================
            $dados = [
                'empresa_id' => $empresaId,
                'cliente_id' => $clienteId,
                'origem' => 'woocommerce',
                'origem_id' => $pedWoo['id'],
                'numero_pedido' => $numeroPedido,
                'data_pedido' => date('Y-m-d H:i:s', strtotime($pedWoo['date_created'])),
                'status' => $statusSistema,
                'valor_total' => $pedWoo['total'],
                'frete' => $pedWoo['shipping_total'] ?? 0,
                'desconto' => $pedWoo['discount_total'] ?? 0,
                'observacoes' => "Pagamento: {$formaPagamentoTitulo}",
                'dados_origem' => $pedWoo
            ];
            
            // Verifica se pedido já existe
            $pedidoExistente = $this->buscarPedidoExistente($numeroPedido, $empresaId, $pedWoo['id']);
            $isNovo = false;
            
            if ($pedidoExistente) {
                // Atualiza pedido existente
                $this->pedidoModel->update($pedidoExistente['id'], $dados);
                $pedidoId = $pedidoExistente['id'];
                
                \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                    "Pedido #{$numeroPedido}: atualizado (ID: {$pedidoId})",
                    ['status_anterior' => $pedidoExistente['status'], 'status_novo' => $statusSistema]);
                
                // Se status mudou, verifica baixa automática
                if ($pedidoExistente['status'] !== $statusSistema && empty($acaoFormaPgto['nao_criar_receita'])) {
                    $this->verificarBaixaAutomatica(
                        $pedidoId,
                        $statusSistema,
                        $acaoFormaPgto,
                        $statusPagamentoConfirmado,
                        $pedWoo,
                        $empresaId,
                        $contaReceberModel
                    );
                }
            } else {
                // Cria pedido novo
                $pedidoId = $this->pedidoModel->create($dados);
                $isNovo = true;
                
                if (!$pedidoId) {
                    throw new \Exception("Falha ao criar pedido no banco de dados");
                }
                
                \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                    "Pedido #{$numeroPedido}: criado (ID: {$pedidoId})",
                    ['cliente_id' => $clienteId, 'status' => $statusSistema, 'valor' => $pedWoo['total']]);
            }
            
            // =============================================
            // PASSO 5: PROCESSAR PRODUTOS/ITENS DO PEDIDO
            // =============================================
            $lineItems = $pedWoo['line_items'] ?? [];
            \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                "Pedido #{$numeroPedido}: line_items encontrados: " . count($lineItems));
            
            if (!empty($lineItems)) {
                try {
                    $qtdProcessados = $this->processarItensDoPedido($pedidoId, $lineItems, $empresaId, $numeroPedido, $config);
                    \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                        "Pedido #{$numeroPedido}: {$qtdProcessados} itens processados com sucesso");
                } catch (\Throwable $eItens) {
                    \App\Models\LogSistema::error('WooCommerce', 'sincronizarPedidos', 
                        "Pedido #{$numeroPedido}: ERRO ao processar itens: " . $eItens->getMessage(),
                        ['trace' => $eItens->getTraceAsString()]);
                }
            } else {
                \App\Models\LogSistema::warning('WooCommerce', 'sincronizarPedidos', 
                    "Pedido #{$numeroPedido}: NENHUM line_item no pedido WooCommerce!");
            }
            
            // =============================================
            // PASSO 6: CONTA A RECEBER (apenas para pedidos novos)
            // =============================================
            if ($isNovo) {
                if (!empty($acaoFormaPgto['nao_criar_receita'])) {
                    \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                        "Pedido #{$numeroPedido}: conta a receber NÃO criada (forma pgto '{$formaPagamentoTitulo}' configurada como 'não criar receita')"
                    );
                } else {
                    $this->criarContaReceberDoPedido(
                        $pedidoId,
                        $pedWoo,
                        $empresaId,
                        $clienteId,
                        $statusSistema,
                        $acaoFormaPgto,
                        $statusPagamentoConfirmado,
                        $contaReceberModel
                    );
                }
            }
            
            \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                "Pedido #{$numeroPedido}: sincronizado com sucesso! " .
                "(Cliente: #{$clienteId}, Itens: " . count($lineItems) . ", Status: {$statusSistema})");
            
            return ['sucesso' => true, 'novo' => $isNovo, 'erro' => null];
            
        } catch (\Exception $e) {
            $numPed = $pedWoo['number'] ?? $pedWoo['id'] ?? '?';
            $msgErro = "Pedido {$numPed}: " . $e->getMessage();
            \App\Models\LogSistema::error('WooCommerce', 'sincronizarPedidos', 
                "Erro pedido #{$numPed}: " . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
            return ['sucesso' => false, 'novo' => false, 'erro' => $msgErro];
        }
    }
    
    /**
     * Processa os itens (line_items) de um pedido WooCommerce
     * 
     * Para cada item:
     * 1. Busca produto no sistema por SKU ou cria novo
     * 2. Cria o item do pedido vinculado ao produto
     */
    private function processarItensDoPedido($pedidoId, $lineItems, $empresaId, $numeroPedido, $config = null)
    {
        \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
            "=== INICIO processarItensDoPedido #{$numeroPedido} === pedido_id={$pedidoId}, itens=" . count($lineItems));
        
        // Verifica se o model de itens está disponível
        if (!$this->pedidoItemModel) {
            try {
                $this->pedidoItemModel = new \App\Models\PedidoItem();
            } catch (\Throwable $e) {
                \App\Models\LogSistema::error('WooCommerce', 'processarItens', 
                    "Falha ao instanciar PedidoItem: " . $e->getMessage());
                return 0;
            }
        }
        
        // Remove itens antigos deste pedido (para atualização)
        try {
            $itensExistentes = $this->pedidoItemModel->findByPedido($pedidoId);
            if (!empty($itensExistentes)) {
                $this->pedidoItemModel->deleteByPedido($pedidoId);
                \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                    "Pedido #{$numeroPedido}: removidos " . count($itensExistentes) . " itens antigos para recriar");
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'processarItens', 
                "Pedido #{$numeroPedido}: erro ao limpar itens antigos: " . $e->getMessage());
        }
        
        $totalItens = 0;
        $custoTotalPedido = 0;
        
        foreach ($lineItems as $item) {
            try {
                $sku = $item['sku'] ?? '';
                $nomeProduto = $item['name'] ?? 'Produto WooCommerce';
                $quantidade = floatval($item['quantity'] ?? 1);
                $precoUnitario = floatval($item['price'] ?? 0);
                $precoTotal = floatval($item['total'] ?? ($precoUnitario * $quantidade));
                $produtoWooId = $item['product_id'] ?? null;
                $variacaoId = $item['variation_id'] ?? null;
                
                // =============================================
                // BUSCAR CUSTO DO PRODUTO (ordem de prioridade)
                // 1. cod_fornecedor + tabela custo_produtos_personizi
                // 2. Campo personalizado configurado na integração
                // 3. _supplier_cost_from_acf (meta fixo)
                // =============================================
                $custoUnitario = 0;
                $custoOrigem = 'nenhum';
                $itemMetaData = $item['meta_data'] ?? [];
                $campoCusto = $config['campo_custo_produto'] ?? null;
                
                \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                    "Pedido #{$numeroPedido}: '{$nomeProduto}' -> iniciando busca de custo (WOO ID: {$produtoWooId}, campo_custo_config: " . ($campoCusto ?: 'NÃO CONFIGURADO') . ", meta_data no item: " . count($itemMetaData) . ")");
                
                // Prioridade 1: cod_fornecedor + tabela custo_produtos_personizi
                $codFornecedor = $this->extrairCodFornecedor($item);
                
                if (empty($codFornecedor) && $produtoWooId && $config) {
                    $codFornecedor = $this->buscarCodFornecedorDoProdutoWoo($produtoWooId, $config);
                    
                    if (empty($codFornecedor) && $variacaoId) {
                        $codFornecedor = $this->buscarCodFornecedorDoProdutoWoo($variacaoId, $config);
                    }
                }
                
                if (!empty($codFornecedor)) {
                    \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                        "Pedido #{$numeroPedido}: [1/3] Tentando cod_fornecedor='{$codFornecedor}' na tabela custo_produtos_personizi...");
                    $custoEncontrado = $this->buscarCustoPorCodFornecedor($codFornecedor);
                    if ($custoEncontrado !== null) {
                        $custoUnitario = $custoEncontrado;
                        $custoOrigem = 'cod_fornecedor';
                        \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: '{$nomeProduto}' custo=R\${$custoUnitario} (via cod_fornecedor '{$codFornecedor}')");
                    } else {
                        \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: [1/3] cod_fornecedor='{$codFornecedor}': NÃO encontrado na tabela");
                    }
                } else {
                    \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                        "Pedido #{$numeroPedido}: [1/3] Pulado - sem cod_fornecedor");
                }
                
                // Prioridade 2: Campo personalizado configurado na integração
                if ($custoUnitario == 0) {
                    if (!empty($campoCusto)) {
                        \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: [2/3] Tentando campo personalizado '{$campoCusto}' no line_item...");
                        $custoCustom = $this->buscarCustoPorCampoPersonalizado($itemMetaData, $campoCusto);
                        
                        if ($custoCustom === null && $produtoWooId && $config) {
                            \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                                "Pedido #{$numeroPedido}: [2/3] Não encontrado no line_item, buscando via API produto #{$produtoWooId}...");
                            $custoCustom = $this->buscarCustoProdutoWooViaApi($produtoWooId, $config, $campoCusto);
                        }
                        
                        if ($custoCustom !== null && $custoCustom > 0) {
                            $custoUnitario = $custoCustom;
                            $custoOrigem = "campo_personalizado ({$campoCusto})";
                            \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
                                "Pedido #{$numeroPedido}: '{$nomeProduto}' custo=R\${$custoUnitario} (via campo personalizado '{$campoCusto}')");
                        } else {
                            \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                                "Pedido #{$numeroPedido}: [2/3] Campo personalizado '{$campoCusto}': NÃO encontrado");
                        }
                    } else {
                        \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: [2/3] Pulado - campo personalizado NÃO configurado na integração");
                    }
                }
                
                // Prioridade 3: _supplier_cost_from_acf (meta fixo)
                if ($custoUnitario == 0) {
                    \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                        "Pedido #{$numeroPedido}: [3/3] Tentando _supplier_cost_from_acf no line_item...");
                    $custoSupplier = $this->buscarCustoPorCampoPersonalizado($itemMetaData, '_supplier_cost_from_acf');
                    if ($custoSupplier === null && $produtoWooId && $config) {
                        \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: [3/3] Não encontrado no line_item, buscando via API produto #{$produtoWooId}...");
                        $custoSupplier = $this->buscarCustoProdutoWooViaApi($produtoWooId, $config, '_supplier_cost_from_acf');
                    }
                    if ($custoSupplier !== null && $custoSupplier > 0) {
                        $custoUnitario = $custoSupplier;
                        $custoOrigem = '_supplier_cost_from_acf';
                        \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: '{$nomeProduto}' custo=R\${$custoUnitario} (via _supplier_cost_from_acf)");
                    } else {
                        \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: [3/3] _supplier_cost_from_acf: NÃO encontrado");
                    }
                }
                
                if ($custoUnitario == 0) {
                    \App\Models\LogSistema::warning('WooCommerce', 'processarItens', 
                        "Pedido #{$numeroPedido}: '{$nomeProduto}' CUSTO R\$0 - nenhuma fonte encontrou custo. Tentativas: [1] cod_fornecedor: " . ($codFornecedor ?: 'N/A') . ", [2] campo_custo: " . ($campoCusto ?: 'NÃO CONFIG') . ", [3] _supplier_cost_from_acf");
                }
                
                $custoTotal = round($custoUnitario * $quantidade, 2);
                $custoTotalPedido += $custoTotal;
                
                // =============================================
                // BUSCA OU CRIA O PRODUTO NO SISTEMA
                // =============================================
                $produtoId = null;
                
                // 1. Busca por SKU (incluindo inativos, para evitar duplicidade)
                if (!empty($sku)) {
                    $db = \App\Core\Database::getInstance()->getConnection();
                    $sql = "SELECT id, custo_unitario, ativo FROM produtos WHERE sku = :sku AND empresa_id = :empresa_id LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['sku' => $sku, 'empresa_id' => $empresaId]);
                    $produtoExistente = $stmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($produtoExistente) {
                        $produtoId = $produtoExistente['id'];
                        
                        // Reativa se estava inativo
                        if (empty($produtoExistente['ativo'])) {
                            $db->prepare("UPDATE produtos SET ativo = 1 WHERE id = :id")->execute(['id' => $produtoId]);
                        }
                        
                        // Atualiza custo se era zero
                        if ($custoUnitario > 0 && (empty($produtoExistente['custo_unitario']) || $produtoExistente['custo_unitario'] == 0)) {
                            try {
                                $sqlUp = "UPDATE produtos SET custo_unitario = :custo WHERE id = :id";
                                $stmtUp = $db->prepare($sqlUp);
                                $stmtUp->execute(['custo' => $custoUnitario, 'id' => $produtoId]);
                            } catch (\Throwable $e) { }
                        }
                    }
                }
                
                // 2. Busca por nome se não encontrou por SKU
                if (!$produtoId && !empty($nomeProduto)) {
                    $db = \App\Core\Database::getInstance()->getConnection();
                    $sql = "SELECT id, custo_unitario FROM produtos WHERE nome = :nome AND empresa_id = :empresa_id AND ativo = 1 LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['nome' => $nomeProduto, 'empresa_id' => $empresaId]);
                    $produtoEncontrado = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($produtoEncontrado) {
                        $produtoId = $produtoEncontrado['id'];
                        
                        if ($custoUnitario > 0 && (empty($produtoEncontrado['custo_unitario']) || $produtoEncontrado['custo_unitario'] == 0)) {
                            try {
                                $sqlUp = "UPDATE produtos SET custo_unitario = :custo WHERE id = :id";
                                $stmtUp = $db->prepare($sqlUp);
                                $stmtUp->execute(['custo' => $custoUnitario, 'id' => $produtoId]);
                            } catch (\Throwable $e) { }
                        }
                    }
                }
                
                // 3. Cria produto se não existe
                if (!$produtoId) {
                    $codigoProduto = $sku ?: 'WOO-' . ($produtoWooId ?: uniqid());
                    
                    $dadosProduto = [
                        'empresa_id' => $empresaId,
                        'categoria_id' => $this->getCategoriaVendaId($empresaId),
                        'codigo' => $codigoProduto,
                        'sku' => $sku ?: null,
                        'codigo_barras' => null,
                        'nome' => $nomeProduto,
                        'descricao' => null,
                        'custo_unitario' => $custoUnitario,
                        'preco_venda' => $precoUnitario,
                        'unidade_medida' => 'UN',
                        'estoque' => 0,
                        'estoque_minimo' => 0,
                        'cod_fornecedor' => $codFornecedor ?: null,
                    ];
                    
                    try {
                        $produtoId = $this->produtoModel->create($dadosProduto);
                    } catch (\Throwable $e) {
                        // Se deu erro de duplicidade, busca o produto existente
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false && !empty($sku)) {
                            $db = \App\Core\Database::getInstance()->getConnection();
                            $sql = "SELECT id FROM produtos WHERE sku = :sku AND empresa_id = :empresa_id LIMIT 1";
                            $stmt = $db->prepare($sql);
                            $stmt->execute(['sku' => $sku, 'empresa_id' => $empresaId]);
                            $produtoId = $stmt->fetchColumn();
                            
                            \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                                "Pedido #{$numeroPedido}: produto já existia (SKU duplicado '{$sku}') -> ID #{$produtoId}");
                        } else {
                            throw $e;
                        }
                    }
                    
                    if ($produtoId) {
                        \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: produto criado '{$nomeProduto}' (SKU: {$sku}, custo: R\${$custoUnitario}, cod_fornecedor: " . ($codFornecedor ?: 'N/A') . ") -> ID #{$produtoId}");
                        
                        // Salva imagem do produto se disponível
                        $imagemUrl = $item['image']['src'] ?? null;
                        if ($imagemUrl) {
                            $this->salvarImagemProduto($produtoId, $imagemUrl, $nomeProduto);
                        }
                    } else {
                        \App\Models\LogSistema::warning('WooCommerce', 'processarItens', 
                            "Pedido #{$numeroPedido}: falha ao criar produto '{$nomeProduto}'");
                    }
                }
                
                // Salva imagem se produto existia mas não tinha foto
                if ($produtoId && !empty($item['image']['src'])) {
                    try {
                        $fotoModel = new \App\Models\ProdutoFoto();
                        $fotoPrincipal = $fotoModel->findPrincipal($produtoId);
                        if (!$fotoPrincipal) {
                            $this->salvarImagemProduto($produtoId, $item['image']['src'], $nomeProduto);
                        }
                    } catch (\Throwable $e) {
                        // Não é crítico
                    }
                }
                
                // Salva cod_fornecedor no produto existente se não tinha
                if ($produtoId && !empty($codFornecedor)) {
                    try {
                        $db = \App\Core\Database::getInstance()->getConnection();
                        // Verifica se coluna existe
                        $sqlCheck = "SHOW COLUMNS FROM produtos LIKE 'cod_fornecedor'";
                        $stmtCheck = $db->prepare($sqlCheck);
                        $stmtCheck->execute();
                        if ($stmtCheck->rowCount() > 0) {
                            $sqlUp = "UPDATE produtos SET cod_fornecedor = :cod WHERE id = :id AND (cod_fornecedor IS NULL OR cod_fornecedor = '')";
                            $stmtUp = $db->prepare($sqlUp);
                            $stmtUp->execute(['cod' => $codFornecedor, 'id' => $produtoId]);
                        }
                    } catch (\Throwable $e) {
                        // Não é crítico
                    }
                }
                
                // =============================================
                // CRIA O ITEM DO PEDIDO
                // =============================================
                $codigoOrigem = $produtoWooId ? "WOO-{$produtoWooId}" : ($sku ?: null);
                if ($variacaoId) {
                    $codigoOrigem .= "/V{$variacaoId}";
                }
                
                $dadosItem = [
                    'pedido_id' => $pedidoId,
                    'produto_id' => $produtoId,
                    'codigo_produto_origem' => $codigoOrigem,
                    'nome_produto' => $nomeProduto,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $precoUnitario,
                    'valor_total' => $precoTotal,
                    'custo_unitario' => $custoUnitario,
                    'custo_total' => $custoTotal,
                ];
                
                $itemId = $this->pedidoItemModel->create($dadosItem);
                
                if ($itemId) {
                    $totalItens++;
                    \App\Models\LogSistema::debug('WooCommerce', 'processarItens', 
                        "Pedido #{$numeroPedido}: item '{$nomeProduto}' x{$quantidade} venda=R\${$precoTotal} custo=R\${$custoTotal} (cod_fornecedor: " . ($codFornecedor ?: 'N/A') . ")");
                }
                
            } catch (\Throwable $e) {
                \App\Models\LogSistema::error('WooCommerce', 'processarItens', 
                    "Pedido #{$numeroPedido}: erro ao processar item '{$nomeProduto}': " . $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]);
            }
        }
        
        // Atualiza custo total do pedido
        if ($custoTotalPedido > 0) {
            try {
                $db = \App\Core\Database::getInstance()->getConnection();
                $sql = "UPDATE pedidos_vinculados SET valor_custo_total = :custo WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['custo' => $custoTotalPedido, 'id' => $pedidoId]);
                \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
                    "Pedido #{$numeroPedido}: custo total atualizado para R\${$custoTotalPedido}");
            } catch (\Throwable $e) {
                \App\Models\LogSistema::warning('WooCommerce', 'processarItens', 
                    "Pedido #{$numeroPedido}: erro ao atualizar custo total: " . $e->getMessage());
            }
        }
        
        \App\Models\LogSistema::info('WooCommerce', 'processarItens', 
            "Pedido #{$numeroPedido}: {$totalItens}/" . count($lineItems) . " itens processados, custo total: R\${$custoTotalPedido}");
        
        return $totalItens;
    }
    
    /**
     * Cria conta a receber a partir de um pedido WooCommerce
     * 
     * Lógica:
     * - Sempre cria a conta a receber como PENDENTE
     * - Só dá baixa automática se:
     *   1. A forma de pagamento está marcada como "baixar_automaticamente"
     *   2. E o status do pedido indica pagamento confirmado (processando/concluido)
     * - Se "criar_parcelas" estiver ativo, divide em parcelas
     */
    private function criarContaReceberDoPedido(
        $pedidoId, $pedWoo, $empresaId, $clienteId, 
        $statusSistema, $acaoFormaPgto, $statusPagamentoConfirmado,
        $contaReceberModel
    ) {
        $valorTotal = floatval($pedWoo['total']);
        $formaPagamentoWoo = $pedWoo['payment_method'] ?? '';
        $formaPagamentoTitulo = $pedWoo['payment_method_title'] ?? $formaPagamentoWoo;
        $dataPedido = date('Y-m-d', strtotime($pedWoo['date_created']));
        
        // ID da forma de pagamento no sistema (se vinculada)
        $formaPagamentoId = !empty($acaoFormaPgto['forma_pagamento_id']) 
            ? $acaoFormaPgto['forma_pagamento_id'] 
            : null;
        
        // Verifica se deve criar parcelas
        $criarParcelas = !empty($acaoFormaPgto['criar_parcelas']);
        $numeroParcelas = $acaoFormaPgto['numero_parcelas'] ?? 1;
        
        // Se "auto", usa 1 parcela (WooCommerce não informa parcelas)
        if ($numeroParcelas === 'auto' || !is_numeric($numeroParcelas)) {
            $numeroParcelas = 1;
        }
        $numeroParcelas = max(1, intval($numeroParcelas));
        
        // Verifica se deve dar baixa automática
        $devedarBaixa = !empty($acaoFormaPgto['baixar_automaticamente']) 
            && in_array($statusSistema, $statusPagamentoConfirmado);
        
        // Observações
        $observacoes = "Pedido WooCommerce #{$pedWoo['number']}";
        $observacoes .= " | Pagamento: {$formaPagamentoTitulo}";
        if (!empty($acaoFormaPgto['observacoes'])) {
            $observacoes .= " | " . $acaoFormaPgto['observacoes'];
        }
        
        if ($criarParcelas && $numeroParcelas > 1) {
            // === CRIAR MÚLTIPLAS PARCELAS ===
            $valorPrimeiraParcela = null;
            if (!empty($acaoFormaPgto['valor_primeira_parcela'])) {
                $valorPrimeiraConfig = $acaoFormaPgto['valor_primeira_parcela'];
                if (strpos($valorPrimeiraConfig, '%') !== false) {
                    $percentual = floatval(str_replace('%', '', $valorPrimeiraConfig));
                    $valorPrimeiraParcela = round($valorTotal * ($percentual / 100), 2);
                } else {
                    $valorPrimeiraParcela = floatval($valorPrimeiraConfig);
                }
            }
            
            $baixarPrimeiraParcela = !empty($acaoFormaPgto['baixar_primeira_parcela'])
                && in_array($statusSistema, $statusPagamentoConfirmado);
            
            for ($i = 1; $i <= $numeroParcelas; $i++) {
                // Calcula valor da parcela
                if ($i === 1 && $valorPrimeiraParcela !== null) {
                    $valorParcela = $valorPrimeiraParcela;
                } elseif ($i === 1 && $valorPrimeiraParcela !== null) {
                    $valorParcela = $valorPrimeiraParcela;
                } else {
                    $valorRestante = $valorPrimeiraParcela !== null 
                        ? $valorTotal - $valorPrimeiraParcela 
                        : $valorTotal;
                    $parcelasRestantes = $valorPrimeiraParcela !== null 
                        ? $numeroParcelas - 1 
                        : $numeroParcelas;
                    $valorParcela = round($valorRestante / max(1, $parcelasRestantes), 2);
                }
                
                // Data de vencimento (parcela 1 = data do pedido, demais +30 dias cada)
                $dataVencimento = date('Y-m-d', strtotime($dataPedido . ' + ' . (($i - 1) * 30) . ' days'));
                
                // Status e valor recebido da parcela
                $statusParcela = 'pendente';
                $valorRecebido = 0;
                
                // Primeira parcela: baixar se configurado
                if ($i === 1 && $baixarPrimeiraParcela) {
                    $statusParcela = 'recebido';
                    $valorRecebido = $valorParcela;
                }
                // Se baixa automática total (não é parcela, é tudo)
                if ($devedarBaixa && !$criarParcelas) {
                    $statusParcela = 'recebido';
                    $valorRecebido = $valorParcela;
                }
                
                $dadosConta = [
                    'empresa_id' => $empresaId,
                    'cliente_id' => $clienteId,
                    'categoria_id' => $this->getCategoriaFinanceiraVendaId($empresaId),
                    'centro_custo_id' => null,
                    'numero_documento' => "WOO-{$pedWoo['number']}/{$i}",
                    'descricao' => "Pedido #{$pedWoo['number']} - Parcela {$i}/{$numeroParcelas}",
                    'valor_total' => $valorParcela,
                    'valor_recebido' => $valorRecebido,
                    'data_emissao' => $dataPedido,
                    'data_competencia' => $dataPedido,
                    'data_vencimento' => $dataVencimento,
                    'status' => $statusParcela,
                    'observacoes' => $observacoes,
                    'usuario_cadastro_id' => 1, // Sistema
                    'forma_recebimento_id' => $formaPagamentoId,
                    'pedido_id' => $pedidoId,
                    'numero_parcelas' => $numeroParcelas,
                    'parcela_atual' => $i,
                ];
                
                if ($statusParcela === 'recebido') {
                    $dadosConta['data_recebimento'] = $dataPedido;
                }
                
                $contaReceberModel->create($dadosConta);
            }
            
            \App\Models\LogSistema::info('WooCommerce', 'criarContaReceber', 
                "Pedido #{$pedWoo['number']}: {$numeroParcelas} parcelas criadas" .
                ($baixarPrimeiraParcela ? ' (1ª parcela baixada)' : ''),
                ['forma_pgto' => $formaPagamentoTitulo, 'valor' => $valorTotal]
            );
            
        } else {
            // === PARCELA ÚNICA ===
            $statusConta = 'pendente';
            $valorRecebido = 0;
            
            // Só dá baixa se forma de pagamento marcada E status confirma pagamento
            if ($devedarBaixa) {
                $statusConta = 'recebido';
                $valorRecebido = $valorTotal;
            }
            
            $dadosConta = [
                'empresa_id' => $empresaId,
                'cliente_id' => $clienteId,
                'categoria_id' => $this->getCategoriaFinanceiraVendaId($empresaId),
                'centro_custo_id' => null,
                'numero_documento' => "WOO-{$pedWoo['number']}",
                'descricao' => "Pedido WooCommerce #{$pedWoo['number']}",
                'valor_total' => $valorTotal,
                'valor_recebido' => $valorRecebido,
                'data_emissao' => $dataPedido,
                'data_competencia' => $dataPedido,
                'data_vencimento' => $dataPedido,
                'status' => $statusConta,
                'observacoes' => $observacoes,
                'usuario_cadastro_id' => 1, // Sistema
                'forma_recebimento_id' => $formaPagamentoId,
                'pedido_id' => $pedidoId,
            ];
            
            if ($statusConta === 'recebido') {
                $dadosConta['data_recebimento'] = $dataPedido;
            }
            
            $contaReceberModel->create($dadosConta);
            
            \App\Models\LogSistema::info('WooCommerce', 'criarContaReceber', 
                "Pedido #{$pedWoo['number']}: conta a receber criada como '{$statusConta}'",
                ['forma_pgto' => $formaPagamentoTitulo, 'valor' => $valorTotal, 'baixa_auto' => $devedarBaixa]
            );
        }
    }
    
    /**
     * Verifica e aplica baixa automática quando status de pedido muda
     * 
     * Cenário: pedido já existia como pendente, e agora o status mudou
     * para "processando" ou "concluido". Se a forma de pagamento está
     * configurada para baixa automática, dá a baixa agora.
     */
    private function verificarBaixaAutomatica(
        $pedidoId, $statusSistema, $acaoFormaPgto, 
        $statusPagamentoConfirmado, $pedWoo, $empresaId,
        $contaReceberModel
    ) {
        // Só processa se forma de pagamento está marcada para baixa automática
        if (empty($acaoFormaPgto['baixar_automaticamente'])) {
            return;
        }
        
        // Só processa se o status agora indica pagamento confirmado
        if (!in_array($statusSistema, $statusPagamentoConfirmado)) {
            return;
        }
        
        // Busca contas a receber vinculadas a este pedido que ainda estão pendentes
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "SELECT id, valor_total, status FROM contas_receber 
                WHERE pedido_id = :pedido_id 
                AND status = 'pendente'
                AND deleted_at IS NULL";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['pedido_id' => $pedidoId]);
            $contasPendentes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $dataRecebimento = date('Y-m-d');
            
            foreach ($contasPendentes as $conta) {
                $contaReceberModel->atualizarRecebimento(
                    $conta['id'],
                    $conta['valor_total'],
                    $dataRecebimento,
                    'recebido'
                );
            }
            
            if (count($contasPendentes) > 0) {
                \App\Models\LogSistema::info('WooCommerce', 'baixaAutomatica', 
                    "Pedido #{$pedWoo['number']}: baixa automática em " . count($contasPendentes) . " conta(s)",
                    ['status_novo' => $statusSistema, 'forma_pgto' => $pedWoo['payment_method'] ?? '']
                );
            }
        } catch (\Exception $e) {
            \App\Models\LogSistema::error('WooCommerce', 'baixaAutomatica', 
                "Erro ao dar baixa automática pedido #{$pedWoo['number']}: " . $e->getMessage());
        }
    }
    
    /**
     * Extrai cod_fornecedor de um line_item do WooCommerce
     * 
     * O campo personalizado pode vir como:
     * - meta_data do line_item (campo personalizado do produto)
     * - meta_data do pedido
     */
    private function extrairCodFornecedor($item)
    {
        // 1. Busca nos meta_data do line_item
        if (!empty($item['meta_data']) && is_array($item['meta_data'])) {
            foreach ($item['meta_data'] as $meta) {
                $key = $meta['key'] ?? '';
                $value = $meta['value'] ?? '';
                
                if (in_array($key, ['cod_fornecedor', '_cod_fornecedor', 'codigo_fornecedor', '_codigo_fornecedor'])) {
                    if (!empty($value)) {
                        return $value;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Busca custo de produto na tabela custo_produtos_personizi pelo cod_fornecedor
     *
     * @param string $codFornecedor Código do fornecedor
     * @return float|null Custo do produto ou null se não encontrado
     */
    
    /**
     * Limpa prefixos de fornecedor do cod_fornecedor
     * Remove textos como XBZ, SPOT, ASIA que são identificadores do fornecedor
     * e não fazem parte do código real do produto
     * 
     * Ex: "XBZ08203" -> "08203", "SPOT-12345" -> "12345", "ASIA_99887" -> "99887"
     */
    private function limparCodFornecedor($cod)
    {
        if (empty($cod)) return $cod;
        
        $original = $cod;
        
        // Prefixos a remover (case insensitive)
        $prefixos = ['XBZ', 'SPOT', 'ASIA'];
        
        $codUpper = strtoupper(trim($cod));
        
        foreach ($prefixos as $prefixo) {
            if (strpos($codUpper, $prefixo) === 0) {
                // Remove o prefixo
                $cod = substr($cod, strlen($prefixo));
                // Remove separadores no início (-, _, espaço)
                $cod = ltrim($cod, '-_ ');
                break;
            }
        }
        
        $cod = trim($cod);
        
        if ($cod !== $original) {
            \App\Models\LogSistema::debug('WooCommerce', 'limparCodFornecedor', 
                "Código limpo: '{$original}' -> '{$cod}'");
        }
        
        return $cod;
    }
    
    /**
     * Busca custo do produto via campo personalizado (ACF ou meta_data) no WooCommerce
     * 
     * @param array $metaData Array de meta_data do produto ou line_item
     * @param string $campoCusto Meta key configurada (ex: acf[field_67210d632de40], _cost, etc.)
     * @return float|null O custo encontrado ou null
     */
    private function buscarCustoPorCampoPersonalizado($metaData, $campoCusto)
    {
        if (empty($metaData) || empty($campoCusto)) {
            return null;
        }
        
        // Possíveis variações da meta key para buscar
        $keysParaBuscar = [$campoCusto];
        
        // Se é formato ACF (acf[field_xxx]), também busca por _field_xxx e field_xxx
        if (preg_match('/^acf\[(.+)\]$/i', $campoCusto, $matches)) {
            $acfFieldKey = $matches[1];
            $keysParaBuscar[] = $acfFieldKey;
            $keysParaBuscar[] = '_' . $acfFieldKey;
        }
        
        // Também tenta com e sem underscore no início
        if (strpos($campoCusto, '_') !== 0) {
            $keysParaBuscar[] = '_' . $campoCusto;
        } else {
            $keysParaBuscar[] = ltrim($campoCusto, '_');
        }
        
        foreach ($metaData as $meta) {
            $key = $meta['key'] ?? '';
            if (in_array($key, $keysParaBuscar)) {
                $valor = $meta['value'] ?? null;
                if ($valor !== null && $valor !== '' && is_numeric($valor) && floatval($valor) > 0) {
                    \App\Models\LogSistema::info('WooCommerce', 'custoCampoPersonalizado', 
                        "Custo encontrado via campo '{$key}': R\${$valor}");
                    return floatval($valor);
                } else {
                    \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                        "Campo '{$key}' encontrado mas valor inválido/vazio: '" . ($valor ?? 'NULL') . "'");
                }
            }
        }
        
        return null;
    }
    
    /**
     * Busca custo do produto via campo personalizado diretamente na API do WooCommerce
     * Usado quando o campo não está no line_item (geralmente ACF fields só aparecem no produto)
     */
    private function buscarCustoProdutoWooViaApi($produtoWooId, $config, $campoCusto)
    {
        if (empty($produtoWooId) || empty($config) || empty($campoCusto)) {
            return null;
        }
        
        try {
            $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products/' . $produtoWooId;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $produto = json_decode($response, true);
                if ($produto && !empty($produto['meta_data'])) {
                    // Log das meta_keys disponíveis no produto para diagnóstico
                    $metaKeys = array_map(function($m) { 
                        $key = $m['key'] ?? '?';
                        $val = $m['value'] ?? '';
                        // Resumo do valor (máx 50 chars)
                        if (is_array($val)) $val = json_encode($val);
                        $val = is_string($val) ? substr($val, 0, 50) : $val;
                        return "{$key}=" . $val;
                    }, $produto['meta_data']);
                    
                    \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                        "Produto WOO #{$produtoWooId}: buscando '{$campoCusto}' | meta_keys disponíveis: " . implode(', ', $metaKeys));
                    
                    $custo = $this->buscarCustoPorCampoPersonalizado($produto['meta_data'], $campoCusto);
                    if ($custo !== null) {
                        \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                            "Custo R\${$custo} encontrado via API para produto WOO #{$produtoWooId}");
                        return $custo;
                    } else {
                        \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                            "Campo '{$campoCusto}' NÃO encontrado nos meta_data do produto WOO #{$produtoWooId}");
                    }
                } else {
                    \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                        "Produto WOO #{$produtoWooId}: sem meta_data na resposta da API");
                }
            } else {
                \App\Models\LogSistema::debug('WooCommerce', 'custoCampoPersonalizado', 
                    "Produto WOO #{$produtoWooId}: API retornou HTTP {$httpCode}");
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'custoCampoPersonalizado', 
                "Erro ao buscar custo via API para produto #{$produtoWooId}: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function buscarCustoPorCodFornecedor($codFornecedor)
    {
        if (empty($codFornecedor)) {
            return null;
        }
        
        // Limpa prefixos de fornecedor conhecidos (XBZ, SPOT, ASIA)
        $codFornecedor = $this->limparCodFornecedor($codFornecedor);
        
        if (empty($codFornecedor)) {
            return null;
        }
        
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            
            // 1. Busca exata
            $sql = "SELECT preco, cod_fornecedor FROM custo_produtos_personizi WHERE cod_fornecedor = :cod LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['cod' => $codFornecedor]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && isset($result['preco'])) {
                \App\Models\LogSistema::debug('WooCommerce', 'buscarCusto', 
                    "Custo encontrado (exato) para '{$codFornecedor}': R\$ {$result['preco']}");
                return floatval($result['preco']);
            }
            
            // 2. Busca parcial (LIKE) — ex: produto tem "08203", tabela tem "08203-PRETO"
            $sql2 = "SELECT preco, cod_fornecedor FROM custo_produtos_personizi WHERE cod_fornecedor LIKE :cod LIMIT 1";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute(['cod' => $codFornecedor . '%']);
            $result2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
            
            if ($result2 && isset($result2['preco'])) {
                \App\Models\LogSistema::debug('WooCommerce', 'buscarCusto', 
                    "Custo encontrado (LIKE '{$codFornecedor}%') -> '{$result2['cod_fornecedor']}': R\$ {$result2['preco']}");
                return floatval($result2['preco']);
            }
            
            // 3. Busca inversa — tabela tem "08203", produto tem "08203-PRETO"
            $sql3 = "SELECT preco, cod_fornecedor FROM custo_produtos_personizi WHERE :cod LIKE CONCAT(cod_fornecedor, '%') ORDER BY LENGTH(cod_fornecedor) DESC LIMIT 1";
            $stmt3 = $db->prepare($sql3);
            $stmt3->execute(['cod' => $codFornecedor]);
            $result3 = $stmt3->fetch(\PDO::FETCH_ASSOC);
            
            if ($result3 && isset($result3['preco'])) {
                \App\Models\LogSistema::debug('WooCommerce', 'buscarCusto', 
                    "Custo encontrado (inverso) '{$result3['cod_fornecedor']}' contido em '{$codFornecedor}': R\$ {$result3['preco']}");
                return floatval($result3['preco']);
            }
            
            \App\Models\LogSistema::debug('WooCommerce', 'buscarCusto', 
                "Custo NÃO encontrado para cod_fornecedor '{$codFornecedor}' (tentou exato + LIKE + inverso)");
            return null;
        } catch (\Throwable $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'buscarCusto', 
                "Erro ao buscar custo por cod_fornecedor '{$codFornecedor}': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Busca cod_fornecedor diretamente do produto no WooCommerce via API
     * Usado quando o line_item não contém o meta_data do produto
     */
    private function buscarCodFornecedorDoProdutoWoo($produtoWooId, $config)
    {
        if (empty($produtoWooId) || empty($config)) {
            return null;
        }
        
        try {
            $url = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/products/' . $produtoWooId;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $produto = json_decode($response, true);
                if ($produto && !empty($produto['meta_data'])) {
                    foreach ($produto['meta_data'] as $meta) {
                        $key = $meta['key'] ?? '';
                        if (in_array($key, ['cod_fornecedor', '_cod_fornecedor', 'codigo_fornecedor', '_codigo_fornecedor'])) {
                            if (!empty($meta['value'])) {
                                \App\Models\LogSistema::debug('WooCommerce', 'buscarCodFornecedor', 
                                    "cod_fornecedor encontrado no produto WOO #{$produtoWooId}: {$meta['value']}");
                                return $meta['value'];
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'buscarCodFornecedor', 
                "Erro ao buscar produto WOO #{$produtoWooId}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Busca categoria padrão de venda da empresa
     */
    /**
     * Salva imagem do produto WooCommerce como foto principal
     */
    private function salvarImagemProduto($produtoId, $imagemUrl, $nomeProduto = '')
    {
        try {
            if (empty($imagemUrl)) return;
            
            // Baixa a imagem
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $imagemUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($imageData)) {
                \App\Models\LogSistema::warning('WooCommerce', 'salvarImagem', 
                    "Falha ao baixar imagem do produto #{$produtoId}: HTTP {$httpCode}");
                return;
            }
            
            // Define extensão
            $extensao = 'jpg';
            if (strpos($contentType, 'png') !== false) $extensao = 'png';
            elseif (strpos($contentType, 'gif') !== false) $extensao = 'gif';
            elseif (strpos($contentType, 'webp') !== false) $extensao = 'webp';
            
            // Cria diretório
            $baseDir = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
            $uploadDir = $baseDir . '/public/uploads/produtos';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Salva arquivo
            $nomeArquivo = 'woo_' . $produtoId . '_' . time() . '.' . $extensao;
            $caminhoCompleto = $uploadDir . '/' . $nomeArquivo;
            file_put_contents($caminhoCompleto, $imageData);
            
            // Registra na tabela de fotos
            $fotoModel = new \App\Models\ProdutoFoto();
            $fotoModel->create([
                'produto_id' => $produtoId,
                'arquivo' => $nomeArquivo,
                'caminho' => '/uploads/produtos/' . $nomeArquivo,
                'tamanho' => strlen($imageData),
                'tipo' => $contentType,
                'principal' => 1,
                'ordem' => 0,
            ]);
            
            \App\Models\LogSistema::info('WooCommerce', 'salvarImagem', 
                "Imagem salva para produto #{$produtoId}: {$nomeArquivo}");
                
        } catch (\Throwable $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'salvarImagem', 
                "Erro ao salvar imagem do produto #{$produtoId}: " . $e->getMessage());
        }
    }
    
    private function getCategoriaVendaId($empresaId)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Busca categoria de PRODUTOS (tabela categorias_produtos, NÃO categorias_financeiras)
        // Tenta buscar categoria "WooCommerce" ou "Geral" ou qualquer uma
        $nomesBusca = ['WooCommerce', 'Woocommerce', 'Geral', 'Produtos', 'Vendas'];
        
        foreach ($nomesBusca as $nome) {
            try {
                $sql = "SELECT id FROM categorias_produtos 
                        WHERE empresa_id = :empresa_id 
                        AND nome LIKE :nome
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute(['empresa_id' => $empresaId, 'nome' => '%' . $nome . '%']);
                $result = $stmt->fetchColumn();
                if ($result) {
                    return $result;
                }
            } catch (\Throwable $e) {
                // continua tentando
            }
        }
        
        // Fallback: busca qualquer categoria de produtos da empresa
        try {
            $sql = "SELECT id FROM categorias_produtos 
                    WHERE empresa_id = :empresa_id 
                    ORDER BY id ASC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchColumn();
            if ($result) {
                return $result;
            }
        } catch (\Throwable $e) {
            // continua
        }
        
        // Último fallback: busca qualquer categoria de produtos
        try {
            $sql = "SELECT id FROM categorias_produtos ORDER BY id ASC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            if ($result) {
                return $result;
            }
        } catch (\Throwable $e) {
            // continua
        }
        
        // Se não existe nenhuma categoria, cria uma
        try {
            $sql = "INSERT INTO categorias_produtos (empresa_id, nome) VALUES (:empresa_id, 'WooCommerce')";
            $stmt = $db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $novoId = $db->lastInsertId();
            \App\Models\LogSistema::info('WooCommerce', 'getCategoriaVendaId', 
                "Categoria de produtos 'WooCommerce' criada automaticamente: ID #{$novoId}");
            return $novoId;
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'getCategoriaVendaId', 
                "Não foi possível encontrar/criar categoria de produtos: " . $e->getMessage());
            // Retorna NULL para que o produto seja criado sem categoria
            return null;
        }
    }
    
    /**
     * Busca categoria financeira padrão para contas a receber
     * (tabela categorias_financeiras, tipo 'receita')
     */
    private function getCategoriaFinanceiraVendaId($empresaId)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Tenta buscar categoria financeira de receita
        $nomesBusca = ['Vendas', 'Venda', 'WooCommerce', 'Receita', 'Receitas'];
        
        foreach ($nomesBusca as $nome) {
            try {
                $sql = "SELECT id FROM categorias_financeiras 
                        WHERE empresa_id = :empresa_id 
                        AND nome LIKE :nome
                        AND tipo = 'receita'
                        AND ativo = 1
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute(['empresa_id' => $empresaId, 'nome' => '%' . $nome . '%']);
                $result = $stmt->fetchColumn();
                if ($result) {
                    return $result;
                }
            } catch (\Throwable $e) {
                // continua tentando
            }
        }
        
        // Fallback: qualquer categoria financeira de receita da empresa
        try {
            $sql = "SELECT id FROM categorias_financeiras 
                    WHERE empresa_id = :empresa_id 
                    AND tipo = 'receita'
                    AND ativo = 1
                    ORDER BY id ASC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchColumn();
            if ($result) {
                return $result;
            }
        } catch (\Throwable $e) {
            // continua
        }
        
        // Fallback: qualquer categoria financeira da empresa
        try {
            $sql = "SELECT id FROM categorias_financeiras 
                    WHERE empresa_id = :empresa_id 
                    AND ativo = 1
                    ORDER BY id ASC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchColumn();
            if ($result) {
                return $result;
            }
        } catch (\Throwable $e) {
            // continua
        }
        
        // Último fallback: qualquer categoria financeira
        try {
            $sql = "SELECT id FROM categorias_financeiras WHERE ativo = 1 ORDER BY id ASC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            if ($result) {
                return $result;
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'getCategoriaFinanceiraVendaId', 
                "Nenhuma categoria financeira encontrada: " . $e->getMessage());
        }
        
        return null;
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
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception("Erro de conexão: {$curlError}");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}");
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Resposta inválida da API: " . json_last_error_msg());
        }
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Busca pedidos da API WooCommerce com filtros
     */
    private function buscarPedidosWooCommerce($config, $opcoes = [])
    {
        $baseUrl = rtrim($config['url_site'], '/') . '/wp-json/wc/v3/orders';
        
        // Se é pedido único, busca direto pelo ID
        if (!empty($opcoes['pedido_unico_id'])) {
            $pedidoId = trim($opcoes['pedido_unico_id']);
            $url = $baseUrl . '/' . $pedidoId;
            
            \App\Models\LogSistema::info('WooCommerce', 'buscarPedidos', 
                "Buscando pedido único: #{$pedidoId}");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $config['consumer_key'] . ':' . $config['consumer_secret']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new \Exception("Erro de conexão: {$curlError}");
            }
            
            if ($httpCode === 404) {
                throw new \Exception("Pedido #{$pedidoId} não encontrado no WooCommerce");
            }
            
            if ($httpCode !== 200) {
                throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}. Resposta: " . substr($response, 0, 200));
            }
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Resposta inválida da API: " . json_last_error_msg());
            }
            
            // Retorna como array de pedidos (1 único)
            return is_array($data) ? [$data] : [];
        }
        
        // Busca múltiplos pedidos
        $url = $baseUrl;
        $params = [];
        
        // Limite de registros por página
        if (isset($opcoes['limite']) && $opcoes['limite'] > 0) {
            $params[] = 'per_page=' . intval($opcoes['limite']);
        } else {
            $params[] = 'per_page=50';
        }
        
        // Paginação
        if (isset($opcoes['pagina']) && $opcoes['pagina'] > 1) {
            $params[] = 'page=' . intval($opcoes['pagina']);
        }
        
        // Ordenação: mais recentes primeiro
        $params[] = 'orderby=date';
        $params[] = 'order=desc';
        
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
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception("Erro de conexão: {$curlError}");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro na API WooCommerce. Código: {$httpCode}");
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Resposta inválida da API: " . json_last_error_msg());
        }
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Busca ou cria cliente
     */
    /**
     * Busca ou cria cliente a partir dos dados do pedido WooCommerce
     * 
     * Fluxo:
     * 1. Extrai CPF/CNPJ do billing e meta_data
     * 2. Busca por CPF/CNPJ
     * 3. Busca por email
     * 4. Se não encontrou, cria novo cliente
     * 
     * @param array $billing Dados de cobrança do WooCommerce
     * @param int $empresaId ID da empresa
     * @param array $metaData Meta dados do pedido (contém CPF/CNPJ em plugins BR)
     * @return int ID do cliente
     */
    private function buscarOuCriarCliente($billing, $empresaId, $metaData = [])
    {
        $email = $billing['email'] ?? '';
        $nome = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $telefone = $billing['phone'] ?? '';
        
        // Extrai CPF/CNPJ de múltiplas fontes possíveis
        $cpfCnpj = $this->extrairCpfCnpj($billing, $metaData);
        
        \App\Models\LogSistema::debug('WooCommerce', 'buscarOuCriarCliente', 
            "Processando cliente: nome={$nome}, email={$email}, cpf_cnpj={$cpfCnpj}, telefone={$telefone}",
            ['empresa_id' => $empresaId]);
        
        // 1. Busca por CPF/CNPJ se disponível
        if (!empty($cpfCnpj)) {
            $cliente = $this->clienteModel->findByCpfCnpj($cpfCnpj, $empresaId);
            if ($cliente) {
                \App\Models\LogSistema::info('WooCommerce', 'buscarOuCriarCliente', 
                    "Cliente encontrado por CPF/CNPJ: #{$cliente['id']} - {$cliente['nome_razao_social']}");
                return $cliente['id'];
            }
        }
        
        // 2. Busca por email
        if (!empty($email)) {
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT id, nome_razao_social FROM clientes WHERE email = :email AND empresa_id = :empresa_id AND ativo = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email, 'empresa_id' => $empresaId]);
            $clienteExistente = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($clienteExistente) {
                \App\Models\LogSistema::info('WooCommerce', 'buscarOuCriarCliente', 
                    "Cliente encontrado por email: #{$clienteExistente['id']} - {$clienteExistente['nome_razao_social']}");
                
                // Atualiza CPF/CNPJ se estava vazio e agora temos
                if (!empty($cpfCnpj)) {
                    $db2 = \App\Core\Database::getInstance()->getConnection();
                    $sqlUpdate = "UPDATE clientes SET cpf_cnpj = :cpf_cnpj WHERE id = :id AND (cpf_cnpj IS NULL OR cpf_cnpj = '')";
                    $stmtUpdate = $db2->prepare($sqlUpdate);
                    $stmtUpdate->execute(['cpf_cnpj' => $cpfCnpj, 'id' => $clienteExistente['id']]);
                }
                
                return $clienteExistente['id'];
            }
        }
        
        // 3. Busca por nome exato (último recurso antes de criar)
        if (!empty($nome)) {
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT id, nome_razao_social FROM clientes WHERE nome_razao_social = :nome AND empresa_id = :empresa_id AND ativo = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['nome' => $nome, 'empresa_id' => $empresaId]);
            $clienteExistente = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($clienteExistente) {
                \App\Models\LogSistema::info('WooCommerce', 'buscarOuCriarCliente', 
                    "Cliente encontrado por nome: #{$clienteExistente['id']} - {$clienteExistente['nome_razao_social']}");
                return $clienteExistente['id'];
            }
        }
        
        // 4. Cria cliente novo
        if (empty($nome)) {
            $nome = $email ?: 'Cliente WooCommerce';
        }
        
        $endereco = [
            'logradouro' => ($billing['address_1'] ?? '') . (!empty($billing['number']) ? ', ' . $billing['number'] : ''),
            'complemento' => $billing['address_2'] ?? '',
            'bairro' => $billing['neighborhood'] ?? '',
            'cidade' => $billing['city'] ?? '',
            'estado' => $billing['state'] ?? '',
            'cep' => $billing['postcode'] ?? '',
            'pais' => $billing['country'] ?? 'BR',
        ];
        
        try {
            $clienteId = $this->clienteModel->create([
                'empresa_id' => $empresaId,
                'tipo' => strlen(preg_replace('/\D/', '', $cpfCnpj)) > 11 ? 'juridica' : 'fisica',
                'nome_razao_social' => $nome,
                'cpf_cnpj' => $cpfCnpj ?: null,
                'email' => $email ?: null,
                'telefone' => $telefone ?: null,
                'endereco' => $endereco,
                'ativo' => 1
            ]);
            
            \App\Models\LogSistema::info('WooCommerce', 'buscarOuCriarCliente', 
                "Novo cliente criado: #{$clienteId} - {$nome}", 
                ['email' => $email, 'cpf_cnpj' => $cpfCnpj, 'telefone' => $telefone]);
            
            return $clienteId;
        } catch (\Exception $e) {
            \App\Models\LogSistema::error('WooCommerce', 'buscarOuCriarCliente', 
                "Erro ao criar cliente: " . $e->getMessage(), 
                ['nome' => $nome, 'email' => $email, 'trace' => $e->getTraceAsString()]);
            
            // NÃO retorna fallback 1. Lança exceção para parar e avisar
            throw new \Exception("Falha ao criar cliente '{$nome}': " . $e->getMessage());
        }
    }
    
    /**
     * Extrai CPF/CNPJ dos dados de billing e meta_data do WooCommerce
     * 
     * Plugins BR (Brazilian Market, Extra Checkout Fields, etc) colocam
     * CPF/CNPJ em diferentes campos e meta_data.
     */
    private function extrairCpfCnpj($billing, $metaData = [])
    {
        // 1. Campos diretos do billing (plugins populares)
        $campos = ['cpf', 'cnpj', 'persontype_cpf', 'persontype_cnpj', 
                    'billing_cpf', 'billing_cnpj', 'cpf_cnpj',
                    'billing_cpf_cnpj', 'document', 'billing_document'];
        
        foreach ($campos as $campo) {
            if (!empty($billing[$campo])) {
                $valor = preg_replace('/\D/', '', $billing[$campo]);
                if (strlen($valor) >= 11) {
                    return $billing[$campo];
                }
            }
        }
        
        // 2. Meta data do pedido (WooCommerce BR plugins)
        if (!empty($metaData) && is_array($metaData)) {
            $metaCampos = ['_billing_cpf', '_billing_cnpj', '_billing_cpf_cnpj',
                           'billing_cpf', 'billing_cnpj', '_cpf', '_cnpj',
                           '_billing_persontype', '_billing_number'];
            
            foreach ($metaData as $meta) {
                $key = $meta['key'] ?? '';
                $value = $meta['value'] ?? '';
                
                if (in_array($key, $metaCampos) && !empty($value)) {
                    $valor = preg_replace('/\D/', '', $value);
                    if (strlen($valor) >= 11) {
                        return $value;
                    }
                }
            }
        }
        
        return '';
    }
    
    /**
     * Busca pedido existente pelo número
     */
    private function buscarPedidoExistente($numeroPedido, $empresaId, $origemId = null)
    {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            
            // Busca por numero_pedido OU origem_id
            $sql = "SELECT * FROM pedidos_vinculados 
                    WHERE empresa_id = :empresa_id 
                    AND origem = 'woocommerce'
                    AND (numero_pedido = :numero OR origem_id = :origem_id)
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'numero' => $numeroPedido,
                'origem_id' => $origemId ?? $numeroPedido,
            ]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            \App\Models\LogSistema::warning('WooCommerce', 'buscarPedidoExistente', 
                "Erro ao buscar pedido #{$numeroPedido}: " . $e->getMessage());
            return null;
        }
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
        \App\Models\LogSistema::info('WooCommerce', 'webhook', 
            "=== WEBHOOK RECEBIDO === topic: {$topic}, integracao: {$integracaoId}",
            ['empresa_id' => $empresaId, 'data_id' => $data['id'] ?? 'N/A']);
        
        try {
            // Carrega config para ter acesso às chaves da API (necessário para buscar cod_fornecedor)
            $config = $this->woocommerceModel->findByIntegracaoId($integracaoId);
            
            switch ($topic) {
                case 'product.created':
                case 'product.updated':
                    return $this->processarWebhookProduto($data, $empresaId, $config);
                    
                case 'product.deleted':
                    return $this->processarWebhookProdutoDeletado($data, $empresaId);
                    
                case 'order.created':
                case 'order.updated':
                    return $this->processarWebhookPedido($data, $empresaId, $integracaoId, $config);
                    
                case 'order.deleted':
                    return $this->processarWebhookPedidoDeletado($data, $empresaId);
                    
                default:
                    \App\Models\LogSistema::info('WooCommerce', 'webhook', 
                        "Evento não tratado: {$topic}");
                    return ['sucesso' => true, 'mensagem' => 'Evento não tratado'];
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'webhook', 
                "Erro fatal no webhook ({$topic}): " . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de produto (created/updated)
     */
    private function processarWebhookProduto($prodWoo, $empresaId, $config = null)
    {
        try {
            $nome = $prodWoo['name'] ?? 'Produto sem nome';
            $sku = $prodWoo['sku'] ?? '';
            $produtoWooId = $prodWoo['id'] ?? null;
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                "Processando produto: '{$nome}' (SKU: {$sku}, WOO ID: {$produtoWooId})");
            
            // =============================================
            // BUSCAR CUSTO DO PRODUTO (ordem de prioridade)
            // 1. cod_fornecedor + tabela custo_produtos_personizi
            // 2. Campo personalizado configurado
            // 3. _supplier_cost_from_acf
            // =============================================
            $custoUnitario = 0;
            $prodMetaData = $prodWoo['meta_data'] ?? [];
            
            // Prioridade 1: cod_fornecedor + tabela custo_produtos_personizi
            $codFornecedor = null;
            if (!empty($prodMetaData)) {
                foreach ($prodMetaData as $meta) {
                    if (in_array($meta['key'] ?? '', ['cod_fornecedor', '_cod_fornecedor', 'codigo_fornecedor', '_codigo_fornecedor'])) {
                        $codFornecedor = $meta['value'] ?? null;
                        break;
                    }
                }
            }
            
            if (!empty($codFornecedor)) {
                $custoEncontrado = $this->buscarCustoPorCodFornecedor($codFornecedor);
                if ($custoEncontrado !== null) {
                    $custoUnitario = $custoEncontrado;
                    \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                        "Custo R\${$custoUnitario} encontrado via cod_fornecedor '{$codFornecedor}' para '{$nome}'");
                }
            }
            
            // Prioridade 2: Campo personalizado configurado na integração
            if ($custoUnitario == 0) {
                $campoCusto = $config['campo_custo_produto'] ?? null;
                if (!empty($campoCusto) && !empty($prodMetaData)) {
                    $custoCustom = $this->buscarCustoPorCampoPersonalizado($prodMetaData, $campoCusto);
                    if ($custoCustom !== null && $custoCustom > 0) {
                        $custoUnitario = $custoCustom;
                        \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                            "Custo R\${$custoUnitario} encontrado via campo personalizado '{$campoCusto}' para '{$nome}'");
                    }
                }
            }
            
            // Prioridade 3: _supplier_cost_from_acf
            if ($custoUnitario == 0 && !empty($prodMetaData)) {
                $custoSupplier = $this->buscarCustoPorCampoPersonalizado($prodMetaData, '_supplier_cost_from_acf');
                if ($custoSupplier !== null && $custoSupplier > 0) {
                    $custoUnitario = $custoSupplier;
                    \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                        "Custo R\${$custoUnitario} encontrado via _supplier_cost_from_acf para '{$nome}'");
                }
            }
            
            // Busca produto existente por SKU
            $produtoExistente = null;
            if (!empty($sku)) {
                $produtoExistente = $this->produtoModel->findBySku($sku, $empresaId);
            }
            
            $dados = [
                'empresa_id' => $empresaId,
                'categoria_id' => $this->getCategoriaVendaId($empresaId),
                'codigo' => $sku ?: 'WOO-' . ($produtoWooId ?: uniqid()),
                'sku' => $sku ?: null,
                'codigo_barras' => null,
                'nome' => $nome,
                'descricao' => strip_tags($prodWoo['description'] ?? ''),
                'custo_unitario' => $custoUnitario,
                'preco_venda' => floatval($prodWoo['price'] ?? 0),
                'unidade_medida' => 'UN',
                'estoque' => intval($prodWoo['stock_quantity'] ?? 0),
                'estoque_minimo' => 0,
            ];
            
            if ($produtoExistente) {
                $this->produtoModel->update($produtoExistente['id'], $dados);
                \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                    "Produto atualizado: #{$produtoExistente['id']} - {$nome} (custo: R\${$custoUnitario})");
                return ['sucesso' => true, 'mensagem' => "Produto #{$produtoExistente['id']} atualizado"];
            } else {
                $novoId = $this->produtoModel->create($dados);
                \App\Models\LogSistema::info('WooCommerce', 'webhookProduto', 
                    "Produto criado: #{$novoId} - {$nome} (custo: R\${$custoUnitario})");
                return ['sucesso' => true, 'mensagem' => "Produto #{$novoId} criado"];
            }
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'webhookProduto', 
                "Erro ao processar produto: " . $e->getMessage(),
                ['trace' => $e->getTraceAsString(), 'produto' => $prodWoo['name'] ?? 'N/A']);
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de produto deletado
     */
    private function processarWebhookProdutoDeletado($prodWoo, $empresaId)
    {
        try {
            $sku = $prodWoo['sku'] ?? '';
            $produtoWooId = $prodWoo['id'] ?? null;
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookProdutoDeletado', 
                "Desativando produto WOO #{$produtoWooId} (SKU: {$sku})");
            
            if (!empty($sku)) {
                $produtoExistente = $this->produtoModel->findBySku($sku, $empresaId);
                if ($produtoExistente) {
                    $db = \App\Core\Database::getInstance()->getConnection();
                    $sql = "UPDATE produtos SET ativo = 0 WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['id' => $produtoExistente['id']]);
                    
                    \App\Models\LogSistema::info('WooCommerce', 'webhookProdutoDeletado', 
                        "Produto #{$produtoExistente['id']} desativado");
                    return ['sucesso' => true, 'mensagem' => "Produto #{$produtoExistente['id']} desativado"];
                }
            }
            
            return ['sucesso' => true, 'mensagem' => 'Produto não encontrado no sistema'];
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'webhookProdutoDeletado', 
                "Erro: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de pedido (created/updated)
     * Fluxo completo: cliente + produtos + itens + conta a receber
     */
    private function processarWebhookPedido($pedWoo, $empresaId, $integracaoId = null, $config = null)
    {
        try {
            $numeroPedido = $pedWoo['number'] ?? $pedWoo['id'] ?? '?';
            $statusWoo = $pedWoo['status'] ?? 'pending';
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                "=== Processando pedido #{$numeroPedido} via Webhook ===",
                [
                    'woo_id' => $pedWoo['id'] ?? 'N/A',
                    'status' => $statusWoo,
                    'total' => $pedWoo['total'] ?? 0,
                    'payment_method' => $pedWoo['payment_method'] ?? 'N/A',
                    'cliente' => ($pedWoo['billing']['first_name'] ?? '') . ' ' . ($pedWoo['billing']['last_name'] ?? ''),
                    'itens' => count($pedWoo['line_items'] ?? [])
                ]);
            
            // Carrega configurações
            $acoesFormasPagamento = [];
            $mapeamentoStatus = [];
            
            if ($config) {
                $acoesFormasPagamento = !empty($config['acoes_formas_pagamento']) 
                    ? json_decode($config['acoes_formas_pagamento'], true) : [];
                $mapeamentoStatus = !empty($config['mapeamento_status']) 
                    ? json_decode($config['mapeamento_status'], true) : [];
            }
            
            $statusPagamentoConfirmado = ['em_processamento', 'concluido'];
            
            // PASSO 1: CLIENTE
            $metaData = $pedWoo['meta_data'] ?? [];
            $clienteId = $this->buscarOuCriarCliente($pedWoo['billing'] ?? [], $empresaId, $metaData);
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                "Pedido #{$numeroPedido}: cliente_id={$clienteId}");
            
            // PASSO 2: MAPEAR STATUS
            $statusSistema = $mapeamentoStatus['wc-' . $statusWoo] 
                ?? $mapeamentoStatus[$statusWoo] 
                ?? $this->mapearStatus($statusWoo);
            
            // PASSO 3: FORMA DE PAGAMENTO
            $formaPagamentoWoo = $pedWoo['payment_method'] ?? '';
            $formaPagamentoTitulo = $pedWoo['payment_method_title'] ?? $formaPagamentoWoo;
            $acaoFormaPgto = $acoesFormasPagamento[$formaPagamentoWoo] ?? [];
            
            // PASSO 4: DADOS DO PEDIDO
            $dados = [
                'empresa_id' => $empresaId,
                'cliente_id' => $clienteId,
                'origem' => 'woocommerce',
                'origem_id' => $pedWoo['id'] ?? null,
                'numero_pedido' => $numeroPedido,
                'data_pedido' => date('Y-m-d H:i:s', strtotime($pedWoo['date_created'] ?? 'now')),
                'status' => $statusSistema,
                'valor_total' => $pedWoo['total'] ?? 0,
                'frete' => $pedWoo['shipping_total'] ?? 0,
                'desconto' => $pedWoo['discount_total'] ?? 0,
                'observacoes' => "Pagamento: {$formaPagamentoTitulo} (via Webhook)",
                'dados_origem' => $pedWoo
            ];
            
            // PASSO 5: CRIAR OU ATUALIZAR PEDIDO
            $pedidoExistente = $this->buscarPedidoExistente($numeroPedido, $empresaId, $pedWoo['id'] ?? null);
            
            if ($pedidoExistente) {
                $this->pedidoModel->update($pedidoExistente['id'], $dados);
                $pedidoId = $pedidoExistente['id'];
                
                \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                    "Pedido #{$numeroPedido}: atualizado (ID: {$pedidoId})",
                    ['status_anterior' => $pedidoExistente['status'], 'status_novo' => $statusSistema]);
                
                // Se status mudou, verifica baixa automática
                if ($pedidoExistente['status'] !== $statusSistema && empty($acaoFormaPgto['nao_criar_receita'])) {
                    $contaReceberModel = new \App\Models\ContaReceber();
                    $this->verificarBaixaAutomatica(
                        $pedidoId, $statusSistema, $acaoFormaPgto,
                        $statusPagamentoConfirmado, $pedWoo, $empresaId, $contaReceberModel
                    );
                }
            } else {
                $pedidoId = $this->pedidoModel->create($dados);
                
                if (!$pedidoId) {
                    throw new \Exception("Falha ao criar pedido no banco");
                }
                
                \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                    "Pedido #{$numeroPedido}: criado (ID: {$pedidoId})");
            }
            
            // PASSO 6: PROCESSAR ITENS/PRODUTOS
            $lineItems = $pedWoo['line_items'] ?? [];
            if (!empty($lineItems)) {
                try {
                    $qtdItens = $this->processarItensDoPedido($pedidoId, $lineItems, $empresaId, $numeroPedido, $config);
                    \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                        "Pedido #{$numeroPedido}: {$qtdItens} itens processados");
                } catch (\Throwable $eItens) {
                    \App\Models\LogSistema::error('WooCommerce', 'webhookPedido', 
                        "Pedido #{$numeroPedido}: erro nos itens: " . $eItens->getMessage());
                }
            }
            
            // PASSO 7: CONTA A RECEBER (apenas para pedidos novos)
            if (!$pedidoExistente) {
                if (!empty($acaoFormaPgto['nao_criar_receita'])) {
                    \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                        "Pedido #{$numeroPedido}: receita NÃO criada ('{$formaPagamentoTitulo}' = não criar receita)");
                } else {
                    $contaReceberModel = new \App\Models\ContaReceber();
                    $this->criarContaReceberDoPedido(
                        $pedidoId, $pedWoo, $empresaId, $clienteId,
                        $statusSistema, $acaoFormaPgto, $statusPagamentoConfirmado, $contaReceberModel
                    );
                }
            }
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookPedido', 
                "Pedido #{$numeroPedido}: webhook processado com sucesso!");
            
            $acao = $pedidoExistente ? 'atualizado' : 'criado';
            return ['sucesso' => true, 'mensagem' => "Pedido #{$numeroPedido} {$acao} (ID: {$pedidoId})"];
            
        } catch (\Throwable $e) {
            $numPed = $pedWoo['number'] ?? $pedWoo['id'] ?? '?';
            \App\Models\LogSistema::error('WooCommerce', 'webhookPedido', 
                "ERRO no pedido #{$numPed}: " . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
            return ['sucesso' => false, 'erro' => "Pedido #{$numPed}: " . $e->getMessage()];
        }
    }
    
    /**
     * Processa webhook de pedido deletado
     */
    private function processarWebhookPedidoDeletado($pedWoo, $empresaId)
    {
        try {
            $numeroPedido = $pedWoo['number'] ?? $pedWoo['id'] ?? '?';
            
            \App\Models\LogSistema::info('WooCommerce', 'webhookPedidoDeletado', 
                "Pedido #{$numeroPedido}: solicitação de exclusão via webhook");
            
            $pedidoExistente = $this->buscarPedidoExistente($numeroPedido, $empresaId, $pedWoo['id'] ?? null);
            
            if ($pedidoExistente) {
                // Atualiza status para cancelado
                $db = \App\Core\Database::getInstance()->getConnection();
                $sql = "UPDATE pedidos_vinculados SET status = 'cancelado' WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['id' => $pedidoExistente['id']]);
                
                \App\Models\LogSistema::info('WooCommerce', 'webhookPedidoDeletado', 
                    "Pedido #{$numeroPedido} (ID: {$pedidoExistente['id']}) marcado como cancelado");
                return ['sucesso' => true, 'mensagem' => "Pedido #{$numeroPedido} cancelado"];
            }
            
            return ['sucesso' => true, 'mensagem' => 'Pedido não encontrado no sistema'];
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'webhookPedidoDeletado', 
                "Erro: " . $e->getMessage());
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
            
            // Valida se é JSON válido
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Resposta inválida da API (não é JSON válido): " . substr($response, 0, 100));
            }
            
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
            
            // Valida se é JSON válido
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Resposta inválida da API (não é JSON válido): " . substr($response, 0, 100));
            }
            
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
            
            // Valida se é JSON válido
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Resposta inválida da API (não é JSON válido): " . substr($response, 0, 100));
            }
            
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
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
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        // Verifica erro de cURL
        if ($curlErrno !== 0) {
            throw new \Exception("Erro de conexão com WooCommerce: {$curlError} (código: {$curlErrno})");
        }
        
        // Verifica código HTTP
        if ($httpCode !== 200 && $httpCode !== 201) {
            // Tenta extrair mensagem de erro da resposta
            $errorMessage = $response;
            
            // Se for JSON, tenta pegar mensagem de erro
            $jsonData = @json_decode($response, true);
            if ($jsonData && isset($jsonData['message'])) {
                $errorMessage = $jsonData['message'];
            } elseif ($jsonData && isset($jsonData['error'])) {
                $errorMessage = $jsonData['error'];
            }
            
            // Limita tamanho da mensagem
            if (strlen($errorMessage) > 200) {
                $errorMessage = substr($errorMessage, 0, 200) . '...';
            }
            
            throw new \Exception("Erro na API WooCommerce (HTTP {$httpCode}): {$errorMessage}");
        }
        
        // Verifica se a resposta é vazia
        if (empty($response)) {
            throw new \Exception("Resposta vazia da API WooCommerce");
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
        
        // Valida JSON
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Resposta inválida da API de produtos: " . json_last_error_msg());
        }
        
        return is_array($data) ? $data : [];
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
                FROM integracoes_logs 
                WHERE integracao_id = :integracao_id 
                AND data_execucao >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
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
