<?php
session_start();
require 'conexao.php'; // Arquivo que faz a conexão com o banco

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user'];

// SQL para remover os dados do usuário, mantendo o ID
$sql = "UPDATE users 
SET full_name = NULL, 
    email = CONCAT('user', id, '_removed@example.com'), 
    password = NULL, 
    profile_picture = NULL 
WHERE id = ?;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $message = "Dados do usuário excluídos com sucesso!";
} else {
    $message = "Erro ao excluir os dados do usuário!";
}

$stmt->close();
$conn->close();

// Redireciona para a página de mensagem
header("Location: profile_update_message.php?message=" . urlencode($message));
exit();
?>