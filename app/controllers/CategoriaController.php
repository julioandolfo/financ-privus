<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoriaFinanceira;
use App\Models\Empresa;

class CategoriaController extends Controller
{
    private $categoriaModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->categoriaModel = new CategoriaFinanceira();
            $empresaId = $request->get('empresa_id');
            $tipo = $request->get('tipo');
            
            // Retorna hierárquico ou flat baseado no parâmetro
            $viewMode = $request->get('view', 'flat'); // 'flat' ou 'tree'
            
            if ($viewMode === 'tree') {
                $categorias = $this->categoriaModel->findHierarchical($empresaId, $tipo);
            } else {
                $categorias = $this->categoriaModel->findAll($empresaId, $tipo);
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('categorias/index', [
                'title' => 'Gerenciar Categorias Financeiras',
                'categorias' => $categorias,
                'empresas' => $empresas,
                'viewMode' => $viewMode,
                'filters' => [
                    'empresa_id' => $empresaId,
                    'tipo' => $tipo
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar categorias: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $empresaId = $request->get('empresa_id');
            $tipo = $request->get('tipo');
            
            $this->categoriaModel = new CategoriaFinanceira();
            $categoriasPai = [];
            
            if ($empresaId) {
                $categoriasPai = $this->categoriaModel->getAvailableParents($empresaId, null, $tipo);
            }
            
            return $this->render('categorias/create', [
                'title' => 'Nova Categoria Financeira',
                'empresas' => $empresas,
                'categoriasPai' => $categoriasPai,
                'defaultEmpresaId' => $empresaId,
                'defaultTipo' => $tipo
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/categorias');
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
                $response->redirect('/categorias/create');
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Converte categoria_pai_id vazio em NULL
            if (empty($data['categoria_pai_id'])) {
                $data['categoria_pai_id'] = null;
            }
            
            // Cria categoria
            $this->categoriaModel = new CategoriaFinanceira();
            $id = $this->categoriaModel->create($data);
            
            $_SESSION['success'] = 'Categoria criada com sucesso!';
            $response->redirect('/categorias');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar categoria: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/categorias/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->categoriaModel = new CategoriaFinanceira();
            $categoria = $this->categoriaModel->findById($id);
            
            if (!$categoria) {
                $_SESSION['error'] = 'Categoria não encontrada!';
                $response->redirect('/categorias');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $categoria['empresa'] = $this->empresaModel->findById($categoria['empresa_id']);
            
            // Busca categoria pai se houver
            if ($categoria['categoria_pai_id']) {
                $categoria['categoria_pai'] = $this->categoriaModel->findById($categoria['categoria_pai_id']);
            }
            
            // Busca categorias filhas
            $categoria['filhas'] = $this->categoriaModel->findChildren($id);
            
            // Busca caminho completo
            $categoria['path'] = $this->categoriaModel->getPath($id);
            
            return $this->render('categorias/show', [
                'title' => 'Detalhes da Categoria',
                'categoria' => $categoria
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar categoria: ' . $e->getMessage();
            $response->redirect('/categorias');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->categoriaModel = new CategoriaFinanceira();
            $categoria = $this->categoriaModel->findById($id);
            
            if (!$categoria) {
                $_SESSION['error'] = 'Categoria não encontrada!';
                $response->redirect('/categorias');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            // Busca categorias disponíveis para serem pais
            $categoriasPai = $this->categoriaModel->getAvailableParents(
                $categoria['empresa_id'], 
                $id, 
                $categoria['tipo']
            );
            
            return $this->render('categorias/edit', [
                'title' => 'Editar Categoria Financeira',
                'categoria' => $categoria,
                'empresas' => $empresas,
                'categoriasPai' => $categoriasPai
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar categoria: ' . $e->getMessage();
            $response->redirect('/categorias');
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
                $response->redirect("/categorias/edit/{$id}");
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Converte categoria_pai_id vazio em NULL
            if (empty($data['categoria_pai_id'])) {
                $data['categoria_pai_id'] = null;
            }
            
            // Atualiza categoria
            $this->categoriaModel = new CategoriaFinanceira();
            $this->categoriaModel->update($id, $data);
            
            $_SESSION['success'] = 'Categoria atualizada com sucesso!';
            $response->redirect('/categorias');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar categoria: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/categorias/edit/{$id}");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->categoriaModel = new CategoriaFinanceira();
            
            // Verifica se tem categorias filhas
            $children = $this->categoriaModel->findChildren($id);
            if (!empty($children)) {
                $_SESSION['error'] = 'Não é possível excluir uma categoria que possui subcategorias!';
                $response->redirect('/categorias');
                return;
            }
            
            $this->categoriaModel->delete($id);
            $_SESSION['success'] = 'Categoria excluída com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir categoria: ' . $e->getMessage();
        }
        
        $response->redirect('/categorias');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Código
        if (empty($data['codigo'])) {
            $errors['codigo'] = 'O código é obrigatório';
        } else {
            $this->categoriaModel = new CategoriaFinanceira();
            $existing = $this->categoriaModel->findByCodigo($data['codigo'], $data['empresa_id']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['codigo'] = 'Este código já está em uso para esta empresa';
            }
        }
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        // Tipo
        if (empty($data['tipo']) || !in_array($data['tipo'], ['receita', 'despesa'])) {
            $errors['tipo'] = 'O tipo é obrigatório (receita ou despesa)';
        }
        
        // Validação de categoria pai (evita loops)
        if (!empty($data['categoria_pai_id'])) {
            $this->categoriaModel = new CategoriaFinanceira();
            if (!$this->categoriaModel->canBeParent($id ?? 0, $data['categoria_pai_id'])) {
                $errors['categoria_pai_id'] = 'Não é possível definir esta categoria como pai (criaria um loop hierárquico)';
            }
        }
        
        return $errors;
    }
}

