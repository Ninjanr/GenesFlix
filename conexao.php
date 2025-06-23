<?php

$hostName = "";
$dbUser = "";
$dbPassword = "";
$dbName = "";

// Cria a conexão com o banco de dados
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);

// Verifica se houve falha na conexão
if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error()); // Mostra uma mensagem de erro
}

?>
