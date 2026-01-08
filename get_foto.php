<?php
// assets/php/get_foto.php
session_start();
require_once('config.php');

if (!isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

$user_id = intval($_GET['id']);

// Buscar foto da BD
$sql = "SELECT foto_perfil FROM utilizadores WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {
    mysqli_stmt_bind_result($stmt, $foto);
    mysqli_stmt_fetch($stmt);
    
    if (!empty($foto)) {
        // Definir headers para imagem
        header("Content-Type: image/jpeg");
        header("Content-Length: " . strlen($foto));
        echo $foto;
    } else {
        // Retornar imagem padrão
        header("Content-Type: image/svg+xml");
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="50" fill="rgb(132, 30, 30)"/>
                <text x="50" y="55" font-size="40" fill="white" text-anchor="middle" font-family="Arial" font-weight="bold">PD</text>
              </svg>';
    }
} else {
    header("HTTP/1.0 404 Not Found");
}

mysqli_stmt_close($stmt);
?>