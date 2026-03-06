<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);
require_admin();

$nome = $_SESSION['usuario_nome'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'novo_usuario') {
        $nomeNovo = trim($_POST['nome'] ?? '');
        $idAcesso = trim($_POST['id_acesso'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] === 'admin' ? 'admin' : 'usuario';

        if ($nomeNovo && $idAcesso && $senha) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, id_acesso, senha, perfil) VALUES (:nome, :id_acesso, :senha, :perfil)');
            $stmt->bindParam(':nome', $nomeNovo);
            $stmt->bindParam(':id_acesso', $idAcesso);
            $stmt->bindParam(':senha', $hash);
            $stmt->bindParam(':perfil', $perfil);
            $msg = $stmt->execute() ? 'Usuário cadastrado com sucesso.' : 'Erro ao cadastrar usuário.';
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
            $msg = 'Senha atualizada e usuário desbloqueado.';
        }
    }

    if ($acao === 'novo_equipamento') {
        $patrimonio = trim($_POST['patrimonio'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        if ($patrimonio && in_array($tipo, ['Notebook', 'Mouse', 'Carregador'], true)) {
            $stmt = $conn->prepare("INSERT INTO equipamento (patrimonio, tipo, status, estado) VALUES (:patrimonio, :tipo, 'Disponivel', 'Bom')");
            $stmt->bindParam(':patrimonio', $patrimonio);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->execute();
            $msg = 'Equipamento cadastrado.';
        }
    }
}

$usuarios = $conn->query('SELECT id, nome, id_acesso, perfil, bloqueado FROM usuarios ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$equipamentos = $conn->query('SELECT id_equipamento, patrimonio, tipo, status, estado FROM equipamento ORDER BY id_equipamento DESC')->fetchAll(PDO::FETCH_ASSOC);

$registros = $conn->query("SELECT r.id, r.data_retirada, r.data_devolucao, r.danos, u.nome,
                          GROUP_CONCAT(CONCAT(e.tipo, ' #', e.id_equipamento) SEPARATOR ', ') AS itens
                          FROM registros r
                          LEFT JOIN usuarios u ON u.id = r.equipe_id
                          LEFT JOIN registro_itens ri ON ri.registro_id = r.id
                          LEFT JOIN equipamento e ON e.id_equipamento = ri.equipamento_id
                          GROUP BY r.id
                          ORDER BY r.data_retirada DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrador - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand-block">
        <img src="../imagens/logo-senai.png" alt="Logo SENAI" class="brand-logo small">
        <h1>CONTROL LAB - ADMIN</h1>
    </div>
    <nav class="main-menu">
        <a href="#usuarios">Usuários</a>
        <a href="#equipamentos">Equipamentos</a>
        <a href="#emprestimos">Empréstimos</a>
        <a href="relatorios.php">Relatórios</a>
    </nav>
    <div class="user-info"><span><?php echo htmlspecialchars($nome); ?></span><a href="logout.php" class="logout-btn">Sair</a></div>
</header>

<main class="admin-layout">
    <?php if ($msg): ?><p class="success-message"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>

    <section id="usuarios" class="card">
        <h2>Gestão de usuários</h2>
        <div class="split-grid">
            <form method="POST">
                <input type="hidden" name="acao" value="novo_usuario">
                <h3>Novo usuário</h3>
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="id_acesso" placeholder="ID de acesso" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <select name="perfil">
                    <option value="usuario">Usuário</option>
                    <option value="admin">Administrador</option>
                </select>
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
            <thead><tr><th>ID</th><th>Nome</th><th>Acesso</th><th>Perfil</th><th>Bloqueado</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo (int) $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['id_acesso']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['perfil'] ?? 'usuario'); ?></td>
                    <td><?php echo (int) $usuario['bloqueado'] ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="equipamentos" class="card">
        <h2>Cadastro e controle de equipamentos</h2>
        <form method="POST" class="inline-form">
            <input type="hidden" name="acao" value="novo_equipamento">
            <input type="text" name="patrimonio" placeholder="Patrimônio" required>
            <select name="tipo" required>
                <option value="Notebook">Notebook</option>
                <option value="Mouse">Mouse</option>
                <option value="Carregador">Carregador</option>
            </select>
            <button class="btn" type="submit">Cadastrar equipamento</button>
        </form>
        <table class="tabela compact">
            <thead><tr><th>ID</th><th>Patrimônio</th><th>Tipo</th><th>Status</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($equipamentos as $equipamento): ?>
                <tr>
                    <td><?php echo (int) $equipamento['id_equipamento']; ?></td>
                    <td><?php echo htmlspecialchars($equipamento['patrimonio']); ?></td>
                    <td><?php echo htmlspecialchars($equipamento['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($equipamento['status']); ?></td>
                    <td><?php echo htmlspecialchars($equipamento['estado']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="emprestimos" class="card">
        <h2>Tabela completa de empréstimos</h2>
        <table class="tabela">
            <thead><tr><th>ID</th><th>Usuário</th><th>Retirada</th><th>Devolução</th><th>Equipamentos</th><th>Danos</th></tr></thead>
            <tbody>
            <?php foreach ($registros as $registro): ?>
                <tr>
                    <td><?php echo (int) $registro['id']; ?></td>
                    <td><?php echo htmlspecialchars($registro['nome'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($registro['data_retirada']); ?></td>
                    <td><?php echo htmlspecialchars($registro['data_devolucao'] ?: 'Em uso'); ?></td>
                    <td><?php echo htmlspecialchars($registro['itens'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($registro['danos'] ?: '-'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<script src="../js/main.js"></script>
</body>
</html>
