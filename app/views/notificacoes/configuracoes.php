<?php
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>

<div class="container mx-auto max-w-4xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configurações de Notificações</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Personalize suas preferências de notificação</p>
        </div>
        <a href="/notificacoes" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            ← Voltar
        </a>
    </div>

    <form action="/notificacoes/configuracoes" method="POST">
        <!-- Tipos de Notificação -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Tipos de Notificação
            </h2>
            
            <div class="space-y-4">
                <!-- Vencimentos -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Contas a Vencer</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Receba alertas sobre contas próximas do vencimento</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <select name="antecedencia_vencimento" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <?php foreach ([1, 3, 5, 7, 15, 30] as $dias): ?>
                                <option value="<?= $dias ?>" <?= ($config['antecedencia_vencimento'] ?? 3) == $dias ? 'selected' : '' ?>>
                                    <?= $dias ?> dia<?= $dias > 1 ? 's' : '' ?> antes
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notificar_vencimentos" value="1" <?= ($config['notificar_vencimentos'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Vencidas -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Contas Vencidas</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Alertas sobre contas em atraso</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notificar_vencidas" value="1" <?= ($config['notificar_vencidas'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Recorrências -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Despesas/Receitas Recorrentes</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Notificações quando uma recorrência for gerada automaticamente</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notificar_recorrencias" value="1" <?= ($config['notificar_recorrencias'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Recebimentos -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Recebimentos Previstos</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Alertas sobre recebimentos esperados</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notificar_recebimentos" value="1" <?= ($config['notificar_recebimentos'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Fluxo de Caixa -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Alertas de Fluxo de Caixa</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Avisos sobre saldo negativo previsto</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notificar_fluxo_caixa" value="1" <?= ($config['notificar_fluxo_caixa'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Preferências -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Preferências
            </h2>
            
            <div class="space-y-4">
                <!-- Som -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Som de Notificação</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tocar som ao receber novas notificações</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="som_ativo" value="1" <?= ($config['som_ativo'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Agrupar -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Agrupar Notificações</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Agrupa várias notificações do mesmo tipo em uma só</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="agrupar_notificacoes" value="1" <?= ($config['agrupar_notificacoes'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Horário de Silêncio -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="font-medium text-gray-900 dark:text-gray-100">Horário de Silêncio</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Não enviar push notifications neste período</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">De</label>
                            <input type="time" name="horario_silencio_inicio" value="<?= $config['horario_silencio_inicio'] ?? '22:00' ?>" 
                                   class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Até</label>
                            <input type="time" name="horario_silencio_fim" value="<?= $config['horario_silencio_fim'] ?? '07:00' ?>" 
                                   class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Web Push -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6" x-data="webPushConfig()">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Notificações Push
            </h2>
            
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-900 dark:text-gray-100">Web Push Notifications</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Receba notificações mesmo quando o navegador estiver fechado</p>
                        <p class="text-xs text-gray-400 mt-1" x-show="!pushSupported">
                            Seu navegador não suporta notificações push
                        </p>
                    </div>
                    <div x-show="pushSupported">
                        <button type="button" 
                                @click="togglePush()"
                                :class="pushEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500'"
                                class="px-4 py-2 text-white rounded-lg transition-colors">
                            <span x-text="pushEnabled ? 'Desativar' : 'Ativar'"></span>
                        </button>
                    </div>
                </div>
                <div x-show="pushEnabled" class="mt-3 text-sm text-green-700 dark:text-green-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Push notifications ativadas neste dispositivo
                </div>
            </div>
        </div>

        <!-- Botão Salvar -->
        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all font-medium shadow-lg">
                Salvar Configurações
            </button>
        </div>
    </form>
</div>

<script>
function webPushConfig() {
    return {
        pushSupported: 'serviceWorker' in navigator && 'PushManager' in window,
        pushEnabled: <?= ($config['web_push_ativo'] ?? 0) ? 'true' : 'false' ?>,
        
        async togglePush() {
            if (this.pushEnabled) {
                // Desativar
                await fetch('/notificacoes/push/remover', { method: 'POST' });
                this.pushEnabled = false;
            } else {
                // Ativar
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        const registration = await navigator.serviceWorker.register('/service-worker.js');
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: '<?= getenv('VAPID_PUBLIC_KEY') ?: '' ?>'
                        });
                        
                        await fetch('/notificacoes/push/salvar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(subscription.toJSON())
                        });
                        
                        this.pushEnabled = true;
                    }
                } catch (error) {
                    console.error('Erro ao ativar push:', error);
                    alert('Não foi possível ativar as notificações push');
                }
            }
        }
    }
}
</script>
