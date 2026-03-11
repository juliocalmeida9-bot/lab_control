<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Home';
$currentPage = 'home.php';

$totalEquip = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
$disp = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$ativos = (int) $conn->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'Em uso'")->fetchColumn();
$usuarios = (int) $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>CONTROL LAB</h1>
    <p>Sistema de gerenciamento de equipamentos de laboratório.</p>
</section>
<section class="summary-grid">
    <article class="summary-card"><h3>Total de equipamentos</h3><p><?php echo $totalEquip; ?></p></article>
    <article class="summary-card"><h3>Equipamentos disponíveis</h3><p><?php echo $disp; ?></p></article>
    <article class="summary-card"><h3>Empréstimos ativos</h3><p><?php echo $ativos; ?></p></article>
    <article class="summary-card"><h3>Usuários cadastrados</h3><p><?php echo $usuarios; ?></p></article>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
