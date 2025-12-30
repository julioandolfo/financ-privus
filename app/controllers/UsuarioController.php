<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Empresa;

class UsuarioController extends Controller
{
    private $usuarioModel;
    private $empresaModel;

    public function index()
    {
        try {
            $this->usuarioModel = new Usuario();
            $usuarios = $this->usuarioModel->findAll();
            
            $this->view('usuarios/index', [
                'title' => 'Gerenciar Usuários',
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar usuários: ' . $e->getMessage());
            $this->redirect('/');
        }
    }

    public function create()
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $this->view('usuarios/create', [
                'title' => 'Novo Usuário',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar formulário: ' . $e->getMessage());
            $this->redirect('/usuarios');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            // Validações
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $this->redirect('/usuarios/create');
                return;
            }
            
            // Hash da senha
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
            
            // Converte empresa_id vazio para NULL
            if (empty($data['empresa_id'])) {
                $data['empresa_id'] = null;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Cria usuário
            $this->usuarioModel = new Usuario();
            $id = $this->usuarioModel->create($data);
            
            $this->session->set('success', 'Usuário criado com sucesso!');
            $this->redirect('/usuarios');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao criar usuário: ' . $e->getMessage());
            $this->redirect('/usuarios/create');
        }
    }

    public function show($id)
    {
        try {
            $this->usuarioModel = new Usuario();
            $usuario = $this->usuarioModel->findById($id);
            
            if (!$usuario) {
                $this->session->set('error', 'Usuário não encontrado!');
                $this->redirect('/usuarios');
                return;
            }
            
            // Busca empresa se houver
            if ($usuario['empresa_id']) {
                $this->empresaModel = new Empresa();
                $usuario['empresa'] = $this->empresaModel->findById($usuario['empresa_id']);
            }
            
            $this->view('usuarios/show', [
                'title' => 'Detalhes do Usuário',
                'usuario' => $usuario
            ]);
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar usuário: ' . $e->getMessage());
            $this->redirect('/usuarios');
        }
    }

    public function edit($id)
    {
        try {
            $this->usuarioModel = new Usuario();
            $usuario = $this->usuarioModel->findById($id);
            
            if (!$usuario) {
                $this->session->set('error', 'Usuário não encontrado!');
                $this->redirect('/usuarios');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $this->view('usuarios/edit', [
                'title' => 'Editar Usuário',
                'usuario' => $usuario,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar usuário: ' . $e->getMessage());
            $this->redirect('/usuarios');
        }
    }

    public function update($id)
    {
        try {
            $data = $this->request->all();
            
            // Validações
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $this->redirect("/usuarios/edit/{$id}");
                return;
            }
            
            // Remove senha se estiver vazia (não alterar)
            if (empty($data['senha'])) {
                unset($data['senha']);
            } else {
                $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }
            
            // Converte empresa_id vazio para NULL
            if (empty($data['empresa_id'])) {
                $data['empresa_id'] = null;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Atualiza usuário
            $this->usuarioModel = new Usuario();
            $this->usuarioModel->update($id, $data);
            
            $this->session->set('success', 'Usuário atualizado com sucesso!');
            $this->redirect('/usuarios');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
            $this->redirect("/usuarios/edit/{$id}");
        }
    }

    public function destroy($id)
    {
        try {
            // Não permite excluir o próprio usuário
            if ($id == $this->session->get('user_id')) {
                $this->session->set('error', 'Você não pode excluir seu próprio usuário!');
                $this->redirect('/usuarios');
                return;
            }
            
            $this->usuarioModel = new Usuario();
            $this->usuarioModel->delete($id);
            
            $this->session->set('success', 'Usuário excluído com sucesso!');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao excluir usuário: ' . $e->getMessage());
        }
        
        $this->redirect('/usuarios');
    }

    private function validate($data, $id = null)
    {
        $errors = [];
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        // Email
        if (empty($data['email'])) {
            $errors['email'] = 'O email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        } else {
            // Verifica se email já existe
            $this->usuarioModel = new Usuario();
            $existing = $this->usuarioModel->findByEmail($data['email']);
            
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['email'] = 'Este email já está cadastrado';
            }
        }
        
        // Senha (obrigatória apenas na criação)
        if (!$id && empty($data['senha'])) {
            $errors['senha'] = 'A senha é obrigatória';
        } elseif (!empty($data['senha']) && strlen($data['senha']) < 6) {
            $errors['senha'] = 'A senha deve ter no mínimo 6 caracteres';
        }
        
        return $errors;
    }
}

