<?php
namespace Includes\Services;

/**
 * Contrato comum para provedores de API de WhatsApp (Evolution, QuePasa, ...).
 *
 * Todo método retorna um array padronizado:
 *   [
 *     'ok'     => bool,
 *     'status' => int,        // HTTP status
 *     'body'   => mixed,      // resposta decodificada (array|null)
 *     'raw'    => string|null,
 *     'error'  => string|null
 *   ]
 */
interface WhatsAppApiServiceInterface
{
    /**
     * Envia uma mensagem de texto para um número (E.164 sem '+').
     */
    public function enviarTexto(string $numero, string $mensagem): array;

    /**
     * Verifica o estado da conexão da instância/bot.
     */
    public function verificarConexao(): array;

    /**
     * Solicita o QR code para parear/conectar.
     */
    public function gerarQrCode(): array;

    /**
     * Identificador legível do provedor (ex: 'evolution', 'quepasa').
     */
    public function getProvider(): string;
}
