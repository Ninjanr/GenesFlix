<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';
require_once "conexao.php";

// Verificar se o token foi fornecido via GET (como na URL)
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar se o token existe no banco de dados e não expirou
    $sql = "SELECT * FROM users WHERE reset_token = ? AND token_expiration > NOW()";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($user) {
            // Token válido, permitir que o usuário redefina a senha
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $new_password = $_POST["new_password"];
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Atualizar a senha no banco de dados
                $sql_update = "UPDATE users SET password = ?, reset_token = NULL, token_expiration = NULL WHERE reset_token = ?";
                $stmt_update = mysqli_stmt_init($conn);

                if (mysqli_stmt_prepare($stmt_update, $sql_update)) {
                    mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $token);
                    mysqli_stmt_execute($stmt_update);

                    echo "<div class='alert alert-success'>Senha atualizada com sucesso!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Erro ao atualizar a senha. Tente novamente mais tarde.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger'>Token inválido ou expirado.</div>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
} else {
    echo "<div class='alert alert-danger'>Token não fornecido.</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/alterar.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Redefinir Senha</title>
    <style>
       .password-container {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            left: 340px;
            top: 50%;
            transform: translateY(-5%);
            cursor: pointer;
            color: #ffffff;
        }
    </style>
</head>

<body>
<button class="back-button" onclick="goBack()">
        <i class="material-icons">arrow_back</i>
</button>
    <div class="container">
        <div class="header">
            <img src="https://cdn-icons-png.flaticon.com/512/10120/10120205.png" alt="Logo" class="logo">
            <h2>Redefinir Senha</h2>
            <p>Defina uma nova senha forte para garantir a segurança das suas informações.</p>
        </div>
        <form action="alterar_senha.php?token=<?php echo $token; ?>" method="post">
        <div class="form-group">
                <div class="form-group password-container">
                    <label for="new_password">Nova Senha</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Digite a nova senha" required>
                    <span class="eye-icon" onclick="togglePassword()">
                        <img src="overlay/eye-icon.png" alt="Mostrar Senha" id="eye-icon" style="width: 20px;">
                    </span>
                </div>
            </div>
            <button type="submit" class="btn">Redefinir Senha</button>
        </form>
        <div class="footer">
            <p>Voltar para o <a href="login.php">login</a>.</p>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('new_password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.src = 'overlay/eye-icon-open.png'; // Substitua pelo ícone do olho aberto
            } else {
                passwordField.type = 'password';
                eyeIcon.src = 'overlay/eye-icon.png'; // Substitua pelo ícone do olho fechado
            }
        }

        function goBack() {
            window.history.back(); // Navega de volta na história do navegador
        }
    </script>
</body>

</html>