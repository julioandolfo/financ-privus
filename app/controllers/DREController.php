<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Empresa;
use App\Models\CategoriaFinanceira;
use App\Models\ContaPagar;
use App\Models\ContaReceber;

class DREController extends Controller
{
    private $empresaModel;
    private $categoriaModel;
    private $contaPagarModel;
    private $contaReceberModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->empresaModel = new Empresa();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
    }
    
    /**
     * Exibe o DRE (Demonstrativo de Resultado do Exercício)
     */
    public function index(Request $request, Response $response)
    {
        // Filtros
        $empresaId = $request->get('empresa_id', '');
        $empresasIds = $request->get('empresas_ids', []);
        $dataInicio = $request->get('data_inicio', date('Y-m-01')); // Primeiro dia do mês
        $dataFim = $request->get('data_fim', date('Y-m-t')); // Último dia do mês
        $visualizacao = $request->get('visualizacao', 'completo'); // completo ou resumido
        
        // Se houver múltiplas empresas
        if (!empty($empresasIds) && is_array($empresasIds)) {
            $empresaId = '';
        } elseif ($empresaId) {
            $empresasIds = [$empresaId];
        }
        
        // Buscar dados usando data de COMPETÊNCIA
        $whereEmpresas = '';
        $params = [
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ];
        
        if (!empty($empresasIds)) {
            $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
            $whereEmpresas = " AND empresa_id IN ($placeholders)";
        }
        
        // Buscar Contas a Receber (RECEITAS) por competência
        $receitas = $this->buscarReceitasPorCategoria($empresasIds, $dataInicio, $dataFim);
        
        // Buscar Contas a Pagar (DESPESAS) por competência
        $despesas = $this->buscarDespesasPorCategoria($empresasIds, $dataInicio, $dataFim);
        
        // Calcular totais
        $totalReceitas = array_sum(array_column($receitas, 'valor'));
        $totalDespesas = array_sum(array_column($despesas, 'valor'));
        $resultadoLiquido = $totalReceitas - $totalDespesas;
        
        // Margem
        $margemLiquida = $totalReceitas > 0 ? ($resultadoLiquido / $totalReceitas) * 100 : 0;
        
        // Dados para filtros
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        
        // Preparar dados para gráfico comparativo
        $categoriasReceitas = array_column($receitas, 'categoria');
        $valoresReceitas = array_column($receitas, 'valor');
        $categoriasDespesas = array_column($despesas, 'categoria');
        $valoresDespesas = array_column($despesas, 'valor');
        
        return $this->render('dre/index', [
            'title' => 'DRE - Demonstrativo de Resultado',
            'filters' => [
                'empresa_id' => $empresaId,
                'empresas_ids' => $empresasIds,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'visualizacao' => $visualizacao
            ],
            'empresas' => $empresas,
            'receitas' => $receitas,
            'despesas' => $despesas,
            'totais' => [
                'receitas' => $totalReceitas,
                'despesas' => $totalDespesas,
                'resultado' => $resultadoLiquido,
                'margem' => $margemLiquida
            ],
            'grafico' => [
                'categorias_receitas' => json_encode($categoriasReceitas),
                'valores_receitas' => json_encode($valoresReceitas),
                'categorias_despesas' => json_encode($categoriasDespesas),
                'valores_despesas' => json_encode($valoresDespesas)
            ]
        ]);
    }
    
    /**
     * Busca receitas agrupadas por categoria usando data de competência
     */
    private function buscarReceitasPorCategoria($empresasIds, $dataInicio, $dataFim)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    c.nome as categoria,
                    c.id as categoria_id,
                    SUM(cr.valor_total) as valor,
                    COUNT(cr.id) as quantidade
                FROM contas_receber cr
                INNER JOIN categorias_financeiras c ON cr.categoria_id = c.id
                WHERE cr.data_competencia BETWEEN :data_inicio AND :data_fim
                  AND c.tipo = 'receita'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if (!empty($empresasIds)) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND cr.empresa_id IN ($placeholders)";
            $stmt = $db->prepare($sql . " GROUP BY c.id, c.nome ORDER BY valor DESC");
            $stmt->execute(array_merge(array_values($params), $empresasIds));
        } else {
            $stmt = $db->prepare($sql . " GROUP BY c.id, c.nome ORDER BY valor DESC");
            $stmt->execute($params);
        }
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca despesas agrupadas por categoria usando data de competência
     */
    private function buscarDespesasPorCategoria($empresasIds, $dataInicio, $dataFim)
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    c.nome as categoria,
                    c.id as categoria_id,
                    SUM(cp.valor_total) as valor,
                    COUNT(cp.id) as quantidade
                FROM contas_pagar cp
                INNER JOIN categorias_financeiras c ON cp.categoria_id = c.id
                WHERE cp.data_competencia BETWEEN :data_inicio AND :data_fim
                  AND c.tipo = 'despesa'";
        
        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ];
        
        if (!empty($empresasIds)) {
            $placeholders = str_repeat('?,', count($empresasIds) - 1) . '?';
            $sql .= " AND cp.empresa_id IN ($placeholders)";
            $stmt = $db->prepare($sql . " GROUP BY c.id, c.nome ORDER BY valor DESC");
            $stmt->execute(array_merge(array_values($params), $empresasIds));
        } else {
            $stmt = $db->prepare($sql . " GROUP BY c.id, c.nome ORDER BY valor DESC");
            $stmt->execute($params);
        }
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
