<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\CategoriaProduto;
use App\Models\ProdutoFoto;
use App\Models\ProdutoVariacao;

class ProdutoController extends Controller
{
    private $produtoModel;
    private $empresaModel;
    private $categoriaModel;
    private $fotoModel;
    private $variacaoModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->produtoModel = new Produto();
        $this->empresaModel = new Empresa();
        $this->categoriaModel = new CategoriaProduto();
        $this->fotoModel = new ProdutoFoto();
        $this->variacaoModel = new ProdutoVariacao();
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
    
    /**
     * Upload de foto do produto
     */
    public function uploadFoto(Request $request, Response $response, $produtoId)
    {
        $produto = $this->produtoModel->findById($produtoId);
        
        if (!$produto) {
            return $response->json(['success' => false, 'error' => 'Produto não encontrado'], 404);
        }
        
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return $response->json(['success' => false, 'error' => 'Erro no upload do arquivo'], 400);
        }
        
        $file = $_FILES['foto'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return $response->json(['success' => false, 'error' => 'Tipo de arquivo não permitido. Use: JPEG, PNG, GIF ou WebP'], 400);
        }
        
        // Verifica tamanho (máximo 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return $response->json(['success' => false, 'error' => 'Arquivo muito grande. Tamanho máximo: 5MB'], 400);
        }
        
        // Cria diretório se não existir
        $uploadDir = __DIR__ . '/../../storage/uploads/produtos/' . $produtoId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gera nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('foto_') . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return $response->json(['success' => false, 'error' => 'Erro ao salvar arquivo'], 500);
        }
        
        // Salva no banco
        $fotoData = [
            'produto_id' => $produtoId,
            'arquivo' => $filename,
            'caminho' => '/storage/uploads/produtos/' . $produtoId . '/' . $filename,
            'tamanho' => $file['size'],
            'tipo' => $file['type'],
            'principal' => $request->get('principal', 0),
            'ordem' => $this->fotoModel->count($produtoId)
        ];
        
        $fotoId = $this->fotoModel->create($fotoData);
        
        if ($fotoId) {
            // Se marcou como principal, atualiza as outras
            if ($fotoData['principal']) {
                $this->fotoModel->setPrincipal($fotoId, $produtoId);
            }
            
            return $response->json([
                'success' => true, 
                'foto' => array_merge($fotoData, ['id' => $fotoId])
            ]);
        }
        
        return $response->json(['success' => false, 'error' => 'Erro ao salvar foto no banco'], 500);
    }
    
    /**
     * Excluir foto do produto
     */
    public function deleteFoto(Request $request, Response $response, $fotoId)
    {
        $foto = $this->fotoModel->findById($fotoId);
        
        if (!$foto) {
            return $response->json(['success' => false, 'error' => 'Foto não encontrada'], 404);
        }
        
        // Remove arquivo físico
        $fullPath = __DIR__ . '/../../' . $foto['caminho'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Remove do banco
        $result = $this->fotoModel->delete($fotoId);
        
        return $response->json(['success' => $result]);
    }
    
    /**
     * Definir foto como principal
     */
    public function setFotoPrincipal(Request $request, Response $response, $fotoId)
    {
        $foto = $this->fotoModel->findById($fotoId);
        
        if (!$foto) {
            return $response->json(['success' => false, 'error' => 'Foto não encontrada'], 404);
        }
        
        $result = $this->fotoModel->setPrincipal($fotoId, $foto['produto_id']);
        
        return $response->json(['success' => $result]);
    }
    
    /**
     * Adicionar variação ao produto
     */
    public function addVariacao(Request $request, Response $response, $produtoId)
    {
        $data = $request->all();
        $data['produto_id'] = $produtoId;
        
        // Converte atributos de array para JSON
        if (isset($data['atributos']) && is_array($data['atributos'])) {
            $data['atributos'] = $data['atributos'];
        } else {
            $data['atributos'] = [];
        }
        
        $variacaoId = $this->variacaoModel->create($data);
        
        if ($variacaoId) {
            $this->session->set('success', 'Variação adicionada com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao adicionar variação.');
        }
        
        return $response->redirect('/produtos/' . $produtoId . '/edit#variacoes');
    }
    
    /**
     * Atualizar variação
     */
    public function updateVariacao(Request $request, Response $response, $variacaoId)
    {
        $data = $request->all();
        $variacao = $this->variacaoModel->findById($variacaoId);
        
        if (!$variacao) {
            $this->session->set('error', 'Variação não encontrada.');
            return $response->redirect('/produtos');
        }
        
        // Converte atributos de array para JSON
        if (isset($data['atributos']) && is_array($data['atributos'])) {
            $data['atributos'] = $data['atributos'];
        } else {
            $data['atributos'] = [];
        }
        
        $result = $this->variacaoModel->update($variacaoId, $data);
        
        if ($result) {
            $this->session->set('success', 'Variação atualizada com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao atualizar variação.');
        }
        
        return $response->redirect('/produtos/' . $variacao['produto_id'] . '/edit#variacoes');
    }
    
    /**
     * Excluir variação
     */
    public function deleteVariacao(Request $request, Response $response, $variacaoId)
    {
        $variacao = $this->variacaoModel->findById($variacaoId);
        
        if (!$variacao) {
            $this->session->set('error', 'Variação não encontrada.');
            return $response->redirect('/produtos');
        }
        
        $result = $this->variacaoModel->delete($variacaoId);
        
        if ($result) {
            $this->session->set('success', 'Variação excluída com sucesso!');
        } else {
            $this->session->set('error', 'Erro ao excluir variação.');
        }
        
        return $response->redirect('/produtos/' . $variacao['produto_id'] . '/edit#variacoes');
    }
    
    /**
     * Gerar código de barras
     */
    public function gerarCodigoBarras(Request $request, Response $response)
    {
        // Gera um código EAN-13 simples
        $codigo = '789' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        
        return $response->json(['success' => true, 'codigo' => $codigo]);
    }
}
