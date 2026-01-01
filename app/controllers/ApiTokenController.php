<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ApiToken;
use App\Models\ApiLog;
use App\Models\Empresa;

class ApiTokenController extends Controller
{
    private $apiTokenModel;
    private $apiLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->apiTokenModel = new ApiToken();
        $this->apiLogModel = new ApiLog();
    }

    /**
     * Lista todos os tokens
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        $tokens = $this->apiTokenModel->findAll($empresaId);
        
        // Adicionar estatísticas para cada token
        foreach ($tokens as &$token) {
            $token['stats'] = $this->apiTokenModel->getStats($token['id']);
        }
        
        return $this->render('api_tokens/index', [
            'tokens' => $tokens
        ]);
    }

    /**
     * Exibe formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        $empresaModel = new Empresa();
        $empresas = $empresaModel->findAll();
        
        return $this->render('api_tokens/create', [
            'empresas' => $empresas
        ]);
    }

    /**
     * Cria um novo token
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $data['usuario_id'] = $_SESSION['usuario_id'];
        
        // Validação
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect('/api-tokens/create');
        }
        
        // Processar permissões
        if (isset($data['permissoes'])) {
            $data['permissoes'] = array_filter($data['permissoes']);
        }
        
        // Processar IP whitelist
        if (!empty($data['ip_whitelist_text'])) {
            $ips = explode("\n", $data['ip_whitelist_text']);
            $ips = array_map('trim', $ips);
            $ips = array_filter($ips);
            $data['ip_whitelist'] = $ips;
        }
        unset($data['ip_whitelist_text']);
        
        // Processar data de expiração
        if (empty($data['expira_em'])) {
            $data['expira_em'] = null;
        }
        
        $id = $this->apiTokenModel->create($data);
        
        // Buscar token criado para exibir
        $token = $this->apiTokenModel->findById($id);
        
        $_SESSION['success'] = 'Token criado com sucesso! Guarde o token em local seguro, ele não será exibido novamente.';
        $_SESSION['new_token'] = $token['token'];
        
        return $response->redirect('/api-tokens');
    }

    /**
     * Exibe detalhes do token
     */
    public function show(Request $request, Response $response, $id)
    {
        $token = $this->apiTokenModel->findById($id);
        
        if (!$token) {
            $_SESSION['error'] = 'Token não encontrado';
            return $response->redirect('/api-tokens');
        }
        
        // Estatísticas
        $stats = $this->apiTokenModel->getStats($id);
        
        // Últimas requisições
        $logs = $this->apiLogModel->findAll(['token_id' => $id], 50);
        
        // Endpoints mais usados
        $topEndpoints = $this->apiLogModel->getTopEndpoints(10);
        
        return $this->render('api_tokens/show', [
            'token' => $token,
            'stats' => $stats,
            'logs' => $logs,
            'topEndpoints' => $topEndpoints
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $token = $this->apiTokenModel->findById($id);
        
        if (!$token) {
            $_SESSION['error'] = 'Token não encontrado';
            return $response->redirect('/api-tokens');
        }
        
        $empresaModel = new Empresa();
        $empresas = $empresaModel->findAll();
        
        // Decodificar JSON
        $token['permissoes'] = json_decode($token['permissoes'], true) ?? [];
        $token['ip_whitelist'] = json_decode($token['ip_whitelist'], true) ?? [];
        
        return $this->render('api_tokens/edit', [
            'token' => $token,
            'empresas' => $empresas
        ]);
    }

    /**
     * Atualiza um token
     */
    public function update(Request $request, Response $response, $id)
    {
        $token = $this->apiTokenModel->findById($id);
        
        if (!$token) {
            $_SESSION['error'] = 'Token não encontrado';
            return $response->redirect('/api-tokens');
        }
        
        $data = $request->all();
        
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect("/api-tokens/$id/edit");
        }
        
        // Processar permissões
        if (isset($data['permissoes'])) {
            $data['permissoes'] = array_filter($data['permissoes']);
        }
        
        // Processar IP whitelist
        if (!empty($data['ip_whitelist_text'])) {
            $ips = explode("\n", $data['ip_whitelist_text']);
            $ips = array_map('trim', $ips);
            $ips = array_filter($ips);
            $data['ip_whitelist'] = $ips;
        } else {
            $data['ip_whitelist'] = [];
        }
        unset($data['ip_whitelist_text']);
        
        // Processar data de expiração
        if (empty($data['expira_em'])) {
            $data['expira_em'] = null;
        }
        
        $this->apiTokenModel->update($id, $data);
        
        $_SESSION['success'] = 'Token atualizado com sucesso!';
        return $response->redirect('/api-tokens');
    }

    /**
     * Exclui um token
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $token = $this->apiTokenModel->findById($id);
        
        if (!$token) {
            $_SESSION['error'] = 'Token não encontrado';
            return $response->redirect('/api-tokens');
        }
        
        $this->apiTokenModel->delete($id);
        
        $_SESSION['success'] = 'Token excluído com sucesso!';
        return $response->redirect('/api-tokens');
    }

    /**
     * Regenera o token
     */
    public function regenerate(Request $request, Response $response, $id)
    {
        $token = $this->apiTokenModel->findById($id);
        
        if (!$token) {
            $_SESSION['error'] = 'Token não encontrado';
            return $response->redirect('/api-tokens');
        }
        
        $newToken = $this->apiTokenModel->regenerate($id);
        
        $_SESSION['success'] = 'Token regenerado com sucesso! Guarde o novo token em local seguro.';
        $_SESSION['new_token'] = $newToken;
        
        return $response->redirect('/api-tokens');
    }

    /**
     * Validação
     */
    protected function validate($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        
        if (!isset($data['rate_limit']) || $data['rate_limit'] < 1) {
            $errors['rate_limit'] = 'Rate limit deve ser no mínimo 1';
        }
        
        return $errors;
    }
}
