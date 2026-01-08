<?php
// assets/php/logout.php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Remover cookie "lembrar-me"
setcookie('pingu_remember', '', time() - 3600, '/');

// Redirecionar para a página inicial
header("Location: ../../home.html");
exit();
?>