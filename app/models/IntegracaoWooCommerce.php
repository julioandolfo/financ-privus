<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoWooCommerce extends Model
{
    protected $table = 'integracoes_woocommerce';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca configuração WooCommerce por integração_id
     */
    public function findByIntegracaoId($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Busca por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Cria configuração WooCommerce
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, url_site, consumer_key, consumer_secret, webhook_secret, 
                 eventos_webhook, sincronizar_produtos, sincronizar_pedidos, 
                 empresa_vinculada_id, ativo) 
                VALUES 
                (:integracao_id, :url_site, :consumer_key, :consumer_secret, :webhook_secret,
                 :eventos_webhook, :sincronizar_produtos, :sincronizar_pedidos,
                 :empresa_vinculada_id, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'url_site' => $data['url_site'],
            'consumer_key' => $data['consumer_key'],
            'consumer_secret' => $data['consumer_secret'],
            'webhook_secret' => $data['webhook_secret'] ?? null,
            'eventos_webhook' => isset($data['eventos_webhook']) ? json_encode($data['eventos_webhook']) : null,
            'sincronizar_produtos' => $data['sincronizar_produtos'] ?? 1,
            'sincronizar_pedidos' => $data['sincronizar_pedidos'] ?? 1,
            'empresa_vinculada_id' => $data['empresa_vinculada_id'],
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza configuração WooCommerce
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                url_site = :url_site,
                consumer_key = :consumer_key,
                consumer_secret = :consumer_secret,
                webhook_secret = :webhook_secret,
                eventos_webhook = :eventos_webhook,
                sincronizar_produtos = :sincronizar_produtos,
                sincronizar_pedidos = :sincronizar_pedidos,
                empresa_vinculada_id = :empresa_vinculada_id,
                ativo = :ativo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'url_site' => $data['url_site'],
            'consumer_key' => $data['consumer_key'],
            'consumer_secret' => $data['consumer_secret'],
            'webhook_secret' => $data['webhook_secret'] ?? null,
            'eventos_webhook' => isset($data['eventos_webhook']) ? json_encode($data['eventos_webhook']) : null,
            'sincronizar_produtos' => $data['sincronizar_produtos'] ?? 1,
            'sincronizar_pedidos' => $data['sincronizar_pedidos'] ?? 1,
            'empresa_vinculada_id' => $data['empresa_vinculada_id'],
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Exclui configuração WooCommerce
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Testa conexão com a API do WooCommerce
     */
    public function testarConexao($urlSite, $consumerKey, $consumerSecret)
    {
        try {
            $url = rtrim($urlSite, '/') . '/wp-json/wc/v3/system_status';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return [
                'sucesso' => $httpCode === 200,
                'codigo' => $httpCode,
                'mensagem' => $httpCode === 200 ? 'Conexão estabelecida com sucesso!' : 'Falha na conexão. Código: ' . $httpCode
            ];
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
}
