<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContaPagar;
use App\Models\PadraoImportacaoExtrato;
use App\Models\Empresa;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Fornecedor;
use App\Models\ContaBancaria;
use App\Models\FormaPagamento;
use includes\services\ExtratoParserService;

/**
 * Controller para Importação de Extratos Bancários
 */
class ExtratoBancarioController extends Controller
{
    private $contaPagarModel;
    private $padraoModel;
    private $empresaModel;
    private $categoriaModel;
    private $centroCustoModel;
    private $fornecedorModel;
    private $contaBancariaModel;
    private $formaPagamentoModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->contaPagarModel = new ContaPagar();
        $this->padraoModel = new PadraoImportacaoExtrato();
        $this->empresaModel = new Empresa();
        $this->categoriaModel = new CategoriaFinanceira();
        $this->centroCustoModel = new CentroCusto();
        $this->fornecedorModel = new Fornecedor();
        $this->contaBancariaModel = new ContaBancaria();
        $this->formaPagamentoModel = new FormaPagamento();
    }
    
    /**
     * Página inicial - Upload de extrato
     */
    public function index(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return $response->redirect('/login');
        }
        
        // Buscar empresas do usuário
        $usuarioModel = new \App\Models\Usuario();
        $empresas = $usuarioModel->getEmpresas($usuarioId);
        
        // Buscar categorias, centros de custo, etc para dropdowns
        $categorias = $this->categoriaModel->findAll(null, 'despesa');
        $centrosCusto = $this->centroCustoModel->findAll();
        $fornecedores = $this->fornecedorModel->findAll();
        $contasBancarias = $this->contaBancariaModel->findAll();
        $formasPagamento = $this->formaPagamentoModel->findAll();
        
        return $this->view('extrato_bancario/index', [
            'empresas' => $empresas,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'fornecedores' => $fornecedores,
            'contasBancarias' => $contasBancarias,
            'formasPagamento' => $formasPagamento,
        ]);
    }
    
    /**
     * Processa upload do arquivo de extrato
     */
    public function upload(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return $response->json(['success' => false, 'error' => 'Usuário não autenticado'], 401);
        }
        
        if (!isset($_FILES['extrato']) || $_FILES['extrato']['error'] !== UPLOAD_ERR_OK) {
            return $response->json(['success' => false, 'error' => 'Erro no upload do arquivo'], 400);
        }
        
        $file = $_FILES['extrato'];
        $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/x-ofx'];
        $allowedExtensions = ['csv', 'ofx', 'txt'];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            return $response->json(['success' => false, 'error' => 'Formato não suportado. Use CSV, OFX ou TXT'], 400);
        }
        
        try {
            // Processar extrato
            $transacoes = ExtratoParserService::processar($file);
            
            // Filtrar apenas débitos
            $debitos = array_filter($transacoes, function($transacao) {
                return ($transacao['tipo'] ?? 'debito') === 'debito';
            });
            
            // Aplicar padrões salvos
            $empresaId = $request->post('empresa_id');
            $transacoesComPadroes = [];
            
            foreach ($debitos as $transacao) {
                $descricaoNormalizada = PadraoImportacaoExtrato::normalizarDescricao($transacao['descricao']);
                $padrao = $this->padraoModel->findByDescricao($descricaoNormalizada, $usuarioId, $empresaId);
                
                // Se não encontrou exato, tenta similar
                if (!$padrao) {
                    $padrao = $this->padraoModel->findSimilar($transacao['descricao'], $usuarioId, $empresaId);
                }
                
                $transacao['descricao_normalizada'] = $descricaoNormalizada;
                $transacao['padrao'] = $padrao;
                $transacao['selecionada'] = true; // Por padrão, todas selecionadas
                
                $transacoesComPadroes[] = $transacao;
            }
            
            // Salvar na sessão para revisão
            $_SESSION['extrato_transacoes'] = $transacoesComPadroes;
            $_SESSION['extrato_empresa_id'] = $empresaId;
            $_SESSION['extrato_arquivo_nome'] = $file['name'];
            
            return $response->json([
                'success' => true,
                'total' => count($transacoesComPadroes),
                'message' => count($transacoesComPadroes) . ' débitos encontrados no extrato'
            ]);
            
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Página de revisão das transações
     */
    public function revisar(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return $response->redirect('/login');
        }
        
        if (!isset($_SESSION['extrato_transacoes'])) {
            $_SESSION['error'] = 'Nenhum extrato carregado. Faça o upload novamente.';
            return $response->redirect('/extrato-bancario');
        }
        
        $transacoes = $_SESSION['extrato_transacoes'];
        $empresaId = $_SESSION['extrato_empresa_id'] ?? null;
        $arquivoNome = $_SESSION['extrato_arquivo_nome'] ?? 'extrato';
        
        // Buscar dados para dropdowns
        $empresa = $this->empresaModel->findById($empresaId);
        $categorias = $this->categoriaModel->findAll($empresaId, 'despesa');
        $centrosCusto = $this->centroCustoModel->findAll($empresaId);
        $fornecedores = $this->fornecedorModel->findAll($empresaId);
        $contasBancarias = $this->contaBancariaModel->findAll($empresaId);
        $formasPagamento = $this->formaPagamentoModel->findAll();
        
        return $this->view('extrato_bancario/revisar', [
            'transacoes' => $transacoes,
            'empresa' => $empresa,
            'empresaId' => $empresaId,
            'arquivoNome' => $arquivoNome,
            'categorias' => $categorias,
            'centrosCusto' => $centrosCusto,
            'fornecedores' => $fornecedores,
            'contasBancarias' => $contasBancarias,
            'formasPagamento' => $formasPagamento,
        ]);
    }
    
    /**
     * Excluir/ignorar uma linha
     */
    public function excluirLinha(Request $request, Response $response)
    {
        $indice = (int)$request->post('indice');
        
        if (isset($_SESSION['extrato_transacoes'][$indice])) {
            unset($_SESSION['extrato_transacoes'][$indice]);
            $_SESSION['extrato_transacoes'] = array_values($_SESSION['extrato_transacoes']); // Reindexar
        }
        
        return $response->json(['success' => true]);
    }
    
    /**
     * Salvar padrão de uma transação
     */
    public function salvarPadrao(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return $response->json(['success' => false, 'error' => 'Usuário não autenticado'], 401);
        }
        
        $indice = (int)$request->post('indice');
        
        if (!isset($_SESSION['extrato_transacoes'][$indice])) {
            return $response->json(['success' => false, 'error' => 'Transação não encontrada'], 404);
        }
        
        $transacao = $_SESSION['extrato_transacoes'][$indice];
        $descricaoNormalizada = $transacao['descricao_normalizada'] ?? PadraoImportacaoExtrato::normalizarDescricao($transacao['descricao']);
        
        $padraoData = [
            'usuario_id' => $usuarioId,
            'empresa_id' => $request->post('empresa_id'),
            'descricao_padrao' => $descricaoNormalizada,
            'descricao_original' => $transacao['descricao'],
            'categoria_id' => !empty($request->post('categoria_id')) ? $request->post('categoria_id') : null,
            'centro_custo_id' => !empty($request->post('centro_custo_id')) ? $request->post('centro_custo_id') : null,
            'fornecedor_id' => !empty($request->post('fornecedor_id')) ? $request->post('fornecedor_id') : null,
            'conta_bancaria_id' => !empty($request->post('conta_bancaria_id')) ? $request->post('conta_bancaria_id') : null,
            'forma_pagamento_id' => !empty($request->post('forma_pagamento_id')) ? $request->post('forma_pagamento_id') : null,
            'tem_rateio' => !empty($request->post('tem_rateio')) ? 1 : 0,
            'observacoes_padrao' => $request->post('observacoes') ?? null,
        ];
        
        $padraoId = $this->padraoModel->saveOrUpdate($padraoData);
        
        // Atualizar transação na sessão com o padrão
        $_SESSION['extrato_transacoes'][$indice]['padrao'] = $this->padraoModel->findById($padraoId);
        
        return $response->json(['success' => true, 'message' => 'Padrão salvo com sucesso']);
    }
    
    /**
     * Cadastrar todas as transações selecionadas em massa
     */
    public function cadastrar(Request $request, Response $response)
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if (!$usuarioId) {
            return $response->json(['success' => false, 'error' => 'Usuário não autenticado'], 401);
        }
        
        if (!isset($_SESSION['extrato_transacoes'])) {
            return $response->json(['success' => false, 'error' => 'Nenhuma transação para cadastrar'], 400);
        }
        
        $transacoes = $_SESSION['extrato_transacoes'];
        $dadosForm = $request->post('transacoes', []);
        
        $cadastradas = 0;
        $erros = [];
        
        foreach ($transacoes as $index => $transacao) {
            // Verificar se está selecionada
            if (empty($dadosForm[$index]['selecionada'])) {
                continue;
            }
            
            $dados = $dadosForm[$index];
            
            // Validar dados mínimos
            if (empty($dados['categoria_id']) || empty($dados['data_vencimento'])) {
                $erros[] = "Linha " . ($index + 1) . ": Categoria e data de vencimento são obrigatórios";
                continue;
            }
            
            // Preparar dados para criar conta a pagar
            $contaData = [
                'empresa_id' => $dados['empresa_id'],
                'fornecedor_id' => !empty($dados['fornecedor_id']) ? $dados['fornecedor_id'] : null,
                'categoria_id' => $dados['categoria_id'],
                'numero_documento' => 'EXT-' . date('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT), // Gerar número único
                'descricao' => $transacao['descricao'],
                'valor_total' => $transacao['valor'],
                'data_emissao' => $transacao['data'],
                'data_competencia' => $transacao['data'],
                'data_vencimento' => $dados['data_vencimento'],
                'centro_custo_id' => !empty($dados['centro_custo_id']) ? $dados['centro_custo_id'] : null,
                'conta_bancaria_id' => !empty($dados['conta_bancaria_id']) ? $dados['conta_bancaria_id'] : null,
                'forma_pagamento_id' => !empty($dados['forma_pagamento_id']) ? $dados['forma_pagamento_id'] : null,
                'tem_rateio' => !empty($dados['tem_rateio']) ? 1 : 0,
                'observacoes' => $dados['observacoes'] ?? null,
                'status' => 'pendente',
                'usuario_cadastro_id' => $usuarioId,
            ];
            
            try {
                $contaId = $this->contaPagarModel->create($contaData);
                
                if ($contaId) {
                    $cadastradas++;
                    
                    // Se marcou como padrão, salvar padrão
                    if (!empty($dados['salvar_padrao'])) {
                        $descricaoNormalizada = $transacao['descricao_normalizada'] ?? PadraoImportacaoExtrato::normalizarDescricao($transacao['descricao']);
                        
                        $padraoData = [
                            'usuario_id' => $usuarioId,
                            'empresa_id' => $dados['empresa_id'],
                            'descricao_padrao' => $descricaoNormalizada,
                            'descricao_original' => $transacao['descricao'],
                            'categoria_id' => $dados['categoria_id'],
                            'centro_custo_id' => !empty($dados['centro_custo_id']) ? $dados['centro_custo_id'] : null,
                            'fornecedor_id' => !empty($dados['fornecedor_id']) ? $dados['fornecedor_id'] : null,
                            'conta_bancaria_id' => !empty($dados['conta_bancaria_id']) ? $dados['conta_bancaria_id'] : null,
                            'forma_pagamento_id' => !empty($dados['forma_pagamento_id']) ? $dados['forma_pagamento_id'] : null,
                            'tem_rateio' => !empty($dados['tem_rateio']) ? 1 : 0,
                            'observacoes_padrao' => $dados['observacoes'] ?? null,
                        ];
                        
                        $this->padraoModel->saveOrUpdate($padraoData);
                    }
                }
            } catch (\Exception $e) {
                $erros[] = "Linha " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        // Limpar sessão
        unset($_SESSION['extrato_transacoes']);
        unset($_SESSION['extrato_empresa_id']);
        unset($_SESSION['extrato_arquivo_nome']);
        
        if ($cadastradas > 0) {
            $_SESSION['success'] = "{$cadastradas} conta(s) cadastrada(s) com sucesso!";
        }
        
        if (!empty($erros)) {
            $_SESSION['error'] = implode('<br>', $erros);
        }
        
        return $response->json([
            'success' => true,
            'cadastradas' => $cadastradas,
            'erros' => $erros,
            'message' => "{$cadastradas} conta(s) cadastrada(s) com sucesso!"
        ]);
    }
}
