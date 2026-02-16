<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

$sqls = [
    // Campos extras na conexoes_bancarias
    "ALTER TABLE conexoes_bancarias ADD COLUMN IF NOT EXISTS saldo_limite DECIMAL(15,2) DEFAULT 0.00 AFTER saldo_banco",
    "ALTER TABLE conexoes_bancarias ADD COLUMN IF NOT EXISTS saldo_contabil DECIMAL(15,2) DEFAULT 0.00 AFTER saldo_limite",
    "ALTER TABLE conexoes_bancarias ADD COLUMN IF NOT EXISTS tx_futuras INT DEFAULT 0 AFTER saldo_contabil",
    "ALTER TABLE conexoes_bancarias ADD COLUMN IF NOT EXISTS soma_futuros_debito DECIMAL(15,2) DEFAULT 0.00 AFTER tx_futuras",
    "ALTER TABLE conexoes_bancarias ADD COLUMN IF NOT EXISTS soma_futuros_credito DECIMAL(15,2) DEFAULT 0.00 AFTER soma_futuros_debito",
    
    // Tabela de histórico de saldos
    "CREATE TABLE IF NOT EXISTS saldo_historico (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        conexao_bancaria_id INT UNSIGNED NOT NULL,
        empresa_id INT UNSIGNED NULL,
        conta_bancaria_id INT UNSIGNED NULL,
        saldo_contabil DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Dinheiro próprio (sem limite)',
        saldo_disponivel DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Contábil + limite',
        saldo_limite DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        saldo_bloqueado DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        tx_futuras INT NOT NULL DEFAULT 0,
        soma_futuros_debito DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        soma_futuros_credito DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        data_referencia VARCHAR(30) NULL COMMENT 'Data de referência da API',
        fonte ENUM('api_saldo','api_sync','api_teste','manual','importacao') DEFAULT 'api_saldo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_conexao_data (conexao_bancaria_id, created_at),
        INDEX idx_empresa_data (empresa_id, created_at),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

echo "=== Executando migrations ===" . PHP_EOL . PHP_EOL;

foreach ($sqls as $i => $sql) {
    $label = substr(trim($sql), 0, 80);
    try {
        $db->exec($sql);
        echo "[OK] " . $label . "..." . PHP_EOL;
    } catch (Exception $e) {
        echo "[SKIP] " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "Migration concluida!" . PHP_EOL;
