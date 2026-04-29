<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class WhatsAppRelatorioRegra extends Model
{
    protected $table = 'whatsapp_relatorio_regras';
    protected $db;

    const TIPOS = [
        'contas_pagar_atrasadas' => 'Contas a Pagar - Atrasadas',
        'contas_pagar_vencer' => 'Contas a Pagar - A Vencer',
        'contas_pagar_vencendo_hoje' => 'Contas a Pagar - Vencendo Hoje',
        'contas_receber_atrasadas' => 'Contas a Receber - Atrasadas',
        'contas_receber_vencer' => 'Contas a Receber - A Vencer',
        'contas_receber_vencendo_hoje' => 'Contas a Receber - Vencendo Hoje',
        'resumo_diario' => 'Resumo Diário (Pagar + Receber)',
        'fluxo_caixa' => 'Fluxo de Caixa do Período',
        'inadimplencia' => 'Inadimplência (Receber Atrasadas)',
    ];

    const FREQUENCIAS = [
        'diaria' => 'Diariamente',
        'semanal' => 'Semanalmente',
        'mensal' => 'Mensalmente',
        'intervalo_horas' => 'A cada N horas',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll($empresaId = null, $ativo = null)
    {
        $sql = "SELECT r.*, ec.nome AS evolution_nome, ec.instance_name, e.nome_fantasia AS empresa_nome
                  FROM {$this->table} r
                  LEFT JOIN evolution_config ec ON ec.id = r.evolution_config_id
                  LEFT JOIN empresas e ON e.id = r.empresa_id
                 WHERE 1=1";
        $params = [];
        if ($empresaId !== null) {
            $sql .= " AND r.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        if ($ativo !== null) {
            $sql .= " AND r.ativo = :ativo";
            $params['ativo'] = $ativo ? 1 : 0;
        }
        $sql .= " ORDER BY r.ativo DESC, r.proxima_execucao ASC, r.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$r) {
            $r['filtros'] = $r['filtros'] ? json_decode($r['filtros'], true) : [];
        }
        return $rows;
    }

    public function findById($id)
    {
        $sql = "SELECT r.*, ec.nome AS evolution_nome, ec.instance_name, ec.base_url, e.nome_fantasia AS empresa_nome
                  FROM {$this->table} r
                  LEFT JOIN evolution_config ec ON ec.id = r.evolution_config_id
                  LEFT JOIN empresas e ON e.id = r.empresa_id
                 WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['filtros'] = $row['filtros'] ? json_decode($row['filtros'], true) : [];
        return $row;
    }

    public function findDevidas($limit = 50)
    {
        $sql = "SELECT r.*, ec.nome AS evolution_nome
                  FROM {$this->table} r
                  JOIN evolution_config ec ON ec.id = r.evolution_config_id
                 WHERE r.ativo = 1
                   AND ec.ativo = 1
                   AND (r.proxima_execucao IS NULL OR r.proxima_execucao <= NOW())
                 ORDER BY r.proxima_execucao ASC
                 LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$r) {
            $r['filtros'] = $r['filtros'] ? json_decode($r['filtros'], true) : [];
        }
        return $rows;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table}
                (empresa_id, evolution_config_id, nome, descricao, ativo, tipo_relatorio, filtros,
                 frequencia, horario, dias_semana, dia_mes, intervalo_horas,
                 template_mensagem, incluir_lista_detalhada, limite_itens_lista,
                 proxima_execucao, usuario_cadastro_id)
                VALUES
                (:empresa_id, :evolution_config_id, :nome, :descricao, :ativo, :tipo_relatorio, :filtros,
                 :frequencia, :horario, :dias_semana, :dia_mes, :intervalo_horas,
                 :template_mensagem, :incluir_lista_detalhada, :limite_itens_lista,
                 :proxima_execucao, :usuario_cadastro_id)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'evolution_config_id' => $data['evolution_config_id'],
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'ativo' => !empty($data['ativo']) ? 1 : 0,
            'tipo_relatorio' => $data['tipo_relatorio'],
            'filtros' => isset($data['filtros']) ? json_encode($data['filtros'], JSON_UNESCAPED_UNICODE) : null,
            'frequencia' => $data['frequencia'],
            'horario' => $data['horario'] ?? null,
            'dias_semana' => $data['dias_semana'] ?? null,
            'dia_mes' => $data['dia_mes'] ?? null,
            'intervalo_horas' => $data['intervalo_horas'] ?? null,
            'template_mensagem' => $data['template_mensagem'] ?? null,
            'incluir_lista_detalhada' => !empty($data['incluir_lista_detalhada']) ? 1 : 0,
            'limite_itens_lista' => $data['limite_itens_lista'] ?? 20,
            'proxima_execucao' => $data['proxima_execucao'] ?? null,
            'usuario_cadastro_id' => $data['usuario_cadastro_id'] ?? null
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, array $data)
    {
        $allowed = ['empresa_id','evolution_config_id','nome','descricao','ativo','tipo_relatorio',
            'frequencia','horario','dias_semana','dia_mes','intervalo_horas',
            'template_mensagem','incluir_lista_detalhada','limite_itens_lista','proxima_execucao'];
        $fields = [];
        $params = ['id' => $id];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $value = $data[$f];
                if (in_array($f, ['ativo','incluir_lista_detalhada'])) {
                    $value = !empty($value) ? 1 : 0;
                }
                $fields[] = "{$f} = :{$f}";
                $params[$f] = $value;
            }
        }
        if (array_key_exists('filtros', $data)) {
            $fields[] = "filtros = :filtros";
            $params['filtros'] = json_encode($data['filtros'], JSON_UNESCAPED_UNICODE);
        }
        if (empty($fields)) return true;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleAtivo($id)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET ativo = 1 - ativo WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function marcarExecutada($id, $status, $proximaExecucao, $erro = null)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
               SET ultima_execucao = NOW(),
                   ultimo_status = :status,
                   ultimo_erro = :erro,
                   proxima_execucao = :proxima
             WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'erro' => $erro,
            'proxima' => $proximaExecucao
        ]);
    }

    public function getEstatisticas($empresaId = null)
    {
        $sql = "SELECT
                  COUNT(*) AS total,
                  SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) AS ativas,
                  SUM(CASE WHEN ultimo_status = 'sucesso' THEN 1 ELSE 0 END) AS com_sucesso,
                  SUM(CASE WHEN ultimo_status = 'falha' THEN 1 ELSE 0 END) AS com_falha
                FROM {$this->table}";
        $params = [];
        if ($empresaId) {
            $sql .= " WHERE empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'ativas' => 0, 'com_sucesso' => 0, 'com_falha' => 0];
    }
}
