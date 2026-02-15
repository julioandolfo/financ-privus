<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoJob;
use App\Models\IntegracaoLog;
use App\Models\LogSistema;
use Includes\Services\WooCommerceService;

/**
 * Controller para Dashboard de Monitoramento da Integração WooCommerce
 */
class IntegracaoWooDashboardController extends Controller
{
    private $wooModel;
    private $jobModel;
    private $logModel;
    private $wooService;
    
    public function __construct()
    {
        parent::__construct();
        
        try {
            LogSistema::debug('WooDashboard', '__construct', 'Iniciando construtor');
            
            $this->wooModel = new IntegracaoWooCommerce();
            LogSistema::debug('WooDashboard', '__construct', 'IntegracaoWooCommerce OK');
            
            try {
                $this->jobModel = new IntegracaoJob();
                LogSistema::debug('WooDashboard', '__construct', 'IntegracaoJob OK');
            } catch (\Throwable $e) {
                $this->jobModel = null;
                LogSistema::warning('WooDashboard', '__construct', 'Erro IntegracaoJob: ' . $e->getMessage());
            }
            
            try {
                $this->logModel = new IntegracaoLog();
                LogSistema::debug('WooDashboard', '__construct', 'IntegracaoLog OK');
            } catch (\Throwable $e) {
                $this->logModel = null;
                LogSistema::warning('WooDashboard', '__construct', 'Erro IntegracaoLog: ' . $e->getMessage());
            }
            
            try {
                $this->wooService = new WooCommerceService();
                LogSistema::debug('WooDashboard', '__construct', 'WooCommerceService OK');
            } catch (\Throwable $e) {
                $this->wooService = null;
                LogSistema::warning('WooDashboard', '__construct', 'Erro WooCommerceService: ' . $e->getMessage());
            }
            
            LogSistema::debug('WooDashboard', '__construct', 'Construtor finalizado');
        } catch (\Throwable $e) {
            LogSistema::error('WooDashboard', '__construct', 'ERRO FATAL: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Dashboard principal
     */
    public function index(Request $request, Response $response, $integracaoId)
    {
        try {
            $config = $this->wooModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
            }
            
            // Busca métricas (com fallback)
            $metricas = ['jobs' => [], 'logs' => [], 'produtos_hoje' => 0, 'ultima_sync_produtos' => null, 'ultima_sync_pedidos' => null];
            try {
                if ($this->wooService) {
                    $metricas = $this->wooService->obterMetricasDashboard($integracaoId, 7);
                }
            } catch (\Exception $e) {
                // Ignora erro - usa valores padrão
            }
            
            // Jobs recentes (com fallback)
            $jobsRecentes = [];
            try {
                if ($this->jobModel) {
                    $jobsRecentes = $this->jobModel->listar([
                        'integracao_id' => $integracaoId,
                        'limit' => 10
                    ]);
                }
            } catch (\Exception $e) {
                // Ignora erro - tabela pode não existir ainda
            }
            
            // Logs recentes (com fallback)
            $logsRecentes = [];
            try {
                if ($this->logModel) {
                    $logsRecentes = $this->logModel->findByIntegracaoId($integracaoId, 20);
                }
            } catch (\Exception $e) {
                // Ignora erro
            }
            
            // Estatísticas de cache (com fallback)
            $estatisticasCache = [];
            try {
                $estatisticasCache = $this->obterEstatisticasCache($integracaoId);
            } catch (\Exception $e) {
                // Ignora erro
            }
            
            $this->render('integracoes/woocommerce_dashboard', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'metricas' => $metricas,
                'jobsRecentes' => $jobsRecentes,
                'logsRecentes' => $logsRecentes,
                'estatisticasCache' => $estatisticasCache
            ]);
            
        } catch (\Throwable $e) {
            LogSistema::error('WooDashboard', 'index', 'ERRO: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Retorna métricas em tempo real (JSON)
     */
    public function metricas(Request $request, Response $response, $integracaoId)
    {
        try {
            $dias = $request->get('dias', 7);
            
            $metricas = ['jobs' => [], 'logs' => [], 'produtos_hoje' => 0, 'ultima_sync_produtos' => null, 'ultima_sync_pedidos' => null];
            if ($this->wooService) {
                $metricas = $this->wooService->obterMetricasDashboard($integracaoId, $dias);
            }
            
            return $response->json([
                'success' => true,
                'data' => $metricas
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Lista jobs
     */
    public function jobs(Request $request, Response $response, $integracaoId)
    {
        try {
            if (!$this->jobModel) {
                return $response->json(['success' => true, 'data' => []], 200);
            }
            
            $status = $request->get('status');
            $limit = $request->get('limit', 50);
            $offset = $request->get('offset', 0);
            
            $filtros = [
                'integracao_id' => $integracaoId,
                'limit' => $limit,
                'offset' => $offset
            ];
            
            if ($status) {
                $filtros['status'] = $status;
            }
            
            $jobs = $this->jobModel->listar($filtros);
            
            return $response->json([
                'success' => true,
                'data' => $jobs
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Processa próximo job manualmente
     */
    public function processarJob(Request $request, Response $response, $integracaoId)
    {
        try {
            $resultado = $this->wooService->processarProximoJob();
            
            return $response->json([
                'success' => $resultado['sucesso'],
                'message' => $resultado['mensagem'] ?? 'Job processado',
                'data' => $resultado['resultado'] ?? null
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Criar job manual
     */
    public function criarJob(Request $request, Response $response, $integracaoId)
    {
        try {
            $data = $request->isJson() ? $request->json() : $request->post();
            $tipo = $data['tipo'] ?? null;
            $payload = $data['payload'] ?? [];
            $prioridade = $data['prioridade'] ?? (IntegracaoJob::PRIORIDADE_NORMAL ?? 5);
            
            if (!$tipo) {
                return $response->json([
                    'success' => false,
                    'error' => 'Tipo de job não informado'
                ], 400);
            }
            
            $jobId = $this->wooService->criarJob($integracaoId, $tipo, $payload, $prioridade);
            
            if ($jobId) {
                return $response->json([
                    'success' => true,
                    'message' => 'Job criado com sucesso!',
                    'job_id' => $jobId
                ]);
            } else {
                return $response->json([
                    'success' => false,
                    'error' => 'Erro ao criar job'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Limpar jobs antigos
     */
    public function limparJobs(Request $request, Response $response, $integracaoId)
    {
        try {
            $diasRetencao = $request->post('dias', 30);
            
            $result = $this->jobModel->limparAntigos($diasRetencao);
            
            return $response->json([
                'success' => true,
                'message' => 'Jobs antigos limpos com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Estatísticas de produtos
     */
    public function estatisticasProdutos(Request $request, Response $response, $integracaoId)
    {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            
            // Total de produtos sincronizados
            $sql = "SELECT COUNT(*) as total FROM integracao_sync_cache 
                    WHERE integracao_id = :integracao_id AND tipo = 'produto'";
            $stmt = $db->prepare($sql);
            $stmt->execute(['integracao_id' => $integracaoId]);
            $totalProdutos = $stmt->fetchColumn();
            
            // Produtos com imagem
            $sql = "SELECT COUNT(DISTINCT isc.referencia_interna) as total 
                    FROM integracao_sync_cache isc
                    INNER JOIN produtos_imagens pi ON pi.produto_id = isc.referencia_interna
                    WHERE isc.integracao_id = :integracao_id AND isc.tipo = 'produto'";
            $stmt = $db->prepare($sql);
            $stmt->execute(['integracao_id' => $integracaoId]);
            $produtosComImagem = $stmt->fetchColumn();
            
            // Produtos atualizados hoje
            $sql = "SELECT COUNT(*) as total FROM integracao_sync_cache 
                    WHERE integracao_id = :integracao_id 
                    AND tipo = 'produto'
                    AND DATE(ultima_atualizacao) = CURDATE()";
            $stmt = $db->prepare($sql);
            $stmt->execute(['integracao_id' => $integracaoId]);
            $produtosHoje = $stmt->fetchColumn();
            
            return $response->json([
                'success' => true,
                'data' => [
                    'total_produtos' => $totalProdutos,
                    'produtos_com_imagem' => $produtosComImagem,
                    'produtos_hoje' => $produtosHoje,
                    'percentual_com_imagem' => $totalProdutos > 0 
                        ? round(($produtosComImagem / $totalProdutos) * 100, 2) 
                        : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtém estatísticas de cache
     */
    private function obterEstatisticasCache($integracaoId)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Total de itens no cache por tipo
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM integracao_sync_cache 
                WHERE integracao_id = :integracao_id 
                GROUP BY tipo";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        
        $cache = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $cache[$row['tipo']] = $row['total'];
        }
        
        return $cache;
    }
    
    /**
     * Gráfico: Sincronizações por dia (últimos 30 dias)
     */
    public function graficoSincronizacoes(Request $request, Response $response, $integracaoId)
    {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        DATE(criado_em) as data,
                        tipo,
                        COUNT(*) as total
                    FROM integracao_jobs
                    WHERE integracao_id = :integracao_id
                    AND status = 'concluido'
                    AND criado_em >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(criado_em), tipo
                    ORDER BY data DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['integracao_id' => $integracaoId]);
            
            $dados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $response->json([
                'success' => true,
                'data' => $dados
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Gráfico: Taxa de sucesso/erro
     */
    public function graficoTaxaSucesso(Request $request, Response $response, $integracaoId)
    {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        status,
                        COUNT(*) as total
                    FROM integracao_jobs
                    WHERE integracao_id = :integracao_id
                    AND criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY status";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['integracao_id' => $integracaoId]);
            
            $dados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $response->json([
                'success' => true,
                'data' => $dados
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Validar pedidos faltantes
     * Compara pedidos do WooCommerce com pedidos importados no sistema
     */
    public function validarPedidos(Request $request, Response $response, $integracaoId)
    {
        try {
            if (!$this->wooService) {
                return $response->json([
                    'success' => false,
                    'error' => 'Serviço WooCommerce não disponível'
                ], 500);
            }
            
            $data = $request->isJson() ? $request->json() : $request->post();
            
            $opcoes = [];
            
            // Período
            if (!empty($data['periodo'])) {
                $opcoes['periodo'] = $data['periodo'];
            }
            
            // Data customizada
            if (!empty($data['data_inicio']) && !empty($data['data_fim'])) {
                $opcoes['data_inicio'] = $data['data_inicio'];
                $opcoes['data_fim'] = $data['data_fim'];
            }
            
            LogSistema::info('WooDashboard', 'validarPedidos', 
                'Iniciando validação de pedidos', 
                ['integracao_id' => $integracaoId, 'opcoes' => $opcoes]);
            
            $resultado = $this->wooService->validarPedidosFaltantes($integracaoId, $opcoes);
            
            if (!$resultado['sucesso']) {
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro'] ?? 'Erro ao validar pedidos'
                ], 400);
            }
            
            return $response->json([
                'success' => true,
                'message' => 'Validação concluída com sucesso',
                'data' => [
                    'total_woo' => $resultado['total_woo'],
                    'total_local' => $resultado['total_local'],
                    'total_faltantes' => $resultado['total_faltantes'],
                    'pedidos_faltantes' => $resultado['pedidos_faltantes']
                ]
            ]);
            
        } catch (\Exception $e) {
            LogSistema::error('WooDashboard', 'validarPedidos', 
                'Erro: ' . $e->getMessage());
            
            return $response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Sincronizar pedidos faltantes
     * Importa pedidos específicos que estão faltando
     */
    public function sincronizarFaltantes(Request $request, Response $response, $integracaoId)
    {
        try {
            if (!$this->wooService) {
                return $response->json([
                    'success' => false,
                    'error' => 'Serviço WooCommerce não disponível'
                ], 500);
            }
            
            $data = $request->isJson() ? $request->json() : $request->post();
            
            if (empty($data['pedidos_ids']) || !is_array($data['pedidos_ids'])) {
                return $response->json([
                    'success' => false,
                    'error' => 'IDs dos pedidos não informados'
                ], 400);
            }
            
            $idsWooCommerce = $data['pedidos_ids'];
            
            LogSistema::info('WooDashboard', 'sincronizarFaltantes', 
                'Iniciando sincronização de pedidos faltantes', 
                [
                    'integracao_id' => $integracaoId, 
                    'total_pedidos' => count($idsWooCommerce)
                ]);
            
            $resultado = $this->wooService->sincronizarPedidosFaltantes($integracaoId, $idsWooCommerce);
            
            if (!$resultado['sucesso']) {
                return $response->json([
                    'success' => false,
                    'error' => $resultado['erro'] ?? 'Erro ao sincronizar pedidos'
                ], 400);
            }
            
            $mensagem = "Sincronização concluída: {$resultado['total']} de {$resultado['total_tentativas']} pedidos importados";
            
            if (!empty($resultado['erros'])) {
                $mensagem .= ". " . count($resultado['erros']) . " erros encontrados.";
            }
            
            return $response->json([
                'success' => true,
                'message' => $mensagem,
                'data' => [
                    'total_importados' => $resultado['total'],
                    'total_tentativas' => $resultado['total_tentativas'],
                    'erros' => $resultado['erros']
                ]
            ]);
            
        } catch (\Exception $e) {
            LogSistema::error('WooDashboard', 'sincronizarFaltantes', 
                'Erro: ' . $e->getMessage());
            
            return $response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
