<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once('../../assets/php/config.php');
    
    // Verificar conexão
    if (!$conexao) {
        die("Erro na conexão com a base de dados: " . mysqli_connect_error());
    }

    // Sanitizar e validar dados
    $nome = isset($_POST['nome']) ? mysqli_real_escape_string($conexao, trim($_POST['nome'])) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conexao, trim($_POST['email'])) : '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validar campos obrigatórios
    if (empty($nome) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>
                alert('Erro: Todos os campos são obrigatórios.');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
                alert('Erro: Email inválido.');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Validar password
    if (strlen($password) < 6) {
        echo "<script>
                alert('Erro: A password deve ter pelo menos 6 caracteres.');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Verificar se passwords coincidem
    if ($password !== $confirm_password) {
        echo "<script>
                alert('Erro: As passwords não coincidem.');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Verificar se email já existe
    $check_email = "SELECT id FROM utilizadores WHERE email = '$email'";
    $result_check = mysqli_query($conexao, $check_email);
    
    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>
                alert('Erro: Este email já está registado.');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Encriptar password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Inserir novo utilizador
    $sql = "INSERT INTO utilizadores (nome, email, password) VALUES ('$nome', '$email', '$hashed_password')";
    
    if (mysqli_query($conexao, $sql)) {
        // Registro bem-sucedido
        echo "<script>
                alert('Registo realizado com sucesso! Agora pode fazer login.');
                window.location.href='login.php';
              </script>";
    } else {
        echo "<script>
                alert('Erro ao registar: " . mysqli_error($conexao) . "');
                window.location.href='register.php';
              </script>";
    }

    mysqli_close($conexao);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PinguDevelopment</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moon+Dance&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/home.css">
    <link rel="stylesheet" href="../../assets/css/form.css">
    <link rel="icon" href="../../assets/img/logo.png">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(20, 20, 20, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(132, 30, 30, 0.3);
            border: 2px solid rgb(132, 30, 30);
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-header h1 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-family: 'Moon Dance', cursive;
        }

        .register-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1em;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 14px;
        }

        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            background: #ff4444;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-fill.weak { width: 33%; background: #ff4444; }
        .strength-fill.medium { width: 66%; background: #ffbb33; }
        .strength-fill.strong { width: 100%; background: #00C851; }

        .password-requirements {
            margin-top: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .requirement i {
            margin-right: 5px;
            font-size: 10px;
        }

        .requirement.valid {
            color: #00C851;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.7);
        }

        .login-link a {
            color: rgb(132, 30, 30);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s ease;
            padding: 5px 10px;
            border-radius: 6px;
            background: rgba(132, 30, 30, 0.1);
        }

        .login-link a:hover {
            color: rgb(178, 24, 24);
            text-decoration: underline;
            background: rgba(132, 30, 30, 0.2);
        }

        /* Estilos comuns do formulário */
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

        body {
            background: url("../../assets/img/fundo.png") no-repeat fixed center/cover;
            min-height: 100vh;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-container {
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
                <a href="../../home.html" class="logo">
                    <img src="../../assets/img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                    <div class="moon-dance-regular">PinguDevelopment</div>
                </a>
                <nav>
                    <ul>
                        <li><a href="../../assets/html/loja-mlos.php"><i class="fas fa-home"></i> MLO's</a></li>
                        <li><a href="../../assets/html/loja-peds.php"><i class="fas fa-child"></i> PED's</a></li>
                        <li><a href="../../assets/html/loja-clothes.php"><i class="fas fa-tshirt"></i> Clothes</a></li>
                        <li><a href="../php/cands.php"><i class="fa fa-address-card"></i>Login</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h1>Junte-se a Nós</h1>
                <p>Crie sua conta PinguDevelopment</p>
            </div>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required placeholder="Seu nome completo">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement" id="reqLength">
                            <i class="far fa-circle"></i> Mínimo 6 caracteres
                        </div>
                        <div class="requirement" id="reqLetter">
                            <i class="far fa-circle"></i> Pelo menos uma letra
                        </div>
                        <div class="requirement" id="reqNumber">
                            <i class="far fa-circle"></i> Pelo menos um número
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repita a password">
                    <div id="passwordMatch" style="margin-top: 5px; font-size: 14px;"></div>
                </div>

                <button type="submit" class="botao">
                    Criar Conta
                    <div class="ico"></div>
                </button>

                <div class="login-link">
                    Já tem uma conta? <a href="login.php">Fazer login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validação de password em tempo real
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strengthFill');
        const passwordMatch = document.getElementById('passwordMatch');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Verificar requisitos
            const hasMinLength = password.length >= 6;
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            
            // Atualizar ícones dos requisitos
            document.getElementById('reqLength').className = `requirement ${hasMinLength ? 'valid' : ''}`;
            document.getElementById('reqLetter').className = `requirement ${hasLetter ? 'valid' : ''}`;
            document.getElementById('reqNumber').className = `requirement ${hasNumber ? 'valid' : ''}`;
            
            if (hasMinLength) strength++;
            if (hasLetter) strength++;
            if (hasNumber) strength++;
            
            // Atualizar barra de força
            if (strength === 0) {
                strengthFill.className = 'strength-fill';
                strengthFill.style.width = '0%';
            } else if (strength === 1) {
                strengthFill.className = 'strength-fill weak';
            } else if (strength === 2) {
                strengthFill.className = 'strength-fill medium';
            } else if (strength === 3) {
                strengthFill.className = 'strength-fill strong';
            }
            
            // Verificar se as passwords coincidem
            checkPasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword === '') {
                passwordMatch.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                passwordMatch.innerHTML = '<i class="fas fa-check" style="color: #00C851;"></i> As passwords coincidem';
                passwordMatch.style.color = '#00C851';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-times" style="color: #ff4444;"></i> As passwords não coincidem';
                passwordMatch.style.color = '#ff4444';
            }
        }
    </script>
</body>
</html>