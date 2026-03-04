<?php
session_start();

// Se não estiver logado, volta para login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$nome = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Control Lab - Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="topbar">
    <h1>CONTROL LAB</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($nome); ?></span>
        <a href="logout.php" class="logout-btn">Sair</a>
        <button id="toggleTheme" class="btn">Alternar Tema</button>
    </div>
</header>

<main class="dashboard">

    <div class="card">
        <h2>Retirada de Equipamentos</h2>
        <p>Registrar retirada com checklist obrigatório.</p>
<a href="retirada.php" class="btn">Registrar Retirada</a>    </div>

    <div class="card">
        <h2>Devolução de Equipamentos</h2>
        <p>Registrar devolução e possíveis danos.</p>
        <a href="devolucao.php" class="btn">Registrar Devolução</a>
    </div>

    <div class="card">
        <h2>Histórico</h2>
        <p>Consultar registros anteriores da equipe.</p>
        <a href="historico.php" class="btn">Ver Histórico</a>
    </div>

    <div class="card">
        <h2>Relatórios</h2>
        <p>Relatórios gerais para Scrum Master.</p>
        <a href="#" class="btn">Ver Relatórios</a>
    </div>

</main>

<script src="../js/main.js"></script>
</body>
</html>