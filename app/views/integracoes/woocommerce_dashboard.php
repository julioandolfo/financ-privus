<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WooCommerce - Integração #<?= $integracaoId ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        .status-pendente { background-color: #ffc107; color: #000; }
        .status-processando { background-color: #0dcaf0; color: #000; }
        .status-concluido { background-color: #198754; color: #fff; }
        .status-erro { background-color: #dc3545; color: #fff; }
        .status-cancelado { background-color: #6c757d; color: #fff; }
        
        .log-item {
            border-left: 3px solid;
            padding-left: 12px;
            margin-bottom: 10px;
        }
        .log-sucesso { border-color: #198754; }
        .log-erro { border-color: #dc3545; }
        .log-aviso { border-color: #ffc107; }
        .log-info { border-color: #0dcaf0; }
        
        .refresh-animation {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-2">
                    <i class="bi bi-speedometer2"></i> Dashboard WooCommerce
                </h1>
                <p class="text-muted">
                    <i class="bi bi-shop"></i> <?= htmlspecialchars($config['url_site'] ?? '') ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-outline-primary btn-sm" onclick="atualizarDashboard()">
                    <i class="bi bi-arrow-clockwise" id="iconRefresh"></i> Atualizar
                </button>
                <a href="/integracoes/<?= $integracaoId ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Cards de Métricas -->
        <div class="row g-3 mb-4">
            <!-- Jobs Pendentes -->
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-value" id="metricJobsPendentes">
                                    <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_PENDENTE] ?? 0 ?>
                                </div>
                                <div class="metric-label">Jobs Pendentes</div>
                            </div>
                            <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs Concluídos Hoje -->
            <div class="col-md-3">
                <div class="card metric-card h-100" style="border-left-color: #198754;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-value text-success" id="metricJobsConcluidos">
                                    <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_CONCLUIDO] ?? 0 ?>
                                </div>
                                <div class="metric-label">Jobs Concluídos</div>
                            </div>
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos Sincronizados Hoje -->
            <div class="col-md-3">
                <div class="card metric-card h-100" style="border-left-color: #0dcaf0;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-value text-info" id="metricProdutosHoje">
                                    <?= $metricas['produtos_hoje'] ?? 0 ?>
                                </div>
                                <div class="metric-label">Produtos Hoje</div>
                            </div>
                            <i class="bi bi-box-seam fs-1 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Erros (últimos 7 dias) -->
            <div class="col-md-3">
                <div class="card metric-card h-100" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-value text-danger" id="metricErros">
                                    <?= ($metricas['jobs'][App\Models\IntegracaoJob::STATUS_ERRO] ?? 0) ?>
                                </div>
                                <div class="metric-label">Erros (7 dias)</div>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações de Sincronização -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history"></i> Última Sincronização
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Produtos:</strong><br>
                                <span class="text-muted">
                                    <?php if ($metricas['ultima_sync_produtos']): ?>
                                        <?= date('d/m/Y H:i', strtotime($metricas['ultima_sync_produtos'])) ?>
                                        <br>
                                        <small>(<?= $this->tempoDecorrido($metricas['ultima_sync_produtos']) ?>)</small>
                                    <?php else: ?>
                                        Nunca sincronizado
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <strong>Pedidos:</strong><br>
                                <span class="text-muted">
                                    <?php if ($metricas['ultima_sync_pedidos']): ?>
                                        <?= date('d/m/Y H:i', strtotime($metricas['ultima_sync_pedidos'])) ?>
                                        <br>
                                        <small>(<?= $this->tempoDecorrido($metricas['ultima_sync_pedidos']) ?>)</small>
                                    <?php else: ?>
                                        Nunca sincronizado
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" onclick="criarJobSync('produtos')">
                                <i class="bi bi-arrow-repeat"></i> Sincronizar Produtos
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="criarJobSync('pedidos')">
                                <i class="bi bi-arrow-repeat"></i> Sincronizar Pedidos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-database"></i> Cache de Sincronização
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h3 text-primary">
                                    <?= $estatisticasCache['produto'] ?? 0 ?>
                                </div>
                                <div class="text-muted small">Produtos no Cache</div>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-success">
                                    <?= $estatisticasCache['pedido'] ?? 0 ?>
                                </div>
                                <div class="text-muted small">Pedidos no Cache</div>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle"></i> 
                            O cache permite sincronização incremental, importando apenas itens modificados.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tabJobs">
                            <i class="bi bi-list-task"></i> Jobs Recentes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabLogs">
                            <i class="bi bi-file-earmark-text"></i> Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabGraficos">
                            <i class="bi bi-graph-up"></i> Gráficos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Tab Jobs -->
                    <div class="tab-pane fade show active" id="tabJobs">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Tentativas</th>
                                        <th>Criado em</th>
                                        <th>Tempo Execução</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaJobs">
                                    <?php foreach ($jobsRecentes as $job): ?>
                                        <tr>
                                            <td><?= $job['id'] ?></td>
                                            <td>
                                                <i class="bi bi-<?= $this->iconeTipoJob($job['tipo']) ?>"></i>
                                                <?= ucwords(str_replace('_', ' ', $job['tipo'])) ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $job['status'] ?>">
                                                    <?= ucfirst($job['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $job['tentativas'] ?> / <?= $job['max_tentativas'] ?>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i:s', strtotime($job['criado_em'])) ?>
                                            </td>
                                            <td>
                                                <?= $job['tempo_execucao'] ? number_format($job['tempo_execucao'], 2) . 's' : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($job['erro']): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="mostrarErroJob(<?= $job['id'] ?>, '<?= addslashes($job['erro']) ?>')">
                                                        <i class="bi bi-exclamation-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Logs -->
                    <div class="tab-pane fade" id="tabLogs">
                        <div id="containerLogs">
                            <?php foreach ($logsRecentes as $log): ?>
                                <div class="log-item log-<?= strtolower($log['tipo']) ?>">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($log['mensagem']) ?></strong>
                                        <span class="text-muted small">
                                            <?= date('d/m/Y H:i:s', strtotime($log['data'])) ?>
                                        </span>
                                    </div>
                                    <?php if ($log['detalhes']): ?>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($log['detalhes'], 0, 200)) ?>
                                            <?= strlen($log['detalhes']) > 200 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab Gráficos -->
                    <div class="tab-pane fade" id="tabGraficos">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <canvas id="graficoTaxaSucesso"></canvas>
                            </div>
                            <div class="col-md-6 mb-4">
                                <canvas id="graficoSincronizacoes"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Erro Job -->
    <div class="modal fade" id="modalErroJob" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Erro no Job
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="textoErroJob"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const integracaoId = <?= $integracaoId ?>;

        // Atualizar dashboard
        function atualizarDashboard() {
            const icon = document.getElementById('iconRefresh');
            icon.classList.add('refresh-animation');
            
            fetch(`/api/integracoes/${integracaoId}/dashboard/metricas`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar métricas
                        const metricas = data.data;
                        document.getElementById('metricJobsPendentes').textContent = metricas.jobs.pendente || 0;
                        document.getElementById('metricJobsConcluidos').textContent = metricas.jobs.concluido || 0;
                        document.getElementById('metricProdutosHoje').textContent = metricas.produtos_hoje || 0;
                        document.getElementById('metricErros').textContent = metricas.jobs.erro || 0;
                    }
                })
                .finally(() => {
                    icon.classList.remove('refresh-animation');
                });
        }

        // Criar job de sincronização
        function criarJobSync(tipo) {
            const tipoJob = tipo === 'produtos' ? 'sync_produtos' : 'sync_pedidos';
            
            fetch(`/api/integracoes/${integracaoId}/dashboard/jobs/criar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo: tipoJob })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Job de sincronização de ${tipo} criado com sucesso!`);
                    location.reload();
                } else {
                    alert(`Erro: ${data.error}`);
                }
            });
        }

        // Mostrar erro do job
        function mostrarErroJob(jobId, erro) {
            document.getElementById('textoErroJob').textContent = erro;
            new bootstrap.Modal(document.getElementById('modalErroJob')).show();
        }

        // Inicializar gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico Taxa de Sucesso
            const ctxTaxa = document.getElementById('graficoTaxaSucesso');
            if (ctxTaxa) {
                new Chart(ctxTaxa, {
                    type: 'doughnut',
                    data: {
                        labels: ['Concluído', 'Erro', 'Pendente', 'Processando'],
                        datasets: [{
                            data: [
                                <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_CONCLUIDO] ?? 0 ?>,
                                <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_ERRO] ?? 0 ?>,
                                <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_PENDENTE] ?? 0 ?>,
                                <?= $metricas['jobs'][App\Models\IntegracaoJob::STATUS_PROCESSANDO] ?? 0 ?>
                            ],
                            backgroundColor: ['#198754', '#dc3545', '#ffc107', '#0dcaf0']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Taxa de Sucesso (Últimos 7 dias)'
                            }
                        }
                    }
                });
            }

            // Auto-refresh a cada 30 segundos
            setInterval(atualizarDashboard, 30000);
        });
    </script>
</body>
</html>

<?php
// Helper functions
function tempoDecorrido($dataHora) {
    $tempo = time() - strtotime($dataHora);
    if ($tempo < 60) return "há " . $tempo . " segundos";
    if ($tempo < 3600) return "há " . floor($tempo / 60) . " minutos";
    if ($tempo < 86400) return "há " . floor($tempo / 3600) . " horas";
    return "há " . floor($tempo / 86400) . " dias";
}

function iconeTipoJob($tipo) {
    $icones = [
        'sync_produtos' => 'box-seam',
        'sync_pedidos' => 'cart',
        'webhook' => 'arrow-down-circle',
        'importar_imagens' => 'image'
    ];
    return $icones[$tipo] ?? 'gear';
}
?>
