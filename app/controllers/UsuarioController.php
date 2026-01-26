<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Permissao;

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
            $permissaoModel = new Permissao();
            
            return $this->render('usuarios/create', [
                'title' => 'Novo Usuário',
                'empresas' => $empresas,
                'modulos' => Permissao::MODULOS,
                'acoes' => Permissao::ACOES
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
            $modelErrors = $this->usuarioModel->validate($data);
            $errors = [];
            
            // Converte array de erros do model para formato de chave-valor
            if (!empty($modelErrors)) {
                foreach ($modelErrors as $error) {
                    if (strpos($error, 'Nome') !== false || strpos($error, 'nome') !== false) {
                        $errors['nome'] = $error;
                    } elseif (strpos($error, 'Email') !== false || strpos($error, 'email') !== false) {
                        $errors['email'] = $error;
                    } elseif (strpos($error, 'Senha') !== false || strpos($error, 'senha') !== false) {
                        $errors['senha'] = $error;
                    } else {
                        $errors['geral'] = $error;
                    }
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect('/usuarios/create');
                return;
            }
            
            // Cria usuário (o Model já faz hash da senha)
            $id = $this->usuarioModel->create($data);
            
            // SEMPRE processar permissões (mesmo se vazio)
            $permissaoModel = new Permissao();
            $permissoes = [];
            
            if (!empty($data['permissoes']) && is_array($data['permissoes'])) {
                foreach ($data['permissoes'] as $permissaoStr) {
                    if (strpos($permissaoStr, '_') !== false) {
                        list($modulo, $acao) = explode('_', $permissaoStr, 2);
                        $permissoes[] = [
                            'modulo' => $modulo,
                            'acao' => $acao
                        ];
                    }
                }
            }
            
            // Determinar empresa_id para as permissões
            $empresaId = null;
            if (!empty($data['empresa_id']) && is_numeric($data['empresa_id']) && (int)$data['empresa_id'] > 0) {
                $empresaId = (int)$data['empresa_id'];
            }
            
            // Salvar permissões
            if (!empty($permissoes)) {
                $permissaoModel->saveBatch($id, $permissoes, $empresaId);
            }
            
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
            
            // Busca permissões do usuário
            $permissaoModel = new Permissao();
            $permissoesFormatadas = $permissaoModel->getFormattedPermissions($id, $usuario['empresa_id']);
            
            return $this->render('usuarios/show', [
                'title' => 'Detalhes do Usuário',
                'usuario' => $usuario,
                'permissoes' => $permissoesFormatadas,
                'modulos' => Permissao::MODULOS,
                'acoes' => Permissao::ACOES
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
            $permissaoModel = new Permissao();
            
            // Buscar permissões - usar empresa_id do usuário ou null para buscar todas
            $empresaIdParaPermissoes = !empty($usuario['empresa_id']) ? $usuario['empresa_id'] : null;
            $permissoesFormatadas = $permissaoModel->getFormattedPermissions($id, $empresaIdParaPermissoes);
            
            return $this->render('usuarios/edit', [
                'title' => 'Editar Usuário',
                'usuario' => $usuario,
                'empresas' => $empresas,
                'modulos' => Permissao::MODULOS,
                'acoes' => Permissao::ACOES,
                'permissoes' => $permissoesFormatadas
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
            $modelErrors = $this->usuarioModel->validate($data, $id);
            $errors = [];
            
            // Converte array de erros do model para formato de chave-valor
            if (!empty($modelErrors)) {
                foreach ($modelErrors as $error) {
                    if (strpos($error, 'Nome') !== false || strpos($error, 'nome') !== false) {
                        $errors['nome'] = $error;
                    } elseif (strpos($error, 'Email') !== false || strpos($error, 'email') !== false) {
                        $errors['email'] = $error;
                    } elseif (strpos($error, 'Senha') !== false || strpos($error, 'senha') !== false) {
                        $errors['senha'] = $error;
                    } else {
                        $errors['geral'] = $error;
                    }
                }
            }
            
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
            
            // SEMPRE processar permissões (mesmo se vazio, para permitir remover todas)
            $permissaoModel = new Permissao();
            $permissoes = [];
            
            // Processar permissões se existirem
            if (!empty($data['permissoes']) && is_array($data['permissoes'])) {
                foreach ($data['permissoes'] as $permissaoStr) {
                    // Validar formato da permissão
                    if (strpos($permissaoStr, '_') !== false) {
                        list($modulo, $acao) = explode('_', $permissaoStr, 2);
                        $permissoes[] = [
                            'modulo' => $modulo,
                            'acao' => $acao
                        ];
                    }
                }
            }
            
            // Determinar empresa_id para as permissões
            $empresaId = null;
            if (!empty($data['empresa_id']) && is_numeric($data['empresa_id']) && (int)$data['empresa_id'] > 0) {
                $empresaId = (int)$data['empresa_id'];
            } elseif (!empty($usuario['empresa_id']) && is_numeric($usuario['empresa_id']) && (int)$usuario['empresa_id'] > 0) {
                $empresaId = (int)$usuario['empresa_id'];
            }
            
            // Salvar permissões (mesmo se vazio, isso vai limpar as permissões existentes)
            $permissaoModel->saveBatch($id, $permissoes, $empresaId);
            
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
    
    /**
     * Exibe página "Minha Conta" (perfil do usuário logado)
     */
    public function minhaConta(Request $request, Response $response)
    {
        try {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                $_SESSION['error'] = 'Você precisa estar logado para acessar sua conta!';
                $response->redirect('/login');
                return;
            }
            
            $usuario = $this->usuarioModel->findById($usuarioId);
            
            if (!$usuario) {
                $_SESSION['error'] = 'Usuário não encontrado!';
                $response->redirect('/');
                return;
            }
            
            // Busca empresa se houver
            if ($usuario['empresa_id']) {
                $usuario['empresa'] = $this->empresaModel->findById($usuario['empresa_id']);
            }
            
            return $this->render('usuarios/minha-conta', [
                'title' => 'Minha Conta',
                'usuario' => $usuario
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar sua conta: ' . $e->getMessage();
            $response->redirect('/');
        }
    }
    
    /**
     * Atualiza perfil do usuário logado
     */
    public function atualizarMinhaConta(Request $request, Response $response)
    {
        try {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                $_SESSION['error'] = 'Você precisa estar logado!';
                $response->redirect('/login');
                return;
            }
            
            $data = $request->all();
            
            // Processa upload de avatar se houver
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarPath = $this->uploadAvatar($_FILES['avatar'], $usuarioId);
                if ($avatarPath) {
                    $data['avatar'] = $avatarPath;
                }
            }
            
            // Validação de senha se fornecida
            $errors = [];
            if (!empty($data['senha'])) {
                if (strlen($data['senha']) < 8) {
                    $errors['senha'] = 'Senha deve ter no mínimo 8 caracteres';
                }
                if ($data['senha'] !== ($data['senha_confirm'] ?? '')) {
                    $errors['senha_confirm'] = 'As senhas não coincidem';
                }
            }
            
            // Validações usando o Model (nome e email)
            $modelErrors = $this->usuarioModel->validate($data, $usuarioId);
            if (!empty($modelErrors)) {
                // Converte array de erros do model para formato de chave-valor
                foreach ($modelErrors as $error) {
                    if (strpos($error, 'Nome') !== false) {
                        $errors['nome'] = $error;
                    } elseif (strpos($error, 'Email') !== false) {
                        $errors['email'] = $error;
                    }
                }
            }
            
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect('/minha-conta');
                return;
            }
            
            // Remove senha se estiver vazia (não alterar)
            if (empty($data['senha'])) {
                unset($data['senha']);
            }
            unset($data['senha_confirm']);
            
            // Remove empresa_id e ativo - usuário não pode alterar isso em sua própria conta
            unset($data['empresa_id']);
            unset($data['ativo']);
            
            // Atualiza usuário
            $this->usuarioModel->update($usuarioId, $data);
            
            // Atualiza dados na sessão
            $_SESSION['usuario_nome'] = $data['nome'];
            $_SESSION['usuario_email'] = $data['email'];
            if (isset($data['avatar'])) {
                $_SESSION['usuario_avatar'] = $data['avatar'];
            }
            
            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            $response->redirect('/minha-conta');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/minha-conta');
        }
    }
    
    /**
     * Faz upload do avatar do usuário
     */
    private function uploadAvatar($file, $usuarioId)
    {
        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
        
        // Cria diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Valida tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception('Tipo de arquivo não permitido. Use apenas imagens (JPG, PNG, GIF ou WEBP).');
        }
        
        // Valida tamanho (máximo 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception('Arquivo muito grande. Tamanho máximo: 2MB.');
        }
        
        // Gera nome único para o arquivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'avatar_' . $usuarioId . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Move arquivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Erro ao fazer upload do arquivo.');
        }
        
        // Remove avatar antigo se existir
        $usuario = $this->usuarioModel->findById($usuarioId);
        if (!empty($usuario['avatar'])) {
            $oldAvatarPath = __DIR__ . '/../../public/' . ltrim($usuario['avatar'], '/');
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }
        
        // Retorna caminho relativo para salvar no banco
        return '/uploads/avatars/' . $fileName;
    }
}

