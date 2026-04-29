<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class WhatsAppRelatorioDestinatario extends Model
{
    protected $table = 'whatsapp_relatorio_destinatarios';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByRegra($regraId, $apenasAtivos = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE regra_id = :regra_id";
        if ($apenasAtivos) {
            $sql .= " AND ativo = 1";
        }
        $sql .= " ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['regra_id' => $regraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (regra_id, nome, numero, ativo)
                VALUES (:regra_id, :nome, :numero, :ativo)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'regra_id' => $data['regra_id'],
            'nome' => $data['nome'],
            'numero' => self::normalizarNumero($data['numero']),
            'ativo' => !empty($data['ativo']) ? 1 : 0
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, array $data)
    {
        $fields = [];
        $params = ['id' => $id];
        if (array_key_exists('nome', $data)) {
            $fields[] = "nome = :nome";
            $params['nome'] = $data['nome'];
        }
        if (array_key_exists('numero', $data)) {
            $fields[] = "numero = :numero";
            $params['numero'] = self::normalizarNumero($data['numero']);
        }
        if (array_key_exists('ativo', $data)) {
            $fields[] = "ativo = :ativo";
            $params['ativo'] = !empty($data['ativo']) ? 1 : 0;
        }
        if (empty($fields)) return true;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteByRegra($regraId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE regra_id = :regra_id");
        return $stmt->execute(['regra_id' => $regraId]);
    }

    public static function normalizarNumero($numero)
    {
        $apenas = preg_replace('/\D+/', '', (string)$numero);
        return $apenas;
    }

    public static function validarNumero($numero)
    {
        $n = self::normalizarNumero($numero);
        return preg_match('/^\d{10,15}$/', $n) === 1;
    }
}
