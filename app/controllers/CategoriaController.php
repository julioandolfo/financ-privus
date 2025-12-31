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
            $ajax = $request->get('ajax');
            
            // Retorna hierárquico ou flat baseado no parâmetro
            $viewMode = $request->get('view', 'flat'); // 'flat' ou 'tree'
            
            if ($viewMode === 'tree') {
                $categorias = $this->categoriaModel->findHierarchical($empresaId, $tipo);
            } else {
                $categorias = $this->categoriaModel->findAll($empresaId, $tipo);
            }
            
            // Se for requisição AJAX, retorna JSON com nome da empresa
            if ($ajax) {
                // Adiciona nome da empresa em cada categoria
                foreach ($categorias as &$categoria) {
                    if (!empty($categoria['empresa_id'])) {
                        $this->empresaModel = new Empresa();
                        $empresa = $this->empresaModel->findById($categoria['empresa_id']);
                        $categoria['empresa_nome'] = $empresa ? $empresa['nome_fantasia'] : 'N/A';
                    }
                }
                
                return $response->json([
                    'success' => true,
                    'categorias' => $categorias
                ]);
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
            
            $tipo = $request->get('tipo');
            
            $this->categoriaModel = new CategoriaFinanceira();
            
            // Busca todas as categorias ativas para mostrar como opções de categoria pai
            // O filtro por empresa e tipo será feito dinamicamente via AJAX quando o usuário selecionar
            $categoriasPai = $this->categoriaModel->findAll(null, $tipo);
            
            return $this->render('categorias/create', [
                'title' => 'Nova Categoria Financeira',
                'empresas' => $empresas,
                'categoriasPai' => $categoriasPai,
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
            
            // Pega array de empresas selecionadas
            $empresasIds = $data['empresa_ids'] ?? [];
            
            if (empty($empresasIds)) {
                $_SESSION['error'] = 'Selecione pelo menos uma empresa.';
                $this->session->set('old', $data);
                $response->redirect('/categorias/create');
                return;
            }
            
            // Cria categoria para cada empresa selecionada
            $this->categoriaModel = new CategoriaFinanceira();
            $criadas = 0;
            
            foreach ($empresasIds as $empresaId) {
                $dataCopia = $data;
                $dataCopia['empresa_id'] = $empresaId;
                
                // Remove empresa_ids do array de dados (não existe na tabela)
                unset($dataCopia['empresa_ids']);
                
                $id = $this->categoriaModel->create($dataCopia);
                if ($id) {
                    $criadas++;
                }
            }
            
            if ($criadas > 0) {
                $plural = $criadas > 1 ? 's' : '';
                $_SESSION['success'] = "Categoria criada com sucesso para {$criadas} empresa{$plural}!";
            } else {
                $_SESSION['error'] = 'Não foi possível criar as categorias.';
            }
            
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
        
        // Empresa (para criação, valida empresa_ids; para edição, valida empresa_id)
        if (isset($data['empresa_ids'])) {
            // Validação para criação (múltiplas empresas)
            if (empty($data['empresa_ids']) || !is_array($data['empresa_ids'])) {
                $errors['empresa_ids'] = 'Selecione pelo menos uma empresa';
            }
        } elseif (empty($data['empresa_id'])) {
            // Validação para edição (empresa única)
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

