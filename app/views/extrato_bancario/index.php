<?php
$title = 'Importar Extrato Bancário';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">📄 Importar Extrato Bancário</h1>
        <p class="text-gray-600 dark:text-gray-400">
            Faça upload do seu extrato bancário (CSV, OFX ou TXT) para cadastrar contas a pagar automaticamente.
            Apenas débitos serão importados.
        </p>
    </div>
    
    <!-- Card de Upload -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="space-y-6">
                <!-- Seleção de Empresa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Empresa <span class="text-red-500">*</span>
                    </label>
                    <select name="empresa_id" id="empresa_id" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Upload de Arquivo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Arquivo do Extrato <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="extrato" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Selecione um arquivo</span>
                                    <input id="extrato" name="extrato" type="file" accept=".csv,.ofx,.txt" class="sr-only" required>
                                </label>
                                <p class="pl-1">ou arraste e solte</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">CSV, OFX ou TXT (máx. 10MB)</p>
                            <p id="fileName" class="text-sm text-gray-900 dark:text-gray-100 mt-2 hidden"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Botão de Upload -->
                <div class="flex justify-end">
                    <button type="submit" id="uploadBtn"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span>Processar Extrato</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Informações -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">Como funciona:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Apenas transações de <strong>débito</strong> serão importadas</li>
                    <li>O sistema tentará aplicar padrões salvos anteriormente</li>
                    <li>Você poderá revisar e editar cada transação antes de cadastrar</li>
                    <li>É possível salvar padrões para facilitar importações futuras</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('extrato');
    const fileName = document.getElementById('fileName');
    const uploadBtn = document.getElementById('uploadBtn');
    
    // Mostrar nome do arquivo selecionado
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            fileName.textContent = 'Arquivo selecionado: ' + e.target.files[0].name;
            fileName.classList.remove('hidden');
        } else {
            fileName.classList.add('hidden');
        }
    });
    
    // Upload do arquivo
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const empresaId = document.getElementById('empresa_id').value;
        if (!empresaId) {
            alert('Selecione uma empresa');
            return;
        }
        
        if (!fileInput.files.length) {
            alert('Selecione um arquivo');
            return;
        }
        
        const formData = new FormData();
        formData.append('extrato', fileInput.files[0]);
        formData.append('empresa_id', empresaId);
        
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...';
        
        try {
            const response = await fetch('/extrato-bancario/upload', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = '/extrato-bancario/revisar';
            } else {
                alert('Erro: ' + (data.error || 'Erro ao processar extrato'));
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg><span>Processar Extrato</span>';
            }
        } catch (error) {
            alert('Erro ao fazer upload: ' + error.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg><span>Processar Extrato</span>';
        }
    });
});
</script>
