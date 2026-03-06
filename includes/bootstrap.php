<?php
require_once(__DIR__ . '/config.php');

function ensure_schema(PDO $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100),
        id_acesso VARCHAR(20) UNIQUE,
        senha VARCHAR(255),
        perfil VARCHAR(20) DEFAULT 'usuario',
        tentativas INT DEFAULT 0,
        bloqueado BOOLEAN DEFAULT FALSE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN perfil VARCHAR(20) DEFAULT 'usuario'");
    } catch (PDOException $e) {
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS equipamento (
        id_equipamento INT AUTO_INCREMENT PRIMARY KEY,
        patrimonio VARCHAR(50) UNIQUE NOT NULL,
        tipo ENUM('Notebook','Mouse','Carregador') NOT NULL,
        status ENUM('Disponivel','Em Uso','Manutencao') DEFAULT 'Disponivel',
        estado ENUM('Bom','Danificado') DEFAULT 'Bom',
        id_sala INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS registros (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipe_id INT,
        data_retirada DATETIME,
        data_devolucao DATETIME,
        danos TEXT,
        FOREIGN KEY (equipe_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS registro_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registro_id INT NOT NULL,
        equipamento_id INT NOT NULL,
        condicao_retirada VARCHAR(30) DEFAULT 'ok',
        condicao_devolucao VARCHAR(30) DEFAULT NULL,
        FOREIGN KEY (registro_id) REFERENCES registros(id) ON DELETE CASCADE,
        FOREIGN KEY (equipamento_id) REFERENCES equipamento(id_equipamento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $adminStmt = $conn->prepare("SELECT id FROM usuarios WHERE id_acesso = :id LIMIT 1");
    $adminId = 'admin';
    $adminStmt->bindParam(':id', $adminId);
    $adminStmt->execute();

    if (!$adminStmt->fetch(PDO::FETCH_ASSOC)) {
        $senha = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil) VALUES ('Administrador', :id_acesso, :senha, 'admin')");
        $insert->bindParam(':id_acesso', $adminId);
        $insert->bindParam(':senha', $senha);
        $insert->execute();
    }

    $done = true;
}

function require_login(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit();
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['usuario_perfil'] ?? 'usuario') !== 'admin') {
        header('Location: dashboard.php?erro=sem_permissao');
        exit();
    }
}
