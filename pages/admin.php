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
        $turma = trim($_POST['turma'] ?? '');

        if ($nome !== '' && $acesso !== '' && $senha !== '') {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $perfil = 'usuario';
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

    if ($acao === 'reset_senha') {
        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $novaSenha = $_POST['nova_senha'] ?? '';
        if ($usuarioId > 0 && $novaSenha !== '') {
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET senha = :senha, bloqueado = 0, tentativas = 0 WHERE id = :id');
            $stmt->bindParam(':senha', $hash);
            $stmt->bindParam(':id', $usuarioId);
            $stmt->execute();
            log_event($conn, 'senha_resetada', $adminId, 'Usuário ID ' . $usuarioId);
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
$emprestimos = $conn->query("SELECT e.id, e.responsavel_nome, e.turma, e.data_retirada, e.status, eq.codigo_equipamento
                             FROM emprestimos e
                             JOIN equipamentos eq ON eq.id = e.equipamento_id
                             ORDER BY e.id DESC
                             LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$eventos = $conn->query("SELECT h.created_at, h.acao, h.detalhes, u.nome
                         FROM historico h
                         LEFT JOIN usuarios u ON u.id = h.usuario_id
                         ORDER BY h.id DESC
                         LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php render_app_header('Painel Administrativo', 'admin'); ?>
<main class="page-wrap">
    <section class="card">
        <h2>Gestão de usuários</h2>
        <div class="split-grid">
            <form method="POST">
                <input type="hidden" name="acao" value="novo_usuario">
                <h3>Novo usuário (perfil padrão: usuário)</h3>
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="id_acesso" placeholder="ID de acesso" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <input type="text" name="turma" placeholder="Turma (opcional)">
                <button class="btn" type="submit">Cadastrar usuário</button>
            </form>

            <form method="POST">
                <input type="hidden" name="acao" value="reset_senha">
                <h3>Resetar senha</h3>
                <select name="usuario_id" required>
                    <option value="">Selecione usuário</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo (int) $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?> (<?php echo htmlspecialchars($usuario['id_acesso']); ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="password" name="nova_senha" placeholder="Nova senha" required>
                <button class="btn" type="submit">Atualizar senha</button>
            </form>
        </div>

        <table class="tabela compact">
            <thead><tr><th>ID</th><th>Nome</th><th>Acesso</th><th>Perfil</th><th>Turma</th><th>Bloqueado</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo (int) $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['id_acesso']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['perfil'] ?? 'usuario'); ?></td>
                    <td><?php echo htmlspecialchars($usuario['turma'] ?: '-'); ?></td>
                    <td><?php echo (int) $usuario['bloqueado'] ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Últimos empréstimos</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Responsável</th><th>Turma</th><th>Equipamento</th><th>Data</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($emprestimos as $emprestimo): ?>
                <tr>
                    <td><?php echo (int) $emprestimo['id']; ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['responsavel_nome']); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['turma'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['data_retirada']); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['status']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="acao" value="excluir_emprestimo">
                            <input type="hidden" name="emprestimo_id" value="<?php echo (int) $emprestimo['id']; ?>">
                            <button class="btn danger" type="submit">Excluir</button>
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
            <tbody>
            <?php foreach ($eventos as $ev): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ev['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($ev['acao']); ?></td>
                    <td><?php echo htmlspecialchars($ev['nome'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($ev['detalhes'] ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
