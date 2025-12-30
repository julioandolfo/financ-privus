<?php
/**
 * Página de erro 404
 */
?>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-12 text-center animate-fade-in">
    <div class="max-w-md mx-auto">
        <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Página não encontrada</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">
            A página que você está procurando não existe ou foi movida.
        </p>
        <div class="flex justify-center space-x-4">
            <a href="/" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">
                Voltar ao Início
            </a>
            <a href="javascript:history.back()" class="px-6 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>
</div>

