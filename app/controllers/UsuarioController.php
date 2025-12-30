<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Usuario;
use App\Models\Empresa;

class UsuarioController extends Controller
{
    protected $usuarioModel;
    protected $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
    }

    public function index(Request $request, Response $response)
    {
        try {
            $filters = [
                'ativo' => $request->get('ativo', ''),
                'empresa_id' => $request->get('empresa_id', ''),
                'search' => $request->get('search', '')
            ];
            
            $usuarios = $this->usuarioModel->findAll($filters);
            
            return $this->render('usuarios/index', [
                'title' => 'Gerenciar Usuários',
                'usuarios' => $usuarios,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar usuários: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('usuarios/create', [
                'title' => 'Novo Usuário',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/usuarios');
        }
    }

    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->all();
            
            // Validações usando o Model
            $errors = $this->usuarioModel->validate($data);
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect('/usuarios/create');
                return;
            }
            
            // Cria usuário (o Model já faz hash da senha)
            $id = $this->usuarioModel->create($data);
            
            $_SESSION['success'] = 'Usuário criado com sucesso!';
            $response->redirect('/usuarios');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar usuário: ' . $e->getMessage();
            $response->redirect('/usuarios/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $usuario = $this->usuarioModel->findById($id);
            
            if (!$usuario) {
                $response->status(404);
                return $this->render('errors/404', [
                    'title' => 'Usuário não encontrado'
                ]);
            }
            
            // Busca empresa se houver
            if ($usuario['empresa_id']) {
                $usuario['empresa'] = $this->empresaModel->findById($usuario['empresa_id']);
            }
            
            return $this->render('usuarios/show', [
                'title' => 'Detalhes do Usuário',
                'usuario' => $usuario
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar usuário: ' . $e->getMessage();
            $response->redirect('/usuarios');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $usuario = $this->usuarioModel->findById($id);
            
            if (!$usuario) {
                $response->status(404);
                return $this->render('errors/404', [
                    'title' => 'Usuário não encontrado'
                ]);
            }
            
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('usuarios/edit', [
                'title' => 'Editar Usuário',
                'usuario' => $usuario,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar usuário: ' . $e->getMessage();
            $response->redirect('/usuarios');
        }
    }

    public function update(Request $request, Response $response, $id)
    {
        try {
            $usuario = $this->usuarioModel->findById($id);
            
            if (!$usuario) {
                $response->status(404);
                return $this->render('errors/404', [
                    'title' => 'Usuário não encontrado'
                ]);
            }
            
            $data = $request->all();
            
            // Validações usando o Model
            $errors = $this->usuarioModel->validate($data, $id);
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect("/usuarios/{$id}/edit");
                return;
            }
            
            // Remove senha se estiver vazia (não alterar)
            if (empty($data['senha'])) {
                unset($data['senha']);
            }
            
            // Atualiza usuário
            $this->usuarioModel->update($id, $data);
            
            $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            $response->redirect('/usuarios');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            $response->redirect("/usuarios/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            // Não permite excluir o próprio usuário
            if ($id == ($_SESSION['usuario_id'] ?? null)) {
                $_SESSION['error'] = 'Você não pode excluir seu próprio usuário!';
                $response->redirect('/usuarios');
                return;
            }
            
            $this->usuarioModel->delete($id);
            
            $_SESSION['success'] = 'Usuário excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir usuário: ' . $e->getMessage();
        }
        
        $response->redirect('/usuarios');
    }
}

