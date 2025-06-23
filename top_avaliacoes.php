<?php
require 'conexao.php';

// Consulta para obter os 10 filmes com melhores avaliações
$sql = "
    SELECT 
        f.titulo AS titulo, 
        AVG(a.nota) AS media_nota
    FROM 
        filmes f
    INNER JOIN 
        avaliacao a ON f.id = a.filmes_avaliacao
    GROUP BY 
        f.id
    ORDER BY 
        media_nota DESC
    LIMIT 10
";

$result = $conn->query($sql);
$dados = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($dados);
