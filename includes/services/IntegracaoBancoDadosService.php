<?php
namespace Includes\Services;

use App\Models\IntegracaoConfig;
use App\Models\IntegracaoBancoDados;
use App\Models\IntegracaoLog;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Fornecedor;
use PDO;

class IntegracaoBancoDadosService
{
    private $integracaoModel;
    private $bancoDadosModel;
    private $logModel;
    
    public function __construct()
    {
        $this->integracaoModel = new IntegracaoConfig();
        $this->bancoDadosModel = new IntegracaoBancoDados();
        $this->logModel = new IntegracaoLog();
    }
    
    /**
     * Executa sincronização
     */
    public function sincronizar($integracaoId)
    {
        $integracao = $this->integracaoModel->findById($integracaoId);
        
        if (!$integracao || $integracao['tipo'] !== 'banco_dados') {
            return ['sucesso' => false, 'erro' => 'Integração inválida'];
        }
        
        $config = $this->bancoDadosModel->findByIntegracaoId($integracaoId);
        
        if (!$config) {
            return ['sucesso' => false, 'erro' => 'Configuração não encontrada'];
        }
        
        try {
            // Conecta ao banco externo
            $pdoExterno = $this->conectarBancoExterno($config);
            
            // Busca dados
            $dados = $this->buscarDados($pdoExterno, $config);
            
            // Importa dados
            $total = $this->importarDados($dados, $config, $integracao['empresa_id']);
            
            // Atualiza sincronização
            $proximaSinc = date('Y-m-d H:i:s', strtotime("+{$integracao['intervalo_sincronizacao']} minutes"));
            $this->integracaoModel->updateUltimaSincronizacao($integracaoId, $proximaSinc);
            
            // Log
            $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, "Sincronização concluída: {$total} registros importados");
            
            return ['sucesso' => true, 'total' => $total];
            
        } catch (\Exception $e) {
            $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 'Erro: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Conecta ao banco externo
     */
    private function conectarBancoExterno($config)
    {
        $dsn = $this->buildDSN($config);
        $senha = $this->decrypt($config['senha']);
        
        return new PDO($dsn, $config['usuario'], $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
    /**
     * Busca dados do banco externo
     */
    private function buscarDados($pdo, $config)
    {
        $sql = "SELECT * FROM {$config['tabela_origem']}";
        
        // Adiciona condições se houver
        if (!empty($config['condicoes'])) {
            $condicoes = json_decode($config['condicoes'], true);
            if (!empty($condicoes)) {
                $sql .= " WHERE " . implode(' AND ', $condicoes);
            }
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Importa dados para o sistema
     */
    private function importarDados($dados, $config, $empresaId)
    {
        $total = 0;
        $mapeamento = json_decode($config['mapeamento_colunas'], true) ?: [];
        $tabelaDestino = $config['tabela_destino'];
        
        foreach ($dados as $registro) {
            try {
                $dadosImportar = ['empresa_id' => $empresaId];
                
                // Mapeia colunas
                foreach ($registro as $coluna => $valor) {
                    $colunaDestino = $mapeamento[$coluna] ?? $coluna;
                    $dadosImportar[$colunaDestino] = $valor;
                }
                
                // Importa baseado na tabela destino
                switch ($tabelaDestino) {
                    case 'produtos':
                        $this->produtoModel->create($dadosImportar);
                        break;
                    case 'clientes':
                        $this->clienteModel->create($dadosImportar);
                        break;
                    case 'fornecedores':
                        $fornecedorModel = new Fornecedor();
                        $fornecedorModel->create($dadosImportar);
                        break;
                }
                
                $total++;
            } catch (\Exception $e) {
                // Log erro individual
                continue;
            }
        }
        
        return $total;
    }
    
    /**
     * Constrói DSN
     */
    private function buildDSN($config)
    {
        switch ($config['tipo_banco']) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['porta']};dbname={$config['database']};charset=utf8mb4";
            case 'postgresql':
                return "pgsql:host={$config['host']};port={$config['porta']};dbname={$config['database']}";
            case 'sqlserver':
                return "sqlsrv:Server={$config['host']},{$config['porta']};Database={$config['database']}";
            case 'oracle':
                return "oci:dbname=//{$config['host']}:{$config['porta']}/{$config['database']}";
            default:
                throw new \Exception("Tipo de banco não suportado");
        }
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
