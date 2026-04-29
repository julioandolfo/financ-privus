<?php
$isEdit = !empty($regra);
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$val = function ($field, $default = '') use ($regra, $old) {
    if (array_key_exists($field, $old)) return $old[$field];
    if ($regra && array_key_exists($field, $regra)) return $regra[$field];
    return $default;
};

$filtros = [];
if (!empty($old)) {
    $filtros = $old;
} elseif ($regra && !empty($regra['filtros']) && is_array($regra['filtros'])) {
    $filtros = $regra['filtros'];
}
$filtroVal = function ($field, $default = '') use ($filtros) {
    return $filtros[$field] ?? $default;
};

$diasSemanaAtuais = [];
if ($regra && !empty($regra['dias_semana'])) {
    $diasSemanaAtuais = array_map('intval', explode(',', $regra['dias_semana']));
}

$action = $isEdit ? $this->baseUrl("/whatsapp/regras/{$regra['id']}") : $this->baseUrl('/whatsapp/regras');
?>

<div class="max-w-5xl mx-auto" x-data="regraForm()" x-init="init()">
    <a href="<?= $this->baseUrl('/whatsapp/regras') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Voltar</a>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2 mb-6">
        <?= $isEdit ? 'Editar Regra de Relatório' : 'Nova Regra de Relatório' ?>
    </h1>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $action ?>" class="space-y-6">

        <!-- Bloco 1: identificação -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">📋 Identificação</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nome da regra *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($val('nome')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg"
                           placeholder="Ex: Contas a pagar atrasadas — diretor">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Empresa *</label>
                    <select name="empresa_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <option value="">— selecione —</option>
                        <?php foreach ($empresas as $e): ?>
                            <option value="<?= $e['id'] ?>"
                                <?= ($val('empresa_id', $empresaIdSessao) == $e['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nome_fantasia'] ?? $e['razao_social'] ?? ('Empresa #' . $e['id'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Conexão Evolution *</label>
                    <select name="evolution_config_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <option value="">— selecione —</option>
                        <?php foreach ($conexoes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($val('evolution_config_id') == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?> — <?= htmlspecialchars($c['instance_name']) ?>
                                <?= !$c['ativo'] ? '(inativa)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($conexoes)): ?>
                        <p class="text-xs text-red-600 mt-1">Cadastre uma conexão Evolution antes em <a href="<?= $this->baseUrl('/whatsapp/conexoes/create') ?>" class="underline">Conexões</a>.</p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Descrição (opcional)</label>
                    <textarea name="descricao" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg"><?= htmlspecialchars($val('descricao')) ?></textarea>
                </div>
                <label class="flex items-center gap-3 md:col-span-2">
                    <input type="checkbox" name="ativo" value="1" <?= $val('ativo', 1) ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Regra ativa</span>
                </label>
            </div>
        </div>

        <!-- Bloco 2: Tipo + filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">🎯 O que enviar</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo de relatório *</label>
                    <select name="tipo_relatorio" required x-model="tipo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <?php foreach ($tipos as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($val('tipo_relatorio') == $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Filtros condicionais -->
            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div x-show="precisaDiasAtraso()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Dias de atraso (mín)</label>
                    <input type="number" name="dias_atraso_min" min="0" value="<?= htmlspecialchars($filtroVal('dias_atraso_min', 1)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Ex: 0 = inclui vencidas hoje, 1 = só atrasadas.</p>
                </div>
                <div x-show="precisaDiasAtraso()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Dias de atraso (máx, opcional)</label>
                    <input type="number" name="dias_atraso_max" min="0" value="<?= htmlspecialchars($filtroVal('dias_atraso_max', '')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="precisaDiasVencer()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Dias a vencer (próx. N dias)</label>
                    <input type="number" name="dias_vencer" min="0" value="<?= htmlspecialchars($filtroVal('dias_vencer', 7)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="tipo === 'fluxo_caixa'">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Janela (± dias)</label>
                    <input type="number" name="dias_janela" min="1" value="<?= htmlspecialchars($filtroVal('dias_janela', 30)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="suportaValor()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Valor mínimo (R$)</label>
                    <input type="number" step="0.01" name="valor_min" value="<?= htmlspecialchars($filtroVal('valor_min', '')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="suportaValor()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Valor máximo (R$)</label>
                    <input type="number" step="0.01" name="valor_max" value="<?= htmlspecialchars($filtroVal('valor_max', '')) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
            </div>

            <?php if (!empty($categorias)): ?>
                <div class="mt-4" x-show="suportaValor()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Categorias (opcional)</label>
                    <select name="categorias[]" multiple size="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <?php
                        $catSel = $filtroVal('categorias', []);
                        if (!is_array($catSel)) $catSel = [];
                        foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $catSel) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if (!empty($centros)): ?>
                <div class="mt-4" x-show="suportaValor()">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Centros de custo (opcional)</label>
                    <select name="centros_custo[]" multiple size="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <?php
                        $cenSel = $filtroVal('centros_custo', []);
                        if (!is_array($cenSel)) $cenSel = [];
                        foreach ($centros as $cc): ?>
                            <option value="<?= $cc['id'] ?>" <?= in_array($cc['id'], $cenSel) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cc['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bloco 3: Agendamento -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">⏰ Quando enviar</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Frequência *</label>
                    <select name="frequencia" required x-model="frequencia" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <?php foreach ($frequencias as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($val('frequencia') == $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div x-show="frequencia !== 'intervalo_horas'">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Horário</label>
                    <input type="time" name="horario" value="<?= htmlspecialchars(substr($val('horario', '08:00'), 0, 5)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="frequencia === 'mensal'">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Dia do mês</label>
                    <input type="number" name="dia_mes" min="1" max="31" value="<?= htmlspecialchars($val('dia_mes', 1)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
                <div x-show="frequencia === 'intervalo_horas'">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">A cada (horas)</label>
                    <input type="number" name="intervalo_horas" min="1" max="168" value="<?= htmlspecialchars($val('intervalo_horas', 6)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
            </div>

            <div class="mt-4" x-show="frequencia === 'semanal'">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Dias da semana</label>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $diasSemanaLabel = [1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb', 7 => 'Dom'];
                    foreach ($diasSemanaLabel as $k => $lbl):
                        $checked = in_array($k, $diasSemanaAtuais);
                    ?>
                        <label class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                            <input type="checkbox" name="dias_semana[]" value="<?= $k ?>" <?= $checked ? 'checked' : '' ?> class="mr-2">
                            <?= $lbl ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Bloco 4: Mensagem -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">✏️ Mensagem</h2>
                <button type="button" onclick="gerarTemplateIA(this)"
                    class="px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all">
                    ✨ Gerar com IA
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Variáveis disponíveis:
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{empresa}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{titulo}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{total}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{valor_total}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{lista}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{data}</code>
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs">{hora}</code>
                — deixe em branco para usar o template padrão do tipo, ou clique em <strong>✨ Gerar com IA</strong>.
            </p>
            <textarea name="template_mensagem" id="template_mensagem" rows="6"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono text-sm"
                placeholder="*🔴 {titulo}*&#10;_{empresa}_ · {data}&#10;&#10;*{total} contas* totalizando *{valor_total}*&#10;&#10;{lista}"><?= htmlspecialchars($val('template_mensagem')) ?></textarea>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="incluir_lista_detalhada" value="1" <?= $val('incluir_lista_detalhada', 1) ? 'checked' : '' ?> class="w-5 h-5 rounded border-gray-300 text-emerald-600">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Incluir lista detalhada de contas</span>
                </label>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Limite de itens na lista</label>
                    <input type="number" name="limite_itens_lista" min="1" max="200" value="<?= htmlspecialchars($val('limite_itens_lista', 20)) ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Bloco 5: Destinatários (apenas no novo; em edit há gestão na página show) -->
        <?php if (!$isEdit): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">📱 Destinatários iniciais</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Você poderá adicionar mais destinatários depois. Use o formato <strong>5511999999999</strong>.</p>
            <div id="destinatariosBox" class="space-y-2">
                <?php for ($i = 0; $i < 1; $i++): ?>
                    <div class="grid grid-cols-12 gap-2 items-center destinatario-row">
                        <input type="text" name="destinatarios[<?= $i ?>][nome]" placeholder="Rótulo (ex: Diretor)" class="col-span-4 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <input type="text" name="destinatarios[<?= $i ?>][numero]" placeholder="5511999999999" class="col-span-7 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono">
                        <input type="hidden" name="destinatarios[<?= $i ?>][ativo]" value="1">
                        <button type="button" class="col-span-1 text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endfor; ?>
            </div>
            <button type="button" onclick="addDestinatario()" class="mt-3 text-sm text-emerald-600 hover:text-emerald-700 font-semibold">+ Adicionar destinatário</button>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg">
                <?= $isEdit ? 'Salvar regra' : 'Criar regra' ?>
            </button>
            <a href="<?= $this->baseUrl('/whatsapp/regras') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl">Cancelar</a>
        </div>
    </form>
</div>

<script>
function regraForm() {
    return {
        tipo: '<?= htmlspecialchars($val('tipo_relatorio', 'contas_pagar_atrasadas')) ?>',
        frequencia: '<?= htmlspecialchars($val('frequencia', 'diaria')) ?>',
        init() {},
        precisaDiasAtraso() {
            return ['contas_pagar_atrasadas','contas_receber_atrasadas','inadimplencia'].includes(this.tipo);
        },
        precisaDiasVencer() {
            return ['contas_pagar_vencer','contas_receber_vencer','resumo_diario'].includes(this.tipo);
        },
        suportaValor() {
            return ['contas_pagar_atrasadas','contas_pagar_vencer','contas_pagar_vencendo_hoje','contas_receber_atrasadas','contas_receber_vencer','contas_receber_vencendo_hoje','inadimplencia'].includes(this.tipo);
        }
    };
}
async function gerarTemplateIA(btn) {
    const form = btn.closest('form');
    if (!form) return;
    const fd = new FormData(form);
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Gerando…';

    try {
        const r = await fetch('<?= $this->baseUrl('/whatsapp/regras/gerar-template') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const j = await r.json();
        if (j.ok && j.template) {
            const ta = document.getElementById('template_mensagem');
            const atual = (ta.value || '').trim();
            if (atual && !confirm('Substituir o template atual pelo gerado pela IA?')) {
                return;
            }
            ta.value = j.template;
            ta.style.transition = 'background-color 0.4s';
            ta.style.backgroundColor = 'rgba(167, 139, 250, 0.15)';
            setTimeout(() => { ta.style.backgroundColor = ''; }, 1200);
        } else {
            alert('Erro ao gerar template: ' + (j.error || 'desconhecido'));
        }
    } catch (e) {
        alert('Erro: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
}

let destIdx = 1;
function addDestinatario() {
    const box = document.getElementById('destinatariosBox');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-2 items-center destinatario-row';
    row.innerHTML = `
        <input type="text" name="destinatarios[${destIdx}][nome]" placeholder="Rótulo" class="col-span-4 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
        <input type="text" name="destinatarios[${destIdx}][numero]" placeholder="5511999999999" class="col-span-7 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded-lg font-mono">
        <input type="hidden" name="destinatarios[${destIdx}][ativo]" value="1">
        <button type="button" class="col-span-1 text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">✕</button>`;
    box.appendChild(row);
    destIdx++;
}
</script>
