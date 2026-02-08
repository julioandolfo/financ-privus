<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Fornecedor;
use App\Models\Empresa;

class FornecedorController extends Controller
{
    private $fornecedorModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $empresaId = $request->get('empresa_id');
            $ajax = $request->get('ajax');
            
            // Filtros
            $filters = [
                'empresa_id' => $empresaId,
                'busca' => $request->get('busca'),
                'tipo_pessoa' => $request->get('tipo_pessoa')
            ];
            
            // Se for AJAX, retorna todos sem paginação
            if ($ajax) {
                $fornecedores = $this->fornecedorModel->findAll($empresaId);
                return $response->json([
                    'success' => true,
                    'fornecedores' => $fornecedores
                ]);
            }
            
            // Paginação
            $porPagina = $request->get('por_pagina') ?? 25;
            $paginaAtual = $request->get('pagina') ?? 1;
            $paginaAtual = max(1, (int)$paginaAtual);
            
            $totalRegistros = $this->fornecedorModel->countWithFilters($filters);
            
            $totalPaginas = 1;
            $offset = 0;
            
            if ($porPagina !== 'todos') {
                $porPagina = (int) $porPagina;
                $totalPaginas = ceil($totalRegistros / $porPagina);
                if ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
                    $paginaAtual = $totalPaginas;
                }
                $offset = ($paginaAtual - 1) * $porPagina;
                $filters['limite'] = $porPagina;
                $filters['offset'] = $offset;
            }
            
            $fornecedores = $this->fornecedorModel->findAllWithFilters($filters);
            $filtersApplied = $request->all();
            
            return $this->render('fornecedores/index', [
                'title' => 'Gerenciar Fornecedores',
                'fornecedores' => $fornecedores,
                'filters' => $filtersApplied,
                'paginacao' => [
                    'total_registros' => $totalRegistros,
                    'por_pagina' => $porPagina,
                    'pagina_atual' => $paginaAtual,
                    'total_paginas' => $totalPaginas,
                    'offset' => $offset
                ]
            ]);
        } catch (\Exception $e) {
            if ($request->get('ajax')) {
                return $response->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            $_SESSION['error'] = 'Erro ao carregar fornecedores: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('fornecedores/create', [
                'title' => 'Novo Fornecedor',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/fornecedores');
        }
    }

    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->all();
            
            // Processa endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
                // Remove campos vazios do endereço
                $endereco = array_filter($data['endereco'], function($value) {
                    return !empty($value);
                });
                $data['endereco'] = !empty($endereco) ? $endereco : null;
            }
            
            // Validações
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect('/fornecedores/create');
                return;
            }
            
            // Remove máscara do CPF/CNPJ
            if (!empty($data['cpf_cnpj'])) {
                $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
            }
            
            // Remove máscara do telefone
            if (!empty($data['telefone'])) {
                $data['telefone'] = preg_replace('/[^0-9]/', '', $data['telefone']);
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Cria fornecedor
            $this->fornecedorModel = new Fornecedor();
            $id = $this->fornecedorModel->create($data);
            
            $_SESSION['success'] = 'Fornecedor criado com sucesso!';
            $response->redirect('/fornecedores');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar fornecedor: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/fornecedores/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $fornecedor = $this->fornecedorModel->findById($id);
            
            if (!$fornecedor) {
                $_SESSION['error'] = 'Fornecedor não encontrado!';
                $response->redirect('/fornecedores');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $fornecedor['empresa'] = $this->empresaModel->findById($fornecedor['empresa_id']);
            
            // Decodifica endereço JSON
            if ($fornecedor['endereco']) {
                $fornecedor['endereco'] = json_decode($fornecedor['endereco'], true);
            }
            
            return $this->render('fornecedores/show', [
                'title' => 'Detalhes do Fornecedor',
                'fornecedor' => $fornecedor
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar fornecedor: ' . $e->getMessage();
            $response->redirect('/fornecedores');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $fornecedor = $this->fornecedorModel->findById($id);
            
            if (!$fornecedor) {
                $_SESSION['error'] = 'Fornecedor não encontrado!';
                $response->redirect('/fornecedores');
                return;
            }
            
            // Decodifica endereço JSON
            if ($fornecedor['endereco']) {
                $fornecedor['endereco'] = json_decode($fornecedor['endereco'], true);
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('fornecedores/edit', [
                'title' => 'Editar Fornecedor',
                'fornecedor' => $fornecedor,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar fornecedor: ' . $e->getMessage();
            $response->redirect('/fornecedores');
        }
    }

    public function update(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Processa endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
                // Remove campos vazios do endereço
                $endereco = array_filter($data['endereco'], function($value) {
                    return !empty($value);
                });
                $data['endereco'] = !empty($endereco) ? $endereco : null;
            }
            
            // Validações
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect("/fornecedores/edit/{$id}");
                return;
            }
            
            // Remove máscara do CPF/CNPJ
            if (!empty($data['cpf_cnpj'])) {
                $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
            }
            
            // Remove máscara do telefone
            if (!empty($data['telefone'])) {
                $data['telefone'] = preg_replace('/[^0-9]/', '', $data['telefone']);
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Atualiza fornecedor
            $this->fornecedorModel = new Fornecedor();
            $this->fornecedorModel->update($id, $data);
            
            $_SESSION['success'] = 'Fornecedor atualizado com sucesso!';
            $response->redirect('/fornecedores');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/fornecedores/edit/{$id}");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $this->fornecedorModel->delete($id);
            
            $_SESSION['success'] = 'Fornecedor excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir fornecedor: ' . $e->getMessage();
        }
        
        $response->redirect('/fornecedores');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Tipo
        if (empty($data['tipo']) || !in_array($data['tipo'], ['fisica', 'juridica'])) {
            $errors['tipo'] = 'Tipo inválido (física ou jurídica)';
        }
        
        // Nome/Razão Social
        if (empty($data['nome_razao_social'])) {
            $errors['nome_razao_social'] = 'O nome/razão social é obrigatório';
        }
        
        // CPF/CNPJ
        if (!empty($data['cpf_cnpj'])) {
            $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
            
            $this->fornecedorModel = new Fornecedor();
            
            if ($data['tipo'] === 'fisica') {
                if (!$this->fornecedorModel->validarCPF($cpfCnpj)) {
                    $errors['cpf_cnpj'] = 'CPF inválido';
                }
            } else {
                if (!$this->fornecedorModel->validarCNPJ($cpfCnpj)) {
                    $errors['cpf_cnpj'] = 'CNPJ inválido';
                }
            }
            
            // Verifica se CPF/CNPJ já existe
            $existing = $this->fornecedorModel->findByCpfCnpj($cpfCnpj, $data['empresa_id']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['cpf_cnpj'] = 'Este CPF/CNPJ já está cadastrado para esta empresa';
            }
        }
        
        // Email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }
        
        return $errors;
    }
}

