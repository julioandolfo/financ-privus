<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\FormaPagamento;
use App\Models\Empresa;

class FormaPagamentoController extends Controller
{
    private $formaPagamentoModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->formaPagamentoModel = new FormaPagamento();
            $empresaId = $request->get('empresa_id');
            $tipo = $request->get('tipo');
            
            $formasPagamento = $this->formaPagamentoModel->findAll($empresaId);
            
            // Filtra por tipo se especificado
            if ($tipo) {
                $formasPagamento = array_filter($formasPagamento, function($fp) use ($tipo) {
                    return $fp['tipo'] === $tipo || $fp['tipo'] === 'ambos';
                });
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('formas_pagamento/index', [
                'title' => 'Gerenciar Formas de Pagamento',
                'formasPagamento' => $formasPagamento,
                'empresas' => $empresas,
                'filters' => [
                    'empresa_id' => $empresaId,
                    'tipo' => $tipo
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formas de pagamento: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $empresaId = $request->get('empresa_id');
            
            return $this->render('formas_pagamento/create', [
                'title' => 'Nova Forma de Pagamento',
                'empresas' => $empresas,
                'defaultEmpresaId' => $empresaId
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/formas-pagamento');
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
                $response->redirect('/formas-pagamento/create');
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Cria forma de pagamento
            $this->formaPagamentoModel = new FormaPagamento();
            $id = $this->formaPagamentoModel->create($data);
            
            $_SESSION['success'] = 'Forma de pagamento criada com sucesso!';
            $response->redirect('/formas-pagamento');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar forma de pagamento: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/formas-pagamento/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->formaPagamentoModel = new FormaPagamento();
            $formaPagamento = $this->formaPagamentoModel->findById($id);
            
            if (!$formaPagamento) {
                $_SESSION['error'] = 'Forma de pagamento não encontrada!';
                $response->redirect('/formas-pagamento');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $formaPagamento['empresa'] = $this->empresaModel->findById($formaPagamento['empresa_id']);
            
            return $this->render('formas_pagamento/show', [
                'title' => 'Detalhes da Forma de Pagamento',
                'formaPagamento' => $formaPagamento
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar forma de pagamento: ' . $e->getMessage();
            $response->redirect('/formas-pagamento');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->formaPagamentoModel = new FormaPagamento();
            $formaPagamento = $this->formaPagamentoModel->findById($id);
            
            if (!$formaPagamento) {
                $_SESSION['error'] = 'Forma de pagamento não encontrada!';
                $response->redirect('/formas-pagamento');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('formas_pagamento/edit', [
                'title' => 'Editar Forma de Pagamento',
                'formaPagamento' => $formaPagamento,
                'empresas' => $empresas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar forma de pagamento: ' . $e->getMessage();
            $response->redirect('/formas-pagamento');
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
                $response->redirect("/formas-pagamento/edit/{$id}");
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Atualiza forma de pagamento
            $this->formaPagamentoModel = new FormaPagamento();
            $this->formaPagamentoModel->update($id, $data);
            
            $_SESSION['success'] = 'Forma de pagamento atualizada com sucesso!';
            $response->redirect('/formas-pagamento');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar forma de pagamento: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/formas-pagamento/edit/{$id}");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->formaPagamentoModel = new FormaPagamento();
            $this->formaPagamentoModel->delete($id);
            $_SESSION['success'] = 'Forma de pagamento excluída com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir forma de pagamento: ' . $e->getMessage();
        }
        
        $response->redirect('/formas-pagamento');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Código
        if (empty($data['codigo'])) {
            $errors['codigo'] = 'O código é obrigatório';
        } else {
            $this->formaPagamentoModel = new FormaPagamento();
            $existing = $this->formaPagamentoModel->findByCodigo($data['codigo'], $data['empresa_id']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['codigo'] = 'Este código já está em uso para esta empresa';
            }
        }
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        // Tipo
        if (!empty($data['tipo']) && !in_array($data['tipo'], ['pagamento', 'recebimento', 'ambos'])) {
            $errors['tipo'] = 'Tipo inválido';
        }
        
        return $errors;
    }
}

