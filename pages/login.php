<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_acesso = $_POST['id_acesso'];
    $senha = $_POST['senha'];

    // Buscar usuário pelo ID
    $sql = "SELECT * FROM usuarios WHERE id_acesso = :id_acesso";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id_acesso", $id_acesso);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {

        // Verifica se está bloqueado
        if ($usuario['bloqueado']) {
            header("Location: index.php?bloqueado=1");
            exit();
        }

        // Verifica senha
        if (password_verify($senha, $usuario['senha'])) {

            // Resetar tentativas
            $reset = $conn->prepare("UPDATE usuarios SET tentativas = 0 WHERE id = :id");
            $reset->bindParam(":id", $usuario['id']);
            $reset->execute();

            // Criar sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            header("Location: dashboard.php");
            exit();

        } else {

            // Incrementar tentativas
            $tentativas = $usuario['tentativas'] + 1;

            if ($tentativas >= 3) {
                $bloquear = $conn->prepare("UPDATE usuarios SET tentativas = :tentativas, bloqueado = 1 WHERE id = :id");
                $bloquear->bindParam(":tentativas", $tentativas);
                $bloquear->bindParam(":id", $usuario['id']);
                $bloquear->execute();

                header("Location: index.php?bloqueado=1");
                exit();
            } else {
                $update = $conn->prepare("UPDATE usuarios SET tentativas = :tentativas WHERE id = :id");
                $update->bindParam(":tentativas", $tentativas);
                $update->bindParam(":id", $usuario['id']);
                $update->execute();

                header("Location: index.php?erro=1");
                exit();
            }
        }

    } else {
        header("Location: index.php?erro=1");
        exit();
    }
}
?>