<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoWebmaniBR extends Model
{
    protected $table = 'integracoes_webmanibr';
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca configuração por integração
     */
    public function findByIntegracao($integracaoId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integracao_id = :integracao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integracao_id' => $integracaoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria uma nova configuração WebmaniaBR
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    integracao_id, consumer_key, consumer_secret, 
                    access_token, access_token_secret, ambiente,
                    serie_nfe, numero_nfe_inicial, emitir_automatico,
                    enviar_email_cliente, natureza_operacao, tipo_documento,
                    finalidade_emissao
                ) VALUES (
                    :integracao_id, :consumer_key, :consumer_secret,
                    :access_token, :access_token_secret, :ambiente,
                    :serie_nfe, :numero_nfe_inicial, :emitir_automatico,
                    :enviar_email_cliente, :natureza_operacao, :tipo_documento,
                    :finalidade_emissao
                )";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Atualiza configuração WebmaniaBR
     */
    public function update($integracaoId, $data)
    {
        $sql = "UPDATE {$this->table} SET
                    consumer_key = :consumer_key,
                    consumer_secret = :consumer_secret,
                    access_token = :access_token,
                    access_token_secret = :access_token_secret,
                    ambiente = :ambiente,
                    serie_nfe = :serie_nfe,
                    numero_nfe_inicial = :numero_nfe_inicial,
                    emitir_automatico = :emitir_automatico,
                    enviar_email_cliente = :enviar_email_cliente,
                    natureza_operacao = :natureza_operacao,
                    tipo_documento = :tipo_documento,
                    finalidade_emissao = :finalidade_emissao,
                    updated_at = NOW()
                WHERE integracao_id = :integracao_id";
        
        $data['integracao_id'] = $integracaoId;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
