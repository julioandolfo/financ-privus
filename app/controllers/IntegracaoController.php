<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoBancoDados;
use App\Models\IntegracaoLog;
use App\Models\Empresa;
use Includes\Services\WooCommerceService;
use Includes\Services\IntegracaoBancoDadosService;

class IntegracaoController extends Controller
{
    private $integracaoModel;
    private $woocommerceModel;
    private $bancoDadosModel;
    private $logModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->integracaoModel = new IntegracaoConfig();
        $this->woocommerceModel = new IntegracaoWooCommerce();
        $this->bancoDadosModel = new IntegracaoBancoDados();
        $this->logModel = new IntegracaoLog();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Dashboard de integrações
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        // Busca integrações
        $integracoes = $this->integracaoModel->findAll($empresaId);
        
        // Estatísticas
        $estatisticas = $this->integracaoModel->getEstatisticas($empresaId);
        $estatisticasLogs = $this->logModel->getEstatisticas(null, '7 days');
        
        // Últimos logs
        $ultimosLogs = $this->logModel->getUltimosLogs(20);
        
        return $this->render('integracoes/index', [
            'integracoes' => $integracoes,
            'estatisticas' => $estatisticas,
            'estatisticasLogs' => $estatisticasLogs,
            'ultimosLogs' => $ultimosLogs
        ]);
    }
    
    /**
     * Página de criação - seleção de tipo
     */
    public function create(Request $request, Response $response)
    {
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        
        return $this->render('integracoes/create', [
            'empresas' => $empresas
        ]);
    }
    
    /**
     * Formulário específico por tipo
     */
    public function createTipo(Request $request, Response $response, $tipo)
    {
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        switch ($tipo) {
            case 'woocommerce':
                return $this->render('integracoes/woocommerce/create', [
                    'empresas' => $empresas,
                    'empresaId' => $empresaId
                ]);
                
            case 'banco-dados':
                return $this->render('integracoes/banco_dados/create', [
                    'empresas' => $empresas,
                    'empresaId' => $empresaId
                ]);
                
            default:
                $_SESSION['error'] = 'Tipo de integração inválido.';
                return $response->redirect('/integracoes');
        }
    }
    
    /**
     * Salva integração WooCommerce
     */
    public function storeWooCommerce(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Validação
        $errors = $this->validateWooCommerce($data);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/woocommerce');
        }
        
        try {
            // Cria configuração principal
            $integracaoId = $this->integracaoModel->create([
                'empresa_id' => $data['empresa_id'],
                'tipo' => IntegracaoConfig::TIPO_WOOCOMMERCE,
                'nome' => $data['nome'],
                'ativo' => $data['ativo'] ?? 1,
                'configuracoes' => [
                    'url_site' => $data['url_site']
                ],
                'intervalo_sincronizacao' => $data['intervalo_sincronizacao'] ?? 60
            ]);
            
            if ($integracaoId) {
                // Cria configuração WooCommerce
                $eventos = [];
                if (isset($data['evento_criacao'])) $eventos[] = 'criacao';
                if (isset($data['evento_atualizacao'])) $eventos[] = 'atualizacao';
                if (isset($data['evento_exclusao'])) $eventos[] = 'exclusao';
                
                $this->woocommerceModel->create([
                    'integracao_id' => $integracaoId,
                    'url_site' => $data['url_site'],
                    'consumer_key' => $data['consumer_key'],
                    'consumer_secret' => $data['consumer_secret'],
                    'webhook_secret' => $data['webhook_secret'] ?? null,
                    'eventos_webhook' => $eventos,
                    'sincronizar_produtos' => $data['sincronizar_produtos'] ?? 1,
                    'sincronizar_pedidos' => $data['sincronizar_pedidos'] ?? 1,
                    'empresa_vinculada_id' => $data['empresa_id'],
                    'ativo' => $data['ativo'] ?? 1
                ]);
                
                // Log de sucesso
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, 'Integração WooCommerce criada com sucesso');
                
                $_SESSION['success'] = 'Integração WooCommerce criada com sucesso!';
                return $response->redirect('/integracoes');
            }
            
            $_SESSION['error'] = 'Erro ao criar integração.';
            return $response->redirect('/integracoes/create/woocommerce');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/woocommerce');
        }
    }
    
    /**
     * Salva integração Banco de Dados
     */
    public function storeBancoDados(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Validação
        $errors = $this->validateBancoDados($data);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/banco-dados');
        }
        
        try {
            // Cria configuração principal
            $integracaoId = $this->integracaoModel->create([
                'empresa_id' => $data['empresa_id'],
                'tipo' => IntegracaoConfig::TIPO_BANCO_DADOS,
                'nome' => $data['nome'],
                'ativo' => $data['ativo'] ?? 1,
                'configuracoes' => [
                    'tipo_banco' => $data['tipo_banco'],
                    'host' => $data['host']
                ],
                'intervalo_sincronizacao' => $data['intervalo_sincronizacao'] ?? 60
            ]);
            
            if ($integracaoId) {
                // Cria configuração Banco de Dados
                $this->bancoDadosModel->create([
                    'integracao_id' => $integracaoId,
                    'nome_conexao' => $data['nome'],
                    'tipo_banco' => $data['tipo_banco'],
                    'host' => $data['host'],
                    'porta' => $data['porta'],
                    'database' => $data['database'],
                    'usuario' => $data['usuario'],
                    'senha' => $data['senha'],
                    'tabela_origem' => $data['tabela_origem'],
                    'colunas_selecionadas' => $data['colunas_selecionadas'] ?? [],
                    'condicoes' => $data['condicoes'] ?? [],
                    'mapeamento_colunas' => $data['mapeamento_colunas'] ?? [],
                    'tabela_destino' => $data['tabela_destino'],
                    'ativo' => $data['ativo'] ?? 1
                ]);
                
                // Log de sucesso
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, 'Integração Banco de Dados criada com sucesso');
                
                $_SESSION['success'] = 'Integração Banco de Dados criada com sucesso!';
                return $response->redirect('/integracoes');
            }
            
            $_SESSION['error'] = 'Erro ao criar integração.';
            return $response->redirect('/integracoes/create/banco-dados');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/banco-dados');
        }
    }
    
    /**
     * Visualiza integração
     */
    public function show(Request $request, Response $response, $id)
    {
        $integracao = $this->integracaoModel->findById($id);
        
        if (!$integracao) {
            $_SESSION['error'] = 'Integração não encontrada.';
            return $response->redirect('/integracoes');
        }
        
        // Busca configuração específica baseada no tipo
        $configuracao = null;
        switch ($integracao['tipo']) {
            case IntegracaoConfig::TIPO_WOOCOMMERCE:
                $configuracao = $this->woocommerceModel->findByIntegracaoId($id);
                break;
            case IntegracaoConfig::TIPO_BANCO_DADOS:
                $configuracao = $this->bancoDadosModel->findByIntegracaoId($id);
                break;
        }
        
        // Busca logs
        $logs = $this->logModel->findByIntegracaoId($id, 50);
        $estatisticasLogs = $this->logModel->getEstatisticas($id, '30 days');
        
        return $this->render('integracoes/show', [
            'integracao' => $integracao,
            'configuracao' => $configuracao,
            'logs' => $logs,
            'estatisticasLogs' => $estatisticasLogs
        ]);
    }
    
    /**
     * Excluir integração
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $integracao = $this->integracaoModel->findById($id);
        
        if (!$integracao) {
            $_SESSION['error'] = 'Integração não encontrada.';
            return $response->redirect('/integracoes');
        }
        
        if ($this->integracaoModel->delete($id)) {
            $_SESSION['success'] = 'Integração excluída com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir integração.';
        }
        
        return $response->redirect('/integracoes');
    }
    
    /**
     * Ativa/Desativa integração
     */
    public function toggleStatus(Request $request, Response $response, $id)
    {
        $integracao = $this->integracaoModel->findById($id);
        
        if (!$integracao) {
            return $response->json(['success' => false, 'error' => 'Integração não encontrada'], 404);
        }
        
        $novoStatus = !$integracao['ativo'];
        
        if ($this->integracaoModel->update($id, ['ativo' => $novoStatus, 'nome' => $integracao['nome'], 'configuracoes' => $integracao['configuracoes']])) {
            return $response->json([
                'success' => true,
                'status' => $novoStatus,
                'mensagem' => $novoStatus ? 'Integração ativada!' : 'Integração desativada!'
            ]);
        }
        
        return $response->json(['success' => false, 'error' => 'Erro ao alterar status'], 500);
    }
    
    /**
     * Teste de conexão WooCommerce
     */
    public function testarWooCommerce(Request $request, Response $response)
    {
        $data = $request->all();
        
        $resultado = $this->woocommerceModel->testarConexao(
            $data['url_site'],
            $data['consumer_key'],
            $data['consumer_secret']
        );
        
        return $response->json($resultado);
    }
    
    /**
     * Teste de conexão Banco de Dados
     */
    public function testarBancoDados(Request $request, Response $response)
    {
        $data = $request->all();
        
        $resultado = $this->bancoDadosModel->testarConexao(
            $data['tipo_banco'],
            $data['host'],
            $data['porta'],
            $data['database'],
            $data['usuario'],
            $data['senha']
        );
        
        return $response->json($resultado);
    }
    
    /**
     * Sincronização manual com opções
     */
    public function sincronizar(Request $request, Response $response, $id)
    {
        $integracao = $this->integracaoModel->findById($id);
        
        if (!$integracao) {
            return $response->json(['sucesso' => false, 'erro' => 'Integração não encontrada'], 404);
        }
        
        try {
            // Captura opções de sincronização
            $opcoes = $request->all();
            
            $resultado = null;
            
            if ($integracao['tipo'] === IntegracaoConfig::TIPO_WOOCOMMERCE) {
                $service = new WooCommerceService();
                $resultado = $service->sincronizar($id, $opcoes);
            } elseif ($integracao['tipo'] === IntegracaoConfig::TIPO_BANCO_DADOS) {
                $service = new IntegracaoBancoDadosService();
                $resultado = $service->sincronizar($id, $opcoes);
            }
            
            if ($resultado && $resultado['sucesso']) {
                $_SESSION['success'] = 'Sincronização executada com sucesso!';
                return $response->json($resultado);
            } else {
                return $response->json($resultado ?: ['sucesso' => false, 'erro' => 'Tipo de integração não suportado'], 400);
            }
        } catch (\Exception $e) {
            $this->logModel->create($id, IntegracaoLog::TIPO_ERRO, 'Erro na sincronização manual: ' . $e->getMessage());
            return $response->json(['sucesso' => false, 'erro' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Webhook do WooCommerce
     */
    public function webhook(Request $request, Response $response, $integracaoId)
    {
        $integracao = $this->integracaoModel->findById($integracaoId);
        
        if (!$integracao || $integracao['tipo'] !== IntegracaoConfig::TIPO_WOOCOMMERCE) {
            return $response->json(['erro' => 'Integração inválida'], 404);
        }
        
        $config = $this->woocommerceModel->findByIntegracaoId($integracaoId);
        
        if (!$config) {
            return $response->json(['erro' => 'Configuração não encontrada'], 404);
        }
        
        try {
            // Verifica assinatura do webhook
            $signature = $_SERVER['HTTP_X_WC_WEBHOOK_SIGNATURE'] ?? '';
            $payload = file_get_contents('php://input');
            
            if ($config['webhook_secret']) {
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $config['webhook_secret'], true));
                if ($signature !== $expectedSignature) {
                    $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 'Webhook: Assinatura inválida');
                    return $response->json(['erro' => 'Assinatura inválida'], 401);
                }
            }
            
            // Processa webhook
            $data = json_decode($payload, true);
            $topic = $_SERVER['HTTP_X_WC_WEBHOOK_TOPIC'] ?? '';
            
            $service = new WooCommerceService();
            $resultado = $service->processarWebhook($integracaoId, $topic, $data, $integracao['empresa_id']);
            
            if ($resultado['sucesso']) {
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, "Webhook processado: {$topic}");
                return $response->json(['sucesso' => true]);
            } else {
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, "Webhook falhou: {$resultado['erro']}");
                return $response->json(['erro' => $resultado['erro']], 400);
            }
            
        } catch (\Exception $e) {
            $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 'Erro no webhook: ' . $e->getMessage());
            return $response->json(['erro' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Validação WooCommerce
     */
    protected function validateWooCommerce($data)
    {
        $errors = [];
        
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'Selecione uma empresa';
        }
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        if (empty($data['url_site'])) {
            $errors['url_site'] = 'A URL do site é obrigatória';
        } elseif (!filter_var($data['url_site'], FILTER_VALIDATE_URL)) {
            $errors['url_site'] = 'URL inválida';
        }
        
        if (empty($data['consumer_key'])) {
            $errors['consumer_key'] = 'Consumer Key é obrigatória';
        }
        
        if (empty($data['consumer_secret'])) {
            $errors['consumer_secret'] = 'Consumer Secret é obrigatória';
        }
        
        return $errors;
    }
    
    /**
     * Validação Banco de Dados
     */
    protected function validateBancoDados($data)
    {
        $errors = [];
        
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'Selecione uma empresa';
        }
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        if (empty($data['tipo_banco'])) {
            $errors['tipo_banco'] = 'Selecione o tipo de banco';
        }
        
        if (empty($data['host'])) {
            $errors['host'] = 'O host é obrigatório';
        }
        
        if (empty($data['porta'])) {
            $errors['porta'] = 'A porta é obrigatória';
        }
        
        if (empty($data['database'])) {
            $errors['database'] = 'O nome do banco é obrigatório';
        }
        
        if (empty($data['usuario'])) {
            $errors['usuario'] = 'O usuário é obrigatório';
        }
        
        if (empty($data['senha'])) {
            $errors['senha'] = 'A senha é obrigatória';
        }
        
        if (empty($data['tabela_origem'])) {
            $errors['tabela_origem'] = 'A tabela de origem é obrigatória';
        }
        
        if (empty($data['tabela_destino'])) {
            $errors['tabela_destino'] = 'A tabela de destino é obrigatória';
        }
        
        return $errors;
    }
}
