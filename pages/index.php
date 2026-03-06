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
$logoControl = logo_control_lab_path();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Control Lab - Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="home-nav">
    <a href="#login">Login</a>
    <a href="#about">Sobre</a>
    <a href="#contact">Contato</a>
</nav>
<div class="login-container">
    <div class="login-card" id="login">
        <div class="login-logos">
            <img src="../imagens/logo-senai.png" alt="SENAI" class="logo senai">
            <?php if ($logoControl): ?><img src="<?php echo htmlspecialchars($logoControl); ?>" class="logo control" alt="Control Lab"><?php else: ?><span class="control-badge">CONTROL LAB</span><?php endif; ?>
        </div>
        <h1>CONTROL LAB</h1>
        <p>Sistema administrativo de controle de equipamentos</p>
        <form action="login.php" method="POST">
            <input type="text" name="id_acesso" placeholder="Login" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <p class="helper-text">Admin padrão: <strong>admin</strong> / <strong>admin123</strong></p>
        <?php if (isset($_GET['erro'])): ?><p class="erro">Credenciais inválidas.</p><?php endif; ?>
        <?php if (isset($_GET['bloqueado'])): ?><p class="erro">Usuário bloqueado.</p><?php endif; ?>
    </div>
</div>

<section class="summary" id="about">
    <h2>Sobre o Control Lab</h2>
    <p>O Control Lab é um sistema desenvolvido pelo SENAI para gerenciamento eficiente de equipamentos em laboratórios. Permite o controle de empréstimos, devoluções e manutenção de itens, garantindo organização e rastreabilidade. Desenvolvido com tecnologias modernas para facilitar o dia a dia dos usuários e administradores.</p>
</section>

<section class="summary" id="contact">
    <h2>Contato</h2>
    <p>Para dúvidas ou suporte, entre em contato com a equipe do SENAI através do email: suporte@senai.com.br</p>
</section>

<script src="../js/main.js"></script>
</body>
</html>


