<?php
// assets/php/profile.php
session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../login.php");
    exit();
}

// Incluir configuração da base de dados
require_once('config.php');

// Obter dados do utilizador
$user_id = $_SESSION['user_id'];
$user = [];
$message = '';
$message_type = '';

// Buscar dados do utilizador
$sql = "SELECT id, nome, email, nivel_acesso, foto_perfil, data_registro as data_registo
        FROM utilizadores 
        WHERE id = ? AND ativo = TRUE";
$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Bind dos resultados
    mysqli_stmt_bind_result($stmt, $id, $nome, $email, $nivel_acesso, $foto_perfil, $data_registo);
    
    if (mysqli_stmt_fetch($stmt)) {
        $user = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'nivel_acesso' => $nivel_acesso,
            'foto_perfil' => $foto_perfil,
            'data_registo' => $data_registo
        ];
        
        // Processar foto do perfil
        if (!empty($user['foto_perfil'])) {
            // Converter para base64
            $user['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($user['foto_perfil']);
        } else {
            // Criar avatar SVG padrão
            $initials = substr($user['nome'], 0, 2);
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="50" fill="rgb(132, 30, 30)"/>
                    <text x="50" y="55" font-size="40" fill="white" text-anchor="middle" font-family="Arial" font-weight="bold">' . strtoupper($initials) . '</text>
                </svg>';
            $user['foto_base64'] = 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
        
        // Armazenar na sessão para a navbar
        if (!isset($_SESSION['user_photo']) && !empty($user['foto_base64'])) {
            $_SESSION['user_photo'] = $user['foto_base64'];
        }
        
        // Adicionar campos extras
        $user['telefone'] = '';
        $user['morada'] = '';
        $user['cidade'] = '';
        $user['codigo_postal'] = '';
        $user['pais'] = '';
        $user['ultimo_login'] = date('Y-m-d H:i:s');
        
    } else {
        // Utilizador não encontrado
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Processar atualização do perfil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $message = "Erro de segurança: Token inválido.";
        $message_type = "error";
    } else {
        // Sanitizar inputs
        $nome = isset($_POST['nome']) ? mysqli_real_escape_string($conexao, trim($_POST['nome'])) : $user['nome'];
        
        // Variável para controlar se há nova foto
        $has_new_photo = false;
        $new_photo_data = null;
        $new_photo_base64 = null;
        
        // Processar upload de foto
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $foto_info = $_FILES['foto_perfil'];
            
            // Verificar se é uma imagem
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($foto_info['tmp_name']);
            
            if (in_array($file_type, $allowed_types)) {
                // Verificar tamanho (max 2MB para BD)
                if ($foto_info['size'] <= 2 * 1024 * 1024) {
                    // Ler o arquivo como binário
                    $new_photo_data = file_get_contents($foto_info['tmp_name']);
                    $has_new_photo = true;
                    // Criar versão base64 para preview
                    $new_photo_base64 = 'data:' . $file_type . ';base64,' . base64_encode($new_photo_data);
                } else {
                    $message = "A imagem é muito grande. Tamanho máximo: 2MB.";
                    $message_type = "error";
                }
            } else {
                $message = "Formato de imagem não suportado. Use JPG, PNG, GIF ou WebP.";
                $message_type = "error";
            }
        }
        
        // Atualizar na base de dados
        if (empty($message)) {
            if ($has_new_photo && $new_photo_data !== null) {
                // Método ALTERNATIVO: Usar query direta para evitar problemas com BLOBs
                $escaped_foto = mysqli_real_escape_string($conexao, $new_photo_data);
                $sql_update = "UPDATE utilizadores SET nome = '$nome', foto_perfil = '$escaped_foto' WHERE id = $user_id";
                
                $result = mysqli_query($conexao, $sql_update);
                
                if ($result) {
                    $message = "Perfil atualizado com sucesso!";
                    $message_type = "success";
                    
                    // Atualizar dados na sessão
                    $_SESSION['user_name'] = $nome;
                    
                    // Atualizar dados do utilizador localmente com a NOVA foto
                    $user['foto_perfil'] = $new_photo_data;
                    $user['foto_base64'] = $new_photo_base64;
                    $user['nome'] = $nome;
                    
                    // Atualizar foto na sessão
                    $_SESSION['user_photo'] = $new_photo_base64;
                    
                    // DEBUG: Verificar se a foto foi realmente salva
                    error_log("DEBUG: Foto atualizada - tamanho: " . strlen($new_photo_data) . " bytes");
                    
                } else {
                    $message = "Erro ao atualizar perfil (com foto): " . mysqli_error($conexao);
                    $message_type = "error";
                }
            } else {
                // Atualizar apenas o nome (sem foto)
                $sql_update = "UPDATE utilizadores SET nome = '$nome' WHERE id = $user_id";
                $result = mysqli_query($conexao, $sql_update);
                
                if ($result) {
                    $message = "Perfil atualizado com sucesso!";
                    $message_type = "success";
                    
                    // Atualizar dados na sessão
                    $_SESSION['user_name'] = $nome;
                    $user['nome'] = $nome;
                    
                } else {
                    $message = "Erro ao atualizar perfil (sem foto): " . mysqli_error($conexao);
                    $message_type = "error";
                }
            }
            
            // Recarregar a página para mostrar dados atualizados
            if ($message_type === 'success') {
                // Redirecionar para evitar reenvio do formulário
                header("Location: profile.php?updated=1");
                exit();
            }
        }
    }
}

// Verificar se foi redirecionado após atualização
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $message = "Perfil atualizado com sucesso!";
    $message_type = "success";
}

// Gerar novo token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Formatar data de registo
$data_registo_formatada = '';
if (!empty($user['data_registo'])) {
    try {
        $data_registo = new DateTime($user['data_registo']);
        $data_registo_formatada = $data_registo->format('d/m/Y H:i');
    } catch (Exception $e) {
        $data_registo_formatada = date('d/m/Y H:i');
    }
} else {
    $data_registo_formatada = date('d/m/Y H:i');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - PinguDevelopment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="shortcut icon" href="../img/pdgif.gif" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Moon+Dance&display=swap" rel="stylesheet">
    <style>
        /* ========== NAVBAR ========== */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(20, 20, 20, 0.98);
            border-bottom: 2px solid rgb(132, 30, 30);
            z-index: 1000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            min-height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            gap: 10px;
        }

        .pd-gif {
            height: 40px;
            width: auto;
        }

        .moon-dance-regular {
            font-family: 'Moon Dance', cursive;
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, rgb(132, 30, 30), rgb(178, 24, 24));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        #nav-menu {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            height: 100%;
            list-style: none;
            gap: 10px;
        }

        #nav-menu > li {
            display: flex;
            align-items: center;
            height: 100%;
        }

        #nav-menu > li > a {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 8px;
            font-size: 15px;
        }

        #nav-menu > li > a:hover {
            background: rgba(132, 30, 30, 0.2);
            color: rgb(178, 24, 24);
        }

        /* Dropdown do perfil */
        .profile-container {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
            margin-left: 10px;
        }

        .profile-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            height: 44px;
            width: 44px;
        }

        .profile-toggle:hover {
            border-color: rgb(132, 30, 30);
            box-shadow: 0 0 10px rgba(132, 30, 30, 0.5);
        }

        .profile-image {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgb(132, 30, 30);
            background-color: #1a1a1a;
        }

        /* ========== ESTILOS DO PERFIL ========== */
        body {
            background: url("../img/fundo.png") no-repeat fixed center/cover;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            padding-top: 70px;
        }

        .profile-main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .back-to-home {
            margin-bottom: 20px;
        }

        .back-to-home a {
            color: rgb(132, 30, 30);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 6px;
            background: rgba(132, 30, 30, 0.1);
            border: 1px solid rgba(132, 30, 30, 0.3);
            transition: all 0.3s ease;
        }

        .back-to-home a:hover {
            color: rgb(178, 24, 24);
            background: rgba(132, 30, 30, 0.2);
            transform: translateX(-5px);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-success {
            background: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.5);
            color: #28a745;
        }

        .message-error {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .profile-sidebar {
            background: rgba(20, 20, 20, 0.95);
            border: 2px solid rgb(132, 30, 30);
            border-radius: 15px;
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 90px;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-avatar-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid rgb(132, 30, 30);
            object-fit: cover;
            margin: 0 auto 15px;
            background: #1a1a1a;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-avatar-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(132, 30, 30, 0.5);
        }

        .profile-avatar-upload {
            position: relative;
            display: inline-block;
        }

        .profile-avatar-upload input[type="file"] {
            position: absolute;
            width: 150px;
            height: 150px;
            opacity: 0;
            cursor: pointer;
            border-radius: 50%;
        }

        .profile-avatar-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            cursor: pointer;
            display: block;
            margin-top: 10px;
        }

        .profile-avatar-label i {
            color: rgb(132, 30, 30);
            margin-right: 5px;
        }

        .profile-info h3 {
            color: white;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(132, 30, 30, 0.5);
            padding-bottom: 10px;
        }

        .info-item {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .info-item i {
            width: 20px;
            color: rgb(132, 30, 30);
            margin-right: 10px;
            flex-shrink: 0;
        }

        .info-label {
            font-weight: bold;
            margin-right: 5px;
            min-width: 120px;
            flex-shrink: 0;
        }

        .info-value {
            color: rgba(255, 255, 255, 0.8);
            word-break: break-word;
        }

        .profile-edit-form {
            background: rgba(20, 20, 20, 0.95);
            border: 2px solid rgb(132, 30, 30);
            border-radius: 15px;
            padding: 25px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section h3 {
            color: white;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(132, 30, 30, 0.5);
            display: flex;
            align-items: center;
            font-size: 1.2rem;
        }

        .form-section h3 i {
            margin-right: 10px;
            color: rgb(132, 30, 30);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: white;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid rgba(132, 30, 30, 0.5);
            border-radius: 6px;
            background: rgba(30, 30, 30, 0.8);
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgb(132, 30, 30);
            box-shadow: 0 0 10px rgba(132, 30, 30, 0.3);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: 2px solid rgb(132, 30, 30);
            border-radius: 6px;
            background: rgba(132, 30, 30, 0.1);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, rgb(132, 30, 30), rgb(178, 24, 24));
            border-color: rgb(178, 24, 24);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, rgb(178, 24, 24), rgb(132, 30, 30));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(132, 30, 30, 0.4);
        }

        /* Responsividade */
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 250px 1fr;
                gap: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: static;
                top: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header com navbar -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../../home.html" class="logo">
                    <img src="../img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                    <div class="moon-dance-regular">PinguDevelopment</div>
                </a>
                <nav>
                    <ul id="nav-menu">
                        <li><a href="../html/loja-mlos.php"><i class="fas fa-home"></i> <span>MLO's</span></a></li>
                        <li><a href="../html/loja-peds.php"><i class="fas fa-child"></i> <span>PED's</span></a></li>
                        <li><a href="../html/loja-clothes.php"><i class="fas fa-tshirt"></i> <span>Clothes</span></a></li>
                        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <!-- Perfil do utilizador logado -->
                            <li class="profile-container" id="profile-container">
                                <div class="profile-toggle" id="profile-toggle">
                                    <?php
                                    // Usar foto do utilizador atual
                                    $foto_src = $user['foto_base64'] ?? '../img/pdgif.gif';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($foto_src, ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Perfil" 
                                         class="profile-image"
                                         id="navbar-profile-image">
                                </div>
                            </li>
                        <?php else: ?>
                            <li id="login-li">
                                <a href="login.php" id="login-link"><i class="fa fa-address-card"></i> <span>Login</span></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="profile-main-container">
        <!-- Link para voltar -->
        <div class="back-to-home">
            <a href="../../home.html">
                <i class="fas fa-arrow-left"></i> Voltar para Home
            </a>
        </div>

        <!-- Mensagens -->
        <?php if (!empty($message)): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
                <span><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="profile-avatar-upload">
                        <img src="<?php echo htmlspecialchars($user['foto_base64'] ?? '../img/pdgif.gif', ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="Foto de Perfil" 
                             class="profile-avatar-img"
                             id="avatar-preview">
                        <input type="file" id="avatar-input" name="foto_perfil" accept="image/*">
                    </div>
                    <label for="avatar-input" class="profile-avatar-label">
                        <i class="fas fa-camera"></i> Clique para alterar (max 2MB)
                    </label>
                </div>

                <div class="profile-info">
                    <h3>Informações da Conta</h3>
                    
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span class="info-label">Nome:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-shield-alt"></i>
                        <span class="info-label">Conta:</span>
                        <span class="info-value">
                            <?php 
                                if ($user['nivel_acesso'] == 999) echo 'Super Admin';
                                elseif ($user['nivel_acesso'] == 1) echo 'Administrador';
                                else echo 'Membro';
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="info-label">Membro desde:</span>
                        <span class="info-value"><?php echo $data_registo_formatada; ?></span>
                    </div>
                </div>
            </div>

            <!-- Formulário de edição -->
            <form method="POST" action="profile.php" enctype="multipart/form-data" class="profile-edit-form" id="profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-section">
                    <h3><i class="fas fa-user-circle"></i> Informações Pessoais</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" 
                                   value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" 
                                   disabled>
                            <small style="color: #aaa; font-size: 12px;">Contacte o suporte para alterar o email</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='../../home.html'">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatar-input');
            const avatarPreview = document.getElementById('avatar-preview');
            const profileForm = document.getElementById('profile-form');
            const navbarImage = document.getElementById('navbar-profile-image');
            
            // Preview da imagem selecionada
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Verificar tamanho (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        showMessage('A imagem é muito grande. Tamanho máximo: 2MB', 'error');
                        avatarInput.value = '';
                        return;
                    }
                    
                    // Verificar tipo
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        showMessage('Formato de imagem não suportado. Use JPG, PNG, GIF ou WebP.', 'error');
                        avatarInput.value = '';
                        return;
                    }
                    
                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        
                        // Atualizar também a imagem da navbar (preview apenas)
                        if (navbarImage) {
                            navbarImage.src = e.target.result;
                        }
                        
                        showMessage('Foto carregada! Clique em "Guardar Alterações" para salvar permanentemente.', 'success');
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Mostrar mensagens
            function showMessage(text, type) {
                // Remover mensagens existentes (exceto as do PHP)
                const existingMessages = document.querySelectorAll('.message[data-temporary]');
                existingMessages.forEach(msg => msg.remove());
                
                // Criar nova mensagem
                const messageDiv = document.createElement('div');
                messageDiv.className = `message message-${type}`;
                messageDiv.setAttribute('data-temporary', 'true');
                
                let icon = 'info-circle';
                if (type === 'success') icon = 'check-circle';
                if (type === 'error') icon = 'exclamation-circle';
                
                messageDiv.innerHTML = `
                    <i class="fas fa-${icon}"></i>
                    <span>${text}</span>
                `;
                
                // Inserir após o link de voltar
                const backLink = document.querySelector('.back-to-home');
                if (backLink) {
                    backLink.parentNode.insertBefore(messageDiv, backLink.nextSibling);
                }
                
                // Remover após 5 segundos (se não for erro)
                if (type !== 'error') {
                    setTimeout(() => {
                        if (messageDiv.parentNode) {
                            messageDiv.parentNode.removeChild(messageDiv);
                        }
                    }, 5000);
                }
            }
            
            // Validação do formulário
            profileForm.addEventListener('submit', function(e) {
                const nome = document.getElementById('nome').value;
                if (!nome || nome.trim().length < 2) {
                    e.preventDefault();
                    showMessage('O nome deve ter pelo menos 2 caracteres.', 'error');
                    return;
                }
                
                // Se houver uma imagem selecionada, verificar novamente
                if (avatarInput.files.length > 0) {
                    const file = avatarInput.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        e.preventDefault();
                        showMessage('A imagem é muito grande. Tamanho máximo: 2MB', 'error');
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html>