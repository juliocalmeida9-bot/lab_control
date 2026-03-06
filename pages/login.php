<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

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
