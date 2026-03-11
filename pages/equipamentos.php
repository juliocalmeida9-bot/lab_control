<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Equipamentos';
$currentPage = 'equipamentos.php';
$lista = $conn->query("SELECT codigo_equipamento, nome, tipo, localizacao, status FROM equipamentos ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Equipamentos</h1></section>
<div class="table-card">
<table>
<thead><tr><th>Código</th><th>Nome</th><th>Tipo</th><th>Localização</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($lista as $eq): ?>
<tr>
<td><?php echo htmlspecialchars($eq['codigo_equipamento']); ?></td>
<td><?php echo htmlspecialchars($eq['nome']); ?></td>
<td><?php echo htmlspecialchars($eq['tipo']); ?></td>
<td><?php echo htmlspecialchars($eq['localizacao']); ?></td>
<td><?php echo htmlspecialchars($eq['status']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
