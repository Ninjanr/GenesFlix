<?php
session_start();
require 'conexao.php';

// Verificar se o id_filme foi passado como parâmetro
if (isset($_GET['id_filme'])) {
    $id_filme = $_GET['id_filme'];

    // Preparar a consulta para pegar os comentários e o nome do usuário
    $sql = "
        SELECT 
            comentarios.comentario, 
            comentarios.data_comentario, 
            users.full_name,
            users.profile_picture
        FROM 
            comentarios
        JOIN 
            users ON comentarios.id_usuario = users.id
        WHERE 
            comentarios.id_filme = ? 
        ORDER BY 
            comentarios.data_comentario DESC
    ";

    // Preparar a consulta
    if ($stmt = $conn->prepare($sql)) {
        // Vincular o parâmetro
        $stmt->bind_param("i", $id_filme);

        // Executar a consulta
        $stmt->execute();

        // Obter os resultados
        $result = $stmt->get_result();
        $comments = [];

        // Verificar se há comentários
        while ($row = $result->fetch_assoc()) {
            $comments[] = [
                'full_name' => $row['full_name'],
                'comentario' => $row['comentario'],
                'data_comentario' => $row['data_comentario'],
                'profile_picture' => htmlspecialchars($row['profile_picture']) // URL da imagem de perfil
            ];
        }

        // Fechar a consulta
        $stmt->close();

        // Retornar os comentários como JSON
        echo json_encode($comments);
    } else {
        // Caso a consulta falhe
        echo json_encode(['error' => 'Erro na consulta ao banco de dados']);
    }
} else {
    // Caso o id_filme não seja passado
    echo json_encode(['error' => 'ID do filme não fornecido']);
}

// Fechar a conexão com o banco
$conn->close();
?>
