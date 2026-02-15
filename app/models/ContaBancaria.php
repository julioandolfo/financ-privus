<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Contas Bancárias
 */
class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as contas bancárias com dados da conexão API (se houver).
     * 
     * O saldo_atual agora é substituído pelo saldo real da API quando 
     * a conta tem uma conexão bancária vinculada e o saldo foi atualizado.
     */
    public function findAll($empresaId = null)
    {
        $sql = "SELECT cb.*,
                       cx.id as conexao_id,
                       cx.banco as conexao_banco,
                       cx.saldo_banco as saldo_api,
                       cx.saldo_atualizado_em as saldo_api_atualizado_em,
                       cx.status_conexao,
                       cx.ultima_sincronizacao,
                       cx.identificacao as conexao_identificacao
                FROM {$this->table} cb
                LEFT JOIN conexoes_bancarias cx ON cx.conta_bancaria_id = cb.id AND cx.ativo = 1
                WHERE cb.ativo = 1";
        $params = [];
        
        if ($empresaId) {
            $sql .= " AND cb.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " ORDER BY cb.banco_nome ASC, cb.agencia ASC, cb.conta ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $contas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Substituir saldo_atual pelo saldo da API quando disponível
        foreach ($contas as &$conta) {
            $conta['saldo_calculado'] = (float) $conta['saldo_atual']; // Saldo baseado em movimentações
            $conta['tem_conexao_api'] = !empty($conta['conexao_id']);
            
            if ($conta['tem_conexao_api'] && $conta['saldo_api'] !== null) {
                $conta['saldo_atual'] = (float) $conta['saldo_api'];
                $conta['fonte_saldo'] = 'api';
            } else {
                $conta['fonte_saldo'] = 'calculado';
            }
            
            // Diferença entre saldo API e calculado
            $conta['diferenca_saldo'] = $conta['saldo_atual'] - $conta['saldo_calculado'];
        }
        unset($conta);

        return $contas;
    }
    
    /**
     * Retorna uma conta bancária por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna conta bancária por agência e conta
     */
    public function findByAgenciaConta($agencia, $conta, $empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE agencia = :agencia AND conta = :conta";
        $params = [
            'agencia' => $agencia,
            'conta' => $conta
        ];
        
        if ($empresaId) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conta bancária
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, banco_codigo, banco_nome, agencia, conta, tipo_conta, saldo_inicial, saldo_atual, ativo) 
                VALUES 
                (:empresa_id, :banco_codigo, :banco_nome, :agencia, :conta, :tipo_conta, :saldo_inicial, :saldo_atual, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        
        $saldoInicial = isset($data['saldo_inicial']) ? (float)$data['saldo_inicial'] : 0;
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'banco_codigo' => $data['banco_codigo'],
            'banco_nome' => $data['banco_nome'],
            'agencia' => $data['agencia'],
            'conta' => $data['conta'],
            'tipo_conta' => $data['tipo_conta'] ?? 'corrente',
            'saldo_inicial' => $saldoInicial,
            'saldo_atual' => $saldoInicial, // Saldo atual começa igual ao inicial
            'ativo' => $data['ativo'] ?? 1
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma conta bancária
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                empresa_id = :empresa_id,
                banco_codigo = :banco_codigo,
                banco_nome = :banco_nome,
                agencia = :agencia,
                conta = :conta,
                tipo_conta = :tipo_conta,
                ativo = :ativo";
        
        $params = [
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'banco_codigo' => $data['banco_codigo'],
            'banco_nome' => $data['banco_nome'],
            'agencia' => $data['agencia'],
            'conta' => $data['conta'],
            'tipo_conta' => $data['tipo_conta'] ?? 'corrente',
            'ativo' => $data['ativo'] ?? 1
        ];
        
        // Atualiza saldo inicial se fornecido
        if (isset($data['saldo_inicial'])) {
            $sql .= ", saldo_inicial = :saldo_inicial";
            $params['saldo_inicial'] = (float)$data['saldo_inicial'];
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Atualiza o saldo atual da conta
     */
    public function atualizarSaldo($id, $valor, $operacao = 'adicionar')
    {
        if ($operacao === 'adicionar') {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual + :valor WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual - :valor WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'valor' => abs((float)$valor)
        ]);
    }
    
    /**
     * Exclui uma conta bancária (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Retorna o saldo total de todas as contas de uma empresa.
     * Prioriza saldo real da API quando disponível.
     */
    public function getSaldoTotalEmpresa($empresaId)
    {
        // Saldo real: prioriza saldo_banco da conexão, senão usa saldo_atual calculado
        $sql = "SELECT 
                    SUM(
                        CASE 
                            WHEN cx.saldo_banco IS NOT NULL AND cx.ativo = 1 
                            THEN cx.saldo_banco 
                            ELSE cb.saldo_atual 
                        END
                    ) as saldo_total,
                    SUM(cb.saldo_atual) as saldo_calculado,
                    SUM(CASE WHEN cx.saldo_banco IS NOT NULL AND cx.ativo = 1 THEN cx.saldo_banco ELSE 0 END) as saldo_api,
                    COUNT(cb.id) as total_contas,
                    COUNT(cx.id) as contas_com_api
                FROM {$this->table} cb
                LEFT JOIN conexoes_bancarias cx ON cx.conta_bancaria_id = cb.id AND cx.ativo = 1
                WHERE cb.empresa_id = :empresa_id AND cb.ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['saldo_total'] ?? 0;
    }

    /**
     * Retorna resumo completo de saldos com comparativo API vs calculado.
     */
    public function getResumoSaldos($empresaId)
    {
        $sql = "SELECT 
                    SUM(cb.saldo_atual) as saldo_calculado_total,
                    SUM(CASE WHEN cx.saldo_banco IS NOT NULL AND cx.ativo = 1 THEN cx.saldo_banco ELSE 0 END) as saldo_api_total,
                    SUM(
                        CASE 
                            WHEN cx.saldo_banco IS NOT NULL AND cx.ativo = 1 
                            THEN cx.saldo_banco 
                            ELSE cb.saldo_atual 
                        END
                    ) as saldo_real_total,
                    COUNT(cb.id) as total_contas,
                    SUM(CASE WHEN cx.id IS NOT NULL AND cx.ativo = 1 THEN 1 ELSE 0 END) as contas_conectadas,
                    MIN(cx.saldo_atualizado_em) as saldo_mais_antigo
                FROM {$this->table} cb
                LEFT JOIN conexoes_bancarias cx ON cx.conta_bancaria_id = cb.id AND cx.ativo = 1
                WHERE cb.empresa_id = :empresa_id AND cb.ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'saldo_calculado_total' => 0,
            'saldo_api_total' => 0,
            'saldo_real_total' => 0,
            'total_contas' => 0,
            'contas_conectadas' => 0,
            'saldo_mais_antigo' => null
        ];
    }

    /**
     * Seta o saldo_atual diretamente (chamado pela sincronização da API bancária).
     * Usado para sincronizar o saldo real do banco com a conta interna.
     */
    public function setSaldoReal($id, $saldo)
    {
        $sql = "UPDATE {$this->table} SET saldo_atual = :saldo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'saldo' => $saldo]);
    }

    /**
     * Atualiza saldo adicionando ou subtraindo um valor ao saldo atual.
     * Compatibilidade com MovimentacaoService.
     */
    public function updateSaldoAtual($id, $valor)
    {
        if ($valor >= 0) {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual + :valor WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->table} SET saldo_atual = saldo_atual - :valor WHERE id = :id";
            $valor = abs($valor);
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'valor' => $valor]);
    }

    /**
     * Retorna conta bancária por ID com dados da conexão API.
     */
    public function findByIdComConexao($id)
    {
        $sql = "SELECT cb.*,
                       cx.id as conexao_id,
                       cx.banco as conexao_banco,
                       cx.saldo_banco as saldo_api,
                       cx.saldo_atualizado_em as saldo_api_atualizado_em,
                       cx.status_conexao,
                       cx.ultima_sincronizacao,
                       cx.identificacao as conexao_identificacao
                FROM {$this->table} cb
                LEFT JOIN conexoes_bancarias cx ON cx.conta_bancaria_id = cb.id AND cx.ativo = 1
                WHERE cb.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $conta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conta) {
            $conta['saldo_calculado'] = (float) $conta['saldo_atual'];
            $conta['tem_conexao_api'] = !empty($conta['conexao_id']);
            
            if ($conta['tem_conexao_api'] && $conta['saldo_api'] !== null) {
                $conta['saldo_atual'] = (float) $conta['saldo_api'];
                $conta['fonte_saldo'] = 'api';
            } else {
                $conta['fonte_saldo'] = 'calculado';
            }
            
            $conta['diferenca_saldo'] = $conta['saldo_atual'] - $conta['saldo_calculado'];
        }
        
        return $conta;
    }
}
