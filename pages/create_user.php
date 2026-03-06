<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$nome = trim($_POST['nome'] ?? '');
$id_acesso = trim($_POST['id_acesso'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil = $_POST['perfil'] ?? '';
$turma = trim($_POST['turma'] ?? '');
$equipe = trim($_POST['equipe'] ?? '');

if ($nome && $id_acesso && $senha && $perfil && $turma && $equipe) {
    // Verificar se já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE id_acesso = :id_acesso");
    $check->bindParam(':id_acesso', $id_acesso);
    $check->execute();
    if ($check->fetch()) {
        header('Location: index.php?cadastro_erro=1#cadastro');
        exit();
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil, turma) VALUES (:nome, :id_acesso, :senha, :perfil, :turma)");
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':id_acesso', $id_acesso);
    $stmt->bindParam(':senha', $hash);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->bindParam(':turma', $turma);
    $stmt->execute();

    log_event($conn, 'usuario_auto_cadastro', null, $id_acesso . ' - ' . $perfil . ' - ' . $turma . ' - ' . $equipe);

    header('Location: index.php?cadastro_sucesso=1#login');
    exit();
}

header('Location: index.php?cadastro_erro=1#cadastro');
exit();
?>
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


