<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\EvolutionConfig;
use App\Models\WhatsAppRelatorioRegra;
use App\Models\WhatsAppRelatorioDestinatario;
use App\Models\WhatsAppRelatorioEnvio;
use App\Models\Empresa;
use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use Includes\Services\EvolutionApiService;
use Includes\Services\RelatorioWhatsAppService;

class WhatsAppController extends Controller
{
    private $evolutionModel;
    private $regraModel;
    private $destinatarioModel;
    private $envioModel;
    private $empresaModel;

    public function __construct()
    {
        parent::__construct();
        $this->evolutionModel = new EvolutionConfig();
        $this->regraModel = new WhatsAppRelatorioRegra();
        $this->destinatarioModel = new WhatsAppRelatorioDestinatario();
        $this->envioModel = new WhatsAppRelatorioEnvio();
        $this->empresaModel = new Empresa();
    }

    private function empresaIdSessao()
    {
        return $_SESSION['usuario_empresa_id'] ?? $_SESSION['empresa_id'] ?? null;
    }

    // ================= Dashboard =================

    public function index(Request $request, Response $response)
    {
        $empresaId = $this->empresaIdSessao();
        $conexoes = $this->evolutionModel->findAll($empresaId);
        $regras = $this->regraModel->findAll($empresaId);
        $estatisticasRegras = $this->regraModel->getEstatisticas($empresaId);
        $estatisticasEnvios = $this->envioModel->getEstatisticas($empresaId, 7);
        $ultimosEnvios = $this->envioModel->findRecent(15, $empresaId);

        return $this->render('whatsapp/index', [
            'title' => 'WhatsApp · Relatórios',
            'conexoes' => $conexoes,
            'regras' => $regras,
            'estatisticasRegras' => $estatisticasRegras,
            'estatisticasEnvios' => $estatisticasEnvios,
            'ultimosEnvios' => $ultimosEnvios,
            'tipos' => WhatsAppRelatorioRegra::TIPOS,
        ]);
    }

    // ================= Conexões Evolution =================

    public function conexoesIndex(Request $request, Response $response)
    {
        $empresaId = $this->empresaIdSessao();
        $conexoes = $this->evolutionModel->findAll($empresaId);
        return $this->render('whatsapp/conexoes/index', [
            'title' => 'WhatsApp · Conexões',
            'conexoes' => $conexoes
        ]);
    }

    public function conexaoCreate(Request $request, Response $response)
    {
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        return $this->render('whatsapp/conexoes/form', [
            'title' => 'Nova Conexão Evolution',
            'conexao' => null,
            'empresas' => $empresas,
            'empresaIdSessao' => $this->empresaIdSessao()
        ]);
    }

    public function conexaoStore(Request $request, Response $response)
    {
        $data = $request->all();
        $errors = $this->validarConexao($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect($this->baseUrl('/whatsapp/conexoes/create'));
        }

        try {
            $id = $this->evolutionModel->create([
                'empresa_id' => !empty($data['empresa_id']) ? (int)$data['empresa_id'] : null,
                'nome' => trim($data['nome']),
                'base_url' => trim($data['base_url']),
                'instance_name' => trim($data['instance_name']),
                'api_key' => trim($data['api_key']),
                'webhook_url' => $data['webhook_url'] ?? null,
                'numero_remetente' => $data['numero_remetente'] ?? null,
                'ativo' => $data['ativo'] ?? 0
            ]);
            if ($id) {
                $_SESSION['success'] = 'Conexão Evolution cadastrada com sucesso!';
                return $response->redirect($this->baseUrl('/whatsapp/conexoes'));
            }
            $_SESSION['error'] = 'Não foi possível criar a conexão.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        return $response->redirect($this->baseUrl('/whatsapp/conexoes/create'));
    }

    public function conexaoEdit(Request $request, Response $response, $id)
    {
        $conexao = $this->evolutionModel->findById($id, true);
        if (!$conexao) {
            $_SESSION['error'] = 'Conexão não encontrada.';
            return $response->redirect($this->baseUrl('/whatsapp/conexoes'));
        }
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        return $this->render('whatsapp/conexoes/form', [
            'title' => 'Editar Conexão Evolution',
            'conexao' => $conexao,
            'empresas' => $empresas,
            'empresaIdSessao' => $this->empresaIdSessao()
        ]);
    }

    public function conexaoUpdate(Request $request, Response $response, $id)
    {
        $data = $request->all();
        $errors = $this->validarConexao($data, true);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect($this->baseUrl("/whatsapp/conexoes/{$id}/edit"));
        }

        try {
            $update = [
                'empresa_id' => !empty($data['empresa_id']) ? (int)$data['empresa_id'] : null,
                'nome' => trim($data['nome']),
                'base_url' => trim($data['base_url']),
                'instance_name' => trim($data['instance_name']),
                'webhook_url' => $data['webhook_url'] ?? null,
                'numero_remetente' => $data['numero_remetente'] ?? null,
                'ativo' => $data['ativo'] ?? 0
            ];
            if (!empty($data['api_key'])) {
                $update['api_key'] = trim($data['api_key']);
            }
            $this->evolutionModel->update($id, $update);
            $_SESSION['success'] = 'Conexão atualizada.';
            return $response->redirect($this->baseUrl('/whatsapp/conexoes'));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            return $response->redirect($this->baseUrl("/whatsapp/conexoes/{$id}/edit"));
        }
    }

    public function conexaoDelete(Request $request, Response $response, $id)
    {
        try {
            $this->evolutionModel->delete($id);
            $_SESSION['success'] = 'Conexão removida.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao remover: ' . $e->getMessage();
        }
        return $response->redirect($this->baseUrl('/whatsapp/conexoes'));
    }

    public function conexaoTestar(Request $request, Response $response, $id)
    {
        $conexao = $this->evolutionModel->findById($id, true);
        if (!$conexao) {
            return $response->json(['ok' => false, 'error' => 'Conexão não encontrada'], 404);
        }
        $svc = new EvolutionApiService($conexao['base_url'], $conexao['instance_name'], $conexao['api_key_decrypted']);
        $resp = $svc->verificarConexao();
        $estado = EvolutionApiService::extrairEstado($resp);

        $statusDb = 'erro';
        if (!empty($resp['ok'])) {
            if ($estado === 'open') $statusDb = 'conectado';
            elseif (in_array($estado, ['connecting', 'close', null], true)) $statusDb = 'desconectado';
        }
        $this->evolutionModel->atualizarStatus($id, $statusDb, isset($resp['raw']) ? substr((string)$resp['raw'], 0, 5000) : null);

        return $response->json([
            'ok' => $resp['ok'] ?? false,
            'status_http' => $resp['status'] ?? 0,
            'estado' => $estado,
            'status_db' => $statusDb,
            'error' => $resp['error'] ?? null,
            'body' => $resp['body'] ?? null
        ]);
    }

    public function conexaoQrcode(Request $request, Response $response, $id)
    {
        $conexao = $this->evolutionModel->findById($id, true);
        if (!$conexao) {
            return $response->json(['ok' => false, 'error' => 'Conexão não encontrada'], 404);
        }
        $svc = new EvolutionApiService($conexao['base_url'], $conexao['instance_name'], $conexao['api_key_decrypted']);
        $resp = $svc->gerarQrCode();
        return $response->json([
            'ok' => $resp['ok'] ?? false,
            'body' => $resp['body'] ?? null,
            'error' => $resp['error'] ?? null
        ]);
    }

    private function validarConexao(array $data, $isUpdate = false)
    {
        $errors = [];
        if (empty($data['nome'])) $errors['nome'] = 'Nome é obrigatório';
        if (empty($data['base_url']) || !filter_var($data['base_url'], FILTER_VALIDATE_URL)) $errors['base_url'] = 'URL inválida';
        if (empty($data['instance_name'])) $errors['instance_name'] = 'Instance name é obrigatório';
        if (!$isUpdate && empty($data['api_key'])) $errors['api_key'] = 'API key é obrigatória';
        return $errors;
    }

    // ================= Regras =================

    public function regrasIndex(Request $request, Response $response)
    {
        $empresaId = $this->empresaIdSessao();
        $regras = $this->regraModel->findAll($empresaId);
        return $this->render('whatsapp/regras/index', [
            'title' => 'WhatsApp · Regras de Relatório',
            'regras' => $regras,
            'tipos' => WhatsAppRelatorioRegra::TIPOS
        ]);
    }

    public function regraCreate(Request $request, Response $response)
    {
        $empresaId = $this->empresaIdSessao();
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $conexoes = $this->evolutionModel->findAll($empresaId, true);
        return $this->render('whatsapp/regras/form', [
            'title' => 'Nova Regra de Relatório',
            'regra' => null,
            'empresas' => $empresas,
            'empresaIdSessao' => $empresaId,
            'conexoes' => $conexoes,
            'tipos' => WhatsAppRelatorioRegra::TIPOS,
            'frequencias' => WhatsAppRelatorioRegra::FREQUENCIAS,
            'destinatarios' => [],
            'categorias' => [],
            'centros' => []
        ]);
    }

    public function regraStore(Request $request, Response $response)
    {
        $data = $request->all();
        $errors = $this->validarRegra($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect($this->baseUrl('/whatsapp/regras/create'));
        }

        try {
            $payload = $this->prepararPayloadRegra($data);
            $payload['usuario_cadastro_id'] = $this->getUserId();
            // Calcula próxima execução
            $svc = new RelatorioWhatsAppService();
            $payload['proxima_execucao'] = $svc->calcularProximaExecucao($payload);

            $id = $this->regraModel->create($payload);

            if ($id) {
                $this->salvarDestinatarios($id, $data['destinatarios'] ?? []);
                $_SESSION['success'] = 'Regra criada com sucesso!';
                return $response->redirect($this->baseUrl("/whatsapp/regras/{$id}"));
            }
            $_SESSION['error'] = 'Não foi possível criar a regra.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        return $response->redirect($this->baseUrl('/whatsapp/regras/create'));
    }

    public function regraShow(Request $request, Response $response, $id)
    {
        $regra = $this->regraModel->findById($id);
        if (!$regra) {
            $_SESSION['error'] = 'Regra não encontrada.';
            return $response->redirect($this->baseUrl('/whatsapp/regras'));
        }
        $destinatarios = $this->destinatarioModel->findByRegra($id);
        $envios = $this->envioModel->findByRegra($id, 50);
        return $this->render('whatsapp/regras/show', [
            'title' => $regra['nome'],
            'regra' => $regra,
            'destinatarios' => $destinatarios,
            'envios' => $envios,
            'tipos' => WhatsAppRelatorioRegra::TIPOS
        ]);
    }

    public function regraEdit(Request $request, Response $response, $id)
    {
        $regra = $this->regraModel->findById($id);
        if (!$regra) {
            $_SESSION['error'] = 'Regra não encontrada.';
            return $response->redirect($this->baseUrl('/whatsapp/regras'));
        }
        $empresas = $this->empresaModel->findAll(['ativo' => 1]);
        $conexoes = $this->evolutionModel->findAll($regra['empresa_id']);
        $destinatarios = $this->destinatarioModel->findByRegra($id);

        // Categorias / centros para popular selects
        $catModel = new CategoriaFinanceira();
        $centroModel = new CentroCusto();
        $categorias = $catModel->findAll($regra['empresa_id']);
        $centros = $centroModel->findAll($regra['empresa_id']);

        return $this->render('whatsapp/regras/form', [
            'title' => 'Editar Regra',
            'regra' => $regra,
            'empresas' => $empresas,
            'empresaIdSessao' => $this->empresaIdSessao(),
            'conexoes' => $conexoes,
            'tipos' => WhatsAppRelatorioRegra::TIPOS,
            'frequencias' => WhatsAppRelatorioRegra::FREQUENCIAS,
            'destinatarios' => $destinatarios,
            'categorias' => $categorias,
            'centros' => $centros
        ]);
    }

    public function regraUpdate(Request $request, Response $response, $id)
    {
        $data = $request->all();
        $errors = $this->validarRegra($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return $response->redirect($this->baseUrl("/whatsapp/regras/{$id}/edit"));
        }

        try {
            $payload = $this->prepararPayloadRegra($data);
            $svc = new RelatorioWhatsAppService();
            $payload['proxima_execucao'] = $svc->calcularProximaExecucao($payload);

            $this->regraModel->update($id, $payload);
            $this->salvarDestinatarios($id, $data['destinatarios'] ?? [], true);

            $_SESSION['success'] = 'Regra atualizada!';
            return $response->redirect($this->baseUrl("/whatsapp/regras/{$id}"));
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
            return $response->redirect($this->baseUrl("/whatsapp/regras/{$id}/edit"));
        }
    }

    public function regraDelete(Request $request, Response $response, $id)
    {
        try {
            $this->regraModel->delete($id);
            $_SESSION['success'] = 'Regra removida.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro: ' . $e->getMessage();
        }
        return $response->redirect($this->baseUrl('/whatsapp/regras'));
    }

    public function regraToggle(Request $request, Response $response, $id)
    {
        $this->regraModel->toggleAtivo($id);
        $_SESSION['success'] = 'Status atualizado.';
        return $response->redirect($this->baseUrl('/whatsapp/regras'));
    }

    public function regraExecutar(Request $request, Response $response, $id)
    {
        try {
            $svc = new RelatorioWhatsAppService();
            $resultado = $svc->executarRegra($id, 'manual');
            return $response->json(['ok' => true, 'resultado' => $resultado]);
        } catch (\Throwable $e) {
            return $response->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function regraTestar(Request $request, Response $response, $id)
    {
        $numero = trim((string)$request->post('numero', ''));
        if (!WhatsAppRelatorioDestinatario::validarNumero($numero)) {
            return $response->json(['ok' => false, 'error' => 'Número inválido (use formato 5511999999999)'], 422);
        }
        try {
            $svc = new RelatorioWhatsAppService();
            $r = $svc->enviarTeste($id, $numero);
            return $response->json($r);
        } catch (\Throwable $e) {
            return $response->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function regraPreview(Request $request, Response $response, $id)
    {
        $regra = $this->regraModel->findById($id);
        if (!$regra) {
            return $response->json(['ok' => false, 'error' => 'Regra não encontrada'], 404);
        }
        try {
            $svc = new RelatorioWhatsAppService();
            $dados = $svc->gerarDadosRelatorio($regra);
            $mensagem = $svc->renderizarTemplate($regra, $dados);
            return $response->json([
                'ok' => true,
                'mensagem' => $mensagem,
                'total' => $dados['total'] ?? 0,
                'valor_total' => $dados['valor_total'] ?? 0,
                'titulo' => $dados['titulo'] ?? null
            ]);
        } catch (\Throwable $e) {
            return $response->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function regraEnvios(Request $request, Response $response, $id)
    {
        $regra = $this->regraModel->findById($id);
        if (!$regra) {
            $_SESSION['error'] = 'Regra não encontrada.';
            return $response->redirect($this->baseUrl('/whatsapp/regras'));
        }
        $envios = $this->envioModel->findByRegra($id, 200);
        return $this->render('whatsapp/regras/envios', [
            'title' => 'Histórico · ' . $regra['nome'],
            'regra' => $regra,
            'envios' => $envios
        ]);
    }

    // ================= Destinatários =================

    public function destinatarioStore(Request $request, Response $response, $regraId)
    {
        $data = $request->all();
        if (!WhatsAppRelatorioDestinatario::validarNumero($data['numero'] ?? '')) {
            $_SESSION['error'] = 'Número inválido. Use o formato 5511999999999.';
            return $response->redirect($this->baseUrl("/whatsapp/regras/{$regraId}"));
        }
        $this->destinatarioModel->create([
            'regra_id' => $regraId,
            'nome' => $data['nome'] ?? 'Destinatário',
            'numero' => $data['numero'],
            'ativo' => $data['ativo'] ?? 1
        ]);
        $_SESSION['success'] = 'Destinatário adicionado.';
        return $response->redirect($this->baseUrl("/whatsapp/regras/{$regraId}"));
    }

    public function destinatarioUpdate(Request $request, Response $response, $regraId, $id)
    {
        $data = $request->all();
        if (isset($data['numero']) && !WhatsAppRelatorioDestinatario::validarNumero($data['numero'])) {
            $_SESSION['error'] = 'Número inválido.';
            return $response->redirect($this->baseUrl("/whatsapp/regras/{$regraId}"));
        }
        $this->destinatarioModel->update($id, $data);
        $_SESSION['success'] = 'Destinatário atualizado.';
        return $response->redirect($this->baseUrl("/whatsapp/regras/{$regraId}"));
    }

    public function destinatarioDelete(Request $request, Response $response, $regraId, $id)
    {
        $this->destinatarioModel->delete($id);
        $_SESSION['success'] = 'Destinatário removido.';
        return $response->redirect($this->baseUrl("/whatsapp/regras/{$regraId}"));
    }

    // ================= Helpers =================

    private function validarRegra(array $data)
    {
        $errors = [];
        if (empty($data['nome'])) $errors['nome'] = 'Nome obrigatório';
        if (empty($data['empresa_id'])) $errors['empresa_id'] = 'Empresa obrigatória';
        if (empty($data['evolution_config_id'])) $errors['evolution_config_id'] = 'Conexão Evolution obrigatória';
        if (empty($data['tipo_relatorio']) || !array_key_exists($data['tipo_relatorio'], WhatsAppRelatorioRegra::TIPOS)) {
            $errors['tipo_relatorio'] = 'Tipo de relatório inválido';
        }
        if (empty($data['frequencia']) || !array_key_exists($data['frequencia'], WhatsAppRelatorioRegra::FREQUENCIAS)) {
            $errors['frequencia'] = 'Frequência inválida';
        }
        if (($data['frequencia'] ?? '') === 'intervalo_horas' && empty($data['intervalo_horas'])) {
            $errors['intervalo_horas'] = 'Intervalo em horas é obrigatório';
        }
        return $errors;
    }

    private function prepararPayloadRegra(array $data)
    {
        $filtros = [];
        if (isset($data['dias_atraso_min']) && $data['dias_atraso_min'] !== '') $filtros['dias_atraso_min'] = (int)$data['dias_atraso_min'];
        if (isset($data['dias_atraso_max']) && $data['dias_atraso_max'] !== '') $filtros['dias_atraso_max'] = (int)$data['dias_atraso_max'];
        if (isset($data['dias_vencer']) && $data['dias_vencer'] !== '') $filtros['dias_vencer'] = (int)$data['dias_vencer'];
        if (isset($data['dias_janela']) && $data['dias_janela'] !== '') $filtros['dias_janela'] = (int)$data['dias_janela'];
        if (isset($data['valor_min']) && $data['valor_min'] !== '') $filtros['valor_min'] = (float)str_replace(',', '.', $data['valor_min']);
        if (isset($data['valor_max']) && $data['valor_max'] !== '') $filtros['valor_max'] = (float)str_replace(',', '.', $data['valor_max']);
        if (!empty($data['categorias']) && is_array($data['categorias'])) $filtros['categorias'] = $data['categorias'];
        if (!empty($data['centros_custo']) && is_array($data['centros_custo'])) $filtros['centros_custo'] = $data['centros_custo'];

        $diasSemana = null;
        if (!empty($data['dias_semana']) && is_array($data['dias_semana'])) {
            $diasSemana = implode(',', array_map('intval', $data['dias_semana']));
        }

        return [
            'empresa_id' => (int)$data['empresa_id'],
            'evolution_config_id' => (int)$data['evolution_config_id'],
            'nome' => trim($data['nome']),
            'descricao' => $data['descricao'] ?? null,
            'ativo' => $data['ativo'] ?? 0,
            'tipo_relatorio' => $data['tipo_relatorio'],
            'filtros' => $filtros,
            'frequencia' => $data['frequencia'],
            'horario' => !empty($data['horario']) ? $data['horario'] . ':00' : null,
            'dias_semana' => $diasSemana,
            'dia_mes' => !empty($data['dia_mes']) ? (int)$data['dia_mes'] : null,
            'intervalo_horas' => !empty($data['intervalo_horas']) ? (int)$data['intervalo_horas'] : null,
            'template_mensagem' => $data['template_mensagem'] ?? null,
            'incluir_lista_detalhada' => $data['incluir_lista_detalhada'] ?? 0,
            'limite_itens_lista' => $data['limite_itens_lista'] ?? 20,
        ];
    }

    private function salvarDestinatarios($regraId, $destinatarios, $substituir = false)
    {
        if (!is_array($destinatarios)) return;
        if ($substituir) {
            $this->destinatarioModel->deleteByRegra($regraId);
        }
        foreach ($destinatarios as $d) {
            if (empty($d['numero'])) continue;
            if (!WhatsAppRelatorioDestinatario::validarNumero($d['numero'])) continue;
            $this->destinatarioModel->create([
                'regra_id' => $regraId,
                'nome' => $d['nome'] ?? 'Destinatário',
                'numero' => $d['numero'],
                'ativo' => $d['ativo'] ?? 1
            ]);
        }
    }
}
