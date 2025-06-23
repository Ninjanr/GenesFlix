<?php
session_start();
require 'conexao.php'; // Conexão com o banco de dados

header('Content-Type: application/json');

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado.']);
    exit();
}

$user_id = $_SESSION['user'];

// Receber o corpo do POST request (avaliação e filme)
$data = json_decode(file_get_contents('php://input'), true);

// Validações básicas
if (!isset($data['rating'], $data['filme'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos enviados.']);
    exit();
}

$rating = (int) $data['rating'];
$filmeTitulo = trim($data['filme']);

// Validações adicionais
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Nota inválida.']);
    exit();
}

// Buscar o ID do filme baseado no título
$sql_filme = "SELECT id FROM filmes WHERE titulo = ?";
$stmt = $conn->prepare($sql_filme);
$stmt->bind_param('s', $filmeTitulo);
$stmt->execute();
$result_filme = $stmt->get_result();

if ($result_filme->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Filme não encontrado.']);
    exit();
}

$filme = $result_filme->fetch_assoc();
$filme_id = $filme['id'];

// Verificar se o usuário já avaliou o filme
$sql_check = "SELECT id FROM avaliacao WHERE filmes_avaliacao = ? AND usuario_avaliacao = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param('ii', $filme_id, $user_id);
$stmt->execute();
$result_check = $stmt->get_result();

if ($result_check->num_rows > 0) {
    // Avaliação existente: atualizar a nota
    $sql_update = "UPDATE avaliacao SET nota = ?, data = CURDATE() WHERE filmes_avaliacao = ? AND usuario_avaliacao = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('iii', $rating, $filme_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Avaliação atualizada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar avaliação.']);
    }
} else {
    // Inserir nova avaliação
    $sql_insert = "INSERT INTO avaliacao (nota, data, filmes_avaliacao, usuario_avaliacao) VALUES (?, CURDATE(), ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param('iii', $rating, $filme_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Avaliação enviada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao inserir avaliação.']);
    }
}

// Fechar conexões
$stmt->close();
$conn->close();
?>
