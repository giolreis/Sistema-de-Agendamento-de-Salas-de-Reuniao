<?php
$servername = "localhost";  // Nome do servidor (geralmente 'localhost' no XAMPP)
$username = "root";         // Nome de usuário do banco de dados (usuário padrão do XAMPP é 'root')
$password = "";             // Senha do banco de dados (vazia no XAMPP por padrão)
$dbname = "NaturezaViva";   // Nome do banco de dados que você criou

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
