<?php
// assets/php/diagnostic.php
require_once('config.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico do Sistema</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>🔧 Diagnóstico do Sistema de Login</h2>";
    
if (!$conexao) {
    die("<p class='error'>❌ Conexão falhou: " . mysqli_connect_error() . "</p>");
}
echo "<p class='success'>✅ Conexão com banco de dados OK</p>";

// Teste rápido de login
echo "<h3>Teste Rápido de Login:</h3>";
$test_email = 'admin@pingu.dev';
$test_password = 'admin123';

$sql = "SELECT * FROM utilizadores WHERE email = '$test_email'";
$result = mysqli_query($conexao, $sql);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    echo "<p>Usuário encontrado: <strong>{$user['nome']}</strong></p>";
    echo "<p>Hash armazenado: <code>{$user['password']}</code></p>";
    echo "<p>Tamanho do hash: " . strlen($user['password']) . "</p>";
    
    // Testar senha
    if (password_verify($test_password, $user['password'])) {
        echo "<p class='success'>✅ Senha 'admin123' funciona com password_verify()!</p>";
    } else if (md5($test_password) === $user['password']) {
        echo "<p class='success'>✅ Senha funciona com MD5 (converter para password_hash!)</p>";
    } else if ($test_password === $user['password']) {
        echo "<p class='error'>❌ Senha em texto plano! MUITO PERIGOSO!</p>";
    } else {
        echo "<p class='error'>❌ Senha NÃO funciona</p>";
    }
} else {
    echo "<p class='error'>❌ Usuário '$test_email' não encontrado!</p>";
}

// Mostrar todos os usuários
echo "<h3>Todos os Usuários:</h3>";
$all_users = mysqli_query($conexao, "SELECT id, nome, email, nivel_acesso, ativo, password FROM utilizadores");

echo "<table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Nível</th>
            <th>Ativo</th>
            <th>Hash (primeiros 20 chars)</th>
        </tr>";

while($row = mysqli_fetch_assoc($all_users)) {
    $hash_preview = substr($row['password'], 0, 20) . '...';
    $status = $row['ativo'] ? '✅' : '❌';
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['nome']}</td>
            <td>{$row['email']}</td>
            <td>{$row['nivel_acesso']}</td>
            <td>{$status}</td>
            <td title='{$row['password']}'>{$hash_preview}</td>
          </tr>";
}
echo "</table>";

// Link para teste
echo "<h3>Testar Login:</h3>";
echo "<p><a href='../html/login.php'>Ir para página de login</a></p>";
echo "<p><a href='../html/login.php?debug=true'>Login com debug ativado</a></p>";

echo "</body></html>";
mysqli_close($conexao);
?>