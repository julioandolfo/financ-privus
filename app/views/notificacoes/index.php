<?php
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

function formatarTempoRelativo($data) {
    $agora = new DateTime();
    $dataNotif = new DateTime($data);
    $diff = $agora->diff($dataNotif);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) return 'agora';
            return "há {$diff->i} min";
        }
        return "há {$diff->h}h";
    } elseif ($diff->days == 1) {
        return 'ontem';
    } elseif ($diff->days < 7) {
        return "há {$diff->days} dias";
    }
    return $dataNotif->format('d/m/Y H:i');
}

$tipos = [
    'vencimento' => ['label' => 'Vencimentos', 'cor' => 'yellow', 'icone' => 'clock'],
    'vencida' => ['label' => 'Vencidas', 'cor' => 'red', 'icone' => 'exclamation-circle'],
    'recorrencia' => ['label' => 'Recorrências', 'cor' => 'indigo', 'icone' => 'refresh'],
    'recebimento' => ['label' => 'Recebimentos', 'cor' => 'green', 'icone' => 'cash'],
    'sistema' => ['label' => 'Sistema', 'cor' => 'gray', 'icone' => 'cog'],
    'fluxo_caixa' => ['label' => 'Fluxo de Caixa', 'cor' => 'blue', 'icone' => 'chart-line'],
];
?>

<div class="container mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Notificações</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gerencie suas notificações</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/notificacoes/configuracoes" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configurações
            </a>
            <form action="/notificacoes/marcar-todas-lidas" method="POST" class="inline">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Marcar todas como lidas
                </button>
            </form>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $key => $tipo): ?>
                        <option value="<?= $key ?>" <?= ($filtros['tipo'] ?? '') == $key ? 'selected' : '' ?>><?= $tipo['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="lida" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    <option value="0" <?= ($filtros['lida'] ?? '') === '0' ? 'selected' : '' ?>>Não lidas</option>
                    <option value="1" <?= ($filtros['lida'] ?? '') === '1' ? 'selected' : '' ?>>Lidas</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Notificações -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <?php if (empty($notificacoes)): ?>
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Nenhuma notificação encontrada</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($notificacoes as $notif): ?>
                    <?php
                    $tipoInfo = $tipos[$notif['tipo']] ?? ['label' => 'Geral', 'cor' => 'gray'];
                    $corClasse = "text-{$tipoInfo['cor']}-500 bg-{$tipoInfo['cor']}-100 dark:bg-{$tipoInfo['cor']}-900/30";
                    ?>
                    <div class="p-4 <?= $notif['lida'] ? '' : 'bg-blue-50 dark:bg-blue-900/10' ?> hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start space-x-4">
                            <div class="p-2 rounded-full <?= $corClasse ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($notif['titulo']) ?>
                                        <?php if (!$notif['lida']): ?>
                                            <span class="ml-2 inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                        <?php endif; ?>
                                    </h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        <?= formatarTempoRelativo($notif['created_at']) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 mt-1"><?= htmlspecialchars($notif['mensagem']) ?></p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs px-2 py-1 rounded-full <?= $corClasse ?>">
                                        <?= $tipoInfo['label'] ?>
                                    </span>
                                    <?php if ($notif['link_url']): ?>
                                        <a href="<?= htmlspecialchars($notif['link_url']) ?>" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            <?= htmlspecialchars($notif['link_texto'] ?? 'Ver detalhes') ?> →
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$notif['lida']): ?>
                                        <form action="/notificacoes/<?= $notif['id'] ?>/lida" method="POST" class="inline">
                                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                                Marcar como lida
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="/notificacoes/<?= $notif['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Excluir esta notificação?')">
                                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginação -->
            <?php if ($total > $porPagina): ?>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Mostrando <?= min(($pagina - 1) * $porPagina + 1, $total) ?> - <?= min($pagina * $porPagina, $total) ?> de <?= $total ?>
                        </p>
                        <div class="flex space-x-2">
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?= $pagina - 1 ?>" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                                    Anterior
                                </a>
                            <?php endif; ?>
                            <?php if ($pagina * $porPagina < $total): ?>
                                <a href="?pagina=<?= $pagina + 1 ?>" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                                    Próxima
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
