<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $novoLogin = trim($_POST['id_acesso'] ?? '');
    $novaSenha = trim($_POST['senha'] ?? '');

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET email = :email WHERE id = :id");
        $stmt->bindValue(':email', $email !== '' ? $email : null, $email !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':id', $usuarioId);
        $stmt->execute();

        if ($novoLogin !== '') {
            $check = $conn->prepare("SELECT id FROM usuarios WHERE id_acesso = :id_acesso AND id != :id");
            $check->bindParam(':id_acesso', $novoLogin);
            $check->bindParam(':id', $usuarioId);
            $check->execute();

            if ($check->fetch(PDO::FETCH_ASSOC)) {
                $erro = 'Este login já está em uso.';
            } else {
                $upLogin = $conn->prepare("UPDATE usuarios SET id_acesso = :id_acesso WHERE id = :id");
                $upLogin->bindParam(':id_acesso', $novoLogin);
                $upLogin->bindParam(':id', $usuarioId);
                $upLogin->execute();
            }
        }

        if ($erro === '' && $novaSenha !== '') {
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $upSenha = $conn->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
            $upSenha->bindParam(':senha', $hash);
            $upSenha->bindParam(':id', $usuarioId);
            $upSenha->execute();
        }

        if (isset($_FILES['foto']) && (int) $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ((int) $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $erro = 'Não foi possível enviar a foto.';
            } else {
                $tmp = $_FILES['foto']['tmp_name'];
                $mime = mime_content_type($tmp);
                $permitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($permitidos[$mime])) {
                    $erro = 'Formato inválido. Use JPG, PNG ou WEBP.';
                } elseif ((int) $_FILES['foto']['size'] > (2 * 1024 * 1024)) {
                    $erro = 'A foto deve ter no máximo 2MB.';
                } else {
                    $dir = __DIR__ . '/../imagens/perfis';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }

                    $ext = $permitidos[$mime];
                    $nomeArquivo = 'perfil_' . $usuarioId . '_' . time() . '.' . $ext;
                    $destino = $dir . '/' . $nomeArquivo;

                    if (!move_uploaded_file($tmp, $destino)) {
                        $erro = 'Não foi possível salvar a foto.';
                    } else {
                        $caminhoRelativo = '../imagens/perfis/' . $nomeArquivo;
                        $upFoto = $conn->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
                        $upFoto->bindParam(':foto', $caminhoRelativo);
                        $upFoto->bindParam(':id', $usuarioId);
                        $upFoto->execute();
                    }
                }
            }
        }

        if ($erro === '') {
            $msg = 'Perfil atualizado com sucesso.';
            log_event($conn, 'perfil_atualizado', $usuarioId, 'Atualização de e-mail/foto');
        }
    }
}

$dados = $conn->prepare("SELECT nome, id_acesso, email, perfil, foto_perfil FROM usuarios WHERE id = :id");
$dados->bindParam(':id', $usuarioId);
$dados->execute();
$usuario = $dados->fetch(PDO::FETCH_ASSOC);

$fotoPerfil = trim((string) ($usuario['foto_perfil'] ?? ''));
$iniciais = strtoupper(substr((string) ($usuario['nome'] ?? 'U'), 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Meu Perfil', 'perfil'); ?>
<main class="page-wrap">
    <section class="card profile-card">
        <div class="profile-head">
            <?php if ($fotoPerfil !== ''): ?>
                <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar placeholder"><?php echo htmlspecialchars($iniciais); ?></div>
            <?php endif; ?>
            <div>
                <h2><?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?></h2>
                <p><?php echo htmlspecialchars($usuario['perfil'] ?? 'usuário'); ?> · <?php echo htmlspecialchars($usuario['id_acesso'] ?? ''); ?></p>
            </div>
        </div>

        <?php if ($msg): ?><p class="success-message"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
        <?php if ($erro): ?><p class="erro"><?php echo htmlspecialchars($erro); ?></p><?php endif; ?>

        <form method="POST" class="grid-form profile-form" enctype="multipart/form-data">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars((string) ($usuario['email'] ?? '')); ?>" placeholder="seuemail@exemplo.com">

            <label for="id_acesso">Login</label>
            <input type="text" id="id_acesso" name="id_acesso" value="<?php echo htmlspecialchars((string) ($usuario['id_acesso'] ?? '')); ?>" placeholder="Seu login de acesso">

            <label for="senha">Nova senha</label>
            <input type="password" id="senha" name="senha" placeholder="Preencha apenas se quiser alterar">

            <label for="foto">Foto de perfil</label>
            <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp">

            <button type="submit" class="btn">Salvar perfil</button>
        </form>
    </section>
</main>
</body>
</html>
