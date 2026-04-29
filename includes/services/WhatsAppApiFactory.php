<?php
namespace Includes\Services;

/**
 * Factory que cria o serviço de WhatsApp adequado a partir de uma row de
 * `evolution_config` (a tabela mantém o nome legado por compatibilidade,
 * mas hoje suporta múltiplos providers via coluna `provider`).
 *
 * Espera receber a row com:
 *   - provider (string)
 *   - base_url (string)
 *   - instance_name (string|null) — só para provider=evolution
 *   - api_key_decrypted (string)
 */
class WhatsAppApiFactory
{
    /**
     * @param array $config row de evolution_config (com api_key já decifrada em api_key_decrypted)
     * @param int $timeout segundos
     */
    public static function fromConfig(array $config, int $timeout = 15): WhatsAppApiServiceInterface
    {
        $provider = $config['provider'] ?? 'evolution';
        $baseUrl = $config['base_url'] ?? '';
        $token = $config['api_key_decrypted'] ?? '';
        $instance = $config['instance_name'] ?? '';

        switch ($provider) {
            case 'quepasa':
                return new QuePasaApiService($baseUrl, $token, $timeout);

            case 'evolution':
            default:
                return new EvolutionApiService($baseUrl, $instance, $token, $timeout);
        }
    }

    /**
     * Lista os providers suportados (chave => label).
     */
    public static function providers(): array
    {
        return [
            'evolution' => 'Evolution API',
            'quepasa'   => 'QuePasa API',
        ];
    }

    /**
     * Extrai o estado da conexão a partir da resposta de verificarConexao(),
     * roteando para o helper específico do provider.
     */
    public static function extrairEstado(string $provider, array $resposta): ?string
    {
        if ($provider === 'quepasa') {
            return QuePasaApiService::extrairEstado($resposta);
        }
        return EvolutionApiService::extrairEstado($resposta);
    }

    /**
     * Mapeia o estado retornado pela API para o ENUM interno status_conexao.
     * Estados de "pronto/conectado" → 'conectado'; resto → 'desconectado'.
     */
    public static function mapearStatusDb(string $provider, ?string $estado): string
    {
        $e = strtolower((string)$estado);
        $conectados = ['open', 'ready', 'connected', 'verified'];
        if (in_array($e, $conectados, true)) {
            return 'conectado';
        }
        if ($e === '' || $e === null) {
            return 'desconectado';
        }
        return 'desconectado';
    }
}
