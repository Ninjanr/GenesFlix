<?php
session_start();
require 'conexao.php';

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user'];

// Verifique se o ID do filme foi passado
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $filme_id = $_POST['id'];

    // SQL para excluir a associação do filme com os gêneros, sem excluir os gêneros
    $sql_delete_association = "DELETE FROM filmes_genero WHERE id_filmes = ?";
    $stmt = $conn->prepare($sql_delete_association);

    if ($stmt === false) {
        // Se prepare falhar, exiba o erro SQL
        die('Erro na consulta SQL: ' . $conn->error);
    }

    $stmt->bind_param('i', $filme_id);

    // Executa a consulta para remover a associação
    if ($stmt->execute()) {
        // Agora, vamos atualizar o filme, limpando os dados sem excluir o filme
        $sql_update = "UPDATE filmes SET titulo = NULL, descricao = NULL, imagem = NULL, ano = NULL, url = NULL WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update === false) {
            // Se prepare falhar, exiba o erro SQL
            die('Erro na consulta SQL de atualização: ' . $conn->error);
        }

        $stmt_update->bind_param('i', $filme_id);

        // Executa a consulta de atualização
        if ($stmt_update->execute()) {
            $message = "Filme excluído com sucesso, mas os gêneros não foram afetados.";
        } else {
            $message = "Erro ao atualizar o filme.";
        }

        $stmt_update->close();
    } else {
        $message = "Erro ao remover a associação do filme com os gêneros.";
    }

    $stmt->close();
} else {
    // Caso o ID não seja válido
    $message = "ID de filme inválido.";
}

$conn->close();

// Redireciona para a página de mensagem com a variável de status
header("Location: filme_update_message.php?message=" . urlencode($message));
exit();
?>
