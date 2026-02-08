<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Formas de Pagamento - WooCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .payment-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }
        .payment-card.collapsed {
            border-left-color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2">
                    <i class="bi bi-credit-card"></i> Configurar Formas de Pagamento
                </h1>
                <p class="text-muted">Defina ações automáticas para cada gateway de pagamento do WooCommerce</p>
            </div>
            <a href="/integracoes/<?= $integracaoId ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Botão Atualizar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">
                            <i class="bi bi-cloud-download"></i> Buscar Formas de Pagamento
                        </h5>
                        <p class="text-muted mb-0 small">
                            Clique para buscar todos os gateways de pagamento ativos no WooCommerce
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" onclick="atualizarFormasPagamento()">
                            <i class="bi bi-arrow-repeat"></i> Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form id="formAcoes" onsubmit="salvarAcoes(event)">
            <?php if (empty($formasPgtoWoo)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Nenhuma forma de pagamento encontrada. Clique em <strong>"Atualizar"</strong> para buscar do WooCommerce.
                </div>
            <?php else: ?>
                <div class="accordion" id="accordionPagamentos">
                    <?php foreach ($formasPgtoWoo as $index => $gateway): ?>
                        <?php 
                            $chave = $gateway['chave'];
                            $config = $acoesConfig[$chave] ?? [];
                        ?>
                        <div class="card payment-card">
                            <div class="card-header" id="heading<?= $index ?>">
                                <h5 class="mb-0">
                                    <button 
                                        class="btn btn-link w-100 text-start" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?= $index ?>"
                                    >
                                        <i class="bi bi-wallet2"></i>
                                        <strong><?= htmlspecialchars($gateway['nome']) ?></strong>
                                        <small class="text-muted">(<?= $chave ?>)</small>
                                        <?php if ($gateway['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </button>
                                </h5>
                            </div>

                            <div 
                                id="collapse<?= $index ?>" 
                                class="collapse <?= $index === 0 ? 'show' : '' ?>" 
                                data-bs-parent="#accordionPagamentos"
                            >
                                <div class="card-body">
                                    <!-- Vincular Forma de Pagamento -->
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-link-45deg"></i> Vincular a Forma de Pagamento do Sistema
                                        </label>
                                        <select 
                                            class="form-select" 
                                            name="acoes[<?= $chave ?>][forma_pagamento_id]"
                                        >
                                            <option value="">-- Não vincular --</option>
                                            <?php foreach ($formasPgtoSistema as $forma): ?>
                                                <option 
                                                    value="<?= $forma['id'] ?>"
                                                    <?= (isset($config['forma_pagamento_id']) && $config['forma_pagamento_id'] == $forma['id']) ? 'selected' : '' ?>
                                                >
                                                    <?= htmlspecialchars($forma['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Parcelas -->
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="criar_parcelas_<?= $chave ?>"
                                                name="acoes[<?= $chave ?>][criar_parcelas]"
                                                value="1"
                                                <?= !empty($config['criar_parcelas']) ? 'checked' : '' ?>
                                                onchange="toggleParcelas('<?= $chave ?>')"
                                            >
                                            <label class="form-check-label" for="criar_parcelas_<?= $chave ?>">
                                                <i class="bi bi-calendar3"></i> Criar Parcelas
                                            </label>
                                        </div>
                                    </div>

                                    <div id="opcoes_parcelas_<?= $chave ?>" style="display: <?= !empty($config['criar_parcelas']) ? 'block' : 'none' ?>">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Número de Parcelas</label>
                                                <select 
                                                    class="form-select" 
                                                    name="acoes[<?= $chave ?>][numero_parcelas]"
                                                >
                                                    <option value="auto" <?= ($config['numero_parcelas'] ?? '') === 'auto' ? 'selected' : '' ?>>
                                                        Automático (do pedido)
                                                    </option>
                                                    <?php for ($i = 2; $i <= 12; $i++): ?>
                                                        <option 
                                                            value="<?= $i ?>"
                                                            <?= (isset($config['numero_parcelas']) && $config['numero_parcelas'] == $i) ? 'selected' : '' ?>
                                                        >
                                                            <?= $i ?>x
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Valor 1ª Parcela</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    name="acoes[<?= $chave ?>][valor_primeira_parcela]"
                                                    placeholder="Ex: 50% ou 500.00"
                                                    value="<?= htmlspecialchars($config['valor_primeira_parcela'] ?? '') ?>"
                                                >
                                            </div>
                                        </div>

                                        <div class="form-check mb-3">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="acoes[<?= $chave ?>][baixar_primeira_parcela]"
                                                value="1"
                                                <?= !empty($config['baixar_primeira_parcela']) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label">
                                                Baixar primeira parcela automaticamente (entrada)
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Baixar Automaticamente -->
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="acoes[<?= $chave ?>][baixar_automaticamente]"
                                                value="1"
                                                <?= !empty($config['baixar_automaticamente']) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label">
                                                <i class="bi bi-check-circle"></i> 
                                                Baixar automaticamente (marcar como recebido)
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            Recomendado para PIX e outros pagamentos instantâneos
                                        </small>
                                    </div>

                                    <!-- Observações -->
                                    <div class="mb-3">
                                        <label class="form-label">Observações</label>
                                        <textarea 
                                            class="form-control" 
                                            name="acoes[<?= $chave ?>][observacoes]"
                                            rows="2"
                                            placeholder="Observações sobre esta forma de pagamento"
                                        ><?= htmlspecialchars($config['observacoes'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-success mt-4">
                    <h6><i class="bi bi-lightbulb"></i> Exemplos de Configuração:</h6>
                    <ul class="mb-0">
                        <li><strong>PIX:</strong> Baixar automaticamente ✓, Criar parcelas ✗</li>
                        <li><strong>Cartão de Crédito:</strong> Criar parcelas ✓ (Automático), Baixar ✗</li>
                        <li><strong>Pagamento 50%:</strong> Criar 2 parcelas, Baixar primeira ✓</li>
                        <li><strong>Boleto:</strong> Criar parcelas ✗, Baixar ✗ (aguardar confirmação)</li>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Salvar Configurações
                </button>
                <a href="/integracoes/<?= $integracaoId ?>" class="btn btn-outline-primary">
                    Concluir Configuração
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const integracaoId = <?= $integracaoId ?>;

        function toggleParcelas(chave) {
            const checkbox = document.getElementById('criar_parcelas_' + chave);
            const opcoes = document.getElementById('opcoes_parcelas_' + chave);
            opcoes.style.display = checkbox.checked ? 'block' : 'none';
        }

        function atualizarFormasPagamento() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando...';

            fetch(`/integracoes/${integracaoId}/woocommerce/config/pagamentos/atualizar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ ${data.message}\nTotal: ${data.total} formas de pagamento encontradas`);
                    location.reload();
                } else {
                    alert(`❌ Erro: ${data.error}`);
                }
            })
            .catch(err => {
                alert('❌ Erro: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Atualizar';
            });
        }

        function salvarAcoes(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const acoes = {};
            
            // Processa os dados do formulário
            for (let [key, value] of formData.entries()) {
                const match = key.match(/acoes\[(.*?)\]\[(.*?)\]/);
                if (match) {
                    const gateway = match[1];
                    const campo = match[2];
                    
                    if (!acoes[gateway]) {
                        acoes[gateway] = {};
                    }
                    
                    // Converte checkbox para boolean
                    if (campo === 'criar_parcelas' || campo === 'baixar_primeira_parcela' || campo === 'baixar_automaticamente') {
                        acoes[gateway][campo] = value === '1';
                    } else {
                        acoes[gateway][campo] = value;
                    }
                }
            }

            fetch(`/integracoes/${integracaoId}/woocommerce/config/pagamentos/salvar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acoes })
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
