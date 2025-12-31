<?php
namespace includes\services;

use App\Models\MovimentacaoCaixa;
use App\Models\ContaBancaria;
use Exception;

/**
 * Service para gerenciar movimentações de caixa
 */
class MovimentacaoService
{
    private $movimentacaoModel;
    private $contaBancariaModel;
    
    public function __construct()
    {
        $this->movimentacaoModel = new MovimentacaoCaixa();
        $this->contaBancariaModel = new ContaBancaria();
    }
    
    /**
     * Cria movimentação a partir de baixa de conta a pagar
     * 
     * @param int $contaPagarId ID da conta a pagar
     * @param array $dadosBaixa Dados da baixa (valor, data, conta_bancaria_id, etc)
     * @return int|false ID da movimentação criada ou false
     */
    public function criarMovimentacaoPagamento($contaPagarId, $dadosBaixa)
    {
        try {
            $dados = [
                'empresa_id' => $dadosBaixa['empresa_id'],
                'tipo' => 'saida',
                'categoria_id' => $dadosBaixa['categoria_id'],
                'centro_custo_id' => $dadosBaixa['centro_custo_id'] ?? null,
                'conta_bancaria_id' => $dadosBaixa['conta_bancaria_id'],
                'descricao' => $dadosBaixa['descricao'] ?? 'Pagamento de conta',
                'valor' => $dadosBaixa['valor_pago'],
                'data_movimentacao' => $dadosBaixa['data_pagamento'],
                'data_competencia' => $dadosBaixa['data_competencia'] ?? null,
                'forma_pagamento_id' => $dadosBaixa['forma_pagamento_id'] ?? null,
                'referencia_id' => $contaPagarId,
                'referencia_tipo' => 'conta_pagar',
                'observacoes' => $dadosBaixa['observacoes'] ?? null
            ];
            
            $movimentacaoId = $this->movimentacaoModel->create($dados);
            
            if ($movimentacaoId) {
                // Atualiza saldo da conta bancária
                $this->atualizarSaldoConta($dadosBaixa['conta_bancaria_id'], -$dadosBaixa['valor_pago']);
            }
            
            return $movimentacaoId;
            
        } catch (Exception $e) {
            error_log("Erro ao criar movimentação de pagamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cria movimentação a partir de baixa de conta a receber
     * 
     * @param int $contaReceberId ID da conta a receber
     * @param array $dadosBaixa Dados da baixa (valor, data, conta_bancaria_id, etc)
     * @return int|false ID da movimentação criada ou false
     */
    public function criarMovimentacaoRecebimento($contaReceberId, $dadosBaixa)
    {
        try {
            $dados = [
                'empresa_id' => $dadosBaixa['empresa_id'],
                'tipo' => 'entrada',
                'categoria_id' => $dadosBaixa['categoria_id'],
                'centro_custo_id' => $dadosBaixa['centro_custo_id'] ?? null,
                'conta_bancaria_id' => $dadosBaixa['conta_bancaria_id'],
                'descricao' => $dadosBaixa['descricao'] ?? 'Recebimento de conta',
                'valor' => $dadosBaixa['valor_recebido'],
                'data_movimentacao' => $dadosBaixa['data_recebimento'],
                'data_competencia' => $dadosBaixa['data_competencia'] ?? null,
                'forma_pagamento_id' => $dadosBaixa['forma_recebimento_id'] ?? null,
                'referencia_id' => $contaReceberId,
                'referencia_tipo' => 'conta_receber',
                'observacoes' => $dadosBaixa['observacoes'] ?? null
            ];
            
            $movimentacaoId = $this->movimentacaoModel->create($dados);
            
            if ($movimentacaoId) {
                // Atualiza saldo da conta bancária
                $this->atualizarSaldoConta($dadosBaixa['conta_bancaria_id'], $dadosBaixa['valor_recebido']);
            }
            
            return $movimentacaoId;
            
        } catch (Exception $e) {
            error_log("Erro ao criar movimentação de recebimento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza saldo de uma conta bancária
     * 
     * @param int $contaBancariaId ID da conta bancária
     * @param float $valor Valor a adicionar (positivo) ou subtrair (negativo)
     * @return bool
     */
    private function atualizarSaldoConta($contaBancariaId, $valor)
    {
        try {
            return $this->contaBancariaModel->updateSaldoAtual($contaBancariaId, $valor);
        } catch (Exception $e) {
            error_log("Erro ao atualizar saldo da conta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida dados de uma movimentação
     * 
     * @param array $dados Dados da movimentação
     * @return array Array de erros (vazio se válido)
     */
    public function validar($dados)
    {
        $erros = [];
        
        if (empty($dados['empresa_id'])) {
            $erros['empresa_id'] = 'Empresa é obrigatória';
        }
        
        if (empty($dados['tipo']) || !in_array($dados['tipo'], ['entrada', 'saida'])) {
            $erros['tipo'] = 'Tipo deve ser "entrada" ou "saida"';
        }
        
        if (empty($dados['categoria_id'])) {
            $erros['categoria_id'] = 'Categoria é obrigatória';
        }
        
        if (empty($dados['conta_bancaria_id'])) {
            $erros['conta_bancaria_id'] = 'Conta bancária é obrigatória';
        }
        
        if (empty($dados['descricao'])) {
            $erros['descricao'] = 'Descrição é obrigatória';
        }
        
        if (empty($dados['valor']) || $dados['valor'] <= 0) {
            $erros['valor'] = 'Valor deve ser maior que zero';
        }
        
        if (empty($dados['data_movimentacao'])) {
            $erros['data_movimentacao'] = 'Data de movimentação é obrigatória';
        }
        
        return $erros;
    }
    
    /**
     * Estorna uma movimentação (reverte)
     * 
     * @param int $movimentacaoId ID da movimentação a estornar
     * @return bool
     */
    public function estornar($movimentacaoId)
    {
        try {
            $movimentacao = $this->movimentacaoModel->findById($movimentacaoId);
            
            if (!$movimentacao) {
                return false;
            }
            
            // Reverte o saldo na conta bancária
            $valorEstorno = ($movimentacao['tipo'] == 'entrada') ? -$movimentacao['valor'] : $movimentacao['valor'];
            $this->atualizarSaldoConta($movimentacao['conta_bancaria_id'], $valorEstorno);
            
            // Remove a movimentação
            return $this->movimentacaoModel->delete($movimentacaoId);
            
        } catch (Exception $e) {
            error_log("Erro ao estornar movimentação: " . $e->getMessage());
            return false;
        }
    }
}
