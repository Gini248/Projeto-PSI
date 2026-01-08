<?php
// assets/php/check_session.php
session_start();

// Configurar cabeçalho JSON ANTES de qualquer output
header('Content-Type: application/json; charset=utf-8');

// Desabilitar exibição de erros para o usuário
error_reporting(0);

// Inicializar array de resposta
$response = [
    'loggedIn' => false,
    'userName' => '',
    'isAdmin' => false
];

try {
    // Verificar se há sessão ativa
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $response['loggedIn'] = true;
        
        // Verificar qual variável de sessão está sendo usada
        if (isset($_SESSION['user_name'])) {
            $response['userName'] = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
        } elseif (isset($_SESSION['user_nome'])) {
            $response['userName'] = htmlspecialchars($_SESSION['user_nome'], ENT_QUOTES, 'UTF-8');
        } elseif (isset($_SESSION['nome'])) {
            $response['userName'] = htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8');
        }
        
        // Verificar se é admin
        // Verificar primeiro na sessão
        if (isset($_SESSION['nivel_acesso'])) {
            // Se nivel_acesso for 'admin' ou 1 ou 999, então é admin
            if ($_SESSION['nivel_acesso'] === 'admin' || $_SESSION['nivel_acesso'] == 1 || $_SESSION['nivel_acesso'] == 999) {
                $response['isAdmin'] = true;
            }
        }
        
        // Verificar também se há variável específica de admin
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $response['isAdmin'] = true;
        }
    }
    
    // Output JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Em caso de erro, retornar JSON de erro
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro interno do servidor',
        'loggedIn' => false,
        'userName' => '',
        'isAdmin' => false
    ], JSON_UNESCAPED_UNICODE);
}

exit();
?>