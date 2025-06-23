<?php
session_start();
require 'conexao.php'; // Inclui a conexão com o banco de dados

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = strtolower($_POST['email']);
    $profile_picture = $_POST['profile_picture']; // Captura o valor do avatar

    // Verificação simples para debug
    var_dump($profile_picture); // Verifica se o valor do avatar foi recebido corretamente

    // Verifica se o avatar está vazio
    if (empty($profile_picture)) {
        $profile_picture = 'img/default_avatar.png'; // Caso não tenha sido selecionado, usa uma imagem padrão (se desejar)
    }

    // Atualize os dados do usuário no banco de dados
    $sql = "UPDATE users SET full_name = ?, email = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $profile_picture, $user_id);

    if ($stmt->execute()) {
        $message = "Perfil atualizado com sucesso!";
    } else {
        $message = "Erro ao atualizar perfil: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redireciona para a página de mensagem
    header("Location: profile_update_message.php?message=" . urlencode($message));
    exit();
}
