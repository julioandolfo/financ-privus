<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class BoletoHistorico extends Model
{
    protected $table = 'boletos_historico';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra um evento no histórico do boleto.
     */
    public function registrar(int $boletoId, string $tipoEvento, string $descricao = '', array $dados = [], ?int $usuarioId = null): int
    {
        $sql = "INSERT INTO {$this->table} (boleto_id, tipo_evento, descricao, dados_json, usuario_id)
                VALUES (:boleto_id, :tipo_evento, :descricao, :dados_json, :usuario_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'boleto_id' => $boletoId,
            'tipo_evento' => $tipoEvento,
            'descricao' => $descricao,
            'dados_json' => !empty($dados) ? json_encode($dados, JSON_UNESCAPED_UNICODE) : null,
            'usuario_id' => $usuarioId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Retorna histórico de um boleto (timeline).
     */
    public function findByBoleto(int $boletoId): array
    {
        $sql = "SELECT bh.*, u.nome as usuario_nome
                FROM {$this->table} bh
                LEFT JOIN usuarios u ON bh.usuario_id = u.id
                WHERE bh.boleto_id = :boleto_id
                ORDER BY bh.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['boleto_id' => $boletoId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($results as &$row) {
            if (!empty($row['dados_json'])) {
                $row['dados_json'] = json_decode($row['dados_json'], true) ?: [];
            }
        }

        return $results;
    }

    /**
     * Retorna últimos eventos de todos os boletos de uma empresa.
     */
    public function ultimosEventos(int $empresaId, int $limit = 20): array
    {
        $sql = "SELECT bh.*, b.nosso_numero, b.pagador_nome, b.valor, b.situacao
                FROM {$this->table} bh
                INNER JOIN boletos b ON bh.boleto_id = b.id
                WHERE b.empresa_id = :empresa_id AND b.deleted_at IS NULL
                ORDER BY bh.created_at DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
