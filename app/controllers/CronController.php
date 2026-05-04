<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * Endpoints HTTP para disparar tarefas agendadas (crons) via wget/curl.
 * Protegidos por token (env CRON_TOKEN). Não usam AuthMiddleware por design —
 * a validação é feita pelo token na query string.
 */
class CronController
{
    /**
     * GET /cron/integracoes?token=XXX
     * Dispara a sincronização de integrações (WooCommerce etc).
     */
    public function integracoes(Request $request, Response $response)
    {
        if (!$this->validarToken($request, $response)) {
            return;
        }

        $this->executarScript(APP_ROOT . '/cron/integracoes.php', $response);
    }

    /**
     * GET /cron/whatsapp?token=XXX
     * Dispara o envio de relatórios WhatsApp.
     */
    public function whatsapp(Request $request, Response $response)
    {
        if (!$this->validarToken($request, $response)) {
            return;
        }

        $this->executarScript(APP_ROOT . '/cron/enviar_relatorios_whatsapp.php', $response);
    }

    /**
     * Valida o token da query string contra a env CRON_TOKEN.
     */
    private function validarToken(Request $request, Response $response): bool
    {
        $tokenEsperado = $_ENV['CRON_TOKEN'] ?? '';
        $tokenRecebido = (string) $request->get('token', '');

        if (empty($tokenEsperado)) {
            $response->setStatusCode(500);
            echo "CRON_TOKEN não configurado no .env\n";
            return false;
        }

        if (!hash_equals($tokenEsperado, (string) $tokenRecebido)) {
            $response->setStatusCode(403);
            echo "Token inválido\n";
            return false;
        }

        return true;
    }

    /**
     * Executa um script de cron preservando o output e estendendo limites de execução.
     */
    private function executarScript(string $caminho, Response $response): void
    {
        if (!file_exists($caminho)) {
            $response->setStatusCode(404);
            echo "Script não encontrado: {$caminho}\n";
            return;
        }

        @set_time_limit(600);
        @ini_set('max_execution_time', '600');

        // Os scripts CLI usam "echo" e podem chamar exit(). Buffer evita que
        // qualquer output anterior seja descartado e garante que tudo seja
        // enviado ao cliente HTTP.
        header('Content-Type: text/plain; charset=utf-8');
        require $caminho;
    }
}
