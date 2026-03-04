<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

// Se já estiver logado, vai para dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
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
        <h1>CONTROL LAB</h1>
        <p>Sistema de Gestão de Equipamentos</p>

        <form action="login.php" method="POST">
            <input type="text" name="id_acesso" placeholder="ID de Acesso" required>
            <input type="password" name="senha" placeholder="Chave Secreta" required>
            <button type="submit">Entrar</button>
        </form>

        <?php
        if (isset($_GET['erro'])) {
            echo "<p class='erro'>ID ou senha inválidos!</p>";
        }

        if (isset($_GET['bloqueado'])) {
            echo "<p class='erro'>Usuário bloqueado por tentativas inválidas.</p>";
        }
        ?>
    </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>