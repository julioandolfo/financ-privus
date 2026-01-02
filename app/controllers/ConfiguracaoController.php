<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Configuracao;

class ConfiguracaoController extends Controller
{
    private $logFile;
    
    public function __construct()
    {
        parent::__construct();
        $this->logFile = __DIR__ . '/../../storage/logs/configuracoes.log';
        
        // Criar diretório se não existir
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Loga mensagem específica de configurações
     */
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Exibe os logs de configuração
     */
    public function verLogs(Request $request, Response $response)
    {
        if (!file_exists($this->logFile)) {
            $logs = "Nenhum log ainda.";
        } else {
            $logs = file_get_contents($this->logFile);
            // Pegar últimas 200 linhas
            $lines = explode("\n", $logs);
            $lines = array_slice($lines, -200);
            $logs = implode("\n", $lines);
        }
        
        return $this->render('configuracoes/logs', [
            'title' => 'Logs de Configurações',
            'logs' => $logs
        ]);
    }
    
    /**
     * Limpa os logs
     */
    public function limparLogs(Request $request, Response $response)
    {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
        $_SESSION['success'] = 'Logs limpos com sucesso!';
        return $response->redirect('/configuracoes/logs');
    }
    
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
        $this->log("========================================");
        $this->log("NOVA REQUISIÇÃO DE SALVAMENTO");
        $this->log("========================================");
        
        $data = $request->all();
        $grupo = $data['grupo'] ?? '';
        
        $this->log("Grupo recebido: " . ($grupo ?: '(vazio)'));
        $this->log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        $this->log("URI: " . $_SERVER['REQUEST_URI']);
        $this->log("IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
        
        if (empty($grupo)) {
            $this->log("ERRO: Grupo vazio!");
            $_SESSION['error'] = 'Grupo de configuração não especificado.';
            return $response->redirect('/configuracoes');
        }
        
        // Log dos dados recebidos
        $this->log("Dados POST completos (ANTES da conversão):");
        foreach ($data as $key => $value) {
            $valueStr = is_array($value) ? json_encode($value) : $value;
            $this->log("  - {$key} = {$valueStr}");
        }
        
        if (!empty($_FILES)) {
            $this->log("Arquivos recebidos: " . json_encode(array_keys($_FILES)));
        }
        
        // Remover campos de controle
        unset($data['grupo']);
        
        // CORREÇÃO: PHP converte pontos (.) em underscores (_) automaticamente nos nomes de campos POST!
        // Precisamos reverter isso para as chaves que pertencem ao grupo
        $this->log("Convertendo underscores de volta para pontos...");
        $dataCorrigido = [];
        $prefixo = $grupo . '_';
        $prefixoComPonto = $grupo . '.';
        
        foreach ($data as $key => $value) {
            // Se a chave começa com "grupo_", converter para "grupo."
            if (strpos($key, $prefixo) === 0) {
                $novaChave = str_replace($prefixo, $prefixoComPonto, $key);
                $dataCorrigido[$novaChave] = $value;
                $this->log("  - {$key} → {$novaChave}");
            } else {
                $dataCorrigido[$key] = $value;
            }
        }
        
        $data = $dataCorrigido;
        $this->log("Dados POST completos (DEPOIS da conversão):");
        foreach ($data as $key => $value) {
            $valueStr = is_array($value) ? json_encode($value) : $value;
            $this->log("  - {$key} = {$valueStr}");
        }
        
        // Buscar todas as configurações do grupo primeiro
        $configsGrupo = Configuracao::getGrupo($grupo);
        $this->log("Configurações do grupo '{$grupo}': " . count($configsGrupo) . " itens");
        
        // Preparar configurações para salvar
        $configuracoes = [];
        
        // PASSO 1: Processar checkboxes (boolean) PRIMEIRO
        $this->log("PASSO 1: Processando checkboxes (boolean)");
        foreach ($configsGrupo as $chave => $config) {
            if ($config['tipo'] === 'boolean') {
                $enviado = isset($data[$chave]);
                $configuracoes[$chave] = $enviado;
                $status = $enviado ? 'TRUE (marcado)' : 'FALSE (desmarcado)';
                $this->log("  - {$chave}: {$status}");
            }
        }
        
        // PASSO 2: Processar outros campos (string, number, etc)
        $this->log("PASSO 2: Processando outros campos (string, number, etc)");
        foreach ($data as $chave => $valor) {
            // Verificar se a chave existe no grupo de configurações
            if (!isset($configsGrupo[$chave])) {
                $this->log("  - {$chave}: IGNORADO (não existe no grupo)");
                continue;
            }
            
            // Pular se já foi processado como boolean
            if ($configsGrupo[$chave]['tipo'] === 'boolean') {
                continue;
            }
            
            $tipoConfig = $configsGrupo[$chave]['tipo'];
            
            // Para campos numéricos, aceitar zero como valor válido
            if ($tipoConfig === 'number') {
                $valorNumerico = is_numeric($valor) ? $valor : 0;
                $configuracoes[$chave] = $valorNumerico;
                $this->log("  - {$chave}: '{$valorNumerico}' (number)");
                continue;
            }
            
            // Para campos sensíveis (senha/password/key/token REAIS, não campos com essas palavras no nome)
            // Detectar apenas se termina com essas palavras ou são exatamente essas palavras
            $isCampoSensivel = (
                preg_match('/\.(senha|password|key|token|secret|api_key|api_secret)$/i', $chave) ||
                in_array($chave, ['senha', 'password', 'key', 'token', 'secret'])
            );
            
            if ($isCampoSensivel) {
                // Para campos sensíveis, só salvar se não estiver vazio
                if (strlen(trim($valor)) > 0) {
                    $configuracoes[$chave] = trim($valor);
                    $this->log("  - {$chave}: '***' (campo sensível atualizado)");
                } else {
                    $this->log("  - {$chave}: (vazio, mantém valor atual para campo sensível)");
                }
            } else {
                // Campos normais (string, etc)
                $valorTrim = is_string($valor) ? trim($valor) : $valor;
                $configuracoes[$chave] = $valorTrim;
                $this->log("  - {$chave}: '{$valorTrim}'");
            }
        }
        
        // PASSO 3: Processar uploads de arquivos
        $this->log("PASSO 3: Processando uploads de arquivos");
        if (!empty($_FILES)) {
            foreach ($_FILES as $chave => $file) {
                if (isset($configsGrupo[$chave]) && $file['error'] === UPLOAD_ERR_OK) {
                    $this->log("  - {$chave}: processando upload...");
                    $uploadPath = $this->processarUpload($file, $chave);
                    if ($uploadPath) {
                        $configuracoes[$chave] = $uploadPath;
                        $this->log("  - {$chave}: upload OK → {$uploadPath}");
                    } else {
                        $this->log("  - {$chave}: upload FALHOU");
                    }
                }
            }
        } else {
            $this->log("  (nenhum arquivo para upload)");
        }
        
        // Resumo das configurações a salvar
        $this->log("RESUMO: Total de " . count($configuracoes) . " configurações para salvar");
        foreach ($configuracoes as $k => $v) {
            $vStr = is_bool($v) ? ($v ? 'TRUE' : 'FALSE') : (is_string($v) ? "'{$v}'" : $v);
            $this->log("  → {$k} = {$vStr}");
        }
        
        // Verificar se há configurações para salvar
        if (empty($configuracoes)) {
            $this->log("AVISO: Nenhuma configuração para salvar!");
            Configuracao::clearCache();
            $_SESSION['success'] = 'Configurações verificadas. Nenhuma alteração necessária.';
            $this->log("Resultado: Nenhuma alteração");
            $this->log("========================================");
            return $response->redirect('/configuracoes?aba=' . $grupo);
        }
        
        $this->log("SALVANDO no banco de dados...");
        try {
            $success = Configuracao::setMultiplas($configuracoes);
            
            if ($success) {
                $this->log("SUCESSO: Configurações salvas no banco!");
                
                // Limpar cache de configurações
                Configuracao::clearCache();
                $this->log("Cache limpo.");
                
                // Verificar se realmente foram salvas
                $this->log("Verificando valores salvos no banco:");
                foreach ($configuracoes as $chave => $valorEsperado) {
                    $valorAtual = Configuracao::get($chave);
                    $match = $valorAtual === $valorEsperado ? 'OK' : 'ERRO';
                    $valorAtualStr = is_bool($valorAtual) ? ($valorAtual ? 'TRUE' : 'FALSE') : $valorAtual;
                    $valorEsperadoStr = is_bool($valorEsperado) ? ($valorEsperado ? 'TRUE' : 'FALSE') : $valorEsperado;
                    $this->log("  [{$match}] {$chave}: esperado={$valorEsperadoStr}, atual={$valorAtualStr}");
                }
                
                $_SESSION['success'] = 'Configurações salvas com sucesso!';
            } else {
                $this->log("FALHA: Erro ao salvar no banco!");
                $_SESSION['error'] = 'Erro ao salvar configurações. Tente novamente.';
            }
        } catch (\Exception $e) {
            $this->log("EXCEÇÃO: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Erro ao salvar configurações: ' . $e->getMessage();
        }
        
        $this->log("========================================");
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
