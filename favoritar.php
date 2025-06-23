<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

$user_id = $_SESSION['user'];

// Captura os dados enviados pelo JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$filme_id = $data['filme_id'];

// Verifica se o filme já está favoritado
$sql_check = "SELECT * FROM favoritos WHERE user_id = ? AND filme_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param('ii', $user_id, $filme_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Filme já está favoritado.']);
    exit();
}

// Insere o filme nos favoritos do usuário
$sql_insert = "INSERT INTO favoritos (user_id, filme_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param('ii', $user_id, $filme_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao favoritar o filme.']);
}

$stmt->close();
$conn->close();
?>
