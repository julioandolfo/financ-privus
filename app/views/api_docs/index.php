<!DOCTYPE html>
<html lang="pt-BR" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" 
      :class="{ 'dark': darkMode }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API - <?= htmlspecialchars($apiDoc['info']['title']) ?></title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Highlight.js para syntax highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .scroll-smooth { scroll-behavior: smooth; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div x-data="{
        activeSection: 'intro',
        activeEndpoint: 'contas_pagar',
        activeMethod: 0,
        activeLanguage: 'curl',
        
        scrollToSection(section) {
            this.activeSection = section;
            document.getElementById(section)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        
        copyCode(code) {
            navigator.clipboard.writeText(code);
            alert('Código copiado para a área de transferência!');
        },
        
        getMethodColor(method) {
            const colors = {
                'GET': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'POST': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'PUT': 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                'DELETE': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            };
            return colors[method] || 'bg-gray-100 text-gray-800';
        }
    }" class="min-h-screen">
        
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($apiDoc['info']['title']) ?></h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Versão <?= htmlspecialchars($apiDoc['info']['version']) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Theme Toggle -->
                        <button @click="darkMode = !darkMode" 
                                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg x-show="!darkMode" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <svg x-show="darkMode" class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>
                        
                        <?php if (!empty($tokens)): ?>
                            <a href="/api-tokens" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                Meus Tokens
                            </a>
                        <?php endif; ?>
                        
                        <a href="/" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Voltar ao Sistema
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="flex max-w-7xl mx-auto">
            <!-- Sidebar Navigation -->
            <aside class="w-64 flex-shrink-0 sticky top-20 h-screen overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
                <nav class="p-4 space-y-1">
                    <button @click="scrollToSection('intro')" 
                            :class="activeSection === 'intro' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors">
                        📖 Introdução
                    </button>
                    
                    <button @click="scrollToSection('auth')" 
                            :class="activeSection === 'auth' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors">
                        🔐 Autenticação
                    </button>
                    
                    <button @click="scrollToSection('quickstart')" 
                            :class="activeSection === 'quickstart' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors">
                        ⚡ Quick Start
                    </button>
                    
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Endpoints</p>
                    </div>
                    
                    <?php foreach ($apiDoc['endpoints'] as $key => $endpoint): ?>
                        <button @click="scrollToSection('endpoint-<?= $key ?>'); activeEndpoint = '<?= $key ?>'" 
                                :class="activeSection === 'endpoint-<?= $key ?>' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                class="w-full text-left px-3 py-2 rounded-lg transition-colors text-sm">
                            <?= htmlspecialchars($endpoint['name']) ?>
                        </button>
                    <?php endforeach; ?>
                    
                    <button @click="scrollToSection('errors')" 
                            :class="activeSection === 'errors' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors">
                        ⚠️ Códigos de Erro
                    </button>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <main class="flex-1 p-8 overflow-y-auto">
                <!-- Introdução -->
                <section id="intro" class="mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">📖 Introdução</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <?= htmlspecialchars($apiDoc['info']['description']) ?>
                    </p>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-6 mt-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Base URL</h3>
                                <code class="text-sm bg-white dark:bg-gray-800 px-3 py-1 rounded border border-blue-300 dark:border-blue-600">
                                    <?= htmlspecialchars($baseUrl) ?>/api/v1
                                </code>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Autenticação -->
                <section id="auth" class="mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">🔐 Autenticação</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <?= htmlspecialchars($apiDoc['authentication']['description']) ?>
                    </p>
                    
                    <div class="bg-gray-800 rounded-xl p-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-white font-semibold">Header de Autenticação</h3>
                            <button @click="copyCode('Authorization: Bearer SEU_TOKEN_AQUI')" 
                                    class="text-sm text-blue-400 hover:text-blue-300">
                                📋 Copiar
                            </button>
                        </div>
                        <pre class="text-green-400"><code>Authorization: Bearer SEU_TOKEN_AQUI</code></pre>
                    </div>
                    
                    <?php if (!empty($tokens)): ?>
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-6">
                            <h3 class="font-semibold text-green-900 dark:text-green-100 mb-3">✅ Seus Tokens Ativos</h3>
                            <?php foreach ($tokens as $token): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3 last:mb-0">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($token['nome']) ?></p>
                                            <code class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($token['token']) ?></code>
                                        </div>
                                        <button @click="copyCode('<?= htmlspecialchars($token['token']) ?>')" 
                                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                            📋
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-6">
                            <p class="text-amber-900 dark:text-amber-100">
                                ⚠️ Você ainda não possui tokens de API. 
                                <a href="/api-tokens/create" class="underline font-semibold">Crie um token</a> para começar a usar a API.
                            </p>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Quick Start -->
                <section id="quickstart" class="mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">⚡ Quick Start</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        Exemplo rápido de como fazer sua primeira requisição à API:
                    </p>
                    
                    <!-- Language Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                        <nav class="flex space-x-4">
                            <button @click="activeLanguage = 'curl'" 
                                    :class="activeLanguage === 'curl' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                cURL
                            </button>
                            <button @click="activeLanguage = 'php'" 
                                    :class="activeLanguage === 'php' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                PHP
                            </button>
                            <button @click="activeLanguage = 'javascript'" 
                                    :class="activeLanguage === 'javascript' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                JavaScript
                            </button>
                            <button @click="activeLanguage = 'python'" 
                                    :class="activeLanguage === 'python' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="pb-2 px-1 border-b-2 font-medium text-sm transition-colors">
                                Python
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Code Examples -->
                    <div class="bg-gray-800 rounded-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-white font-semibold">Exemplo de Requisição GET</h3>
                            <button @click="copyCode($refs.quickstartCode.textContent)" 
                                    class="text-sm text-blue-400 hover:text-blue-300">
                                📋 Copiar
                            </button>
                        </div>
                        
                        <div x-show="activeLanguage === 'curl'" x-cloak>
                            <pre x-ref="quickstartCode" class="text-green-400 overflow-x-auto"><code>curl -X GET "<?= htmlspecialchars($baseUrl) ?>/api/v1/contas-pagar" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json"</code></pre>
                        </div>
                        
                        <div x-show="activeLanguage === 'php'" x-cloak>
                            <pre x-ref="quickstartCode" class="text-green-400 overflow-x-auto"><code>&lt;?php
$token = 'SEU_TOKEN_AQUI';
$url = '<?= htmlspecialchars($baseUrl) ?>/api/v1/contas-pagar';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

print_r($result);
?&gt;</code></pre>
                        </div>
                        
                        <div x-show="activeLanguage === 'javascript'" x-cloak>
                            <pre x-ref="quickstartCode" class="text-green-400 overflow-x-auto"><code>const token = 'SEU_TOKEN_AQUI';
const url = '<?= htmlspecialchars($baseUrl) ?>/api/v1/contas-pagar';

fetch(url, {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Erro:', error));</code></pre>
                        </div>
                        
                        <div x-show="activeLanguage === 'python'" x-cloak>
                            <pre x-ref="quickstartCode" class="text-green-400 overflow-x-auto"><code>import requests

token = 'SEU_TOKEN_AQUI'
url = '<?= htmlspecialchars($baseUrl) ?>/api/v1/contas-pagar'

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

response = requests.get(url, headers=headers)
data = response.json()
print(data)</code></pre>
                        </div>
                    </div>
                </section>
                
                <!-- Endpoints -->
                <?php foreach ($apiDoc['endpoints'] as $key => $endpoint): ?>
                    <section id="endpoint-<?= $key ?>" class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2"><?= htmlspecialchars($endpoint['name']) ?></h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-6"><?= htmlspecialchars($endpoint['description']) ?></p>
                        
                        <?php foreach ($endpoint['methods'] as $index => $method): ?>
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <span :class="getMethodColor('<?= $method['method'] ?>')" 
                                              class="px-3 py-1 rounded-lg font-semibold text-sm">
                                            <?= $method['method'] ?>
                                        </span>
                                        <code class="text-gray-900 dark:text-gray-100 font-mono text-sm">
                                            <?= htmlspecialchars($method['endpoint']) ?>
                                        </code>
                                    </div>
                                </div>
                                
                                <p class="text-gray-700 dark:text-gray-300 mb-4"><?= htmlspecialchars($method['description']) ?></p>
                                
                                <?php if (isset($method['params']) && !empty($method['params'])): ?>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Parâmetros</h4>
                                    <div class="overflow-x-auto mb-4">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Nome</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Tipo</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Obrigatório</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Descrição</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                <?php foreach ($method['params'] as $param): ?>
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($param['name']) ?></td>
                                                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($param['type']) ?></td>
                                                        <td class="px-4 py-2 text-sm">
                                                            <?php if ($param['required']): ?>
                                                                <span class="text-red-600 dark:text-red-400 font-semibold">✓ Sim</span>
                                                            <?php else: ?>
                                                                <span class="text-gray-500">Não</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($param['description'] ?? '') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($method['body']) && !empty($method['body'])): ?>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Body (JSON)</h4>
                                    <div class="overflow-x-auto mb-4">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Campo</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Tipo</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Obrigatório</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Descrição</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                <?php foreach ($method['body'] as $field => $details): ?>
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($field) ?></td>
                                                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($details['type']) ?></td>
                                                        <td class="px-4 py-2 text-sm">
                                                            <?php if ($details['required']): ?>
                                                                <span class="text-red-600 dark:text-red-400 font-semibold">✓ Sim</span>
                                                            <?php else: ?>
                                                                <span class="text-gray-500">Não</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($details['description'] ?? '') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($method['response'])): ?>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Resposta de Sucesso</h4>
                                    <div class="bg-gray-800 rounded-lg p-4">
                                        <pre class="text-green-400 text-sm overflow-x-auto"><code><?= htmlspecialchars(json_encode($method['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
                
                <!-- Códigos de Erro -->
                <section id="errors" class="mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">⚠️ Códigos de Erro</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        A API utiliza códigos de status HTTP padrão para indicar sucesso ou falha nas requisições:
                    </p>
                    
                    <div class="space-y-4">
                        <?php foreach ($apiDoc['errors'] as $error): ?>
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <div class="flex items-start">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-lg font-semibold text-sm mr-4">
                                        <?= $error['code'] ?>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1"><?= htmlspecialchars($error['message']) ?></h3>
                                        <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($error['description']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="bg-gray-800 rounded-xl p-6 mt-6">
                        <h3 class="text-white font-semibold mb-4">Exemplo de Resposta de Erro</h3>
                        <pre class="text-red-400 text-sm overflow-x-auto"><code>{
  "success": false,
  "error": "Unauthorized",
  "message": "Token inválido ou ausente"
}</code></pre>
                    </div>
                </section>
                
                <!-- Footer -->
                <footer class="border-t border-gray-200 dark:border-gray-700 pt-8 mt-16">
                    <div class="text-center text-gray-600 dark:text-gray-400">
                        <p class="mb-2">📚 Documentação da API - <?= htmlspecialchars($apiDoc['info']['title']) ?></p>
                        <p class="text-sm">Versão <?= htmlspecialchars($apiDoc['info']['version']) ?> • Atualizado em <?= date('d/m/Y') ?></p>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <script>
        // Initialize syntax highlighting
        document.addEventListener('DOMContentLoaded', () => {
            hljs.highlightAll();
        });
    </script>
</body>
</html>
