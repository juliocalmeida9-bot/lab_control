<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Turmas';
$currentPage = 'turmas.php';
$turmas = $conn->query("SELECT turma, COUNT(*) total FROM usuarios WHERE turma IS NOT NULL AND turma <> '' GROUP BY turma ORDER BY turma")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>Turmas</h1>
    <p>Visão das turmas vinculadas ao sistema.</p>
</section>

<section class="card table-card">
    <div class="table-wrap"><table>
        <thead><tr><th>Turma</th><th>Usuários vinculados</th></tr></thead>
        <tbody>
        <?php foreach ($turmas as $turma): ?>
            <tr><td><?php echo htmlspecialchars($turma['turma']); ?></td><td><?php echo (int) $turma['total']; ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
