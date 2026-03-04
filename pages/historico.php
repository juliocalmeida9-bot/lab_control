<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];

// Buscar registros da equipe logada
$sql = "SELECT * FROM registros 
        WHERE equipe_id = :equipe_id 
        ORDER BY data_retirada DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":equipe_id", $usuario_id);
$stmt->execute();

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="topbar">
    <h1>CONTROL LAB</h1>
    <div class="user-info">
        <span><?php echo htmlspecialchars($nome); ?></span>
        <a href="dashboard.php" class="logout-btn">Voltar</a>
    </div>
</header>

<main class="form-container">
    <div class="card">
        <h2>Histórico de Utilização</h2>

        <?php if (count($registros) > 0): ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Data Retirada</th>
                        <th>Data Devolução</th>
                        <th>Status</th>
                        <th>Danos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?php echo $registro['data_retirada']; ?></td>
                            <td>
                                <?php 
                                    echo $registro['data_devolucao'] 
                                    ? $registro['data_devolucao'] 
                                    : "Em Uso";
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $registro['data_devolucao'] 
                                    ? "Finalizado" 
                                    : "Em Uso";
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $registro['danos'] 
                                    ? htmlspecialchars($registro['danos']) 
                                    : "-";
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado.</p>
        <?php endif; ?>

    </div>
</main>

<script src="../js/main.js"></script>
</body>
</html>