<?php
// assets/php/check_admin.php
require_once('config.php');

// Verificar conexão
if (!$conexao) {
    die("❌ Erro na conexão com a base de dados: " . mysqli_connect_error());
}

echo "<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificar Admin - PinguDevelopment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #841e1e;
            padding-bottom: 10px;
        }
        .success {
            color: green;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: red;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background-color: #841e1e;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #841e1e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #a82a2a;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .password-info {
            font-family: monospace;
            background: #2d3748;
            color: #e2e8f0;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            margin: 5px 0;
        }
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🔧 Verificação de Administradores</h2>";

// Verificar estrutura da tabela
echo "<div class='test-section'>
        <h3>📊 Estrutura da Tabela</h3>";

$structure_sql = "DESCRIBE utilizadores";
$structure_result = mysqli_query($conexao, $structure_sql);

if ($structure_result) {
    echo "<table>
            <tr>
                <th>Campo</th>
                <th>Tipo</th>
                <th>Nulo</th>
                <th>Chave</th>
                <th>Padrão</th>
                <th>Extra</th>
            </tr>";
    
    while($field = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>
                <td><strong>{$field['Field']}</strong></td>
                <td>{$field['Type']}</td>
                <td>{$field['Null']}</td>
                <td>{$field['Key']}</td>
                <td>{$field['Default']}</td>
                <td>{$field['Extra']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Erro ao verificar estrutura da tabela: " . mysqli_error($conexao) . "</p>";
}

echo "</div>";

// Buscar administradores
$sql = "SELECT id, nome, email, nivel_acesso, ativo FROM utilizadores WHERE nivel_acesso = 'admin'";
$result = mysqli_query($conexao, $sql);

if (!$result) {
    echo "<p class='error'>❌ Erro na consulta: " . mysqli_error($conexao) . "</p>";
} elseif (mysqli_num_rows($result) > 0) {
    echo "<div class='success'>✅ " . mysqli_num_rows($result) . " administrador(es) encontrado(s)!</div>";
    
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Nível</th>
                <th>Ativo</th>
            </tr>";
    
    while($row = mysqli_fetch_assoc($result)) {
        $status = $row['ativo'] ? '✅ Ativo' : '❌ Inativo';
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nome']}</td>
                <td>{$row['email']}</td>
                <td>{$row['nivel_acesso']}</td>
                <td>{$status}</td>
              </tr>";
    }
    echo "</table>";
    
    // Testar senha do admin específico
    echo "<div class='test-section'>
            <h3>🔐 Teste de Senha do Admin</h3>";
    
    $test_sql = "SELECT password FROM utilizadores WHERE nivel_acesso = 'admin' AND ativo = TRUE LIMIT 1";
    $test_result = mysqli_query($conexao, $test_sql);
    
    if (mysqli_num_rows($test_result) > 0) {
        $row = mysqli_fetch_assoc($test_result);
        $test_password = 'admin123';
        
        echo "<p>Testando senha: <strong>'admin123'</strong></p>";
        echo "<p>Hash armazenado: <div class='password-info'>{$row['password']}</div></p>";
        
        if (password_verify($test_password, $row['password'])) {
            echo "<p class='success'>✅ Senha 'admin123' está correta!</p>";
        } else {
            echo "<p class='error'>❌ Senha 'admin123' não funciona!</p>";
            
            // Testar outros formatos de senha
            echo "<p>Testando outros formatos:</p>";
            echo "<ul>";
            
            // Teste 1: MD5
            if (md5($test_password) === $row['password']) {
                echo "<li class='success'>✅ Senha em MD5 funciona!</li>";
            } else {
                echo "<li class='error'>❌ Não é MD5</li>";
            }
            
            // Teste 2: SHA1
            if (sha1($test_password) === $row['password']) {
                echo "<li class='success'>✅ Senha em SHA1 funciona!</li>";
            } else {
                echo "<li class='error'>❌ Não é SHA1</li>";
            }
            
            // Teste 3: Texto plano
            if ($test_password === $row['password']) {
                echo "<li class='success'>✅ Senha em texto plano funciona!</li>";
            } else {
                echo "<li class='error'>❌ Não é texto plano</li>";
            }
            
            echo "</ul>";
        }
    } else {
        echo "<p class='warning'>⚠️ Nenhum admin ativo para testar senha</p>";
    }
    
    echo "</div>";
    
} else {
    echo "<div class='error'>❌ Nenhum administrador encontrado!</div>";
    
    // Verificar todos os usuários
    echo "<div class='test-section'>
            <h3>👥 Todos os Usuários</h3>";
    
    $all_sql = "SELECT id, nome, email, nivel_acesso, ativo FROM utilizadores LIMIT 10";
    $all_result = mysqli_query($conexao, $all_sql);
    
    if (mysqli_num_rows($all_result) > 0) {
        echo "<p>Primeiros 10 usuários encontrados:</p>";
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Ativo</th>
                </tr>";
        
        while($row = mysqli_fetch_assoc($all_result)) {
            $status = $row['ativo'] ? '✅ Ativo' : '❌ Inativo';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['nivel_acesso']}</td>
                    <td>{$status}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum usuário encontrado na tabela</p>";
    }
    
    echo "</div>";
    
    echo "<div class='warning'>
            <h3>🚨 Ação Necessária</h3>
            <p>Você precisa criar um administrador:</p>
            <p><a href='create_admin.php' class='btn'>➕ Criar Administrador</a></p>
            <p>Ou execute este comando SQL manualmente:</p>
            <div class='password-info'>
                INSERT INTO utilizadores (nome, email, password, nivel_acesso, ativo) VALUES ('Administrador', 'admin@pingu.dev', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin', TRUE);
            </div>
          </div>";
}

// Informações adicionais
echo "<div class='test-section'>
        <h3>📈 Estatísticas</h3>";

$count_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN nivel_acesso = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN nivel_acesso = 'user' THEN 1 ELSE 0 END) as users,
    SUM(CASE WHEN ativo = TRUE THEN 1 ELSE 0 END) as ativos
    FROM utilizadores";

$count_result = mysqli_query($conexao, $count_sql);
if ($count_result) {
    $stats = mysqli_fetch_assoc($count_result);
    echo "<p>Total de usuários: <strong>{$stats['total']}</strong></p>";
    echo "<p>Administradores: <strong>{$stats['admins']}</strong></p>";
    echo "<p>Usuários normais: <strong>{$stats['users']}</strong></p>";
    echo "<p>Usuários ativos: <strong>{$stats['ativos']}</strong></p>";
}

echo "</div>";

echo "<div class='actions'>
        <h3>🔗 Ações</h3>
        <p>
            <a href='create_admin.php' class='btn'>➕ Criar Novo Admin</a>
            <a href='../../assets/html/login.php' class='btn'>🔑 Testar Login</a>
            <a href='../../home.html' class='btn'>🏠 Voltar ao Site</a>
            <a href='clear_sessions.php' class='btn'>🧹 Limpar Sessões</a>
        </p>
      </div>";

echo "</div></body></html>";

mysqli_close($conexao);
?>