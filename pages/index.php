<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if (isset($_SESSION['usuario_id'])) {
    header('Location: home.php');
    exit();
}
$adminLoginSalvo = $_COOKIE['admin_login'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CONTROL LAB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="login-page">
<button class="icon-action login-theme-toggle" id="loginThemeToggle" aria-label="Alternar tema"><i class="bi bi-moon-stars"></i></button>

<div class="login-wrapper">
    <section class="card login-card">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="login-logo">
        <h1>CONTROL LAB</h1>
        <p>Sistema administrativo de controle de equipamentos</p>

        <form action="login.php" method="POST" class="login-form">
            <input type="text" name="id_acesso" placeholder="Usuário" value="<?php echo htmlspecialchars($adminLoginSalvo); ?>" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="text" name="turma" placeholder="Turma (apenas professor)">
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>

        <?php if (isset($_GET['erro']) && $_GET['erro'] !== 'turma'): ?><p>Credenciais inválidas.</p><?php endif; ?>
        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'turma'): ?><p>Professor: selecione uma turma válida para entrar.</p><?php endif; ?>
        <?php if (isset($_GET['bloqueado'])): ?><p>Usuário bloqueado.</p><?php endif; ?>
    </section>

    <section class="card">
        <h2>Sobre o Control Lab</h2>
        <p>O CONTROL LAB é o sistema institucional do SENAI para controlar equipamentos, empréstimos, disponibilidade e histórico dos laboratórios, com rastreabilidade e gestão centralizada.</p>
    </section>

    <section class="card">
        <h2>Contato</h2>
        <p>Email: suporte@senai.com.br</p>
    </section>
</div>

<script src="../js/main.js"></script>
</body>
</html>
