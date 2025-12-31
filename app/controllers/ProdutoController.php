<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Produto;
use App\Models\Empresa;

class ProdutoController extends Controller
{
    private $produtoModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->produtoModel = new Produto();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Lista todos os produtos
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $this->session->get('empresa_id');
        
        $filters = [
            'busca' => $request->get('busca')
        ];
        
        $produtos = $this->produtoModel->findAll($empresaId, $filters);
        $estatisticas = $this->produtoModel->getEstatisticas($empresaId);
        
        return $this->render('produtos/index', [
            'title' => 'Produtos',
            'produtos' => $produtos,
            'estatisticas' => $estatisticas,
            'filters' => $filters
        ]);
    }
    
    /**
     * Exibe formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        return $this->render('produtos/create', [
            'title' => 'Novo Produto'
        ]);
    }
    
    /**
     * Salva novo produto
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $empresaId = $this->session->get('empresa_id');
        
        // Validar
        $errors = $this->validate($data, null, $empresaId);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/produtos/create');
        }
        
        // Adicionar empresa_id
        $data['empresa_id'] = $empresaId;
        
        // Criar produto
        $produtoId = $this->produtoModel->create($data);
        
        if ($produtoId) {
            $this->session->set('success', 'Produto cadastrado com sucesso!');
            return $response->redirect('/produtos/' . $produtoId);
        }
        
        $this->session->set('error', 'Erro ao cadastrar produto.');
        return $response->redirect('/produtos/create');
    }
    
    /**
     * Exibe detalhes do produto
     */
    public function show(Request $request, Response $response, $id)
    {
        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            $this->session->set('error', 'Produto não encontrado.');
            return $response->redirect('/produtos');
        }
        
        // Calcular margem
        $margemLucro = $this->produtoModel->calcularMargemLucro(
            $produto['custo_unitario'],
            $produto['preco_venda']
        );
        
        return $this->render('produtos/show', [
            'title' => 'Detalhes do Produto',
            'produto' => $produto,
            'margem_lucro' => $margemLucro
        ]);
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            $this->session->set('error', 'Produto não encontrado.');
            return $response->redirect('/produtos');
        }
        
        return $this->render('produtos/edit', [
            'title' => 'Editar Produto',
            'produto' => $produto
        ]);
    }
    
    /**
     * Atualiza produto
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        $empresaId = $this->session->get('empresa_id');
        
        // Buscar produto
        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            $this->session->set('error', 'Produto não encontrado.');
            return $response->redirect('/produtos');
        }
        
        // Validar
        $errors = $this->validate($data, $id, $empresaId);
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            return $response->redirect('/produtos/' . $id . '/edit');
        }
        
        // Atualizar produto
        $success = $this->produtoModel->update($id, $data);
        
        if ($success) {
            $this->session->set('success', 'Produto atualizado com sucesso!');
            return $response->redirect('/produtos/' . $id);
        }
        
        $this->session->set('error', 'Erro ao atualizar produto.');
        return $response->redirect('/produtos/' . $id . '/edit');
    }
    
    /**
     * Deleta produto
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $success = $this->produtoModel->delete($id);
        
        if ($success) {
            $this->session->set('success', 'Produto excluído com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao excluir produto.');
        }
        
        return $response->redirect('/produtos');
    }
    
    /**
     * Validação
     */
    protected function validate($data, $id = null, $empresaId = null)
    {
        $errors = [];
        
        // Código
        if (empty($data['codigo'])) {
            $errors['codigo'] = 'Código é obrigatório.';
        } elseif ($empresaId) {
            // Verificar se código já existe
            $existente = $this->produtoModel->findByCodigo($data['codigo'], $empresaId, $id);
            if ($existente) {
                $errors['codigo'] = 'Código já cadastrado para esta empresa.';
            }
        }
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório.';
        } elseif (strlen($data['nome']) < 3) {
            $errors['nome'] = 'Nome deve ter no mínimo 3 caracteres.';
        }
        
        // Custo Unitário
        if (isset($data['custo_unitario']) && !is_numeric($data['custo_unitario'])) {
            $errors['custo_unitario'] = 'Custo unitário deve ser um número válido.';
        } elseif (isset($data['custo_unitario']) && $data['custo_unitario'] < 0) {
            $errors['custo_unitario'] = 'Custo unitário não pode ser negativo.';
        }
        
        // Preço de Venda
        if (isset($data['preco_venda']) && !is_numeric($data['preco_venda'])) {
            $errors['preco_venda'] = 'Preço de venda deve ser um número válido.';
        } elseif (isset($data['preco_venda']) && $data['preco_venda'] < 0) {
            $errors['preco_venda'] = 'Preço de venda não pode ser negativo.';
        }
        
        // Unidade de Medida
        if (empty($data['unidade_medida'])) {
            $errors['unidade_medida'] = 'Unidade de medida é obrigatória.';
        }
        
        return $errors;
    }
}
