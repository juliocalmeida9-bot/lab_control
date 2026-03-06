<?php
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $id_acesso = trim($_POST['id_acesso']);
    $senha = $_POST['senha'];
    $perfil = $_POST['perfil'] === 'admin' ? 'admin' : 'usuario';

    if ($nome && $id_acesso && $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO usuarios (nome, id_acesso, senha, perfil) VALUES (:nome, :id_acesso, :senha, :perfil)';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id_acesso', $id_acesso);
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':perfil', $perfil);
        $msg = $stmt->execute() ? 'Usuário criado com sucesso' : 'Erro ao criar usuário';
    } else {
        $msg = 'Preencha todos os campos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar usuário - Control Lab</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
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
