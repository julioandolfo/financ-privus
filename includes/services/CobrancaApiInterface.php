<?php
namespace Includes\Services;

/**
 * Interface para serviços de Cobrança Bancária (Boletos).
 * Separada da BankApiInterface (conta corrente/extrato).
 * 
 * Cada banco implementa esta interface para operações de boleto:
 * inclusão, consulta, alteração, baixa, protesto, negativação.
 */
interface CobrancaApiInterface
{
    /**
     * Autentica com scopes de cobrança/boleto.
     */
    public function autenticarCobranca(array $conexao): array;

    /**
     * Incluir um novo boleto.
     * @return array Dados do boleto criado (nossoNumero, codigoBarras, linhaDigitavel, qrCode, pdfBoleto)
     */
    public function incluirBoleto(array $conexao, array $boleto): array;

    /**
     * Consultar um boleto específico.
     * @param array $filtros ['nossoNumero' => X] ou ['linhaDigitavel' => Y] ou ['codigoBarras' => Z]
     */
    public function consultarBoleto(array $conexao, array $filtros): array;

    /**
     * Listar boletos de um pagador.
     */
    public function listarBoletosPagador(array $conexao, string $cpfCnpj, array $filtros = []): array;

    /**
     * Alterar dados de um boleto (vencimento, desconto, multa, juros, etc).
     */
    public function alterarBoleto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Comandar a baixa de um boleto.
     */
    public function baixarBoleto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Emitir segunda via (com PDF).
     */
    public function segundaViaBoleto(array $conexao, array $filtros): array;

    /**
     * Protestar boleto vencido.
     */
    public function protestarBoleto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Cancelar apontamento de protesto.
     */
    public function cancelarProtesto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Desistir do protesto.
     */
    public function desistirProtesto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Negativar pagador (SERASA).
     */
    public function negativarBoleto(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Cancelar apontamento de negativação.
     */
    public function cancelarNegativacao(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Baixar negativação.
     */
    public function baixarNegativacao(array $conexao, int $nossoNumero, array $dados): bool;

    /**
     * Solicitar movimentação da carteira.
     */
    public function solicitarMovimentacao(array $conexao, array $dados): array;

    /**
     * Retorna nome do banco.
     */
    public function getBancoNome(): string;

    /**
     * Retorna label do banco.
     */
    public function getBancoLabel(): string;
}
