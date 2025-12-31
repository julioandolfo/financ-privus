<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class IntegracaoBancoDados extends Model
{
    protected $table = 'integracoes_bancos_dados';
    protected $db;
    
    const TIPO_MYSQL = 'mysql';
    const TIPO_POSTGRESQL = 'postgresql';
    const TIPO_SQLSERVER = 'sqlserver';
    const TIPO_ORACLE = 'oracle';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca configuração por integração_id
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
     * Cria configuração de banco de dados
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (integracao_id, nome_conexao, tipo_banco, host, porta, `database`, usuario, senha,
                 tabela_origem, colunas_selecionadas, condicoes, mapeamento_colunas, 
                 tabela_destino, ativo) 
                VALUES 
                (:integracao_id, :nome_conexao, :tipo_banco, :host, :porta, :database, :usuario, :senha,
                 :tabela_origem, :colunas_selecionadas, :condicoes, :mapeamento_colunas,
                 :tabela_destino, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'integracao_id' => $data['integracao_id'],
            'nome_conexao' => $data['nome_conexao'],
            'tipo_banco' => $data['tipo_banco'],
            'host' => $data['host'],
            'porta' => $data['porta'],
            'database' => $data['database'],
            'usuario' => $data['usuario'],
            'senha' => $this->encrypt($data['senha']),
            'tabela_origem' => $data['tabela_origem'],
            'colunas_selecionadas' => isset($data['colunas_selecionadas']) ? json_encode($data['colunas_selecionadas']) : null,
            'condicoes' => isset($data['condicoes']) ? json_encode($data['condicoes']) : null,
            'mapeamento_colunas' => isset($data['mapeamento_colunas']) ? json_encode($data['mapeamento_colunas']) : null,
            'tabela_destino' => $data['tabela_destino'],
            'ativo' => $data['ativo'] ?? 1
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Atualiza configuração de banco de dados
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                nome_conexao = :nome_conexao,
                tipo_banco = :tipo_banco,
                host = :host,
                porta = :porta,
                `database` = :database,
                usuario = :usuario,
                tabela_origem = :tabela_origem,
                colunas_selecionadas = :colunas_selecionadas,
                condicoes = :condicoes,
                mapeamento_colunas = :mapeamento_colunas,
                tabela_destino = :tabela_destino,
                ativo = :ativo";
        
        $params = [
            'id' => $id,
            'nome_conexao' => $data['nome_conexao'],
            'tipo_banco' => $data['tipo_banco'],
            'host' => $data['host'],
            'porta' => $data['porta'],
            'database' => $data['database'],
            'usuario' => $data['usuario'],
            'tabela_origem' => $data['tabela_origem'],
            'colunas_selecionadas' => isset($data['colunas_selecionadas']) ? json_encode($data['colunas_selecionadas']) : null,
            'condicoes' => isset($data['condicoes']) ? json_encode($data['condicoes']) : null,
            'mapeamento_colunas' => isset($data['mapeamento_colunas']) ? json_encode($data['mapeamento_colunas']) : null,
            'tabela_destino' => $data['tabela_destino'],
            'ativo' => $data['ativo'] ?? 1
        ];
        
        // Só atualiza senha se foi fornecida
        if (!empty($data['senha'])) {
            $sql .= ", senha = :senha";
            $params['senha'] = $this->encrypt($data['senha']);
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Exclui configuração
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Testa conexão com o banco de dados externo
     */
    public function testarConexao($tipoBanco, $host, $porta, $database, $usuario, $senha)
    {
        try {
            $dsn = $this->buildDSN($tipoBanco, $host, $porta, $database);
            
            $pdo = new PDO($dsn, $usuario, $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            return [
                'sucesso' => true,
                'mensagem' => 'Conexão estabelecida com sucesso!'
            ];
        } catch (\PDOException $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao conectar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lista tabelas do banco de dados externo
     */
    public function listarTabelas($config)
    {
        try {
            $dsn = $this->buildDSN($config['tipo_banco'], $config['host'], $config['porta'], $config['database']);
            $senha = $this->decrypt($config['senha']);
            
            $pdo = new PDO($dsn, $config['usuario'], $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $query = $this->getShowTablesQuery($config['tipo_banco']);
            $stmt = $pdo->query($query);
            $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'sucesso' => true,
                'tabelas' => $tabelas
            ];
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lista colunas de uma tabela
     */
    public function listarColunas($config, $tabela)
    {
        try {
            $dsn = $this->buildDSN($config['tipo_banco'], $config['host'], $config['porta'], $config['database']);
            $senha = $this->decrypt($config['senha']);
            
            $pdo = new PDO($dsn, $config['usuario'], $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $query = $this->getShowColumnsQuery($config['tipo_banco'], $tabela);
            $stmt = $pdo->query($query);
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'sucesso' => true,
                'colunas' => $colunas
            ];
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Constrói DSN baseado no tipo de banco
     */
    private function buildDSN($tipo, $host, $porta, $database)
    {
        switch ($tipo) {
            case self::TIPO_MYSQL:
                return "mysql:host={$host};port={$porta};dbname={$database};charset=utf8mb4";
            case self::TIPO_POSTGRESQL:
                return "pgsql:host={$host};port={$porta};dbname={$database}";
            case self::TIPO_SQLSERVER:
                return "sqlsrv:Server={$host},{$porta};Database={$database}";
            case self::TIPO_ORACLE:
                return "oci:dbname=//{$host}:{$porta}/{$database}";
            default:
                throw new \Exception("Tipo de banco não suportado: {$tipo}");
        }
    }
    
    /**
     * Retorna query para listar tabelas baseado no tipo de banco
     */
    private function getShowTablesQuery($tipo)
    {
        switch ($tipo) {
            case self::TIPO_MYSQL:
                return "SHOW TABLES";
            case self::TIPO_POSTGRESQL:
                return "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";
            case self::TIPO_SQLSERVER:
                return "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
            case self::TIPO_ORACLE:
                return "SELECT table_name FROM user_tables";
            default:
                return "SHOW TABLES";
        }
    }
    
    /**
     * Retorna query para listar colunas baseado no tipo de banco
     */
    private function getShowColumnsQuery($tipo, $tabela)
    {
        switch ($tipo) {
            case self::TIPO_MYSQL:
                return "SHOW COLUMNS FROM {$tabela}";
            case self::TIPO_POSTGRESQL:
                return "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '{$tabela}'";
            case self::TIPO_SQLSERVER:
                return "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$tabela}'";
            case self::TIPO_ORACLE:
                return "SELECT column_name, data_type FROM user_tab_columns WHERE table_name = '{$tabela}'";
            default:
                return "SHOW COLUMNS FROM {$tabela}";
        }
    }
    
    /**
     * Criptografa senha
     */
    private function encrypt($value)
    {
        $key = getenv('APP_KEY') ?: 'financeiro-key-2024';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Descriptografa senha
     */
    private function decrypt($value)
    {
        $key = getenv('APP_KEY') ?: 'financeiro-key-2024';
        list($encrypted, $iv) = explode('::', base64_decode($value), 2);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}
