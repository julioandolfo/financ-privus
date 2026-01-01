<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $this->baseUrl('/nfes') ?>" 
           class="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para NF-es
        </a>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">NF-e #<?= $nfe['numero_nfe'] ?></h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Série: <?= $nfe['serie_nfe'] ?></p>
    </div>

    <!-- Status e Ações -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Status da NF-e</h2>
                <?php
                $statusColors = [
                    'aguardando' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                    'processando' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                    'autorizada' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                    'cancelada' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                    'rejeitada' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
                ];
                $colorClass = $statusColors[$nfe['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-4 py-2 rounded-full text-lg font-semibold <?= $colorClass ?>">
                    <?= ucfirst($nfe['status']) ?>
                </span>
            </div>

            <div class="flex gap-2">
                <?php if ($nfe['status'] == 'autorizada'): ?>
                    <a href="<?= $this->baseUrl('/nfes/' . $nfe['id'] . '/download-xml') ?>" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        XML
                    </a>
                    <a href="<?= $this->baseUrl('/nfes/' . $nfe['id'] . '/download-danfe') ?>" target="_blank"
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        DANFE
                    </a>
                <?php endif; ?>

                <form method="POST" action="<?= $this->baseUrl('/nfes/' . $nfe['id'] . '/consultar') ?>" class="inline">
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Consultar Status
                    </button>
                </form>

                <?php if ($nfe['status'] == 'autorizada'): ?>
                    <button onclick="abrirModalCancelar()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cancelar NF-e
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($nfe['motivo_status']): ?>
            <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Motivo:</p>
                <p class="text-sm text-amber-800 dark:text-amber-200"><?= htmlspecialchars($nfe['motivo_status']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Informações da NF-e -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Dados da NF-e -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Dados da NF-e</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Chave de Acesso</p>
                    <p class="font-mono text-sm text-gray-900 dark:text-white break-all"><?= htmlspecialchars($nfe['chave_nfe']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Protocolo</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($nfe['protocolo'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Data de Emissão</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= date('d/m/Y H:i:s', strtotime($nfe['data_emissao'])) ?></p>
                </div>
                <?php if ($nfe['data_autorizacao']): ?>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Data de Autorização</p>
                        <p class="font-semibold text-gray-900 dark:text-white"><?= date('d/m/Y H:i:s', strtotime($nfe['data_autorizacao'])) ?></p>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Valor Total</p>
                    <p class="text-2xl font-bold text-emerald-600">R$ <?= number_format($nfe['valor_total'], 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Dados do Cliente</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Nome/Razão Social</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($nfe['cliente_nome']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">CPF/CNPJ</p>
                    <p class="font-mono text-gray-900 dark:text-white"><?= htmlspecialchars($nfe['cliente_documento']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pedido Vinculado -->
    <?php if ($pedido): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pedido Vinculado</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Número do Pedido</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($pedido['numero_pedido']) ?></p>
                </div>
                <a href="<?= $this->baseUrl('/pedidos/' . $pedido['id']) ?>" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Ver Pedido
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Cancelar -->
<div id="modalCancelar" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="fecharModalCancelar()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Cancelar NF-e</h3>
            <form method="POST" action="<?= $this->baseUrl('/nfes/' . $nfe['id'] . '/cancelar') ?>">
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Motivo do Cancelamento *
                    </label>
                    <textarea name="motivo" required rows="4"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Ex: Cancelamento a pedido do cliente"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Mínimo de 15 caracteres</p>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="fecharModalCancelar()"
                            class="flex-1 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors font-semibold">
                        Voltar
                    </button>
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-semibold">
                        Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalCancelar() {
    document.getElementById('modalCancelar').classList.remove('hidden');
}

function fecharModalCancelar() {
    document.getElementById('modalCancelar').classList.add('hidden');
}
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
