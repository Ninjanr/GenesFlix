<?php
session_start();
require 'conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

$user_id = $_SESSION['user'];

// Receber dados enviados pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['message']) || empty($data['sender_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    exit();
}

$message = $data['message'];
$sender_id = $data['sender_id'];

// Procurar o e-mail do destinatário (se houver) após o primeiro "@"
preg_match('/@([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $message, $matches);
$receiver_email = isset($matches[1]) ? $matches[1] : null;

if ($receiver_email) {
    // Buscar o ID do usuário com esse e-mail
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $receiver_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $receiver = $result->fetch_assoc();
        $receiver_id = $receiver['id'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado com esse e-mail.']);
        exit();
    }
} else {
    // Se não houver "@" no texto, atribui como mensagem para o admin (ou outro padrão)
    $receiver_id = 1; // ID do administrador ou outro padrão
}

// Preparar a instrução SQL para inserir a mensagem
$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

// Verificar se a preparação da query foi bem-sucedida
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao preparar a consulta.']);
    exit();
}

// Vincular parâmetros e executar a consulta
$stmt->bind_param('iis', $sender_id, $receiver_id, $message);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Mensagem enviada com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar mensagem.']);
}

// Fechar a consulta
$stmt->close();
$conn->close();
?>
