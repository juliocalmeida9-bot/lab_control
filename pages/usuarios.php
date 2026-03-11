<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth(['admin']);

$pageTitle = 'Usuários';
$currentPage = 'usuarios.php';
$usuarios = $conn->query("SELECT nome, id_acesso, perfil, turma FROM usuarios ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Usuários</h1></section>
<div class="table-card">
<table>
<thead><tr><th>Nome</th><th>ID acesso</th><th>Perfil</th><th>Turma</th></tr></thead>
<tbody><?php foreach ($usuarios as $u): ?><tr><td><?php echo htmlspecialchars($u['nome']); ?></td><td><?php echo htmlspecialchars($u['id_acesso']); ?></td><td><?php echo htmlspecialchars($u['perfil']); ?></td><td><?php echo htmlspecialchars($u['turma'] ?? '-'); ?></td></tr><?php endforeach; ?></tbody>
</table>
</div>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
