<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $danos = trim($_POST['danos']);
    $data_devolucao = date("Y-m-d H:i:s");

    // encontrar registro aberto
    $sql = "SELECT id FROM registros 
            WHERE equipe_id = :equipe_id 
              AND data_devolucao IS NULL 
            ORDER BY data_retirada DESC LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":equipe_id", $usuario_id);
    $stmt->execute();
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        $update = $conn->prepare("UPDATE registros 
                                  SET data_devolucao = :data, danos = :danos 
                                  WHERE id = :id");
        $update->bindParam(":data", $data_devolucao);
        $update->bindParam(":danos", $danos);
        $update->bindParam(":id", $registro['id']);
        $update->execute();

        // atualizar equipamentos
        $updEq = $conn->prepare("UPDATE equipamentos 
                                  SET status = 'Disponível' 
                                  WHERE equipe_id = :equipe_id");
        $updEq->bindParam(":equipe_id", $usuario_id);
        $updEq->execute();
    }

    header("Location: dashboard.php");
    exit();
}
?>