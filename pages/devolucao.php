<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

$abertos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, eq.codigo_equipamento, eq.nome, eq.tipo
                         FROM emprestimos e
                         JOIN equipamentos eq ON eq.id = e.equipamento_id
                         WHERE e.status = 'Em uso'
                         ORDER BY e.data_retirada ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Devoluções - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Gestão de Devoluções', 'devolucoes'); ?>
<main class="page-wrap">
    <section class="card">
        <h2>Registrar devolução por ID do empréstimo</h2>
        <form action="processar_devolucao.php" method="POST" class="grid-form">
            <select name="emprestimo_id" required>
                <option value="">Selecione um empréstimo em uso</option>
                <?php foreach ($abertos as $a): ?>
                    <option value="<?php echo (int) $a['id']; ?>">
                        #<?php echo (int) $a['id']; ?> | <?php echo htmlspecialchars($a['codigo_equipamento']); ?> | <?php echo htmlspecialchars($a['responsavel_nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="observacoes" placeholder="Observações da devolução (opcional)">
            <button class="btn" type="submit">Confirmar devolução</button>
        </form>
    </section>

    <section class="card">
        <h2>Equipamentos em uso</h2>
        <table class="tabela">
            <thead><tr><th>Empréstimo</th><th>Responsável</th><th>Turma</th><th>Equipamento</th><th>Tipo</th><th>Retirada</th></tr></thead>
            <tbody>
            <?php foreach ($abertos as $item): ?>
                <tr>
                    <td>#<?php echo (int) $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['responsavel_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['turma']); ?></td>
                    <td><?php echo htmlspecialchars($item['codigo_equipamento'] . ' - ' . $item['nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_retirada']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
