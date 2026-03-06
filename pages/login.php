<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

 codex/improve-product-removal-features-dz7tx5
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$id = trim($_POST['id_acesso'] ?? '');
$senha = $_POST['senha'] ?? '';

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_acesso = :id_acesso");
$stmt->bindParam(':id_acesso', $id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    if ($usuario) {
        $tentativas = ((int) $usuario['tentativas']) + 1;
        $bloqueado = $tentativas >= 3 ? 1 : 0;
        $upd = $conn->prepare("UPDATE usuarios SET tentativas = :tentativas, bloqueado = :bloqueado WHERE id = :id");
        $upd->bindParam(':tentativas', $tentativas);
        $upd->bindParam(':bloqueado', $bloqueado);
        $upd->bindParam(':id', $usuario['id']);
        $upd->execute();
    }
    header('Location: index.php?erro=1');
    exit();
}

if ((int) $usuario['bloqueado'] === 1) {
    header('Location: index.php?bloqueado=1');
    exit();
}

$reset = $conn->prepare("UPDATE usuarios SET tentativas = 0 WHERE id = :id");
$reset->bindParam(':id', $usuario['id']);
$reset->execute();

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_perfil'] = $usuario['perfil'];

log_event($conn, 'login', (int) $usuario['id'], 'Acesso ao sistema');

header('Location: dashboard.php');
exit();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_acesso = trim($_POST['id_acesso']);
    $senha = $_POST['senha'];

    $sql = 'SELECT * FROM usuarios WHERE id_acesso = :id_acesso';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_acesso', $id_acesso);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: index.php?erro=1');
        exit();
    }

    if ((int) $usuario['bloqueado'] === 1) {
        header('Location: index.php?bloqueado=1');
        exit();
    }

    if (password_verify($senha, $usuario['senha'])) {
        $reset = $conn->prepare('UPDATE usuarios SET tentativas = 0 WHERE id = :id');
        $reset->bindParam(':id', $usuario['id']);
        $reset->execute();

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_perfil'] = $usuario['perfil'] ?? 'usuario';

        if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
            header('Location: admin.php');
            exit();
        }

        header('Location: dashboard.php');
        exit();
    }

    $tentativas = ((int) $usuario['tentativas']) + 1;

    if ($tentativas >= 3) {
        $bloquear = $conn->prepare('UPDATE usuarios SET tentativas = :tentativas, bloqueado = 1 WHERE id = :id');
        $bloquear->bindParam(':tentativas', $tentativas);
        $bloquear->bindParam(':id', $usuario['id']);
        $bloquear->execute();
        header('Location: index.php?bloqueado=1');
        exit();
    }

    $update = $conn->prepare('UPDATE usuarios SET tentativas = :tentativas WHERE id = :id');
    $update->bindParam(':tentativas', $tentativas);
    $update->bindParam(':id', $usuario['id']);
    $update->execute();
    header('Location: index.php?erro=1');
    exit();
}
 main
