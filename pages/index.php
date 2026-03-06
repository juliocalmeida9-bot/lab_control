<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if (isset($_SESSION['usuario_id'])) {
    if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
        header('Location: admin.php');
        exit();
    }

    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Control Lab - Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo">
        <h1>CONTROL LAB</h1>
        <p>Sistema de Gestão de Equipamentos SENAI</p>

        <form action="login.php" method="POST">
            <input type="text" name="id_acesso" placeholder="ID de Acesso" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <p class="helper-text">Login administrador padrão: <strong>admin</strong> / <strong>admin123</strong></p>

        <?php if (isset($_GET['erro'])): ?>
            <p class="erro">ID ou senha inválidos.</p>
        <?php endif; ?>

        <?php if (isset($_GET['bloqueado'])): ?>
            <p class="erro">Usuário bloqueado por tentativas inválidas.</p>
        <?php endif; ?>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>
