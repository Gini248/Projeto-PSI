<?php
include_once('../../assets/php/config.php');

 if (isset($_GET['discord']) && !empty($_GET['discord'])) {
     $discord = mysqli_real_escape_string($conexao, $_GET['discord']);

     $sql = "SELECT * FROM cands_anno WHERE discord = '$discord'";
    $result = $conexao->query($sql);

     if ($result->num_rows > 0) {
        $candidato = $result->fetch_assoc();
    } else {
        echo "Candidato não encontrado.";
        exit;
    }
} else {
    echo "Discord inválido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Candidatura</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moon+Dance&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/navbar.css">
    <link rel="stylesheet" href="../../assets/css/ver.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../../assets/img/logo.png" >

</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="assets/html/wiki.html">Wiki</a>
        <a href="assets/html/loja.html">Loja</a>
    </div>
    <a href="../../index.html">
        <div class="logo"><img src="../../assets/img/logo.png" alt="Logo"></div>
    </a>
    <div class="nav-right">
        <a href="assets/html/regras.html">Regras</a>
        <a href="assets/php/cands.php">Candidaturas</a>
    </div>
</nav>

 <div class="detalhes_ooc">
    <h2>Detalhes OOC</h2>
    <p><strong>Discord:</strong> <?= $candidato['discord']; ?></p>
    <p><strong>recomendação:</strong> <?= $recomendacao['recomendacao']; ?></p>
    <p><strong>Nome OOC:</strong> <?= $tipo['tipo_de_pedido']; ?></p>
    <p><strong>Já foi banido:</strong> <?= $detalhes['detalhes']; ?></p>
    <div style="text-align: center;">  </div>
</div>

 <div class="detalhes_ic">
    <h2>Detalhes da Candidatura</h2>
    <p><strong>Nome IC:</strong> <?= $candidato['nome_ic']; ?></p>
    <p><strong>Idade IC:</strong> <?= $candidato['idade_ic']; ?></p>
    <p><strong>Objetivos:</strong> <?= $candidato['objetivos']; ?></p>
    <p><strong>Psicologico:</strong> <?= $candidato['psicologico']; ?></p>
    <p><strong>Pontos (Positivos/Negativos):</strong> <?= $candidato['pontos']; ?></p>
    <div style="text-align: center;">  </div>
</div>

 <div class="historia">
    <h2>História</h2>
    <p> <?= nl2br($candidato['historia']); ?></p>
    <div style="text-align: center;">  </div>
</div>

</body>
</html>
