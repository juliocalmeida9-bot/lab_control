<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_login();

if (!profile_is_professor($_SESSION['usuario_perfil'] ?? '')) {
    header('Location: dashboard.php?erro=sem_permissao');
    exit();
}

$professorId = (int) $_SESSION['usuario_id'];
$turmaSelecionada = trim((string) ($_SESSION['usuario_turma'] ?? ''));

$equipStmt = $conn->prepare("SELECT id, codigo_equipamento, nome, tipo, localizacao, status
                            FROM equipamentos
                            WHERE localizacao = :turma
                            ORDER BY status, codigo_equipamento");
$equipStmt->bindParam(':turma', $turmaSelecionada);
$equipStmt->execute();
$equipamentos = $equipStmt->fetchAll(PDO::FETCH_ASSOC);

$histStmt = $conn->prepare("SELECT ps.created_at, ps.turma_selecionada
                            FROM professor_sessoes ps
                            WHERE ps.professor_id = :professor_id
                            ORDER BY ps.created_at DESC
                            LIMIT 10");
$histStmt->bindParam(':professor_id', $professorId);
$histStmt->execute();
$acessos = $histStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Professor - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Painel do Professor', 'professor'); ?>
<main class="page-wrap">
    <section class="card">
        <h2>Turma selecionada no login: <?php echo htmlspecialchars($turmaSelecionada ?: '-'); ?></h2>
        <p>Você visualiza apenas equipamentos da sala/turma vinculada ao seu acesso.</p>
    </section>

    <section class="card">
        <h2>Equipamentos da sua sala</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Código</th><th>Nome</th><th>Tipo</th><th>Sala</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($equipamentos as $eq): ?>
                <tr>
                    <td><?php echo (int) $eq['id']; ?></td>
                    <td><?php echo htmlspecialchars($eq['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($eq['nome']); ?></td>
                    <td><?php echo htmlspecialchars($eq['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($eq['localizacao']); ?></td>
                    <td><?php echo htmlspecialchars($eq['status']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Últimos acessos registrados</h2>
        <table class="tabela">
            <thead><tr><th>Data/Hora</th><th>Turma escolhida</th></tr></thead>
            <tbody>
            <?php foreach ($acessos as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($a['turma_selecionada']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
