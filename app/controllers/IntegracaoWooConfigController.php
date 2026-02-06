<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoWooCommerce;
use App\Models\WooCommerceMetadata;
use App\Models\CategoriaFinanceira;
use App\Models\FormaPagamento;
use Includes\Services\WooCommerceService;

/**
 * Controller para configuração da integração WooCommerce
 */
class IntegracaoWooConfigController extends Controller
{
    private $wooModel;
    private $metadataModel;
    private $categoriaModel;
    private $formaPgtoModel;
    private $wooService;
    
    public function __construct()
    {
        parent::__construct();
        $this->wooModel = new IntegracaoWooCommerce();
        $this->metadataModel = new WooCommerceMetadata();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->formaPgtoModel = new FormaPagamento();
        $this->wooService = new WooCommerceService();
    }
    
    /**
     * Tela de configuração de status
     */
    public function configurarStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca status do WooCommerce (armazenados no metadata)
            $statusWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_STATUS);
            
            // Busca mapeamento atual
            $mapeamento = $config['mapeamento_status'] 
                ? json_decode($config['mapeamento_status'], true) 
                : [];
            
            // Status disponíveis no sistema
            $statusSistema = [
                'pendente' => 'Pendente',
                'em_processamento' => 'Em Processamento',
                'concluido' => 'Concluído',
                'cancelado' => 'Cancelado',
                'parcial' => 'Parcial'
            ];
            
            $this->render('integracoes/woocommerce_config_status', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'statusWoo' => $statusWoo,
                'statusSistema' => $statusSistema,
                'mapeamento' => $mapeamento
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza status do WooCommerce
     */
    public function atualizarStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca status do WooCommerce
            $resultado = $this->wooService->buscarStatusWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                return $response->json([
                    'success' => false, 
                    'error' => $resultado['erro']
                ], 400);
            }
            
            return $response->json([
                'success' => true,
                'message' => 'Status atualizados com sucesso!',
                'total' => count($resultado['status'])
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva mapeamento de status
     */
    public function salvarMapeamentoStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            $mapeamento = $request->post('mapeamento', []);
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Atualiza mapeamento
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "UPDATE integracoes_woocommerce 
                    SET mapeamento_status = :mapeamento 
                    WHERE integracao_id = :integracao_id";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'mapeamento' => json_encode($mapeamento),
                'integracao_id' => $integracaoId
            ]);
            
            if ($result) {
                return $response->json([
                    'success' => true,
                    'message' => 'Mapeamento de status salvo com sucesso!'
                ]);
            } else {
                return $response->json([
                    'success' => false,
                    'error' => 'Erro ao salvar mapeamento'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Tela de configuração de formas de pagamento
     */
    public function configurarFormasPagamento(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca formas de pagamento do WooCommerce
            $formasPgtoWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_PAYMENT_GATEWAY);
            
            // Busca acoes configuradas
            $acoesConfig = $config['acoes_formas_pagamento'] 
                ? json_decode($config['acoes_formas_pagamento'], true) 
                : [];
            
            // Formas de pagamento do sistema
            $formasPgtoSistema = $this->formaPgtoModel->findAll();
            
            $this->render('integracoes/woocommerce_config_pagamento', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'formasPgtoWoo' => $formasPgtoWoo,
                'formasPgtoSistema' => $formasPgtoSistema,
                'acoesConfig' => $acoesConfig
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza formas de pagamento do WooCommerce
     */
    public function atualizarFormasPagamento(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca formas de pagamento do WooCommerce
            $resultado = $this->wooService->buscarFormasPagamentoWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro']
                ], 400);
            }
            
            return $response->json([
                'success' => true,
                'message' => 'Formas de pagamento atualizadas com sucesso!',
                'total' => count($resultado['formas_pagamento'])
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva configuração de ações por forma de pagamento
     */
    public function salvarAcoesFormasPagamento(Request $request, Response $response, $integracaoId)
    {
        try {
            $acoes = $request->post('acoes', []);
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Atualiza configuração
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "UPDATE integracoes_woocommerce 
                    SET acoes_formas_pagamento = :acoes 
                    WHERE integracao_id = :integracao_id";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'acoes' => json_encode($acoes),
                'integracao_id' => $integracaoId
            ]);
            
            if ($result) {
                return $response->json([
                    'success' => true,
                    'message' => 'Configuração de formas de pagamento salva com sucesso!'
                ]);
            } else {
                return $response->json([
                    'success' => false,
                    'error' => 'Erro ao salvar configuração'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Tela de configuração de categorias
     */
    public function configurarCategorias(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca categorias do WooCommerce
            $categoriasWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_CATEGORIA);
            
            // Busca mapeamento atual
            $mapeamento = $config['mapeamento_categorias'] 
                ? json_decode($config['mapeamento_categorias'], true) 
                : [];
            
            // Categorias do sistema (produtos)
            // Assumindo que existe uma tabela de categorias de produtos
            $categoriasSistema = []; // Implementar busca de categorias
            
            $this->render('integracoes/woocommerce_config_categorias', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'categoriasWoo' => $categoriasWoo,
                'categoriasSistema' => $categoriasSistema,
                'mapeamento' => $mapeamento
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza categorias do WooCommerce
     */
    public function atualizarCategorias(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca categorias do WooCommerce
            $resultado = $this->wooService->buscarCategoriasWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro']
                ], 400);
            }
            
            return $response->json([
                'success' => true,
                'message' => 'Categorias atualizadas com sucesso!',
                'total' => count($resultado['categorias'])
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva mapeamento de categorias
     */
    public function salvarMapeamentoCategorias(Request $request, Response $response, $integracaoId)
    {
        try {
            $mapeamento = $request->post('mapeamento', []);
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Atualiza mapeamento
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "UPDATE integracoes_woocommerce 
                    SET mapeamento_categorias = :mapeamento 
                    WHERE integracao_id = :integracao_id";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'mapeamento' => json_encode($mapeamento),
                'integracao_id' => $integracaoId
            ]);
            
            if ($result) {
                return $response->json([
                    'success' => true,
                    'message' => 'Mapeamento de categorias salvo com sucesso!'
                ]);
            } else {
                return $response->json([
                    'success' => false,
                    'error' => 'Erro ao salvar mapeamento'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
