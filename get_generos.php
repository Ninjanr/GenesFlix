<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT id, nome FROM genero";
$result = $conn->query($sql);

$generos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $generos[] = $row;
    }
}

echo json_encode($generos);
$conn->close();
