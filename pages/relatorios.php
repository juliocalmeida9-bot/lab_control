<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth(['admin']);

$pageTitle = 'Relatórios';
$currentPage = 'relatorios.php';
$porStatus = $conn->query("SELECT status, COUNT(*) AS total FROM equipamentos GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Relatórios</h1></section>
<section class="summary-grid">
<?php foreach ($porStatus as $item): ?>
<article class="summary-card"><h3><?php echo htmlspecialchars($item['status']); ?></h3><p><?php echo (int) $item['total']; ?></p></article>
<?php endforeach; ?>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
