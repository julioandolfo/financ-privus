<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                👥 Gerenciar Clientes
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Gerencie os clientes do sistema
            </p>
        </div>
        <a href="<?= $this->baseUrl('/clientes/create') ?>" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Cliente
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="<?= $this->baseUrl('/clientes') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                <input type="text" name="busca" value="<?= htmlspecialchars($filters['busca'] ?? '') ?>" 
                       placeholder="Nome, CPF/CNPJ, e-mail..."
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo</label>
                <select name="tipo_pessoa" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="fisica" <?= ($filters['tipo_pessoa'] ?? '') === 'fisica' ? 'selected' : '' ?>>Pessoa Física</option>
                    <option value="juridica" <?= ($filters['tipo_pessoa'] ?? '') === 'juridica' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Itens por Página</label>
                <select name="por_pagina" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="25" <?= ($filters['por_pagina'] ?? '25') == '25' ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($filters['por_pagina'] ?? '') == '50' ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($filters['por_pagina'] ?? '') == '100' ? 'selected' : '' ?>>100</option>
                    <option value="todos" <?= ($filters['por_pagina'] ?? '') == 'todos' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Filtrar</button>
                <a href="<?= $this->baseUrl('/clientes') ?>" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <?php if (empty($clientes)): ?>
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Nenhum cliente cadastrado</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Comece criando seu primeiro cliente</p>
                <a href="<?= $this->baseUrl('/clientes/create') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Criar Cliente
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Nome/Razão Social</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Tipo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">CPF/CNPJ</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($clientes as $cliente): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    #<?= $cliente['id'] ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($cliente['nome_razao_social']) ?></div>
                                    <?php if ($cliente['email']): ?>
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($cliente['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($cliente['tipo'] === 'fisica'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            👤 Física
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            🏢 Jurídica
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <?php 
                                    if ($cliente['cpf_cnpj']) {
                                        $doc = $cliente['cpf_cnpj'];
                                        if (strlen($doc) === 11) {
                                            echo preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
                                        } else {
                                            echo preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($cliente['ativo']): ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= $this->baseUrl('/clientes/' . $cliente['id']) ?>" 
                                           class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" 
                                           title="Visualizar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="<?= $this->baseUrl('/clientes/' . $cliente['id'] . '/edit') ?>" 
                                           class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" 
                                           title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="<?= $this->baseUrl('/clientes/' . $cliente['id'] . '/delete') ?>" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')" 
                                              class="inline">
                                            <button type="submit" 
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" 
                                                    title="Excluir">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando <span class="font-medium"><?= min($paginacao['offset'] + 1, $paginacao['total_registros']) ?></span>
                            até <span class="font-medium"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
                            de <span class="font-medium"><?= $paginacao['total_registros'] ?></span> registros
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <?php
                            $urlParams = $filters ?? [];
                            unset($urlParams['pagina']);
                            
                            // Remover parâmetros vazios
                            $urlParams = array_filter($urlParams, function($value) {
                                return $value !== '' && $value !== null;
                            });
                            
                            $urlBase = '/clientes' . (!empty($urlParams) ? '?' . http_build_query($urlParams) : '');
                            $separador = empty($urlParams) ? '?' : '&';
                            $range = 2;
                            $inicio = max(1, $paginacao['pagina_atual'] - $range);
                            $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + $range);
                            ?>
                            
                            <?php if ($paginacao['pagina_atual'] > 1): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=1" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Primeira</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] - 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Anterior</a>
                            <?php endif; ?>
                            
                            <?php if ($inicio > 1): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                                <?php if ($i == $paginacao['pagina_atual']): ?>
                                    <span class="px-4 py-2 text-sm bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-medium"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= $urlBase . $separador ?>pagina=<?= $i ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($fim < $paginacao['total_paginas']): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                            
                            <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['pagina_atual'] + 1 ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Próxima</a>
                                <a href="<?= $urlBase . $separador ?>pagina=<?= $paginacao['total_paginas'] ?>" class="px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Última</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

