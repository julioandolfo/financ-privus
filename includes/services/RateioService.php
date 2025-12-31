<?php
namespace includes\services;

use Exception;

/**
 * Service para gerenciar rateios de pagamentos e recebimentos entre empresas
 */
class RateioService
{
    /**
     * Valida um conjunto de rateios
     * 
     * @param array $rateios Array de rateios com empresas e valores
     * @param float $valorTotal Valor total que deve ser rateado
     * @return array Retorna array vazio se válido, ou array de erros
     */
    public function validarRateios($rateios, $valorTotal)
    {
        $erros = [];
        
        if (empty($rateios)) {
            $erros[] = 'Nenhum rateio foi informado';
            return $erros;
        }
        
        $somaValores = 0;
        $somaPercentuais = 0;
        $empresasUsadas = [];
        
        foreach ($rateios as $index => $rateio) {
            // Validar empresa_id
            if (empty($rateio['empresa_id'])) {
                $erros[] = "Rateio #{$index}: Empresa é obrigatória";
                continue;
            }
            
            // Validar empresa duplicada
            if (in_array($rateio['empresa_id'], $empresasUsadas)) {
                $erros[] = "Rateio #{$index}: Empresa já foi adicionada ao rateio";
            }
            $empresasUsadas[] = $rateio['empresa_id'];
            
            // Validar valor_rateio
            if (!isset($rateio['valor_rateio']) || $rateio['valor_rateio'] < 0) {
                $erros[] = "Rateio #{$index}: Valor deve ser maior ou igual a zero";
            }
            
            // Validar percentual
            if (isset($rateio['percentual'])) {
                if ($rateio['percentual'] < 0 || $rateio['percentual'] > 100) {
                    $erros[] = "Rateio #{$index}: Percentual deve estar entre 0 e 100";
                }
                $somaPercentuais += floatval($rateio['percentual']);
            }
            
            $somaValores += floatval($rateio['valor_rateio']);
            
            // Validar data_competencia
            if (empty($rateio['data_competencia'])) {
                $erros[] = "Rateio #{$index}: Data de competência é obrigatória";
            }
        }
        
        // Validar soma dos valores
        $diferenca = abs($somaValores - $valorTotal);
        if ($diferenca > 0.01) { // Tolerância de 1 centavo para arredondamentos
            $erros[] = "A soma dos valores rateados (R$ " . number_format($somaValores, 2, ',', '.') . 
                       ") não é igual ao valor total (R$ " . number_format($valorTotal, 2, ',', '.') . ")";
        }
        
        // Validar soma dos percentuais (se informados)
        if ($somaPercentuais > 0) {
            $diferencaPercentual = abs($somaPercentuais - 100);
            if ($diferencaPercentual > 0.01) {
                $erros[] = "A soma dos percentuais ({$somaPercentuais}%) deve ser igual a 100%";
            }
        }
        
        return $erros;
    }
    
    /**
     * Calcula percentuais baseado em valores
     * 
     * @param array $rateios Array de rateios com valores
     * @param float $valorTotal Valor total
     * @return array Rateios com percentuais calculados
     */
    public function calcularPercentuais($rateios, $valorTotal)
    {
        if ($valorTotal == 0) {
            return $rateios;
        }
        
        foreach ($rateios as &$rateio) {
            $rateio['percentual'] = ($rateio['valor_rateio'] / $valorTotal) * 100;
        }
        
        return $rateios;
    }
    
    /**
     * Calcula valores baseado em percentuais
     * 
     * @param array $rateios Array de rateios com percentuais
     * @param float $valorTotal Valor total
     * @return array Rateios com valores calculados
     */
    public function calcularValores($rateios, $valorTotal)
    {
        foreach ($rateios as &$rateio) {
            $rateio['valor_rateio'] = ($rateio['percentual'] / 100) * $valorTotal;
        }
        
        return $rateios;
    }
    
    /**
     * Ajusta rateios para garantir que a soma seja exata
     * Adiciona/subtrai diferença no maior rateio
     * 
     * @param array $rateios Array de rateios
     * @param float $valorTotal Valor total esperado
     * @return array Rateios ajustados
     */
    public function ajustarDiferenca($rateios, $valorTotal)
    {
        $somaAtual = array_sum(array_column($rateios, 'valor_rateio'));
        $diferenca = $valorTotal - $somaAtual;
        
        if (abs($diferenca) > 0.01) {
            // Encontra o maior rateio
            $maiorIndex = 0;
            $maiorValor = 0;
            foreach ($rateios as $index => $rateio) {
                if ($rateio['valor_rateio'] > $maiorValor) {
                    $maiorValor = $rateio['valor_rateio'];
                    $maiorIndex = $index;
                }
            }
            
            // Ajusta o maior rateio
            $rateios[$maiorIndex]['valor_rateio'] += $diferenca;
            
            // Recalcula percentual
            $rateios[$maiorIndex]['percentual'] = ($rateios[$maiorIndex]['valor_rateio'] / $valorTotal) * 100;
        }
        
        return $rateios;
    }
    
    /**
     * Prepara dados de rateios para salvar no banco
     * 
     * @param array $rateios Array de rateios
     * @param int $usuarioId ID do usuário que está criando
     * @return array Rateios preparados
     */
    public function prepararParaSalvar($rateios, $usuarioId)
    {
        $preparados = [];
        
        foreach ($rateios as $rateio) {
            $preparados[] = [
                'empresa_id' => $rateio['empresa_id'],
                'valor_rateio' => $rateio['valor_rateio'],
                'percentual' => $rateio['percentual'] ?? 0,
                'data_competencia' => $rateio['data_competencia'],
                'observacoes' => $rateio['observacoes'] ?? null,
                'usuario_cadastro_id' => $usuarioId
            ];
        }
        
        return $preparados;
    }
    
    /**
     * Distribui valor total proporcionalmente entre N empresas
     * 
     * @param array $empresasIds Array de IDs das empresas
     * @param float $valorTotal Valor total a distribuir
     * @param string $dataCompetencia Data de competência padrão
     * @return array Rateios distribuídos igualmente
     */
    public function distribuirIgualmente($empresasIds, $valorTotal, $dataCompetencia)
    {
        $quantidade = count($empresasIds);
        
        if ($quantidade == 0) {
            return [];
        }
        
        $valorPorEmpresa = $valorTotal / $quantidade;
        $percentualPorEmpresa = 100 / $quantidade;
        
        $rateios = [];
        foreach ($empresasIds as $empresaId) {
            $rateios[] = [
                'empresa_id' => $empresaId,
                'valor_rateio' => round($valorPorEmpresa, 2),
                'percentual' => round($percentualPorEmpresa, 2),
                'data_competencia' => $dataCompetencia
            ];
        }
        
        // Ajusta diferença de arredondamento
        return $this->ajustarDiferenca($rateios, $valorTotal);
    }
}
