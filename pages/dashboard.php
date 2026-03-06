<?php
session_start();
 codex/improve-product-removal-features-dz7tx5
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

$totalEquip = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
$emUso = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Em uso'")->fetchColumn();
$disponiveis = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$ultimos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, eq.codigo_equipamento
                        FROM emprestimos e
                        JOIN equipamentos eq ON eq.id = e.equipamento_id
                        ORDER BY e.data_retirada DESC
                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
    header('Location: admin.php');
    exit();
}

$nome = $_SESSION['usuario_nome'];
 main
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
 codex/improve-product-removal-features-dz7tx5
<?php render_app_header('Dashboard', 'dashboard'); ?>
<main class="page-wrap">
    <section class="kpi-grid">
        <article class="card"><h2>Total de equipamentos</h2><p class="metric"><?php echo $totalEquip; ?></p></article>
        <article class="card"><h2>Equipamentos em uso</h2><p class="metric"><?php echo $emUso; ?></p></article>
        <article class="card"><h2>Equipamentos disponíveis</h2><p class="metric"><?php echo $disponiveis; ?></p></article>
    </section>

    <section class="card">
        <h2>Últimos empréstimos realizados</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Responsável</th><th>Turma</th><th>Equipamento</th><th>Data de retirada</th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $item): ?>
                <tr>
                    <td>#<?php echo (int) $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['responsavel_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['turma']); ?></td>
                    <td><?php echo htmlspecialchars($item['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_retirada']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

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
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($nome); ?></span>
        <a href="logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<main class="dashboard full">
    <div class="card">
        <h2>Retirada de Equipamentos</h2>
        <p>Selecione os equipamentos por ID e confirme o checklist.</p>
        <a href="retirada.php" class="btn">Registrar Retirada</a>
    </div>

    <div class="card">
        <h2>Devolução</h2>
        <p>Finalize os empréstimos em aberto e descreva danos se houver.</p>
        <a href="devolucao.php" class="btn">Registrar Devolução</a>
    </div>

    <div class="card">
        <h2>Histórico</h2>
        <p>Acompanhe o uso de notebooks, mouses e carregadores por equipe.</p>
        <a href="historico.php" class="btn">Ver Histórico</a>
    </div>

    <div class="card">
        <h2>Relatórios</h2>
        <p>Visualize indicadores de uso e equipamentos em manutenção.</p>
        <a href="relatorios.php" class="btn">Ver Relatórios</a>
    </div>
main
</main>
</body>
</html>
