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
        
        // Processar uploads de arquivos
        if (!empty($_FILES)) {
            foreach ($_FILES as $chave => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $uploadPath = $this->processarUpload($file, $chave);
                    if ($uploadPath) {
                        $configuracoes[$chave] = $uploadPath;
                    }
                }
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
    
    /**
     * Processa upload de arquivos (logo/favicon)
     */
    private function processarUpload($file, $chave)
    {
        // Diretório de upload
        $uploadDir = __DIR__ . '/../../storage/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Tipo de arquivo não permitido. Use JPG, PNG, GIF, SVG ou ICO.';
            return false;
        }
        
        // Validar tamanho (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Arquivo muito grande. Tamanho máximo: 2MB.';
            return false;
        }
        
        // Gerar nome único
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $tipoArquivo = str_replace('sistema.', '', $chave); // "logo" ou "favicon"
        $nomeArquivo = $tipoArquivo . '_' . time() . '.' . $ext;
        $caminhoCompleto = $uploadDir . $nomeArquivo;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
            // Retornar caminho relativo para salvar no banco
            return '/storage/uploads/' . $nomeArquivo;
        }
        
        $_SESSION['error'] = 'Erro ao fazer upload do arquivo.';
        return false;
    }
}
