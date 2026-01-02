<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Logs de Configurações</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Debug detalhado do salvamento</p>
        </div>
        <div class="flex space-x-3">
            <a href="/configuracoes" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl transition-colors">
                ← Voltar
            </a>
            <form method="POST" action="/configuracoes/logs/limpar" class="inline">
                <button type="submit" 
                        onclick="return confirm('Tem certeza que deseja limpar os logs?')"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors">
                    🗑️ Limpar Logs
                </button>
            </form>
            <button onclick="location.reload()" 
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-colors">
                🔄 Atualizar
            </button>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4">
        <div class="flex">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <strong>Como usar:</strong> Vá para Configurações, faça alterações, salve e depois volte aqui para ver o log detalhado de tudo que aconteceu.
                <br><strong>Última atualização:</strong> <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>

    <!-- Log Container -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="font-mono text-sm bg-gray-900 text-green-400 p-6 rounded-xl overflow-x-auto max-h-[600px] overflow-y-auto">
            <?php if (empty(trim($logs)) || $logs === 'Nenhum log ainda.'): ?>
                <div class="text-gray-500 text-center py-8">
                    Nenhum log registrado ainda.
                    <br>Vá para Configurações e salve algo para gerar logs.
                </div>
            <?php else: ?>
                <pre class="whitespace-pre-wrap"><?= htmlspecialchars($logs) ?></pre>
            <?php endif; ?>
        </div>
        
        <!-- Auto-refresh -->
        <div class="mt-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="autoRefresh" class="mr-2">
                    <span>Auto-atualizar a cada 5 segundos</span>
                </label>
            </div>
            <div id="lastUpdate">
                Atualizado agora
            </div>
        </div>
    </div>

    <!-- Dicas -->
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4">
        <div class="flex">
            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm text-yellow-800 dark:text-yellow-200">
                <strong>Dicas:</strong>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Procure por "TRUE" ou "FALSE" para ver valores de checkboxes</li>
                    <li>Linhas com "[OK]" indicam que o valor foi salvo corretamente</li>
                    <li>Linhas com "[ERRO]" indicam divergência entre esperado e salvo</li>
                    <li>Cada bloco começa com "========" e mostra uma requisição completa</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;
const autoRefreshCheckbox = document.getElementById('autoRefresh');
const lastUpdateDiv = document.getElementById('lastUpdate');

autoRefreshCheckbox.addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(() => {
            location.reload();
        }, 5000);
    } else {
        clearInterval(autoRefreshInterval);
    }
});

// Atualizar timestamp
setInterval(() => {
    const now = new Date();
    lastUpdateDiv.textContent = `Última atualização: ${now.toLocaleTimeString('pt-BR')}`;
}, 1000);
</script>

<?php
$this->session->delete('old');
$this->session->delete('errors');
?>
