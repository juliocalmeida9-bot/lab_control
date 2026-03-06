<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_admin();

$adminId = (int) $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'novo_usuario') {
        $nome = trim($_POST['nome'] ?? '');
        $acesso = trim($_POST['id_acesso'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $perfil = $_POST['perfil'] === 'admin' ? 'admin' : 'usuario';
        $turma = trim($_POST['turma'] ?? '');

        if ($nome && $acesso && $senha) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil, turma) VALUES (:nome, :acesso, :senha, :perfil, :turma)");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':acesso', $acesso);
            $stmt->bindParam(':senha', $hash);
            $stmt->bindParam(':perfil', $perfil);
            $stmt->bindParam(':turma', $turma);
            $stmt->execute();
            log_event($conn, 'usuario_cadastrado', $adminId, $acesso);
        }
    }

    if ($acao === 'excluir_emprestimo') {
        $id = (int) ($_POST['emprestimo_id'] ?? 0);
        if ($id > 0) {
            $conn->beginTransaction();
            $ref = $conn->prepare("SELECT equipamento_id, status FROM emprestimos WHERE id = :id FOR UPDATE");
            $ref->bindParam(':id', $id);
            $ref->execute();
            $emp = $ref->fetch(PDO::FETCH_ASSOC);
            if ($emp) {
                $delDev = $conn->prepare("DELETE FROM devolucoes WHERE emprestimo_id = :id");
                $delDev->bindParam(':id', $id);
                $delDev->execute();
                $delEmp = $conn->prepare("DELETE FROM emprestimos WHERE id = :id");
                $delEmp->bindParam(':id', $id);
                $delEmp->execute();
                if ($emp['status'] === 'Em uso') {
                    $up = $conn->prepare("UPDATE equipamentos SET status = 'Disponível' WHERE id = :id");
                    $up->bindParam(':id', $emp['equipamento_id']);
                    $up->execute();
                }
                log_event($conn, 'emprestimo_excluido', $adminId, 'ID ' . $id);
            }
            $conn->commit();
        }
    }

    header('Location: admin.php');
    exit();
}

$usuarios = $conn->query("SELECT id, nome, id_acesso, perfil, turma, bloqueado FROM usuarios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$emUso = $conn->query("SELECT e.id, e.responsavel_nome, eq.codigo_equipamento
                       FROM emprestimos e JOIN equipamentos eq ON eq.id = e.equipamento_id
                       WHERE e.status = 'Em uso'")->fetchAll(PDO::FETCH_ASSOC);
$emprestimos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, e.status, eq.codigo_equipamento
                             FROM emprestimos e JOIN equipamentos eq ON eq.id = e.equipamento_id
                             ORDER BY e.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$eventos = $conn->query("SELECT h.created_at, h.acao, h.detalhes, u.nome
                         FROM historico h LEFT JOIN usuarios u ON u.id = h.usuario_id
                         ORDER BY h.id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Painel de Administração', 'admin'); ?>
<main class="page-wrap">
    <section class="kpi-grid">
        <article class="card"><h2>Assistente: equipamentos em uso</h2><p class="metric"><?php echo count($emUso); ?></p><a class="btn" href="devolucao.php">Registrar devolução rápida</a></article>
        <article class="card"><h2>Assistente: relatórios</h2><p>Acesso rápido à exportação de planilha</p><a class="btn" href="relatorios.php">Abrir relatórios</a></article>
    </section>

    <section class="card">
        <h2>Gerenciar usuários</h2>
        <form method="POST" class="grid-form two-cols">
            <input type="hidden" name="acao" value="novo_usuario">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="id_acesso" placeholder="Login" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="text" name="turma" placeholder="Turma (opcional)">
            <select name="perfil"><option value="usuario">Usuário</option><option value="admin">Administrador</option></select>
            <button class="btn" type="submit">Cadastrar usuário</button>
        </form>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Nome</th><th>Login</th><th>Perfil</th><th>Turma</th></tr></thead>
            <tbody><?php foreach ($usuarios as $u): ?><tr><td><?php echo (int) $u['id']; ?></td><td><?php echo htmlspecialchars($u['nome']); ?></td><td><?php echo htmlspecialchars($u['id_acesso']); ?></td><td><?php echo htmlspecialchars($u['perfil']); ?></td><td><?php echo htmlspecialchars($u['turma'] ?: '-'); ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </section>

    <section class="card">
        <h2>Editar/remover registros de empréstimo</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Responsável</th><th>Equipamento</th><th>Retirada</th><th>Status</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($emprestimos as $emp): ?>
                <tr>
                    <td><?php echo (int) $emp['id']; ?></td>
                    <td><?php echo htmlspecialchars($emp['responsavel_nome'] . ' - ' . $emp['turma']); ?></td>
                    <td><?php echo htmlspecialchars($emp['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($emp['data_retirada']); ?></td>
                    <td><?php echo htmlspecialchars($emp['status']); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remover empréstimo?');">
                            <input type="hidden" name="acao" value="excluir_emprestimo">
                            <input type="hidden" name="emprestimo_id" value="<?php echo (int) $emp['id']; ?>">
                            <button class="btn danger" type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Histórico de movimentações</h2>
        <table class="tabela">
            <thead><tr><th>Quando</th><th>Ação</th><th>Usuário</th><th>Detalhes</th></tr></thead>
            <tbody><?php foreach ($eventos as $ev): ?><tr><td><?php echo htmlspecialchars($ev['created_at']); ?></td><td><?php echo htmlspecialchars($ev['acao']); ?></td><td><?php echo htmlspecialchars($ev['nome'] ?: '-'); ?></td><td><?php echo htmlspecialchars($ev['detalhes'] ?: '-'); ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </section>
</main>
</body>
</html>
