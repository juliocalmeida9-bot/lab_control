<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];

// buscar o último registro aberto (sem data_devolucao)
$sql = "SELECT * FROM registros 
        WHERE equipe_id = :equipe_id 
          AND data_devolucao IS NULL 
        ORDER BY data_retirada DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":equipe_id", $usuario_id);
$stmt->execute();
$registro = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Devolução - Control Lab</title>
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
        <h2>Registrar Devolução</h2>

        <?php if ($registro): ?>
            <form action="processar_devolucao.php" method="POST">
                <p>Retirado em: <?php echo $registro['data_retirada']; ?></p>

                <label for="danos">Danos (opcional):</label>
                <textarea name="danos" id="danos" rows="4"></textarea>

                <button type="submit" class="btn">Confirmar Devolução</button>
            </form>
        <?php else: ?>
            <p>Nenhum equipamento em uso para devolução.</p>
        <?php endif; ?>
    </div>
</main>

<script src="../js/main.js"></script>
</body>
</html>