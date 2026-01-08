<?php
// assets/php/create_admin.php
require_once('config.php');

if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Senha para o admin
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir admin
$sql = "INSERT INTO utilizadores (nome, email, password, nivel_acesso, ativo, data_registro) 
        VALUES ('Administrador', 'admin@pingu.dev', '$hash', 'admin', TRUE, NOW())";

if (mysqli_query($conexao, $sql)) {
    echo "<h2>✅ Admin criado com sucesso!</h2>";
    echo "<p>Email: <strong>admin@pingu.dev</strong></p>";
    echo "<p>Senha: <strong>admin123</strong></p>";
    echo "<p>Hash gerado: <code>$hash</code></p>";
    echo "<p><a href='../html/login.php'>Ir para login</a></p>";
} else {
    echo "<h2>❌ Erro ao criar admin:</h2>";
    echo "<p>" . mysqli_error($conexao) . "</p>";
}

mysqli_close($conexao);
?>