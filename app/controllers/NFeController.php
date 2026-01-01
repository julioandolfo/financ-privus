<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\NFeEmitida;
use App\Models\PedidoVinculado;
use App\Models\IntegracaoConfig;
use App\Models\IntegracaoWebmaniBR;
use Includes\Services\WebmaniBRService;

class NFeController extends Controller
{
    private $nfeModel;
    private $pedidoModel;
    private $integracaoModel;
    private $webmanibrModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->nfeModel = new NFeEmitida();
        $this->pedidoModel = new PedidoVinculado();
        $this->integracaoModel = new IntegracaoConfig();
        $this->webmanibrModel = new IntegracaoWebmaniBR();
    }
    
    /**
     * Lista todas as NF-es
     */
    public function index(Request $request, Response $response)
    {
        $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
        
        $filters = [
            'status' => $request->get('status'),
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim')
        ];
        
        $nfes = $this->nfeModel->findAll($empresaId, $filters);
        $estatisticas = $this->nfeModel->getEstatisticas($empresaId);
        
        return $this->render('nfes/index', [
            'nfes' => $nfes,
            'estatisticas' => $estatisticas,
            'filters' => $filters
        ]);
    }
    
    /**
     * Exibe detalhes de uma NF-e
     */
    public function show(Request $request, Response $response, $id)
    {
        $nfe = $this->nfeModel->findById($id);
        
        if (!$nfe) {
            $_SESSION['error'] = 'NF-e não encontrada.';
            return $response->redirect('/nfes');
        }
        
        // Buscar pedido vinculado se existir
        $pedido = null;
        if ($nfe['pedido_id']) {
            $pedido = $this->pedidoModel->findById($nfe['pedido_id']);
        }
        
        return $this->render('nfes/show', [
            'nfe' => $nfe,
            'pedido' => $pedido
        ]);
    }
    
    /**
     * Emitir NF-e a partir de um pedido
     */
    public function emitir(Request $request, Response $response, $pedidoId)
    {
        try {
            $empresaId = $_SESSION['usuario_empresa_id'] ?? null;
            
            // Buscar pedido
            $pedido = $this->pedidoModel->findById($pedidoId);
            if (!$pedido) {
                throw new \Exception('Pedido não encontrado.');
            }
            
            // Buscar integração WebmaniaBR da empresa
            $integracao = $this->integracaoModel->findByEmpresaAndTipo($empresaId, IntegracaoConfig::TIPO_WEBMANIBR);
            if (!$integracao || !$integracao['ativo']) {
                throw new \Exception('Integração WebmaniaBR não configurada ou inativa.');
            }
            
            // Buscar configuração WebmaniaBR
            $config = $this->webmanibrModel->findByIntegracao($integracao['id']);
            if (!$config) {
                throw new \Exception('Configuração WebmaniaBR não encontrada.');
            }
            
            // Preparar dados da nota
            $dadosNota = $this->prepararDadosNota($pedido, $config);
            
            // Inicializar serviço
            $service = new WebmaniBRService([
                'consumer_key' => $config['consumer_key'],
                'consumer_secret' => $config['consumer_secret'],
                'access_token' => $config['access_token'],
                'access_token_secret' => $config['access_token_secret'],
                'ambiente' => $config['ambiente']
            ]);
            
            // Emitir NF-e
            $resultado = $service->emitirNFe($dadosNota);
            
            // Salvar NF-e no banco
            $nfeId = $this->nfeModel->create([
                'empresa_id' => $empresaId,
                'pedido_id' => $pedidoId,
                'integracao_id' => $integracao['id'],
                'uuid' => $resultado['uuid'] ?? uniqid(),
                'chave_nfe' => $resultado['chave'] ?? '',
                'numero_nfe' => $resultado['numero'] ?? 0,
                'serie_nfe' => $resultado['serie'] ?? '1',
                'modelo' => '55',
                'status' => $resultado['status'] ?? 'processando',
                'data_emissao' => date('Y-m-d H:i:s'),
                'valor_total' => $pedido['valor_total'],
                'cliente_nome' => $pedido['cliente_nome'],
                'cliente_documento' => $pedido['cliente_cpf_cnpj']
            ]);
            
            // Atualizar com dados adicionais se disponíveis
            if (!empty($resultado['protocolo'])) {
                $this->nfeModel->updateStatus($nfeId, 'autorizada', null, [
                    'protocolo' => $resultado['protocolo'],
                    'data_autorizacao' => date('Y-m-d H:i:s'),
                    'xml_nfe' => $resultado['xml'] ?? null,
                    'danfe_url' => $resultado['danfe'] ?? null,
                    'xml_url' => $resultado['xml_url'] ?? null
                ]);
            }
            
            $_SESSION['success'] = 'NF-e emitida com sucesso!';
            return $response->redirect('/nfes/' . $nfeId);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao emitir NF-e: ' . $e->getMessage();
            return $response->redirect('/pedidos/' . $pedidoId);
        }
    }
    
    /**
     * Cancelar NF-e
     */
    public function cancelar(Request $request, Response $response, $id)
    {
        try {
            $data = $request->all();
            $motivo = $data['motivo'] ?? 'Cancelamento a pedido do cliente';
            
            $nfe = $this->nfeModel->findById($id);
            if (!$nfe) {
                throw new \Exception('NF-e não encontrada.');
            }
            
            if ($nfe['status'] != 'autorizada') {
                throw new \Exception('Apenas NF-es autorizadas podem ser canceladas.');
            }
            
            // Buscar configuração
            $integracao = $this->integracaoModel->findById($nfe['integracao_id']);
            $config = $this->webmanibrModel->findByIntegracao($integracao['id']);
            
            // Inicializar serviço
            $service = new WebmaniBRService([
                'consumer_key' => $config['consumer_key'],
                'consumer_secret' => $config['consumer_secret'],
                'access_token' => $config['access_token'],
                'access_token_secret' => $config['access_token_secret'],
                'ambiente' => $config['ambiente']
            ]);
            
            // Cancelar na WebmaniaBR
            $resultado = $service->cancelarNFe($nfe['chave_nfe'], $motivo);
            
            // Atualizar status
            $this->nfeModel->updateStatus($id, 'cancelada', $motivo);
            
            $_SESSION['success'] = 'NF-e cancelada com sucesso!';
            return $response->redirect('/nfes/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar NF-e: ' . $e->getMessage();
            return $response->redirect('/nfes/' . $id);
        }
    }
    
    /**
     * Consultar status da NF-e
     */
    public function consultar(Request $request, Response $response, $id)
    {
        try {
            $nfe = $this->nfeModel->findById($id);
            if (!$nfe) {
                throw new \Exception('NF-e não encontrada.');
            }
            
            // Buscar configuração
            $integracao = $this->integracaoModel->findById($nfe['integracao_id']);
            $config = $this->webmanibrModel->findByIntegracao($integracao['id']);
            
            // Inicializar serviço
            $service = new WebmaniBRService([
                'consumer_key' => $config['consumer_key'],
                'consumer_secret' => $config['consumer_secret'],
                'access_token' => $config['access_token'],
                'access_token_secret' => $config['access_token_secret'],
                'ambiente' => $config['ambiente']
            ]);
            
            // Consultar status
            $resultado = $service->consultarNFe($nfe['chave_nfe']);
            
            // Atualizar status se mudou
            if (isset($resultado['status']) && $resultado['status'] != $nfe['status']) {
                $this->nfeModel->updateStatus($id, $resultado['status'], $resultado['motivo'] ?? null, [
                    'protocolo' => $resultado['protocolo'] ?? null,
                    'xml_nfe' => $resultado['xml'] ?? null,
                    'danfe_url' => $resultado['danfe'] ?? null,
                    'xml_url' => $resultado['xml_url'] ?? null
                ]);
            }
            
            $_SESSION['success'] = 'Status atualizado com sucesso!';
            return $response->redirect('/nfes/' . $id);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao consultar NF-e: ' . $e->getMessage();
            return $response->redirect('/nfes/' . $id);
        }
    }
    
    /**
     * Download do XML
     */
    public function downloadXML(Request $request, Response $response, $id)
    {
        $nfe = $this->nfeModel->findById($id);
        
        if (!$nfe || empty($nfe['xml_nfe'])) {
            $_SESSION['error'] = 'XML não disponível.';
            return $response->redirect('/nfes/' . $id);
        }
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="NFe_' . $nfe['chave_nfe'] . '.xml"');
        echo $nfe['xml_nfe'];
        exit;
    }
    
    /**
     * Download do DANFE
     */
    public function downloadDANFE(Request $request, Response $response, $id)
    {
        $nfe = $this->nfeModel->findById($id);
        
        if (!$nfe || empty($nfe['danfe_url'])) {
            $_SESSION['error'] = 'DANFE não disponível.';
            return $response->redirect('/nfes/' . $id);
        }
        
        // Redirecionar para URL do DANFE na WebmaniaBR
        return $response->redirect($nfe['danfe_url']);
    }
    
    /**
     * Prepara dados da nota a partir do pedido
     */
    private function prepararDadosNota($pedido, $config)
    {
        // Esta é uma estrutura básica
        // Em produção, você precisará mapear todos os campos necessários
        return [
            'natureza_operacao' => $config['natureza_operacao'],
            'cliente' => [
                'cpf_cnpj' => $pedido['cliente_cpf_cnpj'],
                'nome_razao_social' => $pedido['cliente_nome'],
                'email' => $pedido['cliente_email'] ?? ''
            ],
            'produtos' => [], // Buscar itens do pedido
            'pedido' => [
                'numero' => $pedido['numero_pedido'],
                'valor_total' => $pedido['valor_total']
            ]
        ];
    }
}
