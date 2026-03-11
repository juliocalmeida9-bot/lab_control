<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
require_once(__DIR__ . '/../components/tables/tables.php');
ensure_schema($conn);
require_login();

$usuarioId = (int) $_SESSION['usuario_id'];
$nomeDefault = $_SESSION['usuario_nome'] ?? '';
$turmaDefault = '';

$perfilUsuario = $_SESSION['usuario_perfil'] ?? 'usuario';
$turmaSessao = trim((string) ($_SESSION['usuario_turma'] ?? ''));
$isProfessor = profile_is_professor($perfilUsuario);

$stmt = $conn->prepare("SELECT turma FROM emprestimos WHERE usuario_id = :id ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $usuarioId);
$stmt->execute();
$turmaDefault = $stmt->fetchColumn() ?: '';

if ($isProfessor) {
    $turmaDefault = $turmaSessao;

    $eqStmt = $conn->prepare("SELECT id, codigo_equipamento, nome, tipo, localizacao, status
                              FROM equipamentos
                              WHERE status = 'Disponível' AND localizacao = :turma
                              ORDER BY tipo, codigo_equipamento");
    $eqStmt->bindParam(':turma', $turmaSessao);
    $eqStmt->execute();
    $equipamentos = $eqStmt->fetchAll(PDO::FETCH_ASSOC);

    $recStmt = $conn->prepare("SELECT e.id, e.data_retirada, u.nome as usuario, eq.codigo_equipamento, eq.tipo
                               FROM emprestimos e
                               JOIN usuarios u ON u.id = e.usuario_id
                               JOIN equipamentos eq ON eq.id = e.equipamento_id
                               WHERE e.turma = :turma
                               ORDER BY e.data_retirada DESC
                               LIMIT 8");
    $recStmt->bindParam(':turma', $turmaSessao);
    $recStmt->execute();
    $recentes = $recStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $equipamentos = $conn->query("SELECT id, codigo_equipamento, nome, tipo, localizacao, status FROM equipamentos WHERE status = 'Disponível' ORDER BY tipo, codigo_equipamento")->fetchAll(PDO::FETCH_ASSOC);

    $recentes = $conn->query("SELECT e.id, e.data_retirada, u.nome as usuario, eq.codigo_equipamento, eq.tipo
                              FROM emprestimos e
                              JOIN usuarios u ON u.id = e.usuario_id
                              JOIN equipamentos eq ON eq.id = e.equipamento_id
                              ORDER BY e.data_retirada DESC
                              LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Empréstimos - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Gestão de Empréstimos', 'emprestimos'); ?>
<main class="page-wrap">
    <section class="kpi-grid">
        <article class="card"><h2><i class="bi bi-journal-plus"></i> Registrar empréstimo</h2><p class="helper-text">Formalize a retirada de equipamento em segundos.</p></article>
        <article class="card"><h2><i class="bi bi-journal-check"></i> Registrar devolução</h2><a class="btn" href="devolucao.php">Ir para devoluções</a></article>
        <article class="card"><h2><i class="bi bi-clock-history"></i> Histórico</h2><a class="btn secondary" href="historico.php">Ver histórico</a></article>
    </section>
    <section class="card">
        <h2>Novo empréstimo</h2>
        <form method="POST" action="processar_emprestimo.php" class="grid-form">
            <input type="text" name="responsavel_nome" value="<?php echo htmlspecialchars($nomeDefault); ?>" placeholder="Nome da equipe/aluno" required>
            <input type="text" name="turma" value="<?php echo htmlspecialchars($turmaDefault); ?>" placeholder="Turma" <?php echo $isProfessor ? 'readonly' : ''; ?> required>
            <select name="equipamento_id" required>
                <option value="">Selecione o equipamento por ID</option>
                <?php foreach ($equipamentos as $eq): ?>
                    <option value="<?php echo (int) $eq['id']; ?>">
                        <?php echo htmlspecialchars($eq['codigo_equipamento']); ?> - <?php echo htmlspecialchars($eq['tipo']); ?> - <?php echo htmlspecialchars($eq['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Registrar empréstimo</button>
        </form>
    </section>

    <section class="card">
        <h2>Últimos empréstimos</h2>
        <?php render_table_tools("Buscar empréstimos por responsável, item ou data..."); ?><div class="tabela-wrap"><table class="tabela data-table">
            <thead><tr><th>ID</th><th>Responsável</th><th>Equipamento</th><th>Tipo</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($recentes as $item): ?>
                <tr>
                    <td><?php echo (int) $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($item['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_retirada']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </section>
</main>
</body>
</html>


