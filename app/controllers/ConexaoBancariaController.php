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
        
        $updateData = [
            'identificacao' => $data['identificacao'] ?? $conexao['identificacao'],
            'auto_sync' => isset($data['auto_sync']) ? 1 : 0,
            'frequencia_sync' => $data['frequencia_sync'] ?? $conexao['frequencia_sync'],
            'categoria_padrao_id' => !empty($data['categoria_padrao_id']) ? $data['categoria_padrao_id'] : null,
            'centro_custo_padrao_id' => !empty($data['centro_custo_padrao_id']) ? $data['centro_custo_padrao_id'] : null,
            'aprovacao_automatica' => isset($data['aprovacao_automatica']) ? 1 : 0,
            'conta_bancaria_id' => !empty($data['conta_bancaria_id']) ? $data['conta_bancaria_id'] : null,
            'banco_conta_id' => $data['banco_conta_id'] ?? $conexao['banco_conta_id'],
            'ativo' => isset($data['ativo']) ? 1 : ($conexao['ativo'] ?? 1)
        ];
        
        // Atualizar credenciais se fornecidas
        if (!empty($data['client_id'])) $updateData['client_id'] = $data['client_id'];
        if (!empty($data['client_secret'])) $updateData['client_secret'] = $data['client_secret'];
        if (!empty($data['access_token'])) $updateData['access_token'] = $data['access_token'];
        if (!empty($data['cert_pem'])) $updateData['cert_pem'] = $data['cert_pem'];
        if (!empty($data['key_pem'])) $updateData['key_pem'] = $data['key_pem'];
        if (!empty($data['cert_password'])) $updateData['cert_password'] = $data['cert_password'];
        if (!empty($data['ambiente'])) $updateData['ambiente'] = $data['ambiente'];
        
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
        
        try {
            $service = BankServiceFactory::create($conexao['banco']);
            
            // Buscar transações dos últimos 30 dias (incluindo hoje)
            $dataInicio = date('Y-m-d', strtotime('-30 days'));
            $dataFim = date('Y-m-d');
            
            $detalhes[] = "Período: " . date('d/m/Y', strtotime($dataInicio)) . " a " . date('d/m/Y');
            
            $transacoes = $service->getTransacoes($conexao, $dataInicio, $dataFim);
            $totalBanco = count($transacoes);
            $detalhes[] = "Transações retornadas pelo banco: {$totalBanco}";
            
            // Filtrar transações conforme tipo_sync configurado
            $tipoSync = $conexao['tipo_sync'] ?? 'ambos';
            $totalFiltradas = 0;
            if ($tipoSync !== 'ambos') {
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
                $totalFiltradas = $totalBanco - count($transacoes);
                $tipoLabel = $tipoSync === 'apenas_despesas' ? 'Apenas despesas' : 'Apenas receitas';
                $detalhes[] = "Filtro aplicado: {$tipoLabel} ({$totalFiltradas} ignoradas)";
            }
            
            // Processar e salvar transações
            $resultado = $this->processarTransacoes($transacoes, $conexao);
            
            if ($resultado['novas'] > 0) {
                $detalhes[] = "{$resultado['novas']} transações novas importadas";
            }
            if ($resultado['duplicadas'] > 0) {
                $detalhes[] = "{$resultado['duplicadas']} já existiam (duplicadas ignoradas)";
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
            } catch (\Exception $e) {
                $detalhes[] = "Saldo: erro ao obter (" . $e->getMessage() . ")";
            }
            
            // Atualizar data de sincronização
            $this->conexaoModel->atualizarUltimaSync($id);
            $this->conexaoModel->update($id, ['status_conexao' => 'ativa', 'ultimo_erro' => null]);
            
            // Montar mensagem resumida
            $mensagem = "Sincronização concluída!";
            if ($totalBanco === 0) {
                $mensagem = "Nenhuma transação encontrada no período.";
            } elseif ($resultado['novas'] > 0) {
                $mensagem = "{$resultado['novas']} nova(s) transação(ões) importada(s).";
            } else {
                $mensagem = "Todas as {$totalBanco} transações do período já foram importadas anteriormente.";
            }
            
            if ($saldoInfo) {
                $mensagem .= "\nSaldo atualizado: {$saldoInfo}";
            }
            
            return $response->json([
                'success' => true,
                'message' => $mensagem,
                'resumo' => [
                    'total_banco' => $totalBanco,
                    'filtradas' => $totalFiltradas,
                    'novas' => $resultado['novas'],
                    'duplicadas' => $resultado['duplicadas'],
                    'erros' => $resultado['erros'],
                    'saldo' => $saldoInfo,
                    'periodo' => $dataInicio . ' a ' . $dataFim
                ],
                'detalhes' => $detalhes
            ]);
            
        } catch (\Exception $e) {
            $this->conexaoModel->registrarErro($id, $e->getMessage());
            return $response->json([
                'error' => 'Erro ao sincronizar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Processar transações sincronizadas
     * Retorna array com contadores: novas, duplicadas, erros
     */
    private function processarTransacoes($transacoes, $conexao)
    {
        $transacaoPendenteModel = new TransacaoPendente();
        $classificadorService = new ClassificadorIAService($conexao['empresa_id']);
        
        $novas = 0;
        $duplicadas = 0;
        $erros = 0;
        
        foreach ($transacoes as $transacao) {
            try {
                // Classificar (regras fixas -> histórico -> IA -> fallback)
                $classificacao = $classificadorService->analisar($transacao);
                
                $transacaoData = [
                    'empresa_id' => $conexao['empresa_id'],
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
            'erros' => $erros
        ];
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
