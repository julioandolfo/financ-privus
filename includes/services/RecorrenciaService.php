<?php
namespace Includes\Services;

use App\Models\DespesaRecorrente;
use App\Models\ReceitaRecorrente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\RateioPagamento;
use App\Models\RateioRecebimento;
use App\Core\Database;

/**
 * Service para gerenciamento de recorrências
 */
class RecorrenciaService
{
    private $despesaRecorrenteModel;
    private $receitaRecorrenteModel;
    private $contaPagarModel;
    private $contaReceberModel;
    private $notificacaoService;
    private $movimentacaoService;
    
    public function __construct()
    {
        $this->despesaRecorrenteModel = new DespesaRecorrente();
        $this->receitaRecorrenteModel = new ReceitaRecorrente();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
        $this->notificacaoService = new NotificacaoService();
        $this->movimentacaoService = new MovimentacaoService();
    }
    
    /**
     * Processa todas as recorrências pendentes
     */
    public function processarRecorrencias()
    {
        $resultado = [
            'despesas_geradas' => 0,
            'receitas_geradas' => 0,
            'erros' => []
        ];
        
        // Processa despesas recorrentes
        $despesas = $this->despesaRecorrenteModel->buscarParaGerar();
        foreach ($despesas as $despesa) {
            try {
                $this->gerarDespesa($despesa);
                $resultado['despesas_geradas']++;
            } catch (\Exception $e) {
                $resultado['erros'][] = "Despesa #{$despesa['id']}: " . $e->getMessage();
            }
        }
        
        // Processa receitas recorrentes
        $receitas = $this->receitaRecorrenteModel->buscarParaGerar();
        foreach ($receitas as $receita) {
            try {
                $this->gerarReceita($receita);
                $resultado['receitas_geradas']++;
            } catch (\Exception $e) {
                $resultado['erros'][] = "Receita #{$receita['id']}: " . $e->getMessage();
            }
        }
        
        // Processa reajustes
        $this->processarReajustes();
        
        return $resultado;
    }
    
    /**
     * Gera uma conta a pagar a partir de despesa recorrente
     */
    public function gerarDespesa($despesa)
    {
        $dataVencimento = $despesa['proxima_geracao'];
        $hoje = date('Y-m-d');
        
        // Prepara dados da conta a pagar
        $dadosConta = [
            'empresa_id' => $despesa['empresa_id'],
            'fornecedor_id' => $despesa['fornecedor_id'],
            'categoria_id' => $despesa['categoria_id'],
            'centro_custo_id' => $despesa['centro_custo_id'],
            'numero_documento' => 'REC-' . $despesa['id'] . '-' . date('Ymd', strtotime($dataVencimento)),
            'descricao' => $despesa['descricao'],
            'valor_total' => $despesa['valor'],
            'valor_pago' => 0,
            'data_emissao' => $hoje,
            'data_competencia' => $dataVencimento,
            'data_vencimento' => $dataVencimento,
            'status' => $despesa['status_inicial'],
            'forma_pagamento_id' => $despesa['forma_pagamento_id'],
            'conta_bancaria_id' => $despesa['conta_bancaria_id'],
            'tem_rateio' => 0,
            'observacoes' => $despesa['observacoes'] . "\n[Gerado automaticamente - Recorrência #{$despesa['id']}]",
            'tipo_custo' => $despesa['tipo_custo'],
            'despesa_recorrente_id' => $despesa['id'],
            'usuario_cadastro_id' => $despesa['usuario_cadastro_id']
        ];
        
        // Cria a conta a pagar
        $contaId = $this->contaPagarModel->create($dadosConta);
        
        if (!$contaId) {
            throw new \Exception('Erro ao criar conta a pagar');
        }
        
        // Aplica rateio se configurado na despesa recorrente
        if (!empty($despesa['rateios_json'])) {
            $rateios = json_decode($despesa['rateios_json'], true);
            if (is_array($rateios) && !empty($rateios)) {
                $usuarioId = $despesa['usuario_cadastro_id'] ?? null;
                foreach ($rateios as &$r) {
                    $r['data_competencia'] = $dataVencimento;
                }
                $rateioModel = new RateioPagamento();
                $rateioModel->saveBatch($contaId, $rateios, $usuarioId);
                $this->contaPagarModel->atualizarRateio($contaId, 1);
            }
        }
        
        // Se já é pago, cria movimentação
        if ($despesa['status_inicial'] === 'pago' && $despesa['conta_bancaria_id']) {
            $this->contaPagarModel->atualizarPagamento($contaId, $despesa['valor'], $hoje, 'pago');
            
            $dadosBaixa = [
                'empresa_id' => $despesa['empresa_id'],
                'categoria_id' => $despesa['categoria_id'],
                'centro_custo_id' => $despesa['centro_custo_id'],
                'conta_bancaria_id' => $despesa['conta_bancaria_id'],
                'descricao' => "Pagamento automático: " . $despesa['descricao'],
                'valor' => $despesa['valor'],
                'data_movimento' => $hoje,
                'data_competencia' => $dataVencimento,
                'forma_pagamento_id' => $despesa['forma_pagamento_id']
            ];
            
            $this->movimentacaoService->criarMovimentacaoPagamento($contaId, $dadosBaixa);
        }
        
        // Calcula próxima geração
        $proximaGeracao = $this->despesaRecorrenteModel->calcularProximaGeracao([
            'data_inicio' => $dataVencimento,
            'frequencia' => $despesa['frequencia'],
            'dia_mes' => $despesa['dia_mes'],
            'dia_semana' => $despesa['dia_semana'],
            'intervalo_dias' => $despesa['intervalo_dias'],
            'ajuste_fim_semana' => $despesa['ajuste_fim_semana']
        ]);
        
        // Atualiza recorrência
        $this->despesaRecorrenteModel->atualizarAposGeracao($despesa['id'], $proximaGeracao);
        
        // Notifica usuário
        $this->notificacaoService->notificarRecorrenciaGerada(
            $despesa['usuario_cadastro_id'],
            'despesa',
            $despesa['descricao'],
            $despesa['valor'],
            $contaId
        );
        
        return $contaId;
    }
    
    /**
     * Gera uma conta a receber a partir de receita recorrente
     */
    public function gerarReceita($receita)
    {
        $dataVencimento = $receita['proxima_geracao'];
        $hoje = date('Y-m-d');
        
        // Prepara dados da conta a receber
        $dadosConta = [
            'empresa_id' => $receita['empresa_id'],
            'cliente_id' => $receita['cliente_id'],
            'categoria_id' => $receita['categoria_id'],
            'centro_custo_id' => $receita['centro_custo_id'],
            'numero_documento' => 'REC-' . $receita['id'] . '-' . date('Ymd', strtotime($dataVencimento)),
            'descricao' => $receita['descricao'],
            'valor_total' => $receita['valor'],
            'valor_recebido' => 0,
            'desconto' => 0,
            'data_emissao' => $hoje,
            'data_competencia' => $dataVencimento,
            'data_vencimento' => $dataVencimento,
            'status' => $receita['status_inicial'],
            'observacoes' => $receita['observacoes'] . "\n[Gerado automaticamente - Recorrência #{$receita['id']}]",
            'receita_recorrente_id' => $receita['id'],
            'usuario_cadastro_id' => $receita['usuario_cadastro_id']
        ];
        
        // Cria a conta a receber
        $contaId = $this->contaReceberModel->create($dadosConta);
        
        if (!$contaId) {
            throw new \Exception('Erro ao criar conta a receber');
        }
        
        // Aplica rateio se configurado na receita recorrente
        if (!empty($receita['rateios_json'])) {
            $rateios = json_decode($receita['rateios_json'], true);
            if (is_array($rateios) && !empty($rateios)) {
                $usuarioId = $receita['usuario_cadastro_id'] ?? null;
                foreach ($rateios as &$r) {
                    $r['data_competencia'] = $dataVencimento;
                }
                $rateioModel = new RateioRecebimento();
                $rateioModel->saveBatch($contaId, $rateios, $usuarioId);
                $this->contaReceberModel->atualizarRateio($contaId, 1);
            }
        }
        
        // Se já é recebido, cria movimentação
        if ($receita['status_inicial'] === 'recebido' && $receita['conta_bancaria_id']) {
            $this->contaReceberModel->atualizarRecebimento($contaId, $receita['valor'], $hoje, 'recebido');
            
            $dadosBaixa = [
                'empresa_id' => $receita['empresa_id'],
                'categoria_id' => $receita['categoria_id'],
                'centro_custo_id' => $receita['centro_custo_id'],
                'conta_bancaria_id' => $receita['conta_bancaria_id'],
                'descricao' => "Recebimento automático: " . $receita['descricao'],
                'valor' => $receita['valor'],
                'data_movimento' => $hoje,
                'data_competencia' => $dataVencimento,
                'forma_pagamento_id' => $receita['forma_pagamento_id']
            ];
            
            $this->movimentacaoService->criarMovimentacaoRecebimento($contaId, $dadosBaixa);
        }
        
        // Calcula próxima geração
        $proximaGeracao = $this->receitaRecorrenteModel->calcularProximaGeracao([
            'data_inicio' => $dataVencimento,
            'frequencia' => $receita['frequencia'],
            'dia_mes' => $receita['dia_mes'],
            'dia_semana' => $receita['dia_semana'],
            'intervalo_dias' => $receita['intervalo_dias'],
            'ajuste_fim_semana' => $receita['ajuste_fim_semana']
        ]);
        
        // Atualiza recorrência
        $this->receitaRecorrenteModel->atualizarAposGeracao($receita['id'], $proximaGeracao);
        
        // Notifica usuário
        $this->notificacaoService->notificarRecorrenciaGerada(
            $receita['usuario_cadastro_id'],
            'receita',
            $receita['descricao'],
            $receita['valor'],
            $contaId
        );
        
        return $contaId;
    }
    
    /**
     * Processa reajustes anuais
     */
    public function processarReajustes()
    {
        // Reajustes de despesas
        $despesas = $this->despesaRecorrenteModel->buscarParaReajuste();
        foreach ($despesas as $despesa) {
            $this->despesaRecorrenteModel->aplicarReajuste($despesa['id']);
        }
        
        // Reajustes de receitas
        $receitas = $this->receitaRecorrenteModel->buscarParaReajuste();
        foreach ($receitas as $receita) {
            $this->receitaRecorrenteModel->aplicarReajuste($receita['id']);
        }
    }
    
    /**
     * Cria despesa recorrente a partir de conta a pagar existente
     */
    public function criarDespesaRecorrenteDeContaPagar($contaPagar, $configRecorrencia, $usuarioId)
    {
        $dados = [
            'empresa_id' => $contaPagar['empresa_id'],
            'fornecedor_id' => $contaPagar['fornecedor_id'],
            'categoria_id' => $contaPagar['categoria_id'],
            'centro_custo_id' => $contaPagar['centro_custo_id'],
            'descricao' => $contaPagar['descricao'],
            'valor' => $contaPagar['valor_total'],
            'tipo_custo' => $contaPagar['tipo_custo'] ?? 'variavel',
            'observacoes' => $contaPagar['observacoes'],
            'frequencia' => $configRecorrencia['frequencia'],
            'dia_mes' => $configRecorrencia['dia_mes'] ?? null,
            'dia_semana' => $configRecorrencia['dia_semana'] ?? null,
            'intervalo_dias' => $configRecorrencia['intervalo_dias'] ?? null,
            'data_inicio' => $configRecorrencia['data_inicio'],
            'data_fim' => $configRecorrencia['data_fim'] ?? null,
            'max_ocorrencias' => $configRecorrencia['max_ocorrencias'] ?? null,
            'antecedencia_dias' => $configRecorrencia['antecedencia_dias'] ?? 5,
            'status_inicial' => $configRecorrencia['status_inicial'] ?? 'pendente',
            'criar_automaticamente' => $configRecorrencia['criar_automaticamente'] ?? 1,
            'ajuste_fim_semana' => $configRecorrencia['ajuste_fim_semana'] ?? 'manter',
            'reajuste_ativo' => $configRecorrencia['reajuste_ativo'] ?? 0,
            'reajuste_tipo' => $configRecorrencia['reajuste_tipo'] ?? 'percentual',
            'reajuste_valor' => $configRecorrencia['reajuste_valor'] ?? null,
            'reajuste_mes' => $configRecorrencia['reajuste_mes'] ?? null,
            'forma_pagamento_id' => $contaPagar['forma_pagamento_id'],
            'conta_bancaria_id' => $contaPagar['conta_bancaria_id'],
            'usuario_cadastro_id' => $usuarioId
        ];
        
        return $this->despesaRecorrenteModel->create($dados);
    }
    
    /**
     * Cria receita recorrente a partir de conta a receber existente
     */
    public function criarReceitaRecorrenteDeContaReceber($contaReceber, $configRecorrencia, $usuarioId)
    {
        $dados = [
            'empresa_id' => $contaReceber['empresa_id'],
            'cliente_id' => $contaReceber['cliente_id'],
            'categoria_id' => $contaReceber['categoria_id'],
            'centro_custo_id' => $contaReceber['centro_custo_id'],
            'descricao' => $contaReceber['descricao'],
            'valor' => $contaReceber['valor_total'],
            'observacoes' => $contaReceber['observacoes'],
            'frequencia' => $configRecorrencia['frequencia'],
            'dia_mes' => $configRecorrencia['dia_mes'] ?? null,
            'dia_semana' => $configRecorrencia['dia_semana'] ?? null,
            'intervalo_dias' => $configRecorrencia['intervalo_dias'] ?? null,
            'data_inicio' => $configRecorrencia['data_inicio'],
            'data_fim' => $configRecorrencia['data_fim'] ?? null,
            'max_ocorrencias' => $configRecorrencia['max_ocorrencias'] ?? null,
            'antecedencia_dias' => $configRecorrencia['antecedencia_dias'] ?? 5,
            'status_inicial' => $configRecorrencia['status_inicial'] ?? 'pendente',
            'criar_automaticamente' => $configRecorrencia['criar_automaticamente'] ?? 1,
            'ajuste_fim_semana' => $configRecorrencia['ajuste_fim_semana'] ?? 'manter',
            'reajuste_ativo' => $configRecorrencia['reajuste_ativo'] ?? 0,
            'reajuste_tipo' => $configRecorrencia['reajuste_tipo'] ?? 'percentual',
            'reajuste_valor' => $configRecorrencia['reajuste_valor'] ?? null,
            'reajuste_mes' => $configRecorrencia['reajuste_mes'] ?? null,
            'forma_pagamento_id' => $contaReceber['forma_pagamento_id'] ?? null,
            'conta_bancaria_id' => $contaReceber['conta_bancaria_id'] ?? null,
            'usuario_cadastro_id' => $usuarioId
        ];
        
        return $this->receitaRecorrenteModel->create($dados);
    }
    
    /**
     * Retorna resumo das recorrências
     */
    public function getResumo($empresaId = null)
    {
        $filtros = $empresaId ? ['empresa_id' => $empresaId, 'ativo' => 1] : ['ativo' => 1];
        
        $despesas = $this->despesaRecorrenteModel->findAll($filtros);
        $receitas = $this->receitaRecorrenteModel->findAll($filtros);
        
        $totalDespesas = array_sum(array_column($despesas, 'valor'));
        $totalReceitas = array_sum(array_column($receitas, 'valor'));
        
        return [
            'despesas_count' => count($despesas),
            'receitas_count' => count($receitas),
            'total_despesas' => $totalDespesas,
            'total_receitas' => $totalReceitas,
            'saldo_previsto' => $totalReceitas - $totalDespesas
        ];
    }
}
