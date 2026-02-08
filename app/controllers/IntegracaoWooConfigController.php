<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoConfig;
use App\Models\WooCommerceMetadata;
use App\Models\CategoriaFinanceira;
use App\Models\FormaPagamento;
use App\Models\LogSistema;
use Includes\Services\WooCommerceService;

/**
 * Controller para configuração da integração WooCommerce
 */
class IntegracaoWooConfigController extends Controller
{
    private $wooModel;
    private $integracaoConfigModel;
    private $metadataModel;
    private $categoriaModel;
    private $formaPgtoModel;
    private $wooService;
    
    public function __construct()
    {
        parent::__construct();
        
        try {
            LogSistema::debug('WooConfig', '__construct', 'Iniciando construtor IntegracaoWooConfigController');
            
            $this->wooModel = new IntegracaoWooCommerce();
            $this->integracaoConfigModel = new IntegracaoConfig();
            LogSistema::debug('WooConfig', '__construct', 'IntegracaoWooCommerce instanciado');
            
            $this->metadataModel = new WooCommerceMetadata();
            LogSistema::debug('WooConfig', '__construct', 'WooCommerceMetadata instanciado');
            
            $this->formaPgtoModel = new FormaPagamento();
            LogSistema::debug('WooConfig', '__construct', 'FormaPagamento instanciado');
            
            try {
                $this->categoriaModel = new CategoriaFinanceira();
                LogSistema::debug('WooConfig', '__construct', 'CategoriaFinanceira instanciado');
            } catch (\Throwable $e) {
                $this->categoriaModel = null;
                LogSistema::warning('WooConfig', '__construct', 'Erro CategoriaFinanceira: ' . $e->getMessage());
            }
            
            try {
                $this->wooService = new WooCommerceService();
                LogSistema::debug('WooConfig', '__construct', 'WooCommerceService instanciado');
            } catch (\Throwable $e) {
                $this->wooService = null;
                LogSistema::warning('WooConfig', '__construct', 'Erro WooCommerceService: ' . $e->getMessage());
            }
            
            LogSistema::debug('WooConfig', '__construct', 'Construtor finalizado com sucesso');
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', '__construct', 'ERRO FATAL no construtor: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Tela de configuração de status
     */
    public function configurarStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            LogSistema::info('WooConfig', 'configurarStatus', "Acessando config status, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                LogSistema::warning('WooConfig', 'configurarStatus', "Integração {$integracaoId} não encontrada");
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            LogSistema::debug('WooConfig', 'configurarStatus', 'Config encontrada, buscando metadata...');
            
            // Busca status do WooCommerce (armazenados no metadata)
            $statusWoo = [];
            try {
                $statusWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_STATUS);
                LogSistema::debug('WooConfig', 'configurarStatus', 'Status WooCommerce: ' . count($statusWoo) . ' encontrados');
            } catch (\Throwable $e) {
                LogSistema::warning('WooConfig', 'configurarStatus', 'Erro ao buscar metadata status: ' . $e->getMessage());
            }
            
            // Busca mapeamento atual
            $mapeamento = !empty($config['mapeamento_status']) 
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
            
            LogSistema::debug('WooConfig', 'configurarStatus', 'Renderizando view...');
            
            $this->render('integracoes/woocommerce_config_status', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'statusWoo' => $statusWoo,
                'statusSistema' => $statusSistema,
                'mapeamento' => $mapeamento
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'configurarStatus', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza status do WooCommerce
     */
    public function atualizarStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            LogSistema::info('WooConfig', 'atualizarStatus', "Atualizando status WooCommerce, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            if (!$this->wooService) {
                return $response->json(['success' => false, 'error' => 'WooCommerceService não disponível'], 500);
            }
            
            $resultado = $this->wooService->buscarStatusWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                LogSistema::warning('WooConfig', 'atualizarStatus', 'Falha: ' . ($resultado['erro'] ?? 'Erro desconhecido'));
                return $response->json([
                    'success' => false, 
                    'error' => $resultado['erro']
                ], 400);
            }
            
            LogSistema::info('WooConfig', 'atualizarStatus', 'Status atualizados: ' . count($resultado['status']));
            
            return $response->json([
                'success' => true,
                'message' => 'Status atualizados com sucesso!',
                'total' => count($resultado['status'])
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'atualizarStatus', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva mapeamento de status
     */
    public function salvarMapeamentoStatus(Request $request, Response $response, $integracaoId)
    {
        try {
            $data = $request->isJson() ? $request->json() : $request->post();
            $mapeamento = $data['mapeamento'] ?? [];
            
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
            LogSistema::info('WooConfig', 'configurarFormasPagamento', "Acessando config pagamentos, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca formas de pagamento do WooCommerce
            $formasPgtoWoo = [];
            try {
                $formasPgtoWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_PAYMENT_GATEWAY);
            } catch (\Throwable $e) {
                LogSistema::warning('WooConfig', 'configurarFormasPagamento', 'Erro ao buscar metadata pagamentos: ' . $e->getMessage());
            }
            
            // Busca acoes configuradas
            $acoesConfig = !empty($config['acoes_formas_pagamento']) 
                ? json_decode($config['acoes_formas_pagamento'], true) 
                : [];
            
            // Busca empresa_id da integração
            $integracao = $this->integracaoConfigModel->findById($integracaoId);
            $empresaId = $integracao['empresa_id'] ?? null;
            
            // Formas de pagamento do sistema (filtradas pela empresa da integração)
            $formasPgtoSistema = [];
            try {
                $formasPgtoSistema = $this->formaPgtoModel->findAll($empresaId);
            } catch (\Throwable $e) {
                LogSistema::warning('WooConfig', 'configurarFormasPagamento', 'Erro ao buscar formas pgto sistema: ' . $e->getMessage());
            }
            
            $this->render('integracoes/woocommerce_config_pagamento', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'formasPgtoWoo' => $formasPgtoWoo,
                'formasPgtoSistema' => $formasPgtoSistema,
                'acoesConfig' => $acoesConfig
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'configurarFormasPagamento', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza formas de pagamento do WooCommerce
     */
    public function atualizarFormasPagamento(Request $request, Response $response, $integracaoId)
    {
        try {
            LogSistema::info('WooConfig', 'atualizarFormasPagamento', "Atualizando formas pagamento, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            if (!$this->wooService) {
                return $response->json(['success' => false, 'error' => 'WooCommerceService não disponível'], 500);
            }
            
            $resultado = $this->wooService->buscarFormasPagamentoWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                LogSistema::warning('WooConfig', 'atualizarFormasPagamento', 'Falha: ' . ($resultado['erro'] ?? 'Erro desconhecido'));
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro']
                ], 400);
            }
            
            LogSistema::info('WooConfig', 'atualizarFormasPagamento', 'Formas pagamento atualizadas: ' . count($resultado['formas_pagamento']));
            
            return $response->json([
                'success' => true,
                'message' => 'Formas de pagamento atualizadas com sucesso!',
                'total' => count($resultado['formas_pagamento'])
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'atualizarFormasPagamento', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva configuração de ações por forma de pagamento
     */
    public function salvarAcoesFormasPagamento(Request $request, Response $response, $integracaoId)
    {
        try {
            $data = $request->isJson() ? $request->json() : $request->post();
            $acoes = $data['acoes'] ?? [];
            
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
            LogSistema::info('WooConfig', 'configurarCategorias', "Acessando config categorias, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca categorias do WooCommerce
            $categoriasWoo = [];
            try {
                $categoriasWoo = $this->metadataModel->findByTipo($integracaoId, WooCommerceMetadata::TIPO_CATEGORIA);
            } catch (\Throwable $e) {
                LogSistema::warning('WooConfig', 'configurarCategorias', 'Erro ao buscar metadata categorias: ' . $e->getMessage());
            }
            
            // Busca mapeamento atual
            $mapeamento = !empty($config['mapeamento_categorias']) 
                ? json_decode($config['mapeamento_categorias'], true) 
                : [];
            
            // Categorias do sistema
            $categoriasSistema = [];
            try {
                if ($this->categoriaModel) {
                    $categoriasSistema = $this->categoriaModel->findAll();
                }
            } catch (\Throwable $e) {
                LogSistema::warning('WooConfig', 'configurarCategorias', 'Erro ao buscar categorias sistema: ' . $e->getMessage());
            }
            
            $this->render('integracoes/woocommerce_config_categorias', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'categoriasWoo' => $categoriasWoo,
                'categoriasSistema' => $categoriasSistema,
                'mapeamento' => $mapeamento
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'configurarCategorias', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualiza categorias do WooCommerce
     */
    public function atualizarCategorias(Request $request, Response $response, $integracaoId)
    {
        try {
            LogSistema::info('WooConfig', 'atualizarCategorias', "Atualizando categorias, integracaoId: {$integracaoId}");
            
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            if (!$this->wooService) {
                return $response->json(['success' => false, 'error' => 'WooCommerceService não disponível'], 500);
            }
            
            $resultado = $this->wooService->buscarCategoriasWooCommerce($config);
            
            if (!$resultado['sucesso']) {
                LogSistema::warning('WooConfig', 'atualizarCategorias', 'Falha: ' . ($resultado['erro'] ?? 'Erro desconhecido'));
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro']
                ], 400);
            }
            
            LogSistema::info('WooConfig', 'atualizarCategorias', 'Categorias atualizadas: ' . count($resultado['categorias']));
            
            return $response->json([
                'success' => true,
                'message' => 'Categorias atualizadas com sucesso!',
                'total' => count($resultado['categorias'])
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooConfig', 'atualizarCategorias', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva mapeamento de categorias
     */
    public function salvarMapeamentoCategorias(Request $request, Response $response, $integracaoId)
    {
        try {
            $data = $request->isJson() ? $request->json() : $request->post();
            $mapeamento = $data['mapeamento'] ?? [];
            
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
