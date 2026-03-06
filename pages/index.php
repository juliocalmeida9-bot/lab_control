<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if (isset($_SESSION['usuario_id'])) {
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
<div class="login-container">
    <div class="login-card">
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
</body>
</html>
