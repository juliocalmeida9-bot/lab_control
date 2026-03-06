<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

$sqlBase = "SELECT e.id,
                   e.responsavel_nome,
                   e.turma,
                   eq.codigo_equipamento,
                   eq.nome AS equipamento_nome,
                   e.data_retirada,
                   d.data_devolucao,
                   e.status
            FROM emprestimos e
            JOIN equipamentos eq ON eq.id = e.equipamento_id
            LEFT JOIN devolucoes d ON d.emprestimo_id = e.id
            ORDER BY e.data_retirada DESC";

$rows = $conn->query($sqlBase)->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_control_lab.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID Empréstimo', 'Responsável', 'Turma', 'ID Equipamento', 'Equipamento', 'Data Retirada', 'Data Devolução', 'Status']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['responsavel_nome'], $r['turma'], $r['codigo_equipamento'], $r['equipamento_nome'], $r['data_retirada'], $r['data_devolucao'], $r['status']]);
    }
    fclose($out);
    exit();
}

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
<?php render_app_header('Relatórios e Exportações', 'relatorios'); ?>
<main class="page-wrap">
    <section class="card">
        <div class="row-between">
            <h2>Histórico completo de empréstimos</h2>
            <a class="btn" href="relatorios.php?export=csv">Exportar CSV</a>
        </div>
        <table class="tabela">
            <thead><tr><th>Responsável</th><th>ID Equip.</th><th>Equipamento</th><th>Retirada</th><th>Devolução</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['responsavel_nome'] . ' (' . $r['turma'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($r['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($r['equipamento_nome']); ?></td>
                    <td><?php echo htmlspecialchars($r['data_retirada']); ?></td>
                    <td><?php echo htmlspecialchars($r['data_devolucao'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($r['status']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
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
main
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<script src="../js/main.js"></script>
</body>
</html>


