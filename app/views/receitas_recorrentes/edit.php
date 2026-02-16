<?php
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
$old = $old ?? [];
$d = !empty($old) ? $old : $receita;
$dias = range(1, 31);
$meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
$rateiosIniciais = [];
if (!empty($receita['rateios_json'])) {
    $decoded = json_decode($receita['rateios_json'], true);
    if (is_array($decoded)) {
        $rateiosIniciais = array_map(function($r) {
            return ['empresa_id' => $r['empresa_id'], 'valor_rateio' => $r['valor_rateio'], 'percentual' => $r['percentual'] ?? 0, 'data_competencia' => $r['data_competencia'] ?? ''];
        }, $decoded);
    }
}
?>
<div class="container mx-auto max-w-5xl" x-data="receitaRecorrenteFormEdit()">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Editar Receita Recorrente</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?= htmlspecialchars($receita['descricao']) ?></p>
        </div>
        <a href="/receitas-recorrentes/<?= $receita['id'] ?>" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">← Voltar</a>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 rounded-xl">
        <ul class="list-disc list-inside text-red-700"><?php foreach ((array)$errors as $e): ?><li><?= htmlspecialchars(is_array($e) ? implode(', ', $e) : $e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <form method="POST" action="/receitas-recorrentes/<?= $receita['id'] ?>">
        <input type="hidden" name="_method" value="PUT">
        <?php $old = $d; $empresaId = $receita['empresa_id'] ?? null; include __DIR__ . '/_form_body.php'; ?>
        <div class="flex justify-end space-x-4 mt-6">
            <a href="/receitas-recorrentes/<?= $receita['id'] ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 rounded-xl">Cancelar</a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-medium">Atualizar Receita Recorrente</button>
        </div>
    </form>
</div>
<script>
function receitaRecorrenteFormEdit() {
    const rateiosIniciais = <?= json_encode($rateiosIniciais) ?>;
    const d = <?= json_encode($receita) ?>;
    return {
        frequencia: d.frequencia || 'mensal',
        reajusteAtivo: !!d.reajuste_ativo,
        temRateio: rateiosIniciais.length > 0,
        valorBase: parseFloat(d.valor) || 0,
        rateios: rateiosIniciais.length ? rateiosIniciais : [],
        toggleRateio() { if (this.temRateio && this.rateios.length === 0) this.adicionarRateio(); },
        adicionarRateio() { this.rateios.push({ empresa_id: '', valor_rateio: '', percentual: 0, data_competencia: d.data_inicio || '' }); },
        removerRateio(i) { this.rateios.splice(i, 1); },
        calcularPercentual(i) {
            const v = parseFloat(this.valorBase) || 0;
            if (v > 0 && this.rateios[i]) this.rateios[i].percentual = (parseFloat(this.rateios[i].valor_rateio || 0) / v * 100).toFixed(2);
        },
        atualizarPercentuaisRateios() { this.rateios.forEach((_, i) => this.calcularPercentual(i)); },
        get totalRateado() { return this.rateios.reduce((s, r) => s + parseFloat(r.valor_rateio || 0), 0); }
    }
}
</script>
