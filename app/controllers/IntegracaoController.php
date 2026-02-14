<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWooCommerce;
use App\Models\IntegracaoBancoDados;
use App\Models\IntegracaoWebhook;
use App\Models\IntegracaoApi;
use App\Models\IntegracaoWebmaniBR;
use App\Models\IntegracaoLog;
use App\Models\Empresa;
use Includes\Services\WooCommerceService;
use Includes\Services\IntegracaoBancoDadosService;
use Includes\Services\WebmaniBRService;

class IntegracaoController extends Controller
{
    private $integracaoModel;
    private $woocommerceModel;
    private $bancoDadosModel;
    private $webhookModel;
    private $apiModel;
    private $webmanibrModel;
    private $logModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->integracaoModel = new IntegracaoConfig();
        $this->woocommerceModel = new IntegracaoWooCommerce();
        $this->bancoDadosModel = new IntegracaoBancoDados();
        $this->webhookModel = new IntegracaoWebhook();
        $this->apiModel = new IntegracaoApi();
        $this->webmanibrModel = new IntegracaoWebmaniBR();
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
                
            case 'webhook':
                return $this->render('integracoes/webhook/create', [
                    'empresas' => $empresas,
                    'empresaId' => $empresaId
                ]);
                
            case 'api':
                return $this->render('integracoes/api/create', [
                    'empresas' => $empresas,
                    'empresaId' => $empresaId
                ]);
                
            case 'webmanibr':
                return $this->render('integracoes/webmanibr/create', [
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
            // Captura opções de sincronização (suporta JSON e form-data)
            $opcoes = $request->isJson() ? ($request->json() ?: []) : $request->all();
            
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
        // Log imediato para saber que o webhook foi chamado
        \App\Models\LogSistema::info('WooCommerce', 'webhook_controller', 
            "Webhook recebido para integração #{$integracaoId}",
            ['method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A', 'topic' => $_SERVER['HTTP_X_WC_WEBHOOK_TOPIC'] ?? 'N/A']);
        
        try {
            $integracao = $this->integracaoModel->findById($integracaoId);
            
            if (!$integracao || $integracao['tipo'] !== IntegracaoConfig::TIPO_WOOCOMMERCE) {
                \App\Models\LogSistema::error('WooCommerce', 'webhook_controller', 
                    "Integração #{$integracaoId} inválida ou não é WooCommerce");
                return $response->json(['erro' => 'Integração inválida'], 404);
            }
            
            $config = $this->woocommerceModel->findByIntegracaoId($integracaoId);
            
            if (!$config) {
                \App\Models\LogSistema::error('WooCommerce', 'webhook_controller', 
                    "Config não encontrada para integração #{$integracaoId}");
                return $response->json(['erro' => 'Configuração não encontrada'], 404);
            }
            
            // Lê payload
            $payload = file_get_contents('php://input');
            $topic = $_SERVER['HTTP_X_WC_WEBHOOK_TOPIC'] ?? '';
            
            \App\Models\LogSistema::debug('WooCommerce', 'webhook_controller', 
                "Topic: {$topic}, Payload size: " . strlen($payload) . " bytes");
            
            // Verifica assinatura do webhook (se configurada)
            if (!empty($config['webhook_secret'])) {
                $signature = $_SERVER['HTTP_X_WC_WEBHOOK_SIGNATURE'] ?? '';
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $config['webhook_secret'], true));
                if ($signature !== $expectedSignature) {
                    \App\Models\LogSistema::error('WooCommerce', 'webhook_controller', 
                        "Assinatura inválida! Esperada: {$expectedSignature}, Recebida: {$signature}");
                    $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 'Webhook: Assinatura inválida');
                    return $response->json(['erro' => 'Assinatura inválida'], 401);
                }
            }
            
            // Decodifica payload
            $data = json_decode($payload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \App\Models\LogSistema::error('WooCommerce', 'webhook_controller', 
                    "Payload JSON inválido: " . json_last_error_msg(),
                    ['payload_preview' => substr($payload, 0, 500)]);
                return $response->json(['erro' => 'Payload JSON inválido'], 400);
            }
            
            // Ignora pings do WooCommerce (webhook de teste)
            if (empty($topic) || $topic === 'action.woocommerce_webhook_payload') {
                \App\Models\LogSistema::info('WooCommerce', 'webhook_controller', 
                    "Ping/teste do WooCommerce recebido - OK");
                return $response->json(['sucesso' => true, 'mensagem' => 'Ping recebido']);
            }
            
            // Processa webhook
            $service = new WooCommerceService();
            $resultado = $service->processarWebhook($integracaoId, $topic, $data, $integracao['empresa_id']);
            
            if ($resultado['sucesso']) {
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, 
                    "Webhook ({$topic}): " . ($resultado['mensagem'] ?? 'OK'));
                return $response->json(['sucesso' => true]);
            } else {
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 
                    "Webhook ({$topic}) falhou: " . ($resultado['erro'] ?? 'Erro desconhecido'));
                return $response->json(['erro' => $resultado['erro']], 400);
            }
            
        } catch (\Throwable $e) {
            \App\Models\LogSistema::error('WooCommerce', 'webhook_controller', 
                "ERRO FATAL no webhook: " . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]);
            
            try {
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_ERRO, 
                    'Erro fatal no webhook: ' . $e->getMessage());
            } catch (\Throwable $e2) {
                // Não pode nem logar
            }
            
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
    
    /**
     * Salva integração Webhook
     */
    public function storeWebhook(Request $request, Response $response)
    {
        $data = $request->all();
        
        try {
            // Cria configuração principal
            $integracaoId = $this->integracaoModel->create([
                'empresa_id' => $data['empresa_id'],
                'tipo' => IntegracaoConfig::TIPO_WEBHOOK,
                'nome' => $data['nome'],
                'ativo' => $data['ativo'] ?? 1,
                'configuracoes' => ['url_webhook' => $data['url_webhook']],
                'intervalo_sincronizacao' => 0 // Webhooks são em tempo real
            ]);
            
            if ($integracaoId) {
                // Cria configuração Webhook
                $this->webhookModel->create([
                    'integracao_id' => $integracaoId,
                    'nome_webhook' => $data['nome_webhook'],
                    'url_webhook' => $data['url_webhook'],
                    'metodo' => $data['metodo'] ?? 'POST',
                    'autenticacao' => $data['autenticacao'] ?? 'none',
                    'auth_usuario' => $data['auth_usuario'] ?? null,
                    'auth_senha' => $data['auth_senha'] ?? null,
                    'auth_token' => $data['auth_token'] ?? null,
                    'api_key_header' => $data['api_key_header'] ?? null,
                    'api_key_value' => $data['api_key_value'] ?? null,
                    'eventos_disparo' => $data['eventos_disparo'] ?? [],
                    'ativo' => $data['ativo'] ?? 1
                ]);
                
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, 'Integração Webhook criada');
                $_SESSION['success'] = 'Webhook criado com sucesso!';
                return $response->redirect('/integracoes');
            }
            
            $_SESSION['error'] = 'Erro ao criar webhook.';
            return $response->redirect('/integracoes/create/webhook');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/webhook');
        }
    }
    
    /**
     * Salva integração API
     */
    public function storeApi(Request $request, Response $response)
    {
        $data = $request->all();
        
        try {
            // Cria configuração principal
            $integracaoId = $this->integracaoModel->create([
                'empresa_id' => $data['empresa_id'],
                'tipo' => IntegracaoConfig::TIPO_API,
                'nome' => $data['nome'],
                'ativo' => $data['ativo'] ?? 1,
                'configuracoes' => ['base_url' => $data['base_url']],
                'intervalo_sincronizacao' => 60
            ]);
            
            if ($integracaoId) {
                // Cria configuração API
                $this->apiModel->create([
                    'integracao_id' => $integracaoId,
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
                    'ativo' => $data['ativo'] ?? 1
                ]);
                
                $this->logModel->create($integracaoId, IntegracaoLog::TIPO_SUCESSO, 'Integração API criada');
                $_SESSION['success'] = 'API criada com sucesso!';
                return $response->redirect('/integracoes');
            }
            
            $_SESSION['error'] = 'Erro ao criar API.';
            return $response->redirect('/integracoes/create/api');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/api');
        }
    }
    
    /**
     * Teste de conexão API
     */
    public function testarApi(Request $request, Response $response)
    {
        $data = $request->all();
        
        $resultado = $this->apiModel->testarConexao(
            $data['base_url'],
            $data['autenticacao'],
            $data['authData'] ?? []
        );
        
        return $response->json($resultado);
    }
    
    /**
     * Salva configuração WebmaniaBR
     */
    public function storeWebmaniBR(Request $request, Response $response)
    {
        $data = $request->all();
        
        try {
            // Criar integração principal
            $integracaoId = $this->integracaoModel->create([
                'empresa_id' => $data['empresa_id'],
                'nome' => $data['nome'],
                'tipo' => IntegracaoConfig::TIPO_WEBMANIBR,
                'descricao' => $data['descricao'] ?? null,
                'ativo' => 1
            ]);
            
            // Processar certificado digital se foi enviado
            $certificadoBase64 = null;
            if (isset($_FILES['certificado_arquivo']) && $_FILES['certificado_arquivo']['error'] == 0) {
                $certificadoBase64 = base64_encode(file_get_contents($_FILES['certificado_arquivo']['tmp_name']));
            }
            
            // Criar configuração WebmaniaBR
            $configData = [
                'integracao_id' => $integracaoId,
                'consumer_key' => $data['consumer_key'],
                'consumer_secret' => $data['consumer_secret'],
                'access_token' => $data['access_token'],
                'access_token_secret' => $data['access_token_secret'],
                'bearer_token' => $data['bearer_token'] ?? null,
                'ambiente' => $data['ambiente'],
                'emitir_automatico' => $data['emitir_automatico'],
                'enviar_email_cliente' => isset($data['enviar_email_cliente']) ? 1 : 0,
                'emitir_data_pedido' => isset($data['emitir_data_pedido']) ? 1 : 0,
                'email_notificacao' => $data['email_notificacao'] ?? null,
                'nfse_classe_imposto' => $data['nfse_classe_imposto'] ?? null,
                'nfse_tipo_desconto' => $data['nfse_tipo_desconto'] ?? 'nenhum',
                'nfse_incluir_taxas' => isset($data['nfse_incluir_taxas']) ? 1 : 0,
                'natureza_operacao' => $data['natureza_operacao'] ?? 'Venda',
                'nfe_classe_imposto' => $data['nfe_classe_imposto'] ?? null,
                'ncm_padrao' => $data['ncm_padrao'] ?? null,
                'cest_padrao' => $data['cest_padrao'] ?? null,
                'origem_padrao' => $data['origem_padrao'] ?? 0,
                'intermediador' => $data['intermediador'] ?? 0,
                'intermediador_cnpj' => $data['intermediador_cnpj'] ?? null,
                'intermediador_id' => $data['intermediador_id'] ?? null,
                'informacoes_fisco' => $data['informacoes_fisco'] ?? null,
                'informacoes_complementares' => $data['informacoes_complementares'] ?? null,
                'descricao_complementar_servico' => $data['descricao_complementar_servico'] ?? null,
                'preenchimento_automatico_endereco' => isset($data['preenchimento_automatico_endereco']) ? 1 : 0,
                'bairro_obrigatorio' => isset($data['bairro_obrigatorio']) ? 1 : 0,
                'certificado_digital' => $certificadoBase64,
                'certificado_senha' => $data['certificado_senha'] ?? null,
                'certificado_validade' => $data['certificado_validade'] ?? null
            ];
            
            $this->webmanibrModel->create($configData);
            
            $_SESSION['success'] = 'Integração WebmaniaBR configurada com sucesso!';
            return $response->redirect('/integracoes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao salvar integração: ' . $e->getMessage();
            $this->session->set('old', $data);
            return $response->redirect('/integracoes/create/webmanibr');
        }
    }
    
    /**
     * Teste de conexão WebmaniaBR
     */
    public function testarWebmaniBR(Request $request, Response $response)
    {
        $data = $request->all();
        
        try {
            $service = new WebmaniBRService([
                'consumer_key' => $data['consumer_key'],
                'consumer_secret' => $data['consumer_secret'],
                'access_token' => $data['access_token'],
                'access_token_secret' => $data['access_token_secret'],
                'ambiente' => $data['ambiente']
            ]);
            
            $resultado = $service->testarConexao();
            
            return $response->json($resultado);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ]);
        }
    }
}
