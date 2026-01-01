<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ConexaoBancaria;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Empresa;
use App\Models\Usuario;
use Includes\Services\OpenBankingService;

class ConexaoBancariaController extends Controller
{
    private $conexaoModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->conexaoModel = new ConexaoBancaria();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->centroCustoModel = new CentroCusto();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Lista todas as conexões bancárias
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        
        // Buscar empresas do usuário
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        
        // Pegar empresa da URL ou primeira empresa
        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }
        
        $conexoes = [];
        $empresa = null;
        
        if ($empresaId) {
            $conexoes = $this->conexaoModel->findByEmpresa($empresaId);
            $empresa = $this->empresaModel->findById($empresaId);
        }
        
        return $this->render('conexoes_bancarias/index', [
            'conexoes' => $conexoes,
            'empresa' => $empresa,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId
        ]);
    }
    
    /**
     * Formulário de nova conexão
     */
    public function create(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        
        // Buscar empresas do usuário
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        
        // Pegar empresa da URL ou primeira empresa
        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }
        
        $categorias = [];
        $centrosCusto = [];
        $empresa = null;
        
        if ($empresaId) {
            $categorias = $this->categoriaModel->findAll($empresaId);
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $empresa = $this->empresaModel->findById($empresaId);
        }
        
        return $this->render('conexoes_bancarias/create', [
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'empresa' => $empresa,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId
        ]);
    }
    
    /**
     * Iniciar fluxo de consentimento OAuth 2.0
     */
    public function iniciarConsentimento(Request $request, Response $response)
    {
        $data = $request->all();
        // Captura empresa_id do POST (form), ou querystring, ou sessão
        $empresaId = $data['empresa_id'] ?? $request->get('empresa_id') ?? ($_SESSION['usuario_empresa_id'] ?? null);
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (!$empresaId || !$usuarioId) {
            return $response->json(['error' => 'Sessão inválida'], 401);
        }
        
        // Validar dados básicos
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect('/conexoes-bancarias/create');
        }
        
        // Salvar configurações temporariamente na sessão
        $_SESSION['conexao_temp'] = [
            'banco' => $data['banco'],
            'tipo' => $data['tipo'],
            'identificacao' => $data['identificacao'] ?? null,
            'auto_sync' => $data['auto_sync'] ?? 1,
            'frequencia_sync' => $data['frequencia_sync'] ?? 'diaria',
            'categoria_padrao_id' => !empty($data['categoria_padrao_id']) ? $data['categoria_padrao_id'] : null,
            'centro_custo_padrao_id' => !empty($data['centro_custo_padrao_id']) ? $data['centro_custo_padrao_id'] : null,
            'aprovacao_automatica' => $data['aprovacao_automatica'] ?? 0
        ];
        
        // Iniciar fluxo OAuth
        $openBankingService = new OpenBankingService();
        $redirectUri = $request->getBaseUrl() . '/conexoes-bancarias/callback';
        $authUrl = $openBankingService->iniciarConsentimento($empresaId, $usuarioId, $data['banco'], $redirectUri);
        
        return $response->redirect($authUrl);
    }
    
    /**
     * Callback do OAuth 2.0
     */
    public function callback(Request $request, Response $response)
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (!$code || !$empresaId || !$usuarioId) {
            $_SESSION['error'] = 'Erro ao autorizar acesso ao banco';
            return $response->redirect('/conexoes-bancarias');
        }
        
        try {
            // Trocar código por access_token
            $openBankingService = new OpenBankingService();
            $redirectUri = $request->getBaseUrl() . '/conexoes-bancarias/callback';
            $tokens = $openBankingService->obterAccessToken($code, $redirectUri);
            
            // Recuperar dados temporários
            $conexaoTemp = $_SESSION['conexao_temp'] ?? [];
            unset($_SESSION['conexao_temp']);
            
            // Criptografar tokens
            $encryptionKey = getenv('ENCRYPTION_KEY') ?: 'default_key_change_in_production';
            $accessTokenEncrypted = OpenBankingService::encrypt($tokens['access_token'], $encryptionKey);
            $refreshTokenEncrypted = OpenBankingService::encrypt($tokens['refresh_token'], $encryptionKey);
            
            // Calcular expiração
            $expiresIn = $tokens['expires_in'] ?? 3600;
            $tokenExpiraEm = date('Y-m-d H:i:s', time() + $expiresIn);
            
            // Salvar conexão no banco
            $conexaoData = array_merge($conexaoTemp, [
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'access_token' => $accessTokenEncrypted,
                'refresh_token' => $refreshTokenEncrypted,
                'token_expira_em' => $tokenExpiraEm,
                'consent_id' => $tokens['consent_id'] ?? null,
                'ativo' => 1
            ]);
            
            $conexaoId = $this->conexaoModel->create($conexaoData);
            
            if ($conexaoId) {
                $_SESSION['success'] = 'Conexão bancária autorizada com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao salvar conexão bancária';
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao processar autorização: ' . $e->getMessage();
        }
        
        return $response->redirect('/conexoes-bancarias');
    }
    
    /**
     * Exibir detalhes da conexão
     */
    public function show(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        // Verificar se pertence à empresa do usuário
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        if ($conexao['empresa_id'] != $empresaId) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresa = $this->empresaModel->findById($conexao['empresa_id']);
        $categoria = null;
        $centroCusto = null;
        
        if ($conexao['categoria_padrao_id']) {
            $categoria = $this->categoriaModel->findById($conexao['categoria_padrao_id']);
        }
        
        if ($conexao['centro_custo_padrao_id']) {
            $centroCusto = $this->centroCustoModel->findById($conexao['centro_custo_padrao_id']);
        }
        
        return $this->render('conexoes_bancarias/show', [
            'conexao' => $conexao,
            'empresa' => $empresa,
            'categoria' => $categoria,
            'centroCusto' => $centroCusto
        ]);
    }
    
    /**
     * Formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        if ($conexao['empresa_id'] != $empresaId) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $categorias = $this->categoriaModel->findAll($empresaId);
        $centrosCusto = $this->centroCustoModel->findAll($empresaId);
        $empresa = $this->empresaModel->findById($empresaId);
        
        return $this->render('conexoes_bancarias/edit', [
            'conexao' => $conexao,
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'empresa' => $empresa
        ]);
    }
    
    /**
     * Atualizar conexão
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        if ($conexao['empresa_id'] != $empresaId) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/conexoes-bancarias');
        }
        
        // Validar apenas campos editáveis
        $errors = $this->validateUpdate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect("/conexoes-bancarias/{$id}/edit");
        }
        
        // Atualizar apenas campos permitidos
        $updateData = [
            'empresa_id' => $conexao['empresa_id'],
            'usuario_id' => $conexao['usuario_id'],
            'banco' => $conexao['banco'],
            'tipo' => $conexao['tipo'],
            'identificacao' => $data['identificacao'] ?? $conexao['identificacao'],
            'access_token' => $conexao['access_token'],
            'refresh_token' => $conexao['refresh_token'],
            'token_expira_em' => $conexao['token_expira_em'],
            'consent_id' => $conexao['consent_id'],
            'auto_sync' => $data['auto_sync'] ?? 1,
            'frequencia_sync' => $data['frequencia_sync'] ?? 'diaria',
            'categoria_padrao_id' => !empty($data['categoria_padrao_id']) ? $data['categoria_padrao_id'] : null,
            'centro_custo_padrao_id' => !empty($data['centro_custo_padrao_id']) ? $data['centro_custo_padrao_id'] : null,
            'aprovacao_automatica' => $data['aprovacao_automatica'] ?? 0,
            'ativo' => $data['ativo'] ?? 1,
            'ultima_sincronizacao' => $conexao['ultima_sincronizacao']
        ];
        
        if ($this->conexaoModel->update($id, $updateData)) {
            $_SESSION['success'] = 'Conexão atualizada com sucesso!';
            return $response->redirect("/conexoes-bancarias/{$id}");
        }
        
        $_SESSION['error'] = 'Erro ao atualizar conexão';
        return $response->redirect("/conexoes-bancarias/{$id}/edit");
    }
    
    /**
     * Sincronizar manualmente
     */
    public function sincronizar(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            return $response->json(['error' => 'Conexão não encontrada'], 404);
        }
        
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        if ($conexao['empresa_id'] != $empresaId) {
            return $response->json(['error' => 'Acesso negado'], 403);
        }
        
        try {
            $openBankingService = new OpenBankingService();
            
            // Verificar se token expirou
            if (strtotime($conexao['token_expira_em']) < time()) {
                $newTokens = $openBankingService->renovarAccessToken($conexao);
                
                // Atualizar tokens
                $encryptionKey = getenv('ENCRYPTION_KEY') ?: 'default_key_change_in_production';
                $conexao['access_token'] = OpenBankingService::encrypt($newTokens['access_token'], $encryptionKey);
                $conexao['token_expira_em'] = date('Y-m-d H:i:s', time() + $newTokens['expires_in']);
                
                $this->conexaoModel->update($id, $conexao);
            }
            
            // Sincronizar transações
            if ($conexao['tipo'] === 'cartao_credito') {
                $transacoes = $openBankingService->sincronizarCartao($conexao);
            } else {
                $transacoes = $openBankingService->sincronizarExtrato($conexao);
            }
            
            // Processar e salvar transações
            $novasTransacoes = $this->processarTransacoes($transacoes, $conexao);
            
            // Atualizar data de sincronização
            $this->conexaoModel->update($id, array_merge($conexao, [
                'ultima_sincronizacao' => date('Y-m-d H:i:s')
            ]));
            
            return $response->json([
                'success' => true,
                'message' => count($novasTransacoes) . ' novas transações sincronizadas',
                'total' => count($novasTransacoes)
            ]);
            
        } catch (\Exception $e) {
            return $response->json([
                'error' => 'Erro ao sincronizar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Processar transações sincronizadas
     */
    private function processarTransacoes($transacoes, $conexao)
    {
        $transacaoPendenteModel = new \App\Models\TransacaoPendente();
        $classificadorService = new \Includes\Services\ClassificadorIAService($conexao['empresa_id']);
        
        $novas = [];
        
        foreach ($transacoes as $transacao) {
            // Gerar hash único para evitar duplicatas
            $hash = hash('sha256', $conexao['id'] . $transacao['transacao_id_banco'] . $transacao['data_transacao'] . $transacao['valor']);
            
            // Verificar se já existe
            $existente = $transacaoPendenteModel->findByHash($hash);
            if ($existente) {
                continue;
            }
            
            // Classificar com IA
            $classificacao = $classificadorService->analisar($transacao);
            
            // Salvar transação pendente
            $transacaoData = [
                'empresa_id' => $conexao['empresa_id'],
                'conexao_bancaria_id' => $conexao['id'],
                'data_transacao' => $transacao['data_transacao'],
                'descricao_original' => $transacao['descricao_original'],
                'valor' => $transacao['valor'],
                'tipo' => $transacao['tipo'],
                'origem' => $transacao['origem'],
                'transacao_hash' => $hash,
                'categoria_sugerida_id' => $classificacao['categoria_id'] ?? $conexao['categoria_padrao_id'],
                'centro_custo_sugerido_id' => $classificacao['centro_custo_id'] ?? $conexao['centro_custo_padrao_id'],
                'fornecedor_sugerido_id' => $classificacao['fornecedor_id'] ?? null,
                'cliente_sugerido_id' => $classificacao['cliente_id'] ?? null,
                'confianca_ia' => $classificacao['confianca'] ?? null,
                'status' => 'pendente'
            ];
            
            $novaId = $transacaoPendenteModel->create($transacaoData);
            if ($novaId) {
                $novas[] = $novaId;
            }
        }
        
        return $novas;
    }
    
    /**
     * Desativar conexão
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        if ($conexao['empresa_id'] != $empresaId) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/conexoes-bancarias');
        }
        
        // Soft delete
        if ($this->conexaoModel->update($id, array_merge($conexao, ['ativo' => 0]))) {
            $_SESSION['success'] = 'Conexão desativada com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao desativar conexão';
        }
        
        return $response->redirect('/conexoes-bancarias');
    }
    
    /**
     * Validar dados da conexão
     */
    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Banco
        if (empty($data['banco'])) {
            $errors['banco'] = 'O banco é obrigatório';
        } elseif (!in_array($data['banco'], ['sicredi', 'sicoob', 'bradesco', 'itau'])) {
            $errors['banco'] = 'Banco inválido';
        }
        
        // Tipo
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'O tipo de conta é obrigatório';
        } elseif (!in_array($data['tipo'], ['conta_corrente', 'conta_poupanca', 'cartao_credito'])) {
            $errors['tipo'] = 'Tipo de conta inválido';
        }
        
        return $errors;
    }
    
    /**
     * Validar dados de atualização
     */
    protected function validateUpdate($data)
    {
        $errors = [];
        
        // Frequência de sync
        if (isset($data['frequencia_sync']) && !in_array($data['frequencia_sync'], ['manual', '10min', '30min', 'horaria', 'diaria', 'semanal'])) {
            $errors['frequencia_sync'] = 'Frequência de sincronização inválida';
        }
        
        return $errors;
    }
}
