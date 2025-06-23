<?php
session_start();
require 'conexao.php'; // Conexão com o banco de dados

// Definir o cabeçalho para retorno em JSON
header('Content-Type: application/json');

// Verificar se o ID do filme foi passado
if (!isset($_GET['filme_id'])) {
    echo json_encode(['success' => false, 'message' => 'Erro: ID do filme não fornecido.']);
    exit();
}

$filme_id = $_GET['filme_id'];

// Query para pegar a média da primeira avaliação de cada usuário para o filme
$sql = "SELECT AVG(nota) AS media FROM avaliacao WHERE filmes_avaliacao = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da query.']);
    exit();
}

$stmt->bind_param('i', $filme_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se houve retorno e calcular a média
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $media_avaliacao = $row['media'];

    // Verificar se a média é null e definir para 0
    if ($media_avaliacao === null) {
        $media_avaliacao = 0;
    }

    // Retornar a média no formato JSON
    echo json_encode(['success' => true, 'media_avaliacao' => $media_avaliacao]);
} else {
    echo json_encode(['success' => false, 'message' => 'Sem avaliações ainda.']);
}

$stmt->close();
$conn->close();
?>
