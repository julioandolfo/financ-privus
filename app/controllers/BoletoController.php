<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Boleto;
use App\Models\BoletoHistorico;
use App\Models\ConexaoBancaria;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Usuario;
use App\Models\LogSistema;
use Includes\Services\CobrancaServiceFactory;

class BoletoController extends Controller
{
    private $boletoModel;
    private $historicoModel;
    private $conexaoModel;
    private $clienteModel;
    private $empresasUsuarioIds = [];

    public function __construct()
    {
        parent::__construct();
        $this->boletoModel = new Boleto();
        $this->historicoModel = new BoletoHistorico();
        $this->conexaoModel = new ConexaoBancaria();
        $this->clienteModel = new Cliente();

        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId) {
            $usuarioModel = new Usuario();
            $empresas = $usuarioModel->getEmpresas($usuarioId);
            $this->empresasUsuarioIds = array_map(fn($e) => (int)$e['id'], $empresas);
        }
    }

    private function temAcessoEmpresa($empresaId): bool
    {
        return in_array((int)$empresaId, $this->empresasUsuarioIds);
    }

    /**
     * Listagem de boletos com filtros.
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);

        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }

        $filtros = [
            'situacao' => $request->get('situacao'),
            'conexao_bancaria_id' => $request->get('conexao_bancaria_id'),
            'cliente_id' => $request->get('cliente_id'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim'),
            'busca' => $request->get('busca'),
        ];

        $page = max(1, (int)$request->get('page', 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $boletos = [];
        $total = 0;
        $estatisticas = [];
        $totalPages = 0;
        $migrationPendente = false;

        try {
            $boletos = $this->boletoModel->findByEmpresa($empresaId, $filtros, $perPage, $offset);
            $total = $this->boletoModel->countByEmpresa($empresaId, $filtros);
            $estatisticas = $this->boletoModel->getEstatisticas($empresaId);
            $totalPages = $perPage > 0 ? ceil($total / $perPage) : 0;
        } catch (\Exception $e) {
            // Tabela boletos provavelmente não existe ainda (migration pendente)
            $migrationPendente = true;
            LogSistema::error('Boletos', 'index', 'Erro ao carregar boletos (migration pendente?)', [
                'erro' => $e->getMessage(),
                'empresa_id' => $empresaId
            ]);
        }

        $conexoes = [];
        try {
            $conexoes = $this->conexaoModel->findByEmpresa($empresaId);
        } catch (\Exception $e) {}

        $clientes = [];
        try {
            $clientes = $this->clienteModel->findAll($empresaId);
        } catch (\Exception $e) {}

        $this->render('boletos/index', [
            'title' => 'Boletos Bancários',
            'boletos' => $boletos,
            'estatisticas' => $estatisticas,
            'conexoes' => $conexoes,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'empresaId' => $empresaId,
            'empresasUsuario' => $empresasUsuario,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'migrationPendente' => $migrationPendente,
        ]);
    }

    /**
     * Formulário de emissão de boleto.
     */
    public function create(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);

        $empresaId = $request->get('empresa_id');
        if (!$empresaId && !empty($empresasUsuario)) {
            $empresaId = $empresasUsuario[0]['id'];
        }

        $conexoes = [];
        $conexoesCobranca = [];
        try {
            $conexoes = $this->conexaoModel->findByEmpresa($empresaId);
            $conexoesCobranca = array_values(array_filter($conexoes, function($c) {
                return CobrancaServiceFactory::isSuportado($c['banco'] ?? '') && !empty($c['numero_cliente_banco']);
            }));
        } catch (\Exception $e) {}

        $clientes = [];
        try {
            $clientes = $this->clienteModel->findAll($empresaId);
        } catch (\Exception $e) {}

        // Buscar pedidos disponíveis
        $pedidos = [];
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, numero, descricao, valor_total, cliente_id FROM pedidos_vinculados WHERE empresa_id = :eid ORDER BY created_at DESC LIMIT 200");
            $stmt->execute(['eid' => $empresaId]);
            $pedidos = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            // Tabela pode não existir
        }

        $this->render('boletos/create', [
            'title' => 'Emitir Boleto',
            'conexoes' => $conexoesCobranca,
            'todasConexoes' => $conexoes,
            'clientes' => $clientes,
            'pedidos' => $pedidos,
            'empresaId' => $empresaId,
            'empresasUsuario' => $empresasUsuario,
        ]);
    }

    /**
     * Emite boleto via API + salva local.
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();

        $conexaoId = (int)($data['conexao_bancaria_id'] ?? 0);
        $conexao = $this->conexaoModel->findById($conexaoId);

        if (!$conexao || !$this->temAcessoEmpresa($conexao['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Conexão bancária não encontrada ou sem permissão'], 403);
        }

        if (!CobrancaServiceFactory::isSuportado($conexao['banco'])) {
            return $response->json(['success' => false, 'error' => 'Banco não suporta cobrança: ' . $conexao['banco']], 400);
        }

        $empresaId = $conexao['empresa_id'];
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        try {
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $numeroCliente = $conexao['numero_cliente_banco'] ?? '';
            $modalidade = $conexao['codigo_modalidade_cobranca'] ?? 1;
            $contaCorrente = $conexao['conta_corrente_cobranca'] ?? $conexao['banco_conta_id'] ?? '';

            // Montar payload para API
            $boletoApi = [
                'numeroCliente' => (int)$numeroCliente,
                'codigoModalidade' => (int)$modalidade,
                'numeroContaCorrente' => (int)$contaCorrente,
                'codigoEspecieDocumento' => $data['especie_documento'] ?? 'DM',
                'dataEmissao' => $data['data_emissao'] ?? date('Y-m-d'),
                'seuNumero' => $data['seu_numero'] ?? uniqid(),
                'identificacaoEmissaoBoleto' => (int)($data['emissao_banco'] ?? 1),
                'identificacaoDistribuicaoBoleto' => (int)($data['distribuicao_banco'] ?? 1),
                'valor' => (float)($data['valor'] ?? 0),
                'dataVencimento' => $data['data_vencimento'],
                'numeroParcela' => (int)($data['numero_parcela'] ?? 1),
                'pagador' => [
                    'numeroCpfCnpj' => preg_replace('/[^0-9]/', '', $data['pagador_cpf_cnpj'] ?? ''),
                    'nome' => substr($data['pagador_nome'] ?? '', 0, 50),
                    'endereco' => substr($data['pagador_endereco'] ?? '', 0, 40),
                    'bairro' => substr($data['pagador_bairro'] ?? '', 0, 30),
                    'cidade' => substr($data['pagador_cidade'] ?? '', 0, 40),
                    'cep' => preg_replace('/[^0-9]/', '', $data['pagador_cep'] ?? ''),
                    'uf' => strtoupper(substr($data['pagador_uf'] ?? '', 0, 2)),
                ],
                'gerarPdf' => true,
                'codigoCadastrarPIX' => (int)($data['codigo_cadastrar_pix'] ?? 1),
            ];

            // Campos opcionais - só envia se preenchidos (API Sicoob não aceita null)
            if (!empty($data['data_limite_pagamento'])) $boletoApi['dataLimitePagamento'] = $data['data_limite_pagamento'];
            if (!empty($data['pagador_email'])) $boletoApi['pagador']['email'] = $data['pagador_email'];
            if (!empty($data['identificacao_boleto_empresa'])) $boletoApi['identificacaoBoletoEmpresa'] = $data['identificacao_boleto_empresa'];
            if (!empty($data['aceite'])) $boletoApi['aceite'] = (bool)$data['aceite'];

            // Desconto
            $tipoDesconto = (int)($data['tipo_desconto'] ?? 0);
            $boletoApi['tipoDesconto'] = $tipoDesconto;
            if ($tipoDesconto > 0) {
                if (!empty($data['data_primeiro_desconto'])) $boletoApi['dataPrimeiroDesconto'] = $data['data_primeiro_desconto'];
                if (!empty($data['valor_primeiro_desconto'])) $boletoApi['valorPrimeiroDesconto'] = (float)$data['valor_primeiro_desconto'];
            }

            // Multa
            $tipoMulta = (int)($data['tipo_multa'] ?? 0);
            $boletoApi['tipoMulta'] = $tipoMulta;
            if ($tipoMulta > 0) {
                if (!empty($data['data_multa'])) $boletoApi['dataMulta'] = $data['data_multa'];
                if (!empty($data['valor_multa'])) $boletoApi['valorMulta'] = (float)$data['valor_multa'];
            }

            // Juros
            $tipoJuros = (int)($data['tipo_juros_mora'] ?? 3);
            $boletoApi['tipoJurosMora'] = $tipoJuros;
            if ($tipoJuros < 3) {
                if (!empty($data['data_juros_mora'])) $boletoApi['dataJurosMora'] = $data['data_juros_mora'];
                if (!empty($data['valor_juros_mora'])) $boletoApi['valorJurosMora'] = (float)$data['valor_juros_mora'];
            }

            // Protesto
            $codProtesto = (int)($data['codigo_protesto'] ?? 3);
            $boletoApi['codigoProtesto'] = $codProtesto;
            if ($codProtesto < 3 && !empty($data['dias_protesto'])) {
                $boletoApi['numeroDiasProtesto'] = (int)$data['dias_protesto'];
            }

            // Negativação
            $codNeg = (int)($data['codigo_negativacao'] ?? 3);
            $boletoApi['codigoNegativacao'] = $codNeg;
            if ($codNeg === 2 && !empty($data['dias_negativacao'])) {
                $boletoApi['numeroDiasNegativacao'] = (int)$data['dias_negativacao'];
            }

            // Abatimento
            if (!empty($data['valor_abatimento']) && (float)$data['valor_abatimento'] > 0) {
                $boletoApi['valorAbatimento'] = (float)$data['valor_abatimento'];
            }

            // Instruções
            $instrucoes = [];
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($data["instrucao_{$i}"])) {
                    $instrucoes[] = substr($data["instrucao_{$i}"], 0, 40);
                }
            }
            if (!empty($instrucoes)) $boletoApi['mensagensInstrucao'] = $instrucoes;

            // Chamar API
            LogSistema::info('Boletos', 'emitir', 'Emitindo boleto via API', [
                'conexao_id' => $conexaoId,
                'banco' => $conexao['banco'],
                'valor' => $data['valor'] ?? 0,
                'pagador' => $data['pagador_nome'] ?? '',
                'vencimento' => $data['data_vencimento'] ?? '',
            ]);

            $resultado = $service->incluirBoleto($conexao, $boletoApi);

            // Salvar boleto localmente
            $boletoLocal = [
                'empresa_id' => $empresaId,
                'conexao_bancaria_id' => $conexaoId,
                'cliente_id' => !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null,
                'pedido_vinculado_id' => !empty($data['pedido_vinculado_id']) ? (int)$data['pedido_vinculado_id'] : null,
                'nosso_numero' => $resultado['nosso_numero'] ?? null,
                'seu_numero' => $data['seu_numero'] ?? $boletoApi['seuNumero'],
                'codigo_barras' => $resultado['codigo_barras'] ?? null,
                'linha_digitavel' => $resultado['linha_digitavel'] ?? null,
                'qr_code_pix' => $resultado['qr_code'] ?? null,
                'valor' => (float)($data['valor'] ?? 0),
                'valor_abatimento' => (float)($data['valor_abatimento'] ?? 0),
                'valor_multa' => (float)($data['valor_multa'] ?? 0),
                'valor_juros_mora' => (float)($data['valor_juros_mora'] ?? 0),
                'data_emissao' => $data['data_emissao'] ?? date('Y-m-d'),
                'data_vencimento' => $data['data_vencimento'],
                'data_limite_pagamento' => $data['data_limite_pagamento'] ?? null,
                'numero_cliente_banco' => $numeroCliente,
                'codigo_modalidade' => $modalidade,
                'numero_conta_corrente' => $contaCorrente,
                'especie_documento' => $data['especie_documento'] ?? 'DM',
                'numero_parcela' => (int)($data['numero_parcela'] ?? 1),
                'aceite' => (bool)($data['aceite'] ?? true),
                'tipo_desconto' => $tipoDesconto,
                'tipo_multa' => $tipoMulta,
                'tipo_juros_mora' => $tipoJuros,
                'codigo_protesto' => $codProtesto,
                'dias_protesto' => (int)($data['dias_protesto'] ?? 0),
                'codigo_negativacao' => $codNeg,
                'dias_negativacao' => (int)($data['dias_negativacao'] ?? 0),
                'pagador_cpf_cnpj' => preg_replace('/[^0-9]/', '', $data['pagador_cpf_cnpj'] ?? ''),
                'pagador_nome' => $data['pagador_nome'] ?? '',
                'pagador_endereco' => $data['pagador_endereco'] ?? null,
                'pagador_bairro' => $data['pagador_bairro'] ?? null,
                'pagador_cidade' => $data['pagador_cidade'] ?? null,
                'pagador_cep' => preg_replace('/[^0-9]/', '', $data['pagador_cep'] ?? ''),
                'pagador_uf' => $data['pagador_uf'] ?? null,
                'pagador_email' => $data['pagador_email'] ?? null,
                'mensagens_instrucao' => $instrucoes,
                'codigo_cadastrar_pix' => (int)($data['codigo_cadastrar_pix'] ?? 1),
                'situacao' => 'em_aberto',
                'pdf_boleto' => $resultado['pdf_boleto'] ?? null,
                'emissao_banco' => (int)($data['emissao_banco'] ?? 1),
                'distribuicao_banco' => (int)($data['distribuicao_banco'] ?? 1),
                'criado_por' => $usuarioId,
            ];

            $boletoId = $this->boletoModel->criar($boletoLocal);

            // Registrar no histórico
            $this->historicoModel->registrar($boletoId, 'entrada', 'Boleto emitido via API', [
                'nosso_numero' => $resultado['nosso_numero'],
                'valor' => $data['valor'],
            ], $usuarioId);

            // Criar Conta a Receber vinculada (se solicitado)
            if (!empty($data['criar_conta_receber'])) {
                try {
                    $contaReceberModel = new ContaReceber();
                    $crData = [
                        'empresa_id' => $empresaId,
                        'cliente_id' => $data['cliente_id'] ?? null,
                        'categoria_id' => $data['categoria_id'] ?? null,
                        'centro_custo_id' => $data['centro_custo_id'] ?? null,
                        'numero_documento' => $resultado['nosso_numero'] ?? $data['seu_numero'] ?? '',
                        'descricao' => 'Boleto #' . ($resultado['nosso_numero'] ?? $boletoId) . ' - ' . ($data['pagador_nome'] ?? ''),
                        'valor_total' => (float)($data['valor'] ?? 0),
                        'valor_recebido' => 0,
                        'data_emissao' => $data['data_emissao'] ?? date('Y-m-d'),
                        'data_competencia' => $data['data_emissao'] ?? date('Y-m-d'),
                        'data_vencimento' => $data['data_vencimento'],
                        'status' => 'pendente',
                        'observacoes' => 'Vinculado ao boleto #' . $boletoId,
                        'usuario_cadastro_id' => $usuarioId,
                    ];
                    $contaReceberId = $contaReceberModel->create($crData);
                    if ($contaReceberId) {
                        $this->boletoModel->atualizar($boletoId, ['conta_receber_id' => $contaReceberId]);
                    }
                } catch (\Exception $e) {
                    LogSistema::error('Boletos', 'conta_receber_erro', 'Erro ao criar conta a receber vinculada', [
                        'boleto_id' => $boletoId, 'erro' => $e->getMessage(),
                    ]);
                }
            }

            LogSistema::info('Boletos', 'emitido', 'Boleto emitido com sucesso', [
                'boleto_id' => $boletoId,
                'nosso_numero' => $resultado['nosso_numero'],
                'valor' => $data['valor'] ?? 0,
                'pagador' => $data['pagador_nome'] ?? '',
                'empresa_id' => $empresaId,
            ]);

            return $response->json([
                'success' => true,
                'boleto_id' => $boletoId,
                'nosso_numero' => $resultado['nosso_numero'],
                'codigo_barras' => $resultado['codigo_barras'],
                'linha_digitavel' => $resultado['linha_digitavel'],
                'message' => 'Boleto emitido com sucesso!'
            ]);

        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'emitir_erro', 'Erro ao emitir boleto', [
                'erro' => $e->getMessage(),
                'conexao_id' => $conexaoId,
                'banco' => $conexao['banco'] ?? '',
                'valor' => $data['valor'] ?? 0,
                'pagador' => $data['pagador_nome'] ?? '',
            ]);
            return $response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detalhes de um boleto.
     */
    public function show(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->redirect('/boletos');
        }

        $historico = $this->historicoModel->findByBoleto((int)$id);
        $empresaId = $boleto['empresa_id'];

        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($_SESSION['usuario_id'] ?? null);

        $this->render('boletos/show', [
            'title' => 'Boleto #' . ($boleto['nosso_numero'] ?? $id),
            'boleto' => $boleto,
            'historico' => $historico,
            'empresaId' => $empresaId,
            'empresasUsuario' => $empresasUsuario,
        ]);
    }

    /**
     * Segunda via do boleto (PDF).
     */
    public function segundaVia(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $resultado = $service->segundaViaBoleto($conexao, [
                'nosso_numero' => $boleto['nosso_numero'],
            ]);

            if (!empty($resultado['pdf_boleto'])) {
                $this->boletoModel->atualizar((int)$id, ['pdf_boleto' => $resultado['pdf_boleto']]);
            }

            $this->historicoModel->registrar((int)$id, 'alteracao', 'Segunda via solicitada', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::info('Boletos', 'segunda_via', '2ª via gerada', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
            ]);

            return $response->json([
                'success' => true,
                'pdf_boleto' => $resultado['pdf_boleto'] ?? null,
                'qr_code' => $resultado['qr_code'] ?? null,
            ]);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'segunda_via_erro', 'Erro ao gerar 2ª via', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Comandar baixa do boleto.
     */
    public function baixar(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $resultado = $service->baixarBoleto($conexao, $boleto['nosso_numero'], []);

            $this->boletoModel->atualizar((int)$id, ['situacao' => 'baixado']);
            $this->historicoModel->registrar((int)$id, 'baixa', 'Boleto baixado via sistema', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::info('Boletos', 'baixa', 'Boleto baixado', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
                'valor' => $boleto['valor'] ?? 0,
            ]);

            return $response->json(['success' => true, 'message' => 'Boleto baixado com sucesso']);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'baixa_erro', 'Erro ao baixar boleto', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Protestar boleto.
     */
    public function protestar(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $service->protestarBoleto($conexao, $boleto['nosso_numero'], []);

            $this->boletoModel->atualizar((int)$id, ['situacao' => 'protestado']);
            $this->historicoModel->registrar((int)$id, 'protesto', 'Boleto enviado para protesto', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::warning('Boletos', 'protesto', 'Boleto enviado para protesto', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
                'pagador' => $boleto['pagador_nome'] ?? '', 'valor' => $boleto['valor'] ?? 0,
            ]);

            return $response->json(['success' => true, 'message' => 'Boleto enviado para protesto']);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'protesto_erro', 'Erro ao protestar boleto', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancelar protesto.
     */
    public function cancelarProtesto(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $service->cancelarProtesto($conexao, $boleto['nosso_numero'], []);

            $novaSituacao = (strtotime($boleto['data_vencimento']) < time()) ? 'vencido' : 'em_aberto';
            $this->boletoModel->atualizar((int)$id, ['situacao' => $novaSituacao]);
            $this->historicoModel->registrar((int)$id, 'cancelamento', 'Protesto cancelado', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::info('Boletos', 'cancelar_protesto', 'Protesto cancelado', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
            ]);

            return $response->json(['success' => true, 'message' => 'Protesto cancelado']);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'cancelar_protesto_erro', 'Erro ao cancelar protesto', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Negativar boleto.
     */
    public function negativar(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $service->negativarBoleto($conexao, $boleto['nosso_numero'], []);

            $this->boletoModel->atualizar((int)$id, ['situacao' => 'negativado']);
            $this->historicoModel->registrar((int)$id, 'negativacao', 'Boleto enviado para negativação SERASA', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::warning('Boletos', 'negativacao', 'Boleto enviado para negativação SERASA', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
                'pagador' => $boleto['pagador_nome'] ?? '', 'cpf_cnpj' => $boleto['pagador_cpf_cnpj'] ?? '',
                'valor' => $boleto['valor'] ?? 0,
            ]);

            return $response->json(['success' => true, 'message' => 'Boleto enviado para negativação']);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'negativacao_erro', 'Erro ao negativar boleto', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancelar negativação.
     */
    public function cancelarNegativacao(Request $request, Response $response, $id)
    {
        $boleto = $this->boletoModel->findById((int)$id);
        if (!$boleto || !$this->temAcessoEmpresa($boleto['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Boleto não encontrado'], 404);
        }

        try {
            $conexao = $this->conexaoModel->findById($boleto['conexao_bancaria_id']);
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $service->cancelarNegativacao($conexao, $boleto['nosso_numero'], []);

            $novaSituacao = (strtotime($boleto['data_vencimento']) < time()) ? 'vencido' : 'em_aberto';
            $this->boletoModel->atualizar((int)$id, ['situacao' => $novaSituacao]);
            $this->historicoModel->registrar((int)$id, 'cancelamento', 'Negativação cancelada', [], $_SESSION['usuario_id'] ?? null);

            LogSistema::info('Boletos', 'cancelar_negativacao', 'Negativação cancelada', [
                'boleto_id' => $id, 'nosso_numero' => $boleto['nosso_numero'] ?? '',
            ]);

            return $response->json(['success' => true, 'message' => 'Negativação cancelada']);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'cancelar_negativacao_erro', 'Erro ao cancelar negativação', [
                'boleto_id' => $id, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sincronizar boletos do banco para o sistema.
     */
    public function sincronizar(Request $request, Response $response)
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->all();
        $conexaoId = (int)($data['conexao_bancaria_id'] ?? 0);

        $conexao = $this->conexaoModel->findById($conexaoId);
        if (!$conexao || !$this->temAcessoEmpresa($conexao['empresa_id'])) {
            return $response->json(['success' => false, 'error' => 'Conexão não encontrada'], 404);
        }

        try {
            $service = CobrancaServiceFactory::create($conexao['banco']);

            $filtros = [
                'nosso_numero' => $data['nosso_numero'] ?? null,
            ];

            $boletosApi = $service->consultarBoleto($conexao, array_filter($filtros));

            $importados = 0;
            $atualizados = 0;
            $erros = 0;

            $listaApi = is_array($boletosApi) && isset($boletosApi[0]) ? $boletosApi : [$boletosApi];

            foreach ($listaApi as $bApi) {
                try {
                    $nn = $bApi['nossoNumero'] ?? null;
                    if (!$nn) continue;

                    $existing = $this->boletoModel->findByNossoNumero($nn, $conexaoId);

                    $situacaoMap = [
                        1 => 'em_aberto', 2 => 'baixado', 3 => 'liquidado',
                    ];
                    $sit = $situacaoMap[$bApi['situacao'] ?? 0] ?? 'em_aberto';

                    if ($existing) {
                        $updateData = [
                            'situacao' => $sit,
                            'situacao_banco' => $bApi['situacaoDescricao'] ?? null,
                        ];
                        if ($sit === 'liquidado') {
                            $updateData['valor_recebido'] = $bApi['valorTotalRecebimento'] ?? $bApi['valor'] ?? null;
                            $updateData['data_pagamento'] = $bApi['dataLiquidacao'] ?? date('Y-m-d');
                        }
                        $this->boletoModel->atualizar($existing['id'], $updateData);
                        $atualizados++;
                    } else {
                        $boletoLocal = [
                            'empresa_id' => $conexao['empresa_id'],
                            'conexao_bancaria_id' => $conexaoId,
                            'nosso_numero' => $nn,
                            'seu_numero' => $bApi['seuNumero'] ?? null,
                            'codigo_barras' => $bApi['codigoBarras'] ?? null,
                            'linha_digitavel' => $bApi['linhaDigitavel'] ?? null,
                            'valor' => $bApi['valor'] ?? 0,
                            'data_emissao' => $bApi['dataEmissao'] ?? date('Y-m-d'),
                            'data_vencimento' => $bApi['dataVencimento'] ?? date('Y-m-d'),
                            'numero_cliente_banco' => $conexao['numero_cliente_banco'] ?? '',
                            'codigo_modalidade' => $bApi['codigoModalidade'] ?? 1,
                            'especie_documento' => $bApi['codigoEspecieDocumento'] ?? 'DM',
                            'pagador_cpf_cnpj' => $bApi['pagador']['numeroCpfCnpj'] ?? '',
                            'pagador_nome' => $bApi['pagador']['nome'] ?? 'Não identificado',
                            'situacao' => $sit,
                            'situacao_banco' => $bApi['situacaoDescricao'] ?? null,
                        ];
                        if ($sit === 'liquidado') {
                            $boletoLocal['valor_recebido'] = $bApi['valorTotalRecebimento'] ?? $bApi['valor'] ?? null;
                            $boletoLocal['data_pagamento'] = $bApi['dataLiquidacao'] ?? null;
                        }
                        $this->boletoModel->criar($boletoLocal);
                        $importados++;
                    }
                } catch (\Exception $e) {
                    $erros++;
                    LogSistema::error('Boletos', 'sincronizar_item_erro', 'Erro ao sincronizar boleto individual', [
                        'nosso_numero' => $nn ?? '', 'erro' => $e->getMessage(),
                    ]);
                }
            }

            LogSistema::info('Boletos', 'sincronizar', "Sincronização concluída", [
                'conexao_id' => $conexaoId, 'banco' => $conexao['banco'] ?? '',
                'importados' => $importados, 'atualizados' => $atualizados, 'erros' => $erros,
            ]);

            return $response->json([
                'success' => true,
                'importados' => $importados,
                'atualizados' => $atualizados,
                'erros' => $erros,
                'message' => "Sincronização concluída: {$importados} importados, {$atualizados} atualizados, {$erros} erros"
            ]);
        } catch (\Exception $e) {
            LogSistema::error('Boletos', 'sincronizar_erro', 'Erro geral na sincronização de boletos', [
                'conexao_id' => $conexaoId, 'erro' => $e->getMessage(),
            ]);
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
