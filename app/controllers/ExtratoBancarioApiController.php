<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExtratoBancarioApi;
use App\Models\ConexaoBancaria;
use App\Models\Usuario;

class ExtratoBancarioApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        
        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }
        
        $conexaoModel = new ConexaoBancaria();
        $extratoModel = new ExtratoBancarioApi();
        
        $conexoes = [];
        if ($empresaId) {
            $conexoes = $conexaoModel->findByEmpresa($empresaId);
        }
        
        // Filtros
        $filtros = [
            'conexao_bancaria_id' => $request->get('conexao_bancaria_id'),
            'tipo' => $request->get('tipo'),
            'data_inicio' => $request->get('data_inicio', date('Y-m-01')),
            'data_fim' => $request->get('data_fim', date('Y-m-d')),
            'busca' => $request->get('busca'),
        ];
        
        // Paginação
        $porPagina = 50;
        $pagina = max(1, (int)$request->get('pagina', 1));
        $offset = ($pagina - 1) * $porPagina;
        
        $transacoes = [];
        $totalRegistros = 0;
        $resumo = ['debito' => ['quantidade' => 0, 'total' => 0], 'credito' => ['quantidade' => 0, 'total' => 0]];
        
        if ($empresaId) {
            $totalRegistros = $extratoModel->countByEmpresa($empresaId, $filtros);
            $filtrosComPag = $filtros;
            $filtrosComPag['limit'] = $porPagina;
            $filtrosComPag['offset'] = $offset;
            $transacoes = $extratoModel->findByEmpresa($empresaId, $filtrosComPag);
            $resumo = $extratoModel->getResumo($empresaId, $filtros);
        }
        
        $totalPaginas = max(1, ceil($totalRegistros / $porPagina));
        
        return $this->render('extrato_api/index', [
            'transacoes' => $transacoes,
            'resumo' => $resumo,
            'filtros' => $filtros,
            'conexoes' => $conexoes,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId,
            'paginacao' => [
                'total_registros' => $totalRegistros,
                'por_pagina' => $porPagina,
                'pagina_atual' => $pagina,
                'total_paginas' => $totalPaginas,
            ]
        ]);
    }
}
