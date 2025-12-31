<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\MovimentacaoCaixa;
use App\Models\Empresa;
use App\Models\ContaBancaria;

class FluxoCaixaController extends Controller
{
    private $movimentacaoModel;
    private $empresaModel;
    private $contaBancariaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->movimentacaoModel = new MovimentacaoCaixa();
        $this->empresaModel = new Empresa();
        $this->contaBancariaModel = new ContaBancaria();
    }
    
    /**
     * Exibe o relatório de fluxo de caixa
     */
    public function index(Request $request, Response $response)
    {
        // Filtros
        $empresaId = $request->get('empresa_id', '');
        $empresasIds = $request->get('empresas_ids', []);
        $contaBancariaId = $request->get('conta_bancaria_id', '');
        $dataInicio = $request->get('data_inicio', date('Y-m-01')); // Primeiro dia do mês atual
        $dataFim = $request->get('data_fim', date('Y-m-t')); // Último dia do mês atual
        $agruparPor = $request->get('agrupar_por', 'dia'); // dia, semana, mes
        $visualizacao = $request->get('visualizacao', 'grafico'); // grafico ou tabela
        
        // Se houver múltiplas empresas selecionadas (consolidação)
        if (!empty($empresasIds) && is_array($empresasIds)) {
            $empresaId = '';
        } elseif ($empresaId) {
            $empresasIds = [$empresaId];
        }
        
        // Buscar dados
        $filters = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if ($contaBancariaId) {
            $filters['conta_bancaria_id'] = $contaBancariaId;
        }
        
        if (!empty($empresasIds)) {
            $filters['empresas_ids'] = $empresasIds;
        } elseif ($empresaId) {
            $filters['empresa_id'] = $empresaId;
        }
        
        // Buscar movimentações
        $movimentacoes = $this->movimentacaoModel->findAll($filters);
        
        // Processar dados por período
        $dadosAgrupados = $this->agruparPorPeriodo($movimentacoes, $agruparPor, $dataInicio, $dataFim);
        
        // Calcular totais
        $totalEntradas = 0;
        $totalSaidas = 0;
        foreach ($movimentacoes as $mov) {
            if ($mov['tipo'] === 'entrada') {
                $totalEntradas += $mov['valor'];
            } else {
                $totalSaidas += $mov['valor'];
            }
        }
        $saldoPeriodo = $totalEntradas - $totalSaidas;
        
        // Buscar saldo inicial (movimentações antes do período)
        $saldoInicial = $this->calcularSaldoInicial($filters, $dataInicio);
        $saldoFinal = $saldoInicial + $saldoPeriodo;
        
        // Dados para os filtros
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $contasBancarias = $this->contaBancariaModel->findAll();
        
        // Preparar dados para o gráfico
        $labels = [];
        $dataEntradas = [];
        $dataSaidas = [];
        $dataSaldo = [];
        
        $saldoAcumulado = $saldoInicial;
        foreach ($dadosAgrupados as $periodo => $dados) {
            $labels[] = $this->formatarPeriodo($periodo, $agruparPor);
            $dataEntradas[] = $dados['entradas'];
            $dataSaidas[] = $dados['saidas'];
            $saldoAcumulado += ($dados['entradas'] - $dados['saidas']);
            $dataSaldo[] = $saldoAcumulado;
        }
        
        return $this->render('fluxo_caixa/index', [
            'title' => 'Fluxo de Caixa',
            'filters' => [
                'empresa_id' => $empresaId,
                'empresas_ids' => $empresasIds,
                'conta_bancaria_id' => $contaBancariaId,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'agrupar_por' => $agruparPor,
                'visualizacao' => $visualizacao
            ],
            'empresas' => $empresas,
            'contasBancarias' => $contasBancarias,
            'totais' => [
                'entradas' => $totalEntradas,
                'saidas' => $totalSaidas,
                'saldo_periodo' => $saldoPeriodo,
                'saldo_inicial' => $saldoInicial,
                'saldo_final' => $saldoFinal
            ],
            'dadosAgrupados' => $dadosAgrupados,
            'grafico' => [
                'labels' => json_encode($labels),
                'entradas' => json_encode($dataEntradas),
                'saidas' => json_encode($dataSaidas),
                'saldo' => json_encode($dataSaldo)
            ]
        ]);
    }
    
    /**
     * Agrupa movimentações por período
     */
    private function agruparPorPeriodo($movimentacoes, $agruparPor, $dataInicio, $dataFim)
    {
        $dados = [];
        
        // Inicializar todos os períodos com zero
        $periodos = $this->gerarPeriodos($dataInicio, $dataFim, $agruparPor);
        foreach ($periodos as $periodo) {
            $dados[$periodo] = [
                'entradas' => 0,
                'saidas' => 0,
                'saldo' => 0,
                'movimentacoes' => []
            ];
        }
        
        // Agrupar movimentações
        foreach ($movimentacoes as $mov) {
            $data = $mov['data_movimentacao'];
            $periodo = $this->obterPeriodo($data, $agruparPor);
            
            if (isset($dados[$periodo])) {
                if ($mov['tipo'] === 'entrada') {
                    $dados[$periodo]['entradas'] += $mov['valor'];
                } else {
                    $dados[$periodo]['saidas'] += $mov['valor'];
                }
                $dados[$periodo]['saldo'] = $dados[$periodo]['entradas'] - $dados[$periodo]['saidas'];
                $dados[$periodo]['movimentacoes'][] = $mov;
            }
        }
        
        return $dados;
    }
    
    /**
     * Gera lista de períodos
     */
    private function gerarPeriodos($dataInicio, $dataFim, $agruparPor)
    {
        $periodos = [];
        $inicio = new \DateTime($dataInicio);
        $fim = new \DateTime($dataFim);
        
        while ($inicio <= $fim) {
            $periodos[] = $this->obterPeriodo($inicio->format('Y-m-d'), $agruparPor);
            
            switch ($agruparPor) {
                case 'dia':
                    $inicio->modify('+1 day');
                    break;
                case 'semana':
                    $inicio->modify('+1 week');
                    break;
                case 'mes':
                    $inicio->modify('+1 month');
                    break;
            }
        }
        
        return array_unique($periodos);
    }
    
    /**
     * Obtém o período de uma data
     */
    private function obterPeriodo($data, $agruparPor)
    {
        $dt = new \DateTime($data);
        
        switch ($agruparPor) {
            case 'dia':
                return $dt->format('Y-m-d');
            case 'semana':
                return $dt->format('Y') . '-W' . $dt->format('W');
            case 'mes':
                return $dt->format('Y-m');
            default:
                return $dt->format('Y-m-d');
        }
    }
    
    /**
     * Formata período para exibição
     */
    private function formatarPeriodo($periodo, $agruparPor)
    {
        switch ($agruparPor) {
            case 'dia':
                $dt = new \DateTime($periodo);
                return $dt->format('d/m/Y');
            case 'semana':
                $parts = explode('-W', $periodo);
                return 'Sem ' . $parts[1] . '/' . $parts[0];
            case 'mes':
                $dt = new \DateTime($periodo . '-01');
                return $dt->format('m/Y');
            default:
                return $periodo;
        }
    }
    
    /**
     * Calcula saldo inicial (antes do período)
     */
    private function calcularSaldoInicial($filters, $dataInicio)
    {
        $filtersInicial = $filters;
        unset($filtersInicial['data_inicio']);
        $filtersInicial['data_fim'] = date('Y-m-d', strtotime($dataInicio . ' -1 day'));
        
        $movimentacoesAnteriores = $this->movimentacaoModel->findAll($filtersInicial);
        
        $saldo = 0;
        foreach ($movimentacoesAnteriores as $mov) {
            if ($mov['tipo'] === 'entrada') {
                $saldo += $mov['valor'];
            } else {
                $saldo -= $mov['valor'];
            }
        }
        
        return $saldo;
    }
}
