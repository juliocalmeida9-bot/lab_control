<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

$usuarioId = (int) $_SESSION['usuario_id'];
$nomeDefault = $_SESSION['usuario_nome'] ?? '';
$turmaDefault = '';

$stmt = $conn->prepare("SELECT turma FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $usuarioId);
$stmt->execute();
$turmaDefault = $stmt->fetchColumn() ?: '';

$equipamentos = $conn->query("SELECT id, codigo_equipamento, nome, tipo, localizacao, status FROM equipamentos WHERE status = 'Disponível' ORDER BY tipo, codigo_equipamento")->fetchAll(PDO::FETCH_ASSOC);

$recentes = $conn->query("SELECT e.id, e.data_retirada, u.nome as usuario, eq.codigo_equipamento, eq.tipo
                          FROM emprestimos e
                          JOIN usuarios u ON u.id = e.usuario_id
                          JOIN equipamentos eq ON eq.id = e.equipamento_id
                          ORDER BY e.data_retirada DESC
                          LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
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
    <section class="card">
        <h2>Novo empréstimo</h2>
        <form method="POST" action="processar_emprestimo.php" class="grid-form">
            <input type="text" name="responsavel_nome" value="<?php echo htmlspecialchars($nomeDefault); ?>" placeholder="Nome da equipe/aluno" required>
            <input type="text" name="turma" value="<?php echo htmlspecialchars($turmaDefault); ?>" placeholder="Turma" required>
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
        <table class="tabela">
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
        </table>
    </section>
</main>
</body>
</html>
