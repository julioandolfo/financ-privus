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
        
        // Validação
        $errors = $this->empresa->validate($data);
        
        if (!empty($errors)) {
            return $this->render('empresas/create', [
                'title' => 'Nova Empresa',
                'errors' => $errors,
                'data' => $data
            ]);
        }
        
        try {
            $id = $this->empresa->create($data);
            
            $_SESSION['success'] = 'Empresa cadastrada com sucesso!';
            $response->redirect('/empresas');
        } catch (\Exception $e) {
            return $this->render('empresas/create', [
                'title' => 'Nova Empresa',
                'error' => 'Erro ao cadastrar empresa: ' . $e->getMessage(),
                'data' => $data
            ]);
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
        
        // Validação
        $errors = $this->empresa->validate($data, $id);
        
        if (!empty($errors)) {
            return $this->render('empresas/edit', [
                'title' => 'Editar Empresa',
                'errors' => $errors,
                'empresa' => array_merge($empresa, $data)
            ]);
        }
        
        try {
            $this->empresa->update($id, $data);
            
            $_SESSION['success'] = 'Empresa atualizada com sucesso!';
            $response->redirect('/empresas');
        } catch (\Exception $e) {
            return $this->render('empresas/edit', [
                'title' => 'Editar Empresa',
                'error' => 'Erro ao atualizar empresa: ' . $e->getMessage(),
                'empresa' => array_merge($empresa, $data)
            ]);
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

