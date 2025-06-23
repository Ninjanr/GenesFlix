<?php
require 'conexao.php';

// Verifica se a query foi enviada
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepara a consulta para buscar filmes que correspondam ao termo pesquisado
    $sql = "SELECT titulo FROM filmes WHERE titulo LIKE ? LIMIT 5"; // Limite para evitar muitas sugestões
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
    }

    // Retorna as sugestões em formato JSON
    echo json_encode($suggestions);
}
?>
