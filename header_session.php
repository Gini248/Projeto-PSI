<?php
// assets/php/header_session.php
?>
<header>
    <div class="container">
        <div class="header-content">
            <a href="../../index.php" class="logo">
                <img src="../../assets/img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                <div class="moon-dance-regular">PinguDevelopment</div>
            </a>
            <nav>
                <ul>
                    <li><a href="../../assets/html/loja-mlos.php"><i class="fas fa-home"></i> MLO's</a></li>
                    <li><a href="../../assets/html/loja-peds.php"><i class="fas fa-child"></i> PED's</a></li>
                    <li><a href="../../assets/html/loja-clothes.php"><i class="fas fa-tshirt"></i> Clothes</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> Custom Orders</a></li>
                    <li>
                        <a href="#" class="cart-icon" id="open-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">3</span>
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <!-- Perfil do usuário logado -->
                    <li class="user-profile">
                        <div class="profile-dropdown">
                            <button class="profile-btn">
                                <img src="../../assets/img/default-avatar.png" alt="Avatar" class="avatar">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="profile.php"><i class="fas fa-user"></i> Meu Perfil</a>
                                <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                                <a href="admin/dashboard.php"><i class="fas fa-crown"></i> Admin</a>
                                <?php endif; ?>
                                <a href="orders.php"><i class="fas fa-box"></i> Meus Pedidos</a>
                                <a href="settings.php"><i class="fas fa-cog"></i> Configurações</a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</a>
                            </div>
                        </div>
                    </li>
                    <?php else: ?>
                    <!-- Botão de login quando não está logado -->
                    <li>
                        <a href="login.php" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<style>
    .user-profile {
        margin-left: 20px;
    }
    
    .profile-btn {
        background: rgba(132, 30, 30, 0.2);
        border: 1px solid rgba(132, 30, 30, 0.5);
        border-radius: 50px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
    }
    
    .profile-btn:hover {
        background: rgba(132, 30, 30, 0.4);
        transform: translateY(-2px);
    }
    
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgb(132, 30, 30);
    }
    
    .user-name {
        font-weight: 600;
        font-size: 14px;
    }
    
    .profile-dropdown {
        position: relative;
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: rgba(20, 20, 20, 0.98);
        border: 1px solid rgb(132, 30, 30);
        border-radius: 10px;
        padding: 10px 0;
        min-width: 200px;
        display: none;
        z-index: 1000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    
    .profile-dropdown:hover .dropdown-menu {
        display: block;
    }
    
    .dropdown-menu a {
        display: block;
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .dropdown-menu a:hover {
        background: rgba(132, 30, 30, 0.3);
        padding-left: 25px;
    }
    
    .dropdown-menu i {
        width: 20px;
        margin-right: 10px;
    }
    
    .dropdown-divider {
        height: 1px;
        background: rgba(255,255,255,0.1);
        margin: 10px 0;
    }
    
    .logout-btn {
        color: #ff6b6b !important;
    }
    
    .logout-btn:hover {
        background: rgba(255, 107, 107, 0.1) !important;
    }
    
    .login-btn {
        background: rgb(132, 30, 30);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .login-btn:hover {
        background: rgb(178, 24, 24);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(132, 30, 30, 0.4);
    }
</style>