<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ExtratoBancarioApi extends Model
{
    protected $table = 'extrato_bancario_api';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Inserir transação do extrato (ignora duplicatas via hash)
     */
    public function inserir(array $data): ?int
    {
        $hash = $this->gerarHash($data);
        
        $sql = "INSERT IGNORE INTO {$this->table} 
                (empresa_id, conexao_bancaria_id, conta_bancaria_id, data_transacao, descricao, valor, tipo, 
                 saldo_apos, banco_transacao_id, metodo_pagamento, hash_unico, origem, dados_raw)
                VALUES 
                (:empresa_id, :conexao_bancaria_id, :conta_bancaria_id, :data_transacao, :descricao, :valor, :tipo,
                 :saldo_apos, :banco_transacao_id, :metodo_pagamento, :hash_unico, :origem, :dados_raw)";
        
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'conexao_bancaria_id' => $data['conexao_bancaria_id'],
            'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
            'data_transacao' => $data['data_transacao'],
            'descricao' => $data['descricao'] ?? '',
            'valor' => $data['valor'],
            'tipo' => $data['tipo'],
            'saldo_apos' => $data['saldo_apos'] ?? null,
            'banco_transacao_id' => $data['banco_transacao_id'] ?? null,
            'metodo_pagamento' => $data['metodo_pagamento'] ?? null,
            'hash_unico' => $hash,
            'origem' => $data['origem'] ?? 'api',
            'dados_raw' => !empty($data['dados_raw']) ? json_encode($data['dados_raw']) : null
        ]);
        
        if ($result && $stmt->rowCount() > 0) {
            return (int)$pdo->lastInsertId();
        }
        return null;
    }
    
    /**
     * Buscar extrato por conexão e período
     */
    public function findByConexao(int $conexaoId, ?string $dataInicio = null, ?string $dataFim = null, ?string $tipo = null): array
    {
        $sql = "SELECT e.*, cb.banco_nome, cb.agencia, cb.conta
                FROM {$this->table} e
                LEFT JOIN contas_bancarias cb ON cb.id = e.conta_bancaria_id
                WHERE e.conexao_bancaria_id = :conexao_id";
        $params = ['conexao_id' => $conexaoId];
        
        if ($dataInicio) {
            $sql .= " AND e.data_transacao >= :data_inicio";
            $params['data_inicio'] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND e.data_transacao <= :data_fim";
            $params['data_fim'] = $dataFim;
        }
        if ($tipo && in_array($tipo, ['debito', 'credito'])) {
            $sql .= " AND e.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        
        $sql .= " ORDER BY e.data_transacao DESC, e.id DESC";
        
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Buscar extrato por empresa com filtros
     */
    public function findByEmpresa(int $empresaId, array $filtros = []): array
    {
        $sql = "SELECT e.*, 
                       con.banco, con.identificacao,
                       cb.banco_nome, cb.agencia, cb.conta
                FROM {$this->table} e
                LEFT JOIN conexoes_bancarias con ON con.id = e.conexao_bancaria_id
                LEFT JOIN contas_bancarias cb ON cb.id = e.conta_bancaria_id
                WHERE e.empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];
        
        if (!empty($filtros['conexao_bancaria_id'])) {
            $sql .= " AND e.conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $filtros['conexao_bancaria_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND e.data_transacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND e.data_transacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['tipo']) && in_array($filtros['tipo'], ['debito', 'credito'])) {
            $sql .= " AND e.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND e.descricao LIKE :busca";
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }
        
        $sql .= " ORDER BY e.data_transacao DESC, e.id DESC";
        
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT " . (int)$filtros['limit'];
            if (!empty($filtros['offset'])) {
                $sql .= " OFFSET " . (int)$filtros['offset'];
            }
        }
        
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Contar registros por empresa com filtros
     */
    public function countByEmpresa(int $empresaId, array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} e WHERE e.empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];
        
        if (!empty($filtros['conexao_bancaria_id'])) {
            $sql .= " AND e.conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $filtros['conexao_bancaria_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND e.data_transacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND e.data_transacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['tipo']) && in_array($filtros['tipo'], ['debito', 'credito'])) {
            $sql .= " AND e.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND e.descricao LIKE :busca";
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }
        
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Resumo por tipo (débito/crédito) para uma empresa no período
     */
    public function getResumo(int $empresaId, array $filtros = []): array
    {
        $sql = "SELECT 
                    tipo,
                    COUNT(*) as quantidade,
                    SUM(ABS(valor)) as total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];
        
        if (!empty($filtros['conexao_bancaria_id'])) {
            $sql .= " AND conexao_bancaria_id = :conexao_id";
            $params['conexao_id'] = $filtros['conexao_bancaria_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND data_transacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND data_transacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        
        $sql .= " GROUP BY tipo";
        
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $resumo = [
            'debito' => ['quantidade' => 0, 'total' => 0],
            'credito' => ['quantidade' => 0, 'total' => 0],
        ];
        foreach ($rows as $r) {
            $resumo[$r['tipo']] = [
                'quantidade' => (int)$r['quantidade'],
                'total' => (float)$r['total']
            ];
        }
        return $resumo;
    }
    
    /**
     * Gerar hash único para evitar duplicatas
     */
    private function gerarHash(array $data): string
    {
        $str = implode('|', [
            $data['empresa_id'],
            $data['conexao_bancaria_id'],
            $data['data_transacao'],
            number_format(abs($data['valor']), 2, '.', ''),
            $data['tipo'],
            $data['banco_transacao_id'] ?? '',
            substr($data['descricao'] ?? '', 0, 255)
        ]);
        return hash('sha256', $str);
    }
}
