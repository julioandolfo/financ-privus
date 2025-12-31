<?php
namespace includes\services;

use App\Models\Empresa;
use Exception;

/**
 * Service para gerenciar consolidação de múltiplas empresas
 */
class ConsolidacaoService
{
    private $empresaModel;
    
    public function __construct()
    {
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Valida seleção de empresas para consolidação
     * 
     * @param array $empresasIds Array de IDs das empresas
     * @return array Array de erros (vazio se válido)
     */
    public function validarSelecao($empresasIds)
    {
        $erros = [];
        
        if (empty($empresasIds) || !is_array($empresasIds)) {
            $erros[] = 'Nenhuma empresa foi selecionada para consolidação';
            return $erros;
        }
        
        if (count($empresasIds) < 2) {
            $erros[] = 'Selecione pelo menos 2 empresas para consolidação';
            return $erros;
        }
        
        // Valida se todas as empresas existem e estão ativas
        foreach ($empresasIds as $empresaId) {
            $empresa = $this->empresaModel->findById($empresaId);
            if (!$empresa) {
                $erros[] = "Empresa ID {$empresaId} não encontrada";
            } elseif (!$empresa['ativo']) {
                $erros[] = "Empresa {$empresa['nome_fantasia']} está inativa";
            }
        }
        
        return $erros;
    }
    
    /**
     * Prepara query SQL com filtro de empresas para consolidação
     * 
     * @param array $empresasIds Array de IDs das empresas
     * @param string $campoEmpresa Nome do campo empresa_id na query
     * @return array ['sql' => string, 'params' => array]
     */
    public function prepararFiltroEmpresas($empresasIds, $campoEmpresa = 'empresa_id')
    {
        $placeholders = implode(',', array_fill(0, count($empresasIds), '?'));
        $sql = "{$campoEmpresa} IN ({$placeholders})";
        
        return [
            'sql' => $sql,
            'params' => $empresasIds
        ];
    }
    
    /**
     * Agrupa dados consolidados por empresa
     * 
     * @param array $dados Dados a agrupar
     * @param string $campoEmpresa Nome do campo que contém o ID da empresa
     * @return array Dados agrupados por empresa
     */
    public function agruparPorEmpresa($dados, $campoEmpresa = 'empresa_id')
    {
        $agrupado = [];
        
        foreach ($dados as $item) {
            $empresaId = $item[$campoEmpresa];
            if (!isset($agrupado[$empresaId])) {
                $agrupado[$empresaId] = [];
            }
            $agrupado[$empresaId][] = $item;
        }
        
        return $agrupado;
    }
    
    /**
     * Soma valores consolidados
     * 
     * @param array $dados Dados com valores a somar
     * @param string $campoValor Nome do campo que contém o valor
     * @return float Soma total
     */
    public function somarValores($dados, $campoValor = 'valor')
    {
        $total = 0;
        
        foreach ($dados as $item) {
            $total += floatval($item[$campoValor]);
        }
        
        return $total;
    }
    
    /**
     * Calcula totais consolidados por categoria
     * 
     * @param array $dados Dados a consolidar
     * @param string $campoCategoria Nome do campo categoria
     * @param string $campoValor Nome do campo valor
     * @return array Totais por categoria
     */
    public function consolidarPorCategoria($dados, $campoCategoria = 'categoria_id', $campoValor = 'valor')
    {
        $consolidado = [];
        
        foreach ($dados as $item) {
            $categoriaId = $item[$campoCategoria];
            
            if (!isset($consolidado[$categoriaId])) {
                $consolidado[$categoriaId] = [
                    'categoria_id' => $categoriaId,
                    'categoria_nome' => $item['categoria_nome'] ?? '',
                    'total' => 0,
                    'quantidade' => 0
                ];
            }
            
            $consolidado[$categoriaId]['total'] += floatval($item[$campoValor]);
            $consolidado[$categoriaId]['quantidade']++;
        }
        
        return array_values($consolidado);
    }
    
    /**
     * Calcula totais consolidados por centro de custo
     * 
     * @param array $dados Dados a consolidar
     * @param string $campoCentro Nome do campo centro de custo
     * @param string $campoValor Nome do campo valor
     * @return array Totais por centro de custo
     */
    public function consolidarPorCentroCusto($dados, $campoCentro = 'centro_custo_id', $campoValor = 'valor')
    {
        $consolidado = [];
        
        foreach ($dados as $item) {
            $centroId = $item[$campoCentro] ?? 'sem_centro';
            
            if (!isset($consolidado[$centroId])) {
                $consolidado[$centroId] = [
                    'centro_custo_id' => $centroId !== 'sem_centro' ? $centroId : null,
                    'centro_custo_nome' => $item['centro_custo_nome'] ?? 'Sem Centro de Custo',
                    'total' => 0,
                    'quantidade' => 0
                ];
            }
            
            $consolidado[$centroId]['total'] += floatval($item[$campoValor]);
            $consolidado[$centroId]['quantidade']++;
        }
        
        return array_values($consolidado);
    }
    
    /**
     * Formata empresas para exibição
     * 
     * @param array $empresasIds Array de IDs das empresas
     * @return string Nomes das empresas formatados
     */
    public function formatarNomesEmpresas($empresasIds)
    {
        $empresas = $this->empresaModel->findByIds($empresasIds);
        $nomes = array_column($empresas, 'nome_fantasia');
        
        if (count($nomes) <= 3) {
            return implode(', ', $nomes);
        }
        
        return implode(', ', array_slice($nomes, 0, 2)) . " e mais " . (count($nomes) - 2) . " empresas";
    }
    
    /**
     * Verifica se deve eliminar transações entre empresas do grupo
     * (para evitar duplicação em consolidação)
     * 
     * @param array $empresasIds Array de IDs das empresas consolidadas
     * @param int $empresaOrigemId ID da empresa de origem
     * @param int $empresaDestinoId ID da empresa de destino
     * @return bool True se deve eliminar
     */
    public function deveEliminarTransacao($empresasIds, $empresaOrigemId, $empresaDestinoId)
    {
        return in_array($empresaOrigemId, $empresasIds) && in_array($empresaDestinoId, $empresasIds);
    }
}
