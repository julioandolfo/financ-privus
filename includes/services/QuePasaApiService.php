<?php
namespace Includes\Services;

/**
 * Wrapper HTTP para a QuePasa API (v4).
 * Doc: https://whats.autoprivus.com.br/swagger/index.html
 *
 * Estratégia de autenticação (defensiva): envia o MESMO valor armazenado em
 * `api_key` em três lugares simultaneamente para cobrir todas as configurações
 * possíveis do servidor QuePasa:
 *   - Header `X-QUEPASA-USER: <valor>`        (modo usuário)
 *   - Header `Authorization: Bearer <valor>`  (modo Bearer token)
 *   - Query string `?token=<valor>` em endpoints que exigem token explícito
 *     (ex: `/scan`, conhecido por retornar "missing token" quando ausente)
 *
 * Endpoints utilizados:
 *   - POST /send          envio de texto `{ chatId, text }`
 *   - GET  /health        estado da conexão (campo `state`)
 *   - GET  /scan          QR code para parear
 */
class QuePasaApiService implements WhatsAppApiServiceInterface
{
    private $baseUrl;
    private $usuario;
    private $timeout;

    /**
     * @param string $baseUrl URL da QuePasa (ex: https://whats.autoprivus.com.br)
     * @param string $usuario Usuário do bot (header X-QUEPASA-USER)
     */
    public function __construct(string $baseUrl, string $usuario, int $timeout = 15)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->usuario = $usuario;
        $this->timeout = $timeout;
    }

    public function getProvider(): string
    {
        return 'quepasa';
    }

    public function enviarTexto(string $numero, string $mensagem): array
    {
        $payload = [
            'chatId' => $this->formatChatId($numero),
            'text' => $mensagem,
        ];
        return $this->request('POST', '/send', $payload);
    }

    public function verificarConexao(): array
    {
        // /health expõe o campo `state` (WhatsappConnectionState) — mais confiável
        // que /info, que retorna apenas metadados da aplicação.
        return $this->request('GET', '/health');
    }

    public function gerarQrCode(): array
    {
        return $this->request('GET', '/scan');
    }

    private function formatChatId(string $numero): string
    {
        $apenas = preg_replace('/\D+/', '', $numero);
        if (strpos($apenas, '@') !== false) {
            return $apenas;
        }
        return $apenas . '@s.whatsapp.net';
    }

    private function request(string $method, string $endpoint, $body = null): array
    {
        // Endpoints conhecidos por exigir o token na query string
        $endpointsComTokenNaQuery = ['/scan', '/paircode'];
        $url = $this->baseUrl . $endpoint;
        if (\in_array($endpoint, $endpointsComTokenNaQuery, true)) {
            $sep = strpos($endpoint, '?') === false ? '?' : '&';
            $url .= $sep . 'token=' . urlencode($this->usuario);
        }

        // Envia em 3 lugares para cobrir as variações do servidor QuePasa:
        //  - Header X-QUEPASA-USER
        //  - Header Authorization: Bearer
        //  - (Já incluído acima) ?token=<valor> em endpoints que exigem
        $headers = [
            'X-QUEPASA-USER: ' . $this->usuario,
            'Authorization: Bearer ' . $this->usuario,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $this->timeout),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $rawBody = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawBody === false) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => null,
                'raw' => null,
                'error' => 'Erro de conexão: ' . ($curlError ?: 'desconhecido'),
            ];
        }

        // /scan pode retornar imagem PNG bruta — embrulha em base64
        if ($endpoint === '/scan' && $contentType && stripos($contentType, 'image/') === 0) {
            return [
                'ok' => $status >= 200 && $status < 300,
                'status' => $status,
                'body' => [
                    'base64' => 'data:' . $contentType . ';base64,' . base64_encode($rawBody),
                    'content_type' => $contentType,
                ],
                'raw' => null,
                'error' => null,
            ];
        }

        $decoded = json_decode($rawBody, true);
        $ok = $status >= 200 && $status < 300;

        // QuePasa retorna { success: bool, status: string, ... }
        if (is_array($decoded) && array_key_exists('success', $decoded)) {
            $ok = $ok && !empty($decoded['success']);
        }

        $error = null;
        if (!$ok) {
            if (is_array($decoded)) {
                if (!empty($decoded['error'])) {
                    $error = is_array($decoded['error']) ? json_encode($decoded['error']) : (string)$decoded['error'];
                } elseif (!empty($decoded['status']) && $decoded['status'] !== 'success') {
                    $error = (string)$decoded['status'];
                } elseif (!empty($decoded['message'])) {
                    $error = (string)$decoded['message'];
                }
            }
            if (!$error) {
                $error = "HTTP {$status}";
            }
        }

        return [
            'ok' => $ok,
            'status' => $status,
            'body' => $decoded,
            'raw' => $rawBody,
            'error' => $error,
        ];
    }

    /**
     * Estados conhecidos do WhatsappConnectionState (QuePasa):
     *   initialized, disconnected, disconnecting, connecting, connected,
     *   ready, failed, statechanged.
     *
     * Prioridade de busca:
     *   1. body.state                  (resposta de /health)
     *   2. body.items[*].state         (servidores agregados)
     *   3. body.bot.state              (legado)
     *   4. body.status (se reconhecido como estado de conexão)
     */
    public static function extrairEstado(array $resposta): ?string
    {
        $body = $resposta['body'] ?? null;
        if (!\is_array($body)) return null;

        // 1) /health: campo state direto
        if (isset($body['state']) && \is_string($body['state']) && $body['state'] !== '') {
            return strtolower($body['state']);
        }

        // 2) /health pode trazer items[].state
        if (!empty($body['items']) && \is_array($body['items'])) {
            foreach ($body['items'] as $it) {
                if (\is_array($it) && !empty($it['state']) && \is_string($it['state'])) {
                    return strtolower($it['state']);
                }
            }
        }

        // 3) Legado: bot.state ou data.state
        $bot = $body['bot'] ?? $body['data'] ?? null;
        if (\is_array($bot) && !empty($bot['state']) && \is_string($bot['state'])) {
            return strtolower($bot['state']);
        }
        if (\is_array($bot) && isset($bot['verified'])) {
            return $bot['verified'] ? 'ready' : 'connecting';
        }

        // 4) Fallback: body.status, mas só se for um estado de conexão conhecido
        if (!empty($body['status']) && \is_string($body['status'])) {
            $s = strtolower($body['status']);
            $estadosConhecidos = [
                'ready','connected','open','verified',
                'connecting','starting','initialized',
                'disconnected','close','closed','disconnecting',
                'failed','offline','statechanged',
            ];
            if (\in_array($s, $estadosConhecidos, true)) {
                return $s;
            }
        }

        // 5) Sinal de saúde da QuePasa: /health retorna `success: true` quando
        //    o servidor considera o bot operante. Tratamos como `connected`.
        if (isset($body['success']) && $body['success'] === true) {
            return 'connected';
        }
        if (isset($body['success']) && $body['success'] === false) {
            return 'disconnected';
        }

        return null;
    }
}
