<?php
// assets/html/admin_dashboard.php

// Primeiro incluímos o check_session
require_once('../php/check_session.php');

// Verificar se o utilizador está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../php/login.php");
    exit();
}

// Verificar se é admin
if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../../home.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PinguDevelopment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="../../assets/img/pdgif.gif" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Moon+Dance&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            padding: 40px;
            max-width: 1400px;
            margin: 40px auto;
            min-height: calc(100vh - 200px);
        }
        
        .admin-header {
            background: rgba(20, 20, 20, 0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            border: 2px solid rgb(132, 30, 30);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .admin-header h1 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 20px;
            font-family: 'Moon Dance', cursive;
        }
        
        .user-info {
            color: white;
            background: rgba(132, 30, 30, 0.15);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid rgb(132, 30, 30);
        }
        
        .user-info strong {
            color: rgb(178, 24, 24);
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .admin-card {
            background: rgba(20, 20, 20, 0.85);
            padding: 25px;
            border-radius: 12px;
            border: 2px solid rgba(132, 30, 30, 0.4);
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            border-color: rgb(132, 30, 30);
            box-shadow: 0 15px 30px rgba(132, 30, 30, 0.2);
        }
        
        .admin-card h3 {
            color: white;
            margin-bottom: 20px;
            border-bottom: 2px solid rgb(132, 30, 30);
            padding-bottom: 15px;
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-card h3 i {
            color: rgb(178, 24, 24);
        }
        
        .admin-card p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            line-height: 1.6;
            min-height: 60px;
        }
        
        .admin-btn {
            display: inline-block;
            background: linear-gradient(135deg, rgb(132, 30, 30), rgb(178, 24, 24));
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.9em;
            text-align: center;
            width: 100%;
        }
        
        .admin-btn:hover {
            background: linear-gradient(135deg, rgb(178, 24, 24), rgb(200, 30, 30));
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(132, 30, 30, 0.3);
        }
        
        .user-welcome {
            color: rgb(132, 30, 30) !important;
            font-weight: bold;
            font-size: 1.1em;
            padding: 5px 10px;
            border-radius: 5px;
            background: rgba(132, 30, 30, 0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: rgba(132, 30, 30, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(132, 30, 30, 0.3);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: rgb(178, 24, 24);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9em;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 20px;
                margin: 20px auto;
            }
            
            .admin-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .admin-header {
                padding: 20px;
            }
            
            .admin-header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../../home.html" class="logo">
                    <img src="../../assets/img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                    <div class="moon-dance-regular">PinguDevelopment</div>
                </a>
                <nav>
                    <ul>
                        <li><a href="../../home.html"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="loja-mlos.php"><i class="fas fa-home"></i> MLO's</a></li>
                        <li><a href="loja-peds.php"><i class="fas fa-child"></i> PED's</a></li>
                        <li><a href="loja-clothes.php"><i class="fas fa-tshirt"></i> Clothes</a></li>
                        <li><span class="user-welcome">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
                        <li><a href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-crown"></i> Painel Administrativo</h1>
            <div class="user-info">
                <p>
                    <strong>Utilizador:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?><br>
                    <strong>Nível de Acesso:</strong> <span style="color: rgb(178, 24, 24); font-weight: bold;"><?php echo htmlspecialchars($_SESSION['nivel_acesso']); ?></span><br>
                    <strong>ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                </p>
                
                <!-- Estatísticas rápidas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Utilizadores</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Produtos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Pedidos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Vendas Hoje</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3><i class="fas fa-users"></i> Gestão de Utilizadores</h3>
                <p>Gerir contas de utilizadores, permissões, níveis de acesso e ativação/desativação de contas.</p>
                <a href="users.php" class="admin-btn">Gerir Utilizadores</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-box"></i> Gestão de Produtos</h3>
                <p>Adicionar, editar, remover produtos. Gerenciar categorias, preços e estoque da loja.</p>
                <a href="products.php" class="admin-btn">Gerir Produtos</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-shopping-cart"></i> Gestão de Pedidos</h3>
                <p>Visualizar, aprovar, cancelar e acompanhar o status dos pedidos dos clientes.</p>
                <a href="orders.php" class="admin-btn">Ver Pedidos</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-chart-line"></i> Estatísticas</h3>
                <p>Estatísticas detalhadas de vendas, utilização do site, produtos mais vendidos e relatórios.</p>
                <a href="stats.php" class="admin-btn">Ver Estatísticas</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-cog"></i> Configurações</h3>
                <p>Configurar opções do site, métodos de pagamento, temas e configurações gerais.</p>
                <a href="settings.php" class="admin-btn">Configurações</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-file-alt"></i> Relatórios</h3>
                <p>Gerar relatórios personalizados, exportar dados em diferentes formatos (PDF, Excel).</p>
                <a href="reports.php" class="admin-btn">Gerar Relatórios</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-tags"></i> Categorias</h3>
                <p>Gerenciar categorias de produtos, criar novas categorias e organizar hierarquicamente.</p>
                <a href="categories.php" class="admin-btn">Gerir Categorias</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-comments"></i> Suporte</h3>
                <p>Gerenciar tickets de suporte, responder a clientes e acompanhar solicitações.</p>
                <a href="support.php" class="admin-btn">Central de Suporte</a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3 class="moon-dance-regular" style="font-size: 221%;">PinguDevelopment</h3>
                    <p>Painel Administrativo v1.0</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-paypal"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Links Rápidos</h3>
                    <ul>
                        <li><a href="../../home.html">Home Page</a></li>
                        <li><a href="loja-clothes.php">Roupas</a></li>
                        <li><a href="loja-mlos.php">MLO's</a></li>
                        <li><a href="loja-peds.php">PED's</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Admin</h3>
                    <ul>
                        <li><a href="users.php">Utilizadores</a></li>
                        <li><a href="products.php">Produtos</a></li>
                        <li><a href="orders.php">Pedidos</a></li>
                        <li><a href="../php/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> PinguDevelopments. Todos os direitos reservados.</p>
                <p style="font-size: 0.8em; color: rgba(255, 255, 255, 0.5);">Sessão iniciada como: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            </div>
        </div>
    </footer>

    <script>
        // Script para atualizar estatísticas em tempo real
        function updateStats() {
            // Aqui você pode adicionar chamadas AJAX para atualizar estatísticas
            fetch('../php/get_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Atualizar os números das estatísticas
                    const statNumbers = document.querySelectorAll('.stat-number');
                    if (data.users !== undefined) statNumbers[0].textContent = data.users;
                    if (data.products !== undefined) statNumbers[1].textContent = data.products;
                    if (data.orders !== undefined) statNumbers[2].textContent = data.orders;
                    if (data.todaySales !== undefined) statNumbers[3].textContent = '$' + data.todaySales;
                })
                .catch(error => console.error('Erro ao buscar estatísticas:', error));
        }
        
        // Atualizar estatísticas a cada 30 segundos
        setInterval(updateStats, 30000);
        
        // Atualizar imediatamente ao carregar
        document.addEventListener('DOMContentLoaded', updateStats);
        
        // Adicionar efeito de confirmação ao sair
        const logoutLinks = document.querySelectorAll('a[href*="logout"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja sair do painel administrativo?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>