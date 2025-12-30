<?php
/**
 * Script para criar usuário administrador
 * Execute: php create-admin.php
 */

require_once __DIR__ . '/includes/EnvLoader.php';
EnvLoader::load();

require_once __DIR__ . '/app/core/Database.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== Criar Usuário Administrador ===\n\n";

// Solicita dados
echo "Nome: ";
$nome = trim(fgets(STDIN));

echo "Email: ";
$email = trim(fgets(STDIN));

echo "Senha: ";
$senha = trim(fgets(STDIN));

if (empty($nome) || empty($email) || empty($senha)) {
    echo "\nErro: Todos os campos são obrigatórios!\n";
    exit(1);
}

// Verifica se email já existe
$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    echo "\nErro: Email já está em uso!\n";
    exit(1);
}

// Cria usuário
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, ativo, empresa_id) VALUES (:nome, :email, :senha, 1, NULL)");
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash
    ]);
    
    $userId = $db->lastInsertId();
    
    echo "\n✓ Usuário criado com sucesso!\n";
    echo "ID: {$userId}\n";
    echo "Nome: {$nome}\n";
    echo "Email: {$email}\n";
    echo "\nVocê pode fazer login agora!\n";
    
} catch (Exception $e) {
    echo "\nErro ao criar usuário: " . $e->getMessage() . "\n";
    exit(1);
}

