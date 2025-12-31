<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoApi extends Model
{
    protected $table = 'integracoes_api';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca API por ID da integração
     */
    public function findByIntegracaoId($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria API
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, nome_api, base_url, tipo_api, autenticacao, 
                 auth_usuario, auth_senha, auth_token, api_key_header, api_key_value,
                 oauth2_client_id, oauth2_client_secret, oauth2_token_url, oauth2_scope,
                 headers_padrao, endpoints, timeout, formato_resposta, ativo) 
                VALUES 
                (:integracao_id, :nome_api, :base_url, :tipo_api, :autenticacao, 
                 :auth_usuario, :auth_senha, :auth_token, :api_key_header, :api_key_value,
                 :oauth2_client_id, :oauth2_client_secret, :oauth2_token_url, :oauth2_scope,
                 :headers_padrao, :endpoints, :timeout, :formato_resposta, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'nome_api' => $data['nome_api'],
            'base_url' => $data['base_url'],
            'tipo_api' => $data['tipo_api'] ?? 'rest',
            'autenticacao' => $data['autenticacao'] ?? 'none',
            'auth_usuario' => $data['auth_usuario'] ?? null,
            'auth_senha' => $data['auth_senha'] ?? null,
            'auth_token' => $data['auth_token'] ?? null,
            'api_key_header' => $data['api_key_header'] ?? null,
            'api_key_value' => $data['api_key_value'] ?? null,
            'oauth2_client_id' => $data['oauth2_client_id'] ?? null,
            'oauth2_client_secret' => $data['oauth2_client_secret'] ?? null,
            'oauth2_token_url' => $data['oauth2_token_url'] ?? null,
            'oauth2_scope' => $data['oauth2_scope'] ?? null,
            'headers_padrao' => isset($data['headers_padrao']) ? json_encode($data['headers_padrao']) : null,
            'endpoints' => isset($data['endpoints']) ? json_encode($data['endpoints']) : null,
            'timeout' => $data['timeout'] ?? 30,
            'formato_resposta' => $data['formato_resposta'] ?? 'json',
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza API
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome_api = :nome_api,
                base_url = :base_url,
                tipo_api = :tipo_api,
                autenticacao = :autenticacao,
                auth_usuario = :auth_usuario,
                auth_senha = :auth_senha,
                auth_token = :auth_token,
                api_key_header = :api_key_header,
                api_key_value = :api_key_value,
                oauth2_client_id = :oauth2_client_id,
                oauth2_client_secret = :oauth2_client_secret,
                oauth2_token_url = :oauth2_token_url,
                oauth2_scope = :oauth2_scope,
                headers_padrao = :headers_padrao,
                endpoints = :endpoints,
                timeout = :timeout,
                formato_resposta = :formato_resposta,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome_api' => $data['nome_api'],
            'base_url' => $data['base_url'],
            'tipo_api' => $data['tipo_api'] ?? 'rest',
            'autenticacao' => $data['autenticacao'] ?? 'none',
            'auth_usuario' => $data['auth_usuario'] ?? null,
            'auth_senha' => $data['auth_senha'] ?? null,
            'auth_token' => $data['auth_token'] ?? null,
            'api_key_header' => $data['api_key_header'] ?? null,
            'api_key_value' => $data['api_key_value'] ?? null,
            'oauth2_client_id' => $data['oauth2_client_id'] ?? null,
            'oauth2_client_secret' => $data['oauth2_client_secret'] ?? null,
            'oauth2_token_url' => $data['oauth2_token_url'] ?? null,
            'oauth2_scope' => $data['oauth2_scope'] ?? null,
            'headers_padrao' => isset($data['headers_padrao']) ? json_encode($data['headers_padrao']) : null,
            'endpoints' => isset($data['endpoints']) ? json_encode($data['endpoints']) : null,
            'timeout' => $data['timeout'] ?? 30,
            'formato_resposta' => $data['formato_resposta'] ?? 'json',
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Deleta API
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Testa conexão com API
     */
    public function testarConexao($baseUrl, $autenticacao, $authData)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, rtrim($baseUrl, '/'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $headers = ['Content-Type: application/json'];
            
            // Autenticação
            if ($autenticacao === 'basic') {
                curl_setopt($ch, CURLOPT_USERPWD, $authData['usuario'] . ':' . $authData['senha']);
            } elseif ($autenticacao === 'bearer') {
                $headers[] = 'Authorization: Bearer ' . $authData['token'];
            } elseif ($autenticacao === 'api_key') {
                $headers[] = $authData['header'] . ': ' . $authData['value'];
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 500) {
                return ['sucesso' => true, 'mensagem' => "Conexão estabelecida! Status: {$httpCode}"];
            } else {
                return ['sucesso' => false, 'mensagem' => "Erro na conexão. Status: {$httpCode}"];
            }
        } catch (\Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()];
        }
    }
}
