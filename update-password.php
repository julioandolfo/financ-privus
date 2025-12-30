<?php
/**
 * Script para atualizar senha de usuário
 * Execute: php update-password.php
 */

require_once __DIR__ . '/includes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

require_once __DIR__ . '/app/core/Database.php';

$db = \App\Core\Database::getInstance();
$conn = $db->getConnection();

// Dados
$userId = 1;
$newPassword = '@Ative199';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Atualiza senha
$stmt = $conn->prepare("UPDATE usuarios SET senha = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$hashedPassword, $userId]);

echo "✅ Senha do usuário ID {$userId} atualizada com sucesso!\n";
echo "Nova senha: {$newPassword}\n";
echo "Hash gerado: {$hashedPassword}\n";

// Verifica se funcionou
$stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
    echo "\n👤 Usuário atualizado:\n";
    echo "ID: {$user['id']}\n";
    echo "Nome: {$user['nome']}\n";
    echo "Email: {$user['email']}\n";
}

