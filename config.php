<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "pd_store";
$port = 3306;

// Tentar conexão
$conexao = new mysqli($servername, $username, $password, $database, $port);

if ($conexao->connect_error) {
    $error_msg = "Erro na ligação à base de dados: " . $conexao->connect_error;
    echo "<script>console.error(' $error_msg');</script>";
    die($error_msg);
}
else {
    $success_msg = " Ligação bem-sucedida à base de dados: $database";
    echo "<script>console.log('$success_msg');</script>";
}

function buscarProdutosPorTipo($conexao, $tipo, $limite = null) {
    if (!$conexao || $conexao->connect_error) {
        error_log("Conexão com banco de dados não disponível");
        return [];
    }
    
    try {
        if ($limite) {
            $sql = "SELECT * FROM produtos WHERE tipo = ? AND disponivel = 1 ORDER BY data_criacao DESC LIMIT ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("si", $tipo, $limite);
        } else {
            $sql = "SELECT * FROM produtos WHERE tipo = ? AND disponivel = 1 ORDER BY data_criacao DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $tipo);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $produtos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $produtos;
    } catch (Exception $e) {
        error_log("Erro ao buscar produtos por tipo: " . $e->getMessage());
        return [];
    }
}
?>