<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_admin();

$eventos = $conn->query("SELECT h.created_at, h.acao, h.detalhes, u.nome
                         FROM historico h
                         LEFT JOIN usuarios u ON u.id = h.usuario_id
                         ORDER BY h.id DESC
                         LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Eventos - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Histórico de Eventos', 'relatorios'); ?>
<main class="page-wrap">
    <section class="card">
        <h2>Log de ações administrativas e de uso</h2>
        <table class="tabela">
            <thead><tr><th>Quando</th><th>Ação</th><th>Usuário</th><th>Detalhes</th></tr></thead>
            <tbody>
            <?php foreach ($eventos as $evento): ?>
                <tr>
                    <td><?php echo htmlspecialchars($evento['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($evento['acao']); ?></td>
                    <td><?php echo htmlspecialchars($evento['nome'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($evento['detalhes'] ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
