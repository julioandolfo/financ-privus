<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Notificacao;
use App\Models\NotificacaoConfig;
use Includes\Services\NotificacaoService;

/**
 * Controller para Notificações
 */
class NotificacaoController extends Controller
{
    private $notificacaoModel;
    private $configModel;
    private $notificacaoService;
    
    /**
     * Log de debug
     */
    private function logDebug($message, $context = [])
    {
        $logFile = dirname(__DIR__) . '/../logs/notificacoes.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->logDebug('Constructor chamado');
    }
    
    /**
     * Lista todas as notificações
     */
    public function index(Request $request, Response $response)
    {
        $this->logDebug('=== INDEX NOTIFICAÇÕES ===');
        
        try {
            $this->logDebug('Iniciando models...');
            $this->notificacaoModel = new Notificacao();
            $this->logDebug('Notificacao model OK');
            
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $this->logDebug('Usuario ID', ['id' => $usuarioId]);
            
            if (!$usuarioId) {
                $this->logDebug('Usuário não logado');
                $response->redirect('/login');
                return;
            }
            
            $pagina = (int) ($request->get('pagina') ?? 1);
            $tipo = $request->get('tipo') ?? '';
            $lida = $request->get('lida') ?? '';
            
            $filtros = [];
            if ($tipo) $filtros['tipo'] = $tipo;
            if ($lida !== '') $filtros['lida'] = $lida;
            
            $this->logDebug('Buscando notificações', ['filtros' => $filtros, 'pagina' => $pagina]);
            $notificacoes = $this->notificacaoModel->findAll($usuarioId, $filtros, $pagina);
            $this->logDebug('Notificações encontradas', ['count' => count($notificacoes)]);
            
            $total = $this->notificacaoModel->countAll($usuarioId, $filtros);
            $this->logDebug('Total', ['total' => $total]);
            
            $this->logDebug('Renderizando view...');
            return $this->render('notificacoes/index', [
                'title' => 'Notificações',
                'notificacoes' => $notificacoes,
                'total' => $total,
                'pagina' => $pagina,
                'porPagina' => 20,
                'filtros' => $filtros
            ]);
        } catch (\Exception $e) {
            $this->logDebug('ERRO NO INDEX', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), 'Base table') !== false) {
                $_SESSION['error'] = 'A tabela de notificações ainda não foi criada. Execute as queries SQL.';
            } else {
                $_SESSION['error'] = 'Erro ao carregar notificações: ' . $e->getMessage();
            }
            $response->redirect('/');
        }
    }
    
    /**
     * Página de configurações
     */
    public function configuracoes(Request $request, Response $response)
    {
        try {
            $this->logDebug('=== CONFIGURAÇÕES ===');
            $this->configModel = new NotificacaoConfig();
            
            $usuarioId = $_SESSION['usuario_id'];
            $config = $this->configModel->findByUsuario($usuarioId);
            
            return $this->render('notificacoes/configuracoes', [
                'title' => 'Configurações de Notificações',
                'config' => $config
            ]);
        } catch (\Exception $e) {
            $this->logDebug('ERRO CONFIGURAÇÕES', ['error' => $e->getMessage()]);
            $_SESSION['error'] = 'Erro ao carregar configurações: ' . $e->getMessage();
            $response->redirect('/');
        }
    }
    
    /**
     * Salva configurações
     */
    public function salvarConfiguracoes(Request $request, Response $response)
    {
        try {
            $this->configModel = new NotificacaoConfig();
            $usuarioId = $_SESSION['usuario_id'];
            $data = $request->all();
            
            // Prepara dados
            $configData = [
                'notificar_vencimentos' => isset($data['notificar_vencimentos']) ? 1 : 0,
                'antecedencia_vencimento' => (int) ($data['antecedencia_vencimento'] ?? 3),
                'notificar_vencidas' => isset($data['notificar_vencidas']) ? 1 : 0,
                'notificar_recorrencias' => isset($data['notificar_recorrencias']) ? 1 : 0,
                'notificar_recebimentos' => isset($data['notificar_recebimentos']) ? 1 : 0,
                'notificar_fluxo_caixa' => isset($data['notificar_fluxo_caixa']) ? 1 : 0,
                'som_ativo' => isset($data['som_ativo']) ? 1 : 0,
                'agrupar_notificacoes' => isset($data['agrupar_notificacoes']) ? 1 : 0,
                'horario_silencio_inicio' => $data['horario_silencio_inicio'] ?? null,
                'horario_silencio_fim' => $data['horario_silencio_fim'] ?? null
            ];
            
            $this->configModel->update($usuarioId, $configData);
            
            $_SESSION['success'] = 'Configurações salvas com sucesso!';
        } catch (\Exception $e) {
            $this->logDebug('ERRO SALVAR CONFIGURAÇÕES', ['error' => $e->getMessage()]);
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        $response->redirect('/notificacoes/configuracoes');
    }
    
    /**
     * API: Busca notificações para dropdown (AJAX)
     */
    public function dropdown(Request $request, Response $response)
    {
        try {
            $this->notificacaoModel = new Notificacao();
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                header('Content-Type: application/json');
                echo json_encode(['notificacoes' => [], 'nao_lidas' => 0]);
                exit;
            }
            
            // Busca notificações diretamente do model
            $notificacoes = $this->notificacaoModel->findAll($usuarioId, [], 1, 10);
            $naoLidas = $this->notificacaoModel->contarNaoLidas($usuarioId);
            
            // Formata para exibição
            foreach ($notificacoes as &$notif) {
                $notif['tempo_relativo'] = $this->tempoRelativo($notif['created_at']);
                $notif['icone_classe'] = $this->getIconeClasse($notif['icone'] ?? 'bell');
                $notif['cor_classe'] = $this->getCorClasse($notif['cor'] ?? 'blue');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'notificacoes' => $notificacoes,
                'nao_lidas' => $naoLidas
            ]);
            exit;
        } catch (\Exception $e) {
            $this->logDebug('ERRO DROPDOWN', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['notificacoes' => [], 'nao_lidas' => 0, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Calcula tempo relativo
     */
    private function tempoRelativo($data)
    {
        $timestamp = strtotime($data);
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'Agora';
        if ($diff < 3600) return floor($diff / 60) . ' min atrás';
        if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
        if ($diff < 604800) return floor($diff / 86400) . 'd atrás';
        
        return date('d/m/Y', $timestamp);
    }
    
    /**
     * API: Marca notificação como lida
     */
    public function marcarLida(Request $request, Response $response, $id)
    {
        try {
            $this->notificacaoModel = new Notificacao();
            $this->notificacaoModel->marcarComoLida($id);
        } catch (\Exception $e) {
            $this->logDebug('ERRO MARCAR LIDA', ['error' => $e->getMessage()]);
        }
        
        if ($request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        $response->redirect('/notificacoes');
    }
    
    /**
     * API: Conta notificações não lidas
     */
    public function contarNaoLidas(Request $request, Response $response)
    {
        try {
            $this->notificacaoModel = new Notificacao();
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            $count = $usuarioId ? $this->notificacaoModel->contarNaoLidas($usuarioId) : 0;
            
            header('Content-Type: application/json');
            echo json_encode(['count' => $count]);
            exit;
        } catch (\Exception $e) {
            $this->logDebug('ERRO CONTAR', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            exit;
        }
    }
    
    /**
     * API: Marca todas como lidas
     */
    public function marcarTodasLidas(Request $request, Response $response)
    {
        try {
            $this->notificacaoModel = new Notificacao();
            $usuarioId = $_SESSION['usuario_id'];
            $this->notificacaoModel->marcarTodasComoLidas($usuarioId);
        } catch (\Exception $e) {
            $this->logDebug('ERRO MARCAR TODAS', ['error' => $e->getMessage()]);
        }
        
        if ($request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        $_SESSION['success'] = 'Todas as notificações foram marcadas como lidas!';
        $response->redirect('/notificacoes');
    }
    
    /**
     * Exclui notificação
     */
    public function delete(Request $request, Response $response, $id)
    {
        try {
            $this->notificacaoModel = new Notificacao();
            $this->notificacaoModel->delete($id);
        } catch (\Exception $e) {
            $this->logDebug('ERRO DELETE', ['error' => $e->getMessage()]);
        }
        
        if ($request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        $_SESSION['success'] = 'Notificação excluída!';
        $response->redirect('/notificacoes');
    }
    
    /**
     * API: Salva subscription do Web Push
     */
    public function salvarPushSubscription(Request $request, Response $response)
    {
        try {
            $this->configModel = new NotificacaoConfig();
            $usuarioId = $_SESSION['usuario_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['endpoint'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Endpoint inválido']);
                exit;
            }
            
            $this->configModel->salvarSubscription(
                $usuarioId,
                $data['endpoint'],
                $data['keys']['p256dh'] ?? '',
                $data['keys']['auth'] ?? ''
            );
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (\Exception $e) {
            $this->logDebug('ERRO SALVAR PUSH', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * API: Remove subscription do Web Push
     */
    public function removerPushSubscription(Request $request, Response $response)
    {
        try {
            $this->configModel = new NotificacaoConfig();
            $usuarioId = $_SESSION['usuario_id'];
            $this->configModel->removerSubscription($usuarioId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (\Exception $e) {
            $this->logDebug('ERRO REMOVER PUSH', ['error' => $e->getMessage()]);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Retorna classe do ícone
     */
    private function getIconeClasse($icone)
    {
        $icones = [
            'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'exclamation-circle' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'cash' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'chart-line' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'
        ];
        
        return $icones[$icone] ?? $icones['bell'];
    }
    
    /**
     * Retorna classe da cor
     */
    private function getCorClasse($cor)
    {
        $cores = [
            'blue' => 'text-blue-500 bg-blue-100 dark:bg-blue-900/30',
            'red' => 'text-red-500 bg-red-100 dark:bg-red-900/30',
            'green' => 'text-green-500 bg-green-100 dark:bg-green-900/30',
            'yellow' => 'text-yellow-500 bg-yellow-100 dark:bg-yellow-900/30',
            'orange' => 'text-orange-500 bg-orange-100 dark:bg-orange-900/30',
            'indigo' => 'text-indigo-500 bg-indigo-100 dark:bg-indigo-900/30',
            'gray' => 'text-gray-500 bg-gray-100 dark:bg-gray-900/30'
        ];
        
        return $cores[$cor] ?? $cores['blue'];
    }
}
