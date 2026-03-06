<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo_equipamento'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $status = trim($_POST['status'] ?? 'Disponível');
    $obs = trim($_POST['observacoes'] ?? '');

    if ($codigo && $nome && $tipo && $localizacao) {
        $stmt = $conn->prepare("INSERT INTO equipamentos (codigo_equipamento, nome, tipo, localizacao, status, observacoes)
                               VALUES (:codigo, :nome, :tipo, :localizacao, :status, :obs)");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':obs', $obs);
        $stmt->execute();
        log_event($conn, 'equipamento_cadastrado', (int) $_SESSION['usuario_id'], 'ID: ' . $codigo);
    }
    header('Location: equipamentos.php');
    exit();
}

$lista = $conn->query("SELECT * FROM equipamentos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Equipamentos - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Cadastro de Equipamentos', 'equipamentos'); ?>
<main class="page-wrap">
    <section class="card">
        <h2>Novo equipamento</h2>
        <form method="POST" class="grid-form two-cols">
            <input type="text" name="codigo_equipamento" placeholder="ID do equipamento (ex: NB-001)" required>
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="tipo" placeholder="Tipo (Notebook, Mouse...)" required>
            <input type="text" name="localizacao" placeholder="Localização (Laboratório TI, Almoxarifado...)" required>
            <select name="status">
                <option>Disponível</option>
                <option>Em uso</option>
                <option>Manutenção</option>
            </select>
            <input type="text" name="observacoes" placeholder="Observações">
            <button class="btn" type="submit">Cadastrar equipamento</button>
        </form>
    </section>

    <section class="card">
        <h2>Inventário</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Código</th><th>Nome</th><th>Tipo</th><th>Localização</th><th>Status</th><th>Obs.</th></tr></thead>
            <tbody>
            <?php foreach ($lista as $eq): ?>
                <tr>
                    <td><?php echo (int) $eq['id']; ?></td>
                    <td><?php echo htmlspecialchars($eq['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($eq['nome']); ?></td>
                    <td><?php echo htmlspecialchars($eq['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($eq['localizacao']); ?></td>
                    <td><?php echo htmlspecialchars($eq['status']); ?></td>
                    <td><?php echo htmlspecialchars($eq['observacoes'] ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
