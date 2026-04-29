<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class WhatsAppRelatorioEnvio extends Model
{
    protected $table = 'whatsapp_relatorio_envios';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table}
                (regra_id, destinatario_id, evolution_config_id, numero, mensagem,
                 status, tentativas, resposta_api, erro, tipo_envio, agendado_para)
                VALUES
                (:regra_id, :destinatario_id, :evolution_config_id, :numero, :mensagem,
                 :status, :tentativas, :resposta_api, :erro, :tipo_envio, :agendado_para)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'regra_id' => $data['regra_id'],
            'destinatario_id' => $data['destinatario_id'] ?? null,
            'evolution_config_id' => $data['evolution_config_id'],
            'numero' => $data['numero'],
            'mensagem' => $data['mensagem'],
            'status' => $data['status'] ?? 'aguardando',
            'tentativas' => $data['tentativas'] ?? 0,
            'resposta_api' => isset($data['resposta_api']) ? json_encode($data['resposta_api'], JSON_UNESCAPED_UNICODE) : null,
            'erro' => $data['erro'] ?? null,
            'tipo_envio' => $data['tipo_envio'] ?? 'automatico',
            'agendado_para' => $data['agendado_para'] ?? null,
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function marcarEnviado($id, $respostaApi)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
               SET status = 'enviado',
                   tentativas = tentativas + 1,
                   resposta_api = :resp,
                   enviado_em = NOW()
             WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'resp' => json_encode($respostaApi, JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function marcarFalha($id, $erro, $respostaApi = null)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
               SET status = 'falha',
                   tentativas = tentativas + 1,
                   resposta_api = :resp,
                   erro = :erro
             WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'erro' => $erro,
            'resp' => $respostaApi ? json_encode($respostaApi, JSON_UNESCAPED_UNICODE) : null
        ]);
    }

    public function findByRegra($regraId, $limit = 50)
    {
        $sql = "SELECT e.*, d.nome AS destinatario_nome
                  FROM {$this->table} e
                  LEFT JOIN whatsapp_relatorio_destinatarios d ON d.id = e.destinatario_id
                 WHERE e.regra_id = :regra_id
                 ORDER BY e.created_at DESC
                 LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['regra_id' => $regraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findRecent($limit = 30, $empresaId = null)
    {
        $sql = "SELECT e.*, r.nome AS regra_nome, r.tipo_relatorio, r.empresa_id
                  FROM {$this->table} e
                  JOIN whatsapp_relatorio_regras r ON r.id = e.regra_id";
        $params = [];
        if ($empresaId !== null) {
            $sql .= " WHERE r.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        $sql .= " ORDER BY e.created_at DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getEstatisticas($empresaId = null, $dias = 7)
    {
        $sql = "SELECT
                  COUNT(*) AS total,
                  SUM(CASE WHEN e.status = 'enviado' THEN 1 ELSE 0 END) AS enviados,
                  SUM(CASE WHEN e.status = 'falha' THEN 1 ELSE 0 END) AS falhas,
                  SUM(CASE WHEN e.status = 'aguardando' THEN 1 ELSE 0 END) AS aguardando
                FROM {$this->table} e
                JOIN whatsapp_relatorio_regras r ON r.id = e.regra_id
                WHERE e.created_at >= DATE_SUB(NOW(), INTERVAL :dias DAY)";
        $params = ['dias' => (int)$dias];
        if ($empresaId !== null) {
            $sql .= " AND r.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'enviados' => 0, 'falhas' => 0, 'aguardando' => 0];
    }
}
