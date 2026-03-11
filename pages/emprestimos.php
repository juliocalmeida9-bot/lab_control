<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Empréstimos';
$currentPage = 'emprestimos.php';
$role = current_user_role();

if ($role === 'admin') {
    $stmt = $conn->query("SELECT e.id, u.nome AS usuario, eq.nome AS equipamento, e.data_retirada, e.status
        FROM emprestimos e
        JOIN usuarios u ON u.id = e.usuario_id
        JOIN equipamentos eq ON eq.id = e.equipamento_id
        ORDER BY e.id DESC LIMIT 50");
    $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT e.id, eq.nome AS equipamento, e.data_retirada, e.status
        FROM emprestimos e
        JOIN equipamentos eq ON eq.id = e.equipamento_id
        WHERE e.usuario_id = :usuario_id
        ORDER BY e.id DESC LIMIT 50");
    $stmt->execute([':usuario_id' => (int) $_SESSION['usuario_id']]);
    $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Empréstimos</h1></section>
<div class="table-card">
<table>
<thead><tr><th>ID</th><?php if ($role === 'admin'): ?><th>Usuário</th><?php endif; ?><th>Equipamento</th><th>Data</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($emprestimos as $em): ?>
<tr>
<td>#<?php echo (int) $em['id']; ?></td>
<?php if ($role === 'admin'): ?><td><?php echo htmlspecialchars($em['usuario']); ?></td><?php endif; ?>
<td><?php echo htmlspecialchars($em['equipamento']); ?></td>
<td><?php echo htmlspecialchars($em['data_retirada']); ?></td>
<td><?php echo htmlspecialchars($em['status']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
