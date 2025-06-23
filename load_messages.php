<?php
session_start();
require 'conexao.php';

$user_id = $_SESSION['user'];

// Buscar as mensagens enviadas para o usuário
$sql = "SELECT * FROM messages WHERE (sender_id = ? OR receiver_id = ?) ORDER BY timestamp";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
