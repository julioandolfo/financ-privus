<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Cliente;
use App\Models\Empresa;

class ClienteController extends Controller
{
    private $clienteModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->clienteModel = new Cliente();
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
                $clientes = $this->clienteModel->findAll($empresaId);
                return $response->json([
                    'success' => true,
                    'clientes' => $clientes
                ]);
            }
            
            // Paginação
            $porPagina = $request->get('por_pagina') ?? 25;
            $paginaAtual = $request->get('pagina') ?? 1;
            $paginaAtual = max(1, (int)$paginaAtual);
            
            $totalRegistros = $this->clienteModel->countWithFilters($filters);
            
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
            
            $clientes = $this->clienteModel->findAllWithFilters($filters);
            $filtersApplied = $request->all();
            
            return $this->render('clientes/index', [
                'title' => 'Gerenciar Clientes',
                'clientes' => $clientes,
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
            $_SESSION['error'] = 'Erro ao carregar clientes: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('clientes/create', [
                'title' => 'Novo Cliente',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/clientes');
        }
    }

    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->all();
            
            // Processa endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
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
                $response->redirect('/clientes/create');
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
            
            // Cria cliente
            $this->clienteModel = new Cliente();
            $id = $this->clienteModel->create($data);
            
            $_SESSION['success'] = 'Cliente criado com sucesso!';
            $response->redirect('/clientes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar cliente: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/clientes/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->clienteModel = new Cliente();
            $cliente = $this->clienteModel->findById($id);
            
            if (!$cliente) {
                $_SESSION['error'] = 'Cliente não encontrado!';
                $response->redirect('/clientes');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $cliente['empresa'] = $this->empresaModel->findById($cliente['empresa_id']);
            
            // Decodifica endereço JSON
            if ($cliente['endereco']) {
                $cliente['endereco'] = json_decode($cliente['endereco'], true);
            }
            
            return $this->render('clientes/show', [
                'title' => 'Detalhes do Cliente',
                'cliente' => $cliente
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar cliente: ' . $e->getMessage();
            $response->redirect('/clientes');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->clienteModel = new Cliente();
            $cliente = $this->clienteModel->findById($id);
            
            if (!$cliente) {
                $_SESSION['error'] = 'Cliente não encontrado!';
                $response->redirect('/clientes');
                return;
            }
            
            // Decodifica endereço JSON
            if ($cliente['endereco']) {
                $cliente['endereco'] = json_decode($cliente['endereco'], true);
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('clientes/edit', [
                'title' => 'Editar Cliente',
                'cliente' => $cliente,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar cliente: ' . $e->getMessage();
            $response->redirect('/clientes');
        }
    }

    public function update(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Processa endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
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
                $response->redirect("/clientes/edit/{$id}");
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
            
            // Atualiza cliente
            $this->clienteModel = new Cliente();
            $this->clienteModel->update($id, $data);
            
            $_SESSION['success'] = 'Cliente atualizado com sucesso!';
            $response->redirect('/clientes');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar cliente: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/clientes/edit/{$id}");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->clienteModel = new Cliente();
            $this->clienteModel->delete($id);
            
            $_SESSION['success'] = 'Cliente excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir cliente: ' . $e->getMessage();
        }
        
        $response->redirect('/clientes');
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
            
            $this->clienteModel = new Cliente();
            
            if ($data['tipo'] === 'fisica') {
                if (!$this->clienteModel->validarCPF($cpfCnpj)) {
                    $errors['cpf_cnpj'] = 'CPF inválido';
                }
            } else {
                if (!$this->clienteModel->validarCNPJ($cpfCnpj)) {
                    $errors['cpf_cnpj'] = 'CNPJ inválido';
                }
            }
            
            // Verifica se CPF/CNPJ já existe
            $existing = $this->clienteModel->findByCpfCnpj($cpfCnpj, $data['empresa_id']);
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

