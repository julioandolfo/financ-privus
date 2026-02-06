<?php
/**
 * Processador de Jobs de Integração
 * 
 * Este script deve ser executado via cron a cada minuto:
 * * * * * * php /caminho/para/processar_jobs_integracao.php >> /dev/null 2>&1
 */

// Bootstrap da aplicação
require_once __DIR__ . '/../bootstrap.php';

use Includes\Services\WooCommerceService;
use App\Models\IntegracaoJob;

// Configurações
const MAX_JOBS_POR_EXECUCAO = 10;
const TIMEOUT_SEGUNDOS = 50; // Deixa 10s de margem para o cron de 1 minuto

// Função de log
function logProcessamento($mensagem) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$mensagem}\n";
}

// Início
logProcessamento("Iniciando processamento de jobs...");

try {
    $service = new WooCommerceService();
    $inicioGeral = microtime(true);
    $jobsProcessados = 0;
    
    // Processa jobs até atingir o máximo ou timeout
    while ($jobsProcessados < MAX_JOBS_POR_EXECUCAO) {
        // Verifica timeout
        $tempoDecorrido = microtime(true) - $inicioGeral;
        if ($tempoDecorrido >= TIMEOUT_SEGUNDOS) {
            logProcessamento("Timeout atingido ({$tempoDecorrido}s). Encerrando...");
            break;
        }
        
        // Processa próximo job
        $resultado = $service->processarProximoJob();
        
        if (!$resultado['sucesso']) {
            // Sem mais jobs ou erro
            if (isset($resultado['mensagem']) && 
                $resultado['mensagem'] === 'Nenhum job pendente') {
                logProcessamento("Sem jobs pendentes. Finalizando.");
            } else {
                logProcessamento("Erro: " . ($resultado['erro'] ?? 'Desconhecido'));
            }
            break;
        }
        
        $jobsProcessados++;
        
        // Log do resultado
        $resumo = $resultado['resultado'] ?? [];
        if (isset($resumo['total'])) {
            logProcessamento("Job #{$jobsProcessados} processado: {$resumo['total']} itens");
        } else {
            logProcessamento("Job #{$jobsProcessados} processado com sucesso");
        }
        
        // Pequena pausa entre jobs
        usleep(100000); // 100ms
    }
    
    $tempoTotal = round(microtime(true) - $inicioGeral, 2);
    logProcessamento("Finalizado: {$jobsProcessados} job(s) processado(s) em {$tempoTotal}s");
    
} catch (\Exception $e) {
    logProcessamento("ERRO CRÍTICO: " . $e->getMessage());
    logProcessamento("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

exit(0);
