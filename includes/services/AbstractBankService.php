<?php
namespace Includes\Services;

/**
 * Classe base com funcionalidades compartilhadas entre todos os bank services.
 * Implementa cURL com suporte a mTLS, retry, logging e mock.
 */
abstract class AbstractBankService implements BankApiInterface
{
    /** @var string|null Access token em cache */
    protected $accessToken = null;
    
    /** @var int Timestamp de expiração do token */
    protected $tokenExpiresAt = 0;

    /**
     * Executa requisição HTTP via cURL.
     * 
     * @param string $url       URL completa
     * @param string $method    GET, POST, PATCH, DELETE
     * @param array  $headers   Headers HTTP
     * @param mixed  $body      Body (string ou array)
     * @param array  $conexao   Dados da conexão (para mTLS)
     * @param bool   $isForm    Se true, envia como form-urlencoded
     * @return array Resposta decodificada
     * @throws \Exception Em caso de erro
     */
    protected function httpRequest(
        string $url,
        string $method = 'GET',
        array $headers = [],
        $body = null,
        array $conexao = [],
        bool $isForm = false
    ): array {
        $ch = curl_init();

        // URL com query params para GET
        if ($method === 'GET' && is_array($body) && !empty($body)) {
            $url .= '?' . http_build_query($body);
            $body = null;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Headers
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Body
        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if ($isForm) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
            }
        }

        // mTLS - Certificado digital (Sicoob, Sicredi, Itaú, Bradesco)
        if (!empty($conexao['cert_pem']) && !empty($conexao['key_pem'])) {
            // Salvar certificados em arquivo temporário se vier como string
            $certPath = $this->resolveCertPath($conexao['cert_pem'], 'cert');
            $keyPath = $this->resolveCertPath($conexao['key_pem'], 'key');

            if ($certPath && $keyPath) {
                curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
                curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
                
                if (!empty($conexao['cert_password'])) {
                    curl_setopt($ch, CURLOPT_KEYPASSWD, $conexao['cert_password']);
                }
            }
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            throw new \Exception("Erro de conexão com {$this->getBancoLabel()}: {$curlError} (errno: {$curlErrno})");
        }

        $decoded = json_decode($result, true);

        if ($httpCode >= 400) {
            $errorMsg = 'Erro desconhecido';
            if (is_array($decoded)) {
                $errorMsg = $decoded['error_description'] 
                    ?? $decoded['message'] 
                    ?? $decoded['error'] 
                    ?? json_encode($decoded);
            }
            throw new \Exception("HTTP {$httpCode} na API {$this->getBancoLabel()}: {$errorMsg}");
        }

        return $decoded ?? [];
    }

    /**
     * Resolve path de certificado - se for conteúdo PEM, salva em temp.
     */
    protected function resolveCertPath(string $certContent, string $prefix): ?string
    {
        if (empty($certContent)) {
            return null;
        }

        // Se já é um path de arquivo existente
        if (file_exists($certContent)) {
            return $certContent;
        }

        // Se é conteúdo PEM, salvar em arquivo temporário
        if (strpos($certContent, '-----BEGIN') !== false) {
            $tempDir = sys_get_temp_dir();
            $hash = md5($certContent);
            $tempFile = $tempDir . "/bank_api_{$prefix}_{$hash}.pem";
            
            if (!file_exists($tempFile)) {
                file_put_contents($tempFile, $certContent);
                chmod($tempFile, 0600);
            }
            
            return $tempFile;
        }

        return null;
    }

    /**
     * Obtém access token, usando cache se válido.
     */
    protected function getAccessToken(array $conexao): string
    {
        // Se temos token em cache e não expirou (com margem de 60s)
        if ($this->accessToken && time() < ($this->tokenExpiresAt - 60)) {
            return $this->accessToken;
        }

        $tokenData = $this->autenticar($conexao);
        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpiresAt = time() + ($tokenData['expires_in'] ?? 3600);

        return $this->accessToken;
    }

    /**
     * Monta headers padrão com Authorization Bearer.
     */
    protected function authHeaders(string $token, string $contentType = 'application/json'): array
    {
        return [
            'Authorization: Bearer ' . $token,
            'Content-Type: ' . $contentType,
            'Accept: application/json'
        ];
    }

    /**
     * Identifica método de pagamento a partir da descrição da transação.
     */
    protected function identificarMetodoPagamento(string $descricao): string
    {
        $texto = strtoupper($descricao);

        if (strpos($texto, 'PIX') !== false) return 'PIX';
        if (strpos($texto, 'TED') !== false) return 'TED';
        if (strpos($texto, 'DOC') !== false) return 'DOC';
        if (strpos($texto, 'BOLETO') !== false || strpos($texto, 'TIT.') !== false) return 'Boleto';
        if (strpos($texto, 'DEB.AUT') !== false || strpos($texto, 'DEBITO AUTOMATICO') !== false) return 'Débito Automático';
        if (strpos($texto, 'TARIFA') !== false) return 'Tarifa Bancária';
        if (strpos($texto, 'CHEQUE') !== false) return 'Cheque';
        if (strpos($texto, 'SAQUE') !== false) return 'Saque';
        if (strpos($texto, 'CARTAO') !== false || strpos($texto, 'CARTÃO') !== false) return 'Cartão';
        if (strpos($texto, 'TRANSF') !== false) return 'Transferência';
        if (strpos($texto, 'COMPRA') !== false) return 'Compra';
        if (strpos($texto, 'DEPOSITO') !== false || strpos($texto, 'DEPÓSITO') !== false) return 'Depósito';
        if (strpos($texto, 'SALARIO') !== false || strpos($texto, 'SALÁRIO') !== false) return 'Salário';

        return 'Outros';
    }

    /**
     * Log de erro para debug
     */
    protected function logError(string $message, array $context = []): void
    {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [{$this->getBancoLabel()}] {$message}";
        if (!empty($context)) {
            $logMessage .= " | " . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        error_log($logMessage);
    }
}
