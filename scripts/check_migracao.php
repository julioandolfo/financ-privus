<?php
/**
 * Verificação pré-migração: legado → financ-privus-v2
 *
 * Uso:
 *   php scripts/check_migracao.php
 *
 * Detecta problemas que causariam falhas ou dados incorretos
 * ao executar o comando `php artisan migrate:legado` no v2.
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/includes/EnvLoader.php';
EnvLoader::load();

$config = require APP_ROOT . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("[ERRO] Não foi possível conectar ao banco: " . $e->getMessage() . "\n");
}

$erros   = 0;
$avisos  = 0;
$ok      = 0;

function ok(string $msg): void    { global $ok;     $ok++;     echo "  [OK]   $msg\n"; }
function aviso(string $msg): void { global $avisos; $avisos++; echo "  [AVS]  $msg\n"; }
function erro(string $msg): void  { global $erros;  $erros++;  echo "  [ERR]  $msg\n"; }

function tabela_existe(PDO $pdo, string $tabela): bool {
    $r = $pdo->query("SHOW TABLES LIKE '$tabela'")->fetch();
    return (bool) $r;
}

function coluna_existe(PDO $pdo, string $tabela, string $coluna): bool {
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $r  = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $r->execute([$db, $tabela, $coluna]);
    return (int) $r->fetchColumn() > 0;
}

function count_table(PDO $pdo, string $tabela): int {
    return (int) $pdo->query("SELECT COUNT(*) FROM `$tabela`")->fetchColumn();
}

echo "\n=================================================\n";
echo " Verificação Pré-Migração — Sistema Legado\n";
echo "=================================================\n\n";

// -----------------------------------------------------------------------
// 1. TABELAS ESSENCIAIS
// -----------------------------------------------------------------------
echo "[1] Tabelas essenciais\n";

$tabelas_essenciais = [
    'empresas', 'usuarios', 'categorias_financeiras', 'centros_custo',
    'formas_pagamento', 'clientes', 'fornecedores', 'contas_bancarias',
    'contas_pagar', 'contas_receber', 'movimentacoes_caixa',
];

$tabelas_opcionais = [
    'parcelas_receber', 'despesas_recorrentes', 'receitas_recorrentes',
    'produtos', 'categorias_produto',
];

foreach ($tabelas_essenciais as $t) {
    if (!tabela_existe($pdo, $t)) {
        erro("Tabela '$t' não encontrada!");
    } else {
        $n = count_table($pdo, $t);
        ok("$t — $n registro(s)");
    }
}

foreach ($tabelas_opcionais as $t) {
    if (!tabela_existe($pdo, $t)) {
        aviso("Tabela opcional '$t' não existe — será pulada na migração.");
    } else {
        $n = count_table($pdo, $t);
        ok("$t — $n registro(s) [opcional]");
    }
}

// -----------------------------------------------------------------------
// 2. COLUNAS ADICIONADAS POR MIGRATIONS TARDIAS
// -----------------------------------------------------------------------
echo "\n[2] Colunas de migrations tardias\n";

$checks_colunas = [
    ['contas_receber', 'desconto',        'migration 053'],
    ['contas_receber', 'numero_parcelas', 'migration 053'],
    ['contas_receber', 'parcela_atual',   'migration 053'],
    ['contas_receber', 'conta_origem_id', 'migration 053'],
    ['contas_receber', 'frete',           'migration 055'],
    ['parcelas_receber', 'frete',         'migration 055'],
];

foreach ($checks_colunas as [$tabela, $coluna, $origem]) {
    if (!tabela_existe($pdo, $tabela)) continue;
    if (!coluna_existe($pdo, $tabela, $coluna)) {
        aviso("$tabela.$coluna ausente ($origem) — migração usará valor padrão 0/null.");
    } else {
        ok("$tabela.$coluna presente.");
    }
}

// -----------------------------------------------------------------------
// 3. STATUS DE CONTAS A RECEBER
// -----------------------------------------------------------------------
echo "\n[3] Status em contas_receber\n";

if (tabela_existe($pdo, 'contas_receber')) {
    $status_validos_v2 = "'pendente','recebido','parcial','cancelado','vencido'";
    $invalidos = $pdo->query(
        "SELECT status, COUNT(*) as qtd FROM contas_receber
          WHERE status NOT IN ($status_validos_v2)
          GROUP BY status"
    )->fetchAll();

    if ($invalidos) {
        foreach ($invalidos as $row) {
            erro("contas_receber: {$row['qtd']} registro(s) com status='{$row['status']}' (inválido no v2). Serão normalizados.");
        }
    } else {
        ok("Todos os status de contas_receber são válidos para o v2.");
    }

    // Verifica 'pago' especificamente (legado usa 'pago', v2 usa 'recebido')
    $n_pago = (int) $pdo->query("SELECT COUNT(*) FROM contas_receber WHERE status='pago'")->fetchColumn();
    if ($n_pago > 0) {
        aviso("contas_receber: $n_pago registro(s) com status='pago' — serão convertidos para 'recebido' na migração.");
    }
}

// -----------------------------------------------------------------------
// 4. STATUS DE CONTAS A PAGAR
// -----------------------------------------------------------------------
echo "\n[4] Status em contas_pagar\n";

if (tabela_existe($pdo, 'contas_pagar')) {
    $status_validos_v2 = "'pendente','pago','parcial','cancelado','vencido'";
    $invalidos = $pdo->query(
        "SELECT status, COUNT(*) as qtd FROM contas_pagar
          WHERE status NOT IN ($status_validos_v2)
          GROUP BY status"
    )->fetchAll();

    if ($invalidos) {
        foreach ($invalidos as $row) {
            erro("contas_pagar: {$row['qtd']} registro(s) com status='{$row['status']}' (inválido no v2). Serão normalizados.");
        }
    } else {
        ok("Todos os status de contas_pagar são válidos para o v2.");
    }
}

// -----------------------------------------------------------------------
// 5. REGISTROS ÓRFÃOS
// -----------------------------------------------------------------------
echo "\n[5] Integridade referencial\n";

$fks = [
    ['contas_receber',  'cliente_id',          'clientes',              'FK clientes em contas_receber'],
    ['contas_receber',  'categoria_id',        'categorias_financeiras', 'FK categorias em contas_receber'],
    ['contas_receber',  'conta_bancaria_id',   'contas_bancarias',      'FK conta_bancaria em contas_receber'],
    ['contas_pagar',    'fornecedor_id',       'fornecedores',          'FK fornecedores em contas_pagar'],
    ['contas_pagar',    'categoria_id',        'categorias_financeiras', 'FK categorias em contas_pagar'],
    ['contas_pagar',    'conta_bancaria_id',   'contas_bancarias',      'FK conta_bancaria em contas_pagar'],
    ['movimentacoes_caixa', 'conta_bancaria_id', 'contas_bancarias',    'FK conta_bancaria em movimentacoes'],
    ['parcelas_receber', 'conta_receber_id',   'contas_receber',        'FK conta_receber em parcelas'],
];

foreach ($fks as [$tabela, $col, $ref_tabela, $desc]) {
    if (!tabela_existe($pdo, $tabela) || !tabela_existe($pdo, $ref_tabela)) continue;
    if (!coluna_existe($pdo, $tabela, $col)) continue;

    $orfaos = (int) $pdo->query(
        "SELECT COUNT(*) FROM `$tabela` t
          LEFT JOIN `$ref_tabela` r ON t.`$col` = r.id
          WHERE t.`$col` IS NOT NULL AND r.id IS NULL"
    )->fetchColumn();

    if ($orfaos > 0) {
        aviso("$desc: $orfaos registro(s) com referência inválida — $col será NULL após migração.");
    } else {
        ok("$desc: OK.");
    }
}

// -----------------------------------------------------------------------
// 6. CAMPOS OBRIGATÓRIOS NULOS
// -----------------------------------------------------------------------
echo "\n[6] Campos obrigatórios\n";

$required = [
    ['empresas',              'razao_social', 'empresas sem razão social'],
    ['categorias_financeiras','nome',         'categorias sem nome'],
    ['clientes',              'nome_razao_social', 'clientes sem nome'],
    ['fornecedores',          'nome_razao_social', 'fornecedores sem nome'],
    ['contas_receber',        'descricao',    'contas_receber sem descrição'],
    ['contas_receber',        'data_vencimento', 'contas_receber sem data de vencimento'],
    ['contas_pagar',          'descricao',    'contas_pagar sem descrição'],
    ['contas_pagar',          'data_vencimento', 'contas_pagar sem data de vencimento'],
];

foreach ($required as [$tabela, $col, $desc]) {
    if (!tabela_existe($pdo, $tabela)) continue;
    $n = (int) $pdo->query("SELECT COUNT(*) FROM `$tabela` WHERE `$col` IS NULL OR `$col`=''")->fetchColumn();
    if ($n > 0) {
        aviso("$desc: $n registro(s). Verifique antes de migrar.");
    } else {
        ok("$desc: OK.");
    }
}

// -----------------------------------------------------------------------
// 7. EMPRESAS SEM CÓDIGO
// -----------------------------------------------------------------------
echo "\n[7] Código de empresa (campo obrigatório no v2)\n";

if (tabela_existe($pdo, 'empresas')) {
    if (!coluna_existe($pdo, 'empresas', 'codigo')) {
        erro("Coluna empresas.codigo não existe! O v2 exige este campo.");
    } else {
        $sem_codigo = (int) $pdo->query("SELECT COUNT(*) FROM empresas WHERE codigo IS NULL OR codigo=''")->fetchColumn();
        if ($sem_codigo > 0) {
            erro("$sem_codigo empresa(s) sem 'codigo'. O v2 rejeitará ao criar o admin. Execute: UPDATE empresas SET codigo=UPPER(SUBSTRING(razao_social,1,10)) WHERE codigo IS NULL;");
        } else {
            ok("Todas as empresas têm código.");
        }
    }
}

// -----------------------------------------------------------------------
// 8. USUÁRIOS COM SENHA NÃO-BCRYPT
// -----------------------------------------------------------------------
echo "\n[8] Senhas de usuários\n";

if (tabela_existe($pdo, 'usuarios')) {
    $col_senha = coluna_existe($pdo, 'usuarios', 'senha') ? 'senha' : (coluna_existe($pdo, 'usuarios', 'password') ? 'password' : null);
    if ($col_senha) {
        $nao_bcrypt = (int) $pdo->query(
            "SELECT COUNT(*) FROM usuarios WHERE `$col_senha` NOT LIKE '\$2y$%' AND `$col_senha` NOT LIKE '\$argon%'"
        )->fetchColumn();
        if ($nao_bcrypt > 0) {
            aviso("$nao_bcrypt usuário(s) com senha não-bcrypt. Receberão senha temporária 'Alterar@{id}' na migração.");
        } else {
            ok("Todas as senhas estão em formato bcrypt/argon.");
        }
    } else {
        aviso("Coluna de senha não identificada em 'usuarios'.");
    }
}

// -----------------------------------------------------------------------
// SUMÁRIO
// -----------------------------------------------------------------------
echo "\n=================================================\n";
echo " Resultado\n";
echo "=================================================\n";
printf("  OK:      %d\n", $ok);
printf("  Avisos:  %d\n", $avisos);
printf("  Erros:   %d\n", $erros);
echo "\n";

if ($erros === 0 && $avisos === 0) {
    echo "  ✓ Banco pronto para migração!\n\n";
    exit(0);
} elseif ($erros === 0) {
    echo "  ⚠  Há avisos. Revise antes de migrar, mas a migração deve funcionar.\n\n";
    exit(0);
} else {
    echo "  ✗ Corrija os ERROS antes de executar a migração.\n\n";
    exit(1);
}
