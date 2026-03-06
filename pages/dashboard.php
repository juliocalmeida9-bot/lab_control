<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
    header('Location: admin.php');
    exit();
}

$totalEquip = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
$emUso = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Em uso'")->fetchColumn();
$disponiveis = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$ultimos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, eq.codigo_equipamento
                        FROM emprestimos e
                        JOIN equipamentos eq ON eq.id = e.equipamento_id
                        ORDER BY e.data_retirada DESC
                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
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
</main>
</body>
</html>
