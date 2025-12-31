<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            // Models
            $empresaModel = new Empresa();
            $usuarioModel = new Usuario();
            $fornecedorModel = new Fornecedor();
            $clienteModel = new Cliente();
            $categoriaModel = new CategoriaFinanceira();
            $centroCustoModel = new CentroCusto();
            $formaPagamentoModel = new FormaPagamento();
            $contaBancariaModel = new ContaBancaria();
            
            // Totais gerais
            $totalEmpresas = count($empresaModel->findAll(['ativo' => 1]));
            $totalUsuarios = count($usuarioModel->findAll());
            $totalFornecedores = count($fornecedorModel->findAll());
            $totalClientes = count($clienteModel->findAll());
            $totalCategorias = count($categoriaModel->findAll());
            $totalCentrosCusto = count($centroCustoModel->findAll());
            $totalFormasPagamento = count($formaPagamentoModel->findAll());
            $totalContasBancarias = count($contaBancariaModel->findAll());
            
            // Dados das empresas
            $empresas = $empresaModel->findAll(['ativo' => 1]);
            $empresasData = [];
            foreach ($empresas as $empresa) {
                $empresasData[] = [
                    'nome' => $empresa['nome_fantasia'],
                    'usuarios' => count($usuarioModel->findByEmpresa($empresa['id'])),
                    'fornecedores' => count($fornecedorModel->findAll($empresa['id'])),
                    'clientes' => count($clienteModel->findAll($empresa['id'])),
                    'contas' => count($contaBancariaModel->findAll($empresa['id']))
                ];
            }
            
            // Usuários ativos vs inativos
            $usuarios = $usuarioModel->findAll();
            $usuariosAtivos = count(array_filter($usuarios, fn($u) => $u['ativo'] == 1));
            $usuariosInativos = count(array_filter($usuarios, fn($u) => $u['ativo'] == 0));
            
            // Fornecedores por tipo de pessoa
            $fornecedores = $fornecedorModel->findAll();
            $fornecedoresPF = count(array_filter($fornecedores, fn($f) => $f['tipo_pessoa'] == 'fisica'));
            $fornecedoresPJ = count(array_filter($fornecedores, fn($f) => $f['tipo_pessoa'] == 'juridica'));
            
            // Clientes por tipo de pessoa
            $clientes = $clienteModel->findAll();
            $clientesPF = count(array_filter($clientes, fn($c) => $c['tipo_pessoa'] == 'fisica'));
            $clientesPJ = count(array_filter($clientes, fn($c) => $c['tipo_pessoa'] == 'juridica'));
            
            // Categorias por tipo
            $categorias = $categoriaModel->findAll();
            $categoriasReceita = count(array_filter($categorias, fn($c) => $c['tipo'] == 'receita'));
            $categoriasDespesa = count(array_filter($categorias, fn($c) => $c['tipo'] == 'despesa'));
            
            // Formas de pagamento por tipo
            $formasPagamento = $formaPagamentoModel->findAll();
            $formasPagamentoSomente = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'pagamento'));
            $formasRecebimentoSomente = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'recebimento'));
            $formasAmbos = count(array_filter($formasPagamento, fn($f) => $f['tipo'] == 'ambos'));
            
            // Contas bancárias por tipo
            $contasBancarias = $contaBancariaModel->findAll();
            $contasCorrente = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'corrente'));
            $contasPoupanca = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'poupanca'));
            $contasInvestimento = count(array_filter($contasBancarias, fn($c) => $c['tipo_conta'] == 'investimento'));
            
            // Saldo total das contas bancárias
            $saldoTotal = 0;
            $contasPorBanco = [];
            foreach ($contasBancarias as $conta) {
                $saldoTotal += (float)$conta['saldo_atual'];
                
                $banco = $conta['banco_nome'];
                if (!isset($contasPorBanco[$banco])) {
                    $contasPorBanco[$banco] = [
                        'total' => 0,
                        'saldo' => 0
                    ];
                }
                $contasPorBanco[$banco]['total']++;
                $contasPorBanco[$banco]['saldo'] += (float)$conta['saldo_atual'];
            }
            
            return $this->render('home/index', [
                'title' => 'Dashboard - Sistema Financeiro',
                'totais' => [
                    'empresas' => $totalEmpresas,
                    'usuarios' => $totalUsuarios,
                    'fornecedores' => $totalFornecedores,
                    'clientes' => $totalClientes,
                    'categorias' => $totalCategorias,
                    'centros_custo' => $totalCentrosCusto,
                    'formas_pagamento' => $totalFormasPagamento,
                    'contas_bancarias' => $totalContasBancarias
                ],
                'empresasData' => $empresasData,
                'usuarios' => [
                    'ativos' => $usuariosAtivos,
                    'inativos' => $usuariosInativos
                ],
                'fornecedores' => [
                    'pf' => $fornecedoresPF,
                    'pj' => $fornecedoresPJ
                ],
                'clientes' => [
                    'pf' => $clientesPF,
                    'pj' => $clientesPJ
                ],
                'categorias' => [
                    'receita' => $categoriasReceita,
                    'despesa' => $categoriasDespesa
                ],
                'formas_pagamento' => [
                    'pagamento' => $formasPagamentoSomente,
                    'recebimento' => $formasRecebimentoSomente,
                    'ambos' => $formasAmbos
                ],
                'contas_bancarias' => [
                    'corrente' => $contasCorrente,
                    'poupanca' => $contasPoupanca,
                    'investimento' => $contasInvestimento,
                    'saldo_total' => $saldoTotal,
                    'por_banco' => $contasPorBanco
                ]
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar dashboard: ' . $e->getMessage();
            return $this->render('home/index', [
                'title' => 'Dashboard - Sistema Financeiro',
                'totais' => [
                    'empresas' => 0,
                    'usuarios' => 0,
                    'fornecedores' => 0,
                    'clientes' => 0,
                    'categorias' => 0,
                    'centros_custo' => 0,
                    'formas_pagamento' => 0,
                    'contas_bancarias' => 0
                ]
            ]);
        }
    }
}
