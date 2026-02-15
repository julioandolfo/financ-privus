<?php
namespace Includes\Services;

/**
 * Interface padrão para integração com APIs bancárias.
 * 
 * Cada banco implementa esta interface com sua lógica específica
 * de autenticação e chamadas de API, mas todos retornam dados
 * no mesmo formato normalizado.
 */
interface BankApiInterface
{
    /**
     * Autentica com o banco e retorna tokens/credenciais.
     * 
     * @param array $conexao Dados da conexão (client_id, client_secret, cert_pem, etc)
     * @return array ['access_token' => string, 'expires_in' => int, ...]
     * @throws \Exception Em caso de erro de autenticação
     */
    public function autenticar(array $conexao): array;

    /**
     * Obtém o saldo atual da conta.
     * 
     * @param array $conexao Dados da conexão bancária
     * @return array [
     *     'saldo' => float,           // Saldo disponível
     *     'saldo_bloqueado' => float,  // Saldo bloqueado (se disponível)
     *     'atualizado_em' => string,   // Data/hora ISO da consulta
     *     'moeda' => string            // BRL
     * ]
     * @throws \Exception Em caso de erro
     */
    public function getSaldo(array $conexao): array;

    /**
     * Obtém transações (extrato) no período especificado.
     * Implementa paginação automática internamente.
     * 
     * Formato normalizado de cada transação:
     * [
     *     'banco_transacao_id' => 'TXN123',
     *     'data_transacao'     => '2026-02-15',
     *     'descricao_original' => 'PIX RECEBIDO - CLIENTE XYZ',
     *     'valor'              => 1500.00,
     *     'tipo'               => 'credito',        // credito ou debito
     *     'metodo_pagamento'   => 'PIX',            // PIX, TED, DOC, Boleto, etc
     *     'saldo_apos'         => 12500.00,         // nullable
     *     'origem'             => 'sicoob',         // nome do banco
     *     'dados_extras'       => []                // dados específicos do banco
     * ]
     * 
     * @param array  $conexao    Dados da conexão bancária
     * @param string $dataInicio Data início (Y-m-d)
     * @param string $dataFim    Data fim (Y-m-d)
     * @return array Lista de transações normalizadas
     * @throws \Exception Em caso de erro
     */
    public function getTransacoes(array $conexao, string $dataInicio, string $dataFim): array;

    /**
     * Testa se a conexão/credenciais estão funcionando.
     * 
     * @param array $conexao Dados da conexão bancária
     * @return bool true se conexão ok, false caso contrário
     */
    public function testarConexao(array $conexao): bool;

    /**
     * Retorna o identificador do banco.
     * 
     * @return string Ex: 'sicoob', 'sicredi', 'itau', 'bradesco', 'mercadopago'
     */
    public function getBancoNome(): string;

    /**
     * Retorna nome amigável do banco para exibição.
     * 
     * @return string Ex: 'Sicoob', 'Sicredi', 'Itaú', 'Bradesco', 'Mercado Pago'
     */
    public function getBancoLabel(): string;

    /**
     * Retorna os campos de configuração necessários para este banco.
     * Usado pelo formulário de criação de conexão.
     * 
     * @return array [
     *     ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
     *     ...
     * ]
     */
    public function getCamposConfiguracao(): array;
}
