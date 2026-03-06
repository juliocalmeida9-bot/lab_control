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

/*  =================================================================================
    A configuração básica do banco de dados e a criação da conexão PDO ficam aqui.
    O bloco abaixo que tratava um POST de cadastro de sala era apenas um exemplo e
    acabava sendo executado sempre que o arquivo era incluído. Isso causava inserções
    inesperadas e não faz parte da lógica principal do sistema. Se precisar dessa
    funcionalidade, mova-a para um script separado (ex: cadastrar_sala.php).
    ================================================================================= */

// Fecha conexão PDO
// (não fechamos aqui pois geralmente queremos usar a mesma conexão em outras páginas)
// $conn = null;
?>

