<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class TransacaoPendente extends Model
{
    protected $table = 'transacoes_pendentes';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna transações pendentes de uma empresa
     */
    public function findByEmpresa($empresaId, $filtros = [])
    {
        $sql = "SELECT tp.*, 
                       cb.banco, cb.tipo as tipo_conexao, cb.identificacao,
                       cat.nome as categoria_sugerida_nome,
                       cc.nome as centro_custo_sugerido_nome,
                       f.nome_razao_social as fornecedor_sugerido_nome,
                       cli.nome_razao_social as cliente_sugerido_nome
                FROM {$this->table} tp
                LEFT JOIN conexoes_bancarias cb ON tp.conexao_bancaria_id = cb.id
                LEFT JOIN categorias_financeiras cat ON tp.categoria_sugerida_id = cat.id
                LEFT JOIN centros_custo cc ON tp.centro_custo_sugerido_id = cc.id
                LEFT JOIN fornecedores f ON tp.fornecedor_sugerido_id = f.id
                LEFT JOIN clientes cli ON tp.cliente_sugerido_id = cli.id
                WHERE tp.empresa_id = :empresa_id";
        
        $params = ['empresa_id' => $empresaId];
        
        // Filtro por status
        if (!empty($filtros['status'])) {
            $sql .= " AND tp.status = :status";
            $params['status'] = $filtros['status'];
        } else {
            // Por padrão, mostrar apenas pendentes
            $sql .= " AND tp.status = 'pendente'";
        }
        
        // Filtro por tipo (debito/credito)
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tp.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        
        // Filtro por banco/origem
        if (!empty($filtros['banco'])) {
            $sql .= " AND (cb.banco = :banco OR tp.origem = :origem)";
            $params['banco'] = $filtros['banco'];
            $params['origem'] = $filtros['banco'];
        }
        
        // Filtro por período
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND tp.data_transacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND tp.data_transacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $sql .= " ORDER BY tp.data_transacao DESC, tp.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        foreach ($results as &$row) {
            if (!empty($row['dados_extras'])) {
                $row['dados_extras'] = json_decode($row['dados_extras'], true) ?: [];
            } else {
                $row['dados_extras'] = [];
            }
        }
        
        return $results;
    }
    
    /**
     * Cria uma nova transação pendente
     */
    public function create($data)
    {
        // Gerar hash para evitar duplicatas
        $hash = $this->gerarHash($data);
        
        // Verificar se já existe
        if ($this->existePorHash($hash)) {
            return false; // Duplicata detectada
        }
        
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, conexao_bancaria_id, data_transacao, descricao_original,
                 valor, tipo, origem, referencia_externa, transacao_hash,
                 categoria_sugerida_id, centro_custo_sugerido_id, 
                 fornecedor_sugerido_id, cliente_sugerido_id, 
                 confianca_ia, justificativa_ia,
                 banco_transacao_id, metodo_pagamento, saldo_apos, dados_extras) 
                VALUES 
                (:empresa_id, :conexao_bancaria_id, :data_transacao, :descricao_original,
                 :valor, :tipo, :origem, :referencia_externa, :transacao_hash,
                 :categoria_sugerida_id, :centro_custo_sugerido_id,
                 :fornecedor_sugerido_id, :cliente_sugerido_id,
                 :confianca_ia, :justificativa_ia,
                 :banco_transacao_id, :metodo_pagamento, :saldo_apos, :dados_extras)";
        
        $stmt = $this->db->prepare($sql);
        
        $dadosExtras = $data['dados_extras'] ?? null;
        if (is_array($dadosExtras)) {
            $dadosExtras = json_encode($dadosExtras, JSON_UNESCAPED_UNICODE);
        }
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'conexao_bancaria_id' => $data['conexao_bancaria_id'],
            'data_transacao' => $data['data_transacao'],
            'descricao_original' => $data['descricao_original'],
            'valor' => $data['valor'],
            'tipo' => $data['tipo'],
            'origem' => $data['origem'],
            'referencia_externa' => $data['referencia_externa'] ?? null,
            'transacao_hash' => $hash,
            'categoria_sugerida_id' => $data['categoria_sugerida_id'] ?? null,
            'centro_custo_sugerido_id' => $data['centro_custo_sugerido_id'] ?? null,
            'fornecedor_sugerido_id' => $data['fornecedor_sugerido_id'] ?? null,
            'cliente_sugerido_id' => $data['cliente_sugerido_id'] ?? null,
            'confianca_ia' => $data['confianca_ia'] ?? null,
            'justificativa_ia' => $data['justificativa_ia'] ?? null,
            'banco_transacao_id' => $data['banco_transacao_id'] ?? null,
            'metodo_pagamento' => $data['metodo_pagamento'] ?? null,
            'saldo_apos' => $data['saldo_apos'] ?? null,
            'dados_extras' => $dadosExtras
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Aprovar transação
     */
    public function aprovar($id, $usuarioId, $observacao = null)
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'aprovada', 
                    aprovada_por = :usuario_id, 
                    aprovada_em = NOW(),
                    observacao = :observacao
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'usuario_id' => $usuarioId,
            'observacao' => $observacao
        ]);
    }
    
    /**
     * Ignorar transação
     */
    public function ignorar($id, $usuarioId, $observacao = null)
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'ignorada', 
                    aprovada_por = :usuario_id, 
                    aprovada_em = NOW(),
                    observacao = :observacao
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'usuario_id' => $usuarioId,
            'observacao' => $observacao
        ]);
    }
    
    /**
     * Vincular a conta a pagar criada
     */
    public function vincularContaPagar($id, $contaPagarId)
    {
        $sql = "UPDATE {$this->table} SET conta_pagar_id = :conta_pagar_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'conta_pagar_id' => $contaPagarId]);
    }
    
    /**
     * Vincular a conta a receber criada
     */
    public function vincularContaReceber($id, $contaReceberId)
    {
        $sql = "UPDATE {$this->table} SET conta_receber_id = :conta_receber_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'conta_receber_id' => $contaReceberId]);
    }
    
    /**
     * Contar transações pendentes por empresa
     */
    public function countByEmpresa($empresaId, $status = 'pendente')
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND status = :status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'status' => $status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Estatísticas por empresa
     */
    public function getEstatisticas($empresaId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as aprovadas,
                    SUM(CASE WHEN status = 'ignorada' THEN 1 ELSE 0 END) as ignoradas,
                    SUM(CASE WHEN tipo = 'debito' THEN ABS(valor) ELSE 0 END) as total_debitos,
                    SUM(CASE WHEN tipo = 'credito' THEN valor ELSE 0 END) as total_creditos
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Gerar hash único para detectar duplicatas.
     * Usa banco_transacao_id quando disponível (mais confiável).
     */
    private function gerarHash($data)
    {
        // Se temos o ID da transação no banco, usar como base (mais preciso)
        if (!empty($data['banco_transacao_id'])) {
            $string = sprintf(
                '%d|%s|%s',
                $data['empresa_id'],
                $data['conexao_bancaria_id'] ?? '',
                $data['banco_transacao_id']
            );
        } else {
            $string = sprintf(
                '%d|%s|%s|%s',
                $data['empresa_id'],
                $data['data_transacao'],
                $data['valor'],
                $data['descricao_original']
            );
        }
        
        return md5($string);
    }
    
    /**
     * Verificar se transação já existe por hash
     */
    private function existePorHash($hash)
    {
        $sql = "SELECT id FROM {$this->table} WHERE transacao_hash = :hash LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['hash' => $hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Buscar transação por ID
     */
    public function findById($id)
    {
        $sql = "SELECT tp.*, 
                       cb.banco, cb.tipo as tipo_conexao, cb.identificacao,
                       cat.nome as categoria_sugerida_nome,
                       cc.nome as centro_custo_sugerido_nome
                FROM {$this->table} tp
                LEFT JOIN conexoes_bancarias cb ON tp.conexao_bancaria_id = cb.id
                LEFT JOIN categorias_financeiras cat ON tp.categoria_sugerida_id = cat.id
                LEFT JOIN centros_custo cc ON tp.centro_custo_sugerido_id = cc.id
                WHERE tp.id = :id LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['dados_extras'])) {
            $result['dados_extras'] = json_decode($result['dados_extras'], true) ?: [];
        } elseif ($result) {
            $result['dados_extras'] = [];
        }
        
        return $result;
    }
}
