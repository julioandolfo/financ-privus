<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\ParcelaReceber;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Empresa;
use App\Models\PedidoVinculado;
use App\Models\PedidoItem;
use App\Models\MovimentacaoCaixa;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use App\Middleware\ApiAuthMiddleware;

/**
 * Controller para API REST - Endpoints Públicos
 * Permite que sistemas externos consumam e insiram dados
 */
class ApiRestController extends Controller
{
    private $middleware;

    public function __construct()
    {
        parent::__construct();
        $this->middleware = new ApiAuthMiddleware();
    }

    /**
     * Autenticação e log de requisições
     */
    private function authenticate(Request $request, Response $response)
    {
        $result = $this->middleware->handle($request, $response);
        if ($result === false) {
            exit; // Middleware já enviou resposta de erro
        }
        return $request->apiToken;
    }

    /**
     * Log de sucesso
     */
    private function logSuccess(Request $request, $statusCode, $data)
    {
        $this->middleware->logRequest($request, $statusCode, $data, $request->apiToken ?? null);
    }

    // =====================================================
    // CONTAS A PAGAR
    // =====================================================

    public function contasPagarIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaPagar();
        $empresaId = $token['empresa_id'];
        
        $contas = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $contas];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasPagarShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaPagar();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $conta];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasPagarStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Mapear 'valor' para 'valor_total' se necessário (compatibilidade)
        if (isset($input['valor']) && !isset($input['valor_total'])) {
            $input['valor_total'] = $input['valor'];
            unset($input['valor']);
        }
        
        // Garantir empresa_id do token
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        // Garantir usuario_cadastro_id
        if (!isset($input['usuario_cadastro_id'])) {
            $input['usuario_cadastro_id'] = $token['usuario_id'] ?? null;
        }
        
        // Valores padrão
        if (!isset($input['data_emissao'])) {
            $input['data_emissao'] = date('Y-m-d');
        }
        if (!isset($input['data_competencia'])) {
            $input['data_competencia'] = $input['data_emissao'] ?? date('Y-m-d');
        }
        if (!isset($input['valor_pago'])) {
            $input['valor_pago'] = 0;
        }
        if (!isset($input['status'])) {
            $input['status'] = 'pendente';
        }
        
        // Validação básica
        $errors = $this->validateContaPagar($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model = new ContaPagar();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Conta criada com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    public function contasPagarUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaPagar();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Mapear 'valor' para 'valor_total' se necessário (compatibilidade)
        if (isset($input['valor']) && !isset($input['valor_total'])) {
            $input['valor_total'] = $input['valor'];
            unset($input['valor']);
        }
        
        // Validação básica (mais flexível para update)
        $errors = $this->validateContaPagar($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model->update($id, $input);
        
        $data = ['success' => true, 'message' => 'Conta atualizada com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasPagarDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaPagar();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $model->softDelete($id, 'Excluído via API');
        
        $data = ['success' => true, 'message' => 'Conta excluída com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // CONTAS A RECEBER
    // =====================================================

    public function contasReceberIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaReceber();
        $empresaId = $token['empresa_id'];
        
        $contas = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $contas];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasReceberShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaReceber();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $conta];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasReceberStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Mapear 'valor' para 'valor_total' se necessário (compatibilidade)
        if (isset($input['valor']) && !isset($input['valor_total'])) {
            $input['valor_total'] = $input['valor'];
            unset($input['valor']);
        }
        
        // Garantir empresa_id do token
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        // Garantir usuario_cadastro_id
        if (!isset($input['usuario_cadastro_id'])) {
            $input['usuario_cadastro_id'] = $token['usuario_id'] ?? null;
        }
        
        // Valores padrão
        if (!isset($input['data_emissao'])) {
            $input['data_emissao'] = date('Y-m-d');
        }
        if (!isset($input['data_competencia'])) {
            $input['data_competencia'] = $input['data_emissao'] ?? date('Y-m-d');
        }
        if (!isset($input['valor_recebido'])) {
            $input['valor_recebido'] = 0;
        }
        if (!isset($input['status'])) {
            $input['status'] = 'pendente';
        }
        if (!isset($input['desconto'])) {
            $input['desconto'] = 0;
        }
        if (!isset($input['frete'])) {
            $input['frete'] = 0;
        }
        
        // 🚀 NOVO: Auto-cadastro completo (Cliente + Produtos + Pedido)
        $clienteCriado = false;
        $produtosCriados = 0;
        $produtosVinculados = 0;
        $pedidoId = null;
        $clienteId = null;
        $valorCustoTotal = 0;
        $lucro = 0;
        $margemLucro = 0;
        
        try {
            // 1. Auto-cadastro de CLIENTE (se enviado)
            if (isset($input['cliente']) && !empty($input['cliente'])) {
                $clienteData = $input['cliente'];
                
                // Permite buscar/criar cliente por CPF/CNPJ ou código do cliente
                if (!empty($clienteData['cpf_cnpj']) || !empty($clienteData['codigo_cliente'])) {
                    $clienteModel = new Cliente();
                    
                    // Verifica se o cliente já existe ANTES de criar
                    $clienteExistente = null;
                    if (!empty($clienteData['cpf_cnpj'])) {
                        $clienteExistente = $clienteModel->findByCpfCnpj($clienteData['cpf_cnpj'], $input['empresa_id']);
                    }
                    if (!$clienteExistente && !empty($clienteData['codigo_cliente'])) {
                        $clienteExistente = $clienteModel->findByCodigoCliente($clienteData['codigo_cliente'], $input['empresa_id']);
                    }
                    
                    // Busca ou cria o cliente
                    $cliente = $clienteModel->findOrCreateByCpfCnpj($clienteData, $input['empresa_id']);
                    
                    if ($cliente) {
                        $clienteId = $cliente['id'];
                        $input['cliente_id'] = $clienteId;
                        
                        // Cliente foi criado se não existia antes
                        $clienteCriado = empty($clienteExistente);
                    }
                }
            }
            
            // Se já temos um cliente_id no input, usar ele
            if (empty($clienteId) && !empty($input['cliente_id'])) {
                $clienteId = $input['cliente_id'];
            }
            
            // 2. Auto-cadastro de PEDIDO com PRODUTOS (se enviado)
            if (isset($input['criar_pedido']) && $input['criar_pedido'] === true && isset($input['pedido'])) {
                $pedidoData = $input['pedido'];
                
                // Criar pedido
                $pedidoModel = new PedidoVinculado();
                // Usar numero_documento da conta como numero_pedido se não informado
                $numeroPedido = $pedidoData['numero_pedido'] ?? $input['numero_documento'] ?? 'API-' . date('YmdHis');
                
                // Tratar data do pedido (converter datetime para date se necessário)
                $dataPedido = $pedidoData['data_pedido'] ?? $input['data_emissao'] ?? date('Y-m-d');
                if (strlen($dataPedido) > 10) {
                    $dataPedido = substr($dataPedido, 0, 10); // Extrai apenas YYYY-MM-DD
                }
                
                // Capturar frete e desconto do pedido ou do input
                $fretePedido = $pedidoData['frete'] ?? $input['frete'] ?? 0;
                $descontoPedido = $pedidoData['desconto'] ?? $input['desconto'] ?? 0;
                
                $pedidoId = $pedidoModel->create([
                    'empresa_id' => $input['empresa_id'],
                    'cliente_id' => $clienteId ?? $input['cliente_id'] ?? null,
                    'numero_pedido' => $numeroPedido,
                    'origem_id' => $numeroPedido,
                    'data_pedido' => $dataPedido,
                    'status' => 'concluido',
                    'origem' => 'api',
                    'valor_total' => 0, // Será calculado
                    'frete' => $fretePedido,
                    'desconto' => $descontoPedido,
                    'bonificado' => $pedidoData['bonificado'] ?? $input['bonificado'] ?? 0,
                    'observacoes' => $pedidoData['observacoes'] ?? null
                ]);
                
                $input['pedido_id'] = $pedidoId;
                
                // Criar itens do pedido
                if (isset($pedidoData['produtos']) && is_array($pedidoData['produtos'])) {
                    $produtoModel = new Produto();
                    $pedidoItemModel = new PedidoItem();
                    $valorTotalPedido = 0;
                    
                    foreach ($pedidoData['produtos'] as $produtoData) {
                        $produto = null;
                        
                        // Buscar ou criar produto por SKU
                        if (!empty($produtoData['sku'])) {
                            $produto = $produtoModel->findOrCreateBySku([
                                'sku' => $produtoData['sku'],
                                'nome' => $produtoData['nome'] ?? 'Produto API',
                                'codigo' => $produtoData['codigo'] ?? strtoupper(substr($produtoData['sku'], 0, 20)),
                                'custo_unitario' => $produtoData['custo_unitario'] ?? 0,
                                'preco_venda' => $produtoData['valor_unitario'] ?? 0,
                                'unidade_medida' => $produtoData['unidade_medida'] ?? 'UN',
                                'empresa_id' => $input['empresa_id']
                            ], $input['empresa_id']);
                            
                            if ($produto && empty($produtoModel->findBySku($produtoData['sku'], $input['empresa_id']))) {
                                $produtosCriados++;
                            }
                        }
                        
                        if ($produto) {
                            $quantidade = $produtoData['quantidade'] ?? 1;
                            $valorUnitario = $produtoData['valor_unitario'] ?? $produto['preco_venda'];
                            $custoUnitario = $produtoData['custo_unitario'] ?? $produto['custo_unitario'] ?? 0;
                            $valorTotal = $quantidade * $valorUnitario;
                            $custoTotal = $quantidade * $custoUnitario;
                            $nomeProduto = $produtoData['nome'] ?? $produto['nome'] ?? 'Produto';
                            
                            // Criar item do pedido
                            $pedidoItemModel->create([
                                'pedido_id' => $pedidoId,
                                'produto_id' => $produto['id'],
                                'codigo_produto_origem' => $produtoData['sku'] ?? $produto['sku'] ?? null,
                                'nome_produto' => $nomeProduto,
                                'quantidade' => $quantidade,
                                'valor_unitario' => $valorUnitario,
                                'custo_unitario' => $custoUnitario,
                                'custo_total' => $custoTotal,
                                'valor_total' => $valorTotal
                            ]);
                            
                            $valorTotalPedido += $valorTotal;
                            $valorCustoTotal += $custoTotal;
                            $produtosVinculados++;
                        }
                    }
                    
                    // Atualizar valor_total e valor_custo_total do pedido (com frete e desconto)
                    $pedidoModel->updateTotais($pedidoId, $valorTotalPedido, $valorCustoTotal, $fretePedido, $descontoPedido);
                    
                    // Se não foi informado valor_total, usar o do pedido
                    if (empty($input['valor_total'])) {
                        $input['valor_total'] = $valorTotalPedido;
                    }
                    
                    // Calcular lucro e margem (lucro = valor_total - custo_total - frete + desconto)
                    // Frete é um custo adicional, desconto é um abatimento do valor recebido
                    if ($valorTotalPedido > 0) {
                        $lucro = $valorTotalPedido - $valorCustoTotal - $fretePedido;
                        $margemLucro = ($lucro / $valorTotalPedido) * 100;
                    }
                }
            }
            
        } catch (\Exception $e) {
            $data = ['success' => false, 'error' => 'Erro ao processar pedido/cliente: ' . $e->getMessage()];
            $this->logSuccess($request, 500, $data);
            return $response->json($data, 500);
        }
        
        $errors = $this->validateContaReceber($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model = new ContaReceber();
        $parcelasIds = [];
        $parcelasDetalhes = [];
        
        // 🚀 Suporte a Parcelas via Array Manual
        if (isset($input['parcelas']) && is_array($input['parcelas']) && count($input['parcelas']) > 0) {
            // Validar parcelas
            $totalParcelas = 0;
            foreach ($input['parcelas'] as $idx => $parcela) {
                if (empty($parcela['valor']) || empty($parcela['data_vencimento'])) {
                    $data = ['success' => false, 'error' => "Parcela " . ($idx + 1) . ": valor e data_vencimento são obrigatórios"];
                    $this->logSuccess($request, 400, $data);
                    return $response->json($data, 400);
                }
                $totalParcelas += floatval($parcela['valor']);
            }
            
            // Atualizar valor_total se não informado (soma das parcelas + frete - desconto)
            if (empty($input['valor_total'])) {
                $input['valor_total'] = $totalParcelas;
            }
            
            // Definir número de parcelas
            $input['numero_parcelas'] = count($input['parcelas']);
            
            // Criar conta principal
            $id = $model->create($input);
            
            // Criar as parcelas
            $parcelaModel = new ParcelaReceber();
            foreach ($input['parcelas'] as $idx => $parcela) {
                $parcelaId = $parcelaModel->create([
                    'conta_receber_id' => $id,
                    'empresa_id' => $input['empresa_id'],
                    'numero_parcela' => $idx + 1,
                    'valor_parcela' => $parcela['valor'],
                    'data_vencimento' => $parcela['data_vencimento'],
                    'desconto' => $parcela['desconto'] ?? 0,
                    'frete' => $parcela['frete'] ?? 0,
                    'observacoes' => $parcela['observacoes'] ?? null
                ]);
                $parcelasIds[] = $parcelaId;
                $parcelasDetalhes[] = [
                    'id' => $parcelaId,
                    'numero' => $idx + 1,
                    'valor' => $parcela['valor'],
                    'data_vencimento' => $parcela['data_vencimento']
                ];
            }
        } else {
            // Criar conta simples (sem parcelas)
            $id = $model->create($input);
        }
        
        // Resposta detalhada
        $data = [
            'success' => true,
            'conta_receber_id' => $id,
            'message' => 'Conta criada com sucesso'
        ];
        
        // Adicionar informações de parcelas
        if (!empty($parcelasIds)) {
            $data['parcelas_ids'] = $parcelasIds;
            $data['numero_parcelas'] = count($parcelasIds);
            $data['parcelas'] = $parcelasDetalhes;
        }
        
        // Adicionar frete e desconto na resposta
        $data['frete'] = $input['frete'] ?? 0;
        $data['desconto'] = $input['desconto'] ?? 0;
        
        // Adicionar informações do auto-cadastro
        if ($pedidoId) {
            $data['pedido_id'] = $pedidoId;
            $data['produtos_vinculados'] = $produtosVinculados;
            $data['valor_total'] = $input['valor_total'];
            $data['valor_custo_total'] = round($valorCustoTotal, 2);
            $data['lucro'] = round($lucro, 2);
            $data['margem_lucro'] = round($margemLucro, 2);
        }
        
        if ($clienteId) {
            $data['cliente_id'] = $clienteId;
            $data['cliente_criado'] = $clienteCriado;
        }
        
        if ($produtosCriados > 0) {
            $data['produtos_criados'] = $produtosCriados;
        }
        
        // Mensagem amigável
        $mensagens = ['Conta a receber criada com sucesso!'];
        if ($clienteCriado) $mensagens[] = 'Cliente cadastrado automaticamente';
        if ($produtosCriados > 0) $mensagens[] = "{$produtosCriados} produto(s) criado(s) automaticamente";
        if ($pedidoId) {
            $numPedido = $input['pedido']['numero_pedido'] ?? $pedidoId;
            $mensagens[] = "Pedido #{$numPedido} vinculado";
        }
        if (!empty($parcelasIds)) $mensagens[] = count($parcelasIds) . " parcela(s) gerada(s)";
        
        $data['message'] = implode('. ', $mensagens) . '.';
        
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    public function contasReceberUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaReceber();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Mapear 'valor' para 'valor_total' se necessário (compatibilidade)
        if (isset($input['valor']) && !isset($input['valor_total'])) {
            $input['valor_total'] = $input['valor'];
            unset($input['valor']);
        }
        
        // Validação básica (mais flexível para update)
        $errors = $this->validateContaReceber($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        // Atualizar conta principal (excluindo parcelas do input)
        $inputConta = $input;
        unset($inputConta['parcelas']);
        
        if (!empty($inputConta)) {
            $model->update($id, $inputConta);
        }
        
        // Atualizar parcelas se enviadas
        $parcelasAtualizadas = 0;
        $parcelasErros = [];
        
        if (isset($input['parcelas']) && is_array($input['parcelas'])) {
            $parcelaModel = new ParcelaReceber();
            
            foreach ($input['parcelas'] as $parcelaInput) {
                // Pode enviar parcela_id ou numero_parcela
                $parcelaId = $parcelaInput['parcela_id'] ?? $parcelaInput['id'] ?? null;
                $numeroParcela = $parcelaInput['numero_parcela'] ?? null;
                
                // Se não tem ID, busca pelo número da parcela
                if (!$parcelaId && $numeroParcela) {
                    $parcelasExistentes = $parcelaModel->findByContaReceber($id);
                    foreach ($parcelasExistentes as $p) {
                        if ($p['numero_parcela'] == $numeroParcela) {
                            $parcelaId = $p['id'];
                            break;
                        }
                    }
                }
                
                if (!$parcelaId) {
                    $parcelasErros[] = "Parcela não identificada: " . json_encode($parcelaInput);
                    continue;
                }
                
                // Verifica se a parcela pertence a esta conta
                $parcela = $parcelaModel->findById($parcelaId);
                if (!$parcela || $parcela['conta_receber_id'] != $id) {
                    $parcelasErros[] = "Parcela #{$parcelaId} não pertence a esta conta";
                    continue;
                }
                
                // Prepara dados para atualização
                $dadosParcelaUpdate = [];
                
                if (isset($parcelaInput['status'])) {
                    $dadosParcelaUpdate['status'] = $parcelaInput['status'];
                }
                if (isset($parcelaInput['valor_recebido'])) {
                    $dadosParcelaUpdate['valor_recebido'] = $parcelaInput['valor_recebido'];
                }
                if (isset($parcelaInput['data_recebimento'])) {
                    $dadosParcelaUpdate['data_recebimento'] = $parcelaInput['data_recebimento'];
                }
                if (isset($parcelaInput['data_vencimento'])) {
                    $dadosParcelaUpdate['data_vencimento'] = $parcelaInput['data_vencimento'];
                }
                if (isset($parcelaInput['valor_parcela'])) {
                    $dadosParcelaUpdate['valor_parcela'] = $parcelaInput['valor_parcela'];
                }
                if (isset($parcelaInput['desconto'])) {
                    $dadosParcelaUpdate['desconto'] = $parcelaInput['desconto'];
                }
                if (isset($parcelaInput['observacoes'])) {
                    $dadosParcelaUpdate['observacoes'] = $parcelaInput['observacoes'];
                }
                
                if (!empty($dadosParcelaUpdate)) {
                    $parcelaModel->update($parcelaId, $dadosParcelaUpdate);
                    $parcelasAtualizadas++;
                }
            }
            
            // Atualiza status da conta com base nas parcelas
            $this->atualizarStatusContaPorParcelas($id);
        }
        
        // Monta resposta
        $mensagem = 'Conta atualizada com sucesso';
        if ($parcelasAtualizadas > 0) {
            $mensagem .= ". {$parcelasAtualizadas} parcela(s) atualizada(s)";
        }
        
        $data = [
            'success' => true, 
            'message' => $mensagem,
            'parcelas_atualizadas' => $parcelasAtualizadas
        ];
        
        if (!empty($parcelasErros)) {
            $data['parcelas_erros'] = $parcelasErros;
        }
        
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasReceberDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaReceber();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $model->softDelete($id, 'Excluído via API');
        
        $data = ['success' => true, 'message' => 'Conta excluída com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // PRODUTOS
    // =====================================================

    public function produtosIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Produto();
        $empresaId = $token['empresa_id'];
        
        $produtos = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $produtos];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function produtosShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Produto();
        $produto = $model->findById($id);
        
        if (!$produto || ($token['empresa_id'] && $produto['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Produto não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $produto];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function produtosStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateProduto($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new Produto();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Produto criado com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    public function produtosUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Produto();
        $produto = $model->findById($id);
        
        if (!$produto || ($token['empresa_id'] && $produto['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Produto não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateProduto($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model->update($id, $input);
        
        $data = ['success' => true, 'message' => 'Produto atualizado com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function produtosDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Produto();
        $produto = $model->findById($id);
        
        if (!$produto || ($token['empresa_id'] && $produto['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Produto não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $model->delete($id);
        
        $data = ['success' => true, 'message' => 'Produto excluído com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // CLIENTES
    // =====================================================

    public function clientesIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Cliente();
        $empresaId = $token['empresa_id'];
        
        $clientes = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $clientes];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function clientesShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Cliente();
        $cliente = $model->findById($id);
        
        if (!$cliente || ($token['empresa_id'] && $cliente['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Cliente não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $cliente];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function clientesStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateCliente($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new Cliente();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Cliente criado com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    public function clientesUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Cliente();
        $cliente = $model->findById($id);
        
        if (!$cliente || ($token['empresa_id'] && $cliente['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Cliente não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateCliente($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model->update($id, $input);
        
        $data = ['success' => true, 'message' => 'Cliente atualizado com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function clientesDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Cliente();
        $cliente = $model->findById($id);
        
        if (!$cliente || ($token['empresa_id'] && $cliente['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Cliente não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $model->delete($id);
        
        $data = ['success' => true, 'message' => 'Cliente excluído com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // FORNECEDORES
    // =====================================================

    public function fornecedoresIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Fornecedor();
        $empresaId = $token['empresa_id'];
        
        $fornecedores = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $fornecedores];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function fornecedoresShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Fornecedor();
        $fornecedor = $model->findById($id);
        
        if (!$fornecedor || ($token['empresa_id'] && $fornecedor['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Fornecedor não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $fornecedor];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function fornecedoresStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateFornecedor($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new Fornecedor();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Fornecedor criado com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    public function fornecedoresUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Fornecedor();
        $fornecedor = $model->findById($id);
        
        if (!$fornecedor || ($token['empresa_id'] && $fornecedor['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Fornecedor não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateFornecedor($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model->update($id, $input);
        
        $data = ['success' => true, 'message' => 'Fornecedor atualizado com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function fornecedoresDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Fornecedor();
        $fornecedor = $model->findById($id);
        
        if (!$fornecedor || ($token['empresa_id'] && $fornecedor['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Fornecedor não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $model->delete($id);
        
        $data = ['success' => true, 'message' => 'Fornecedor excluído com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // VALIDAÇÕES
    // =====================================================

    private function validateContaPagar($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['fornecedor_id'])) {
            $errors['fornecedor_id'] = 'Fornecedor é obrigatório';
        }
        
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'Categoria financeira é obrigatória';
        }
        
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        }
        
        if (empty($data['valor_total']) && empty($data['valor'])) {
            $errors['valor_total'] = 'Valor total é obrigatório';
        }
        
        if (empty($data['data_competencia'])) {
            $errors['data_competencia'] = 'Data de competência é obrigatória';
        }
        
        if (empty($data['data_vencimento'])) {
            $errors['data_vencimento'] = 'Data de vencimento é obrigatória';
        }
        
        return $errors;
    }

    private function validateContaReceber($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['cliente_id'])) {
            $errors['cliente_id'] = 'Cliente é obrigatório';
        }
        
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'Categoria financeira é obrigatória';
        }
        
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        }
        
        if (empty($data['valor_total']) && empty($data['valor'])) {
            $errors['valor_total'] = 'Valor total é obrigatório';
        }
        
        if (empty($data['data_competencia'])) {
            $errors['data_competencia'] = 'Data de competência é obrigatória';
        }
        
        if (empty($data['data_vencimento'])) {
            $errors['data_vencimento'] = 'Data de vencimento é obrigatória';
        }
        
        // Nota: centro_custo_id é opcional para contas a receber
        
        return $errors;
    }

    private function validateProduto($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        
        if (!isset($data['preco_venda'])) {
            $errors['preco_venda'] = 'Preço de venda é obrigatório';
        }
        
        return $errors;
    }

    private function validateCliente($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome_razao_social'])) {
            $errors['nome_razao_social'] = 'Nome/Razão Social é obrigatório';
        }
        
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'Tipo é obrigatório';
        }
        
        return $errors;
    }

    private function validateFornecedor($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome_razao_social'])) {
            $errors['nome_razao_social'] = 'Nome/Razão Social é obrigatório';
        }
        
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'Tipo é obrigatório';
        }
        
        return $errors;
    }

    // =====================================================
    // MOVIMENTAÇÕES DE CAIXA
    // =====================================================

    public function movimentacoesIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new MovimentacaoCaixa();
        $empresaId = $token['empresa_id'];
        
        $movimentacoes = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $movimentacoes];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function movimentacoesShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new MovimentacaoCaixa();
        $movimentacao = $model->findById($id);
        
        if (!$movimentacao || ($token['empresa_id'] && $movimentacao['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Movimentação não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $movimentacao];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function movimentacoesStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateMovimentacao($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new MovimentacaoCaixa();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Movimentação criada com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    // =====================================================
    // CATEGORIAS FINANCEIRAS
    // =====================================================

    public function categoriasIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new CategoriaFinanceira();
        $empresaId = $token['empresa_id'];
        
        $categorias = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $categorias];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function categoriasShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new CategoriaFinanceira();
        $categoria = $model->findById($id);
        
        if (!$categoria || ($token['empresa_id'] && $categoria['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Categoria não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $categoria];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function categoriasStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateCategoria($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new CategoriaFinanceira();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Categoria criada com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    // =====================================================
    // CENTROS DE CUSTO
    // =====================================================

    public function centrosCustoIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new CentroCusto();
        $empresaId = $token['empresa_id'];
        
        $centros = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $centros];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function centrosCustoShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new CentroCusto();
        $centro = $model->findById($id);
        
        if (!$centro || ($token['empresa_id'] && $centro['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Centro de custo não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $centro];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function centrosCustoStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateCentroCusto($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new CentroCusto();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Centro de custo criado com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    // =====================================================
    // CONTAS BANCÁRIAS
    // =====================================================

    public function contasBancariasIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaBancaria();
        $empresaId = $token['empresa_id'];
        
        $contas = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $contas];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasBancariasShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new ContaBancaria();
        $conta = $model->findById($id);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta bancária não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $conta];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function contasBancariasStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateContaBancaria($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $input['empresa_id'] = $token['empresa_id'] ?? $input['empresa_id'];
        
        $model = new ContaBancaria();
        $id = $model->create($input);
        
        $data = ['success' => true, 'id' => $id, 'message' => 'Conta bancária criada com sucesso'];
        $this->logSuccess($request, 201, $data);
        $response->json($data, 201);
    }

    // =====================================================
    // PEDIDOS
    // =====================================================

    public function pedidosIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new PedidoVinculado();
        $empresaId = $token['empresa_id'];
        
        // Filtros opcionais
        $filters = [];
        if ($request->get('status')) {
            $filters['status'] = $request->get('status');
        }
        if ($request->get('origem')) {
            $filters['origem'] = $request->get('origem');
        }
        if ($request->get('cliente_id')) {
            $filters['cliente_id'] = $request->get('cliente_id');
        }
        if ($request->get('data_inicio')) {
            $filters['data_inicio'] = $request->get('data_inicio');
        }
        if ($request->get('data_fim')) {
            $filters['data_fim'] = $request->get('data_fim');
        }
        
        $pedidos = $model->findAll($empresaId, $filters);
        
        $data = ['success' => true, 'data' => $pedidos, 'total' => count($pedidos)];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function pedidosShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new PedidoVinculado();
        $pedido = $model->findById($id);
        
        if (!$pedido || ($token['empresa_id'] && $pedido['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Pedido não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        // Buscar itens do pedido
        $pedido['itens'] = $model->getItems($id);
        
        $data = ['success' => true, 'data' => $pedido];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function pedidosStore(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validatePedido($input);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        // Garantir empresa_id do token
        $input['empresa_id'] = $token['empresa_id'];
        
        $model = new PedidoVinculado();
        
        try {
            $this->db->beginTransaction();
            
            // Criar pedido
            $pedidoId = $model->create($input);
            
            // Adicionar itens se fornecidos
            if (!empty($input['itens']) && is_array($input['itens'])) {
                foreach ($input['itens'] as $item) {
                    $item['pedido_id'] = $pedidoId;
                    $model->addItem($item);
                }
            }
            
            $this->db->commit();
            
            $data = ['success' => true, 'id' => $pedidoId, 'message' => 'Pedido criado com sucesso'];
            $this->logSuccess($request, 201, $data);
            $response->json($data, 201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $data = ['success' => false, 'error' => 'Erro ao criar pedido: ' . $e->getMessage()];
            $this->logSuccess($request, 500, $data);
            $response->json($data, 500);
        }
    }

    public function pedidosUpdate(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new PedidoVinculado();
        $pedido = $model->findById($id);
        
        if (!$pedido || ($token['empresa_id'] && $pedido['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Pedido não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validatePedido($input, $id);
        if (!empty($errors)) {
            $data = ['success' => false, 'errors' => $errors];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        // Garantir que não altere empresa_id
        unset($input['empresa_id']);
        
        $model->update($id, $input);
        
        $data = ['success' => true, 'message' => 'Pedido atualizado com sucesso'];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function pedidosDelete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new PedidoVinculado();
        $pedido = $model->findById($id);
        
        if (!$pedido || ($token['empresa_id'] && $pedido['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Pedido não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        // Contadores para resposta
        $contasReceberExcluidas = 0;
        $contasPagarExcluidas = 0;
        $itensExcluidos = 0;
        $parcelasExcluidas = 0;
        
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // 1. Excluir contas a receber vinculadas ao pedido
            $sqlContasReceber = "SELECT id FROM contas_receber WHERE pedido_id = :pedido_id AND deleted_at IS NULL";
            $stmtCr = $db->prepare($sqlContasReceber);
            $stmtCr->execute(['pedido_id' => $id]);
            $contasReceberIds = $stmtCr->fetchAll(\PDO::FETCH_COLUMN);
            
            if (!empty($contasReceberIds)) {
                // Excluir parcelas das contas a receber
                $placeholdersCr = implode(',', array_fill(0, count($contasReceberIds), '?'));
                $sqlParcelasCr = "DELETE FROM parcelas_receber WHERE conta_receber_id IN ({$placeholdersCr})";
                $stmtParcelasCr = $db->prepare($sqlParcelasCr);
                $stmtParcelasCr->execute($contasReceberIds);
                $parcelasExcluidas += $stmtParcelasCr->rowCount();
                
                // Soft delete das contas a receber
                $sqlDeleteCr = "UPDATE contas_receber SET deleted_at = NOW(), deleted_reason = 'Pedido excluído via API' WHERE pedido_id = :pedido_id";
                $stmtDeleteCr = $db->prepare($sqlDeleteCr);
                $stmtDeleteCr->execute(['pedido_id' => $id]);
                $contasReceberExcluidas = $stmtDeleteCr->rowCount();
            }
            
            // 2. Excluir contas a pagar vinculadas ao pedido (se existir campo pedido_id)
            try {
                $sqlContasPagar = "SELECT id FROM contas_pagar WHERE pedido_id = :pedido_id AND deleted_at IS NULL";
                $stmtCp = $db->prepare($sqlContasPagar);
                $stmtCp->execute(['pedido_id' => $id]);
                $contasPagarIds = $stmtCp->fetchAll(\PDO::FETCH_COLUMN);
                
                if (!empty($contasPagarIds)) {
                    // Excluir parcelas das contas a pagar
                    $placeholdersCp = implode(',', array_fill(0, count($contasPagarIds), '?'));
                    $sqlParcelasCp = "DELETE FROM parcelas_pagar WHERE conta_pagar_id IN ({$placeholdersCp})";
                    $stmtParcelasCp = $db->prepare($sqlParcelasCp);
                    $stmtParcelasCp->execute($contasPagarIds);
                    $parcelasExcluidas += $stmtParcelasCp->rowCount();
                    
                    // Soft delete das contas a pagar
                    $sqlDeleteCp = "UPDATE contas_pagar SET deleted_at = NOW(), deleted_reason = 'Pedido excluído via API' WHERE pedido_id = :pedido_id";
                    $stmtDeleteCp = $db->prepare($sqlDeleteCp);
                    $stmtDeleteCp->execute(['pedido_id' => $id]);
                    $contasPagarExcluidas = $stmtDeleteCp->rowCount();
                }
            } catch (\Exception $e) {
                // Campo pedido_id pode não existir em contas_pagar, ignorar
            }
            
            // 3. Excluir itens do pedido
            $pedidoItemModel = new PedidoItem();
            $itensExcluidos = $pedidoItemModel->countByPedido($id);
            $pedidoItemModel->deleteByPedido($id);
            
            // 4. Excluir o pedido
            $model->delete($id);
            
            $db->commit();
            
            // Montar mensagem de resposta
            $detalhes = [];
            if ($contasReceberExcluidas > 0) {
                $detalhes[] = "{$contasReceberExcluidas} conta(s) a receber";
            }
            if ($contasPagarExcluidas > 0) {
                $detalhes[] = "{$contasPagarExcluidas} conta(s) a pagar";
            }
            if ($itensExcluidos > 0) {
                $detalhes[] = "{$itensExcluidos} item(ns)";
            }
            if ($parcelasExcluidas > 0) {
                $detalhes[] = "{$parcelasExcluidas} parcela(s)";
            }
            
            $mensagem = 'Pedido excluído com sucesso';
            if (!empty($detalhes)) {
                $mensagem .= '. Também excluídos: ' . implode(', ', $detalhes);
            }
            
            $data = [
                'success' => true, 
                'message' => $mensagem,
                'detalhes' => [
                    'contas_receber_excluidas' => $contasReceberExcluidas,
                    'contas_pagar_excluidas' => $contasPagarExcluidas,
                    'itens_excluidos' => $itensExcluidos,
                    'parcelas_excluidas' => $parcelasExcluidas
                ]
            ];
            $this->logSuccess($request, 200, $data);
            $response->json($data);
            
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $data = ['success' => false, 'error' => 'Erro ao excluir pedido: ' . $e->getMessage()];
            $this->logSuccess($request, 500, $data);
            return $response->json($data, 500);
        }
    }
    
    /**
     * Atualizar frete, desconto e/ou bonificado de um pedido (rota simplificada)
     * PATCH /api/v1/pedidos/{id}/frete
     * Body: { "frete": 15.00, "desconto": 5.00, "bonificado": 1 }
     */
    public function pedidosUpdateFrete(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new PedidoVinculado();
        $pedido = $model->findById($id);
        
        if (!$pedido || ($token['empresa_id'] && $pedido['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Pedido não encontrado'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $frete = isset($input['frete']) ? floatval($input['frete']) : null;
        $desconto = isset($input['desconto']) ? floatval($input['desconto']) : null;
        $bonificado = isset($input['bonificado']) ? intval($input['bonificado']) : null;
        
        if ($frete === null && $desconto === null && $bonificado === null) {
            $data = ['success' => false, 'error' => 'Informe ao menos um dos campos: frete, desconto ou bonificado'];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $model->updateFreteDesconto($id, $frete, $desconto, $bonificado);
        
        // Buscar pedido atualizado
        $pedidoAtualizado = $model->findById($id);
        
        // Calcular lucro atualizado
        $valorTotal = floatval($pedidoAtualizado['valor_total'] ?? 0);
        $custoTotal = floatval($pedidoAtualizado['valor_custo_total'] ?? 0);
        $freteAtual = floatval($pedidoAtualizado['frete'] ?? 0);
        $lucro = $valorTotal - $custoTotal - $freteAtual;
        $margem = $valorTotal > 0 ? round(($lucro / $valorTotal) * 100, 2) : 0;
        
        $data = [
            'success' => true, 
            'message' => 'Pedido atualizado com sucesso',
            'pedido' => [
                'id' => $pedidoAtualizado['id'],
                'numero_pedido' => $pedidoAtualizado['numero_pedido'],
                'valor_total' => $pedidoAtualizado['valor_total'],
                'valor_custo_total' => $pedidoAtualizado['valor_custo_total'],
                'frete' => $pedidoAtualizado['frete'],
                'desconto' => $pedidoAtualizado['desconto'],
                'bonificado' => $pedidoAtualizado['bonificado'],
                'lucro' => $lucro,
                'margem_lucro' => $margem
            ]
        ];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // VALIDAÇÕES ADICIONAIS
    // =====================================================

    private function validatePedido($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['data_pedido'])) {
            $errors['data_pedido'] = 'Data do pedido é obrigatória';
        }
        
        if (empty($data['total'])) {
            $errors['total'] = 'Total do pedido é obrigatório';
        }
        
        if (!empty($data['itens']) && !is_array($data['itens'])) {
            $errors['itens'] = 'Itens devem ser um array';
        }
        
        return $errors;
    }

    private function validateMovimentacao($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'Descrição é obrigatória';
        }
        
        if (empty($data['valor'])) {
            $errors['valor'] = 'Valor é obrigatório';
        }
        
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'Tipo é obrigatório';
        }
        
        return $errors;
    }

    private function validateCategoria($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        
        if (empty($data['tipo'])) {
            $errors['tipo'] = 'Tipo é obrigatório';
        }
        
        return $errors;
    }

    private function validateCentroCusto($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['nome'])) {
            $errors['nome'] = 'Nome é obrigatório';
        }
        
        return $errors;
    }

    private function validateContaBancaria($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['banco'])) {
            $errors['banco'] = 'Banco é obrigatório';
        }
        
        if (empty($data['agencia'])) {
            $errors['agencia'] = 'Agência é obrigatória';
        }
        
        if (empty($data['conta'])) {
            $errors['conta'] = 'Conta é obrigatória';
        }
        
        return $errors;
    }

    // =====================================================
    // EMPRESAS (GET para consulta)
    // =====================================================

    public function empresasIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Empresa();
        
        // Se o token está vinculado a uma empresa específica, só retorna ela
        if ($token['empresa_id']) {
            $empresa = $model->findById($token['empresa_id']);
            $empresas = $empresa ? [$empresa] : [];
        } else {
            // Token com acesso a todas as empresas
            $empresas = $model->findAll(['ativo' => 1]);
        }
        
        // Retornar apenas campos essenciais
        $resultado = array_map(function($emp) {
            return [
                'id' => $emp['id'],
                'codigo' => $emp['codigo'],
                'razao_social' => $emp['razao_social'],
                'nome_fantasia' => $emp['nome_fantasia'],
                'cnpj' => $emp['cnpj'],
                'ativo' => $emp['ativo']
            ];
        }, $empresas);
        
        $data = ['success' => true, 'data' => $resultado, 'total' => count($resultado)];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function empresasShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new Empresa();
        $empresa = $model->findById($id);
        
        if (!$empresa || ($token['empresa_id'] && $empresa['id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Empresa não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $empresa];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // FORMAS DE PAGAMENTO (GET para consulta)
    // =====================================================

    public function formasPagamentoIndex(Request $request, Response $response)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new FormaPagamento();
        $empresaId = $token['empresa_id'];
        
        $formas = $model->findAll($empresaId);
        
        $data = ['success' => true, 'data' => $formas, 'total' => count($formas)];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function formasPagamentoShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $model = new FormaPagamento();
        $forma = $model->findById($id);
        
        if (!$forma || ($token['empresa_id'] && $forma['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Forma de pagamento não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $forma];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    // =====================================================
    // PARCELAS DE CONTAS A RECEBER
    // =====================================================

    public function parcelasReceberIndex(Request $request, Response $response, $contaId)
    {
        $token = $this->authenticate($request, $response);
        
        // Verificar se a conta existe e pertence à empresa do token
        $contaModel = new ContaReceber();
        $conta = $contaModel->findById($contaId);
        
        if (!$conta || ($token['empresa_id'] && $conta['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Conta não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $parcelaModel = new ParcelaReceber();
        $parcelas = $parcelaModel->findByContaReceber($contaId);
        $resumo = $parcelaModel->getResumoByContaReceber($contaId);
        
        $data = [
            'success' => true, 
            'data' => $parcelas, 
            'resumo' => $resumo,
            'total' => count($parcelas)
        ];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function parcelasReceberShow(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $parcelaModel = new ParcelaReceber();
        $parcela = $parcelaModel->findById($id);
        
        if (!$parcela || ($token['empresa_id'] && $parcela['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Parcela não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $data = ['success' => true, 'data' => $parcela];
        $this->logSuccess($request, 200, $data);
        $response->json($data);
    }

    public function parcelasReceberBaixar(Request $request, Response $response, $id)
    {
        $token = $this->authenticate($request, $response);
        
        $parcelaModel = new ParcelaReceber();
        $parcela = $parcelaModel->findById($id);
        
        if (!$parcela || ($token['empresa_id'] && $parcela['empresa_id'] != $token['empresa_id'])) {
            $data = ['success' => false, 'error' => 'Parcela não encontrada'];
            $this->logSuccess($request, 404, $data);
            return $response->json($data, 404);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Opção para sobrescrever recebimento anterior
        $sobrescrever = isset($input['sobrescrever']) && $input['sobrescrever'] === true;
        
        // Se sobrescrever, considera o valor da parcela como saldo; senão, calcula saldo restante
        if ($sobrescrever) {
            $saldoRestante = $parcela['valor_parcela'];
        } else {
            $saldoRestante = $parcela['valor_parcela'] - ($parcela['valor_recebido'] ?? 0);
        }
        
        $valorRecebido = $input['valor_recebido'] ?? $saldoRestante;
        $dataRecebimento = $input['data_recebimento'] ?? date('Y-m-d');
        $formaRecebimentoId = $input['forma_recebimento_id'] ?? null;
        $contaBancariaId = $input['conta_bancaria_id'] ?? null;
        $observacoes = $input['observacoes'] ?? null;
        
        // Validar valor
        if ($valorRecebido <= 0) {
            $data = ['success' => false, 'error' => 'Valor do recebimento deve ser maior que zero'];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        // Se saldo restante é 0 e não está sobrescrevendo, informar que já foi recebida
        if ($saldoRestante <= 0 && !$sobrescrever) {
            $data = [
                'success' => false, 
                'error' => 'Parcela já foi totalmente recebida. Use "sobrescrever": true para substituir o recebimento anterior.',
                'valor_parcela' => floatval($parcela['valor_parcela']),
                'valor_recebido' => floatval($parcela['valor_recebido'] ?? 0),
                'status' => $parcela['status']
            ];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        if ($valorRecebido > $saldoRestante && !$sobrescrever) {
            $data = ['success' => false, 'error' => 'Valor do recebimento não pode ser maior que o saldo restante (R$ ' . number_format($saldoRestante, 2, ',', '.') . '). Use "sobrescrever": true para substituir.'];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        // Se sobrescrever, valor não pode ser maior que valor da parcela
        if ($sobrescrever && $valorRecebido > $parcela['valor_parcela']) {
            $data = ['success' => false, 'error' => 'Valor do recebimento não pode ser maior que o valor da parcela (R$ ' . number_format($parcela['valor_parcela'], 2, ',', '.') . ')'];
            $this->logSuccess($request, 400, $data);
            return $response->json($data, 400);
        }
        
        $result = $parcelaModel->registrarRecebimento($id, $valorRecebido, $dataRecebimento, $formaRecebimentoId, $contaBancariaId, $sobrescrever);
        
        if ($result) {
            // Atualizar status da conta principal
            $contaId = $parcela['conta_receber_id'];
            $this->atualizarStatusContaPorParcelas($contaId);
            
            // Buscar dados atualizados
            $parcelaAtualizada = $parcelaModel->findById($id);
            $resumo = $parcelaModel->getResumoByContaReceber($contaId);
            
            $data = [
                'success' => true, 
                'message' => 'Recebimento registrado com sucesso',
                'parcela' => [
                    'id' => $parcelaAtualizada['id'],
                    'numero_parcela' => $parcelaAtualizada['numero_parcela'],
                    'valor_parcela' => floatval($parcelaAtualizada['valor_parcela']),
                    'valor_recebido' => floatval($parcelaAtualizada['valor_recebido']),
                    'saldo_restante' => floatval($parcelaAtualizada['valor_parcela'] - $parcelaAtualizada['valor_recebido']),
                    'status' => $parcelaAtualizada['status'],
                    'data_recebimento' => $parcelaAtualizada['data_recebimento']
                ],
                'conta' => [
                    'id' => $contaId,
                    'total_parcelas' => intval($resumo['total_parcelas']),
                    'parcelas_recebidas' => intval($resumo['parcelas_recebidas']),
                    'total_recebido' => floatval($resumo['total_recebido']),
                    'total_pendente' => floatval($resumo['total_valor'] - $resumo['total_recebido'])
                ]
            ];
            $this->logSuccess($request, 200, $data);
            $response->json($data);
        } else {
            $data = ['success' => false, 'error' => 'Erro ao registrar recebimento'];
            $this->logSuccess($request, 500, $data);
            $response->json($data, 500);
        }
    }
    
    /**
     * Atualiza o status da conta a receber baseado nas parcelas
     */
    private function atualizarStatusContaPorParcelas($contaId)
    {
        $parcelaModel = new ParcelaReceber();
        $resumo = $parcelaModel->getResumoByContaReceber($contaId);
        
        if (!$resumo || $resumo['total_parcelas'] == 0) {
            return;
        }
        
        $contaReceberModel = new ContaReceber();
        
        // Se todas as parcelas foram recebidas
        if ($resumo['parcelas_recebidas'] == $resumo['total_parcelas']) {
            $contaReceberModel->atualizarRecebimento(
                $contaId, 
                $resumo['total_recebido'], 
                date('Y-m-d'), 
                'recebido'
            );
        } 
        // Se pelo menos uma parcela foi recebida (parcial ou total)
        elseif ($resumo['total_recebido'] > 0) {
            $contaReceberModel->atualizarRecebimento(
                $contaId, 
                $resumo['total_recebido'], 
                date('Y-m-d'), 
                'parcial'
            );
        }
        // Se nenhuma parcela foi recebida, volta para pendente
        else {
            $contaReceberModel->atualizarRecebimento(
                $contaId, 
                0, 
                null, 
                'pendente'
            );
        }
    }
}
