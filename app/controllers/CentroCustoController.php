<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\CentroCusto;
use App\Models\Empresa;

class CentroCustoController extends Controller
{
    private $centroCustoModel;
    private $empresaModel;

    public function index(Request $request, Response $response)
    {
        try {
            $this->centroCustoModel = new CentroCusto();
            $empresaId = $request->get('empresa_id');
            $ajax = $request->get('ajax');
            
            // Retorna hierárquico ou flat baseado no parâmetro
            $viewMode = $request->get('view', 'flat'); // 'flat' ou 'tree'
            
            if ($viewMode === 'tree') {
                $centrosCusto = $this->centroCustoModel->findHierarchical($empresaId);
            } else {
                $centrosCusto = $this->centroCustoModel->findAll($empresaId);
            }
            
            // Se for requisição AJAX, retorna JSON
            if ($ajax) {
                return $response->json([
                    'success' => true,
                    'centros' => $centrosCusto
                ]);
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            return $this->render('centros_custo/index', [
                'title' => 'Gerenciar Centros de Custo',
                'centrosCusto' => $centrosCusto,
                'empresas' => $empresas,
                'viewMode' => $viewMode,
                'filters' => [
                    'empresa_id' => $empresaId
                ]
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar centros de custo: ' . $e->getMessage();
            $response->redirect('/');
        }
    }

    public function create(Request $request, Response $response)
    {
        try {
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            $empresaId = $request->get('empresa_id');
            
            $this->centroCustoModel = new CentroCusto();
            $centrosPai = [];
            
            if ($empresaId) {
                $centrosPai = $this->centroCustoModel->getAvailableParents($empresaId, null);
            }
            
            return $this->render('centros_custo/create', [
                'title' => 'Novo Centro de Custo',
                'empresas' => $empresas,
                'centrosPai' => $centrosPai,
                'defaultEmpresaId' => $empresaId
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar formulário: ' . $e->getMessage();
            $response->redirect('/centros-custo');
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
                $response->redirect('/centros-custo/create');
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Converte centro_pai_id vazio em NULL
            if (empty($data['centro_pai_id'])) {
                $data['centro_pai_id'] = null;
            }
            
            // Verificar se código automático está habilitado
            $codigoAutoGerado = \App\Models\Configuracao::get('centros_custo.codigo_auto_gerado', true);
            
            // Gerar código automaticamente se habilitado e código não fornecido
            if ($codigoAutoGerado && empty($data['codigo'])) {
                $this->centroCustoModel = new CentroCusto();
                $data['codigo'] = $this->centroCustoModel->gerarProximoCodigo($data['empresa_id']);
            }
            
            // Cria centro de custo
            $this->centroCustoModel = new CentroCusto();
            $id = $this->centroCustoModel->create($data);
            
            $_SESSION['success'] = 'Centro de custo criado com sucesso!';
            $response->redirect('/centros-custo');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar centro de custo: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect('/centros-custo/create');
        }
    }

    public function show(Request $request, Response $response, $id)
    {
        try {
            $this->centroCustoModel = new CentroCusto();
            $centroCusto = $this->centroCustoModel->findById($id);
            
            if (!$centroCusto) {
                $_SESSION['error'] = 'Centro de custo não encontrado!';
                $response->redirect('/centros-custo');
                return;
            }
            
            // Busca empresa
            $this->empresaModel = new Empresa();
            $centroCusto['empresa'] = $this->empresaModel->findById($centroCusto['empresa_id']);
            
            // Busca centro pai se houver
            if ($centroCusto['centro_pai_id']) {
                $centroCusto['centro_pai'] = $this->centroCustoModel->findById($centroCusto['centro_pai_id']);
            }
            
            // Busca centros filhos
            $centroCusto['filhos'] = $this->centroCustoModel->findChildren($id);
            
            // Busca caminho completo
            $centroCusto['path'] = $this->centroCustoModel->getPath($id);
            
            return $this->render('centros_custo/show', [
                'title' => 'Detalhes do Centro de Custo',
                'centroCusto' => $centroCusto
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar centro de custo: ' . $e->getMessage();
            $response->redirect('/centros-custo');
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        try {
            $this->centroCustoModel = new CentroCusto();
            $centroCusto = $this->centroCustoModel->findById($id);
            
            if (!$centroCusto) {
                $_SESSION['error'] = 'Centro de custo não encontrado!';
                $response->redirect('/centros-custo');
                return;
            }
            
            $this->empresaModel = new Empresa();
            $empresas = $this->empresaModel->findAll();
            
            // Busca centros disponíveis para serem pais
            $centrosPai = $this->centroCustoModel->getAvailableParents(
                $centroCusto['empresa_id'], 
                $id
            );
            
            return $this->render('centros_custo/edit', [
                'title' => 'Editar Centro de Custo',
                'centroCusto' => $centroCusto,
                'empresas' => $empresas,
                'centrosPai' => $centrosPai
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao carregar centro de custo: ' . $e->getMessage();
            $response->redirect('/centros-custo');
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
                $response->redirect("/centros-custo/edit/{$id}");
                return;
            }
            
            // Converte ativo para boolean
            $data['ativo'] = isset($data['ativo']) ? 1 : 0;
            
            // Converte centro_pai_id vazio em NULL
            if (empty($data['centro_pai_id'])) {
                $data['centro_pai_id'] = null;
            }
            
            // Atualiza centro de custo
            $this->centroCustoModel = new CentroCusto();
            $this->centroCustoModel->update($id, $data);
            
            $_SESSION['success'] = 'Centro de custo atualizado com sucesso!';
            $response->redirect('/centros-custo');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar centro de custo: ' . $e->getMessage();
            $this->session->set('old', $data ?? []);
            $response->redirect("/centros-custo/edit/{$id}");
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        try {
            $this->centroCustoModel = new CentroCusto();
            
            // Verifica se tem centros filhos
            $children = $this->centroCustoModel->findChildren($id);
            if (!empty($children)) {
                $_SESSION['error'] = 'Não é possível excluir um centro de custo que possui subcentros!';
                $response->redirect('/centros-custo');
                return;
            }
            
            $this->centroCustoModel->delete($id);
            $_SESSION['success'] = 'Centro de custo excluído com sucesso!';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir centro de custo: ' . $e->getMessage();
        }
        
        $response->redirect('/centros-custo');
    }

    protected function validate($data, $id = null)
    {
        $errors = [];
        
        // Empresa
        if (empty($data['empresa_id'])) {
            $errors['empresa_id'] = 'A empresa é obrigatória';
        }
        
        // Código
        $codigoObrigatorio = \App\Models\Configuracao::get('centros_custo.codigo_obrigatorio', false);
        $codigoAutoGerado = \App\Models\Configuracao::get('centros_custo.codigo_auto_gerado', true);
        
        if (empty($data['codigo'])) {
            // Só valida como obrigatório se:
            // 1. A configuração 'codigo_obrigatorio' está ativada, OU
            // 2. A geração automática está desativada
            if ($codigoObrigatorio || !$codigoAutoGerado) {
                $errors['codigo'] = 'O código é obrigatório';
            }
        } else {
            // Se código foi fornecido, valida unicidade
            $this->centroCustoModel = new CentroCusto();
            $existing = $this->centroCustoModel->findByCodigo($data['codigo'], $data['empresa_id']);
            if ($existing && (!$id || $existing['id'] != $id)) {
                $errors['codigo'] = 'Este código já está em uso para esta empresa';
            }
        }
        
        // Nome
        if (empty($data['nome'])) {
            $errors['nome'] = 'O nome é obrigatório';
        }
        
        // Validação de centro pai (evita loops)
        if (!empty($data['centro_pai_id'])) {
            $this->centroCustoModel = new CentroCusto();
            if (!$this->centroCustoModel->canBeParent($id ?? 0, $data['centro_pai_id'])) {
                $errors['centro_pai_id'] = 'Não é possível definir este centro como pai (criaria um loop hierárquico)';
            }
        }
        
        return $errors;
    }
}

