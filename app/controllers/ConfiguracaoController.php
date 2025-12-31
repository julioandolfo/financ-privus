<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Configuracao;

class ConfiguracaoController extends Controller
{
    /**
     * Exibe a página de configurações
     */
    public function index(Request $request, Response $response)
    {
        $abaAtiva = $request->get('aba', 'empresas');
        
        // Buscar todas as configurações agrupadas
        $grupos = Configuracao::getGrupos();
        $configuracoes = [];
        
        foreach ($grupos as $grupo) {
            $configuracoes[$grupo] = Configuracao::getGrupo($grupo);
        }
        
        return $this->render('configuracoes/index', [
            'title' => 'Configurações do Sistema',
            'abaAtiva' => $abaAtiva,
            'configuracoes' => $configuracoes,
            'grupos' => $grupos
        ]);
    }
    
    /**
     * Salva as configurações
     */
    public function salvar(Request $request, Response $response)
    {
        $data = $request->all();
        $grupo = $data['grupo'] ?? '';
        
        // Remover campos de controle
        unset($data['grupo']);
        
        // Preparar configurações para salvar
        $configuracoes = [];
        foreach ($data as $chave => $valor) {
            // Tratar checkboxes (se não marcado, não vem no POST)
            if (strpos($chave, '.') !== false) {
                $configuracoes[$chave] = $valor;
            }
        }
        
        // Buscar todas as configs do grupo para marcar checkboxes desmarcados como false
        $configsGrupo = Configuracao::getGrupo($grupo);
        foreach ($configsGrupo as $chave => $config) {
            if ($config['tipo'] === 'boolean' && !isset($configuracoes[$chave])) {
                $configuracoes[$chave] = false;
            }
        }
        
        $success = Configuracao::setMultiplas($configuracoes);
        
        if ($success) {
            $_SESSION['success'] = 'Configurações salvas com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao salvar configurações.';
        }
        
        return $response->redirect('/configuracoes?aba=' . $grupo);
    }
}
