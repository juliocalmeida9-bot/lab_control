<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Retirada - Control Lab</title>
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
        <h2>Checklist de Retirada</h2>

        <form action="processar_retirada.php" method="POST">

            <label>
                <input type="checkbox" name="notebook" required>
                Notebook presente e funcionando
            </label>

            <label>
                <input type="checkbox" name="mouse" required>
                Mouse presente e funcionando
            </label>

            <label>
                <input type="checkbox" name="carregador" required>
                Carregador presente e funcionando
            </label>

            <button type="submit" class="btn">Confirmar Retirada</button>

        </form>
    </div>

</main>

<script src="../js/main.js"></script>
</body>
</html>