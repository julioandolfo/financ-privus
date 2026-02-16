<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class SaldoHistorico extends Model
{
    protected $table = 'saldo_historico';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra um snapshot de saldo no histórico.
     */
    public function registrar(int $conexaoId, array $dados): int
    {
        $sql = "INSERT INTO {$this->table} 
                (conexao_bancaria_id, empresa_id, conta_bancaria_id, 
                 saldo_contabil, saldo_disponivel, saldo_limite, saldo_bloqueado,
                 tx_futuras, soma_futuros_debito, soma_futuros_credito,
                 data_referencia, fonte)
                VALUES 
                (:conexao_id, :empresa_id, :conta_id,
                 :saldo_contabil, :saldo_disponivel, :saldo_limite, :saldo_bloqueado,
                 :tx_futuras, :soma_futuros_debito, :soma_futuros_credito,
                 :data_referencia, :fonte)";
        
        $saldoContabil = $dados['saldo_contabil'] ?? 0;
        $saldoLimite = $dados['saldo_limite'] ?? 0;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conexao_id' => $conexaoId,
            'empresa_id' => $dados['empresa_id'] ?? null,
            'conta_id' => $dados['conta_bancaria_id'] ?? null,
            'saldo_contabil' => $saldoContabil,
            'saldo_disponivel' => $saldoContabil + $saldoLimite,
            'saldo_limite' => $saldoLimite,
            'saldo_bloqueado' => $dados['saldo_bloqueado'] ?? 0,
            'tx_futuras' => $dados['tx_futuras'] ?? 0,
            'soma_futuros_debito' => $dados['soma_futuros_debito'] ?? 0,
            'soma_futuros_credito' => $dados['soma_futuros_credito'] ?? 0,
            'data_referencia' => $dados['data_referencia'] ?? null,
            'fonte' => $dados['fonte'] ?? 'api',
        ]);
        
        return (int) $this->db->lastInsertId();
    }

    /**
     * Histórico de uma conexão (mais recente primeiro).
     */
    public function findByConexao(int $conexaoId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE conexao_bancaria_id = :conexao_id 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('conexao_id', $conexaoId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Histórico por empresa (todas as conexões).
     */
    public function findByEmpresa(int $empresaId, int $limit = 200): array
    {
        $sql = "SELECT sh.*, cb.identificacao, cb.banco, cb.banco_conta_id
                FROM {$this->table} sh
                JOIN conexoes_bancarias cb ON cb.id = sh.conexao_bancaria_id
                WHERE sh.empresa_id = :empresa_id 
                ORDER BY sh.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Saldo diário (último registro de cada dia) para gráficos.
     */
    public function saldoDiario(int $conexaoId, string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT 
                    DATE(created_at) as data,
                    saldo_contabil,
                    saldo_disponivel,
                    saldo_limite,
                    tx_futuras,
                    soma_futuros_debito,
                    soma_futuros_credito,
                    created_at
                FROM {$this->table} sh1
                WHERE conexao_bancaria_id = :conexao_id
                  AND DATE(created_at) BETWEEN :data_inicio AND :data_fim
                  AND created_at = (
                      SELECT MAX(sh2.created_at) 
                      FROM {$this->table} sh2 
                      WHERE sh2.conexao_bancaria_id = sh1.conexao_bancaria_id 
                        AND DATE(sh2.created_at) = DATE(sh1.created_at)
                  )
                ORDER BY data ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conexao_id' => $conexaoId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Saldo diário consolidado por empresa (soma de todas as contas).
     */
    public function saldoDiarioEmpresa(int $empresaId, string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT 
                    DATE(sh1.created_at) as data,
                    SUM(sh1.saldo_contabil) as saldo_contabil_total,
                    SUM(sh1.saldo_disponivel) as saldo_disponivel_total,
                    SUM(sh1.saldo_limite) as saldo_limite_total,
                    COUNT(DISTINCT sh1.conexao_bancaria_id) as total_contas
                FROM {$this->table} sh1
                WHERE sh1.empresa_id = :empresa_id
                  AND DATE(sh1.created_at) BETWEEN :data_inicio AND :data_fim
                  AND sh1.created_at = (
                      SELECT MAX(sh2.created_at) 
                      FROM {$this->table} sh2 
                      WHERE sh2.conexao_bancaria_id = sh1.conexao_bancaria_id 
                        AND DATE(sh2.created_at) = DATE(sh1.created_at)
                  )
                GROUP BY DATE(sh1.created_at)
                ORDER BY data ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Último saldo registrado de cada conexão de uma empresa.
     */
    public function ultimoSaldoPorEmpresa(int $empresaId): array
    {
        $sql = "SELECT sh.*, cb.identificacao, cb.banco, cb.banco_conta_id
                FROM {$this->table} sh
                JOIN conexoes_bancarias cb ON cb.id = sh.conexao_bancaria_id
                WHERE sh.empresa_id = :empresa_id
                  AND sh.created_at = (
                      SELECT MAX(sh2.created_at) 
                      FROM {$this->table} sh2 
                      WHERE sh2.conexao_bancaria_id = sh.conexao_bancaria_id
                  )
                ORDER BY cb.identificacao";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Variação de saldo entre dois períodos.
     */
    public function variacaoSaldo(int $conexaoId, string $dataInicio, string $dataFim): ?array
    {
        $primeiro = $this->fetchOne(
            "SELECT saldo_contabil, saldo_disponivel, created_at 
             FROM {$this->table} 
             WHERE conexao_bancaria_id = :id AND DATE(created_at) >= :data 
             ORDER BY created_at ASC LIMIT 1",
            ['id' => $conexaoId, 'data' => $dataInicio]
        );
        
        $ultimo = $this->fetchOne(
            "SELECT saldo_contabil, saldo_disponivel, created_at 
             FROM {$this->table} 
             WHERE conexao_bancaria_id = :id AND DATE(created_at) <= :data 
             ORDER BY created_at DESC LIMIT 1",
            ['id' => $conexaoId, 'data' => $dataFim]
        );
        
        if (!$primeiro || !$ultimo) return null;
        
        $varContabil = $ultimo['saldo_contabil'] - $primeiro['saldo_contabil'];
        $varDisponivel = $ultimo['saldo_disponivel'] - $primeiro['saldo_disponivel'];
        
        return [
            'periodo_inicio' => $primeiro['created_at'],
            'periodo_fim' => $ultimo['created_at'],
            'saldo_inicio' => $primeiro['saldo_contabil'],
            'saldo_fim' => $ultimo['saldo_contabil'],
            'variacao_contabil' => $varContabil,
            'variacao_disponivel' => $varDisponivel,
            'variacao_percentual' => $primeiro['saldo_contabil'] != 0 
                ? round(($varContabil / abs($primeiro['saldo_contabil'])) * 100, 2) 
                : null,
        ];
    }

    /**
     * Estatísticas do período (min, max, média).
     */
    public function estatisticas(int $conexaoId, string $dataInicio, string $dataFim): ?array
    {
        $sql = "SELECT 
                    MIN(saldo_contabil) as saldo_minimo,
                    MAX(saldo_contabil) as saldo_maximo,
                    AVG(saldo_contabil) as saldo_medio,
                    MIN(saldo_disponivel) as disponivel_minimo,
                    MAX(saldo_disponivel) as disponivel_maximo,
                    AVG(saldo_disponivel) as disponivel_medio,
                    COUNT(*) as total_registros,
                    MIN(created_at) as primeiro_registro,
                    MAX(created_at) as ultimo_registro
                FROM {$this->table}
                WHERE conexao_bancaria_id = :conexao_id
                  AND DATE(created_at) BETWEEN :data_inicio AND :data_fim";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conexao_id' => $conexaoId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Total de registros de uma conexão.
     */
    public function countByConexao(int $conexaoId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE conexao_bancaria_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $conexaoId]);
        return (int) $stmt->fetchColumn();
    }
}
