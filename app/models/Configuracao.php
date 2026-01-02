<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * Model para Configurações do Sistema
 */
class Configuracao extends Model
{
    protected $table = 'configuracoes';
    protected $db;
    
    // Cache de configurações
    private static $cache = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtém o valor de uma configuração
     */
    public static function get($chave, $padrao = null)
    {
        // Verificar cache
        if (isset(self::$cache[$chave])) {
            return self::$cache[$chave];
        }
        
        $instance = new self();
        $sql = "SELECT valor, tipo FROM {$instance->table} WHERE chave = :chave LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute(['chave' => $chave]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            self::$cache[$chave] = $padrao;
            return $padrao;
        }
        
        // Converter valor baseado no tipo
        $valor = self::convertValue($config['valor'], $config['tipo']);
        self::$cache[$chave] = $valor;
        
        return $valor;
    }
    
    /**
     * Define o valor de uma configuração
     */
    public static function set($chave, $valor, $tipo = null)
    {
        $instance = new self();
        
        // Verificar se já existe para preservar tipo se necessário
        $sql = "SELECT id, tipo FROM {$instance->table} WHERE chave = :chave LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute(['chave' => $chave]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se tipo não foi especificado, usar tipo existente ou detectar
        if ($tipo === null) {
            if ($exists && !empty($exists['tipo'])) {
                $tipo = $exists['tipo'];
            } else {
                $tipo = self::detectType($valor);
            }
        }
        
        // Converter valor para string baseado no tipo
        $valorString = self::valueToString($valor, $tipo);
        
        if ($exists) {
            // Update
            $sql = "UPDATE {$instance->table} SET valor = :valor, tipo = :tipo WHERE chave = :chave";
            $stmt = $instance->db->prepare($sql);
            $success = $stmt->execute([
                'valor' => $valorString,
                'tipo' => $tipo,
                'chave' => $chave
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO {$instance->table} (chave, valor, tipo) VALUES (:chave, :valor, :tipo)";
            $stmt = $instance->db->prepare($sql);
            $success = $stmt->execute([
                'chave' => $chave,
                'valor' => $valorString,
                'tipo' => $tipo
            ]);
        }
        
        // Atualizar cache
        if ($success) {
            self::$cache[$chave] = $valor;
        }
        
        return $success;
    }
    
    /**
     * Obtém todas as configurações de um grupo
     */
    public static function getGrupo($grupo)
    {
        $instance = new self();
        $sql = "SELECT chave, valor, tipo, descricao FROM {$instance->table} WHERE grupo = :grupo ORDER BY chave";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute(['grupo' => $grupo]);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultado = [];
        foreach ($configs as $config) {
            $resultado[$config['chave']] = [
                'valor' => self::convertValue($config['valor'], $config['tipo']),
                'tipo' => $config['tipo'],
                'descricao' => $config['descricao']
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Salva múltiplas configurações
     */
    public static function setMultiplas($configuracoes)
    {
        if (empty($configuracoes)) {
            return true; // Nada para salvar, retorna sucesso
        }
        
        $instance = new self();
        $instance->db->beginTransaction();
        
        try {
            foreach ($configuracoes as $chave => $valor) {
                $result = self::set($chave, $valor);
                if (!$result) {
                    throw new \Exception("Falha ao salvar configuração: {$chave}");
                }
            }
            $instance->db->commit();
            
            // Limpar cache após salvar
            self::clearCache();
            
            return true;
        } catch (\Exception $e) {
            $instance->db->rollBack();
            error_log("Erro ao salvar múltiplas configurações: " . $e->getMessage());
            error_log("Configurações tentadas: " . json_encode(array_keys($configuracoes)));
            return false;
        }
    }
    
    /**
     * Obtém todos os grupos disponíveis
     */
    public static function getGrupos()
    {
        $instance = new self();
        $sql = "SELECT DISTINCT grupo FROM {$instance->table} WHERE grupo IS NOT NULL ORDER BY grupo";
        $stmt = $instance->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Limpa o cache
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
    
    /**
     * Converte valor baseado no tipo
     */
    private static function convertValue($valor, $tipo)
    {
        switch ($tipo) {
            case 'boolean':
                return in_array(strtolower($valor), ['true', '1', 'yes', 'sim']);
            case 'number':
                return is_numeric($valor) ? (float)$valor : 0;
            case 'json':
                return json_decode($valor, true) ?: [];
            default:
                return $valor;
        }
    }
    
    /**
     * Converte valor para string
     */
    private static function valueToString($valor, $tipo)
    {
        switch ($tipo) {
            case 'boolean':
                return $valor ? 'true' : 'false';
            case 'json':
                return json_encode($valor);
            default:
                return (string)$valor;
        }
    }
    
    /**
     * Detecta o tipo do valor
     */
    private static function detectType($valor)
    {
        if (is_bool($valor)) {
            return 'boolean';
        }
        if (is_numeric($valor)) {
            return 'number';
        }
        if (is_array($valor)) {
            return 'json';
        }
        return 'string';
    }
}
