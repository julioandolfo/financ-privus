<?php
namespace Includes\Services;

/**
 * Factory para instanciar o service correto de cada banco.
 * 
 * Uso:
 *   $service = BankServiceFactory::create('sicoob');
 *   $saldo = $service->getSaldo($conexao);
 */
class BankServiceFactory
{
    /**
     * Bancos suportados com suas configurações básicas
     */
    private static $bancos = [
        'sicoob' => [
            'class' => SicoobBankService::class,
            'nome' => 'Sicoob',
            'cor' => 'green',
            'icone' => 'bank'
        ],
        'sicredi' => [
            'class' => SicrediBankService::class,
            'nome' => 'Sicredi',
            'cor' => 'green',
            'icone' => 'bank'
        ],
        'itau' => [
            'class' => ItauBankService::class,
            'nome' => 'Itaú',
            'cor' => 'orange',
            'icone' => 'bank'
        ],
        'bradesco' => [
            'class' => BradescoBankService::class,
            'nome' => 'Bradesco',
            'cor' => 'red',
            'icone' => 'bank'
        ],
        'mercadopago' => [
            'class' => MercadoPagoBankService::class,
            'nome' => 'Mercado Pago',
            'cor' => 'blue',
            'icone' => 'wallet'
        ]
    ];

    /**
     * Cria instância do service do banco especificado.
     * 
     * @param string $banco Identificador do banco (sicoob, sicredi, itau, bradesco, mercadopago)
     * @return BankApiInterface
     * @throws \Exception Se o banco não for suportado
     */
    public static function create(string $banco): BankApiInterface
    {
        $banco = strtolower(trim($banco));

        if (!isset(self::$bancos[$banco])) {
            throw new \Exception("Banco não suportado: {$banco}. Bancos disponíveis: " . implode(', ', array_keys(self::$bancos)));
        }

        $class = self::$bancos[$banco]['class'];
        return new $class();
    }

    /**
     * Retorna lista de bancos suportados.
     * 
     * @return array ['sicoob' => ['nome' => 'Sicoob', ...], ...]
     */
    public static function getBancosDisponiveis(): array
    {
        $result = [];
        foreach (self::$bancos as $key => $config) {
            $result[$key] = [
                'nome' => $config['nome'],
                'cor' => $config['cor'],
                'icone' => $config['icone']
            ];
        }
        return $result;
    }

    /**
     * Verifica se um banco é suportado.
     */
    public static function isSuportado(string $banco): bool
    {
        return isset(self::$bancos[strtolower(trim($banco))]);
    }

    /**
     * Retorna os campos de configuração de um banco específico.
     */
    public static function getCamposBanco(string $banco): array
    {
        $service = self::create($banco);
        return $service->getCamposConfiguracao();
    }

    /**
     * Retorna todos os campos de todos os bancos (para o formulário JS dinâmico).
     */
    public static function getTodosCampos(): array
    {
        $campos = [];
        foreach (array_keys(self::$bancos) as $banco) {
            try {
                $service = self::create($banco);
                $campos[$banco] = $service->getCamposConfiguracao();
            } catch (\Exception $e) {
                $campos[$banco] = [];
            }
        }
        return $campos;
    }
}
