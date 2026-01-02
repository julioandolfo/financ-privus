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
        
        if (empty($grupo)) {
            $_SESSION['error'] = 'Grupo de configuração não especificado.';
            return $response->redirect('/configuracoes');
        }
        
        // DEBUG: Log dos dados recebidos (sempre ativo temporariamente)
        error_log("=== SALVANDO CONFIGURAÇÕES ===");
        error_log("Grupo: {$grupo}");
        error_log("Dados recebidos (POST): " . json_encode($data, JSON_PRETTY_PRINT));
        error_log("Arquivos recebidos: " . json_encode(array_keys($_FILES), JSON_PRETTY_PRINT));
        
        // Remover campos de controle
        unset($data['grupo']);
        
        // Buscar todas as configurações do grupo primeiro
        $configsGrupo = Configuracao::getGrupo($grupo);
        
        // Preparar configurações para salvar
        $configuracoes = [];
        
        // PASSO 1: Processar checkboxes (boolean) PRIMEIRO
        // Checkboxes marcados enviam value="true", desmarcados não enviam nada
        foreach ($configsGrupo as $chave => $config) {
            if ($config['tipo'] === 'boolean') {
                // Se o checkbox foi enviado no POST, está marcado (true)
                // Se não foi enviado, está desmarcado (false)
                $configuracoes[$chave] = isset($data[$chave]);
            }
        }
        
        // PASSO 2: Processar outros campos (string, number, etc)
        foreach ($data as $chave => $valor) {
            // Verificar se a chave existe no grupo de configurações
            if (!isset($configsGrupo[$chave])) {
                continue; // Ignorar campos que não existem no grupo
            }
            
            // Pular se já foi processado como boolean
            if ($configsGrupo[$chave]['tipo'] === 'boolean') {
                continue;
            }
            
            // Para campos de senha/password/key/token, só salvar se não estiver vazio
            if (strpos($chave, 'senha') !== false || strpos($chave, 'password') !== false || 
                strpos($chave, 'key') !== false || strpos($chave, 'token') !== false) {
                // Se o campo tem valor, salvar
                if (!empty(trim($valor))) {
                    $configuracoes[$chave] = trim($valor);
                }
                // Se estiver vazio, não adicionar (manter valor atual no banco)
            } else {
                // Para outros campos, salvar sempre (permite limpar campos)
                $configuracoes[$chave] = is_string($valor) ? trim($valor) : $valor;
            }
        }
        
        // PASSO 3: Processar uploads de arquivos
        if (!empty($_FILES)) {
            foreach ($_FILES as $chave => $file) {
                if (isset($configsGrupo[$chave]) && $file['error'] === UPLOAD_ERR_OK) {
                    $uploadPath = $this->processarUpload($file, $chave);
                    if ($uploadPath) {
                        $configuracoes[$chave] = $uploadPath;
                    }
                }
            }
        }
        
        // DEBUG: Log das configurações a serem salvas (sempre ativo temporariamente)
        error_log("Configurações processadas para salvar:");
        foreach ($configuracoes as $k => $v) {
            $vStr = is_bool($v) ? ($v ? 'TRUE' : 'FALSE') : $v;
            error_log("  - {$k} = {$vStr}");
        }
        
        // Verificar se há configurações para salvar
        if (empty($configuracoes)) {
            // Se não há configurações para salvar, pode ser porque:
            // 1. Apenas campos de senha vazios foram enviados (mantém valores atuais)
            // 2. Nenhum campo foi alterado
            // Neste caso, consideramos sucesso
            Configuracao::clearCache();
            $_SESSION['success'] = 'Configurações verificadas. Nenhuma alteração necessária.';
            return $response->redirect('/configuracoes?aba=' . $grupo);
        }
        
        try {
            $success = Configuracao::setMultiplas($configuracoes);
            
            if ($success) {
                // Limpar cache de configurações
                Configuracao::clearCache();
                $_SESSION['success'] = 'Configurações salvas com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao salvar configurações. Tente novamente.';
            }
        } catch (\Exception $e) {
            // Log do erro
            error_log("Erro ao salvar configurações: " . $e->getMessage());
            $_SESSION['error'] = 'Erro ao salvar configurações: ' . $e->getMessage();
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
