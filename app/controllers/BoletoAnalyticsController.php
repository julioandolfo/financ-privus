<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Boleto;
use App\Models\BoletoHistorico;
use App\Models\ConexaoBancaria;
use App\Models\Usuario;

class BoletoAnalyticsController extends Controller
{
    private $boletoModel;
    private $conexaoModel;

    public function __construct()
    {
        parent::__construct();
        $this->boletoModel = new Boleto();
        $this->conexaoModel = new ConexaoBancaria();
    }

    /**
     * Dashboard analytics de boletos.
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);

        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }

        $periodoInicio = $request->get('periodo_inicio');
        $periodoFim = $request->get('periodo_fim');
        $conexaoId = $request->get('conexao_bancaria_id') ? (int)$request->get('conexao_bancaria_id') : null;

        $analytics = $this->boletoModel->getAnalytics($empresaId, $periodoInicio, $periodoFim, $conexaoId);
        $estatisticas = $this->boletoModel->getEstatisticas($empresaId);
        $inadimplentes = $this->boletoModel->getInadimplentes($empresaId, 10);
        $conexoes = $this->conexaoModel->findByEmpresa($empresaId);

        $historicoModel = new BoletoHistorico();
        $ultimosEventos = $historicoModel->ultimosEventos($empresaId, 10);

        $this->render('boletos/analytics', [
            'title' => 'Analytics de Boletos',
            'analytics' => $analytics,
            'estatisticas' => $estatisticas,
            'inadimplentes' => $inadimplentes,
            'ultimosEventos' => $ultimosEventos,
            'conexoes' => $conexoes,
            'empresaId' => $empresaId,
            'empresasUsuario' => $empresasUsuario,
            'periodoInicio' => $periodoInicio,
            'periodoFim' => $periodoFim,
            'conexaoId' => $conexaoId,
        ]);
    }

    /**
     * Endpoint JSON para gráficos dinâmicos.
     */
    public function apiDados(Request $request, Response $response)
    {
        $empresaId = $request->get('empresa_id');
        $periodoInicio = $request->get('periodo_inicio');
        $periodoFim = $request->get('periodo_fim');
        $conexaoId = $request->get('conexao_bancaria_id') ? (int)$request->get('conexao_bancaria_id') : null;

        $analytics = $this->boletoModel->getAnalytics($empresaId, $periodoInicio, $periodoFim, $conexaoId);

        return $response->json(['success' => true, 'data' => $analytics]);
    }
}
