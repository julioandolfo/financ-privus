<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoWebhook extends Model
{
    protected $table = 'integracoes_webhooks';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca webhook por ID da integração
     */
    public function findByIntegracaoId($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca todos os webhooks de uma integração
     */
    public function findAllByIntegracaoId($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id ORDER BY nome_webhook";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Busca webhooks ativos por evento
     */
    public function findActiveByEvento($evento)
    {
        $sql = "SELECT w.*, i.empresa_id 
                FROM {$this->table} w
                INNER JOIN integracoes_config i ON w.integracao_id = i.id
                WHERE w.ativo = 1 
                AND i.ativo = 1
                AND JSON_CONTAINS(w.eventos_disparo, :evento)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['evento' => json_encode($evento)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Cria webhook
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, nome_webhook, url_webhook, metodo, headers, 
                 autenticacao, auth_usuario, auth_senha, auth_token, 
                 api_key_header, api_key_value, eventos_disparo, payload_template, 
                 timeout, retry_attempts, retry_delay, ativo) 
                VALUES 
                (:integracao_id, :nome_webhook, :url_webhook, :metodo, :headers, 
                 :autenticacao, :auth_usuario, :auth_senha, :auth_token, 
                 :api_key_header, :api_key_value, :eventos_disparo, :payload_template, 
                 :timeout, :retry_attempts, :retry_delay, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'nome_webhook' => $data['nome_webhook'],
            'url_webhook' => $data['url_webhook'],
            'metodo' => $data['metodo'] ?? 'POST',
            'headers' => isset($data['headers']) ? json_encode($data['headers']) : null,
            'autenticacao' => $data['autenticacao'] ?? 'none',
            'auth_usuario' => $data['auth_usuario'] ?? null,
            'auth_senha' => $data['auth_senha'] ?? null,
            'auth_token' => $data['auth_token'] ?? null,
            'api_key_header' => $data['api_key_header'] ?? null,
            'api_key_value' => $data['api_key_value'] ?? null,
            'eventos_disparo' => isset($data['eventos_disparo']) ? json_encode($data['eventos_disparo']) : null,
            'payload_template' => $data['payload_template'] ?? null,
            'timeout' => $data['timeout'] ?? 30,
            'retry_attempts' => $data['retry_attempts'] ?? 3,
            'retry_delay' => $data['retry_delay'] ?? 60,
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza webhook
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome_webhook = :nome_webhook,
                url_webhook = :url_webhook,
                metodo = :metodo,
                headers = :headers,
                autenticacao = :autenticacao,
                auth_usuario = :auth_usuario,
                auth_senha = :auth_senha,
                auth_token = :auth_token,
                api_key_header = :api_key_header,
                api_key_value = :api_key_value,
                eventos_disparo = :eventos_disparo,
                payload_template = :payload_template,
                timeout = :timeout,
                retry_attempts = :retry_attempts,
                retry_delay = :retry_delay,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome_webhook' => $data['nome_webhook'],
            'url_webhook' => $data['url_webhook'],
            'metodo' => $data['metodo'] ?? 'POST',
            'headers' => isset($data['headers']) ? json_encode($data['headers']) : null,
            'autenticacao' => $data['autenticacao'] ?? 'none',
            'auth_usuario' => $data['auth_usuario'] ?? null,
            'auth_senha' => $data['auth_senha'] ?? null,
            'auth_token' => $data['auth_token'] ?? null,
            'api_key_header' => $data['api_key_header'] ?? null,
            'api_key_value' => $data['api_key_value'] ?? null,
            'eventos_disparo' => isset($data['eventos_disparo']) ? json_encode($data['eventos_disparo']) : null,
            'payload_template' => $data['payload_template'] ?? null,
            'timeout' => $data['timeout'] ?? 30,
            'retry_attempts' => $data['retry_attempts'] ?? 3,
            'retry_delay' => $data['retry_delay'] ?? 60,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Deleta webhook
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Testa webhook
     */
    public function testar($id)
    {
        $webhook = $this->findById($id);
        if (!$webhook) {
            return ['sucesso' => false, 'mensagem' => 'Webhook não encontrado'];
        }
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook['url_webhook']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $webhook['timeout']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $webhook['metodo']);
            
            // Headers
            $headers = ['Content-Type: application/json'];
            if ($webhook['headers']) {
                $customHeaders = json_decode($webhook['headers'], true);
                foreach ($customHeaders as $key => $value) {
                    $headers[] = "$key: $value";
                }
            }
            
            // Autenticação
            if ($webhook['autenticacao'] === 'basic') {
                curl_setopt($ch, CURLOPT_USERPWD, $webhook['auth_usuario'] . ':' . $webhook['auth_senha']);
            } elseif ($webhook['autenticacao'] === 'bearer') {
                $headers[] = 'Authorization: Bearer ' . $webhook['auth_token'];
            } elseif ($webhook['autenticacao'] === 'api_key') {
                $headers[] = $webhook['api_key_header'] . ': ' . $webhook['api_key_value'];
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            // Payload de teste
            $testPayload = ['test' => true, 'timestamp' => date('c')];
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return ['sucesso' => true, 'mensagem' => "Webhook testado com sucesso! Status: {$httpCode}"];
            } else {
                return ['sucesso' => false, 'mensagem' => "Erro no webhook. Status: {$httpCode}"];
            }
        } catch (\Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()];
        }
    }
}
