<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
    header('Location: admin.php');
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
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB</h1>
    </div>
    <nav class="main-menu">
        <a href="dashboard.php">Início</a>
        <a href="retirada.php">Retirada</a>
        <a href="devolucao.php">Devolução</a>
        <a href="historico.php">Histórico</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($nome); ?></span>
        <a href="logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<main class="dashboard full">
    <div class="card">
        <h2>Retirada de Equipamentos</h2>
        <p>Selecione os equipamentos por ID e confirme o checklist.</p>
        <a href="retirada.php" class="btn">Registrar Retirada</a>
    </div>

    <div class="card">
        <h2>Devolução</h2>
        <p>Finalize os empréstimos em aberto e descreva danos se houver.</p>
        <a href="devolucao.php" class="btn">Registrar Devolução</a>
    </div>

    <div class="card">
        <h2>Histórico</h2>
        <p>Acompanhe o uso de notebooks, mouses e carregadores por equipe.</p>
        <a href="historico.php" class="btn">Ver Histórico</a>
    </div>

    <div class="card">
        <h2>Relatórios</h2>
        <p>Visualize indicadores de uso e equipamentos em manutenção.</p>
        <a href="relatorios.php" class="btn">Ver Relatórios</a>
    </div>
</main>

<script src="../js/main.js"></script>
</body>
</html>
