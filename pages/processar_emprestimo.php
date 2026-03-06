<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: emprestimos.php');
    exit();
}

$usuarioId = (int) $_SESSION['usuario_id'];
$responsavel = trim($_POST['responsavel_nome'] ?? '');
$turma = trim($_POST['turma'] ?? '');
$equipamentoId = (int) ($_POST['equipamento_id'] ?? 0);

if ($responsavel === '' || $turma === '' || $equipamentoId <= 0) {
    header('Location: emprestimos.php?erro=campos');
    exit();
}

try {
    $conn->beginTransaction();

    $eqStmt = $conn->prepare("SELECT id, codigo_equipamento, status FROM equipamentos WHERE id = :id FOR UPDATE");
    $eqStmt->bindParam(':id', $equipamentoId);
    $eqStmt->execute();
    $equipamento = $eqStmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipamento || $equipamento['status'] !== 'Disponível') {
        throw new RuntimeException('Equipamento indisponível.');
    }

    $data = date('Y-m-d H:i:s');
    $insert = $conn->prepare("INSERT INTO emprestimos (usuario_id, responsavel_nome, turma, equipamento_id, data_retirada, status)
                              VALUES (:usuario_id, :responsavel_nome, :turma, :equipamento_id, :data_retirada, 'Em uso')");
    $insert->bindParam(':usuario_id', $usuarioId);
    $insert->bindParam(':responsavel_nome', $responsavel);
    $insert->bindParam(':turma', $turma);
    $insert->bindParam(':equipamento_id', $equipamentoId);
    $insert->bindParam(':data_retirada', $data);
    $insert->execute();

    $upd = $conn->prepare("UPDATE equipamentos SET status = 'Em uso' WHERE id = :id");
    $upd->bindParam(':id', $equipamentoId);
    $upd->execute();

    log_event($conn, 'emprestimo_registrado', $usuarioId, 'Equipamento: ' . $equipamento['codigo_equipamento']);

    $conn->commit();
    header('Location: emprestimos.php?ok=1');
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: emprestimos.php?erro=processamento');
    exit();
}
