<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Perfil';
$currentPage = 'perfil.php';
$nome = $_SESSION['usuario_nome'] ?? 'Usuário';

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>Perfil do usuário</h1>
    <p>Dados do usuário e opções de segurança.</p>
</section>

<section class="profile-grid">
    <article class="card">
        <div class="profile-photo"><?php echo strtoupper(substr($nome, 0, 1)); ?></div>
        <h3><?php echo htmlspecialchars($nome); ?></h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['usuario_id'] ?? '-'); ?></p>
        <p><strong>Cargo:</strong> <?php echo htmlspecialchars(ucfirst($_SESSION['usuario_perfil'] ?? '-')); ?></p>
        <p><strong>Turma:</strong> <?php echo htmlspecialchars($_SESSION['usuario_turma'] ?? '-'); ?></p>
    </article>
    <article class="card">
        <h3>Ações da conta</h3>
        <p>Atualize suas informações e mantenha sua conta segura.</p>
        <p><button class="btn btn-primary" type="button">Editar perfil</button> <button class="btn btn-secondary" type="button">Alterar senha</button></p>
    </article>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
