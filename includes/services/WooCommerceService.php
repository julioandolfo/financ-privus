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
        
        try {
            $pedidos = $this->buscarPedidosWooCommerce($config, $opcoes);
            
            \App\Models\LogSistema::info('WooCommerce', 'sincronizarPedidos', 
                'Pedidos encontrados: ' . count($pedidos), ['empresa_id' => $empresaId]);
            
            foreach ($pedidos as $pedWoo) {
                try {
                    // Busca ou cria cliente
                    $clienteId = $this->buscarOuCriarCliente($pedWoo['billing'], $empresaId);
                    
                    // Mapeia o status do WooCommerce para o sistema
                    $statusWoo = $pedWoo['status'];
                    $statusSistema = $mapeamentoStatus['wc-' . $statusWoo] 
                        ?? $mapeamentoStatus[$statusWoo] 
                        ?? $this->mapearStatus($statusWoo);
                    
                    // Identifica forma de pagamento do WooCommerce
                    $formaPagamentoWoo = $pedWoo['payment_method'] ?? '';
                    $formaPagamentoTitulo = $pedWoo['payment_method_title'] ?? $formaPagamentoWoo;
                    
                    // Busca configuração de ação para esta forma de pagamento
                    $acaoFormaPgto = $acoesFormasPagamento[$formaPagamentoWoo] ?? [];
                    
                    // Dados do pedido
                    $dados = [
                        'empresa_id' => $empresaId,
                        'cliente_id' => $clienteId,
                        'origem' => 'woocommerce',
                        'origem_id' => $pedWoo['id'],
                        'numero_pedido' => $pedWoo['number'],
                        'data_pedido' => date('Y-m-d H:i:s', strtotime($pedWoo['date_created'])),
                        'status' => $statusSistema,
                        'valor_total' => $pedWoo['total'],
                        'frete' => $pedWoo['shipping_total'] ?? 0,
                        'desconto' => $pedWoo['discount_total'] ?? 0,
                        'observacoes' => "Pagamento: {$formaPagamentoTitulo}",
                        'dados_origem' => $pedWoo
                    ];
                    
                    // Verifica se pedido já existe
                    $pedidoExistente = $this->buscarPedidoExistente($pedWoo['number'], $empresaId);
                    
                    if ($pedidoExistente) {
                        // Atualiza status se mudou
                        if ($pedidoExistente['status'] !== $statusSistema) {
                            $this->pedidoModel->update($pedidoExistente['id'], $dados);
                            
                            // Verifica se precisa dar baixa automática agora
                            // (status mudou para confirmado E forma de pgto está marcada para baixa)
                            $this->verificarBaixaAutomatica(
                                $pedidoExistente['id'],
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
                        
                        // Cria conta a receber
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
                    
                    $total++;
                } catch (\Exception $e) {
                    $erros[] = "Pedido {$pedWoo['number']}: " . $e->getMessage();
                    \App\Models\LogSistema::error('WooCommerce', 'sincronizarPedidos', 
                        "Erro pedido #{$pedWoo['number']}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $erros[] = "Erro ao buscar pedidos: " . $e->getMessage();
        }
        
        return ['total' => $total, 'erros' => $erros];
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
                    'categoria_id' => $this->getCategoriaVendaId($empresaId),
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
                'categoria_id' => $this->getCategoriaVendaId($empresaId),
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
     * Busca categoria padrão de venda da empresa
     */
    private function getCategoriaVendaId($empresaId)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Tenta buscar categoria "Vendas" ou "Receitas"
        $sql = "SELECT id FROM categorias_financeiras 
                WHERE empresa_id = :empresa_id 
                AND tipo = 'receita'
                AND ativo = 1
                ORDER BY id ASC LIMIT 1";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchColumn();
            
            if ($result) {
                return $result;
            }
        } catch (\Exception $e) {
            // Ignora
        }
        
        // Fallback: busca qualquer categoria ativa
        $sql = "SELECT id FROM categorias_financeiras 
                WHERE ativo = 1 ORDER BY id ASC LIMIT 1";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn() ?: 1;
        } catch (\Exception $e) {
            return 1;
        }
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
    private function buscarOuCriarCliente($billing, $empresaId)
    {
        $email = $billing['email'] ?? '';
        $nome = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $cpfCnpj = $billing['cpf'] ?? $billing['cnpj'] ?? $billing['persontype_cpf'] ?? '';
        $telefone = $billing['phone'] ?? '';
        
        // 1. Busca por CPF/CNPJ se disponível
        if (!empty($cpfCnpj)) {
            $cliente = $this->clienteModel->findByCpfCnpj($cpfCnpj, $empresaId);
            if ($cliente) {
                return $cliente['id'];
            }
        }
        
        // 2. Busca por email
        if (!empty($email)) {
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT id FROM clientes WHERE email = :email AND empresa_id = :empresa_id AND ativo = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email, 'empresa_id' => $empresaId]);
            $clienteId = $stmt->fetchColumn();
            if ($clienteId) {
                return $clienteId;
            }
        }
        
        // 3. Cria cliente novo
        if (empty($nome)) {
            $nome = $email ?: 'Cliente WooCommerce';
        }
        
        $endereco = [
            'logradouro' => $billing['address_1'] ?? '',
            'complemento' => $billing['address_2'] ?? '',
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
            
            \App\Models\LogSistema::info('WooCommerce', 'criarCliente', 
                "Cliente criado: {$nome}", ['email' => $email, 'id' => $clienteId]);
            
            return $clienteId;
        } catch (\Exception $e) {
            \App\Models\LogSistema::error('WooCommerce', 'criarCliente', 
                "Erro ao criar cliente: " . $e->getMessage(), ['nome' => $nome, 'email' => $email]);
            return 1; // Fallback
        }
    }
    
    /**
     * Busca pedido existente pelo número
     */
    private function buscarPedidoExistente($numeroPedido, $empresaId)
    {
        try {
            $pedido = $this->pedidoModel->findByOrigem('woocommerce', $numeroPedido, $empresaId);
            return $pedido ?: null;
        } catch (\Exception $e) {
            // Tenta busca alternativa
            try {
                $db = \App\Core\Database::getInstance()->getConnection();
                $sql = "SELECT * FROM pedidos_vinculados 
                        WHERE numero_pedido = :numero AND empresa_id = :empresa_id 
                        AND origem = 'woocommerce' LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute(['numero' => $numeroPedido, 'empresa_id' => $empresaId]);
                return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Exception $e2) {
                return null;
            }
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
