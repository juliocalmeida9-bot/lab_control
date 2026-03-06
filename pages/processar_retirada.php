<?php
header('Location: processar_emprestimo.php');
exit();

session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: retirada.php');
    exit();
}

$equipamentos = $_POST['equipamentos'] ?? [];
$equipamentos = array_map('intval', $equipamentos);
$equipamentos = array_values(array_unique($equipamentos));

if (count($equipamentos) !== 3 || empty($_POST['notebook']) || empty($_POST['mouse']) || empty($_POST['carregador'])) {
    header('Location: retirada.php?erro=checklist');
    exit();
}

$abertoStmt = $conn->prepare('SELECT id FROM registros WHERE equipe_id = :equipe AND data_devolucao IS NULL LIMIT 1');
$abertoStmt->bindParam(':equipe', $usuario_id);
$abertoStmt->execute();
if ($abertoStmt->fetch(PDO::FETCH_ASSOC)) {
    header('Location: retirada.php?erro=aberto');
    exit();
}

try {
    $conn->beginTransaction();

    $placeholders = implode(',', array_fill(0, count($equipamentos), '?'));
    $checkSql = "SELECT id_equipamento, tipo FROM equipamento WHERE id_equipamento IN ($placeholders) AND status = 'Disponivel' FOR UPDATE";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute($equipamentos);
    $rows = $checkStmt->fetchAll(PDO::FETCH_ASSOC);

    $tipos = array_column($rows, 'tipo');
    sort($tipos);
    if (count($rows) !== 3 || $tipos !== ['Carregador', 'Mouse', 'Notebook']) {
        throw new RuntimeException('Selecione um equipamento válido de cada tipo.');
    }

    $data_retirada = date('Y-m-d H:i:s');
    $regStmt = $conn->prepare('INSERT INTO registros (equipe_id, data_retirada) VALUES (:equipe_id, :data_retirada)');
    $regStmt->bindParam(':equipe_id', $usuario_id);
    $regStmt->bindParam(':data_retirada', $data_retirada);
    $regStmt->execute();

    $registroId = (int) $conn->lastInsertId();

    $itemStmt = $conn->prepare('INSERT INTO registro_itens (registro_id, equipamento_id) VALUES (:registro_id, :equipamento_id)');
    $updStmt = $conn->prepare("UPDATE equipamento SET status = 'Em Uso' WHERE id_equipamento = :id");

    foreach ($equipamentos as $idEquipamento) {
        $itemStmt->bindParam(':registro_id', $registroId);
        $itemStmt->bindParam(':equipamento_id', $idEquipamento);
        $itemStmt->execute();

        $updStmt->bindParam(':id', $idEquipamento);
        $updStmt->execute();
    }

    $conn->commit();
    header('Location: dashboard.php?ok=retirada');
    exit();
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: retirada.php?erro=processamento');
    exit();
}


