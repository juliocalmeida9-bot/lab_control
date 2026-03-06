<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: devolucao.php');
    exit();
}

$usuarioId = (int) $_SESSION['usuario_id'];
$emprestimoId = (int) ($_POST['emprestimo_id'] ?? 0);
$observacoes = trim($_POST['observacoes'] ?? '');

if ($emprestimoId <= 0) {
    header('Location: devolucao.php?erro=campos');
    exit();
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT e.id, e.equipamento_id, eq.codigo_equipamento
                            FROM emprestimos e
                            JOIN equipamentos eq ON eq.id = e.equipamento_id
                            WHERE e.id = :id AND e.status = 'Em uso' FOR UPDATE");
    $stmt->bindParam(':id', $emprestimoId);
    $stmt->execute();
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emprestimo) {
        throw new RuntimeException('Empréstimo não encontrado.');
    }

    $data = date('Y-m-d H:i:s');

    $updEmp = $conn->prepare("UPDATE emprestimos SET status = 'Finalizado' WHERE id = :id");
    $updEmp->bindParam(':id', $emprestimoId);
    $updEmp->execute();

    $updEq = $conn->prepare("UPDATE equipamentos SET status = 'Disponível' WHERE id = :id");
    $updEq->bindParam(':id', $emprestimo['equipamento_id']);
    $updEq->execute();

    $insDev = $conn->prepare("INSERT INTO devolucoes (emprestimo_id, data_devolucao, observacoes) VALUES (:emprestimo_id, :data_devolucao, :observacoes)");
    $insDev->bindParam(':emprestimo_id', $emprestimoId);
    $insDev->bindParam(':data_devolucao', $data);
    $insDev->bindParam(':observacoes', $observacoes);
    $insDev->execute();

    log_event($conn, 'devolucao_registrada', $usuarioId, 'Equipamento: ' . $emprestimo['codigo_equipamento']);

    $conn->commit();
    header('Location: devolucao.php?ok=1');
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: devolucao.php?erro=processamento');
    exit();
}
