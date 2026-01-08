<?php
session_start();

// Verificar se já está logado via sessão
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: ../../home.html");
    exit();
}

// Verificar cookie "lembrar-me"
if (isset($_COOKIE['pingu_remember']) && !isset($_SESSION['user_id'])) {
    include_once('../../assets/php/config.php');
    
    // Validar e decodificar o cookie
    $cookie_data = base64_decode($_COOKIE['pingu_remember']);
    if ($cookie_data !== false) {
        $parts = explode('|', $cookie_data);
        
        if (count($parts) === 2) {
            list($email, $time) = $parts;
            
            // Sanitizar email
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            // Verificar se o cookie ainda é válido (30 dias) e tempo não é futuro
            $cookie_age = time() - (int)$time;
            $max_age = 86400 * 30;
            
            if ($cookie_age > 0 && $cookie_age < $max_age && (int)$time <= time()) {
                // Usar prepared statement
                $sql = "SELECT id, nome, email, nivel_acesso, password FROM utilizadores WHERE email = ? AND ativo = TRUE";
                $stmt = mysqli_prepare($conexao, $sql);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($result) == 1) {
                        $user = mysqli_fetch_assoc($result);
                        
                        // Verificar se o email ainda corresponde
                        if ($user['email'] === $email) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8');
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['nivel_acesso'] = (int)$user['nivel_acesso'];
                            $_SESSION['logged_in'] = true;
                            
                            // Regenerar ID da sessão para prevenir fixation attacks
                            session_regenerate_id(true);
                            
                            // Renovar cookie com novo tempo
                            $new_cookie_value = base64_encode($user['email'] . '|' . time());
                            setcookie('pingu_remember', $new_cookie_value, [
                                'expires' => time() + (86400 * 30),
                                'path' => '/',
                                'secure' => isset($_SERVER['HTTPS']), // Apenas HTTPS se disponível
                                'httponly' => true,
                                'samesite' => 'Strict'
                            ]);
                            
                            header("Location: ../../assets/php/dash.php");
                            exit();
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    
    // Cookie inválido - remover
    setcookie('pingu_remember', '', time() - 3600, '/');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die("Erro de segurança: Token CSRF inválido.");
    }
    
    include_once('../../assets/php/config.php');
    
    // Verificar conexão
    if (!$conexao) {
        error_log("Erro na conexão com a base de dados: " . mysqli_connect_error());
        die("Erro no sistema. Tente novamente mais tarde.");
    }
    
    // Inicializar variáveis
    $email = '';
    $password = '';
    $remember = false;
    
    // Validar e sanitizar inputs
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>
                    alert('Erro: Por favor, insira um email válido.');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
    }
    
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
        
        if (strlen($password) < 6) {
            echo "<script>
                    alert('Erro: A password deve ter pelo menos 6 caracteres.');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
    }
    
    $remember = isset($_POST['remember']);
    
    // Verificar campos obrigatórios
    if (empty($email) || empty($password)) {
        echo "<script>
                alert('Erro: Email e Password são campos obrigatórios.');
                window.location.href='login.php';
              </script>";
        exit();
    }
    
    // Usar prepared statement para prevenir SQL injection
    $sql = "SELECT id, nome, email, password, nivel_acesso FROM utilizadores WHERE email = ? AND ativo = TRUE";
    $stmt = mysqli_prepare($conexao, $sql);
    
    if (!$stmt) {
        error_log("Erro na preparação da query: " . mysqli_error($conexao));
        echo "<script>
                alert('Erro no sistema. Tente novamente mais tarde.');
                window.location.href='login.php';
              </script>";
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verificar password com password_verify
        if (password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8');
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['nivel_acesso'] = (int)$user['nivel_acesso'];
            $_SESSION['logged_in'] = true;
            
            // Regenerar ID da sessão
            session_regenerate_id(true);
            
           
            
            // Registrar login bem-sucedido (opcional)
            $log_sql = "UPDATE utilizadores SET ultimo_login = NOW() WHERE id = ?";
            $log_stmt = mysqli_prepare($conexao, $log_sql);
            if ($log_stmt) {
                mysqli_stmt_bind_param($log_stmt, "i", $user['id']);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);
            }
            
            echo "<script>
                    alert('Login realizado com sucesso! Bem-vindo.');
                    window.location.href='../../home.html';
                  </script>";
            
        } else {
            // Password incorreta
            echo "<script>
                    alert('Erro: Credenciais inválidas.');
                    window.location.href='login.php';
                  </script>";
        }
    } else {
        // Utilizador não encontrado
        echo "<script>
                alert('Erro: Credenciais inválidas.');
                window.location.href='login.php';
              </script>";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    exit();
}

// Gerar token CSRF para o próximo formulário
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html> 
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PinguDevelopment</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moon+Dance&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/home.css">
    <link rel="stylesheet" href="../../assets/css/form.css">
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    <style>
        /* Seus estilos CSS permanecem os mesmos... */
        .login-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(20, 20, 20, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(132, 30, 30, 0.3);
            border: 2px solid rgb(132, 30, 30);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-family: 'Moon Dance', cursive;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1em;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(132, 30, 30, 0.1);
            border: 1px solid rgba(132, 30, 30, 0.3);
        }

        .remember-me:hover {
            background: rgba(132, 30, 30, 0.2);
            border-color: rgba(132, 30, 30, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(132, 30, 30, 0.2);
        }

        .remember-me input {
            margin-right: 8px;
            cursor: pointer;
            width: 16px;
            height: 16px;
            accent-color: rgb(132, 30, 30);
        }

        .forgot-password {
            color: rgb(132, 30, 30);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(132, 30, 30, 0.1);
            border: 1px solid rgba(132, 30, 30, 0.3);
        }

        .forgot-password:hover {
            color: rgb(178, 24, 24);
            text-decoration: underline;
            background: rgba(132, 30, 30, 0.2);
            border-color: rgba(132, 30, 30, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(132, 30, 30, 0.2);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.7);
        }

        .register-link a {
            color: rgb(132, 30, 30);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s ease;
            padding: 5px 10px;
            border-radius: 6px;
            background: rgba(132, 30, 30, 0.1);
        }

        .register-link a:hover {
            color: rgb(178, 24, 24);
            text-decoration: underline;
            background: rgba(132, 30, 30, 0.2);
        }

        .social-login {
            margin-top: 30px;
            text-align: center;
        }

        .social-login p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
            position: relative;
        }

        .social-login p::before,
        .social-login p::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid rgba(132, 30, 30, 0.5);
            background: rgba(30, 30, 30, 0.8);
            color: white;
            font-size: 1.2em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .social-btn:hover {
            border-color: rgb(132, 30, 30);
            background: rgba(132, 30, 30, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(132, 30, 30, 0.3);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 1.1em;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: rgb(132, 30, 30);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(132, 30, 30, 0.5);
            border-radius: 8px;
            background: rgba(30, 30, 30, 0.8);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgb(132, 30, 30);
            box-shadow: 0 0 10px rgba(132, 30, 30, 0.3);
        }

        .botao {
            color: white;   
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 35px;
            border: 2px solid rgb(132, 30, 30);
            border-top-left-radius: 0px;
            border-bottom-left-radius: 0px;
            border-top-right-radius: 50px;
            border-bottom-right-radius: 50px;
            background: linear-gradient(135deg, transparent, rgba(132, 30, 30, 0.1));
            font-size: 16px;
            border-color: rgb(178, 24, 24);
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 1px;
        }

        .botao::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 30%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(132, 30, 30, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .botao:hover::before {
            left: 100%;
        }

        .botao:hover {
            color: rgb(132, 30, 30);
            border-color: rgb(178, 24, 24);
            box-shadow: 0 10px 30px rgba(132, 30, 30, 0.5);
            transform: translateY(-3px);
            background: linear-gradient(135deg, transparent, rgba(132, 30, 30, 0.2));
        }

        .botao:active {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(132, 30, 30, 0.4);
        }

        .botao.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .botao.loading .ico {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .ico {
            width: 24px;
            height: 24px;
            margin-left: 12px;
            background-image: url('../../assets/img/logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: all 0.6s ease-in-out;
            filter: brightness(0) saturate(100%) invert(20%) sepia(80%) saturate(5000%) hue-rotate(350deg);
        }

        .botao:hover .ico {
            transform: rotate(360deg) scale(1.1);
            filter: brightness(0) saturate(100%) invert(100%);
        }

        body {
            background: url("../../assets/img/fundo.png") no-repeat fixed center/cover;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .remember-me, .forgot-password {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .login-container {
                margin: 30px 20px;
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../../index.html" class="logo">
                    <img src="../../assets/img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                    <div class="moon-dance-regular">PinguDevelopment</div>
                </a>
                <nav>
                    <ul>
                        <li><a href="../../assets/html/loja-mlos.html"><i class="fas fa-home"></i> MLO's</a></li>
                        <li><a href="../../assets/html/loja-peds.html"><i class="fas fa-child"></i> PED's</a></li>
                        <li><a href="../../assets/html/loja-clothes.html"><i class="fas fa-tshirt"></i> Clothes</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Custom Orders</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your PinguDevelopment account</p>
            </div>

            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Por favor, insira um email válido">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required 
                               placeholder="Sua password" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Lembrar-me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Esqueceu a password?</a>
                </div>

                <button type="submit" class="botao" id="submitBtn">
                    Entrar na Conta
                    <div class="ico"></div>
                </button>

                <div class="register-link">
                    Não tens uma conta? <a href="register.php">Criar conta</a>
                </div>

                <div class="social-login">
                    <p>Ou entra no nosso</p>
                    <div class="social-buttons">
                       <a href="https://discord.gg/qGmqZSyAz3">
                        <button type="button" class="social-btn">
                            <i class="fab fa-discord"></i>
                        </button>
                       </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Validação do formulário no cliente
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitBtn');
            
            // Validação básica
            if (!email || !password) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                e.preventDefault();
                return;
            }
            
            // Validar formato do email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Por favor, insira um email válido.');
                e.preventDefault();
                return;
            }
            
            // Validar comprimento da password
            if (password.length < 6) {
                alert('A password deve ter pelo menos 6 caracteres.');
                e.preventDefault();
                return;
            }
            
            // Mostrar estado de carregamento
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'A processar... <div class="ico"></div>';
        });
    </script>
</body>
</html>