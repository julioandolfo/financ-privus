<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoJob;
use App\Models\IntegracaoLog;
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
        $this->wooModel = new IntegracaoWooCommerce();
        $this->jobModel = new IntegracaoJob();
        $this->logModel = new IntegracaoLog();
        $this->wooService = new WooCommerceService();
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
            
            // Busca métricas
            $metricas = $this->wooService->obterMetricasDashboard($integracaoId, 7);
            
            // Jobs recentes
            $jobsRecentes = $this->jobModel->listar([
                'integracao_id' => $integracaoId,
                'limit' => 10
            ]);
            
            // Logs recentes
            $logsRecentes = $this->logModel->listar([
                'integracao_id' => $integracaoId,
                'limit' => 20
            ]);
            
            // Estatísticas de cache
            $estatisticasCache = $this->obterEstatisticasCache($integracaoId);
            
            $this->render('integracoes/woocommerce_dashboard', [
                'integracaoId' => $integracaoId,
                'config' => $config,
                'metricas' => $metricas,
                'jobsRecentes' => $jobsRecentes,
                'logsRecentes' => $logsRecentes,
                'estatisticasCache' => $estatisticasCache
            ]);
            
        } catch (\Exception $e) {
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
            
            $metricas = $this->wooService->obterMetricasDashboard($integracaoId, $dias);
            
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
            $tipo = $request->post('tipo');
            $payload = $request->post('payload', []);
            $prioridade = $request->post('prioridade', IntegracaoJob::PRIORIDADE_NORMAL);
            
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
}
