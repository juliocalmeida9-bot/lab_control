<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];

$sql = "SELECT r.*, GROUP_CONCAT(CONCAT(e.tipo, ' #', e.id_equipamento) SEPARATOR ', ') AS itens
        FROM registros r
        LEFT JOIN registro_itens ri ON ri.registro_id = r.id
        LEFT JOIN equipamento e ON e.id_equipamento = ri.equipamento_id
        WHERE r.equipe_id = :equipe_id AND r.data_devolucao IS NULL
        GROUP BY r.id
        ORDER BY r.data_retirada DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':equipe_id', $usuario_id);
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
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB</h1>
    </div>
    <nav class="main-menu">
        <a href="dashboard.php">Início</a>
        <a href="retirada.php">Retirada</a>
        <a href="devolucao.php" class="active">Devolução</a>
        <a href="historico.php">Histórico</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="form-container stretch">
    <div class="card wide">
        <h2>Registrar Devolução</h2>

        <?php if ($registro): ?>
            <form action="processar_devolucao.php" method="POST">
                <p><strong>Retirado em:</strong> <?php echo htmlspecialchars($registro['data_retirada']); ?></p>
                <p><strong>Equipamentos:</strong> <?php echo htmlspecialchars($registro['itens'] ?: '-'); ?></p>

                <label for="danos">Danos/observações (opcional):</label>
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
