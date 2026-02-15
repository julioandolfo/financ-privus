<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\TransacaoPendente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\FormaPagamento;
use App\Models\Usuario;

class TransacaoPendenteController extends Controller
{
    private $transacaoModel;
    private $contaPagarModel;
    private $contaReceberModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $fornecedorModel;
    private $clienteModel;
    private $empresasUsuarioIds = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->transacaoModel = new TransacaoPendente();
        $this->contaPagarModel = new ContaPagar();
        $this->contaReceberModel = new ContaReceber();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->centroCustoModel = new CentroCusto();
        $this->fornecedorModel = new Fornecedor();
        $this->clienteModel = new Cliente();
        
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId) {
            $usuarioModel = new Usuario();
            $empresas = $usuarioModel->getEmpresas($usuarioId);
            $this->empresasUsuarioIds = array_map(fn($e) => (int)$e['id'], $empresas);
        }
    }
    
    /**
     * Verifica se o usuário tem acesso a determinada empresa
     */
    private function temAcessoEmpresa($empresaId): bool
    {
        return in_array((int)$empresaId, $this->empresasUsuarioIds);
    }
    
    /**
     * Listar transações pendentes
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        
        // Buscar empresas do usuário
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        
        // Pegar empresa da URL ou primeira empresa
        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }

        // Filtros
        $filtros = [
            'status' => $request->get('status', 'pendente'),
            'tipo' => $request->get('tipo'),
            'banco' => $request->get('banco'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim')
        ];
        
        $transacoes = [];
        $estatisticas = [
            'total' => 0,
            'pendentes' => 0,
            'aprovadas' => 0,
            'ignoradas' => 0,
            'total_debitos' => 0,
            'total_creditos' => 0
        ];
        $categorias = [];
        $centrosCusto = [];
        $fornecedores = [];
        $clientes = [];
        
        $contasBancarias = [];
        $formasPagamento = [];
        
        if ($empresaId) {
            $transacoes = $this->transacaoModel->findByEmpresa($empresaId, $filtros);
            $estatisticas = $this->transacaoModel->getEstatisticas($empresaId);
            
            $categorias = $this->categoriaModel->findAll($empresaId);
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            $fornecedores = $this->fornecedorModel->findAll(['empresa_id' => $empresaId]);
            $clientes = $this->clienteModel->findAll(['empresa_id' => $empresaId]);
            
            $contaBancariaModel = new ContaBancaria();
            $contasBancarias = $contaBancariaModel->findAll($empresaId);
            
            $formaPagamentoModel = new FormaPagamento();
            $formasPagamento = $formaPagamentoModel->findAll();
        }
        
        return $this->render('transacoes_pendentes/index', [
            'transacoes' => $transacoes,
            'estatisticas' => $estatisticas,
            'filtros' => $filtros,
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'fornecedores' => $fornecedores,
            'clientes' => $clientes,
            'contas_bancarias' => $contasBancarias,
            'formas_pagamento' => $formasPagamento,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId
        ]);
    }
    
    /**
     * Exibir detalhes da transação
     */
    public function show(Request $request, Response $response, $id)
    {
        $transacao = $this->transacaoModel->findById($id);
        
        if (!$transacao) {
            $_SESSION['error'] = 'Transação não encontrada';
            return $response->redirect('/transacoes-pendentes');
        }
        
        if (!$this->temAcessoEmpresa($transacao['empresa_id'])) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/transacoes-pendentes');
        }
        $empresaId = $transacao['empresa_id'];
        
        // Buscar categorias e centros de custo para edição
        $categorias = $this->categoriaModel->findAll($empresaId);
        $centrosCusto = $this->centroCustoModel->findAll($empresaId);
        $fornecedores = $this->fornecedorModel->findAll(['empresa_id' => $empresaId]);
        $clientes = $this->clienteModel->findAll(['empresa_id' => $empresaId]);
        
        return $this->render('transacoes_pendentes/show', [
            'transacao' => $transacao,
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'fornecedores' => $fornecedores,
            'clientes' => $clientes
        ]);
    }
    
    /**
     * Aprovar transação (criando conta a pagar/receber)
     */
    public function aprovar(Request $request, Response $response, $id)
    {
        $transacao = $this->transacaoModel->findById($id);
        
        if (!$transacao) {
            return $response->json(['error' => 'Transação não encontrada'], 404);
        }
        
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (!$this->temAcessoEmpresa($transacao['empresa_id'])) {
            return $response->json(['error' => 'Acesso negado'], 403);
        }
        
        $empresaId = $transacao['empresa_id'];
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();
        
        try {
            $dataVencimento = !empty($data['data_vencimento']) ? $data['data_vencimento'] : $transacao['data_transacao'];
            $dataPagamento = !empty($data['data_pagamento']) ? $data['data_pagamento'] : $transacao['data_transacao'];
            $dataCompetencia = !empty($data['data_competencia']) ? $data['data_competencia'] : $transacao['data_transacao'];
            $observacoes = !empty($data['observacoes']) ? $data['observacoes'] : 'Importado via Open Banking';
            
            if ($transacao['tipo'] === 'debito') {
                $contaData = [
                    'empresa_id' => $empresaId,
                    'categoria_id' => $data['categoria_id'] ?? $transacao['categoria_sugerida_id'],
                    'centro_custo_id' => $data['centro_custo_id'] ?? $transacao['centro_custo_sugerido_id'],
                    'fornecedor_id' => $data['fornecedor_id'] ?? $transacao['fornecedor_sugerido_id'],
                    'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                    'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
                    'descricao' => $data['descricao'] ?? $transacao['descricao_original'],
                    'valor' => abs($transacao['valor']),
                    'data_vencimento' => $dataVencimento,
                    'data_competencia' => $dataCompetencia,
                    'data_pagamento' => $dataPagamento,
                    'status' => 'pago',
                    'observacoes' => $observacoes
                ];
                
                $contaId = $this->contaPagarModel->create($contaData);
                
                if ($contaId) {
                    $this->transacaoModel->vincularContaPagar($id, $contaId);
                }
            } else {
                $contaData = [
                    'empresa_id' => $empresaId,
                    'categoria_id' => $data['categoria_id'] ?? $transacao['categoria_sugerida_id'],
                    'centro_custo_id' => $data['centro_custo_id'] ?? $transacao['centro_custo_sugerido_id'],
                    'cliente_id' => $data['cliente_id'] ?? $transacao['cliente_sugerido_id'],
                    'conta_bancaria_id' => $data['conta_bancaria_id'] ?? null,
                    'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
                    'descricao' => $data['descricao'] ?? $transacao['descricao_original'],
                    'valor' => $transacao['valor'],
                    'data_vencimento' => $dataVencimento,
                    'data_competencia' => $dataCompetencia,
                    'data_recebimento' => $dataPagamento,
                    'status' => 'recebido',
                    'observacoes' => $observacoes
                ];
                
                $contaId = $this->contaReceberModel->create($contaData);
                
                if ($contaId) {
                    $this->transacaoModel->vincularContaReceber($id, $contaId);
                }
            }
            
            // Marcar como aprovada
            $this->transacaoModel->aprovar($id, $usuarioId, $data['observacao'] ?? null);
            
            return $response->json([
                'success' => true,
                'message' => 'Transação aprovada e lançada com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            return $response->json([
                'error' => 'Erro ao aprovar transação: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ignorar transação
     */
    public function ignorar(Request $request, Response $response, $id)
    {
        $transacao = $this->transacaoModel->findById($id);
        
        if (!$transacao) {
            return $response->json(['error' => 'Transação não encontrada'], 404);
        }
        
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (!$this->temAcessoEmpresa($transacao['empresa_id'])) {
            return $response->json(['error' => 'Acesso negado'], 403);
        }
        
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();
        
        if ($this->transacaoModel->ignorar($id, $usuarioId, $data['observacao'] ?? null)) {
            return $response->json([
                'success' => true,
                'message' => 'Transação ignorada com sucesso!'
            ]);
        }
        
        return $response->json(['error' => 'Erro ao ignorar transação'], 500);
    }
    
    /**
     * Aprovar múltiplas transações em lote
     */
    public function aprovarLote(Request $request, Response $response)
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (empty($data['transacoes']) || !is_array($data['transacoes'])) {
            return $response->json(['error' => 'Nenhuma transação selecionada'], 400);
        }
        
        $aprovadas = 0;
        $erros = [];
        
        foreach ($data['transacoes'] as $transacaoId) {
            try {
                $transacao = $this->transacaoModel->findById($transacaoId);
                
                if (!$transacao || !$this->temAcessoEmpresa($transacao['empresa_id'])) {
                    $erros[] = "Transação #{$transacaoId}: não encontrada ou sem permissão";
                    continue;
                }
                
                // Criar conta a pagar/receber usando dados sugeridos
                $txEmpresaId = $transacao['empresa_id'];
                if ($transacao['tipo'] === 'debito') {
                    $contaData = [
                        'empresa_id' => $txEmpresaId,
                        'categoria_id' => $transacao['categoria_sugerida_id'],
                        'centro_custo_id' => $transacao['centro_custo_sugerido_id'],
                        'fornecedor_id' => $transacao['fornecedor_sugerido_id'],
                        'descricao' => $transacao['descricao_original'],
                        'valor' => abs($transacao['valor']),
                        'data_vencimento' => $transacao['data_transacao'],
                        'data_pagamento' => $transacao['data_transacao'],
                        'status' => 'pago',
                        'observacoes' => 'Importado via Open Banking (aprovação em lote)'
                    ];
                    
                    $contaId = $this->contaPagarModel->create($contaData);
                    if ($contaId) {
                        $this->transacaoModel->vincularContaPagar($transacaoId, $contaId);
                    }
                } else {
                    $contaData = [
                        'empresa_id' => $txEmpresaId,
                        'categoria_id' => $transacao['categoria_sugerida_id'],
                        'centro_custo_id' => $transacao['centro_custo_sugerido_id'],
                        'cliente_id' => $transacao['cliente_sugerido_id'],
                        'descricao' => $transacao['descricao_original'],
                        'valor' => $transacao['valor'],
                        'data_vencimento' => $transacao['data_transacao'],
                        'data_recebimento' => $transacao['data_transacao'],
                        'status' => 'recebido',
                        'observacoes' => 'Importado via Open Banking (aprovação em lote)'
                    ];
                    
                    $contaId = $this->contaReceberModel->create($contaData);
                    if ($contaId) {
                        $this->transacaoModel->vincularContaReceber($transacaoId, $contaId);
                    }
                }
                
                $this->transacaoModel->aprovar($transacaoId, $usuarioId);
                $aprovadas++;
                
            } catch (\Exception $e) {
                $erros[] = "Transação #{$transacaoId}: " . $e->getMessage();
            }
        }
        
        return $response->json([
            'success' => true,
            'aprovadas' => $aprovadas,
            'erros' => $erros,
            'message' => "{$aprovadas} transações aprovadas" . (count($erros) > 0 ? " com " . count($erros) . " erros" : "")
        ]);
    }
    
    /**
     * Ignorar múltiplas transações em lote
     */
    public function ignorarLote(Request $request, Response $response)
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (empty($data['transacoes']) || !is_array($data['transacoes'])) {
            return $response->json(['error' => 'Nenhuma transação selecionada'], 400);
        }
        
        $ignoradas = 0;
        $erros = [];
        
        foreach ($data['transacoes'] as $transacaoId) {
            try {
                $transacao = $this->transacaoModel->findById($transacaoId);
                
                if (!$transacao || !$this->temAcessoEmpresa($transacao['empresa_id'])) {
                    $erros[] = "Transação #{$transacaoId}: não encontrada ou sem permissão";
                    continue;
                }
                
                $this->transacaoModel->ignorar($transacaoId, $usuarioId, $data['observacao'] ?? 'Ignorada em lote');
                $ignoradas++;
                
            } catch (\Exception $e) {
                $erros[] = "Transação #{$transacaoId}: " . $e->getMessage();
            }
        }
        
        return $response->json([
            'success' => true,
            'ignoradas' => $ignoradas,
            'erros' => $erros,
            'message' => "{$ignoradas} transações ignoradas" . (count($erros) > 0 ? " com " . count($erros) . " erros" : "")
        ]);
    }
}
