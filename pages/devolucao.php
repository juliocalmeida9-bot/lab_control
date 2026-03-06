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

require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];

$sql = "SELECT r.*, GROUP_CONCAT(CONCAT(e.tipo, ' #', e.id_equipamento) SEPARATOR ', ') AS itens
        FROM registros r
        LEFT JOIN registro_itens ri ON ri.registro_id = r.id
        LEFT JOIN equipamento e ON e.id_equipamento = ri.equipamento_id
        WHERE r.equipe_id = :equipe_id AND r.data_devolucao IS NULL
        GROUP BY r.id
        ORDER BY r.data_retirada DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':equipe_id', $usuario_id);
$stmt->execute();
$registro = $stmt->fetch(PDO::FETCH_ASSOC);
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

<header class="topbar">
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB</h1>
    </div>
    <nav class="main-menu">
        <a href="dashboard.php">Início</a>
        <a href="retirada.php">Retirada</a>
        <a href="devolucao.php" class="active">Devolução</a>
        <a href="historico.php">Histórico</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="form-container stretch">
    <div class="card wide">
        <h2>Registrar Devolução</h2>

        <?php if ($registro): ?>
            <form action="processar_devolucao.php" method="POST">
                <p><strong>Retirado em:</strong> <?php echo htmlspecialchars($registro['data_retirada']); ?></p>
                <p><strong>Equipamentos:</strong> <?php echo htmlspecialchars($registro['itens'] ?: '-'); ?></p>

                <label for="danos">Danos/observações (opcional):</label>
                <textarea name="danos" id="danos" rows="4"></textarea>

                <button type="submit" class="btn">Confirmar Devolução</button>
            </form>
        <?php else: ?>
            <p>Nenhum equipamento em uso para devolução.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>


