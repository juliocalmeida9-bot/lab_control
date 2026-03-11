<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Dashboard';
$currentPage = 'home.php';

$totalEquip = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
$emprestados = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Emprestado' OR status = 'Em uso'")->fetchColumn();
$disponiveis = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$ativos = (int) $conn->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'Em uso' OR status = 'Ativo'")->fetchColumn();

$ultimos = $conn->query("SELECT e.responsavel_nome AS aluno, eq.nome AS equipamento, e.data_retirada, e.data_devolucao, e.status
    FROM emprestimos e
    JOIN equipamentos eq ON eq.id = e.equipamento_id
    ORDER BY e.id DESC
    LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

function status_class(string $status): string {
    $s = mb_strtolower($status);
    if (str_contains($s, 'devolvido')) return 'status-ok';
    if (str_contains($s, 'atras')) return 'status-danger';
    return 'status-warn';
}

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>Dashboard</h1>
    <p>Visão geral do laboratório e dos empréstimos ativos.</p>
</section>

<section class="summary-grid">
    <article class="card summary-card"><div><small>Total de equipamentos</small><div class="metric"><?php echo $totalEquip; ?></div></div><div class="icon"><i class="bi bi-hdd-stack"></i></div></article>
    <article class="card summary-card"><div><small>Equipamentos emprestados</small><div class="metric"><?php echo $emprestados; ?></div></div><div class="icon"><i class="bi bi-box-arrow-up-right"></i></div></article>
    <article class="card summary-card"><div><small>Equipamentos disponíveis</small><div class="metric"><?php echo $disponiveis; ?></div></div><div class="icon"><i class="bi bi-check-circle"></i></div></article>
    <article class="card summary-card"><div><small>Empréstimos ativos</small><div class="metric"><?php echo $ativos; ?></div></div><div class="icon"><i class="bi bi-clock-history"></i></div></article>
</section>

<section class="card table-card">
    <h3>Últimos empréstimos</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Aluno</th><th>Equipamento</th><th>Data empréstimo</th><th>Data devolução</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['aluno']); ?></td>
                    <td><?php echo htmlspecialchars($item['equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_retirada']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_devolucao'] ?: '-'); ?></td>
                    <td><span class="status-pill <?php echo status_class((string) $item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
