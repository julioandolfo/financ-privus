<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Status - WooCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2">
                    <i class="bi bi-toggle-on"></i> Mapeamento de Status
                </h1>
                <p class="text-muted">Configure como os status do WooCommerce serão interpretados no sistema</p>
            </div>
            <a href="/integracoes/<?= $integracaoId ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Botão Atualizar Status -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">
                            <i class="bi bi-cloud-download"></i> Buscar Status do WooCommerce
                        </h5>
                        <p class="text-muted mb-0 small">
                            Clique para buscar todos os status cadastrados no WooCommerce (incluindo plugins como Woo Status Order)
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" onclick="atualizarStatus()">
                            <i class="bi bi-arrow-repeat"></i> Atualizar Status
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Mapeamento -->
        <form id="formMapeamento" onsubmit="salvarMapeamento(event)">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Mapeamento Status WooCommerce → Sistema</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($statusWoo)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Nenhum status encontrado. Clique em <strong>"Atualizar Status"</strong> para buscar do WooCommerce.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40%">Status WooCommerce</th>
                                        <th width="40%">Status no Sistema</th>
                                        <th width="20%">Ativo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statusWoo as $status): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($status['nome']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <code><?= htmlspecialchars($status['chave']) ?></code>
                                                </small>
                                            </td>
                                            <td>
                                                <select 
                                                    class="form-select" 
                                                    name="mapeamento[<?= htmlspecialchars($status['chave']) ?>]"
                                                >
                                                    <option value="">-- Não mapear --</option>
                                                    <?php foreach ($statusSistema as $key => $nome): ?>
                                                        <option 
                                                            value="<?= $key ?>"
                                                            <?= (isset($mapeamento[$status['chave']]) && $mapeamento[$status['chave']] === $key) ? 'selected' : '' ?>
                                                        >
                                                            <?= htmlspecialchars($nome) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="checkbox" 
                                                        <?= $status['ativo'] ? 'checked' : '' ?>
                                                        disabled
                                                    >
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb"></i> 
                            <strong>Dica:</strong> Status customizados de plugins como "Woo Status Order" aparecem automaticamente aqui.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Salvar Mapeamento
                    </button>
                    <a href="/integracoes/<?= $integracaoId ?>/woocommerce/config/pagamentos" class="btn btn-outline-primary">
                        Próximo: Formas de Pagamento <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const integracaoId = <?= $integracaoId ?>;

        function atualizarStatus() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando...';

            fetch(`/integracoes/${integracaoId}/woocommerce/config/status/atualizar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ ${data.message}\nTotal: ${data.total} status encontrados`);
                    location.reload();
                } else {
                    alert(`❌ Erro: ${data.error}`);
                }
            })
            .catch(err => {
                alert('❌ Erro ao buscar status: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Atualizar Status';
            });
        }

        function salvarMapeamento(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const mapeamento = {};
            
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('mapeamento[')) {
                    const statusKey = key.match(/\[(.*?)\]/)[1];
                    if (value) {
                        mapeamento[statusKey] = value;
                    }
                }
            }

            fetch(`/integracoes/${integracaoId}/woocommerce/config/status/salvar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mapeamento })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ Erro: ' + data.error);
                }
            })
            .catch(err => {
                alert('❌ Erro ao salvar: ' + err.message);
            });
        }
    </script>
</body>
</html>
