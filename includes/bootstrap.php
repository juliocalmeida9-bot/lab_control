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
        nome VARCHAR(120) NOT NULL,
        id_acesso VARCHAR(40) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        perfil ENUM('admin','usuario') DEFAULT 'usuario',
        turma VARCHAR(80) DEFAULT NULL,
        tentativas INT DEFAULT 0,
        bloqueado TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS equipamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo_equipamento VARCHAR(40) UNIQUE NOT NULL,
        nome VARCHAR(120) NOT NULL,
        tipo VARCHAR(60) NOT NULL,
        localizacao VARCHAR(120) DEFAULT 'Laboratório TI',
        status ENUM('Disponível','Em uso','Manutenção') DEFAULT 'Disponível',
        observacoes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS emprestimos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        responsavel_nome VARCHAR(120) NOT NULL,
        turma VARCHAR(80) DEFAULT NULL,
        equipamento_id INT NOT NULL,
        data_retirada DATETIME NOT NULL,
        status ENUM('Em uso','Finalizado') DEFAULT 'Em uso',
        observacoes TEXT DEFAULT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS devolucoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        emprestimo_id INT NOT NULL,
        data_devolucao DATETIME NOT NULL,
        observacoes TEXT DEFAULT NULL,
        FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->exec("CREATE TABLE IF NOT EXISTS historico (
        id INT AUTO_INCREMENT PRIMARY KEY,
        acao VARCHAR(80) NOT NULL,
        usuario_id INT DEFAULT NULL,
        detalhes TEXT,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Migração básica para base antiga
    $oldTable = $conn->query("SHOW TABLES LIKE 'equipamento'")->fetchColumn();
    if ($oldTable) {
        $exists = (int) $conn->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
        if ($exists === 0) {
            $conn->exec("INSERT IGNORE INTO equipamentos (codigo_equipamento, nome, tipo, localizacao, status, observacoes)
                SELECT
                    CONCAT('EQ-', id_equipamento),
                    CONCAT(tipo, ' ', patrimonio),
                    tipo,
                    'Laboratório TI',
                    CASE
                        WHEN status = 'Em Uso' THEN 'Em uso'
                        WHEN status = 'Manutencao' THEN 'Manutenção'
                        ELSE 'Disponível'
                    END,
                    NULL
                FROM equipamento");
        }
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id_acesso = :id LIMIT 1");
    $adminId = 'admin';
    $stmt->bindParam(':id', $adminId);
    $stmt->execute();
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $senha = password_hash('admin123', PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO usuarios (nome, id_acesso, senha, perfil) VALUES ('Administrador', :id, :senha, 'admin')");
        $ins->bindParam(':id', $adminId);
        $ins->bindParam(':senha', $senha);
        $ins->execute();
    }

    $done = true;
}

function log_event(PDO $conn, string $acao, ?int $usuarioId, string $detalhes = ''): void
{
    $stmt = $conn->prepare("INSERT INTO historico (acao, usuario_id, detalhes, created_at) VALUES (:acao, :usuario_id, :detalhes, :created_at)");
    $now = date('Y-m-d H:i:s');
    $stmt->bindParam(':acao', $acao);
    $stmt->bindParam(':usuario_id', $usuarioId);
    $stmt->bindParam(':detalhes', $detalhes);
    $stmt->bindParam(':created_at', $now);
    $stmt->execute();
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
        header('Location: dashboard.php');
        exit();
    }
}

function logo_control_lab_path(): ?string
{
    $candidates = [
        __DIR__ . '/../imagens/logo_control_lab.png',
        __DIR__ . '/../imagens/logo-control-lab.png',
        __DIR__ . '/../imagens/logo-controllab.png'
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            return '../imagens/' . basename($file);
        }
    }

    return null;
}
