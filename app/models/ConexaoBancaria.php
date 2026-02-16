<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class ConexaoBancaria extends Model
{
    protected $table = 'conexoes_bancarias';
    protected $db;
    
    const BANCOS_DISPONIVEIS = [
        'sicoob' => ['nome' => 'Sicoob', 'logo' => '🏦', 'cor' => 'green'],
        'sicredi' => ['nome' => 'Sicredi', 'logo' => '🏦', 'cor' => 'green'],
        'itau' => ['nome' => 'Itaú', 'logo' => '🏦', 'cor' => 'orange'],
        'bradesco' => ['nome' => 'Bradesco', 'logo' => '🏦', 'cor' => 'red'],
        'mercadopago' => ['nome' => 'Mercado Pago', 'logo' => '💳', 'cor' => 'blue']
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Retorna todas as conexões de uma empresa
     */
    public function findByEmpresa($empresaId, $apenasAtivas = true)
    {
        $sql = "SELECT cb.*, u.nome as usuario_nome 
                FROM {$this->table} cb
                LEFT JOIN usuarios u ON cb.usuario_id = u.id
                WHERE cb.empresa_id = :empresa_id";
        
        if ($apenasAtivas) {
            $sql .= " AND cb.ativo = 1";
        }
        
        $sql .= " ORDER BY cb.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Retorna uma conexão por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova conexão bancária
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (empresa_id, usuario_id, banco, tipo_integracao, tipo, identificacao, 
                 access_token, refresh_token, token_expira_em, consent_id,
                 auto_sync, frequencia_sync, tipo_sync, categoria_padrao_id, 
                 centro_custo_padrao_id, aprovacao_automatica,
                 ambiente, client_id, client_secret, cert_pem, key_pem, cert_password,
                 cert_pfx, cooperativa,
                 ativo, ultima_sincronizacao,
                 conta_bancaria_id, saldo_banco, saldo_atualizado_em, status_conexao, banco_conta_id) 
                VALUES 
                (:empresa_id, :usuario_id, :banco, :tipo_integracao, :tipo, :identificacao,
                 :access_token, :refresh_token, :token_expira_em, :consent_id,
                 :auto_sync, :frequencia_sync, :tipo_sync, :categoria_padrao_id,
                 :centro_custo_padrao_id, :aprovacao_automatica,
                 :ambiente, :client_id, :client_secret, :cert_pem, :key_pem, :cert_password,
                 :cert_pfx, :cooperativa,
                 :ativo, :ultima_sincronizacao,
                 :conta_bancaria_id, :saldo_banco, :saldo_atualizado_em, :status_conexao, :banco_conta_id)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'usuario_id' => $data['usuario_id'],
            'banco' => $data['banco'],
            'tipo_integracao' => $data['tipo_integracao'] ?? 'api_direta',
            'tipo' => $data['tipo'] ?? 'conta_corrente',
            'identificacao' => $data['identificacao'] ?? null,
            'access_token' => $this->encryptToken($data['access_token'] ?? null),
            'refresh_token' => $this->encryptToken($data['refresh_token'] ?? null),
            'token_expira_em' => $data['token_expira_em'] ?? null,
            'consent_id' => $data['consent_id'] ?? null,
            'auto_sync' => $data['auto_sync'] ?? 1,
            'frequencia_sync' => $data['frequencia_sync'] ?? 'diaria',
            'tipo_sync' => $data['tipo_sync'] ?? 'ambos',
            'categoria_padrao_id' => $data['categoria_padrao_id'] ?? null,
            'centro_custo_padrao_id' => $data['centro_custo_padrao_id'] ?? null,
            'aprovacao_automatica' => $data['aprovacao_automatica'] ?? 0,
            'ambiente' => $data['ambiente'] ?? 'sandbox',
            'client_id' => $data['client_id'] ?? null,
            'client_secret' => $data['client_secret'] ?? null,
            'cert_pem' => $data['cert_pem'] ?? null,
            'key_pem' => $data['key_pem'] ?? null,
            'cert_password' => $data['cert_password'] ?? null,
            'cert_pfx' => $data['cert_pfx'] ?? null,
            'cooperativa' => $data['cooperativa'] ?? null,
            'ativo' => $data['ativo'] ?? 1,
            'ultima_sincronizacao' => $data['ultima_sincronizacao'] ?? null,
            'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
            'saldo_banco' => $data['saldo_banco'] ?? null,
            'saldo_atualizado_em' => $data['saldo_atualizado_em'] ?? null,
            'status_conexao' => $data['status_conexao'] ?? 'ativa',
            'banco_conta_id' => $data['banco_conta_id'] ?? null
        ]) ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza uma conexão
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        $allowed = ['identificacao', 'auto_sync', 'frequencia_sync', 'tipo_sync',
                   'categoria_padrao_id', 'centro_custo_padrao_id', 'aprovacao_automatica',
                   'access_token', 'refresh_token', 'token_expira_em', 'ultima_sincronizacao',
                   'ambiente', 'client_id', 'client_secret', 'cert_pem', 'key_pem', 'cert_password',
                   'cert_pfx', 'cooperativa',
                   'ativo', 'tipo_integracao',
                   'conta_bancaria_id', 'saldo_banco', 'saldo_atualizado_em', 'status_conexao', 'ultimo_erro', 'banco_conta_id'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['access_token', 'refresh_token'])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $this->encryptToken($data[$field]);
                } else {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Desativa uma conexão (soft delete)
     */
    public function desconectar($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verifica se o token está próximo de expirar (menos de 7 dias)
     */
    public function tokenProximoExpiracao($id)
    {
        $conexao = $this->findById($id);
        if (!$conexao || !$conexao['token_expira_em']) {
            return false;
        }
        
        $dataExpiracao = new \DateTime($conexao['token_expira_em']);
        $hoje = new \DateTime();
        $diff = $hoje->diff($dataExpiracao);
        
        return $diff->days <= 7 && $diff->invert == 0;
    }
    
    /**
     * Estatísticas de uma empresa
     */
    public function getEstatisticas($empresaId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_conexoes,
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as conexoes_ativas,
                    SUM(CASE WHEN auto_sync = 1 THEN 1 ELSE 0 END) as com_auto_sync,
                    MAX(ultima_sincronizacao) as ultima_sync_geral
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criptografa token (base64 simples - em produção usar algo mais robusto)
     */
    private function encryptToken($token)
    {
        if (!$token) return null;
        return base64_encode($token);
    }
    
    /**
     * Descriptografa token
     */
    public function decryptToken($encryptedToken)
    {
        if (!$encryptedToken) return null;
        return base64_decode($encryptedToken);
    }
    
    /**
     * Retorna informações do banco
     */
    public static function getBancoInfo($banco)
    {
        return self::BANCOS_DISPONIVEIS[$banco] ?? ['nome' => ucfirst($banco), 'logo' => '🏦', 'cor' => 'gray'];
    }

    /**
     * Busca conexões ativas por banco e empresa.
     */
    public function findByBancoEmpresa($banco, $empresaId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE banco = :banco AND empresa_id = :empresa_id AND ativo = 1
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['banco' => $banco, 'empresa_id' => $empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna todas as conexões ativas que precisam ser sincronizadas.
     */
    public function findAtivasParaSync()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ativo = 1 AND auto_sync = 1 AND status_conexao != 'desconectada'
                ORDER BY ultima_sincronizacao ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Atualiza o saldo real reportado pelo banco e registra no histórico.
     */
    public function atualizarSaldo($id, $saldo, $extras = [])
    {
        $sql = "UPDATE {$this->table} 
                SET saldo_banco = :saldo, 
                    saldo_atualizado_em = NOW(),
                    status_conexao = 'ativa',
                    ultimo_erro = NULL
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(['id' => $id, 'saldo' => $saldo]);
        
        // Salvar campos extras (limite, agendamentos, etc)
        if (!empty($extras)) {
            $camposPermitidos = ['saldo_limite', 'saldo_contabil', 'tx_futuras', 'soma_futuros_debito', 'soma_futuros_credito'];
            try {
                $updates = [];
                $params = ['id' => $id];
                foreach ($camposPermitidos as $campo) {
                    if (isset($extras[$campo])) {
                        $updates[] = "$campo = :$campo";
                        $params[$campo] = $extras[$campo];
                    }
                }
                if (!empty($updates)) {
                    $sqlExtra = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = :id";
                    $stmtExtra = $this->db->prepare($sqlExtra);
                    $stmtExtra->execute($params);
                }
            } catch (\Exception $e) {
                // Campos podem não existir ainda na tabela
            }
        }
        
        // Registrar snapshot no histórico de saldos
        try {
            $conexao = $this->findById($id);
            $historico = new SaldoHistorico();
            $historico->registrar($id, [
                'empresa_id' => $conexao['empresa_id'] ?? null,
                'conta_bancaria_id' => $conexao['conta_bancaria_id'] ?? null,
                'saldo_contabil' => $saldo,
                'saldo_limite' => $extras['saldo_limite'] ?? 0,
                'saldo_bloqueado' => $extras['saldo_bloqueado'] ?? 0,
                'tx_futuras' => $extras['tx_futuras'] ?? 0,
                'soma_futuros_debito' => $extras['soma_futuros_debito'] ?? 0,
                'soma_futuros_credito' => $extras['soma_futuros_credito'] ?? 0,
                'data_referencia' => $extras['data_referencia'] ?? null,
                'fonte' => $extras['fonte'] ?? 'api',
            ]);
        } catch (\Exception $e) {
            // Tabela pode não existir ainda, silencioso
        }
        
        return $result;
    }

    /**
     * Registra erro na conexão.
     */
    public function registrarErro($id, $mensagemErro)
    {
        $sql = "UPDATE {$this->table} 
                SET status_conexao = 'erro', 
                    ultimo_erro = :erro 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'erro' => $mensagemErro]);
    }

    /**
     * Retorna saldo total de todas as conexões ativas de uma empresa.
     */
    public function getSaldoTotalEmpresa($empresaId)
    {
        $sql = "SELECT 
                    COALESCE(SUM(saldo_banco), 0) as saldo_total,
                    COUNT(*) as total_contas,
                    MIN(saldo_atualizado_em) as saldo_mais_antigo
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id AND ativo = 1 AND saldo_banco IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza a data da última sincronização.
     */
    public function atualizarUltimaSync($id)
    {
        $sql = "UPDATE {$this->table} SET ultima_sincronizacao = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Retorna dados da conexão com tokens descriptografados (para uso nos services).
     */
    public function getConexaoComCredenciais($id)
    {
        $conexao = $this->findById($id);
        if (!$conexao) return null;

        // Descriptografar tokens
        if (!empty($conexao['access_token'])) {
            $conexao['access_token'] = $this->decryptToken($conexao['access_token']);
        }
        if (!empty($conexao['refresh_token'])) {
            $conexao['refresh_token'] = $this->decryptToken($conexao['refresh_token']);
        }

        return $conexao;
    }
}
