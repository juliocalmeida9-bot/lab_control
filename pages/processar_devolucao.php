<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: devolucao.php');
    exit();
}

$danos = trim($_POST['danos'] ?? '');
$data_devolucao = date('Y-m-d H:i:s');

try {
    $conn->beginTransaction();

    $sql = "SELECT id FROM registros
            WHERE equipe_id = :equipe_id
              AND data_devolucao IS NULL
            ORDER BY data_retirada DESC LIMIT 1 FOR UPDATE";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':equipe_id', $usuario_id);
    $stmt->execute();
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        throw new RuntimeException('Sem empréstimo aberto.');
    }

    $update = $conn->prepare('UPDATE registros SET data_devolucao = :data, danos = :danos WHERE id = :id');
    $update->bindParam(':data', $data_devolucao);
    $update->bindParam(':danos', $danos);
    $update->bindParam(':id', $registro['id']);
    $update->execute();

    $equipamentos = $conn->prepare('SELECT equipamento_id FROM registro_itens WHERE registro_id = :registro_id');
    $equipamentos->bindParam(':registro_id', $registro['id']);
    $equipamentos->execute();
    $itens = $equipamentos->fetchAll(PDO::FETCH_COLUMN);

    $updEq = $conn->prepare("UPDATE equipamento SET status = 'Disponivel', estado = :estado WHERE id_equipamento = :id");
    $estado = $danos ? 'Danificado' : 'Bom';
    foreach ($itens as $idEquipamento) {
        $updEq->bindParam(':estado', $estado);
        $updEq->bindParam(':id', $idEquipamento);
        $updEq->execute();
    }

    $conn->commit();
    header('Location: dashboard.php?ok=devolucao');
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: devolucao.php?erro=processamento');
    exit();
}
