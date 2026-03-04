<?php
// pequeno formulário para inserir um usuário na tabela `usuarios`
// útil após rodar init_db.php

require_once(__DIR__ . '/../includes/config.php');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $id_acesso = trim($_POST['id_acesso']);
    $senha = $_POST['senha'];

    if ($nome && $id_acesso && $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, id_acesso, senha) VALUES (:nome, :id_acesso, :senha)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id_acesso', $id_acesso);
        $stmt->bindParam(':senha', $hash);
        if ($stmt->execute()) {
            $msg = 'Usuário criado com sucesso';
        } else {
            $msg = 'Erro ao criar usuário';
        }
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
        <div class="card">
            <h2>Cadastrar usuário</h2>
            <?php if ($msg): ?><p><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
            <form method="post">
                <input type="text" name="nome" placeholder="Nome completo" required>
                <input type="text" name="id_acesso" placeholder="ID de acesso" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button class="btn" type="submit">Criar</button>
            </form>
        </div>
    </div>
<script src="../js/main.js"></script>
</body>
</html>