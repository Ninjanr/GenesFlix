<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

$user_id = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filme_id = $_POST['filme_id'] ?? null;

    if ($filme_id) {
        $sql_remove = "DELETE FROM favoritos WHERE user_id = ? AND filme_id = ?";
        $stmt = $conn->prepare($sql_remove);
        $stmt->bind_param('ii', $user_id, $filme_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Filme removido com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover o filme.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID do filme não fornecido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}

$conn->close();
?>
