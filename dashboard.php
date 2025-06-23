<?php
session_start();
require 'conexao.php'; // Inclui a conexão com o banco de dados

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user'];

// Verifica o papel do usuário logado
$sql_user = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

// Se não for chefe, redireciona
if (!$user || $user['role'] !== 'admin_boss') {
    header('Location: erro_acesso.php');
    exit();
}

// Processa o formulário para atualizar o papel
$statusMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $target_user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Impede que o chefe altere seu próprio papel
    if ($target_user_id == $user_id) {
        $statusMessage = "<div class='alert alert-danger'>Você não pode alterar seu próprio papel.</div>";
    } else {
        // Atualiza o papel no banco de dados
        $sql_update = "UPDATE users SET role = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('si', $new_role, $target_user_id);

        if ($stmt_update->execute()) {
            $statusMessage = "<div class='alert alert-success'>Papel atualizado com sucesso!</div>";
        } else {
            $statusMessage = "<div class='alert alert-danger'>Erro ao atualizar o papel do usuário: " . $stmt_update->error . "</div>";
        }
        $stmt_update->close();
    }
}

// Obter lista de usuários para exibição
$sql_users = "SELECT id, full_name, email, role FROM users WHERE role != 'admin_boss'";
$result_users = $conn->query($sql_users);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Chefe</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/home_tema.css">
    <script src="JS/script_tema.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
  <header class="header">
    <div class="header-left">
      <img src="img/logo_title.png" alt="Logo">
    </div>
  </header>
<body>

<button class="back-button" onclick="goBack()">
        <i class="material-icons">arrow_back</i>
</button>

    <div class="container">
        <h1>Bem-vindo, Chefe!</h1>
        <p>Gerencie os papéis dos usuários abaixo:</p>
        <?= $statusMessage ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Papel Atual</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="role" value="admin" 
                                    class="btn-admin" <?= $row['role'] === 'admin' ? 'disabled' : '' ?>>Tornar Admin</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="role" value="user" 
                                    class="btn-user" <?= $row['role'] === 'user' ? 'disabled' : '' ?>>Tornar User</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
        function goBack() {
            window.history.back(); // Navega de volta na história do navegador
        }
    </script>
</body>

</html>
