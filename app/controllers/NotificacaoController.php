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
    
    public function __construct()
    {
        parent::__construct();
        $this->notificacaoModel = new Notificacao();
        $this->configModel = new NotificacaoConfig();
        $this->notificacaoService = new NotificacaoService();
    }
    
    /**
     * Lista todas as notificações
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $pagina = (int) ($request->get('pagina') ?? 1);
        $tipo = $request->get('tipo') ?? '';
        $lida = $request->get('lida') ?? '';
        
        $filtros = [];
        if ($tipo) $filtros['tipo'] = $tipo;
        if ($lida !== '') $filtros['lida'] = $lida;
        
        $notificacoes = $this->notificacaoModel->findAll($usuarioId, $filtros, $pagina);
        $total = $this->notificacaoModel->countAll($usuarioId, $filtros);
        
        return $this->render('notificacoes/index', [
            'title' => 'Notificações',
            'notificacoes' => $notificacoes,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => 20,
            'filtros' => $filtros
        ]);
    }
    
    /**
     * Página de configurações
     */
    public function configuracoes(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $config = $this->configModel->findByUsuario($usuarioId);
        
        return $this->render('notificacoes/configuracoes', [
            'title' => 'Configurações de Notificações',
            'config' => $config
        ]);
    }
    
    /**
     * Salva configurações
     */
    public function salvarConfiguracoes(Request $request, Response $response)
    {
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
        $response->redirect('/notificacoes/configuracoes');
    }
    
    /**
     * API: Busca notificações para dropdown (AJAX)
     */
    public function dropdown(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $dados = $this->notificacaoService->buscarParaDropdown($usuarioId);
        
        // Formata para exibição
        foreach ($dados['notificacoes'] as &$notif) {
            $notif['tempo_relativo'] = NotificacaoService::tempoRelativo($notif['created_at']);
            $notif['icone_classe'] = $this->getIconeClasse($notif['icone']);
            $notif['cor_classe'] = $this->getCorClasse($notif['cor']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($dados);
        exit;
    }
    
    /**
     * API: Marca notificação como lida
     */
    public function marcarLida(Request $request, Response $response, $id)
    {
        $this->notificacaoModel->marcarComoLida($id);
        
        if ($request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        $response->redirect('/notificacoes');
    }
    
    /**
     * API: Marca todas como lidas
     */
    public function marcarTodasLidas(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $this->notificacaoModel->marcarTodasComoLidas($usuarioId);
        
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
        $this->notificacaoModel->delete($id);
        
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
    }
    
    /**
     * API: Remove subscription do Web Push
     */
    public function removerPushSubscription(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $this->configModel->removerSubscription($usuarioId);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * API: Conta não lidas (para atualização periódica)
     */
    public function contarNaoLidas(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'];
        $count = $this->notificacaoModel->contarNaoLidas($usuarioId);
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
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
