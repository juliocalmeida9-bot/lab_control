<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
require_once(__DIR__ . '/../components/cards/cards.php');
require_once(__DIR__ . '/../components/tables/tables.php');
ensure_schema($conn);
require_login();

$searchQuery = trim($_GET['q'] ?? '');
$searchTipo = $_GET['tipo'] ?? 'equipamento';
$searchResults = [];

if ($searchQuery !== '') {
    if ($searchTipo === 'equipamento') {
        $q = '%' . str_replace('%','\\%', $searchQuery) . '%';
        $stmt = $conn->prepare("SELECT id, codigo_equipamento AS item, nome AS detalhe, status FROM equipamentos WHERE codigo_equipamento LIKE :q OR nome LIKE :q ORDER BY id DESC LIMIT 50");
        $stmt->execute([':q' => $q]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $perfil = $searchTipo === 'aluno' ? 'aluno' : 'professor';
        $q = '%' . str_replace('%','\\%', $searchQuery) . '%';
        $stmt = $conn->prepare("SELECT id, nome AS item, id_acesso AS detalhe, turma FROM usuarios WHERE perfil = :perfil AND (nome LIKE :q OR id_acesso LIKE :q) ORDER BY id DESC LIMIT 50");
        $stmt->execute([':perfil' => $perfil, ':q' => $q]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
    header('Location: admin.php');
    exit();
}
if (profile_is_professor($_SESSION['usuario_perfil'] ?? '')) {
    header('Location: professor.php');
    exit();
}

$totalEquip = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
$emUso = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Em uso'")->fetchColumn();
$disponiveis = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$ultimos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, eq.codigo_equipamento
                        FROM emprestimos e
                        JOIN equipamentos eq ON eq.id = e.equipamento_id
                        ORDER BY e.data_retirada DESC
                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Dashboard', 'dashboard'); ?>
<main class="page-wrap">
    <section class="kpi-grid">
        <?php render_stat_card('Total de equipamentos', $totalEquip, 'bi-hdd-rack'); ?>
        <?php render_stat_card('Equipamentos emprestados', $emUso, 'bi-arrow-repeat'); ?>
        <?php render_stat_card('Equipamentos disponíveis', $disponiveis, 'bi-check-circle'); ?>
        <?php render_stat_card('Usuários cadastrados', (int) $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(), 'bi-people-fill'); ?>
    </section>

    <section class="card">
        <h2>Pesquisar</h2>
        <form class="inline-form" method="GET" action="dashboard.php" style="margin-bottom:12px;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Pesquisar...">
            <select name="tipo">
                <option value="equipamento" <?php echo $searchTipo==='equipamento' ? 'selected' : ''; ?>>Equipamento</option>
                <option value="aluno" <?php echo $searchTipo==='aluno' ? 'selected' : ''; ?>>Aluno</option>
                <option value="professor" <?php echo $searchTipo==='professor' ? 'selected' : ''; ?>>Professor</option>
            </select>
            <button type="submit" class="btn">Buscar</button>
        </form>
        <?php if ($searchQuery !== ''): ?>
            <p class="helper-text">Resultados para "<?php echo htmlspecialchars($searchQuery); ?>" em <?php echo htmlspecialchars($searchTipo); ?>.</p>
            <?php if (count($searchResults) > 0): ?>
                <?php render_table_tools("Buscar resultados..."); ?><div class="tabela-wrap"><table class="tabela compact data-table">
                    <thead><tr><th>ID</th><th>Item</th><th>Detalhe</th><th>Turma/Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($searchResults as $item): ?>
                        <tr>
                            <td><?php echo (int) $item['id']; ?></td>
                            <td><?php echo htmlspecialchars($item['item']); ?></td>
                            <td><?php echo htmlspecialchars($item['detalhe']); ?></td>
                            <td><?php echo htmlspecialchars($item['turma'] ?? $item['status'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php else: ?>
                <p class="helper-text">Nenhum resultado encontrado.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Últimos empréstimos realizados</h2>
        <?php render_table_tools("Buscar empréstimos..."); ?><div class="tabela-wrap"><table class="tabela data-table">
            <thead><tr><th>ID</th><th>Responsável</th><th>Turma</th><th>Equipamento</th><th>Data de retirada</th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $item): ?>
                <tr>
                    <td>#<?php echo (int) $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['responsavel_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['turma']); ?></td>
                    <td><?php echo htmlspecialchars($item['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($item['data_retirada']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </section>
</main>
</body>
</html>
