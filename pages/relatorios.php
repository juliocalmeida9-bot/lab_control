<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
require_once(__DIR__ . '/../components/cards/cards.php');
require_once(__DIR__ . '/../components/tables/tables.php');
ensure_schema($conn);
require_admin();

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

$totalEmprestimos = (int) $conn->query('SELECT COUNT(*) FROM emprestimos')->fetchColumn();
$emAndamento = (int) $conn->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'Em uso'")->fetchColumn();
$totalFinalizados = (int) $conn->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'Finalizado'")->fetchColumn();

$topUsuarios = $conn->query("SELECT u.nome, COUNT(e.id) AS total
                             FROM usuarios u
                             LEFT JOIN emprestimos e ON e.usuario_id = u.id
                             GROUP BY u.id, u.nome
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
<?php render_app_header('Relatórios e Histórico de Empréstimos', 'relatorios'); ?>
<main class="page-wrap">
    <section class="kpi-grid">
        <?php render_stat_card("Total de empréstimos", $totalEmprestimos, "bi-collection"); ?>
        <?php render_stat_card("Em andamento", $emAndamento, "bi-hourglass-split"); ?>
        <?php render_stat_card("Finalizados", $totalFinalizados, "bi-check2-square"); ?>
    </section>

    <section class="card">
        <div class="row-between">
            <h2>Exportações e gráficos</h2>
            <div class="row-between">
                <button class="btn" type="button" data-export="pdf"><i class="bi bi-filetype-pdf"></i> Exportar PDF</button>
                <a class="btn secondary" href="relatorios.php?export=csv"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
            </div>
        </div>
        <p class="helper-text">Painel com indicadores para suporte gerencial e auditoria.</p>
    </section>

    <section class="card">
        <h2>Usuários com mais empréstimos</h2>
        <?php render_table_tools("Buscar usuário..."); ?><div class="tabela-wrap"><table class="tabela compact data-table">
            <thead><tr><th>Usuário</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($topUsuarios as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nome'] ?: 'Sem nome'); ?></td>
                    <td><?php echo (int) $item['total']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </section>

    <section class="card">
        <div class="row-between">
            <h2>Histórico completo de empréstimos</h2>
            <a class="btn" href="relatorios.php?export=csv">Exportar CSV</a>
        </div>
        <?php render_table_tools("Buscar no histórico..."); ?><div class="tabela-wrap"><table class="tabela data-table">
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
        </table></div>
    </section>
</main>
</body>
</html>
