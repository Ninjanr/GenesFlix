<?php
session_start();
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $comentario = $data['comentario'] ?? '';
    $id_filme = $data['id_filme'] ?? '';
    $id_usuario = $_SESSION['user'] ?? '';

    if (!$comentario || !$id_filme || !$id_usuario) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit();
    }

    $sql = "INSERT INTO comentarios (id_filme, id_usuario, comentario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $id_filme, $id_usuario, $comentario);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar comentário.']);
    }

    $stmt->close();
    $conn->close();
}
?>
