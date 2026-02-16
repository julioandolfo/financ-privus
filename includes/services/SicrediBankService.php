<?php
namespace Includes\Services;

/**
 * Sicredi - Configuração de conexão e campos.
 * 
 * IMPORTANTE: Sicredi NÃO oferece API de conta corrente (saldo/extrato).
 * A integração real é para Cobrança (boletos) via SicrediCobrancaService.
 * 
 * Este service apenas define os campos de configuração e retorna dados mock
 * para saldo/extrato (pois o sistema exige implementar BankApiInterface).
 * 
 * Docs cobrança: Manual API da Cobrança 1.2 Sicredi
 * Portal: https://developer.sicredi.com.br
 */
class SicrediBankService extends AbstractBankService
{
    // Sicredi NÃO oferece API de conta corrente (saldo/extrato).
    // Saldo e extrato retornam dados mock.
    // A integração REAL é para Cobrança (boletos) via SicrediCobrancaService.

    public function getBancoNome(): string
    {
        return 'sicredi';
    }

    public function getBancoLabel(): string
    {
        return 'Sicredi';
    }

    public function autenticar(array $conexao): array
    {
        // Sicredi não tem API de conta corrente — mock token para testar conexão
        return [
            'access_token' => 'mock-token-sicredi-' . time(),
            'expires_in' => 3600,
            'token_type' => 'Bearer'
        ];
    }

    public function getSaldo(array $conexao): array
    {
        // Sicredi não tem API de conta corrente — retorna dados mock
        return [
            'saldo' => 0,
            'saldo_bloqueado' => 0,
            'saldo_limite' => 0,
            'atualizado_em' => date('Y-m-d\TH:i:s'),
            'data_referencia' => date('Y-m-d'),
            'tx_futuras' => 0,
            'soma_futuros_debito' => 0,
            'soma_futuros_credito' => 0,
            'moeda' => 'BRL',
            'nota' => 'Sicredi não oferece API de conta corrente. Saldo indisponível.'
        ];
    }

    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array
    {
        // Sicredi não tem API de conta corrente — retorna vazio
        return [];
    }

    public function testarConexao(array $conexao): bool
    {
        try {
            $tokenData = $this->autenticar($conexao);
            return !empty($tokenData['access_token']);
        } catch (\Exception $e) {
            $this->logError('Teste de conexão falhou', ['erro' => $e->getMessage()]);
            return false;
        }
    }

    public function getCamposConfiguracao(): array
    {
        return [
            // === Autenticação API Cobrança ===
            ['name' => 'x_api_key', 'label' => 'x-api-key (Token do Portal)', 'type' => 'password', 'required' => true,
             'placeholder' => 'Access token gerado no portal developer.sicredi.com.br',
             'help' => 'Disponível em "Minhas Apps" no portal do desenvolvedor Sicredi.'],
            ['name' => 'username', 'label' => 'Username (Beneficiário + Cooperativa)', 'type' => 'text', 'required' => true,
             'placeholder' => 'Ex: 123456789 (código beneficiário + cooperativa, 9 dígitos)',
             'help' => 'Formato: código do beneficiário (5 dígitos) + código da cooperativa (4 dígitos).'],
            ['name' => 'password', 'label' => 'Código de Acesso', 'type' => 'password', 'required' => true,
             'placeholder' => 'Código de acesso gerado no Internet Banking',
             'help' => 'Gerado no Internet Banking: Cobrança > Código de Acesso > Gerar.'],

            // === Dados da Cooperativa/Convênio ===
            ['name' => 'cooperativa', 'label' => 'Código da Cooperativa', 'type' => 'text', 'required' => true,
             'placeholder' => 'Ex: 0100 (4 dígitos)'],
            ['name' => 'posto', 'label' => 'Código do Posto/Agência', 'type' => 'text', 'required' => true,
             'placeholder' => 'Ex: 02 (2 dígitos)'],
            ['name' => 'codigo_beneficiario', 'label' => 'Código do Beneficiário', 'type' => 'text', 'required' => true,
             'placeholder' => 'Ex: 12345 (5 dígitos, código do convênio de cobrança)'],
            ['name' => 'banco_conta_id', 'label' => 'Conta Corrente', 'type' => 'text', 'required' => false,
             'placeholder' => 'Ex: 12345-6'],

            // === Certificado (para futuras integrações) ===
            ['name' => 'cert_pfx', 'label' => 'Certificado Digital (.pfx)', 'type' => 'file', 'required' => false,
             'accept' => '.pfx,.p12',
             'help' => 'Opcional para cobrança. Necessário para futuras integrações (conta corrente, PIX).'],
            ['name' => 'cert_password', 'label' => 'Senha do Certificado', 'type' => 'password', 'required' => false],

            // === Ambiente ===
            ['name' => 'ambiente', 'label' => 'Ambiente', 'type' => 'select', 'required' => true,
             'options' => ['sandbox' => 'Sandbox (testes)', 'producao' => 'Produção'],
             'default' => 'sandbox'],
        ];
    }

}
