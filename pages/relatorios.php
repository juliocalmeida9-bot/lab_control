<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$nome = $_SESSION['usuario_nome'];

$totalEmprestimos = (int) $conn->query('SELECT COUNT(*) FROM registros')->fetchColumn();
$emAndamento = (int) $conn->query('SELECT COUNT(*) FROM registros WHERE data_devolucao IS NULL')->fetchColumn();
$totalDanificados = (int) $conn->query("SELECT COUNT(*) FROM equipamento WHERE estado = 'Danificado'")->fetchColumn();

$topUsuarios = $conn->query("SELECT u.nome, COUNT(r.id) AS total
                             FROM usuarios u
                             LEFT JOIN registros r ON r.equipe_id = u.id
                             GROUP BY u.id
                             ORDER BY total DESC
                             LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios - Control Lab</title>
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
        <a href="historico.php">Histórico</a>
        <a href="relatorios.php" class="active">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="dashboard full">
    <section class="card">
        <h2>Total de empréstimos</h2>
        <p class="metric"><?php echo $totalEmprestimos; ?></p>
    </section>
    <section class="card">
        <h2>Em andamento</h2>
        <p class="metric"><?php echo $emAndamento; ?></p>
    </section>
    <section class="card">
        <h2>Equipamentos danificados</h2>
        <p class="metric"><?php echo $totalDanificados; ?></p>
    </section>
    <section class="card wide-grid">
        <h2>Usuários com mais empréstimos</h2>
        <table class="tabela compact">
            <thead><tr><th>Usuário</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($topUsuarios as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nome'] ?: 'Sem nome'); ?></td>
                    <td><?php echo (int) $item['total']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<script src="../js/main.js"></script>
</body>
</html>
