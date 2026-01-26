<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\LogSistema;

/**
 * Controller para Visualização de Logs do Sistema
 */
class LogController extends Controller
{
    private $logModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->logModel = new LogSistema();
    }
    
    /**
     * Lista todos os logs
     */
    public function index(Request $request, Response $response)
    {
        $filters = [
            'tipo' => $request->get('tipo', ''),
            'modulo' => $request->get('modulo', ''),
            'acao' => $request->get('acao', ''),
            'search' => $request->get('search', ''),
            'data_inicio' => $request->get('data_inicio', ''),
            'data_fim' => $request->get('data_fim', ''),
        ];
        
        $page = (int)$request->get('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $logs = $this->logModel->findAll($filters, $limit, $offset);
        $total = $this->logModel->count($filters);
        $totalPages = ceil($total / $limit);
        
        return $this->render('logs/index', [
            'title' => 'Logs do Sistema',
            'logs' => $logs,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    /**
     * Limpar logs antigos
     */
    public function limpar(Request $request, Response $response)
    {
        $dias = (int)$request->post('dias', 30);
        
        if ($dias === 0) {
            $this->logModel->limparTodos();
            $_SESSION['success'] = 'Todos os logs foram limpos!';
        } else {
            $this->logModel->limparAntigos($dias);
            $_SESSION['success'] = "Logs com mais de {$dias} dias foram limpos!";
        }
        
        return $response->redirect('/logs');
    }
}
