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
$turmaSelecionada = trim($_POST['turma'] ?? '');

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


if (profile_is_professor($usuario['perfil'] ?? '')) {
    $turmasProfessor = array_filter(array_map('trim', explode(',', (string) ($usuario['turma'] ?? ''))));
    if (!$turmaSelecionada || !in_array($turmaSelecionada, $turmasProfessor, true)) {
        header('Location: index.php?erro=turma');
        exit();
    }
}

$reset = $conn->prepare("UPDATE usuarios SET tentativas = 0 WHERE id = :id");
$reset->bindParam(':id', $usuario['id']);
$reset->execute();

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_perfil'] = $usuario['perfil'];
$_SESSION['usuario_turma'] = $turmaSelecionada !== '' ? $turmaSelecionada : ($usuario['turma'] ?? '');

if (profile_is_professor($usuario['perfil'] ?? '')) {
    $registroSessao = $conn->prepare("INSERT INTO professor_sessoes (professor_id, turma_selecionada, created_at) VALUES (:professor_id, :turma_selecionada, :created_at)");
    $now = date('Y-m-d H:i:s');
    $registroSessao->bindParam(':professor_id', $usuario['id']);
    $registroSessao->bindParam(':turma_selecionada', $_SESSION['usuario_turma']);
    $registroSessao->bindParam(':created_at', $now);
    $registroSessao->execute();
}

if (($usuario['perfil'] ?? 'usuario') === 'admin') {
    $_SESSION['admin_login_salvo'] = $usuario['id_acesso'];
    setcookie('admin_login', $usuario['id_acesso'], time() + (60 * 60 * 24 * 30), '/');
} else {
    setcookie('admin_login', '', time() - 3600, '/');
}

log_event($conn, 'login', (int) $usuario['id'], 'Acesso ao sistema');

if (($usuario['perfil'] ?? 'usuario') === 'admin') {
    header('Location: home.php');
    exit();
}

if (profile_is_professor($usuario['perfil'] ?? '')) {
    header('Location: home.php');
    exit();
}

header('Location: home.php');
exit();
