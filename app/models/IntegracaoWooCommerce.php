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
            // Se receber array ao invés de parâmetros separados
            if (is_array($urlSite)) {
                $config = $urlSite;
                $urlSite = $config['url_site'] ?? '';
                $consumerKey = $config['consumer_key'] ?? '';
                $consumerSecret = $config['consumer_secret'] ?? '';
            }
            
            // Valida URL
            $urlSite = trim($urlSite);
            if (empty($urlSite)) {
                return [
                    'sucesso' => false,
                    'codigo' => 0,
                    'mensagem' => '❌ URL do site não informada'
                ];
            }
            
            // Valida credenciais
            if (empty($consumerKey) || empty($consumerSecret)) {
                return [
                    'sucesso' => false,
                    'codigo' => 0,
                    'mensagem' => '❌ Consumer Key ou Consumer Secret não informados'
                ];
            }
            
            // Tenta endpoint mais simples primeiro
            $url = rtrim($urlSite, '/') . '/wp-json/wc/v3/products';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Sistema Financeiro/1.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            // Verifica erro de cURL
            if ($curlErrno !== 0) {
                $mensagensErro = [
                    6 => 'Não foi possível resolver o host. Verifique a URL do site.',
                    7 => 'Falha ao conectar. Servidor não responde ou URL incorreta.',
                    28 => 'Timeout na conexão. Servidor demorou muito para responder.',
                    35 => 'Erro SSL/TLS. Certificado pode estar inválido.',
                    51 => 'Certificado SSL inválido ou não confiável.',
                    52 => 'Servidor não retornou nada.',
                    60 => 'Erro na verificação do certificado SSL.'
                ];
                
                $mensagemDetalhada = $mensagensErro[$curlErrno] ?? $curlError;
                
                return [
                    'sucesso' => false,
                    'codigo' => $curlErrno,
                    'mensagem' => "❌ Erro de conexão: {$mensagemDetalhada}",
                    'detalhes' => [
                        'curl_error' => $curlError,
                        'curl_errno' => $curlErrno,
                        'url_testada' => $url
                    ]
                ];
            }
            
            // Verifica código HTTP
            if ($httpCode === 0) {
                return [
                    'sucesso' => false,
                    'codigo' => 0,
                    'mensagem' => '❌ Não foi possível conectar ao servidor. Verifique se a URL está correta e se o site está online.',
                    'detalhes' => [
                        'url_testada' => $url,
                        'sugestao' => 'Teste acessar a URL no navegador: ' . $url
                    ]
                ];
            }
            
            if ($httpCode === 401) {
                return [
                    'sucesso' => false,
                    'codigo' => 401,
                    'mensagem' => '❌ Credenciais inválidas. Verifique Consumer Key e Consumer Secret.',
                    'detalhes' => [
                        'consumer_key_inicio' => substr($consumerKey, 0, 10) . '...',
                        'sugestao' => 'Gere novas chaves em: WooCommerce > Configurações > Avançado > API REST'
                    ]
                ];
            }
            
            if ($httpCode === 404) {
                return [
                    'sucesso' => false,
                    'codigo' => 404,
                    'mensagem' => '❌ Endpoint WooCommerce não encontrado. Verifique se o WooCommerce está instalado e ativo.',
                    'detalhes' => [
                        'url_testada' => $url,
                        'sugestao' => 'Acesse: ' . rtrim($urlSite, '/') . '/wp-json/wc/v3'
                    ]
                ];
            }
            
            if ($httpCode === 200) {
                // Tenta decodificar resposta
                $data = json_decode($response, true);
                
                return [
                    'sucesso' => true,
                    'codigo' => 200,
                    'mensagem' => '✅ Conexão estabelecida com sucesso!',
                    'detalhes' => [
                        'woocommerce_versao' => $data['version'] ?? 'Detectado',
                        'produtos_encontrados' => is_array($data) ? count($data) : 'API OK'
                    ]
                ];
            }
            
            // Outros códigos HTTP
            return [
                'sucesso' => false,
                'codigo' => $httpCode,
                'mensagem' => "❌ Erro HTTP {$httpCode}: " . $this->getHttpStatusMessage($httpCode),
                'detalhes' => [
                    'resposta' => substr($response, 0, 500)
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'codigo' => 0,
                'mensagem' => '❌ Erro inesperado: ' . $e->getMessage(),
                'detalhes' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
    
    /**
     * Retorna mensagem amigável para códigos HTTP
     */
    private function getHttpStatusMessage($code)
    {
        $messages = [
            400 => 'Requisição inválida',
            403 => 'Acesso negado',
            500 => 'Erro interno do servidor',
            502 => 'Bad Gateway - Servidor indisponível',
            503 => 'Serviço temporariamente indisponível',
            504 => 'Gateway Timeout - Servidor demorou muito'
        ];
        
        return $messages[$code] ?? 'Erro desconhecido';
    }
}
