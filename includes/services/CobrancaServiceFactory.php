<?php
namespace Includes\Services;

/**
 * Factory para instanciar o service correto de cobrança (boletos) de cada banco.
 * 
 * Similar ao BankServiceFactory, mas para serviços de Cobrança Bancária.
 * 
 * Uso:
 *   $service = CobrancaServiceFactory::create('sicoob');
 *   $resultado = $service->incluirBoleto($conexao, $boleto);
 */
class CobrancaServiceFactory
{
    private static $servicos = [
        'sicoob' => [
            'class' => SicoobCobrancaService::class,
            'nome' => 'Sicoob Cobrança',
            'suporta_protesto' => true,
            'suporta_negativacao' => true,
            'suporta_pix' => true,
        ],
        'sicredi' => [
            'class' => SicrediCobrancaService::class,
            'nome' => 'Sicredi Cobrança',
            'suporta_protesto' => false,
            'suporta_negativacao' => false,
            'suporta_pix' => true,
        ],
    ];

    /**
     * Cria instância do serviço de cobrança do banco.
     */
    public static function create(string $banco): CobrancaApiInterface
    {
        $banco = strtolower(trim($banco));

        if (!isset(self::$servicos[$banco])) {
            throw new \Exception("Cobrança bancária não suportada para: {$banco}. Bancos com cobrança: " . implode(', ', array_keys(self::$servicos)));
        }

        $class = self::$servicos[$banco]['class'];
        return new $class();
    }

    /**
     * Verifica se cobrança é suportada para o banco.
     */
    public static function isSuportado(string $banco): bool
    {
        return isset(self::$servicos[strtolower(trim($banco))]);
    }

    /**
     * Retorna lista de bancos com cobrança disponível.
     */
    public static function getBancosDisponiveis(): array
    {
        $result = [];
        foreach (self::$servicos as $key => $config) {
            $result[$key] = [
                'nome' => $config['nome'],
                'suporta_protesto' => $config['suporta_protesto'],
                'suporta_negativacao' => $config['suporta_negativacao'],
                'suporta_pix' => $config['suporta_pix'],
            ];
        }
        return $result;
    }

    /**
     * Retorna funcionalidades suportadas por um banco.
     */
    public static function getFuncionalidades(string $banco): array
    {
        $banco = strtolower(trim($banco));
        return self::$servicos[$banco] ?? [];
    }
}
