<?php
/**
 * Verifica se há usuários no sistema e cria um admin se necessário
 * Acesse: http://localhost/financeiro/check-users.php
 * ⚠️ REMOVA ESTE ARQUIVO EM PRODUÇÃO!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificação de Usuários</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f4f4f4;padding:10px;overflow:auto;}</style>";

// Carrega .env
require_once __DIR__ . '/../includes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../.env');

// Conecta ao banco
try {
    $config = require __DIR__ . '/../config/database.php';
    
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p class='success'>✅ Conectado ao banco: {$config['database']}</p>";
    
    // Lista usuários
    echo "<h2>👥 Usuários Cadastrados</h2>";
    $stmt = $pdo->query("SELECT id, nome, email, ativo, data_cadastro FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    if (empty($usuarios)) {
        echo "<p class='error'>❌ Nenhum usuário encontrado!</p>";
        echo "<p>🔧 Criando usuário administrador...</p>";
        
        // Cria usuário admin
        $senha = '@Ative199';
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, ativo, data_cadastro) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute(['Administrador', 'admin@privus.com.br', $hash]);
        
        echo "<p class='success'>✅ Usuário criado com sucesso!</p>";
        echo "<pre><strong>Credenciais de acesso:</strong>\nEmail: admin@privus.com.br\nSenha: @Ative199</pre>";
        
        // Verifica o hash
        echo "<p><strong>Hash gerado:</strong></p>";
        echo "<pre>" . $hash . "</pre>";
        
        // Testa verificação
        if (password_verify($senha, $hash)) {
            echo "<p class='success'>✅ Verificação de senha funcionando corretamente!</p>";
        } else {
            echo "<p class='error'>❌ ERRO: Verificação de senha NÃO está funcionando!</p>";
        }
        
    } else {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
        echo "<thead style='background:#3b82f6;color:white;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Status</th><th>Cadastro</th><th>Teste Senha</th></tr>";
        echo "</thead><tbody>";
        
        foreach ($usuarios as $usuario) {
            $status = $usuario['ativo'] ? '<span style="color:green;">✓ Ativo</span>' : '<span style="color:red;">✗ Inativo</span>';
            
            echo "<tr>";
            echo "<td>#{$usuario['id']}</td>";
            echo "<td>{$usuario['nome']}</td>";
            echo "<td>{$usuario['email']}</td>";
            echo "<td>{$status}</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) . "</td>";
            
            // Testa senha padrão
            $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            $hash = $stmt->fetchColumn();
            
            $senhasTeste = ['@Ative199', '123456', 'admin', 'senha'];
            $senhaCorreta = null;
            
            foreach ($senhasTeste as $senha) {
                if (password_verify($senha, $hash)) {
                    $senhaCorreta = $senha;
                    break;
                }
            }
            
            if ($senhaCorreta) {
                echo "<td style='color:green;'>✓ Senha: <strong>{$senhaCorreta}</strong></td>";
            } else {
                echo "<td style='color:orange;'>⚠ Senha desconhecida</td>";
            }
            
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        
        echo "<h3>🔑 Credenciais Disponíveis</h3>";
        echo "<pre>";
        foreach ($usuarios as $usuario) {
            $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            $hash = $stmt->fetchColumn();
            
            $senhasTeste = ['@Ative199', '123456', 'admin', 'senha'];
            foreach ($senhasTeste as $senha) {
                if (password_verify($senha, $hash)) {
                    echo "Email: {$usuario['email']}\n";
                    echo "Senha: {$senha}\n";
                    echo "Status: " . ($usuario['ativo'] ? "Ativo" : "Inativo") . "\n";
                    echo "---\n";
                    break;
                }
            }
        }
        echo "</pre>";
    }
    
    // Botões de ação
    echo "<hr>";
    echo "<h3>🛠️ Ações Rápidas</h3>";
    echo "<a href='?action=reset_admin' style='display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:8px;margin:5px;'>Resetar Senha Admin</a>";
    echo "<a href='/login' style='display:inline-block;padding:10px 20px;background:#10b981;color:white;text-decoration:none;border-radius:8px;margin:5px;'>Ir para Login</a>";
    
    // Reset admin password
    if (isset($_GET['action']) && $_GET['action'] === 'reset_admin') {
        $senha = '@Ative199';
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = 1");
        $stmt->execute([$hash]);
        
        echo "<p class='success' style='margin-top:20px;padding:15px;background:#d1fae5;border:2px solid #10b981;border-radius:8px;'>";
        echo "✅ <strong>Senha do usuário ID=1 resetada com sucesso!</strong><br>";
        echo "Nova senha: <strong>@Ative199</strong>";
        echo "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p style='color:orange;'><strong>⚠️ IMPORTANTE: Remova este arquivo (check-users.php) após verificar!</strong></p>";
?>

