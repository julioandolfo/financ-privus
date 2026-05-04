<?php
/**
 * CRON: Envio de relatórios WhatsApp via Evolution API
 *
 * Frequência recomendada: a cada 5 minutos.
 * Comando:
 *   php /caminho/para/projeto/cron/enviar_relatorios_whatsapp.php
 *
 * O script percorre todas as regras ativas com proxima_execucao <= NOW(),
 * gera o relatório, envia para os destinatários ativos e reagenda.
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

// Core
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';

// Models
require_once APP_ROOT . '/app/models/EvolutionConfig.php';
require_once APP_ROOT . '/app/models/WhatsAppRelatorioRegra.php';
require_once APP_ROOT . '/app/models/WhatsAppRelatorioDestinatario.php';
require_once APP_ROOT . '/app/models/WhatsAppRelatorioEnvio.php';

// Services (interface primeiro, depois implementações, depois a factory que usa todas)
require_once APP_ROOT . '/includes/services/WhatsAppApiServiceInterface.php';
require_once APP_ROOT . '/includes/services/EvolutionApiService.php';
require_once APP_ROOT . '/includes/services/QuePasaApiService.php';
require_once APP_ROOT . '/includes/services/WhatsAppApiFactory.php';
require_once APP_ROOT . '/includes/services/RelatorioWhatsAppService.php';

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

$inicio = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Iniciando envio de relatórios WhatsApp...\n";

// Lock simples baseado em arquivo para evitar overlaps
$lockFile = APP_ROOT . '/storage/whatsapp_cron.lock';
$lockDir = dirname($lockFile);
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0755, true);
}

$fp = fopen($lockFile, 'c');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Outro processo já está em execução. Encerrando.\n";
    exit(0);
}

try {
    $svc = new \Includes\Services\RelatorioWhatsAppService();
    $resumo = $svc->executarRegrasDevidas(100);

    $duracao = round(microtime(true) - $inicio, 2);
    echo "Regras processadas: {$resumo['regras']}\n";
    echo "Mensagens enviadas: {$resumo['enviados']}\n";
    echo "Falhas:             {$resumo['falhas']}\n";
    echo "Sem dados:          {$resumo['sem_dados']}\n";
    echo "Tempo total:        {$duracao}s\n";
    echo "[" . date('Y-m-d H:i:s') . "] Concluído.\n";
} catch (\Throwable $e) {
    echo "[ERRO] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}
