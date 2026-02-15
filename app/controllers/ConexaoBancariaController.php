<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ConexaoBancaria;
use App\Models\ContaBancaria;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\TransacaoPendente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\MovimentacaoCaixa;
use App\Models\ExtratoBancarioApi;
use Includes\Services\BankServiceFactory;
use Includes\Services\ClassificadorIAService;

class ConexaoBancariaController extends Controller
{
    private $conexaoModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $empresaModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->conexaoModel = new ConexaoBancaria();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->centroCustoModel = new CentroCusto();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Lista todas as conexões bancárias com saldos
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        
        // "todas" como padrão, ou o valor selecionado pelo usuário
        $empresaId = $request->get('empresa_id') ?? 'todas';
        
        $conexoes = [];
        $empresa = null;
        $saldoTotal = ['saldo_total' => 0, 'total_contas' => 0, 'saldo_mais_antigo' => null];
        $transacoesPendentes = 0;
        
        if ($empresaId === 'todas') {
            // Buscar conexões de todas as empresas do usuário
            $empresaIds = array_column($empresasUsuario, 'id');
            foreach ($empresaIds as $empId) {
                $empConexoes = $this->conexaoModel->findByEmpresa($empId);
                // Adicionar nome da empresa em cada conexão para identificar
                $empData = $this->empresaModel->findById($empId);
                foreach ($empConexoes as &$con) {
                    $con['empresa_nome'] = $empData['nome_fantasia'] ?? $empData['razao_social'] ?? 'Empresa #' . $empId;
                }
                unset($con);
                $conexoes = array_merge($conexoes, $empConexoes);
                
                $empSaldo = $this->conexaoModel->getSaldoTotalEmpresa($empId);
                $saldoTotal['saldo_total'] += ($empSaldo['saldo_total'] ?? 0);
                $saldoTotal['total_contas'] += ($empSaldo['total_contas'] ?? 0);
                if (!empty($empSaldo['saldo_mais_antigo'])) {
                    if (empty($saldoTotal['saldo_mais_antigo']) || $empSaldo['saldo_mais_antigo'] < $saldoTotal['saldo_mais_antigo']) {
                        $saldoTotal['saldo_mais_antigo'] = $empSaldo['saldo_mais_antigo'];
                    }
                }
            }
            
            $transacaoModel = new TransacaoPendente();
            foreach ($empresaIds as $empId) {
                $transacoesPendentes += $transacaoModel->countByEmpresa($empId, 'pendente');
            }
        } elseif ($empresaId) {
            $conexoes = $this->conexaoModel->findByEmpresa($empresaId);
            $empresa = $this->empresaModel->findById($empresaId);
            $saldoTotal = $this->conexaoModel->getSaldoTotalEmpresa($empresaId);
            
            $transacaoModel = new TransacaoPendente();
            $transacoesPendentes = $transacaoModel->countByEmpresa($empresaId, 'pendente');
        }
        
        // Bancos disponíveis para o template
        $bancosDisponiveis = BankServiceFactory::getBancosDisponiveis();
        
        return $this->render('conexoes_bancarias/index', [
            'conexoes' => $conexoes,
            'empresa' => $empresa,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId,
            'saldo_total' => $saldoTotal,
            'transacoes_pendentes' => $transacoesPendentes,
            'bancos_disponiveis' => $bancosDisponiveis
        ]);
    }
    
    /**
     * Formulário de nova conexão
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
        
        $categorias = [];
        $centrosCusto = [];
        $contasBancarias = [];
        
        if ($empresaId) {
            $categorias = $this->categoriaModel->findAll($empresaId);
            $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            
            $contaBancariaModel = new ContaBancaria();
            $contasBancarias = $contaBancariaModel->findAll($empresaId);
        }
        
        // Bancos e seus campos de configuração
        $bancosDisponiveis = BankServiceFactory::getBancosDisponiveis();
        $camposPorBanco = BankServiceFactory::getTodosCampos();
        
        return $this->render('conexoes_bancarias/create', [
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'contas_bancarias' => $contasBancarias,
            'empresas_usuario' => $empresasUsuario,
            'empresa_id_selecionada' => $empresaId,
            'bancos_disponiveis' => $bancosDisponiveis,
            'campos_por_banco' => $camposPorBanco
        ]);
    }
    
    /**
     * Salvar nova conexão bancária (API direta)
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->all();
        $empresaId = $data['empresa_id'] ?? $request->get('empresa_id') ?? ($_SESSION['usuario_empresa_id'] ?? null);
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        
        if (!$empresaId || !$usuarioId) {
            $_SESSION['errors'] = ['empresa_id' => 'Selecione a empresa.'];
            $_SESSION['old'] = $data;
            return $response->redirect('/conexoes-bancarias/create');
        }
        
        // Validar
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect('/conexoes-bancarias/create');
        }
        
        // Processar upload de certificado PFX se enviado
        $certPfxBase64 = null;
        if (!empty($_FILES['cert_pfx']) && $_FILES['cert_pfx']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['cert_pfx']['tmp_name'];
            $originalName = $_FILES['cert_pfx']['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            if (!in_array($extension, ['pfx', 'p12'])) {
                $_SESSION['errors'] = ['cert_pfx' => 'Formato inválido. Envie um arquivo .pfx ou .p12'];
                $_SESSION['old'] = $data;
                return $response->redirect('/conexoes-bancarias/create');
            }
            
            $pfxContent = file_get_contents($tmpFile);
            if ($pfxContent !== false) {
                $certPfxBase64 = base64_encode($pfxContent);
            }
        }
        
        // Montar dados da conexão
        $conexaoData = [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'banco' => $data['banco'],
            'tipo_integracao' => 'api_direta',
            'tipo' => $data['tipo'] ?? 'conta_corrente',
            'identificacao' => $data['identificacao'] ?? null,
            'auto_sync' => isset($data['auto_sync']) ? 1 : 0,
            'frequencia_sync' => $data['frequencia_sync'] ?? 'diaria',
            'categoria_padrao_id' => !empty($data['categoria_padrao_id']) ? $data['categoria_padrao_id'] : null,
            'centro_custo_padrao_id' => !empty($data['centro_custo_padrao_id']) ? $data['centro_custo_padrao_id'] : null,
            'aprovacao_automatica' => isset($data['aprovacao_automatica']) ? 1 : 0,
            'conta_bancaria_id' => !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null,
            'banco_conta_id' => $data['banco_conta_id'] ?? null,
            // Credenciais (variam por banco)
            'ambiente' => $data['ambiente'] ?? 'producao',
            'client_id' => $data['client_id'] ?? null,
            'client_secret' => $data['client_secret'] ?? null,
            'access_token' => $data['access_token'] ?? null,
            'cert_pem' => $data['cert_pem'] ?? null,
            'key_pem' => $data['key_pem'] ?? null,
            'cert_pfx' => $certPfxBase64,
            'cert_password' => $data['cert_password'] ?? null,
            'cooperativa' => $data['cooperativa'] ?? null,
            'tipo_sync' => $data['tipo_sync'] ?? 'ambos',
            'status_conexao' => 'ativa'
        ];
        
        $conexaoId = $this->conexaoModel->create($conexaoData);
        
        if ($conexaoId) {
            $_SESSION['success'] = 'Conexão bancária criada com sucesso! Use o botão "Testar" para validar as credenciais.';
        } else {
            $_SESSION['error'] = 'Erro ao criar conexão bancária.';
        }
        
        return $response->redirect('/conexoes-bancarias');
    }

    /**
     * Manter compatibilidade com fluxo antigo
     */
    public function iniciarConsentimento(Request $request, Response $response)
    {
        return $this->store($request, $response);
    }

    /**
     * Callback OAuth (mantido para compatibilidade)
     */
    public function callback(Request $request, Response $response)
    {
        $_SESSION['error'] = 'O fluxo OAuth foi substituído por integração direta. Crie uma nova conexão.';
        return $response->redirect('/conexoes-bancarias');
    }
    
    /**
     * Exibir detalhes da conexão
     */
    public function show(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        // Verificar permissão
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioModel = new Usuario();
        $empresasUsuario = $usuarioModel->getEmpresas($usuarioId);
        $empresaIds = array_column($empresasUsuario, 'id');
        
        if (!in_array($conexao['empresa_id'], $empresaIds)) {
            $_SESSION['error'] = 'Acesso negado';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresa = $this->empresaModel->findById($conexao['empresa_id']);
        
        // Buscar últimas transações importadas desta conexão
        $transacaoModel = new TransacaoPendente();
        $db = \App\Core\Database::getInstance()->getConnection();
        $sqlTxn = "SELECT * FROM transacoes_pendentes 
                   WHERE conexao_bancaria_id = :conexao_id 
                   ORDER BY data_transacao DESC, created_at DESC LIMIT 20";
        $stmtTxn = $db->prepare($sqlTxn);
        $stmtTxn->execute(['conexao_id' => $conexao['id']]);
        $ultimasTransacoes = $stmtTxn->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        
        $bancoInfo = ConexaoBancaria::getBancoInfo($conexao['banco']);
        
        return $this->render('conexoes_bancarias/show', [
            'conexao' => $conexao,
            'empresa' => $empresa,
            'banco_info' => $bancoInfo,
            'ultimas_transacoes' => $ultimasTransacoes
        ]);
    }
    
    /**
     * Formulário de edição
     */
    public function edit(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        $empresaId = $conexao['empresa_id'];
        $categorias = $this->categoriaModel->findAll($empresaId);
        $centrosCusto = $this->centroCustoModel->findAll($empresaId);
        
        $contaBancariaModel = new ContaBancaria();
        $contasBancarias = $contaBancariaModel->findAll($empresaId);
        
        return $this->render('conexoes_bancarias/edit', [
            'conexao' => $conexao,
            'categorias' => $categorias,
            'centros_custo' => $centrosCusto,
            'contas_bancarias' => $contasBancarias
        ]);
    }
    
    /**
     * Atualizar conexão
     */
    public function update(Request $request, Response $response, $id)
    {
        $data = $request->all();
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        // Limpar número da conta (apenas dígitos)
        $bancoContaId = $data['banco_conta_id'] ?? $conexao['banco_conta_id'];
        if (!empty($bancoContaId)) {
            $bancoContaId = preg_replace('/[^0-9]/', '', $bancoContaId);
        }
        
        $updateData = [
            'identificacao' => $data['identificacao'] ?? $conexao['identificacao'],
            'auto_sync' => isset($data['auto_sync']) ? 1 : 0,
            'frequencia_sync' => $data['frequencia_sync'] ?? $conexao['frequencia_sync'],
            'tipo_sync' => $data['tipo_sync'] ?? $conexao['tipo_sync'] ?? 'ambos',
            'categoria_padrao_id' => !empty($data['categoria_padrao_id']) ? $data['categoria_padrao_id'] : null,
            'centro_custo_padrao_id' => !empty($data['centro_custo_padrao_id']) ? $data['centro_custo_padrao_id'] : null,
            'aprovacao_automatica' => isset($data['aprovacao_automatica']) ? 1 : 0,
            'conta_bancaria_id' => !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null,
            'banco_conta_id' => $bancoContaId,
            'ativo' => isset($data['ativo']) ? 1 : 0
        ];
        
        // Atualizar credenciais se fornecidas
        if (!empty($data['client_id'])) $updateData['client_id'] = $data['client_id'];
        if (!empty($data['client_secret'])) $updateData['client_secret'] = $data['client_secret'];
        if (!empty($data['access_token'])) $updateData['access_token'] = $data['access_token'];
        if (!empty($data['cert_password'])) $updateData['cert_password'] = $data['cert_password'];
        if (!empty($data['ambiente'])) $updateData['ambiente'] = $data['ambiente'];
        if (!empty($data['cooperativa'])) $updateData['cooperativa'] = $data['cooperativa'];
        
        // Certificado PEM (só atualiza se o conteúdo parecer válido, não o placeholder)
        if (!empty($data['cert_pem']) && strpos($data['cert_pem'], '-----BEGIN') !== false) {
            $updateData['cert_pem'] = $data['cert_pem'];
        }
        if (!empty($data['key_pem']) && strpos($data['key_pem'], '-----BEGIN') !== false) {
            $updateData['key_pem'] = $data['key_pem'];
        }
        
        // Upload de certificado PFX
        if (!empty($_FILES['cert_pfx']['tmp_name']) && $_FILES['cert_pfx']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['cert_pfx']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pfx', 'p12'])) {
                $pfxContent = file_get_contents($_FILES['cert_pfx']['tmp_name']);
                $updateData['cert_pfx'] = base64_encode($pfxContent);
            } else {
                $_SESSION['error'] = 'Arquivo de certificado deve ser .pfx ou .p12';
                return $response->redirect("/conexoes-bancarias/{$id}/edit");
            }
        }
        
        if ($this->conexaoModel->update($id, $updateData)) {
            $_SESSION['success'] = 'Conexão atualizada com sucesso!';
            return $response->redirect("/conexoes-bancarias/{$id}");
        }
        
        $_SESSION['error'] = 'Erro ao atualizar conexão';
        return $response->redirect("/conexoes-bancarias/{$id}/edit");
    }
    
    /**
     * Testar conexão com o banco
     */
    public function testarConexao(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->getConexaoComCredenciais($id);
        
        if (!$conexao) {
            return $response->json(['error' => 'Conexão não encontrada'], 404);
        }
        
        try {
            $service = BankServiceFactory::create($conexao['banco']);
            $ok = $service->testarConexao($conexao);
            
            if ($ok) {
                $this->conexaoModel->update($id, [
                    'status_conexao' => 'ativa',
                    'ultimo_erro' => null
                ]);
                
                return $response->json([
                    'success' => true,
                    'message' => 'Conexão com ' . $service->getBancoLabel() . ' testada com sucesso!'
                ]);
            } else {
                $this->conexaoModel->registrarErro($id, 'Teste de conexão falhou');
                return $response->json([
                    'success' => false,
                    'message' => 'Falha ao conectar. Verifique as credenciais.'
                ]);
            }
        } catch (\Exception $e) {
            $this->conexaoModel->registrarErro($id, $e->getMessage());
            return $response->json([
                'error' => 'Erro ao testar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obter saldo em tempo real (AJAX)
     */
    public function saldo(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->getConexaoComCredenciais($id);
        
        if (!$conexao) {
            return $response->json(['error' => 'Conexão não encontrada'], 404);
        }
        
        try {
            $service = BankServiceFactory::create($conexao['banco']);
            $saldoData = $service->getSaldo($conexao);
            
            // Atualizar saldo na conexão bancária
            $this->conexaoModel->atualizarSaldo($id, $saldoData['saldo']);
            
            // Se tiver conta bancária vinculada, propagar saldo real para ela
            if (!empty($conexao['conta_bancaria_id'])) {
                $contaBancariaModel = new ContaBancaria();
                $contaBancariaModel->setSaldoReal($conexao['conta_bancaria_id'], $saldoData['saldo']);
            }
            
            return $response->json([
                'success' => true,
                'saldo' => $saldoData['saldo'],
                'saldo_formatado' => 'R$ ' . number_format($saldoData['saldo'], 2, ',', '.'),
                'saldo_bloqueado' => $saldoData['saldo_bloqueado'] ?? 0,
                'atualizado_em' => $saldoData['atualizado_em'],
                'moeda' => $saldoData['moeda'] ?? 'BRL'
            ]);
        } catch (\Exception $e) {
            $this->conexaoModel->registrarErro($id, $e->getMessage());
            return $response->json([
                'error' => 'Erro ao obter saldo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sincronizar extrato manualmente
     */
    public function sincronizar(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->getConexaoComCredenciais($id);
        
        if (!$conexao) {
            return $response->json(['error' => 'Conexão não encontrada'], 404);
        }
        
        $detalhes = [];
        $saldoInfo = null;
        $debugApi = [];
        
        try {
            $service = BankServiceFactory::create($conexao['banco']);
            
            // Aceitar período customizado via POST ou usar padrão (últimos 7 dias)
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $dataInicio = !empty($input['data_inicio']) ? $input['data_inicio'] : date('Y-m-d', strtotime('-7 days'));
            $dataFim = !empty($input['data_fim']) ? $input['data_fim'] : date('Y-m-d');
            
            // Validar limites (máximo 3 meses para o Sicoob)
            $diffDays = (strtotime($dataFim) - strtotime($dataInicio)) / 86400;
            if ($diffDays > 90) {
                $dataInicio = date('Y-m-d', strtotime('-90 days', strtotime($dataFim)));
                $detalhes[] = "Período limitado a 90 dias (máximo da API)";
            }
            if ($diffDays < 0) {
                return $response->json(['error' => 'Data inicial deve ser anterior à data final'], 400);
            }
            
            $detalhes[] = "Período solicitado: " . date('d/m/Y', strtotime($dataInicio)) . " a " . date('d/m/Y', strtotime($dataFim)) . " ({$diffDays} dias)";
            $detalhes[] = "Banco: " . ucfirst($conexao['banco']);
            $detalhes[] = "Conta: " . ($conexao['banco_conta_id'] ?? $conexao['identificacao'] ?? 'N/A');
            
            // Autenticação
            $detalhes[] = "Autenticando...";
            $tokenData = $service->autenticar($conexao);
            if (!empty($tokenData['access_token'])) {
                $detalhes[] = "Token obtido com sucesso (expira em " . ($tokenData['expires_in'] ?? '?') . "s)";
                $conexao['access_token'] = $tokenData['access_token'];
                $conexao['token_expira_em'] = date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600));
            }
            
            // Buscar transações
            $detalhes[] = "Buscando transações na API...";
            $transacoes = $service->getTransacoes($conexao, $dataInicio, $dataFim);
            $totalBanco = count($transacoes);
            
            // Capturar debug da API (se disponível)
            if (property_exists($service, 'lastDebug') && !empty($service->lastDebug)) {
                foreach ($service->lastDebug as $mesDebug) {
                    $mesLabel = $mesDebug['mes'] ?? '?';
                    $mesCount = $mesDebug['transacoes_count'] ?? 0;
                    $mesErro = $mesDebug['erro'] ?? null;
                    $formato = $mesDebug['formato'] ?? 'n/a';
                    $httpCode = $mesDebug['http_code'] ?? '?';
                    
                    if ($mesErro) {
                        $detalhes[] = "Mês {$mesLabel}: ERRO (HTTP {$httpCode}) - {$mesErro}";
                    } else {
                        $info = "Mês {$mesLabel}: HTTP {$httpCode}, {$mesCount} transações, formato: {$formato}";
                        if (!empty($mesDebug['saldoAtual'])) $info .= ", saldo: {$mesDebug['saldoAtual']}";
                        if (!empty($mesDebug['response_keys'])) {
                            $keys = is_array($mesDebug['response_keys']) ? implode(', ', $mesDebug['response_keys']) : $mesDebug['response_keys'];
                            $info .= ", keys: [{$keys}]";
                        }
                        $detalhes[] = $info;
                    }
                    
                    // Mostrar primeira transação bruta para debug do formato
                    if ($mesCount > 0 && !empty($mesDebug['primeira_transacao_raw'])) {
                        $raw1 = $mesDebug['primeira_transacao_raw'];
                        $rawKeys = is_array($raw1) ? implode(', ', array_keys($raw1)) : 'n/a';
                        $rawTipo = $raw1['tipo'] ?? $raw1['tipoTransacao'] ?? $raw1['tipoLancamento'] ?? 'n/a';
                        $rawValor = $raw1['valor'] ?? 'n/a';
                        $detalhes[] = "  Campos da transação: [{$rawKeys}]";
                        $detalhes[] = "  tipo={$rawTipo}, valor={$rawValor}";
                    }
                    
                    // Se 0 transações, mostrar URL completa e preview da resposta
                    if ($mesCount === 0 && empty($mesErro)) {
                        $detalhes[] = "  URL: " . ($mesDebug['url_completa'] ?? $mesDebug['url'] ?? 'n/a');
                        if (!empty($mesDebug['response_raw_preview'])) {
                            $raw = substr($mesDebug['response_raw_preview'], 0, 500);
                            $detalhes[] = "  Resposta API: " . $raw;
                        }
                        if (!empty($mesDebug['estrutura'])) {
                            $detalhes[] = "  Estrutura: " . json_encode($mesDebug['estrutura'], JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
                $debugApi = $service->lastDebug;
            }
            
            $detalhes[] = "Total transações retornadas pela API: {$totalBanco}";
            
            if ($totalBanco === 0) {
                $detalhes[] = "A API retornou 0 transações no período.";
            } else {
                // Contar tipos para debug
                $countDebitos = 0;
                $countCreditos = 0;
                $countOutros = 0;
                $tiposEncontrados = [];
                foreach ($transacoes as $t) {
                    $tp = $t['tipo'] ?? 'vazio';
                    $tiposEncontrados[$tp] = ($tiposEncontrados[$tp] ?? 0) + 1;
                    if ($tp === 'debito') $countDebitos++;
                    elseif ($tp === 'credito') $countCreditos++;
                    else $countOutros++;
                }
                $detalhes[] = "Tipos: {$countDebitos} débitos, {$countCreditos} créditos" . ($countOutros > 0 ? ", {$countOutros} outros" : "");
                if ($countOutros > 0 || ($countDebitos === 0 && $countCreditos === 0)) {
                    $detalhes[] = "Distribuição de tipos: " . json_encode($tiposEncontrados, JSON_UNESCAPED_UNICODE);
                }
                
                // Amostra da primeira transação
                if (!empty($transacoes[0])) {
                    $amostra = $transacoes[0];
                    $detalhes[] = "Amostra 1ª transação: tipo={$amostra['tipo']}, valor={$amostra['valor']}, data={$amostra['data_transacao']}, desc=" . substr($amostra['descricao_original'] ?? '', 0, 50);
                }
            }
            
            // Filtrar transações conforme tipo_sync configurado
            $tipoSync = $conexao['tipo_sync'] ?? 'ambos';
            $totalFiltradas = 0;
            if ($tipoSync !== 'ambos') {
                $totalAntes = count($transacoes);
                $transacoes = array_filter($transacoes, function($t) use ($tipoSync) {
                    if ($tipoSync === 'apenas_despesas') {
                        return ($t['tipo'] ?? '') === 'debito';
                    }
                    if ($tipoSync === 'apenas_receitas') {
                        return ($t['tipo'] ?? '') === 'credito';
                    }
                    return true;
                });
                $transacoes = array_values($transacoes);
                $totalFiltradas = $totalAntes - count($transacoes);
                $tipoLabel = $tipoSync === 'apenas_despesas' ? 'Apenas despesas (débitos)' : 'Apenas receitas (créditos)';
                $detalhes[] = "Filtro tipo_sync: {$tipoLabel} → mantidas " . count($transacoes) . " de {$totalAntes} ({$totalFiltradas} removidas)";
                
                if (count($transacoes) === 0 && $totalAntes > 0) {
                    $detalhes[] = "⚠ TODAS as transações foram filtradas! Verifique se o filtro está correto ou altere para 'Ambos'";
                }
            }
            
            // Processar e salvar transações
            $resultado = $this->processarTransacoes($transacoes, $conexao);
            
            if ($resultado['novas'] > 0) {
                $detalhes[] = "{$resultado['novas']} transações novas importadas";
            }
            if ($resultado['duplicadas'] > 0) {
                $detalhes[] = "{$resultado['duplicadas']} já existiam em transações pendentes (hash duplicado)";
            }
            if ($resultado['ja_lancadas'] > 0) {
                $detalhes[] = "{$resultado['ja_lancadas']} já encontradas em contas a pagar/receber (ignoradas)";
            }
            if ($resultado['erros'] > 0) {
                $detalhes[] = "{$resultado['erros']} erros ao processar";
            }
            
            // Atualizar saldo
            try {
                $saldoData = $service->getSaldo($conexao);
                $this->conexaoModel->atualizarSaldo($id, $saldoData['saldo']);
                $saldoInfo = 'R$ ' . number_format($saldoData['saldo'], 2, ',', '.');
                
                if (!empty($conexao['conta_bancaria_id'])) {
                    $contaBancariaModel = new \App\Models\ContaBancaria();
                    $contaBancariaModel->setSaldoReal($conexao['conta_bancaria_id'], $saldoData['saldo']);
                }
                $detalhes[] = "Saldo atualizado: {$saldoInfo}";
            } catch (\Exception $e) {
                $detalhes[] = "Saldo: erro ao obter (" . $e->getMessage() . ")";
            }
            
            // Atualizar data de sincronização
            $this->conexaoModel->atualizarUltimaSync($id);
            $this->conexaoModel->update($id, ['status_conexao' => 'ativa', 'ultimo_erro' => null]);
            
            // Montar mensagem resumida
            $totalAposFiltro = count($transacoes) + $resultado['novas'] + $resultado['duplicadas'] + $resultado['ja_lancadas'] + $resultado['erros'];
            if ($totalBanco === 0) {
                $mensagem = "API retornou 0 transações no período " . date('d/m', strtotime($dataInicio)) . " a " . date('d/m', strtotime($dataFim)) . ".";
            } elseif ($totalFiltradas > 0 && $totalFiltradas === $totalBanco) {
                $mensagem = "{$totalBanco} transações do banco, mas todas foram removidas pelo filtro de tipo.";
            } elseif ($totalFiltradas > 0 && $resultado['novas'] > 0) {
                $mensagem = "{$resultado['novas']} nova(s) importada(s) de {$totalBanco} do banco ({$totalFiltradas} removidas pelo filtro).";
            } elseif ($resultado['novas'] > 0) {
                $mensagem = "{$resultado['novas']} nova(s) transação(ões) importada(s) de {$totalBanco} do banco.";
            } elseif ($resultado['duplicadas'] > 0 || $resultado['ja_lancadas'] > 0) {
                $mensagem = "Todas as transações do período já foram importadas ou lançadas.";
            } else {
                $mensagem = "{$totalBanco} transações encontradas no banco.";
            }
            
            if ($saldoInfo) {
                $mensagem .= " Saldo: {$saldoInfo}";
            }
            
            return $response->json([
                'success' => true,
                'message' => $mensagem,
                'resumo' => [
                    'total_banco' => $totalBanco,
                    'filtradas' => $totalFiltradas,
                    'novas' => $resultado['novas'],
                    'duplicadas' => $resultado['duplicadas'],
                    'ja_lancadas' => $resultado['ja_lancadas'],
                    'erros' => $resultado['erros'],
                    'saldo' => $saldoInfo,
                    'periodo' => date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim))
                ],
                'detalhes' => $detalhes,
                'debug_api' => $debugApi
            ]);
            
        } catch (\Exception $e) {
            $this->conexaoModel->registrarErro($id, $e->getMessage());
            $detalhes[] = "ERRO: " . $e->getMessage();
            return $response->json([
                'error' => 'Erro ao sincronizar: ' . $e->getMessage(),
                'detalhes' => $detalhes
            ], 500);
        }
    }
    
    /**
     * Processar transações sincronizadas
     * Verifica duplicatas contra: transacoes_pendentes (hash), contas_pagar, contas_receber, movimentacoes_caixa
     * Retorna array com contadores: novas, duplicadas, ja_lancadas, erros
     */
    private function processarTransacoes($transacoes, $conexao)
    {
        $transacaoPendenteModel = new TransacaoPendente();
        $classificadorService = new ClassificadorIAService($conexao['empresa_id']);
        
        $novas = 0;
        $duplicadas = 0;
        $jaLancadas = 0;
        $erros = 0;
        
        $empresaId = $conexao['empresa_id'];
        $contaBancariaId = $conexao['conta_bancaria_id'] ?? null;
        
        foreach ($transacoes as $transacao) {
            try {
                // Verificar se já existe em contas_pagar ou contas_receber (por valor + data + empresa)
                if ($this->transacaoJaLancada($empresaId, $contaBancariaId, $transacao)) {
                    $jaLancadas++;
                    continue;
                }
                
                // Classificar (regras fixas -> histórico -> IA -> fallback)
                $classificacao = $classificadorService->analisar($transacao);
                
                $transacaoData = [
                    'empresa_id' => $empresaId,
                    'conexao_bancaria_id' => $conexao['id'],
                    'data_transacao' => $transacao['data_transacao'],
                    'descricao_original' => $transacao['descricao_original'],
                    'valor' => $transacao['valor'],
                    'tipo' => $transacao['tipo'],
                    'origem' => $transacao['origem'],
                    'banco_transacao_id' => $transacao['banco_transacao_id'] ?? null,
                    'metodo_pagamento' => $transacao['metodo_pagamento'] ?? null,
                    'saldo_apos' => $transacao['saldo_apos'] ?? null,
                    'referencia_externa' => $transacao['banco_transacao_id'] ?? null,
                    'categoria_sugerida_id' => $classificacao['categoria_id'] ?? $conexao['categoria_padrao_id'],
                    'centro_custo_sugerido_id' => $classificacao['centro_custo_id'] ?? $conexao['centro_custo_padrao_id'],
                    'fornecedor_sugerido_id' => $classificacao['fornecedor_id'] ?? null,
                    'cliente_sugerido_id' => $classificacao['cliente_id'] ?? null,
                    'confianca_ia' => $classificacao['confianca'] ?? null,
                    'justificativa_ia' => $classificacao['justificativa'] ?? null,
                    'status' => 'pendente'
                ];
                
                $novaId = $transacaoPendenteModel->create($transacaoData);
                if ($novaId) {
                    $novas++;
                } else {
                    $duplicadas++;
                }
            } catch (\Exception $e) {
                $erros++;
            }
        }
        
        return [
            'novas' => $novas,
            'duplicadas' => $duplicadas,
            'ja_lancadas' => $jaLancadas,
            'erros' => $erros
        ];
    }
    
    /**
     * Verifica se a transação do banco já foi lançada manualmente
     * em contas_pagar, contas_receber ou movimentacoes_caixa.
     * Compara por: empresa_id + valor + data + conta_bancaria_id (se disponível)
     */
    private function transacaoJaLancada($empresaId, $contaBancariaId, $transacao): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $valor = abs((float) $transacao['valor']);
        $data = $transacao['data_transacao'];
        $tipo = $transacao['tipo'] ?? '';
        
        // Margem de R$ 0.01 para evitar problemas de arredondamento
        $valorMin = $valor - 0.01;
        $valorMax = $valor + 0.01;
        
        // Verificar em contas_pagar (débitos)
        if ($tipo === 'debito') {
            $sql = "SELECT COUNT(*) FROM contas_pagar 
                    WHERE empresa_id = :empresa_id 
                    AND ABS(valor_total) BETWEEN :valor_min AND :valor_max
                    AND (data_vencimento = :data OR data_pagamento = :data2)
                    AND status != 'cancelado'
                    AND deleted_at IS NULL";
            $params = [
                'empresa_id' => $empresaId,
                'valor_min' => $valorMin,
                'valor_max' => $valorMax,
                'data' => $data,
                'data2' => $data
            ];
            
            if ($contaBancariaId) {
                $sql .= " AND conta_bancaria_id = :conta_id";
                $params['conta_id'] = $contaBancariaId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }
        
        // Verificar em contas_receber (créditos)
        if ($tipo === 'credito') {
            $sql = "SELECT COUNT(*) FROM contas_receber 
                    WHERE empresa_id = :empresa_id 
                    AND ABS(valor_total) BETWEEN :valor_min AND :valor_max
                    AND (data_vencimento = :data OR data_recebimento = :data2)
                    AND status != 'cancelado'
                    AND deleted_at IS NULL";
            $params = [
                'empresa_id' => $empresaId,
                'valor_min' => $valorMin,
                'valor_max' => $valorMax,
                'data' => $data,
                'data2' => $data
            ];
            
            if ($contaBancariaId) {
                $sql .= " AND conta_bancaria_id = :conta_id";
                $params['conta_id'] = $contaBancariaId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }
        
        // Verificar em movimentacoes_caixa (ambos)
        $tipoMov = $tipo === 'debito' ? 'saida' : 'entrada';
        $sql = "SELECT COUNT(*) FROM movimentacoes_caixa 
                WHERE empresa_id = :empresa_id 
                AND tipo = :tipo_mov
                AND ABS(valor) BETWEEN :valor_min AND :valor_max
                AND data_movimentacao = :data";
        $params = [
            'empresa_id' => $empresaId,
            'tipo_mov' => $tipoMov,
            'valor_min' => $valorMin,
            'valor_max' => $valorMax,
            'data' => $data
        ];
        
        if ($contaBancariaId) {
            $sql .= " AND conta_bancaria_id = :conta_id";
            $params['conta_id'] = $contaBancariaId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Importar extrato completo (crédito + débito) apenas para visualização
     */
    public function importarExtrato(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->getConexaoComCredenciais($id);
        
        if (!$conexao) {
            return $response->json(['error' => 'Conexão não encontrada'], 404);
        }
        
        $detalhes = [];
        
        try {
            $service = BankServiceFactory::create($conexao['banco']);
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $dataInicio = !empty($input['data_inicio']) ? $input['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
            $dataFim = !empty($input['data_fim']) ? $input['data_fim'] : date('Y-m-d');
            
            $diffDays = (strtotime($dataFim) - strtotime($dataInicio)) / 86400;
            if ($diffDays > 90) {
                $dataInicio = date('Y-m-d', strtotime('-90 days', strtotime($dataFim)));
                $detalhes[] = "Período limitado a 90 dias (máximo da API)";
            }
            
            $detalhes[] = "Período: " . date('d/m/Y', strtotime($dataInicio)) . " a " . date('d/m/Y', strtotime($dataFim));
            
            // Autenticação
            $detalhes[] = "Autenticando...";
            $tokenData = $service->autenticar($conexao);
            if (!empty($tokenData['access_token'])) {
                $detalhes[] = "Token obtido com sucesso";
                $conexao['access_token'] = $tokenData['access_token'];
                $conexao['token_expira_em'] = date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600));
            }
            
            // Buscar TODAS as transações (sem filtro de tipo)
            $detalhes[] = "Buscando extrato completo na API...";
            $transacoes = $service->getTransacoes($conexao, $dataInicio, $dataFim);
            $totalBanco = count($transacoes);
            $detalhes[] = "API retornou {$totalBanco} transações";
            
            if ($totalBanco === 0) {
                return $response->json([
                    'success' => true,
                    'message' => "Nenhuma transação encontrada no período.",
                    'resumo' => ['total' => 0, 'novas' => 0, 'duplicadas' => 0, 'debitos' => 0, 'creditos' => 0],
                    'detalhes' => $detalhes
                ]);
            }
            
            // Salvar no extrato de visualização
            $extratoModel = new ExtratoBancarioApi();
            $novas = 0;
            $duplicadas = 0;
            $debitos = 0;
            $creditos = 0;
            $totalDebitos = 0;
            $totalCreditos = 0;
            
            foreach ($transacoes as $t) {
                $tipo = $t['tipo'] ?? 'debito';
                if ($tipo === 'debito') {
                    $debitos++;
                    $totalDebitos += abs($t['valor']);
                } else {
                    $creditos++;
                    $totalCreditos += abs($t['valor']);
                }
                
                $novaId = $extratoModel->inserir([
                    'empresa_id' => $conexao['empresa_id'],
                    'conexao_bancaria_id' => $conexao['id'],
                    'conta_bancaria_id' => $conexao['conta_bancaria_id'] ?? null,
                    'data_transacao' => $t['data_transacao'],
                    'descricao' => $t['descricao_original'] ?? $t['descricao'] ?? '',
                    'valor' => $t['valor'],
                    'tipo' => $tipo,
                    'saldo_apos' => $t['saldo_apos'] ?? null,
                    'banco_transacao_id' => $t['banco_transacao_id'] ?? null,
                    'metodo_pagamento' => $t['metodo_pagamento'] ?? null,
                    'origem' => $t['origem'] ?? 'api',
                    'dados_raw' => $t
                ]);
                
                if ($novaId) {
                    $novas++;
                } else {
                    $duplicadas++;
                }
            }
            
            $detalhes[] = "{$novas} novas transações salvas no extrato";
            if ($duplicadas > 0) {
                $detalhes[] = "{$duplicadas} já existiam (duplicadas)";
            }
            $detalhes[] = "Débitos: {$debitos} (R$ " . number_format($totalDebitos, 2, ',', '.') . ")";
            $detalhes[] = "Créditos: {$creditos} (R$ " . number_format($totalCreditos, 2, ',', '.') . ")";
            
            // Atualizar saldo
            $saldoInfo = null;
            try {
                $saldoData = $service->getSaldo($conexao);
                $this->conexaoModel->atualizarSaldo($id, $saldoData['saldo']);
                $saldoInfo = 'R$ ' . number_format($saldoData['saldo'], 2, ',', '.');
            } catch (\Exception $e) {
                // silencioso
            }
            
            $mensagem = "{$novas} transações importadas para o extrato ({$debitos} débitos, {$creditos} créditos).";
            if ($duplicadas > 0) $mensagem .= " {$duplicadas} já existiam.";
            
            return $response->json([
                'success' => true,
                'message' => $mensagem,
                'resumo' => [
                    'total' => $totalBanco,
                    'novas' => $novas,
                    'duplicadas' => $duplicadas,
                    'debitos' => $debitos,
                    'creditos' => $creditos,
                    'total_debitos' => $totalDebitos,
                    'total_creditos' => $totalCreditos,
                    'saldo' => $saldoInfo,
                    'periodo' => date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim))
                ],
                'detalhes' => $detalhes
            ]);
            
        } catch (\Exception $e) {
            $this->conexaoModel->registrarErro($id, $e->getMessage());
            $detalhes[] = "ERRO: " . $e->getMessage();
            return $response->json([
                'error' => 'Erro ao importar extrato: ' . $e->getMessage(),
                'detalhes' => $detalhes
            ], 500);
        }
    }
    
    /**
     * Desativar conexão
     */
    public function destroy(Request $request, Response $response, $id)
    {
        $conexao = $this->conexaoModel->findById($id);
        
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada';
            return $response->redirect('/conexoes-bancarias');
        }
        
        if ($this->conexaoModel->update($id, ['ativo' => 0, 'status_conexao' => 'desconectada'])) {
            $_SESSION['success'] = 'Conexão desativada com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao desativar conexão';
        }
        
        return $response->redirect('/conexoes-bancarias');
    }
    
    /**
     * Validar dados da conexão
     */
    protected function validate($data, $id = null)
    {
        $errors = [];
        
        if (empty($data['banco'])) {
            $errors['banco'] = 'O banco é obrigatório';
        } elseif (!BankServiceFactory::isSuportado($data['banco'])) {
            $errors['banco'] = 'Banco não suportado: ' . $data['banco'];
        }
        
        return $errors;
    }
}
