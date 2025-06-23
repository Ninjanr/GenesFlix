<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Captura os dados do formulário
$id = $_POST['id'] ?? null;
$titulo = $_POST['titulo'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$ano = $_POST['ano'] ?? null;
$url = $_POST['url'] ?? null;
$genero = $_POST['genero'] ?? null; // Captura o gênero enviado

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID do filme não fornecido']);
    exit();
}

// Verifica se uma nova imagem foi enviada
$imagem = null;
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
}

// Recupera os dados atuais do filme
$sql_select = "SELECT titulo, descricao, ano, url, imagem FROM filmes WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Filme não encontrado']);
    exit();
}

// Usa os valores antigos se os novos estiverem vazios
$titulo = $titulo ?: $result['titulo'];
$descricao = $descricao ?: $result['descricao'];
$ano = $ano ?: $result['ano'];
$url = $url ?: $result['url'];
$imagem = $imagem ?: $result['imagem']; // Mantém a imagem anterior se nenhuma nova for fornecida

// Prepara a query de atualização com ou sem a imagem
if ($imagem) {
    $sql = "UPDATE filmes SET titulo = ?, descricao = ?, ano = ?, url = ?, imagem = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissi", $titulo, $descricao, $ano, $url, $imagem, $id);
} else {
    $sql = "UPDATE filmes SET titulo = ?, descricao = ?, ano = ?, url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $titulo, $descricao, $ano, $url, $id);
}

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}

// Atualiza o relacionamento do filme com o gênero na tabela associativa
if ($genero) {
    // Remove o gênero antigo
    $sqlDeleteGenero = "DELETE FROM filmes_genero WHERE id_filmes = ?";
    $stmtDeleteGenero = $conn->prepare($sqlDeleteGenero);
    $stmtDeleteGenero->bind_param("i", $id);
    if (!$stmtDeleteGenero->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao remover o gênero antigo: ' . $stmtDeleteGenero->error]);
        $stmtDeleteGenero->close();
        $conn->close();
        exit();
    }
    $stmtDeleteGenero->close();

    // Adiciona o novo gênero
    $sqlInsertGenero = "INSERT INTO filmes_genero (id_genero, id_filmes) VALUES (?, ?)";
    $stmtInsertGenero = $conn->prepare($sqlInsertGenero);
    $stmtInsertGenero->bind_param("ii", $genero, $id);

    if (!$stmtInsertGenero->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o gênero: ' . $stmtInsertGenero->error]);
        $stmtInsertGenero->close();
        $conn->close();
        exit();
    }
    $stmtInsertGenero->close();
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Filme atualizado com sucesso!']);
exit();
?>