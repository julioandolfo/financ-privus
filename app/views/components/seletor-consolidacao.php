<?php
/**
 * Componente de Seleção de Empresas para Consolidação
 * Uso: <?php include __DIR__ . '/../components/seletor-consolidacao.php'; ?>
 */

use App\Models\Empresa;
use App\Models\PerfilConsolidacao;

$empresaModel = new Empresa();
$perfilModel = new PerfilConsolidacao();

$todasEmpresas = $empresaModel->findAll(['ativo' => 1]);
$empresasConsolidacao = $_SESSION['empresas_consolidacao'] ?? [];
$perfilAtivo = $_SESSION['perfil_consolidacao_ativo'] ?? null;

$usuarioId = $_SESSION['usuario_id'] ?? null;
$perfisUsuario = $usuarioId ? $perfilModel->findByUsuario($usuarioId) : [];
$perfisCompartilhados = $perfilModel->findCompartilhados();

$modoConsolidacao = count($empresasConsolidacao) >= 2;
?>

<div x-data="{ open: false }" class="relative">
    <!-- Botão para abrir seletor -->
    <button 
        @click="open = !open"
        class="flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors <?= $modoConsolidacao ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600' ?>"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <span class="font-medium">
            <?php if ($modoConsolidacao): ?>
                <?php if ($perfilAtivo): ?>
                    <?= htmlspecialchars($perfilAtivo) ?>
                <?php else: ?>
                    <?= count($empresasConsolidacao) ?> empresas selecionadas
                <?php endif; ?>
            <?php else: ?>
                Selecionar Empresas
            <?php endif; ?>
        </span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown do seletor -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
        style="display: none;"
    >
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Consolidação de Empresas</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selecione 2 ou mais empresas</p>
        </div>

        <form method="POST" action="/perfis-consolidacao/aplicar-custom" class="p-4">
            <!-- Lista de Empresas -->
            <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
                <?php foreach ($todasEmpresas as $empresa): ?>
                    <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            name="empresas_ids[]" 
                            value="<?= $empresa['id'] ?>"
                            <?= in_array($empresa['id'], $empresasConsolidacao) ? 'checked' : '' ?>
                            class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                        >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($empresa['nome_fantasia']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($empresa['razao_social']) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Perfis Salvos -->
            <?php if (!empty($perfisUsuario) || !empty($perfisCompartilhados)): ?>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Perfis Salvos:</p>
                    <div class="space-y-1">
                        <?php foreach (array_merge($perfisUsuario, $perfisCompartilhados) as $perfil): ?>
                            <a 
                                href="/perfis-consolidacao/<?= $perfil['id'] ?>/aplicar?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                            >
                                <?= htmlspecialchars($perfil['nome']) ?>
                                <?php if ($perfil['usuario_id'] === null): ?>
                                    <span class="text-xs text-gray-500">(Compartilhado)</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Botões -->
            <div class="flex items-center justify-between space-x-2">
                <button 
                    type="submit"
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium text-sm"
                >
                    Aplicar
                </button>
                <?php if ($modoConsolidacao): ?>
                    <a 
                        href="/perfis-consolidacao/limpar?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium text-sm"
                    >
                        Limpar
                    </a>
                <?php endif; ?>
                <a 
                    href="/perfis-consolidacao"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium text-sm"
                >
                    Gerenciar
                </a>
            </div>
        </form>
    </div>
</div>
