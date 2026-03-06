<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_login();

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['usuario_nome'];

$abertoSql = "SELECT id FROM registros WHERE equipe_id = :equipe_id AND data_devolucao IS NULL LIMIT 1";
$abertoStmt = $conn->prepare($abertoSql);
$abertoStmt->bindParam(':equipe_id', $usuario_id);
$abertoStmt->execute();
$registroAberto = $abertoStmt->fetch(PDO::FETCH_ASSOC);

$equipStmt = $conn->query("SELECT id_equipamento, patrimonio, tipo, status FROM equipamento WHERE status = 'Disponivel' ORDER BY tipo, id_equipamento");
$equipamentos = $equipStmt->fetchAll(PDO::FETCH_ASSOC);

$porTipo = ['Notebook' => [], 'Mouse' => [], 'Carregador' => []];
foreach ($equipamentos as $equipamento) {
    $porTipo[$equipamento['tipo']][] = $equipamento;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Retirada - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB</h1>
    </div>
    <nav class="main-menu">
        <a href="dashboard.php">Início</a>
        <a href="retirada.php" class="active">Retirada</a>
        <a href="devolucao.php">Devolução</a>
        <a href="historico.php">Histórico</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="form-container stretch">
    <div class="card wide">
        <h2>Checklist de Retirada</h2>
        <?php if ($registroAberto): ?>
            <p class="erro">Você já possui uma retirada em aberto. Faça a devolução antes de uma nova retirada.</p>
        <?php elseif (count($equipamentos) === 0): ?>
            <p>Nenhum equipamento disponível no momento.</p>
        <?php else: ?>
            <form action="processar_retirada.php" method="POST">
                <?php foreach ($porTipo as $tipo => $lista): ?>
                    <label for="eq_<?php echo strtolower($tipo); ?>">ID de <?php echo $tipo; ?>:</label>
                    <select id="eq_<?php echo strtolower($tipo); ?>" name="equipamentos[]" required>
                        <option value="">Selecione um <?php echo strtolower($tipo); ?></option>
                        <?php foreach ($lista as $item): ?>
                            <option value="<?php echo (int) $item['id_equipamento']; ?>">
                                ID <?php echo (int) $item['id_equipamento']; ?> - Patrimônio <?php echo htmlspecialchars($item['patrimonio']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>

                <label><input type="checkbox" name="notebook" required> Notebook presente e funcionando</label>
                <label><input type="checkbox" name="mouse" required> Mouse presente e funcionando</label>
                <label><input type="checkbox" name="carregador" required> Carregador presente e funcionando</label>

                <button type="submit" class="btn">Confirmar Retirada</button>
            </form>
        <?php endif; ?>
    </div>
</main>
<script src="../js/main.js"></script>
</body>
</html>
