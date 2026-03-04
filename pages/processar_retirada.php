<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data_retirada = date("Y-m-d H:i:s");

    // Inserir registro
    $sql = "INSERT INTO registros (equipe_id, data_retirada)
            VALUES (:equipe_id, :data_retirada)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":equipe_id", $usuario_id);
    $stmt->bindParam(":data_retirada", $data_retirada);
    $stmt->execute();

    // Atualizar status equipamentos
    $update = $conn->prepare("UPDATE equipamentos 
                              SET status = 'Em Uso' 
                              WHERE equipe_id = :equipe_id");

    $update->bindParam(":equipe_id", $usuario_id);
    $update->execute();

    header("Location: dashboard.php");
    exit();
}
?>