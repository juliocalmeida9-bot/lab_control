<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$nome = trim($_POST['nome'] ?? '');
$id_acesso = trim($_POST['id_acesso'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil = $_POST['perfil'] ?? '';
$turma = trim($_POST['turma'] ?? '');
$equipe = trim($_POST['equipe'] ?? '');
$chaveProfessor = trim($_POST['chave_professor'] ?? '');

if ($nome && $id_acesso && $senha && $perfil && $turma && $equipe) {
    // Verificar se já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE id_acesso = :id_acesso");
    $check->bindParam(':id_acesso', $id_acesso);
    $check->execute();
    if ($check->fetch()) {
        header('Location: index.php?cadastro_erro=1#cadastro');
        exit();
    }

    $perfil = strtolower($perfil);
    if (!in_array($perfil, ['aluno', 'professor'], true)) {
        header('Location: index.php?cadastro_erro=1#cadastro');
        exit();
    }

    if ($perfil === 'professor' && $chaveProfessor !== professor_access_key()) {
        header('Location: index.php?cadastro_erro=chave#cadastro');
        exit();
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil, turma, equipe) VALUES (:nome, :id_acesso, :senha, :perfil, :turma, :equipe)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':id_acesso', $id_acesso);
    $stmt->bindParam(':senha', $hash);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->bindParam(':turma', $turma);
    $stmt->bindParam(':equipe', $equipe);
    $stmt->execute();

    log_event($conn, 'usuario_auto_cadastro', null, $id_acesso . ' - ' . $perfil . ' - ' . $turma . ' - ' . $equipe);

    header('Location: index.php?cadastro_sucesso=1#login');
    exit();
}

header('Location: index.php?cadastro_erro=1#cadastro');
exit();
?>
