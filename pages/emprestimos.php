<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Empréstimos';
$currentPage = 'emprestimos.php';

$historico = $conn->query("SELECT e.id, e.responsavel_nome AS aluno, eq.nome AS equipamento, e.turma, e.data_retirada, e.data_devolucao, e.status
    FROM emprestimos e
    JOIN equipamentos eq ON eq.id = e.equipamento_id
    ORDER BY e.id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="row-between page-heading">
    <div><h1>Controle de Empréstimos</h1><p>Registro e acompanhamento de retiradas e devoluções.</p></div>
    <a href="retirada.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
</section>

<section class="card" style="margin-top:16px;">
    <h3>Novo empréstimo</h3>
    <form class="controls" action="processar_emprestimo.php" method="POST">
        <input name="aluno" placeholder="Aluno">
        <input name="equipamento" placeholder="Equipamento">
        <input name="professor" placeholder="Professor responsável">
        <input name="turma" placeholder="Turma">
        <input name="data_emprestimo" type="date">
        <input name="data_devolucao_prevista" type="date">
        <button class="btn btn-secondary" type="submit">Registrar</button>
    </form>
</section>

<section class="card table-card">
    <h3>Histórico de empréstimos</h3>
    <div class="table-wrap"><table>
        <thead><tr><th>Aluno</th><th>Equipamento</th><th>Turma</th><th>Data retirada</th><th>Data devolução</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($historico as $h): ?>
            <tr>
                <td><?php echo htmlspecialchars($h['aluno']); ?></td>
                <td><?php echo htmlspecialchars($h['equipamento']); ?></td>
                <td><?php echo htmlspecialchars($h['turma']); ?></td>
                <td><?php echo htmlspecialchars($h['data_retirada']); ?></td>
                <td><?php echo htmlspecialchars($h['data_devolucao'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($h['status']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
