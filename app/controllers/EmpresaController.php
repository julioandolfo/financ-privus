<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Empresa;

/**
 * Controller para gerenciar empresas
 */
class EmpresaController extends Controller
{
    protected $empresa;
    
    public function __construct()
    {
        parent::__construct();
        $this->empresa = new Empresa();
    }
    
    /**
     * Lista todas as empresas
     */
    public function index(Request $request, Response $response)
    {
        $filters = [
            'ativo' => $request->get('ativo', ''),
            'grupo_empresarial_id' => $request->get('grupo_empresarial_id', ''),
            'search' => $request->get('search', '')
        ];
        
        $empresas = $this->empresa->findAll($filters);
        
        return $this->render('empresas/index', [
            'title' => 'Empresas',
            'empresas' => $empresas,
            'filters' => $filters
        ]);
    }
    
    /**
     * Exibe formulário de criação
     */
    public function create(Request $request, Response $response)
    {
        return $this->render('empresas/create', [
            'title' => 'Nova Empresa'
        ]);
    }
    
    /**
     * Salva uma nova empresa
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        
        // Processa campos extras para configuracoes JSON
        $configuracoes = [];
        if (!empty($data['telefone'])) $configuracoes['telefone'] = $data['telefone'];
        if (!empty($data['email'])) $configuracoes['email'] = $data['email'];
        if (!empty($data['site'])) $configuracoes['site'] = $data['site'];
        if (!empty($data['inscricao_estadual'])) $configuracoes['inscricao_estadual'] = $data['inscricao_estadual'];
        if (!empty($data['inscricao_municipal'])) $configuracoes['inscricao_municipal'] = $data['inscricao_municipal'];
        if (!empty($data['cep'])) $configuracoes['endereco']['cep'] = $data['cep'];
        if (!empty($data['logradouro'])) $configuracoes['endereco']['logradouro'] = $data['logradouro'];
        if (!empty($data['numero'])) $configuracoes['endereco']['numero'] = $data['numero'];
        if (!empty($data['complemento'])) $configuracoes['endereco']['complemento'] = $data['complemento'];
        if (!empty($data['bairro'])) $configuracoes['endereco']['bairro'] = $data['bairro'];
        if (!empty($data['cidade'])) $configuracoes['endereco']['cidade'] = $data['cidade'];
        if (!empty($data['estado'])) $configuracoes['endereco']['estado'] = $data['estado'];
        if (!empty($data['observacoes'])) $configuracoes['observacoes'] = $data['observacoes'];
        
        if (!empty($configuracoes)) {
            $data['configuracoes'] = $configuracoes;
        }
        
        // Limpa CNPJ (remove formatação)
        if (!empty($data['cnpj'])) {
            $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj']);
        }
        
        // Validação
        $errors = $this->empresa->validate($data);
        
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            $response->redirect('/empresas/create');
            return;
        }
        
        try {
            $id = $this->empresa->create($data);
            
            $_SESSION['success'] = 'Empresa cadastrada com sucesso!';
            $response->redirect('/empresas');
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao cadastrar empresa: ' . $e->getMessage());
            $this->session->set('old', $data);
            $response->redirect('/empresas/create');
        }
    }
    
    /**
     * Exibe detalhes de uma empresa
     */
    public function show(Request $request, Response $response, $id)
    {
        $empresa = $this->empresa->findById($id);
        
        if (!$empresa) {
            $response->status(404);
            return $this->render('errors/404', [
                'title' => 'Empresa não encontrada'
            ]);
        }
        
        return $this->render('empresas/show', [
            'title' => 'Detalhes da Empresa',
            'empresa' => $empresa
        ]);
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $empresa = $this->empresa->findById($id);
        
        if (!$empresa) {
            $response->status(404);
            return $this->render('errors/404', [
                'title' => 'Empresa não encontrada'
            ]);
        }
        
        // Decodifica configurações JSON
        if ($empresa['configuracoes']) {
            $empresa['configuracoes'] = json_decode($empresa['configuracoes'], true);
        }
        
        return $this->render('empresas/edit', [
            'title' => 'Editar Empresa',
            'empresa' => $empresa
        ]);
    }
    
    /**
     * Atualiza uma empresa
     */
    public function update(Request $request, Response $response, $id)
    {
        $empresa = $this->empresa->findById($id);
        
        if (!$empresa) {
            $response->status(404);
            return $this->render('errors/404', [
                'title' => 'Empresa não encontrada'
            ]);
        }
        
        $data = $request->all();
        
        // Processa campos extras para configuracoes JSON
        $configuracoes = [];
        if (!empty($data['telefone'])) $configuracoes['telefone'] = $data['telefone'];
        if (!empty($data['email'])) $configuracoes['email'] = $data['email'];
        if (!empty($data['site'])) $configuracoes['site'] = $data['site'];
        if (!empty($data['inscricao_estadual'])) $configuracoes['inscricao_estadual'] = $data['inscricao_estadual'];
        if (!empty($data['inscricao_municipal'])) $configuracoes['inscricao_municipal'] = $data['inscricao_municipal'];
        if (!empty($data['cep'])) $configuracoes['endereco']['cep'] = $data['cep'];
        if (!empty($data['logradouro'])) $configuracoes['endereco']['logradouro'] = $data['logradouro'];
        if (!empty($data['numero'])) $configuracoes['endereco']['numero'] = $data['numero'];
        if (!empty($data['complemento'])) $configuracoes['endereco']['complemento'] = $data['complemento'];
        if (!empty($data['bairro'])) $configuracoes['endereco']['bairro'] = $data['bairro'];
        if (!empty($data['cidade'])) $configuracoes['endereco']['cidade'] = $data['cidade'];
        if (!empty($data['estado'])) $configuracoes['endereco']['estado'] = $data['estado'];
        if (!empty($data['observacoes'])) $configuracoes['observacoes'] = $data['observacoes'];
        
        if (!empty($configuracoes)) {
            $data['configuracoes'] = $configuracoes;
        }
        
        // Limpa CNPJ (remove formatação)
        if (!empty($data['cnpj'])) {
            $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj']);
        }
        
        // Validação
        $errors = $this->empresa->validate($data, $id);
        
        if (!empty($errors)) {
            $this->session->set('errors', $errors);
            $this->session->set('old', $data);
            $response->redirect('/empresas/edit/' . $id);
            return;
        }
        
        try {
            $this->empresa->update($id, $data);
            
            $_SESSION['success'] = 'Empresa atualizada com sucesso!';
            $response->redirect('/empresas');
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao atualizar empresa: ' . $e->getMessage());
            $this->session->set('old', $data);
            $response->redirect('/empresas/edit/' . $id);
        }
    }
    
    /**
     * Exclui uma empresa
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $empresa = $this->empresa->findById($id);
        
        if (!$empresa) {
            $response->status(404);
            return $this->render('errors/404', [
                'title' => 'Empresa não encontrada'
            ]);
        }
        
        try {
            $this->empresa->delete($id);
            
            $_SESSION['success'] = 'Empresa excluída com sucesso!';
            $response->redirect('/empresas');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir empresa: ' . $e->getMessage();
            $response->redirect('/empresas');
        }
    }
}

