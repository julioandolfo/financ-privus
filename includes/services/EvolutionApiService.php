<?php
namespace Includes\Services;

/**
 * Wrapper HTTP para a Evolution API.
 *
 * Endpoints implementados (compatíveis com Evolution API v1.7+ / v2):
 *  - POST /message/sendText/{instance}
 *  - GET  /instance/connectionState/{instance}
 *  - GET  /instance/connect/{instance}
 *  - POST /instance/create
 *
 * Construa o serviço com a config de uma row de `evolution_config`:
 *   $svc = new EvolutionApiService($config['base_url'], $config['instance_name'], $apiKeyDecrypted);
 */
class EvolutionApiService implements WhatsAppApiServiceInterface
{
    private $baseUrl;
    private $instance;
    private $apiKey;
    private $timeout;

    public function __construct($baseUrl, $instance, $apiKey, $timeout = 15)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->instance = $instance;
        $this->apiKey = $apiKey;
        $this->timeout = (int)$timeout;
    }

    public function getProvider(): string
    {
        return 'evolution';
    }

    /**
     * Envia uma mensagem de texto via WhatsApp.
     *
     * @param string $numero Número em E.164 sem '+' (ex: 5511999999999)
     * @param string $mensagem Texto a enviar
     * @return array Padronizado: ok, status, body, raw, error
     */
    public function enviarTexto(string $numero, string $mensagem): array
    {
        $endpoint = "/message/sendText/" . rawurlencode($this->instance);
        $payload = [
            'number' => $numero,
            'text' => $mensagem,
            // Compatibilidade com versões mais antigas da API
            'textMessage' => ['text' => $mensagem],
            'options' => [
                'delay' => 1200,
                'presence' => 'composing',
                'linkPreview' => true
            ]
        ];
        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * Verifica o estado da conexão da instância.
     */
    public function verificarConexao(): array
    {
        $endpoint = "/instance/connectionState/" . rawurlencode($this->instance);
        return $this->request('GET', $endpoint);
    }

    /**
     * Solicita o QR code para conectar a instância.
     */
    public function gerarQrCode(): array
    {
        $endpoint = "/instance/connect/" . rawurlencode($this->instance);
        return $this->request('GET', $endpoint);
    }

    /**
     * Cria uma nova instância na Evolution API.
     *
     * @param array $opcoes Opções extras (qrcode, integration, webhook, etc)
     * @return array
     */
    public function criarInstancia(array $opcoes = [])
    {
        $payload = array_merge([
            'instanceName' => $this->instance,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS'
        ], $opcoes);
        return $this->request('POST', '/instance/create', $payload);
    }

    /**
     * Faz uma requisição HTTP para a Evolution API.
     *
     * @return array { ok, status, body, error }
     */
    private function request($method, $endpoint, $body = null)
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->apiKey,
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
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawBody === false) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => null,
                'raw' => null,
                'error' => 'Erro de conexão: ' . ($curlError ?: 'desconhecido')
            ];
        }

        $decoded = json_decode($rawBody, true);
        $ok = $status >= 200 && $status < 300;

        $error = null;
        if (!$ok) {
            if (is_array($decoded)) {
                if (!empty($decoded['response']['message'])) {
                    $msg = $decoded['response']['message'];
                    $error = is_array($msg) ? implode('; ', $msg) : (string)$msg;
                } elseif (!empty($decoded['message'])) {
                    $error = is_array($decoded['message']) ? implode('; ', $decoded['message']) : (string)$decoded['message'];
                } elseif (!empty($decoded['error'])) {
                    $error = is_array($decoded['error']) ? json_encode($decoded['error']) : (string)$decoded['error'];
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
            'error' => $error
        ];
    }

    /**
     * Helper que extrai o estado a partir da resposta de connectionState.
     * Possíveis valores: 'open', 'connecting', 'close'.
     */
    public static function extrairEstado(array $resposta)
    {
        $body = $resposta['body'] ?? null;
        if (!is_array($body)) return null;
        if (!empty($body['instance']['state'])) return $body['instance']['state'];
        if (!empty($body['state'])) return $body['state'];
        return null;
    }
}
