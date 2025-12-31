<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaBancaria;
use App\Models\Empresa;

class ContaBancariaController extends Controller
{
    private $contaBancariaModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->contaBancariaModel = new ContaBancaria();
            $empresaId = $request->get('empresa_id');
            
            $contasBancarias = $this->contaBancariaModel->findAll($empresaId);
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('contas_bancarias/index', [
                'title' => 'Gerenciar Contas Bancárias',
                'contasBancarias' => $contasBancarias,
                'empresas' => $empresas,
                'filters' => [
                    'empresa_id' => $empresaId
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar contas bancárias: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $empresaId = $request->get('empresa_id');
            
            return $this->render('contas_bancarias/create', [
                'title' => 'Nova Conta Bancária',
                'empresas' => $empresas,
                'defaultEmpresaId' => $empresaId
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/contas-bancarias');
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
                $response->redirect('/contas-bancarias/create');
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Cria conta bancária
            $this->contaBancariaModel = new ContaBancaria();
            $id = $this->contaBancariaModel->create($data);
            
            $_SESSION['success'] = 'Conta bancária criada com sucesso!';
            $response->redirect('/contas-bancarias');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar conta bancária: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/contas-bancarias/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->contaBancariaModel = new ContaBancaria();
            $contaBancaria = $this->contaBancariaModel->findById($id);
            
            if (!$contaBancaria) {
                $_SESSION['error'] = 'Conta bancária não encontrada!';
                $response->redirect('/contas-bancarias');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $contaBancaria['empresa'] = $this->empresaModel->findById($contaBancaria['empresa_id']);
            
            return $this->render('contas_bancarias/show', [
                'title' => 'Detalhes da Conta Bancária',
                'contaBancaria' => $contaBancaria
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta bancária: ' . $e->getMessage();
            $response->redirect('/contas-bancarias');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->contaBancariaModel = new ContaBancaria();
            $contaBancaria = $this->contaBancariaModel->findById($id);
            
            if (!$contaBancaria) {
                $_SESSION['error'] = 'Conta bancária não encontrada!';
                $response->redirect('/contas-bancarias');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('contas_bancarias/edit', [
                'title' => 'Editar Conta Bancária',
                'contaBancaria' => $contaBancaria,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta bancária: ' . $e->getMessage();
            $response->redirect('/contas-bancarias');
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
                $response->redirect("/contas-bancarias/{$id}/edit");
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Atualiza conta bancária
            $this->contaBancariaModel = new ContaBancaria();
            $this->contaBancariaModel->update($id, $data);
            
            $_SESSION['success'] = 'Conta bancária atualizada com sucesso!';
            $response->redirect('/contas-bancarias');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar conta bancária: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-bancarias/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->contaBancariaModel = new ContaBancaria();
            $this->contaBancariaModel->delete($id);
            $_SESSION['success'] = 'Conta bancária excluída com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir conta bancária: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-bancarias');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Banco Código
        if (empty($data['banco_codigo'])) {
            $errors['banco_codigo'] = 'O código do banco é obrigatório';
        }
        
        // Banco Nome
        if (empty($data['banco_nome'])) {
            $errors['banco_nome'] = 'O nome do banco é obrigatório';
        }
        
        // Agência
        if (empty($data['agencia'])) {
            $errors['agencia'] = 'A agência é obrigatória';
        }
        
        // Conta
        if (empty($data['conta'])) {
            $errors['conta'] = 'O número da conta é obrigatório';
        } else {
            // Verifica se já existe conta com mesma agência e número
            $this->contaBancariaModel = new ContaBancaria();
            $existing = $this->contaBancariaModel->findByAgenciaConta(
                $data['agencia'], 
                $data['conta'], 
                $data['empresa_id']
            );
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['conta'] = 'Já existe uma conta com esta agência e número para esta empresa';
            }
        }
        
        // Tipo de Conta
        if (!empty($data['tipo_conta']) && !in_array($data['tipo_conta'], ['corrente', 'poupanca', 'investimento'])) {
            $errors['tipo_conta'] = 'Tipo de conta inválido';
        }
        
        // Saldo Inicial
        if (isset($data['saldo_inicial']) && !is_numeric($data['saldo_inicial'])) {
            $errors['saldo_inicial'] = 'O saldo inicial deve ser um valor numérico';
        }
        
        return $errors;
    }
}
