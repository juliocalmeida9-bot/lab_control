<?php
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $idAcesso = trim($_POST['id_acesso'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $perfil = $_POST['perfil'] === 'admin' ? 'admin' : 'usuario';
    $turma = trim($_POST['turma'] ?? '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $id_acesso = trim($_POST['id_acesso']);
    $senha = $_POST['senha'];
    $perfil = $_POST['perfil'] === 'admin' ? 'admin' : 'usuario';

    if ($nome && $idAcesso && $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil, turma) VALUES (:nome, :id, :senha, :perfil, :turma)");

        $sql = 'INSERT INTO usuarios (nome, id_acesso, senha, perfil) VALUES (:nome, :id_acesso, :senha, :perfil)';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id', $idAcesso);
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':turma', $turma);
        $stmt->execute();
        $msg = 'Usuário criado com sucesso';

        $msg = $stmt->execute() ? 'Usuário criado com sucesso' : 'Erro ao criar usuário';
    } else {
        $msg = 'Preencha os campos obrigatórios';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><title>Criar usuário</title><link rel="stylesheet" href="../css/style.css"></head>
<body>
<div class="login-container"><div class="login-card"><h2>Criar usuário</h2><?php if ($msg): ?><p><?php echo htmlspecialchars($msg); ?></p><?php endif; ?><form method="POST"><input name="nome" placeholder="Nome" required><input name="id_acesso" placeholder="Login" required><input name="turma" placeholder="Turma"><input type="password" name="senha" placeholder="Senha" required><select name="perfil"><option value="usuario">Usuário</option><option value="admin">Admin</option></select><button class="btn" type="submit">Criar</button></form></div></div>

<div class="form-container">
    <div class="card wide">
        <h2>Cadastrar usuário</h2>
        <?php if ($msg): ?><p><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
        <form method="post">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="id_acesso" placeholder="ID de acesso" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <select name="perfil">
                <option value="usuario">Usuário</option>
                <option value="admin">Administrador</option>
            </select>
            <button class="btn" type="submit">Criar</button>
        </form>
    </div>
</div>
</body>
</html>


