<?php
session_start();
include_once('../../assets/php/config.php');

// Função para buscar produtos em destaque
function buscarProdutosDestaque($conexao, $limite = 4) {
    $sql = "SELECT * FROM produtos WHERE destaque = 1 AND disponivel = 1 ORDER BY data_criacao DESC LIMIT ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $produtos;
}

// Processar operações CRUD
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'adicionar':
                adicionarProduto($conexao);
                break;
            case 'editar':
                editarProduto($conexao);
                break;
            case 'eliminar':
                eliminarProduto($conexao);
                break;
        }
    }
}

function adicionarProduto($conexao) {
    $nome = $conexao->real_escape_string($_POST['nome']);
    $descricao = $conexao->real_escape_string($_POST['descricao']);
    $tipo = $conexao->real_escape_string($_POST['tipo']);
    $preco = floatval($_POST['preco']);
    $preco_desconto = !empty($_POST['preco_desconto']) ? floatval($_POST['preco_desconto']) : NULL;
    $categoria = $conexao->real_escape_string($_POST['categoria']);
    $tags = $conexao->real_escape_string($_POST['tags']);
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $disponivel = isset($_POST['disponivel']) ? 1 : 0;

    $imagens = [];
    if (!empty($_FILES['imagens']['name'][0])) {
        $imagens = processarImagens($_FILES['imagens']);
    }

    $imagens_json = json_encode($imagens);

    $sql = "INSERT INTO produtos (nome, descricao, tipo, preco, preco_desconto, categoria, imagens, destaque, disponivel, tags) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssddssiis", $nome, $descricao, $tipo, $preco, $preco_desconto, $categoria, $imagens_json, $destaque, $disponivel, $tags);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Produto adicionado com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao adicionar produto: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: dash.php");
    exit();
}

function editarProduto($conexao) {
    $id = intval($_POST['id']);
    $nome = $conexao->real_escape_string($_POST['nome']);
    $descricao = $conexao->real_escape_string($_POST['descricao']);
    $tipo = $conexao->real_escape_string($_POST['tipo']);
    $preco = floatval($_POST['preco']);
    $preco_desconto = !empty($_POST['preco_desconto']) ? floatval($_POST['preco_desconto']) : NULL;
    $categoria = $conexao->real_escape_string($_POST['categoria']);
    $tags = $conexao->real_escape_string($_POST['tags']);
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $disponivel = isset($_POST['disponivel']) ? 1 : 0;

    $imagens_json = $_POST['imagens_existentes'] ?? '[]';
    if (!empty($_FILES['imagens']['name'][0])) {
        $novas_imagens = processarImagens($_FILES['imagens']);
        $imagens_existentes = json_decode($imagens_json, true) ?: [];
        $imagens = array_merge($imagens_existentes, $novas_imagens);
        $imagens_json = json_encode($imagens);
    }

    $sql = "UPDATE produtos SET nome=?, descricao=?, tipo=?, preco=?, preco_desconto=?, categoria=?, imagens=?, destaque=?, disponivel=?, tags=? WHERE id=?";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssddssiisi", $nome, $descricao, $tipo, $preco, $preco_desconto, $categoria, $imagens_json, $destaque, $disponivel, $tags, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Produto atualizado com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao atualizar produto: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: dash.php");
    exit();
}

function eliminarProduto($conexao) {
    $id = intval($_POST['id']);
    
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Produto eliminado com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao eliminar produto: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: dash.php");
    exit();
}

function processarImagens($files) {
    $imagens = [];
    $upload_dir = '../../assets/img/produtos/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = uniqid() . '.' . $extensao;
                $caminho_completo = $upload_dir . $nome_arquivo;
                
                if (move_uploaded_file($files['tmp_name'][$i], $caminho_completo)) {
                    $imagens[] = $nome_arquivo;
                }
            }
        }
    }
    
    return $imagens;
}

// Buscar produtos
$sql = "SELECT * FROM produtos ORDER BY data_criacao DESC";
$result = $conexao->query($sql);
$produtos = [];
if ($result) {
    $produtos = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PinguDevelopment</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moon+Dance&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/home.css">
    <link rel="icon" href="../../assets/img/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: url("../../assets/img/fundo.png") no-repeat fixed center/cover;
            min-height: 100vh;
            color: white;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgb(132, 30, 30);
        }

        .dashboard-header h1 {
            font-family: 'Moon Dance', cursive;
            font-size: 2.5em;
            color: white;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-primary {
            background: rgb(132, 30, 30);
            color: white;
        }

        .btn-primary:hover {
            background: rgb(178, 24, 24);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .card {
            background: rgba(20, 20, 20, 0.95);
            border: 2px solid rgb(132, 30, 30);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(132, 30, 30, 0.2);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(132, 30, 30, 0.3);
            color: white;
        }

        .table th {
            background: rgba(132, 30, 30, 0.2);
            font-weight: 700;
        }

        .table tr:hover {
            background: rgba(132, 30, 30, 0.1);
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: black;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background: rgba(20, 20, 20, 0.95);
            margin: 5% auto;
            padding: 30px;
            border: 2px solid rgb(132, 30, 30);
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: rgb(132, 30, 30);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(132, 30, 30, 0.5);
            border-radius: 8px;
            background: rgba(30, 30, 30, 0.8);
            color: white;
            font-size: 14px;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: rgb(132, 30, 30);
            box-shadow: 0 0 10px rgba(132, 30, 30, 0.3);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
        }

        .image-preview {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .image-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(132, 30, 30, 0.5);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .price {
            font-weight: 700;
        }

        .original-price {
            text-decoration: line-through;
            color: #888;
            margin-right: 8px;
        }

        .sale-price {
            color: rgb(132, 30, 30);
        }

      
       

        .moon-dance-regular {
            font-family: 'Moon Dance', cursive;
            font-size: 2rem;
            font-weight: 400;
        }


        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .table {
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .modal-content {
                margin: 10% auto;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header consistente com o teu site -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../../home.html" class="logo">
                    <img src="../../assets/img/pdgif.gif" class="pd-gif" alt="PinguDevelopment Logo">
                    <div class="moon-dance-regular">PinguDevelopment</div>
                </a>
                <nav>
                    <ul>
                        <li><a href="../../assets/html/loja-mlos.php"><i class="fas fa-home"></i>HOME</a></li>
                        <li><a href="../../assets/html/loja-mlos.php"><i class="fas fa-pen"></i>Custom Orders</a></li>

                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Mensagens de alerta -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="dashboard-header">
            <h1>Dashboard - Gestão de Produtos</h1>
            <button class="btn btn-primary" onclick="abrirModal('modalAdicionar')">
                <i class="fas fa-plus"></i> Adicionar Produto
            </button>
        </div>

        <div class="card">
            <h2>Lista de Produtos (<?php echo count($produtos); ?>)</h2>
            <?php if (empty($produtos)): ?>
                <p style="text-align: center; padding: 20px; color: #888;">
                    Nenhum produto encontrado. Clique em "Adicionar Produto" para começar.
                </p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                            <th>Categoria</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): 
                            $imagens = json_decode($produto['imagens'] ?? '[]', true) ?: [];
                        ?>
                            <tr>
                                <td><?php echo $produto['id']; ?></td>
                                <td>
                                    <?php if (!empty($imagens)): ?>
                                        <img src="../../assets/img/produtos/<?php echo $imagens[0]; ?>" 
                                             alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWxsPSIjMzMzIi8+CjxwYXRoIGQ9Ik0yNSAzMEMyOC44NjYgMzAgMzIgMjYuODY2IDMyIDIzQzMyIDE5LjEzNCAyOC44NjYgMTYgMjUgMTZDMjEuMTM0IDE2IDE4IDE5LjEzNCAxOCAyM0MxOCAyNi44NjYgMjEuMTM0IDMwIDI1IDMwWiIgZmlsbD0iIzY2NiIvPgo8cGF0aCBkPSJNMTguNSA0MEMxNi41NjcgNDAgMTUgMzguNDMzIDE1IDM2LjVMMTUgMTguNUMxNSAxNi41NjcgMTYuNTY3IDE1IDE4LjUgMTVMMzEuNSAxNUMzMy40MzMgMTUgMzUgMTYuNTY3IDM1IDE4LjVMMzUgMzYuNUMzNSAzOC40MzMgMzMuNDMzIDQwIDMxLjUgNDBMMTguNSA0MFoiIGZpbGw9IiM2NjYiLz4KPC9zdmc+'">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #333; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #666;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td>
                                    <span class="badge badge-warning"><?php echo $produto['tipo']; ?></span>
                                </td>
                                <td class="price">
                                    <?php if ($produto['preco_desconto'] && $produto['preco_desconto'] > 0): ?>
                                        <span class="original-price">€<?php echo number_format($produto['preco'], 2); ?></span>
                                        <span class="sale-price">€<?php echo number_format($produto['preco_desconto'], 2); ?></span>
                                    <?php else: ?>
                                        €<?php echo number_format($produto['preco'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                <td>
                                    <?php if ($produto['disponivel']): ?>
                                        <span class="badge badge-success">Disponível</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Indisponível</span>
                                    <?php endif; ?>
                                    <?php if ($produto['destaque']): ?>
                                        <span class="badge badge-warning" style="margin-left: 5px;">Destaque</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn btn-success" onclick="editarProduto(<?php echo htmlspecialchars(json_encode($produto)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem a certeza que deseja eliminar este produto?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Adicionar Produto -->
    <div id="modalAdicionar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalAdicionar')">&times;</span>
            <h2>Adicionar Novo Produto</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="adicionar">
                
                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecionar Tipo</option>
                        <option value="MLO">MLO</option>
                        <option value="PED">PED</option>
                        <option value="Clothes">Clothes</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preco">Preço (€) *</label>
                    <input type="number" id="preco" name="preco" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="preco_desconto">Preço com Desconto (€)</label>
                    <input type="number" id="preco_desconto" name="preco_desconto" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <input type="text" id="categoria" name="categoria">
                </div>

                <div class="form-group">
                    <label for="tags">Tags (separadas por vírgula)</label>
                    <input type="text" id="tags" name="tags" placeholder="ex: moderno, interior, casa">
                </div>

                <div class="form-group">
                    <label for="imagens">Imagens</label>
                    <input type="file" id="imagens" name="imagens[]" multiple accept="image/*">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="destaque" name="destaque" value="1">
                    <label for="destaque">Produto em Destaque</label>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="disponivel" name="disponivel" value="1" checked>
                    <label for="disponivel">Disponível para Venda</label>
                </div>

                <button type="submit" class="btn btn-primary">Adicionar Produto</button>
            </form>
        </div>
    </div>

    <!-- Modal Editar Produto -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal('modalEditar')">&times;</span>
            <h2>Editar Produto</h2>
            <form method="POST" enctype="multipart/form-data" id="formEditar">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="imagens_existentes" id="imagens_existentes">
                
                <div class="form-group">
                    <label for="edit_nome">Nome do Produto *</label>
                    <input type="text" id="edit_nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="edit_descricao">Descrição</label>
                    <textarea id="edit_descricao" name="descricao" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_tipo">Tipo *</label>
                    <select id="edit_tipo" name="tipo" required>
                        <option value="">Selecionar Tipo</option>
                        <option value="MLO">MLO</option>
                        <option value="PED">PED</option>
                        <option value="Clothes">Clothes</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_preco">Preço (€) *</label>
                    <input type="number" id="edit_preco" name="preco" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="edit_preco_desconto">Preço com Desconto (€)</label>
                    <input type="number" id="edit_preco_desconto" name="preco_desconto" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="edit_categoria">Categoria</label>
                    <input type="text" id="edit_categoria" name="categoria">
                </div>

                <div class="form-group">
                    <label for="edit_tags">Tags (separadas por vírgula)</label>
                    <input type="text" id="edit_tags" name="tags" placeholder="ex: moderno, interior, casa">
                </div>

                <div class="form-group">
                    <label>Imagens Existentes</label>
                    <div id="preview-imagens" class="image-preview"></div>
                </div>

                <div class="form-group">
                    <label for="edit_imagens">Adicionar Novas Imagens</label>
                    <input type="file" id="edit_imagens" name="imagens[]" multiple accept="image/*">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="edit_destaque" name="destaque" value="1">
                    <label for="edit_destaque">Produto em Destaque</label>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="edit_disponivel" name="disponivel" value="1">
                    <label for="edit_disponivel">Disponível para Venda</label>
                </div>

                <button type="submit" class="btn btn-primary">Atualizar Produto</button>
            </form>
        </div>
    </div>

    <script>
        // Funções para modais
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Função para editar produto
        function editarProduto(produto) {
            document.getElementById('edit_id').value = produto.id;
            document.getElementById('edit_nome').value = produto.nome;
            document.getElementById('edit_descricao').value = produto.descricao || '';
            document.getElementById('edit_tipo').value = produto.tipo;
            document.getElementById('edit_preco').value = produto.preco;
            document.getElementById('edit_preco_desconto').value = produto.preco_desconto || '';
            document.getElementById('edit_categoria').value = produto.categoria || '';
            document.getElementById('edit_tags').value = produto.tags || '';
            document.getElementById('edit_destaque').checked = produto.destaque == 1;
            document.getElementById('edit_disponivel').checked = produto.disponivel == 1;
            
            // Processar imagens
            const imagens = JSON.parse(produto.imagens || '[]');
            document.getElementById('imagens_existentes').value = produto.imagens || '[]';
            
            const previewContainer = document.getElementById('preview-imagens');
            previewContainer.innerHTML = '';
            
            imagens.forEach(imagem => {
                const img = document.createElement('img');
                img.src = `../../assets/img/produtos/${imagem}`;
                img.alt = 'Imagem do produto';
                img.onerror = "this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjMzMzIi8+CjxwYXRoIGQ9Ik00MCA0OEM0Ni42MjcgNDggNTIgNDIuNjI3IDUyIDM2QzUyIDI5LjM3MyA0Ni42MjcgMjQgNDAgMjRDMzMuMzczIDI0IDI4IDI5LjM3MyAyOCAzNkMyOCA0Mi42MjcgMzMuMzczIDQ4IDQwIDQ4WiIgZmlsbD0iIzY2NiIvPgo8cGF0aCBkPSJNMjkuNiA2NEMyNi40NzcgNjQgMjQgNjEuNTIzIDI0IDU4LjRMMjQgMjkuNkMyNCAyNi40NzcgMjYuNDc3IDI0IDI5LjYgMjRMNTAuNCAyNEM1My41MjMgMjQgNTYgMjYuNDc3IDU2IDI5LjZMNjQgNTguNEM2NCA2MS41MjMgNjEuNTIzIDY0IDU4LjQgNjRMMjkuNiA2NFoiIGZpbGw9IiM2NjYiLz4KPC9zdmc+'";
                previewContainer.appendChild(img);
            });
            
            abrirModal('modalEditar');
        }

        // Preview de imagens no formulário de adicionar
        document.getElementById('imagens')?.addEventListener('change', function(e) {
            // Implementação opcional de preview
        });
    </script>
</body>
</html>