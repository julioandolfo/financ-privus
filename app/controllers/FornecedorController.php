<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Fornecedor;
use App\Models\Empresa;

class FornecedorController extends Controller
{
    private $fornecedorModel;
    private $empresaModel;

    public function index()
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $fornecedores = $this->fornecedorModel->findAll();
            
            $this->view('fornecedores/index', [
                'title' => 'Gerenciar Fornecedores',
                'fornecedores' => $fornecedores
            ]);
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar fornecedores: ' . $e->getMessage());
            $this->redirect('/');
        }
    }

    public function create()
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $this->view('fornecedores/create', [
                'title' => 'Novo Fornecedor',
                'empresas' => $empresas
            ]);
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar formulário: ' . $e->getMessage());
            $this->redirect('/fornecedores');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            // Validações
            $errors = $this->validate($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $this->redirect('/fornecedores/create');
                return;
            }
            
            // Remove máscara do CPF/CNPJ
            if (!empty($data['cpf_cnpj'])) {
                $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Cria fornecedor
            $this->fornecedorModel = new Fornecedor();
            $id = $this->fornecedorModel->create($data);
            
            $this->session->set('success', 'Fornecedor criado com sucesso!');
            $this->redirect('/fornecedores');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao criar fornecedor: ' . $e->getMessage());
            $this->redirect('/fornecedores/create');
        }
    }

    public function show($id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $fornecedor = $this->fornecedorModel->findById($id);
            
            if (!$fornecedor) {
                $this->session->set('error', 'Fornecedor não encontrado!');
                $this->redirect('/fornecedores');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $fornecedor['empresa'] = $this->empresaModel->findById($fornecedor['empresa_id']);
            
            // Decodifica endereço JSON
            if ($fornecedor['endereco']) {
                $fornecedor['endereco'] = json_decode($fornecedor['endereco'], true);
            }
            
            $this->view('fornecedores/show', [
                'title' => 'Detalhes do Fornecedor',
                'fornecedor' => $fornecedor
            ]);
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar fornecedor: ' . $e->getMessage());
            $this->redirect('/fornecedores');
        }
    }

    public function edit($id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $fornecedor = $this->fornecedorModel->findById($id);
            
            if (!$fornecedor) {
                $this->session->set('error', 'Fornecedor não encontrado!');
                $this->redirect('/fornecedores');
                return;
            }
            
            // Decodifica endereço JSON
            if ($fornecedor['endereco']) {
                $fornecedor['endereco'] = json_decode($fornecedor['endereco'], true);
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $this->view('fornecedores/edit', [
                'title' => 'Editar Fornecedor',
                'fornecedor' => $fornecedor,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao carregar fornecedor: ' . $e->getMessage());
            $this->redirect('/fornecedores');
        }
    }

    public function update($id)
    {
        try {
            $data = $this->request->all();
            
            // Validações
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $this->redirect("/fornecedores/edit/{$id}");
                return;
            }
            
            // Remove máscara do CPF/CNPJ
            if (!empty($data['cpf_cnpj'])) {
                $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Atualiza fornecedor
            $this->fornecedorModel = new Fornecedor();
            $this->fornecedorModel->update($id, $data);
            
            $this->session->set('success', 'Fornecedor atualizado com sucesso!');
            $this->redirect('/fornecedores');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao atualizar fornecedor: ' . $e->getMessage());
            $this->redirect("/fornecedores/edit/{$id}");
        }
    }

    public function destroy($id)
    {
        try {
            $this->fornecedorModel = new Fornecedor();
            $this->fornecedorModel->delete($id);
            
            $this->session->set('success', 'Fornecedor excluído com sucesso!');
            
        } catch (\Exception $e) {
            $this->session->set('error', 'Erro ao excluir fornecedor: ' . $e->getMessage());
        }
        
        $this->redirect('/fornecedores');
    }

    private function validate($data, $id = null)
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

