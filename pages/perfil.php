<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Perfil';
$currentPage = 'perfil.php';

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Perfil</h1></section>
<div class="summary-card">
    <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></p>
    <p><strong>Perfil:</strong> <?php echo htmlspecialchars($_SESSION['usuario_perfil'] ?? ''); ?></p>
    <p><strong>Turma:</strong> <?php echo htmlspecialchars($_SESSION['usuario_turma'] ?? '-'); ?></p>
</div>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
