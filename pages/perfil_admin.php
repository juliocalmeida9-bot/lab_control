<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_admin();

$adminId = (int) $_SESSION['usuario_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoLogin = trim($_POST['id_acesso'] ?? '');
    $novaSenha = trim($_POST['senha'] ?? '');

    if ($novoLogin !== '') {
        $stmt = $conn->prepare("UPDATE usuarios SET id_acesso = :id_acesso WHERE id = :id");
        $stmt->bindParam(':id_acesso', $novoLogin);
        $stmt->bindParam(':id', $adminId);
        $stmt->execute();
        $_SESSION['usuario_login'] = $novoLogin;
        $msg = 'Login atualizado. ';
        log_event($conn, 'admin_login_alterado', $adminId, $novoLogin);
    }

    if ($novaSenha !== '') {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':id', $adminId);
        $stmt->execute();
        $msg .= 'Senha atualizada.';
        log_event($conn, 'admin_senha_alterada', $adminId, 'Alteração de senha');
    }
}

$dados = $conn->prepare("SELECT nome, id_acesso FROM usuarios WHERE id = :id");
$dados->bindParam(':id', $adminId);
$dados->execute();
$admin = $dados->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Perfil do Administrador', 'perfil'); ?>
<main class="page-wrap">
    <section class="card narrow">
        <h2>Atualizar acesso do administrador</h2>
        <?php if ($msg): ?><p class="success-message"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
        <form method="POST" class="grid-form">
            <input type="text" name="id_acesso" value="<?php echo htmlspecialchars($admin['id_acesso']); ?>" placeholder="Novo login">
            <input type="password" name="senha" placeholder="Nova senha">
            <button type="submit" class="btn">Salvar alterações</button>
        </form>
    </section>
</main>
</body>
</html>
