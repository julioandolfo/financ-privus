<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\PerfilConsolidacao;
use App\Models\Empresa;

class PerfilConsolidacaoController extends Controller
{
    private $perfilModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->perfilModel = new PerfilConsolidacao();
            $usuarioId = $_SESSION['usuario_id'];
            
            // Busca perfis do usuário e compartilhados
            $perfisUsuario = $this->perfilModel->findByUsuario($usuarioId);
            $perfisCompartilhados = $this->perfilModel->findCompartilhados();
            
            return $this->render('perfis_consolidacao/index', [
                'title' => 'Perfis de Consolidação',
                'perfisUsuario' => $perfisUsuario,
                'perfisCompartilhados' => $perfisCompartilhados
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar perfis: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            
            return $this->render('perfis_consolidacao/create', [
                'title' => 'Novo Perfil de Consolidação',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/perfis-consolidacao');
        }
    }

    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->all();
            
            // Validações
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect('/perfis-consolidacao/create');
                return;
            }
            
            // Prepara dados
            $data['usuario_id'] = $_SESSION['usuario_id'];
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            $data['empresas_ids'] = $data['empresas_ids'] ?? [];
            
            // Cria perfil
            $this->perfilModel = new PerfilConsolidacao();
            $id = $this->perfilModel->create($data);
            
            $_SESSION['success'] = 'Perfil de consolidação criado com sucesso!';
            $response->redirect('/perfis-consolidacao');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar perfil: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/perfis-consolidacao/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->perfilModel = new PerfilConsolidacao();
            $perfil = $this->perfilModel->findById($id);
            
            if (!$perfil) {
                $_SESSION['error'] = 'Perfil não encontrado!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Verifica permissão
            if (!$this->perfilModel->podeAcessar($id, $_SESSION['usuario_id'])) {
                $_SESSION['error'] = 'Você não tem permissão para acessar este perfil!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Busca empresas do perfil
            $empresasIds = $this->perfilModel->getEmpresasIds($perfil);
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findByIds($empresasIds);
            
            return $this->render('perfis_consolidacao/show', [
                'title' => 'Detalhes do Perfil',
                'perfil' => $perfil,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar perfil: ' . $e->getMessage();
            $response->redirect('/perfis-consolidacao');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->perfilModel = new PerfilConsolidacao();
            $perfil = $this->perfilModel->findById($id);
            
            if (!$perfil) {
                $_SESSION['error'] = 'Perfil não encontrado!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Verifica permissão (só pode editar se for dele ou se for compartilhado sendo admin)
            if ($perfil['usuario_id'] != $_SESSION['usuario_id'] && $perfil['usuario_id'] !== null) {
                $_SESSION['error'] = 'Você não tem permissão para editar este perfil!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            
            // Decodifica empresas_ids
            $perfil['empresas_ids'] = $this->perfilModel->getEmpresasIds($perfil);
            
            return $this->render('perfis_consolidacao/edit', [
                'title' => 'Editar Perfil',
                'perfil' => $perfil,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar perfil: ' . $e->getMessage();
            $response->redirect('/perfis-consolidacao');
        }
    }

    public function update(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Validações
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect("/perfis-consolidacao/{$id}/edit");
                return;
            }
            
            // Verifica permissão
            $this->perfilModel = new PerfilConsolidacao();
            $perfil = $this->perfilModel->findById($id);
            if ($perfil['usuario_id'] != $_SESSION['usuario_id'] && $perfil['usuario_id'] !== null) {
                $_SESSION['error'] = 'Você não tem permissão para editar este perfil!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Prepara dados
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            $data['empresas_ids'] = $data['empresas_ids'] ?? [];
            
            // Atualiza perfil
            $this->perfilModel->update($id, $data);
            
            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            $response->redirect('/perfis-consolidacao');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/perfis-consolidacao/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->perfilModel = new PerfilConsolidacao();
            $perfil = $this->perfilModel->findById($id);
            
            // Verifica permissão
            if ($perfil['usuario_id'] != $_SESSION['usuario_id'] && $perfil['usuario_id'] !== null) {
                $_SESSION['error'] = 'Você não tem permissão para excluir este perfil!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            $this->perfilModel->delete($id);
            $_SESSION['success'] = 'Perfil excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir perfil: ' . $e->getMessage();
        }
        
        $response->redirect('/perfis-consolidacao');
    }
    
    /**
     * Aplica perfil de consolidação (define empresas na sessão)
     */
    public function aplicar(Request $request, Response $response, $id)
    {
        try {
            $this->perfilModel = new PerfilConsolidacao();
            $perfil = $this->perfilModel->findById($id);
            
            if (!$perfil) {
                $_SESSION['error'] = 'Perfil não encontrado!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Verifica permissão
            if (!$this->perfilModel->podeAcessar($id, $_SESSION['usuario_id'])) {
                $_SESSION['error'] = 'Você não tem permissão para aplicar este perfil!';
                $response->redirect('/perfis-consolidacao');
                return;
            }
            
            // Aplica perfil (salva empresas_ids na sessão)
            $empresasIds = $this->perfilModel->getEmpresasIds($perfil);
            $_SESSION['empresas_consolidacao'] = $empresasIds;
            $_SESSION['perfil_consolidacao_ativo'] = $perfil['nome'];
            
            $_SESSION['success'] = "Perfil \"{$perfil['nome']}\" aplicado com sucesso!";
            $response->redirect($request->get('redirect') ?? '/');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao aplicar perfil: ' . $e->getMessage();
            $response->redirect('/perfis-consolidacao');
        }
    }
    
    /**
     * Aplica consolidação customizada (sem perfil salvo)
     */
    public function aplicarCustom(Request $request, Response $response)
    {
        try {
            $empresasIds = $request->get('empresas_ids') ?? [];
            
            if (count($empresasIds) < 2) {
                $_SESSION['error'] = 'Selecione pelo menos 2 empresas para consolidação';
                $response->redirect($request->get('redirect') ?? '/');
                return;
            }
            
            $_SESSION['empresas_consolidacao'] = $empresasIds;
            unset($_SESSION['perfil_consolidacao_ativo']); // Remove perfil ativo se houver
            
            $_SESSION['success'] = count($empresasIds) . ' empresas selecionadas para consolidação!';
            $response->redirect($request->get('redirect') ?? '/');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao aplicar consolidação: ' . $e->getMessage();
            $response->redirect($request->get('redirect') ?? '/');
        }
    }
    
    /**
     * Remove consolidação ativa
     */
    public function limpar(Request $request, Response $response)
    {
        unset($_SESSION['empresas_consolidacao']);
        unset($_SESSION['perfil_consolidacao_ativo']);
        
        $_SESSION['success'] = 'Consolidação removida com sucesso!';
        $response->redirect($request->get('redirect') ?? '/');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        } elseif (strlen($data['nome']) < 3) {
            $errors['nome'] = 'O nome deve ter pelo menos 3 caracteres';
        }
        
        // Empresas
        if (empty($data['empresas_ids']) || !is_array($data['empresas_ids'])) {
            $errors['empresas_ids'] = 'Selecione pelo menos 2 empresas';
        } elseif (count($data['empresas_ids']) < 2) {
            $errors['empresas_ids'] = 'Selecione pelo menos 2 empresas para consolidação';
        }
        
        return $errors;
    }
}
