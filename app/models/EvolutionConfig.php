<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class EvolutionConfig extends Model
{
    protected $table = 'evolution_config';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll($empresaId = null, $ativo = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if ($empresaId !== null) {
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
        }
        if ($ativo !== null) {
            $sql .= " AND ativo = :ativo";
            $params['ativo'] = $ativo ? 1 : 0;
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$r) {
            $r['api_key_decrypted'] = $this->decrypt($r['api_key']);
        }
        return $rows;
    }

    public function findById($id, $decryptKey = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        if ($decryptKey) {
            $row['api_key_decrypted'] = $this->decrypt($row['api_key']);
        }
        return $row;
    }

    public function findFirstAtivo($empresaId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1";
        $params = [];
        if ($empresaId !== null) {
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresaId;
        }
        $sql .= " ORDER BY (empresa_id IS NULL) ASC, id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['api_key_decrypted'] = $this->decrypt($row['api_key']);
        }
        return $row ?: null;
    }

    public function create(array $data)
    {
        $provider = $data['provider'] ?? 'evolution';
        // QuePasa: instance_name é opcional (WID do bot quando informado).
        // Evolution: obrigatório.
        $instance = $data['instance_name'] ?? null;
        if ($instance === '') {
            $instance = null;
        }

        $sql = "INSERT INTO {$this->table}
                (empresa_id, nome, provider, base_url, instance_name, api_key, webhook_url, numero_remetente, ativo)
                VALUES
                (:empresa_id, :nome, :provider, :base_url, :instance_name, :api_key, :webhook_url, :numero_remetente, :ativo)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'empresa_id' => $data['empresa_id'] ?? null,
            'nome' => $data['nome'],
            'provider' => $provider,
            'base_url' => rtrim($data['base_url'], '/'),
            'instance_name' => $instance,
            'api_key' => $this->encrypt($data['api_key']),
            'webhook_url' => $data['webhook_url'] ?? null,
            'numero_remetente' => $data['numero_remetente'] ?? null,
            'ativo' => !empty($data['ativo']) ? 1 : 0
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, array $data)
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['empresa_id','nome','provider','base_url','instance_name','webhook_url','numero_remetente','ativo'];
        foreach ($allowed as $f) {
            if (\array_key_exists($f, $data)) {
                $value = $data[$f];
                if ($f === 'base_url' && \is_string($value)) {
                    $value = rtrim($value, '/');
                }
                if ($f === 'ativo') {
                    $value = !empty($value) ? 1 : 0;
                }
                // instance_name vazio vira NULL (válido p/ QuePasa, fallback genérico)
                if ($f === 'instance_name' && ($value === '' || $value === false)) {
                    $value = null;
                }
                $fields[] = "{$f} = :{$f}";
                $params[$f] = $value;
            }
        }
        if (!empty($data['api_key'])) {
            $fields[] = "api_key = :api_key";
            $params['api_key'] = $this->encrypt($data['api_key']);
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

    public function atualizarStatus($id, $status, $resposta = null)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
               SET status_conexao = :status,
                   ultima_verificacao = NOW(),
                   ultima_resposta = :resposta
             WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'resposta' => $resposta,
            'id' => $id
        ]);
    }

    private function getKey()
    {
        return getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? null) ?: 'financeiro-key-2024';
    }

    public function encrypt($value)
    {
        if ($value === null || $value === '') return $value;
        $key = $this->getKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public function decrypt($value)
    {
        if ($value === null || $value === '') return '';
        $key = $this->getKey();
        $decoded = base64_decode($value);
        if (strpos($decoded, '::') === false) return '';
        list($encrypted, $iv) = explode('::', $decoded, 2);
        $result = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
        return $result === false ? '' : $result;
    }
}
