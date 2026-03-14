<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaPagar;
use App\Models\RateioPagamento;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\FormaPagamento;
use App\Models\ContaBancaria;
use App\Models\TransacaoPendente;
use includes\services\RateioService;
use includes\services\MovimentacaoService;
use App\Models\LogSistema;

class ContaPagarController extends Controller
{
    private $contaPagarModel;
    private $rateioModel;
    private $empresaModel;
    private $fornecedorModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $formaPagamentoModel;
    private $contaBancariaModel;
    private $rateioService;
    private $movimentacaoService;

    public function index(Request $request, Response $response)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            
            // Prepara filtros
            $filters = [];
            
            // Filtro de empresa
            $empresaId = $request->get('empresa_id');
            if ($empresaId) {
                $filters['empresa_id'] = $empresaId;
            }
            
            // Filtros básicos
            if ($request->get('status')) {
                $filters['status'] = $request->get('status');
            }
            
            // Filtros por ID ou por Nome (consolidado)
            if ($request->get('fornecedor_id')) {
                $filters['fornecedor_id'] = $request->get('fornecedor_id');
            } elseif ($request->get('fornecedor_nome')) {
                $filters['fornecedor_nome'] = $request->get('fornecedor_nome');
            }
            
            if ($request->get('categoria_id')) {
                $filters['categoria_id'] = $request->get('categoria_id');
            } elseif ($request->get('categoria_nome')) {
                $filters['categoria_nome'] = $request->get('categoria_nome');
            }
            
            if ($request->get('centro_custo_id')) {
                $filters['centro_custo_id'] = $request->get('centro_custo_id');
            } elseif ($request->get('centro_custo_nome')) {
                $filters['centro_custo_nome'] = $request->get('centro_custo_nome');
            }
            
            if ($request->get('forma_pagamento_id')) {
                $filters['forma_pagamento_id'] = $request->get('forma_pagamento_id');
            }
            if ($request->get('tipo_custo')) {
                $filters['tipo_custo'] = $request->get('tipo_custo');
            }
            
            // Filtros de data
            if ($request->get('data_inicio')) {
                $filters['data_vencimento_inicio'] = $request->get('data_inicio');
            }
            if ($request->get('data_fim')) {
                $filters['data_vencimento_fim'] = $request->get('data_fim');
            }
            
            // Filtros de valor
            if ($request->get('valor_min')) {
                $filters['valor_min'] = $request->get('valor_min');
            }
            if ($request->get('valor_max')) {
                $filters['valor_max'] = $request->get('valor_max');
            }
            
            // Filtro de parcelamento
            if ($request->get('parcelamento')) {
                $filters['parcelamento'] = $request->get('parcelamento');
            }
            
            // Busca
            if ($request->get('search')) {
                $filters['search'] = $request->get('search');
            }
            
            // Ordenação
            if ($request->get('ordenar')) {
                $filters['ordenar'] = $request->get('ordenar');
            }
            
            // Paginação
            $porPagina = $request->get('por_pagina') ?? 25;
            $paginaAtual = $request->get('pagina') ?? 1;
            $paginaAtual = max(1, (int)$paginaAtual);
            
            // Busca total de registros para calcular paginação
            $totalRegistros = $this->contaPagarModel->countWithFilters($filters);
            
            // Calcula paginação
            $totalPaginas = 1;
            $offset = 0;
            
            if ($porPagina !== 'todos') {
                $porPagina = (int) $porPagina;
                $totalPaginas = ceil($totalRegistros / $porPagina);
                
                // Ajusta página atual se estiver fora do range
                if ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
                    $paginaAtual = $totalPaginas;
                }
                
                $offset = ($paginaAtual - 1) * $porPagina;
                $filters['limite'] = $porPagina;
                $filters['offset'] = $offset;
            }
            
            $contasPagar = $this->contaPagarModel->findAll($filters);
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $this->contaBancariaModel = new ContaBancaria();
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            // Retorna os filtros aplicados para a view
            $filtersApplied = $request->all();
            
            // Contar transações pendentes de aprovação (tipo débito = despesas)
            $transacaoPendenteModel = new TransacaoPendente();
            $transacoesPendentesCount = 0;
            if ($empresaId) {
                $transacoesPendentesCount = $transacaoPendenteModel->countByEmpresa($empresaId, 'pendente');
            } elseif ($empresaAtual) {
                $transacoesPendentesCount = $transacaoPendenteModel->countByEmpresa($empresaAtual, 'pendente');
            }
            
            return $this->render('contas_pagar/index', [
                'title' => 'Contas a Pagar',
                'contasPagar' => $contasPagar,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias ?? [],
                'filters' => $filtersApplied,
                'transacoes_pendentes_count' => $transacoesPendentesCount,
                'paginacao' => [
                    'total_registros' => $totalRegistros,
                    'por_pagina' => $porPagina,
                    'pagina_atual' => $paginaAtual,
                    'total_paginas' => $totalPaginas,
                    'offset' => $offset
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar contas a pagar: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    /**
     * Atualizar categoria em massa
     */
    public function atualizarCategoriaMassa(Request $request, Response $response)
    {
        $ids = $request->post('ids', []);
        $categoriaId = (int) $request->post('categoria_id');
        
        if (empty($ids) || !$categoriaId) {
            $_SESSION['error'] = 'Selecione pelo menos uma conta e uma categoria.';
            $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'categoria_id']))));
            return;
        }
        
        $contaPagarModel = new ContaPagar();
        $categoriaModel = new CategoriaFinanceira();
        $categoria = $categoriaModel->findById($categoriaId);
        
        if (!$categoria || ($categoria['tipo'] ?? '') !== 'despesa') {
            $_SESSION['error'] = 'Categoria inválida ou não é do tipo despesa.';
            $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'categoria_id']))));
            return;
        }
        
        $atualizadas = 0;
        $ignoradas = 0;
        $categoriaEmpresaId = (int)($categoria['empresa_id'] ?? 0);
        
        foreach ($ids as $id) {
            $id = (int) $id;
            if (!$id) continue;
            $conta = $contaPagarModel->findById($id);
            if (!$conta) {
                $ignoradas++;
                continue;
            }
            $contaEmpresaId = (int)($conta['empresa_id'] ?? 0);
            if ($categoriaEmpresaId && $contaEmpresaId !== $categoriaEmpresaId) {
                $ignoradas++;
                continue;
            }
            if ($contaPagarModel->updateCategoria($id, $categoriaId)) {
                $atualizadas++;
            }
        }
        
        $msg = "{$atualizadas} conta(s) atualizada(s).";
        if ($ignoradas > 0) {
            $msg .= " {$ignoradas} ignorada(s) (categoria de outra empresa).";
        }
        $_SESSION['success'] = $msg;
        $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'categoria_id']))));
    }

    /**
     * Atualizar data de pagamento em massa
     */
    public function atualizarDataMassa(Request $request, Response $response)
    {
        $ids = $request->post('ids', []);
        $tipoData = $request->post('tipo_data', 'manual');
        $dataManual = $request->post('data_pagamento_massa');

        if (empty($ids)) {
            $_SESSION['error'] = 'Selecione pelo menos uma conta.';
            $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'tipo_data', 'data_pagamento_massa']))));
            return;
        }

        if ($tipoData === 'manual' && empty($dataManual)) {
            $_SESSION['error'] = 'Informe a data de pagamento.';
            $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'tipo_data', 'data_pagamento_massa']))));
            return;
        }

        $contaPagarModel = new ContaPagar();
        $atualizadas = 0;
        $ignoradas = 0;

        foreach ($ids as $id) {
            $id = (int) $id;
            if (!$id) continue;

            $conta = $contaPagarModel->findById($id);
            if (!$conta) {
                $ignoradas++;
                continue;
            }

            $data = $tipoData === 'vencimento'
                ? $conta['data_vencimento']
                : $dataManual;

            if (empty($data)) {
                $ignoradas++;
                continue;
            }

            if ($contaPagarModel->atualizarDataPagamento($id, $data)) {
                $atualizadas++;
            } else {
                $ignoradas++;
            }
        }

        $redirectParams = array_diff_key($request->all(), array_flip(['ids', 'tipo_data', 'data_pagamento_massa']));

        if ($atualizadas === 0) {
            $_SESSION['error'] = "Nenhuma data foi atualizada." . ($ignoradas > 0 ? " {$ignoradas} conta(s) não encontrada(s)." : '');
        } else {
            $msg = "Data de pagamento atualizada em {$atualizadas} conta(s).";
            if ($ignoradas > 0) {
                $msg .= " {$ignoradas} não atualizada(s).";
            }
            $_SESSION['success'] = $msg;
        }
        $response->redirect('/contas-pagar?' . http_build_query($redirectParams));
    }

    /**
     * Efetuar baixa (pagamento) em massa
     */
    public function efetuarBaixaMassa(Request $request, Response $response)
    {
        $ids = $request->post('ids', []);
        $dataPagamento = $request->post('data_pagamento');
        $tipoBaixaData = $request->post('tipo_data_baixa', 'manual');
        $formaPagamentoId = $request->post('forma_pagamento_id');
        $contaBancariaId = $request->post('conta_bancaria_id');

        // Data é obrigatória apenas no modo manual
        $dataObrigatoria = ($tipoBaixaData !== 'vencimento') && !$dataPagamento;

        if (empty($ids) || $dataObrigatoria || !$formaPagamentoId || !$contaBancariaId) {
            $_SESSION['error'] = 'Selecione contas e preencha forma de pagamento e conta bancária.';
            $response->redirect('/contas-pagar?' . http_build_query(array_diff_key($request->all(), array_flip(['ids', 'data_pagamento', 'forma_pagamento_id', 'conta_bancaria_id']))));
            return;
        }
        
        $contaBancariaModel = new ContaBancaria();
        $contaBancaria = $contaBancariaModel->findById($contaBancariaId);
        if (!$contaBancaria) {
            $_SESSION['error'] = 'Conta bancária inválida.';
            $response->redirect('/contas-pagar');
            return;
        }
        
        $empresaIdContaBancaria = (int)($contaBancaria['empresa_id'] ?? 0);
        $contaPagarModel = new ContaPagar();
        $movimentacaoService = new MovimentacaoService();
        
        $atualizadas = 0;
        $ignoradas = 0;
        $erros = [];
        
        foreach ($ids as $id) {
            $id = (int) $id;
            if (!$id) continue;
            
            $conta = $contaPagarModel->findById($id);
            if (!$conta) {
                $ignoradas++;
                continue;
            }
            if ($conta['status'] === 'pago') {
                $ignoradas++;
                continue;
            }
            // Só verifica empresa quando a conta bancária tem empresa definida
            if ($empresaIdContaBancaria > 0 && (int)($conta['empresa_id'] ?? 0) !== $empresaIdContaBancaria) {
                $ignoradas++;
                continue;
            }
            
            $valorRestante = floatval($conta['valor_total']) - floatval($conta['valor_pago'] ?? 0);
            if ($valorRestante <= 0) {
                $ignoradas++;
                continue;
            }

            $dataEfetiva = ($tipoBaixaData === 'vencimento')
                ? $conta['data_vencimento']
                : $dataPagamento;

            if (empty($dataEfetiva)) {
                $erros[] = "Conta #{$id}: data de vencimento não encontrada.";
                continue;
            }
            
            try {
                $valorPago = floatval($conta['valor_pago'] ?? 0) + $valorRestante;
                $status = 'pago';
                
                $dadosAntes = $conta;
                $result = $contaPagarModel->atualizarPagamento($id, $valorPago, $dataEfetiva, $status);
                
                if (!$result) {
                    $erros[] = "Conta #{$id}: falha ao atualizar no banco de dados.";
                    continue;
                }
                
                \App\Models\Auditoria::registrar(
                    'contas_pagar',
                    $id,
                    'make_payment',
                    $dadosAntes,
                    $contaPagarModel->findById($id),
                    "Pagamento em massa: R$ " . number_format($valorRestante, 2, ',', '.')
                );
                
                $dadosBaixa = [
                    'empresa_id' => $conta['empresa_id'],
                    'categoria_id' => $conta['categoria_id'],
                    'centro_custo_id' => $conta['centro_custo_id'],
                    'conta_bancaria_id' => $contaBancariaId,
                    'descricao' => "Pagamento: " . ($conta['descricao'] ?? ''),
                    'valor' => $valorRestante,
                    'data_movimento' => $dataEfetiva,
                    'data_competencia' => $conta['data_competencia'],
                    'forma_pagamento_id' => $formaPagamentoId,
                    'observacoes' => 'Baixa em massa'
                ];
                
                $movimentacaoService->criarMovimentacaoPagamento($id, $dadosBaixa);
                $atualizadas++;
            } catch (\Exception $e) {
                $erros[] = "Conta #{$id}: " . $e->getMessage();
            }
        }
        
        $redirectParams = array_diff_key($request->all(), array_flip(['ids', 'data_pagamento', 'forma_pagamento_id', 'conta_bancaria_id']));

        if ($atualizadas === 0 && empty($erros)) {
            $msg = "Nenhuma conta foi baixada.";
            if ($ignoradas > 0) {
                $msg .= " {$ignoradas} já paga(s) ou de outra empresa.";
            }
            $_SESSION['error'] = $msg;
        } else {
            $msg = "{$atualizadas} conta(s) paga(s) com sucesso.";
            if ($ignoradas > 0) {
                $msg .= " {$ignoradas} ignorada(s) (já pagas ou de outra empresa).";
            }
            if (!empty($erros)) {
                $msg .= " Erros: " . implode('; ', array_slice($erros, 0, 3));
            }
            $_SESSION['success'] = $msg;
        }
        $response->redirect('/contas-pagar?' . http_build_query($redirectParams));
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();
            
            return $this->render('contas_pagar/create', [
                'title' => 'Nova Conta a Pagar',
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
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
                $response->redirect('/contas-pagar/create');
                return;
            }
            
            // Prepara dados
            $data['usuario_cadastro_id'] = $_SESSION['usuario_id'];
            $data['status'] = 'pendente';
            $data['valor_pago'] = 0;
            
            $this->contaPagarModel = new ContaPagar();
            
            // Verifica se é parcelado
            if (isset($data['eh_parcelado']) && $data['eh_parcelado'] == 1) {
                // Valida campos de parcelamento
                if (empty($data['parcelas_quantidade']) || $data['parcelas_quantidade'] < 2) {
                    $this->session->set('errors', ['parcelas_quantidade' => 'Informe pelo menos 2 parcelas']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                if (empty($data['parcelas_primeiro_vencimento'])) {
                    $this->session->set('errors', ['parcelas_primeiro_vencimento' => 'Informe a data do primeiro vencimento']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                // Prepara configuração de parcelas
                $configParcelas = [
                    'quantidade' => $data['parcelas_quantidade'],
                    'primeiro_vencimento' => $data['parcelas_primeiro_vencimento'],
                    'intervalo' => $data['parcelas_intervalo'] ?? 'mensal',
                    'intervalo_dias' => $data['parcelas_intervalo_dias'] ?? 30,
                    'tipo_valor' => $data['parcelas_tipo_valor'] ?? 'diluido',
                    'status_inicial' => $data['parcelas_status_inicial'] ?? 'pendente'
                ];
                
                // Cria as parcelas
                $resultado = $this->contaPagarModel->criarParcelas($data, $configParcelas);
                
                if (empty($resultado['parcelas_ids'])) {
                    throw new \Exception('Erro ao criar parcelas');
                }
                
                $_SESSION['success'] = "Parcelamento criado com sucesso! {$resultado['total_parcelas']} parcelas geradas.";
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Cria conta a pagar normal (não parcelada)
            $contaPagarId = $this->contaPagarModel->create($data);
            
            if (!$contaPagarId) {
                throw new \Exception('Erro ao criar conta a pagar');
            }
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $contaPagarId,
                'create',
                null,
                $this->contaPagarModel->findById($contaPagarId),
                'Conta a pagar criada'
            );
            
            // Se tem rateio, salva os rateios
            $temRateio = !empty($data['tem_rateio']) && $data['tem_rateio'] != '0';
            
            LogSistema::debug('ContaPagar', 'store_rateio_check', 'Verificando rateio no store', [
                'conta_id' => $contaPagarId,
                'tem_rateio_raw' => $data['tem_rateio'] ?? 'NAO_ENVIADO',
                'tem_rateio_parsed' => $temRateio,
                'rateios_presentes' => isset($data['rateios']),
                'rateios_count' => isset($data['rateios']) ? count($data['rateios']) : 0,
                'rateios_data' => $data['rateios'] ?? 'VAZIO',
            ]);
            
            if ($temRateio && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                
                LogSistema::debug('ContaPagar', 'store_rateio_validacao', 'Resultado da validação', [
                    'conta_id' => $contaPagarId,
                    'valor_total' => $data['valor_total'],
                    'erros' => $errosRateio,
                ]);
                
                if (!empty($errosRateio)) {
                    $this->contaPagarModel->cancelar($contaPagarId);
                    
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                $this->rateioModel = new RateioPagamento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                
                LogSistema::debug('ContaPagar', 'store_rateio_preparados', 'Rateios preparados para salvar', [
                    'conta_id' => $contaPagarId,
                    'rateios_preparados' => $rateiosPreparados,
                ]);
                
                $resultado = $this->rateioModel->saveBatch($contaPagarId, $rateiosPreparados, $_SESSION['usuario_id']);
                
                if ($resultado) {
                    $this->contaPagarModel->atualizarRateio($contaPagarId, 1);
                    LogSistema::info('ContaPagar', 'store_rateio_ok', 'Rateio salvo com sucesso', [
                        'conta_id' => $contaPagarId,
                    ]);
                } else {
                    LogSistema::error('ContaPagar', 'store_rateio_falha', 'Falha ao salvar rateio - saveBatch retornou false', [
                        'conta_id' => $contaPagarId,
                    ]);
                }
            } else {
                LogSistema::debug('ContaPagar', 'store_rateio_skip', 'Rateio não ativado ou sem dados', [
                    'conta_id' => $contaPagarId,
                    'temRateio' => $temRateio,
                    'has_rateios' => !empty($data['rateios']),
                ]);
            }
            
            // Se marcou como já pago, registra o pagamento
            if (isset($data['ja_pago']) && $data['ja_pago'] == 1) {
                // Valida dados de pagamento
                if (empty($data['data_pagamento']) || empty($data['forma_pagamento_id']) || empty($data['conta_bancaria_id'])) {
                    $this->contaPagarModel->cancelar($contaPagarId);
                    $this->session->set('errors', ['ja_pago' => 'Para marcar como já pago, preencha a data, forma de pagamento e conta bancária']);
                    $this->session->set('old', $data);
                    $response->redirect('/contas-pagar/create');
                    return;
                }
                
                $valorTotal = floatval($data['valor_total']);
                
                // Atualiza status para pago
                $this->contaPagarModel->atualizarPagamento($contaPagarId, $valorTotal, $data['data_pagamento'], 'pago');
                
                // Cria movimentação de caixa
                $this->movimentacaoService = new MovimentacaoService();
                $dadosBaixa = [
                    'empresa_id' => $data['empresa_id'],
                    'categoria_id' => $data['categoria_id'],
                    'centro_custo_id' => $data['centro_custo_id'] ?? null,
                    'conta_bancaria_id' => $data['conta_bancaria_id'],
                    'descricao' => "Pagamento: " . $data['descricao'],
                    'valor' => $valorTotal,
                    'data_movimento' => $data['data_pagamento'],
                    'data_competencia' => $data['data_competencia'],
                    'forma_pagamento_id' => $data['forma_pagamento_id'],
                    'observacoes' => $data['observacoes_pagamento'] ?? null
                ];
                
                $this->movimentacaoService->criarMovimentacaoPagamento($contaPagarId, $dadosBaixa);
                
                $_SESSION['success'] = 'Conta a pagar criada e registrada como paga com sucesso!';
            } else {
                $_SESSION['success'] = 'Conta a pagar criada com sucesso!';
            }
            
            // Se marcou para tornar recorrente, cria a despesa recorrente
            if (isset($data['tornar_recorrente']) && $data['tornar_recorrente'] == 1) {
                $recorrenciaService = new \Includes\Services\RecorrenciaService();
                $contaPagar = $this->contaPagarModel->findById($contaPagarId);
                
                $configRecorrencia = [
                    'frequencia' => $data['recorrencia_frequencia'] ?? 'mensal',
                    'dia_mes' => $data['recorrencia_dia_mes'] ?? date('j'),
                    'data_inicio' => $data['recorrencia_data_inicio'] ?? date('Y-m-d'),
                    'antecedencia_dias' => 5,
                    'status_inicial' => 'pendente',
                    'criar_automaticamente' => 1
                ];
                
                $recorrenciaService->criarDespesaRecorrenteDeContaPagar($contaPagar, $configRecorrencia, $_SESSION['usuario_id']);
                
                $_SESSION['success'] .= ' Despesa recorrente criada!';
            }
            
            $response->redirect('/contas-pagar');
            
        } catch (\Exception $e) {
            LogSistema::error('ContaPagar', 'store_exception', 'Exceção ao criar conta a pagar', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $_SESSION['error'] = 'Erro ao criar conta a pagar: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/contas-pagar/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaPagar['tem_rateio']) {
                $this->rateioModel = new RateioPagamento();
                $rateios = $this->rateioModel->findByContaPagar($id);
            }
            
            // Busca informações de parcelamento se houver
            $resumoParcelas = null;
            if (!empty($contaPagar['grupo_parcela_id'])) {
                $resumoParcelas = $this->contaPagarModel->getResumoParcelas($contaPagar['grupo_parcela_id']);
            }
            
            // Busca movimentações (histórico de pagamentos)
            $this->movimentacaoService = new MovimentacaoService();
            $movimentacoes = $this->movimentacaoService->buscarPorContaPagar($id);

            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();

            $formasPagamento = $this->formaPagamentoModel->findAll();
            $contasBancarias = $this->contaBancariaModel->findAll();

            $openBaixaModal = $request->get('acao') === 'baixar';
            
            return $this->render('contas_pagar/show', [
                'title' => 'Detalhes da Conta a Pagar',
                'conta' => $contaPagar,
                'rateios' => $rateios,
                'resumoParcelas' => $resumoParcelas,
                'movimentacoes' => $movimentacoes,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'openBaixaModal' => $openBaixaModal
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a pagar: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $this->fornecedorModel = new Fornecedor();
            $this->categoriaModel = new CategoriaFinanceira();
            $this->centroCustoModel = new CentroCusto();
            $this->formaPagamentoModel = new FormaPagamento();
            $this->contaBancariaModel = new ContaBancaria();
            
            $empresaAtual = $_SESSION['usuario_empresa_id'] ?? null;
            $empresas = $this->empresaModel->findAll(['ativo' => 1]);
            $fornecedores = $this->fornecedorModel->findAll(['ativo' => 1]);
            $categorias = $this->categoriaModel->findAll($empresaAtual, 'despesa');
            $centrosCusto = $this->centroCustoModel->findAll($empresaAtual);
            $empresaId = $contaPagar['empresa_id'];
            $formasPagamento = $this->formaPagamentoModel->findAll($empresaId);
            $contasBancarias = $this->contaBancariaModel->findAll($empresaId);
            
            // Busca rateios se houver
            $rateios = [];
            if ($contaPagar['tem_rateio']) {
                $this->rateioModel = new RateioPagamento();
                $rateios = $this->rateioModel->findByContaPagar($id);
            }
            
            return $this->render('contas_pagar/edit', [
                'title' => 'Editar Conta a Pagar',
                'conta' => $contaPagar,
                'empresas' => $empresas,
                'fornecedores' => $fornecedores,
                'categorias' => $categorias,
                'centrosCusto' => $centrosCusto,
                'formasPagamento' => $formasPagamento,
                'contasBancarias' => $contasBancarias,
                'rateios' => $rateios
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar conta a pagar: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
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
                $response->redirect("/contas-pagar/{$id}/edit");
                return;
            }
            
            // Atualiza conta a pagar
            $this->contaPagarModel = new ContaPagar();
            $dadosAntes = $this->contaPagarModel->findById($id);
            $this->contaPagarModel->update($id, $data);
            
            // Registrar auditoria
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $id,
                'update',
                $dadosAntes,
                $this->contaPagarModel->findById($id),
                'Conta a pagar atualizada'
            );
            
            // Atualiza rateios se necessário
            $temRateio = !empty($data['tem_rateio']) && $data['tem_rateio'] != '0';
            
            LogSistema::debug('ContaPagar', 'update_rateio_check', 'Verificando rateio no update', [
                'conta_id' => $id,
                'tem_rateio_raw' => $data['tem_rateio'] ?? 'NAO_ENVIADO',
                'tem_rateio_parsed' => $temRateio,
                'rateios_presentes' => isset($data['rateios']),
                'rateios_count' => isset($data['rateios']) ? count($data['rateios']) : 0,
                'rateios_data' => $data['rateios'] ?? 'VAZIO',
            ]);
            
            if ($temRateio && !empty($data['rateios'])) {
                $this->rateioService = new RateioService();
                
                $errosRateio = $this->rateioService->validarRateios($data['rateios'], $data['valor_total']);
                
                LogSistema::debug('ContaPagar', 'update_rateio_validacao', 'Resultado da validação', [
                    'conta_id' => $id,
                    'valor_total' => $data['valor_total'],
                    'erros' => $errosRateio,
                ]);
                
                if (!empty($errosRateio)) {
                    $this->session->set('errors', ['rateios' => implode(', ', $errosRateio)]);
                    $this->session->set('old', $data);
                    $response->redirect("/contas-pagar/{$id}/edit");
                    return;
                }
                
                $this->rateioModel = new RateioPagamento();
                $rateiosPreparados = $this->rateioService->prepararParaSalvar($data['rateios'], $_SESSION['usuario_id']);
                
                LogSistema::debug('ContaPagar', 'update_rateio_preparados', 'Rateios preparados para salvar', [
                    'conta_id' => $id,
                    'rateios_preparados' => $rateiosPreparados,
                ]);
                
                $resultado = $this->rateioModel->saveBatch($id, $rateiosPreparados, $_SESSION['usuario_id']);
                
                if ($resultado) {
                    $this->contaPagarModel->atualizarRateio($id, 1);
                    LogSistema::info('ContaPagar', 'update_rateio_ok', 'Rateio atualizado com sucesso', [
                        'conta_id' => $id,
                    ]);
                } else {
                    LogSistema::error('ContaPagar', 'update_rateio_falha', 'Falha ao salvar rateio - saveBatch retornou false', [
                        'conta_id' => $id,
                    ]);
                }
            } else {
                LogSistema::debug('ContaPagar', 'update_rateio_removido', 'Rateio desativado ou sem dados - removendo', [
                    'conta_id' => $id,
                    'temRateio' => $temRateio,
                    'has_rateios' => !empty($data['rateios']),
                ]);
                
                $this->rateioModel = new RateioPagamento();
                $this->rateioModel->deleteByContaPagar($id);
                $this->contaPagarModel->atualizarRateio($id, 0);
            }
            
            $_SESSION['success'] = 'Conta a pagar atualizada com sucesso!';
            $response->redirect('/contas-pagar');
            
        } catch (\Exception $e) {
            LogSistema::error('ContaPagar', 'update_exception', 'Exceção ao atualizar conta a pagar', [
                'conta_id' => $id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $_SESSION['error'] = 'Erro ao atualizar conta a pagar: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-pagar/{$id}/edit");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $motivo = $request->post('motivo', 'Registro excluído pelo usuário');
            
            // Soft delete - não remove do banco, apenas marca como deletado
            $this->contaPagarModel->softDelete($id, $motivo);
            $_SESSION['success'] = 'Conta a pagar excluída com sucesso! (É possível restaurar em Registros Deletados)';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir conta a pagar: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar');
    }
    
    /**
     * Restaura uma conta deletada
     */
    public function restore(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            
            $this->contaPagarModel->restore($id);
            $_SESSION['success'] = 'Conta a pagar restaurada com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao restaurar conta a pagar: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar/deletados');
    }
    
    /**
     * Cancela um pagamento já realizado
     */
    public function cancelarPagamento(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $motivo = $request->post('motivo', 'Pagamento cancelado pelo usuário');
            
            $this->contaPagarModel->cancelarPagamento($id, $motivo);
            $_SESSION['success'] = 'Pagamento cancelado com sucesso! A conta voltou para status "Pendente".';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar pagamento: ' . $e->getMessage();
        }
        
        $response->redirect('/contas-pagar/' . $id);
    }
    
    /**
     * Lista registros deletados
     */
    public function deletados(Request $request, Response $response)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            
            // Buscar empresas do usuário
            $empresaModel = new \App\Models\Empresa();
            $empresas = $empresaModel->findAll(['ativo' => 1]);
            $empresasIds = array_column($empresas, 'id');
            
            $contasDeletadas = $this->contaPagarModel->findDeleted($empresasIds);
            
            return $this->render('contas_pagar/deletados', [
                'title' => 'Contas a Pagar - Registros Deletados',
                'contas' => $contasDeletadas
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar registros deletados: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Visualiza histórico de auditoria de uma conta
     */
    public function historico(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $auditoriaModel = new \App\Models\Auditoria();
            
            $conta = $this->contaPagarModel->findByIdWithDeleted($id);
            
            if (!$conta) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            $historico = $auditoriaModel->getHistorico('contas_pagar', $id);
            
            return $this->render('contas_pagar/historico', [
                'title' => 'Histórico de Auditoria - Conta a Pagar #' . $id,
                'conta' => $conta,
                'historico' => $historico
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar histórico: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Formulário de baixa (pagamento) da conta
     */
    public function baixar(Request $request, Response $response, $id)
    {
        try {
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            if (!$contaPagar) {
                $_SESSION['error'] = 'Conta a pagar não encontrada!';
                $response->redirect('/contas-pagar');
                return;
            }
            
            // Não permite baixar conta já paga
            if ($contaPagar['status'] == 'pago') {
                $_SESSION['error'] = 'Esta conta já está paga!';
                $response->redirect('/contas-pagar/' . $id);
                return;
            }
            
            // Redireciona para a tela de detalhes com modal de baixa
            $response->redirect("/contas-pagar/{$id}?acao=baixar");
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário de baixa: ' . $e->getMessage();
            $response->redirect('/contas-pagar');
        }
    }
    
    /**
     * Processa baixa (pagamento) da conta
     */
    public function efetuarBaixa(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            
            // Validações de baixa
            $errors = $this->validateBaixa($data);
            if (!empty($errors)) {
                $this->session->set('errors', $errors);
                $this->session->set('old', $data);
                $response->redirect("/contas-pagar/{$id}?acao=baixar");
                return;
            }
            
            $this->contaPagarModel = new ContaPagar();
            $contaPagar = $this->contaPagarModel->findById($id);
            
            $valorPagamento = floatval($data['valor_pagamento']);
            $valorJaPago = $contaPagar['valor_pago'];
            $valorPago = $valorJaPago + $valorPagamento;
            $valorTotal = $contaPagar['valor_total'];
            
            // Determina status
            if ($valorPago >= $valorTotal) {
                $status = 'pago';
            } else {
                $status = 'parcial';
            }
            
            // Atualiza conta
            $dadosAntes = $contaPagar;
            $this->contaPagarModel->atualizarPagamento($id, $valorPago, $data['data_pagamento'], $status);
            
            // Registrar auditoria do pagamento
            \App\Models\Auditoria::registrar(
                'contas_pagar',
                $id,
                'make_payment',
                $dadosAntes,
                $this->contaPagarModel->findById($id),
                "Pagamento de R$ " . number_format($valorPagamento, 2, ',', '.')
            );
            
            // Cria movimentação de caixa
            $this->movimentacaoService = new MovimentacaoService();
            $dadosBaixa = [
                'empresa_id' => $contaPagar['empresa_id'],
                'categoria_id' => $contaPagar['categoria_id'],
                'centro_custo_id' => $contaPagar['centro_custo_id'],
                'conta_bancaria_id' => $data['conta_bancaria_id'],
                'descricao' => "Pagamento: " . $contaPagar['descricao'],
                'valor' => $valorPagamento,
                'data_movimento' => $data['data_pagamento'],
                'data_competencia' => $contaPagar['data_competencia'],
                'forma_pagamento_id' => $data['forma_pagamento_id'],
                'observacoes' => $data['observacoes_pagamento'] ?? null
            ];
            
            $this->movimentacaoService->criarMovimentacaoPagamento($id, $dadosBaixa);
            
            $_SESSION['success'] = 'Pagamento registrado com sucesso!';
            $response->redirect('/contas-pagar/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao registrar pagamento: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/contas-pagar/{$id}?acao=baixar");
        }
    }

    /**
     * Deletar contas em massa (soft delete)
     */
    public function deletarMassa(Request $request, Response $response)
    {
        $ids = $request->post('ids', []);
        $motivo = $request->post('motivo', 'Exclusão em massa pelo usuário');
        
        if (empty($ids)) {
            $_SESSION['error'] = 'Selecione pelo menos uma conta para deletar.';
            $response->redirect('/contas-pagar');
            return;
        }
        
        $contaPagarModel = new ContaPagar();
        $deletadas = 0;
        $erros = 0;
        
        foreach ($ids as $id) {
            $id = (int) $id;
            if (!$id) continue;
            
            try {
                $conta = $contaPagarModel->findById($id);
                if (!$conta) {
                    $erros++;
                    continue;
                }
                
                $contaPagarModel->softDelete($id, $motivo);
                $deletadas++;
            } catch (\Exception $e) {
                $erros++;
                LogSistema::error('ContaPagar', 'deletar_massa_erro', "Erro ao deletar conta #{$id}", [
                    'conta_id' => $id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }
        
        if ($deletadas > 0) {
            $_SESSION['success'] = "{$deletadas} conta(s) deletada(s) com sucesso! (É possível restaurar em Registros Deletados)";
        }
        if ($erros > 0) {
            $_SESSION['error'] = ($deletadas > 0 ? '' : '') . "{$erros} conta(s) não puderam ser deletadas.";
        }
        
        LogSistema::info('ContaPagar', 'deletar_massa', "Exclusão em massa: {$deletadas} deletadas, {$erros} erros", [
            'ids' => $ids,
            'motivo' => $motivo,
            'deletadas' => $deletadas,
            'erros' => $erros,
        ]);
        
        $response->redirect('/contas-pagar');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Categoria
        if (empty($data['categoria_id'])) {
            $errors['categoria_id'] = 'A categoria é obrigatória';
        }
        
        // Número do documento
        if (empty($data['numero_documento'])) {
            $errors['numero_documento'] = 'O número do documento é obrigatório';
        }
        
        // Descrição
        if (empty($data['descricao'])) {
            $errors['descricao'] = 'A descrição é obrigatória';
        }
        
        // Valor total
        if (empty($data['valor_total']) || $data['valor_total'] <= 0) {
            $errors['valor_total'] = 'O valor total deve ser maior que zero';
        }
        
        // Data de emissão
        if (empty($data['data_emissao'])) {
            $errors['data_emissao'] = 'A data de emissão é obrigatória';
        }
        
        // Data de competência
        if (empty($data['data_competencia'])) {
            $errors['data_competencia'] = 'A data de competência é obrigatória';
        }
        
        // Data de vencimento
        if (empty($data['data_vencimento'])) {
            $errors['data_vencimento'] = 'A data de vencimento é obrigatória';
        }
        
        return $errors;
    }
    
    protected function validateBaixa($data)
    {
        $errors = [];
        
        // Valor pagamento
        if (empty($data['valor_pagamento']) || $data['valor_pagamento'] <= 0) {
            $errors['valor_pagamento'] = 'O valor do pagamento deve ser maior que zero';
        }
        
        // Data de pagamento
        if (empty($data['data_pagamento'])) {
            $errors['data_pagamento'] = 'A data de pagamento é obrigatória';
        }
        
        // Conta bancária
        if (empty($data['conta_bancaria_id'])) {
            $errors['conta_bancaria_id'] = 'A conta bancária é obrigatória';
        }
        
        // Forma de pagamento
        if (empty($data['forma_pagamento_id'])) {
            $errors['forma_pagamento_id'] = 'A forma de pagamento é obrigatória';
        }
        
        return $errors;
    }
}
