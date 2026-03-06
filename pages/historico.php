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
        WHERE r.equipe_id = :equipe_id
        GROUP BY r.id
        ORDER BY r.data_retirada DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':equipe_id', $usuario_id);
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
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB</h1>
    </div>
    <nav class="main-menu">
        <a href="dashboard.php">Início</a>
        <a href="retirada.php">Retirada</a>
        <a href="devolucao.php">Devolução</a>
        <a href="historico.php" class="active">Histórico</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="form-container stretch">
    <div class="card wide">
        <h2>Histórico de Utilização</h2>

        <?php if (count($registros) > 0): ?>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Data Retirada</th>
                        <th>Data Devolução</th>
                        <th>Equipamentos (ID)</th>
                        <th>Status</th>
                        <th>Danos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($registro['data_retirada']); ?></td>
                            <td><?php echo $registro['data_devolucao'] ? htmlspecialchars($registro['data_devolucao']) : 'Em Uso'; ?></td>
                            <td><?php echo htmlspecialchars($registro['itens'] ?: '-'); ?></td>
                            <td><?php echo $registro['data_devolucao'] ? 'Finalizado' : 'Em Uso'; ?></td>
                            <td><?php echo $registro['danos'] ? htmlspecialchars($registro['danos']) : '-'; ?></td>
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
