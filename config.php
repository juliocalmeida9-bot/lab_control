<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "controle_laboratorio";
$port = 3307;

try {
    // Conexão PDO correta
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Se veio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $bloco = $_POST['bloco'];
    $capacidade = $_POST['capacidade'];
    $descricao = $_POST['descricao'];

    // Usando prepared statement (mais seguro)
    $sql = "INSERT INTO sala (nome, bloco, capacidade, descricao)
            VALUES (:nome, :bloco, :capacidade, :descricao)";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':bloco', $bloco);
    $stmt->bindParam(':capacidade', $capacidade);
    $stmt->bindParam(':descricao', $descricao);

    if ($stmt->execute()) {
        echo "Sala cadastrada com sucesso!";
        echo "<br><a href='index.html'>Voltar</a>";
    } else {
        echo "Erro ao cadastrar.";
    }
}

// Fecha conexão PDO
$conn = null;
?>