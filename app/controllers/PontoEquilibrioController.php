<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Empresa;
use includes\services\PontoEquilibrioService;

class PontoEquilibrioController extends Controller
{
    private $empresaModel;
    private $peService;

    public function __construct()
    {
        parent::__construct();
        $this->empresaModel = new Empresa();
        $this->peService = new PontoEquilibrioService();
    }

    /**
     * Tela completa de Ponto de Equilíbrio
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $request->get('empresa_id', '');
        $empresasIds = $request->get('empresas_ids', []);
        if (is_string($empresasIds)) {
            $empresasIds = $empresasIds ? explode(',', $empresasIds) : [];
        }
        $dataInicio = $request->get('data_inicio', date('Y-m-01'));
        $dataFim = $request->get('data_fim', date('Y-m-t'));

        if (!empty($empresaId)) {
            $empresasIds = [$empresaId];
        }
        if (empty($empresasIds)) {
            $todas = $this->empresaModel->findAll(['ativo' => 1]);
            $empresasIds = array_column($todas, 'id');
        }
        $empresasIds = array_map('intval', (array)$empresasIds);

        $resultado = $this->peService->calcularPorEmpresa($empresasIds, $dataInicio, $dataFim);
        $consolidado = $resultado['consolidado'] ?? [];
        $porEmpresa = $resultado['por_empresa'] ?? [];

        $custosFixosDetalhe = $this->peService->detalharCustosFixos($empresasIds, $dataInicio, $dataFim);
        $custosVariaveisDetalhe = $this->peService->detalharCustosVariaveis($empresasIds, $dataInicio, $dataFim);

        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $empresasMap = [];
        foreach ($empresas as $e) {
            $empresasMap[$e['id']] = $e;
        }

        $porEmpresaComNome = [];
        foreach ($porEmpresa as $empId => $dados) {
            $nome = $empresasMap[$empId]['nome_fantasia'] ?? "Empresa #$empId";
            $porEmpresaComNome[$empId] = array_merge($dados, ['empresa_nome' => $nome]);
        }

        $labelsGrafico = ['Consolidado'];
        $valoresPE = [($consolidado['ponto_equilibrio'] ?? 0)];
        foreach ($porEmpresaComNome as $d) {
            $labelsGrafico[] = $d['empresa_nome'];
            $valoresPE[] = $d['ponto_equilibrio'] ?? 0;
        }

        return $this->render('ponto_equilibrio/index', [
            'title' => 'Ponto de Equilíbrio',
            'filters' => [
                'empresa_id' => $empresaId,
                'empresas_ids' => $empresasIds,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
            ],
            'empresas' => $empresas,
            'consolidado' => $consolidado,
            'por_empresa' => $porEmpresaComNome,
            'custos_fixos_detalhe' => $custosFixosDetalhe,
            'custos_variaveis_detalhe' => $custosVariaveisDetalhe,
            'grafico' => [
                'labels' => $labelsGrafico,
                'valores_pe' => $valoresPE,
            ],
            'periodo_label' => $this->formatarPeriodo($dataInicio, $dataFim),
        ]);
    }

    private function formatarPeriodo(string $inicio, string $fim): string
    {
        $di = \DateTime::createFromFormat('Y-m-d', $inicio);
        $df = \DateTime::createFromFormat('Y-m-d', $fim);
        if (!$di || !$df) return "$inicio a $fim";
        return $di->format('d/m/Y') . ' a ' . $df->format('d/m/Y');
    }
}
