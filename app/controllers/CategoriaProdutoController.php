<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoriaProduto;

class CategoriaProdutoController extends Controller
{
    private $categoriaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->categoriaModel = new CategoriaProduto();
    }
    
    /**
     * Lista todas as categorias
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $this->session->get('empresa_id');
        
        // Opção de visualização (tree ou flat)
        $view = $request->get('view', 'tree');
        
        if ($view === 'tree') {
            $categorias = $this->categoriaModel->buildTree($empresaId);
        } else {
            $categorias = $this->categoriaModel->getFlatList($empresaId);
        }
        
        return $this->render('categorias_produtos/index', [
            'categorias' => $categorias,
            'view' => $view
        ]);
    }
    
    /**
     * Formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        $empresaId = $this->session->get('empresa_id');
        $categorias = $this->categoriaModel->getFlatList($empresaId);
        
        return $this->render('categorias_produtos/create', [
            'categorias' => $categorias
        ]);
    }
    
    /**
     * Salvar nova categoria
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $empresaId = $this->session->get('empresa_id');
        
        // Validação
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/categorias-produtos/create');
        }
        
        // Adiciona empresa_id
        $data['empresa_id'] = $empresaId;
        
        // Converte categoria_pai vazio em null
        if (empty($data['categoria_pai_id'])) {
            $data['categoria_pai_id'] = null;
        }
        
        $id = $this->categoriaModel->create($data);
        
        if ($id) {
            $this->session->set('success', 'Categoria criada com sucesso!');
            return $response->redirect('/categorias-produtos');
        }
        
        $this->session->set('error', 'Erro ao criar categoria.');
        return $response->redirect('/categorias-produtos/create');
    }
    
    /**
     * Formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $categoria = $this->categoriaModel->findById($id);
        
        if (!$categoria) {
            $this->session->set('error', 'Categoria não encontrada.');
            return $response->redirect('/categorias-produtos');
        }
        
        $empresaId = $this->session->get('empresa_id');
        $categorias = $this->categoriaModel->getFlatList($empresaId);
        
        // Remove a categoria atual e seus descendentes da lista (para evitar loops)
        $categorias = array_filter($categorias, function($cat) use ($id) {
            return $cat['id'] != $id && !$this->isDescendant($id, $cat['id']);
        });
        
        return $this->render('categorias_produtos/edit', [
            'categoria' => $categoria,
            'categorias' => $categorias
        ]);
    }
    
    /**
     * Atualizar categoria
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        
        // Validação
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/categorias-produtos/' . $id . '/edit');
        }
        
        // Converte categoria_pai vazio em null
        if (empty($data['categoria_pai_id'])) {
            $data['categoria_pai_id'] = null;
        }
        
        // Verifica se pode ser pai (evita loops)
        if ($data['categoria_pai_id'] && !$this->categoriaModel->canBeParent($id, $data['categoria_pai_id'])) {
            $this->session->set('error', 'Categoria pai inválida. Não é possível criar loops hierárquicos.');
            return $response->redirect('/categorias-produtos/' . $id . '/edit');
        }
        
        $result = $this->categoriaModel->update($id, $data);
        
        if ($result) {
            $this->session->set('success', 'Categoria atualizada com sucesso!');
            return $response->redirect('/categorias-produtos');
        }
        
        $this->session->set('error', 'Erro ao atualizar categoria.');
        return $response->redirect('/categorias-produtos/' . $id . '/edit');
    }
    
    /**
     * Excluir categoria
     */
    public function destroy(Request $request, Response $response, $id)
    {
        // Verifica se tem produtos vinculados
        $totalProdutos = $this->categoriaModel->countProdutos($id);
        if ($totalProdutos > 0) {
            $this->session->set('error', "Não é possível excluir. Esta categoria possui {$totalProdutos} produto(s) vinculado(s).");
            return $response->redirect('/categorias-produtos');
        }
        
        // Verifica se tem subcategorias
        $descendentes = $this->categoriaModel->getDescendants($id);
        if (!empty($descendentes)) {
            $this->session->set('error', 'Não é possível excluir. Esta categoria possui subcategorias.');
            return $response->redirect('/categorias-produtos');
        }
        
        $result = $this->categoriaModel->delete($id);
        
        if ($result) {
            $this->session->set('success', 'Categoria excluída com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao excluir categoria.');
        }
        
        return $response->redirect('/categorias-produtos');
    }
    
    /**
     * Validação
     */
    protected function validate($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório.';
        }
        
        if (!empty($data['cor']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['cor'])) {
            $errors['cor'] = 'Formato de cor inválido. Use formato hexadecimal (#RRGGBB).';
        }
        
        return $errors;
    }
    
    /**
     * Verifica se uma categoria é descendente de outra
     */
    private function isDescendant($parentId, $childId)
    {
        $descendentes = $this->categoriaModel->getDescendants($parentId);
        foreach ($descendentes as $desc) {
            if ($desc['id'] == $childId) {
                return true;
            }
        }
        return false;
    }
}
