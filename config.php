<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "controle_laboratorio";
$port = 3307; // A porta que você alterou no XAMPP

// 1. Criar a conexão
$conn = new mysqli($host, $user, $pass, $db, $port);

// 2. Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 3. Receber dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $bloco = $_POST['bloco'];
    $capacidade = $_POST['capacidade'];
    $descricao = $_POST['descricao'];

    // 4. Inserir no banco
    $sql = "INSERT INTO sala (nome, bloco, capacidade, descricao) VALUES ('$nome', '$bloco', '$capacidade', '$descricao')";

    if ($conn->query($sql) === TRUE) {
        echo "Sala cadastrada com sucesso!";
        echo "<br><a href='index.html'>Voltar</a>";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
}

$conn->close();
?>
