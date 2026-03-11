<?php
session_start();
require_once(__DIR__ . '/../includes/layout.php');
ensure_schema($conn);
require_admin();

$adminId = (int) $_SESSION['usuario_id'];

$searchQuery = trim($_GET['q'] ?? '');
$searchTipo = $_GET['tipo'] ?? 'equipamento';
$searchResults = [];

if ($searchQuery !== '') {
    if ($searchTipo === 'equipamento') {
        $q = '%' . str_replace('%', '\\%', $searchQuery) . '%';
        $stmt = $conn->prepare("SELECT id, codigo_equipamento AS item, nome AS extra, status AS extra2 FROM equipamentos WHERE codigo_equipamento LIKE :q OR nome LIKE :q ORDER BY id DESC LIMIT 80");
        $stmt->execute([':q' => $q]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $perfil = $searchTipo === 'aluno' ? 'aluno' : 'professor';
        $q = '%' . str_replace('%', '\\%', $searchQuery) . '%';
        $stmt = $conn->prepare("SELECT id, nome AS item, id_acesso AS extra, turma AS extra2 FROM usuarios WHERE perfil = :perfil AND (nome LIKE :q OR id_acesso LIKE :q) ORDER BY id DESC LIMIT 80");
        $stmt->execute([':perfil' => $perfil, ':q' => $q]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Estatísticas para dashboard
$totalEquipamentos = (int) $conn->query('SELECT COUNT(*) FROM equipamentos')->fetchColumn();
$equipamentosDisponiveis = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE status = 'Disponível'")->fetchColumn();
$emprestimosAtivos = (int) $conn->query("SELECT COUNT(*) FROM emprestimos WHERE status = 'Em uso'")->fetchColumn();
$equipamentosDanificados = (int) $conn->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'Danificado'")->fetchColumn();

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

    if ($acao === 'novo_equipamento') {
        $codigo = trim($_POST['codigo_equipamento'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $localizacao = trim($_POST['localizacao'] ?? 'Laboratório TI');

        if ($codigo !== '' && $nome !== '' && $tipo !== '') {
            $stmt = $conn->prepare("INSERT INTO equipamentos (codigo_equipamento, nome, tipo, localizacao, status) VALUES (:codigo, :nome, :tipo, :localizacao, 'Disponível')");
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->execute();
            log_event($conn, 'equipamento_cadastrado', $adminId, $codigo);
        }
    }

    if ($acao === 'atualizar_equipamento') {
        $id = (int) ($_POST['equipamento_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $localizacao = trim($_POST['localizacao'] ?? '');

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE equipamentos SET status = :status, estado = :estado, localizacao = :localizacao WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            log_event($conn, 'equipamento_atualizado', $adminId, 'ID ' . $id);
        }
    }

    if ($acao === 'excluir_equipamento') {
        $id = (int) ($_POST['equipamento_id'] ?? 0);
        if ($id > 0) {
            // Verificar se não está em uso
            $emUso = $conn->prepare("SELECT COUNT(*) FROM emprestimos WHERE equipamento_id = :id AND status = 'Em uso'");
            $emUso->bindParam(':id', $id);
            $emUso->execute();
            if ($emUso->fetchColumn() == 0) {
                $stmt = $conn->prepare("DELETE FROM equipamentos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                log_event($conn, 'equipamento_excluido', $adminId, 'ID ' . $id);
            }
        }
    }

    header('Location: admin.php');
    exit();
}

$usuarios = $conn->query("SELECT id, nome, id_acesso, senha, perfil, turma, bloqueado FROM usuarios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$equipamentos = $conn->query("SELECT id, codigo_equipamento, nome, tipo, localizacao, status, estado FROM equipamentos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Pesquisa administrativa</h2>
        <form class="inline-form" method="GET" action="admin.php">
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Buscar..." required>
            <select name="tipo">
                <option value="equipamento" <?php echo $searchTipo==='equipamento' ? 'selected' : ''; ?>>Equipamento</option>
                <option value="aluno" <?php echo $searchTipo==='aluno' ? 'selected' : ''; ?>>Aluno</option>
                <option value="professor" <?php echo $searchTipo==='professor' ? 'selected' : ''; ?>>Professor</option>
            </select>
            <button type="submit" class="btn">Pesquisar</button>
        </form>
        <?php if ($searchQuery !== ''): ?>
            <p class="helper-text">Resultados para "<?php echo htmlspecialchars($searchQuery); ?>" (<?php echo htmlspecialchars($searchTipo); ?>): <?php echo count($searchResults); ?></p>
            <?php if (count($searchResults) > 0): ?>
                <table class="tabela compact">
                    <thead><tr><th>ID</th><th>Item</th><th>Extra</th><th>Extra2</th></tr></thead>
                    <tbody>
                    <?php foreach ($searchResults as $item): ?>
                        <tr>
                            <td><?php echo (int) $item['id']; ?></td>
                            <td><?php echo htmlspecialchars($item['item']); ?></td>
                            <td><?php echo htmlspecialchars($item['extra']); ?></td>
                            <td><?php echo htmlspecialchars($item['extra2']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <section class="kpi-grid">
        <article class="card">
            <h2>Total de Usuários</h2>
            <p class="metric"><?php echo count($usuarios); ?></p>
        </article>
        <article class="card">
            <h2>Equipamentos Disponíveis</h2>
            <p class="metric"><?php echo $equipamentosDisponiveis; ?></p>
        </article>
        <article class="card">
            <h2>Empréstimos Ativos</h2>
            <p class="metric"><?php echo $emprestimosAtivos; ?></p>
        </article>
        <article class="card">
            <h2>Equipamentos Danificados</h2>
            <p class="metric"><?php echo $equipamentosDanificados; ?></p>
        </article>
    </section>

    <section class="card">
        <h2>Distribuição de Equipamentos por Status</h2>
        <div class="chart-container">
            <div class="chart-bar">
                <div class="bar available" style="width: <?php echo $equipamentosDisponiveis > 0 ? ($equipamentosDisponiveis / max($totalEquipamentos, 1)) * 100 : 0; ?>%">
                    <span>Disponível (<?php echo $equipamentosDisponiveis; ?>)</span>
                </div>
            </div>
            <div class="chart-bar">
                <div class="bar in-use" style="width: <?php echo $emprestimosAtivos > 0 ? ($emprestimosAtivos / max($totalEquipamentos, 1)) * 100 : 0; ?>%">
                    <span>Em uso (<?php echo $emprestimosAtivos; ?>)</span>
                </div>
            </div>
            <div class="chart-bar">
                <div class="bar damaged" style="width: <?php echo $equipamentosDanificados > 0 ? ($equipamentosDanificados / max($totalEquipamentos, 1)) * 100 : 0; ?>%">
                    <span>Danificado (<?php echo $equipamentosDanificados; ?>)</span>
                </div>
            </div>
        </div>
    </section>    <section class="card">
        <h2>Gerenciamento de Usuários</h2>
        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <strong>⚠️ Segurança:</strong> As senhas são exibidas apenas como hash para fins administrativos. Nunca compartilhe senhas em texto plano.
        </div>
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
            <thead><tr><th>ID</th><th>Nome</th><th>Acesso</th><th>Senha (Hash)</th><th>Perfil</th><th>Turma</th><th>Bloqueado</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo (int) $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['id_acesso']); ?></td>
                    <td><code style="font-size: 0.8em;"><?php echo htmlspecialchars(substr($usuario['senha'], 0, 20) . '...'); ?></code></td>
                    <td><?php echo htmlspecialchars($usuario['perfil'] ?? 'usuario'); ?></td>
                    <td><?php echo htmlspecialchars($usuario['turma'] ?: '-'); ?></td>
                    <td><?php echo (int) $usuario['bloqueado'] ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Gerenciamento de Equipamentos</h2>
        <div class="split-grid">
            <form method="POST">
                <input type="hidden" name="acao" value="novo_equipamento">
                <h3>Cadastrar novo equipamento</h3>
                <input type="text" name="codigo_equipamento" placeholder="Código do equipamento" required>
                <input type="text" name="nome" placeholder="Nome do equipamento" required>
                <select name="tipo" required>
                    <option value="">Selecione o tipo</option>
                    <option value="Notebook">Notebook</option>
                    <option value="Mouse">Mouse</option>
                    <option value="Carregador">Carregador</option>
                    <option value="Projetor">Projetor</option>
                    <option value="Teclado">Teclado</option>
                    <option value="Monitor">Monitor</option>
                    <option value="Outro">Outro</option>
                </select>
                <input type="text" name="localizacao" placeholder="Localização" value="Laboratório TI">
                <button class="btn" type="submit">Cadastrar equipamento</button>
            </form>

            <div>
                <h3>Atualizar equipamento</h3>
                <form method="POST" style="margin-bottom: 10px;">
                    <input type="hidden" name="acao" value="atualizar_equipamento">
                    <select name="equipamento_id" required>
                        <option value="">Selecione equipamento</option>
                        <?php foreach ($equipamentos as $equip): ?>
                            <option value="<?php echo (int) $equip['id']; ?>"><?php echo htmlspecialchars($equip['codigo_equipamento']); ?> - <?php echo htmlspecialchars($equip['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status">
                        <option value="Disponível">Disponível</option>
                        <option value="Em uso">Em uso</option>
                        <option value="Manutenção">Manutenção</option>
                    </select>
                    <select name="estado">
                        <option value="Bom">Bom</option>
                        <option value="Danificado">Danificado</option>
                        <option value="Manutenção">Manutenção</option>
                    </select>
                    <input type="text" name="localizacao" placeholder="Localização">
                    <button class="btn" type="submit">Atualizar</button>
                </form>

                <form method="POST">
                    <input type="hidden" name="acao" value="excluir_equipamento">
                    <h4>Excluir equipamento</h4>
                    <select name="equipamento_id" required>
                        <option value="">Selecione equipamento</option>
                        <?php foreach ($equipamentos as $equip): ?>
                            <option value="<?php echo (int) $equip['id']; ?>"><?php echo htmlspecialchars($equip['codigo_equipamento']); ?> - <?php echo htmlspecialchars($equip['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn danger" type="submit" onclick="return confirm('Tem certeza que deseja excluir este equipamento?')">Excluir</button>
                </form>
            </div>
        </div>

        <table class="tabela compact">
            <thead><tr><th>ID</th><th>Código</th><th>Nome</th><th>Tipo</th><th>Localização</th><th>Status</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($equipamentos as $equip): ?>
                <tr>
                    <td><?php echo (int) $equip['id']; ?></td>
                    <td><?php echo htmlspecialchars($equip['codigo_equipamento']); ?></td>
                    <td><?php echo htmlspecialchars($equip['nome']); ?></td>
                    <td><?php echo htmlspecialchars($equip['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($equip['localizacao']); ?></td>
                    <td><?php echo htmlspecialchars($equip['status']); ?></td>
                    <td><?php echo htmlspecialchars($equip['estado'] ?: 'Bom'); ?></td>
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
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="acao" value="excluir_emprestimo">
                            <input type="hidden" name="emprestimo_id" value="<?php echo (int) $emprestimo['id']; ?>">
                            <button class="btn danger" type="submit" onclick="return confirm('Excluir empréstimo?')">Excluir</button>
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
