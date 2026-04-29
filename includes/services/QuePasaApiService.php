<?php
namespace Includes\Services;

/**
 * Wrapper HTTP para a QuePasa API (v4).
 * Doc: https://whats.autoprivus.com.br/swagger/index.html
 *
 * Diferenças relevantes vs Evolution:
 *  - Autenticação por Bearer token: `Authorization: Bearer <token>`.
 *  - Não existe `instance` no path. Cada token corresponde a um bot.
 *  - Envio de texto: `POST /send` com `{ chatId, text }`.
 *  - Status: `GET /info` (token válido retorna detalhes do bot).
 *  - QR Code: `GET /scan`.
 */
class QuePasaApiService implements WhatsAppApiServiceInterface
{
    private $baseUrl;
    private $token;
    private $timeout;

    public function __construct(string $baseUrl, string $token, int $timeout = 15)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
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
        return $this->request('GET', '/info');
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
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->token,
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
     * Helper para extrair o estado da conexão a partir de /info ou /health.
     * Possíveis valores típicos: 'ready', 'connecting', 'disconnected'.
     */
    public static function extrairEstado(array $resposta): ?string
    {
        $body = $resposta['body'] ?? null;
        if (!is_array($body)) return null;

        // Tentativas comuns na resposta do QuePasa
        if (!empty($body['status']) && is_string($body['status'])) {
            return strtolower($body['status']);
        }
        $bot = $body['bot'] ?? $body['data'] ?? null;
        if (is_array($bot) && !empty($bot['state'])) {
            return strtolower($bot['state']);
        }
        if (is_array($bot) && isset($bot['verified'])) {
            return $bot['verified'] ? 'ready' : 'connecting';
        }
        return null;
    }
}
